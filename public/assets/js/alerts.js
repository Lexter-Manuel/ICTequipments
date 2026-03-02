/**
 * alerts.js — NIA UPRIIS ICT Inventory System
 * Self-initializing Alert & Confirmation System
 *
 * Include this script once in the shell page (dashboard.php).
 * It automatically injects its own DOM elements — no separate PHP file needed.
 *
 * ── Toast Notifications ───────────────────────────────────
 *   Alerts.success(message, title?, duration?)
 *   Alerts.error(message, title?, duration?)
 *   Alerts.warning(message, title?, duration?)
 *   Alerts.info(message, title?, duration?)
 *
 * ── Confirmation Dialogs ──────────────────────────────────
 *   Alerts.confirmDelete(itemLabel, onConfirm)
 *   Alerts.confirmAction({ title, message, confirmText, type, icon, onConfirm })
 *
 * ── Inline Form Alerts ───────────────────────────────────
 *   Alerts.formError(message, containerSelector?)
 *   Alerts.formSuccess(message, containerSelector?)
 *   Alerts.formWarning(message, containerSelector?)
 *   Alerts.formInfo(message, containerSelector?)
 *   Alerts.clearFormAlert(containerSelector?)
 */

const Alerts = (function () {

    /* ─────────────────────────────────────────────────────────
       SELF-INIT: Inject required DOM elements once
    ───────────────────────────────────────────────────────── */
    function _injectDOM() {
        // Toast container
        if (!document.getElementById('toast-container')) {
            const tc = document.createElement('div');
            tc.id = 'toast-container';
            tc.setAttribute('aria-live', 'polite');
            tc.setAttribute('aria-atomic', 'false');
            document.body.appendChild(tc);
        }

        // Confirm modal
        if (!document.getElementById('confirmModal')) {
            const modal = document.createElement('div');
            modal.id = 'confirmModal';
            modal.className = 'nia-modal-overlay';
            modal.setAttribute('role', 'dialog');
            modal.setAttribute('aria-modal', 'true');
            modal.setAttribute('aria-labelledby', 'confirmModalTitle');
            modal.innerHTML =
                '<div class="nia-modal nia-modal--sm">' +
                    '<div class="nia-modal__icon-wrap">' +
                        '<span class="nia-modal__icon" id="confirmModalIcon">' +
                            '<i class="fas fa-trash" id="confirmModalIconGlyph"></i>' +
                        '</span>' +
                    '</div>' +
                    '<div class="nia-modal__body">' +
                        '<h3 class="nia-modal__title" id="confirmModalTitle"></h3>' +
                        '<p class="nia-modal__message" id="confirmModalMessage"></p>' +
                    '</div>' +
                    '<div class="nia-modal__footer">' +
                        '<button type="button" class="nia-btn nia-btn--ghost" id="confirmModalCancel">Cancel</button>' +
                        '<button type="button" class="nia-btn nia-btn--danger" id="confirmModalConfirm">' +
                            '<i class="fas fa-trash" id="confirmBtnIcon"></i>' +
                            '<span id="confirmBtnLabel">Delete</span>' +
                        '</button>' +
                    '</div>' +
                '</div>';
            document.body.appendChild(modal);
        }
    }

    // Run on load
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', _injectDOM);
    } else {
        _injectDOM();
    }


    /* ─────────────────────────────────────────────────────────
       TOAST CONFIG
    ───────────────────────────────────────────────────────── */
    const TOAST_DURATION = 4500;

    const TOAST_CONFIG = {
        success: { icon: 'fa-circle-check',          title: 'Success' },
        error:   { icon: 'fa-circle-xmark',          title: 'Error'   },
        warning: { icon: 'fa-triangle-exclamation',  title: 'Warning' },
        info:    { icon: 'fa-circle-info',            title: 'Info'    },
    };


    /* ─────────────────────────────────────────────────────────
       INTERNAL: SHOW TOAST
    ───────────────────────────────────────────────────────── */
    function _toast(type, message, title, duration) {
        const cfg = TOAST_CONFIG[type];
        if (!cfg) return;
        duration = duration || TOAST_DURATION;

        const container = document.getElementById('toast-container');
        if (!container) return;

        // Build element
        const el = document.createElement('div');
        el.className = 'nia-toast nia-toast--' + type;
        el.setAttribute('role', type === 'error' ? 'alert' : 'status');
        el.innerHTML =
            '<div class="nia-toast__icon"><i class="fas ' + cfg.icon + '"></i></div>' +
            '<div class="nia-toast__content">' +
                '<div class="nia-toast__title">'   + _esc(title || cfg.title) + '</div>' +
                '<div class="nia-toast__message">' + _esc(message)            + '</div>' +
            '</div>' +
            '<button class="nia-toast__close" aria-label="Dismiss"><i class="fas fa-xmark"></i></button>';

        // Unique class for per-toast progress bar duration
        const uid = 'tp-' + Date.now() + '-' + Math.floor(Math.random() * 1000);
        el.classList.add(uid);

        const styleEl = document.createElement('style');
        styleEl.textContent = '.' + uid + '::after { animation-duration: ' + duration + 'ms; }';
        document.head.appendChild(styleEl);

        container.appendChild(el);

        // Pause on hover tracking
        let paused = false;

        // Auto-dismiss
        let autoTimer = setTimeout(function () { _dismissToast(el, styleEl); }, duration);

        el.querySelector('.nia-toast__close').addEventListener('click', function () {
            clearTimeout(autoTimer);
            _dismissToast(el, styleEl);
        });

        // Pause progress bar + timer on hover
        el.addEventListener('mouseenter', function () {
            paused = true;
            clearTimeout(autoTimer);
            el.style.animationPlayState = 'paused';
            el.classList.add('nia-toast--paused');
        });
        el.addEventListener('mouseleave', function () {
            paused = false;
            el.style.animationPlayState = 'running';
            el.classList.remove('nia-toast--paused');
            autoTimer = setTimeout(function () { _dismissToast(el, styleEl); }, 1200);
        });
    }

    function _dismissToast(el, styleEl) {
        if (!el || el.classList.contains('toast-hiding')) return;
        el.classList.add('toast-hiding');
        setTimeout(function () {
            el.remove();
            if (styleEl) styleEl.remove();
        }, 320);
    }


    /* ─────────────────────────────────────────────────────────
       INTERNAL: ESCAPE HTML
    ───────────────────────────────────────────────────────── */
    function _esc(str) {
        const d = document.createElement('div');
        d.appendChild(document.createTextNode(str || ''));
        return d.innerHTML;
    }


    /* ─────────────────────────────────────────────────────────
       CONFIRM MODAL — LAZY DOM REFS
    ───────────────────────────────────────────────────────── */
    let _overlay, _iconEl, _iconGlyph, _titleEl, _messageEl,
        _btnConfirm, _btnCancel, _btnIcon, _btnLabel;
    let _confirmCallback = null;
    let _modalBound = false;

    function _ensureModalRefs() {
        _overlay    = document.getElementById('confirmModal');
        _iconEl     = document.getElementById('confirmModalIcon');
        _iconGlyph  = document.getElementById('confirmModalIconGlyph');
        _titleEl    = document.getElementById('confirmModalTitle');
        _messageEl  = document.getElementById('confirmModalMessage');
        _btnConfirm = document.getElementById('confirmModalConfirm');
        _btnCancel  = document.getElementById('confirmModalCancel');
        _btnIcon    = document.getElementById('confirmBtnIcon');
        _btnLabel   = document.getElementById('confirmBtnLabel');

        if (!_modalBound && _btnConfirm) {
            _btnConfirm.addEventListener('click', function () {
                var cb = _confirmCallback;
                _closeModal();
                if (typeof cb === 'function') cb();
            });
            _btnCancel.addEventListener('click', _closeModal);

            _overlay.addEventListener('click', function (e) {
                if (e.target === _overlay) _closeModal();
            });

            document.addEventListener('keydown', function (e) {
                if (e.key === 'Escape' && _overlay && _overlay.classList.contains('is-open')) {
                    _closeModal();
                }
            });
            _modalBound = true;
        }
    }

    const STYLE_MAP = {
        danger:  { btn: 'nia-btn--danger',  bg: 'var(--color-danger-bg)',  color: 'var(--color-danger)',  icon: 'fa-circle-exclamation'   },
        warning: { btn: 'nia-btn--warning', bg: 'var(--color-warning-bg)', color: 'var(--color-warning)', icon: 'fa-triangle-exclamation' },
        primary: { btn: 'nia-btn--primary', bg: 'var(--primary-xlight)',   color: 'var(--primary-green)', icon: 'fa-circle-check'         },
    };


    /* ─────────────────────────────────────────────────────────
       INTERNAL: OPEN / CLOSE MODAL
    ───────────────────────────────────────────────────────── */
    function _openModal(opts) {
        _ensureModalRefs();
        if (!_overlay) return;

        _titleEl.textContent   = opts.title       || 'Are you sure?';
        _messageEl.textContent = opts.message     || 'This action cannot be undone.';
        _btnLabel.textContent  = opts.confirmText || 'Confirm';

        _iconGlyph.className = 'fas ' + (opts.iconClass || 'fa-circle-exclamation');
        _btnIcon.className   = 'fas ' + (opts.iconClass || 'fa-circle-exclamation');

        _iconEl.style.background = opts.iconBg    || 'var(--color-danger-bg)';
        _iconEl.style.color      = opts.iconColor || 'var(--color-danger)';

        _btnConfirm.className = 'nia-btn ' + (opts.confirmStyle || 'nia-btn--danger');

        _confirmCallback = opts.onConfirm || null;

        _overlay.classList.add('is-open');
        document.body.style.overflow = 'hidden';
        setTimeout(function () { if (_btnCancel) _btnCancel.focus(); }, 60);
    }

    function _closeModal() {
        if (_overlay) _overlay.classList.remove('is-open');
        document.body.style.overflow = '';
        _confirmCallback = null;
    }


    /* ─────────────────────────────────────────────────────────
       INTERNAL: INLINE FORM ALERT
    ───────────────────────────────────────────────────────── */
    const FORM_ALERT_ICONS = {
        error:   'fa-circle-xmark',
        success: 'fa-circle-check',
        warning: 'fa-triangle-exclamation',
        info:    'fa-circle-info',
    };

    function _formAlert(type, message, sel) {
        // Resolve container: explicit selector → open modal body → first modal-body on page
        var container = sel ? document.querySelector(sel) : null;
        if (!container) container = document.querySelector('.modal.show .modal-body')
                                 || document.querySelector('.modal-body');
        if (!container) {
            console.warn('Alerts: no container found for selector "' + sel + '"');
            return;
        }

        // Remove any existing alert in this container first
        var existing = container.querySelector('.nia-form-alert');
        if (existing) existing.remove();

        var el = document.createElement('div');
        el.className = 'nia-form-alert nia-form-alert--' + type;
        el.innerHTML =
            '<i class="fas ' + FORM_ALERT_ICONS[type] + '"></i>' +
            '<span>' + _esc(message) + '</span>' +
            '<button class="nia-form-alert__close" aria-label="Dismiss"><i class="fas fa-xmark"></i></button>';

        el.querySelector('.nia-form-alert__close').addEventListener('click', function () {
            el.remove();
        });

        container.insertBefore(el, container.firstChild);
        el.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
    }


    /* ─────────────────────────────────────────────────────────
       PUBLIC API
    ───────────────────────────────────────────────────────── */
    return {

        // ── Toasts ────────────────────────────────────────────
        success: function (msg, title, dur) { _toast('success', msg, title, dur); },
        error:   function (msg, title, dur) { _toast('error',   msg, title, dur); },
        warning: function (msg, title, dur) { _toast('warning', msg, title, dur); },
        info:    function (msg, title, dur) { _toast('info',    msg, title, dur); },

        // ── Confirm Delete ─────────────────────────────────────
        /**
         * Alerts.confirmDelete('this printer', function() { ... })
         */
        confirmDelete: function (itemLabel, onConfirm) {
            _openModal({
                title:        'Delete ' + (itemLabel || 'this item') + '?',
                message:      'This will permanently remove it from the system. This action cannot be undone.',
                confirmText:  'Yes, Delete',
                confirmStyle: 'nia-btn--danger',
                iconClass:    'fa-trash',
                iconBg:       'var(--color-danger-bg)',
                iconColor:    'var(--color-danger)',
                onConfirm:    onConfirm,
            });
        },

        // ── Confirm Action ─────────────────────────────────────
        /**
         * Alerts.confirmAction({
         *   title:       'Unassign Equipment?',
         *   message:     'This will remove it from Juan Dela Cruz.',
         *   confirmText: 'Yes, Unassign',
         *   type:        'warning',   // 'danger' | 'warning' | 'primary'
         *   icon:        'fa-link-slash',  // optional FontAwesome icon override
         *   onConfirm:   function() { ... }
         * })
         */
        confirmAction: function (opts) {
            var s = STYLE_MAP[opts.type] || STYLE_MAP.warning;
            _openModal({
                title:        opts.title       || 'Are you sure?',
                message:      opts.message     || 'Please confirm this action.',
                confirmText:  opts.confirmText || 'Confirm',
                confirmStyle: s.btn,
                iconClass:    opts.icon        || s.icon,
                iconBg:       s.bg,
                iconColor:    s.color,
                onConfirm:    opts.onConfirm,
            });
        },

        // ── Inline Form Alerts ─────────────────────────────────
        formError:   function (msg, sel) { _formAlert('error',   msg, sel); },
        formSuccess: function (msg, sel) { _formAlert('success', msg, sel); },
        formWarning: function (msg, sel) { _formAlert('warning', msg, sel); },
        formInfo:    function (msg, sel) { _formAlert('info',    msg, sel); },

        /** Remove any existing inline alert from a container */
        clearFormAlert: function (sel) {
            var container = sel ? document.querySelector(sel) : null;
            if (!container) container = document.querySelector('.modal.show .modal-body')
                                     || document.querySelector('.modal-body');
            if (!container) return;
            var existing = container.querySelector('.nia-form-alert');
            if (existing) existing.remove();
        },
    };

})();