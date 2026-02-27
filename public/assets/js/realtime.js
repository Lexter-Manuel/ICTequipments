/**
 * RealtimeManager — Smart polling with change detection
 * 
 * Instead of hammering the server every 1 second with full data fetches,
 * this polls a tiny endpoint (check_updates.php) every N seconds. That
 * endpoint returns only timestamps (~200 bytes). Only when a category
 * timestamp changes does the manager fire an event so the page can
 * refresh just that data.
 * 
 * Usage:
 *   // Auto-starts on construction
 *   const realtime = new RealtimeManager({ interval: 5000 });
 * 
 *   // Listen for specific category changes
 *   realtime.on('equipment', () => { reloadEquipmentTable(); });
 *   realtime.on('employees', () => { reloadEmployeeList(); });
 *   realtime.on('*', (category) => { console.log(category + ' changed'); });
 * 
 *   // Temporary pause (e.g. while user is editing a form)
 *   realtime.pause();
 *   realtime.resume();
 * 
 *   // Cleanup
 *   realtime.destroy();
 */

class RealtimeManager {
    /**
     * @param {Object} opts
     * @param {number} opts.interval   Polling interval in ms (default 5000)
     * @param {string} opts.endpoint   URL of the check_updates endpoint
     * @param {boolean} opts.autoStart Start polling immediately (default true)
     * @param {boolean} opts.pauseOnHidden Pause when tab is hidden (default true)
     */
    constructor(opts = {}) {
        this.interval       = opts.interval || 5000;
        this.endpoint       = opts.endpoint || '../ajax/check_updates.php';
        this.autoStart      = opts.autoStart !== false;
        this.pauseOnHidden  = opts.pauseOnHidden !== false;

        this._timerId       = null;
        this._paused        = false;
        this._destroyed     = false;
        this._listeners     = {};        // { category: [fn, fn, ...] }
        this._lastTimestamps = {};       // { category: "2026-02-27 ..." }
        this._isFirstCheck  = true;      // Don't fire on initial load
        this._consecutiveErrors = 0;
        this._maxInterval   = 30000;     // Back off to 30s max on errors

        // Pause polling when tab becomes hidden to save resources
        if (this.pauseOnHidden) {
            this._visibilityHandler = () => {
                if (document.hidden) {
                    this._pauseInternal();
                } else {
                    this._resumeInternal();
                }
            };
            document.addEventListener('visibilitychange', this._visibilityHandler);
        }

        if (this.autoStart) {
            this.start();
        }
    }

    // ─── Public API ───────────────────────────────────────

    /**
     * Subscribe to changes for a category
     * @param {string} category  e.g. 'equipment', 'employees', or '*' for all
     * @param {Function} callback  Called when that category's data changes.
     *                             For '*', receives the category name as arg.
     * @returns {Function} Unsubscribe function
     */
    on(category, callback) {
        if (!this._listeners[category]) {
            this._listeners[category] = [];
        }
        this._listeners[category].push(callback);

        // Return convenient unsubscribe function
        return () => {
            this._listeners[category] = this._listeners[category].filter(fn => fn !== callback);
        };
    }

    /** Remove all listeners for a category (or all if no arg) */
    off(category) {
        if (category) {
            delete this._listeners[category];
        } else {
            this._listeners = {};
        }
    }

    /** Start polling */
    start() {
        if (this._destroyed) return;
        this._paused = false;
        this._scheduleNext(0); // First check immediately
    }

    /** Pause polling (e.g. user is editing a modal) */
    pause() {
        this._paused = true;
        this._clearTimer();
    }

    /** Resume after manual pause */
    resume() {
        if (this._destroyed) return;
        this._paused = false;
        this._scheduleNext(0);
    }

    /** Permanently stop — removes all listeners and timers */
    destroy() {
        this._destroyed = true;
        this._clearTimer();
        this._listeners = {};
        if (this._visibilityHandler) {
            document.removeEventListener('visibilitychange', this._visibilityHandler);
        }
    }

    /** Force an immediate check (useful after the current user saves something) */
    checkNow() {
        if (this._destroyed) return;
        this._clearTimer();
        this._poll();
    }

    /** Get current known timestamps (read-only copy) */
    getTimestamps() {
        return { ...this._lastTimestamps };
    }

    /** Check if a specific category has been modified since we last checked */
    isStale(category) {
        return this._lastTimestamps.hasOwnProperty(category);
    }

    // ─── Internal ─────────────────────────────────────────

    _pauseInternal() {
        this._clearTimer();
    }

    _resumeInternal() {
        if (!this._paused && !this._destroyed) {
            this._scheduleNext(1000); // Small delay after tab returns
        }
    }

    _clearTimer() {
        if (this._timerId) {
            clearTimeout(this._timerId);
            this._timerId = null;
        }
    }

    _scheduleNext(delayOverride) {
        this._clearTimer();
        if (this._destroyed || this._paused) return;

        const delay = delayOverride !== undefined ? delayOverride : this._currentInterval();
        this._timerId = setTimeout(() => this._poll(), delay);
    }

    _currentInterval() {
        // Exponential backoff on errors, capped at _maxInterval
        if (this._consecutiveErrors > 0) {
            return Math.min(this.interval * Math.pow(2, this._consecutiveErrors), this._maxInterval);
        }
        return this.interval;
    }

    async _poll() {
        if (this._destroyed || this._paused) return;

        try {
            const resp = await fetch(this.endpoint, {
                method: 'GET',
                credentials: 'same-origin',
                headers: { 'X-Requested-With': 'XMLHttpRequest' },
            });

            if (!resp.ok) throw new Error(`HTTP ${resp.status}`);

            const data = await resp.json();
            if (!data.success) throw new Error(data.message || 'Check failed');

            this._consecutiveErrors = 0;
            this._processTimestamps(data.timestamps);

        } catch (err) {
            this._consecutiveErrors++;
            if (this._consecutiveErrors <= 3) {
                console.warn('[RealtimeManager] Poll error:', err.message,
                    `(retry in ${this._currentInterval() / 1000}s)`);
            }
        }

        this._scheduleNext();
    }

    _processTimestamps(newTimestamps) {
        const changedCategories = [];

        for (const [category, timestamp] of Object.entries(newTimestamps)) {
            const prev = this._lastTimestamps[category];
            if (prev && prev !== timestamp) {
                changedCategories.push(category);
            }
            this._lastTimestamps[category] = timestamp;
        }

        // Don't fire events on the very first check (initial baseline)
        if (this._isFirstCheck) {
            this._isFirstCheck = false;
            return;
        }

        // Fire category-specific listeners
        for (const cat of changedCategories) {
            const listeners = this._listeners[cat] || [];
            listeners.forEach(fn => {
                try { fn(cat); } catch (e) { console.error('[RealtimeManager] Listener error:', e); }
            });

            // Fire wildcard listeners
            const wildcards = this._listeners['*'] || [];
            wildcards.forEach(fn => {
                try { fn(cat); } catch (e) { console.error('[RealtimeManager] Wildcard listener error:', e); }
            });
        }
    }
}

// Export globally for use in inline scripts
window.RealtimeManager = RealtimeManager;
