<?php
/**
 * Unauthorized Access Page
 * Shown when a user tries to access a protected page without a valid session.
 * Auto-redirects to login after a few seconds.
 */

// No-cache so browser always re-fetches this page
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
header('Pragma: no-cache');

$redirect_url = '/ictequipment/modules/auth/login.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Unauthorized - NIA UPRIIS</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Crimson+Pro:wght@400;600;700&family=Work+Sans:wght@400;500;600;700&display=swap');

        :root {
            --primary-green:  #2d7a4f;
            --primary-dark:   #1a5435;
            --primary-light:  #3d9b6b;
            --bg-light:       #f8faf9;
            --text-dark:      #1a3a2e;
            --text-medium:    #4a5f56;
            --text-light:     #7a8f86;
            --color-danger:   #dc2626;
            --font-display:   'Crimson Pro', Georgia, serif;
            --font-body:      'Work Sans', system-ui, sans-serif;
        }

        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            font-family: var(--font-body);
            background: var(--bg-light);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
        }

        .unauth-card {
            text-align: center;
            padding: 48px 40px 40px;
            background: #fff;
            border-radius: 16px;
            box-shadow: 0 8px 40px rgba(0,0,0,0.08);
            max-width: 460px;
            width: 92%;
            animation: slideUp 0.5s ease;
        }

        @keyframes slideUp {
            from { opacity: 0; transform: translateY(24px); }
            to   { opacity: 1; transform: translateY(0); }
        }

        .unauth-icon-wrapper {
            width: 88px;
            height: 88px;
            border-radius: 50%;
            background: linear-gradient(135deg, #fee2e2, #fecaca);
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 24px;
        }

        .unauth-icon-wrapper i {
            font-size: 36px;
            color: var(--color-danger);
        }

        .unauth-title {
            font-family: var(--font-display);
            font-size: 26px;
            font-weight: 700;
            color: var(--text-dark);
            margin-bottom: 8px;
        }

        .unauth-code {
            font-size: 13px;
            font-weight: 600;
            color: var(--color-danger);
            letter-spacing: 1px;
            margin-bottom: 16px;
        }

        .unauth-message {
            font-size: 15px;
            color: var(--text-medium);
            line-height: 1.6;
            margin-bottom: 32px;
        }

        .unauth-divider {
            height: 1px;
            background: #e5e7eb;
            margin: 0 -8px 24px;
        }

        .unauth-btn {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 12px 32px;
            background: var(--primary-green);
            color: #fff;
            border: none;
            border-radius: 8px;
            font-size: 15px;
            font-weight: 600;
            font-family: var(--font-body);
            cursor: pointer;
            text-decoration: none;
            transition: background 0.2s ease, transform 0.15s ease;
        }

        .unauth-btn:hover {
            background: var(--primary-dark);
            transform: translateY(-1px);
        }

        .unauth-countdown {
            margin-top: 20px;
            font-size: 13px;
            color: var(--text-light);
        }

        .unauth-countdown span {
            font-weight: 700;
            color: var(--primary-green);
        }

        .unauth-footer {
            margin-top: 28px;
            font-size: 12px;
            color: var(--text-light);
        }

        .unauth-footer i {
            color: var(--primary-green);
            margin-right: 4px;
        }

        /* Progress bar */
        .progress-track {
            width: 100%;
            height: 3px;
            background: #e5e7eb;
            border-radius: 3px;
            margin-top: 16px;
            overflow: hidden;
        }
        .progress-track .bar {
            height: 100%;
            width: 0%;
            background: linear-gradient(90deg, var(--primary-green), var(--primary-light));
            border-radius: 3px;
            transition: width 1s linear;
        }
    </style>
</head>
<body>
    <div class="unauth-card">
        <div class="unauth-icon-wrapper">
            <i class="fas fa-shield-halved"></i>
        </div>

        <p class="unauth-code">ERROR 401 &mdash; UNAUTHORIZED</p>
        <h1 class="unauth-title">Access Denied</h1>
        <p class="unauth-message">
            Your session has expired or you are not logged in.<br>
            Please sign in to access the system.
        </p>

        <div class="unauth-divider"></div>

        <a href="<?php echo $redirect_url; ?>" class="unauth-btn" id="loginBtn">
            <i class="fas fa-right-to-bracket"></i>
            Go to Login
        </a>

        <p class="unauth-countdown">
            Redirecting in <span id="countdown">5</span> seconds&hellip;
        </p>

        <div class="progress-track"><div class="bar" id="progressBar"></div></div>

        <p class="unauth-footer">
            <i class="fas fa-lock"></i>
            NIA UPRIIS &mdash; ICT Equipment Inventory System
        </p>
    </div>

    <script>
        // Clear any leftover SPA state
        sessionStorage.removeItem('nia-active-page');

        var seconds = 5;
        var bar = document.getElementById('progressBar');
        var countEl = document.getElementById('countdown');
        var target = <?php echo json_encode($redirect_url); ?>;

        // Animate progress bar
        requestAnimationFrame(function() {
            bar.style.width = '100%';
            bar.style.transition = 'width ' + seconds + 's linear';
        });

        var timer = setInterval(function() {
            seconds--;
            if (countEl) countEl.textContent = seconds;
            if (seconds <= 0) {
                clearInterval(timer);
                window.location.replace(target);
            }
        }, 1000);
    </script>

    <noscript>
        <meta http-equiv="refresh" content="3;url=<?php echo htmlspecialchars($redirect_url); ?>">
    </noscript>
</body>
</html>
