<?php
/**
 * Custom 404 – Page Not Found
 * Shown for any request that doesn't match a real file or route.
 */

header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
header('Pragma: no-cache');
http_response_code(404);

// Determine where to redirect based on session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
$is_logged_in = !empty($_SESSION['user_id']);
$redirect_url = $is_logged_in
    ? '/iedevelopment/public/dashboard.php'
    : '/iedevelopment/modules/auth/login.php';
$redirect_label = $is_logged_in ? 'Go to Dashboard' : 'Go to Login';
$redirect_icon  = $is_logged_in ? 'fa-house' : 'fa-right-to-bracket';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>404 Not Found - NIA UPRIIS</title>
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
            --color-warning:  #d97706;
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

        .error-card {
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

        .error-icon-wrapper {
            width: 88px;
            height: 88px;
            border-radius: 50%;
            background: linear-gradient(135deg, #fef3c7, #fde68a);
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 24px;
        }

        .error-icon-wrapper i {
            font-size: 36px;
            color: var(--color-warning);
        }

        .error-title {
            font-family: var(--font-display);
            font-size: 26px;
            font-weight: 700;
            color: var(--text-dark);
            margin-bottom: 8px;
        }

        .error-code {
            font-size: 13px;
            font-weight: 600;
            color: var(--color-warning);
            letter-spacing: 1px;
            margin-bottom: 16px;
        }

        .error-message {
            font-size: 15px;
            color: var(--text-medium);
            line-height: 1.6;
            margin-bottom: 32px;
        }

        .error-divider {
            height: 1px;
            background: #e5e7eb;
            margin: 0 -8px 24px;
        }

        .error-btn {
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

        .error-btn:hover {
            background: var(--primary-dark);
            transform: translateY(-1px);
        }

        .error-countdown {
            margin-top: 20px;
            font-size: 13px;
            color: var(--text-light);
        }

        .error-countdown span {
            font-weight: 700;
            color: var(--primary-green);
        }

        .error-footer {
            margin-top: 28px;
            font-size: 12px;
            color: var(--text-light);
        }

        .error-footer i {
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
    <div class="error-card">
        <div class="error-icon-wrapper">
            <i class="fas fa-compass"></i>
        </div>

        <p class="error-code">ERROR 404 &mdash; NOT FOUND</p>
        <h1 class="error-title">Page Not Found</h1>
        <p class="error-message">
            The page you're looking for doesn't exist or has been moved.<br>
            You'll be redirected automatically.
        </p>

        <div class="error-divider"></div>

        <a href="<?php echo htmlspecialchars($redirect_url); ?>" class="error-btn" id="redirectBtn">
            <i class="fas <?php echo $redirect_icon; ?>"></i>
            <?php echo $redirect_label; ?>
        </a>

        <p class="error-countdown">
            Redirecting in <span id="countdown">5</span> seconds&hellip;
        </p>

        <div class="progress-track"><div class="bar" id="progressBar"></div></div>

        <p class="error-footer">
            <i class="fas fa-lock"></i>
            NIA UPRIIS &mdash; ICT Equipment Inventory System
        </p>
    </div>

    <script>
        var seconds = 5;
        var bar = document.getElementById('progressBar');
        var countEl = document.getElementById('countdown');
        var target = <?php echo json_encode($redirect_url); ?>;

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
        <meta http-equiv="refresh" content="5;url=<?php echo htmlspecialchars($redirect_url); ?>">
    </noscript>
</body>
</html>
