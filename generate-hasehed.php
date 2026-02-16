<?php
// Handle AJAX request FIRST
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['generate'])) {
    header('Content-Type: application/json');

    $password = $_POST['password'] ?? '';

    if (strlen($password) < 8) {
        echo json_encode([
            'success' => false,
            'message' => 'Password must be at least 8 characters'
        ]);
        exit;
    }

    $hash = password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]);

    echo json_encode([
        'success' => true,
        'hash' => $hash
    ]);
    exit; // IMPORTANT: stop HTML from loading
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Password Hash Generator - NIA UPRIIS</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #2d7a4f 0%, #1a5435 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        
        .container {
            background: white;
            padding: 40px;
            border-radius: 15px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
            max-width: 600px;
            width: 100%;
        }
        
        h1 {
            color: #2d7a4f;
            margin-bottom: 10px;
            font-size: 28px;
        }
        
        .subtitle {
            color: #666;
            margin-bottom: 30px;
            font-size: 14px;
        }
        
        .form-group {
            margin-bottom: 25px;
        }
        
        label {
            display: block;
            margin-bottom: 8px;
            color: #333;
            font-weight: 600;
            font-size: 14px;
        }
        
        input[type="text"],
        input[type="password"],
        textarea {
            width: 100%;
            padding: 12px 15px;
            border: 2px solid #ddd;
            border-radius: 8px;
            font-size: 14px;
            font-family: 'Courier New', monospace;
            transition: border-color 0.3s;
        }
        
        input:focus,
        textarea:focus {
            outline: none;
            border-color: #2d7a4f;
        }
        
        textarea {
            resize: vertical;
            min-height: 80px;
        }
        
        .btn {
            background: linear-gradient(135deg, #2d7a4f, #3d9b6b);
            color: white;
            padding: 12px 30px;
            border: none;
            border-radius: 8px;
            font-size: 15px;
            font-weight: 600;
            cursor: pointer;
            transition: transform 0.2s;
            width: 100%;
        }
        
        .btn:hover {
            transform: translateY(-2px);
        }
        
        .btn:active {
            transform: translateY(0);
        }
        
        .result {
            background: #f0f9f4;
            border: 2px solid #2d7a4f;
            border-radius: 8px;
            padding: 20px;
            margin-top: 20px;
            display: none;
        }
        
        .result.show {
            display: block;
        }
        
        .result h3 {
            color: #2d7a4f;
            margin-bottom: 15px;
            font-size: 16px;
        }
        
        .hash-output {
            background: white;
            padding: 15px;
            border-radius: 6px;
            font-family: 'Courier New', monospace;
            font-size: 12px;
            word-break: break-all;
            border: 1px solid #2d7a4f;
            margin-bottom: 15px;
        }
        
        .copy-btn {
            background: #2d7a4f;
            color: white;
            padding: 8px 20px;
            border: none;
            border-radius: 6px;
            font-size: 13px;
            cursor: pointer;
            transition: background 0.3s;
        }
        
        .copy-btn:hover {
            background: #1a5435;
        }
        
        .sql-example {
            background: #fff;
            padding: 15px;
            border-radius: 6px;
            border: 1px solid #ddd;
            margin-top: 15px;
        }
        
        .sql-example h4 {
            color: #333;
            margin-bottom: 10px;
            font-size: 14px;
        }
        
        .sql-code {
            background: #2d2d2d;
            color: #f8f8f2;
            padding: 15px;
            border-radius: 6px;
            font-family: 'Courier New', monospace;
            font-size: 12px;
            overflow-x: auto;
        }
        
        .warning {
            background: #fff3cd;
            border: 1px solid #ffc107;
            color: #856404;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
            font-size: 13px;
        }
        
        .warning strong {
            display: block;
            margin-bottom: 5px;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>🔐 Password Hash Generator</h1>
        <p class="subtitle">Generate secure BCrypt password hashes for NIA UPRIIS accounts</p>
        
        <div class="warning">
            <strong>⚠️ Security Notice:</strong>
            Delete this file after generating your password hashes. Never use this in production!
        </div>
        
        <form id="hashForm">
            <div class="form-group">
                <label for="password">Enter Password:</label>
                <input type="password" id="password" required placeholder="Enter password to hash">
            </div>
            
            <div class="form-group">
                <label>
                    <input type="checkbox" id="showPassword" style="width: auto; margin-right: 8px;">
                    Show password
                </label>
            </div>
            
            <button type="submit" class="btn">Generate Hash</button>
        </form>
        
        <div class="result" id="result">
            <h3>✅ Password Hash Generated</h3>
            
            <div class="hash-output" id="hashOutput"></div>
            
            <button class="copy-btn" onclick="copyHash()">📋 Copy Hash</button>
            
            <div class="sql-example">
                <h4>SQL Update Query:</h4>
                <div class="sql-code" id="sqlQuery"></div>
            </div>
        </div>
    </div>

    <script>
        let generatedHash = '';
        
        document.getElementById('showPassword').addEventListener('change', function() {
            const passwordInput = document.getElementById('password');
            passwordInput.type = this.checked ? 'text' : 'password';
        });
        
        document.getElementById('hashForm').addEventListener('submit', function(e) {
            e.preventDefault();
            
            const password = document.getElementById('password').value;
            
            if (password.length < 8) {
                alert('Password must be at least 8 characters long!');
                return;
            }
            
            // Send to this same file to generate hash
            const formData = new FormData();
            formData.append('password', password);
            formData.append('generate', '1');
            
            fetch(window.location.href, {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    generatedHash = data.hash;
                    document.getElementById('hashOutput').textContent = generatedHash;
                    
                    const sqlQuery = `UPDATE tbl_accounts 
SET password = '${generatedHash}' 
WHERE id = 1;`;
                    
                    document.getElementById('sqlQuery').textContent = sqlQuery;
                    document.getElementById('result').classList.add('show');
                } else {
                    alert('Error generating hash: ' + data.message);
                }
            })
            .catch(error => {
                alert('Error: ' + error.message);
            });
        });
        
        function copyHash() {
            navigator.clipboard.writeText(generatedHash).then(function() {
                const btn = event.target;
                const originalText = btn.textContent;
                btn.textContent = '✅ Copied!';
                
                setTimeout(function() {
                    btn.textContent = originalText;
                }, 2000);
            }).catch(function(err) {
                alert('Failed to copy: ' + err);
            });
        }
    </script>
</body>
</html>