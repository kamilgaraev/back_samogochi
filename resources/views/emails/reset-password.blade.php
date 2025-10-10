<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Восстановление пароля</title>
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;
            line-height: 1.6;
            color: #1f2937;
            max-width: 600px;
            margin: 0 auto;
            padding: 40px 20px;
            background: #f9fafb;
        }
        .container {
            background: #ffffff;
            border-radius: 12px;
            padding: 50px 40px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.07);
        }
        @media only screen and (max-width: 600px) {
            .container {
                padding: 30px 20px;
            }
        }
        .header {
            text-align: center;
            margin-bottom: 35px;
        }
        .logo {
            font-size: 36px;
            font-weight: bold;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            margin-bottom: 10px;
        }
        .content {
            margin: 30px 0;
        }
        .content h2 {
            color: #111827;
            font-size: 24px;
            margin-bottom: 15px;
        }
        .content p {
            color: #4b5563;
            font-size: 16px;
            margin: 12px 0;
        }
        .password-box {
            text-align: center;
            background: linear-gradient(135deg, #f3f4f6 0%, #e5e7eb 100%);
            padding: 25px;
            border-radius: 10px;
            margin: 25px 0;
            border: 2px solid #667eea;
        }
        .password-box code {
            font-size: 20px;
            font-weight: bold;
            color: #667eea;
            letter-spacing: 3px;
            font-family: 'Courier New', monospace;
        }
        .footer {
            margin-top: 45px;
            padding-top: 25px;
            border-top: 2px solid #e5e7eb;
            font-size: 14px;
            color: #6b7280;
            text-align: center;
        }
        .note {
            background: linear-gradient(135deg, #fef3c7 0%, #fde68a 100%);
            padding: 18px;
            border-radius: 8px;
            margin-top: 25px;
            font-size: 14px;
            border-left: 4px solid #f59e0b;
        }
        .note p {
            margin: 8px 0;
            color: #78350f;
        }
        .note strong {
            color: #92400e;
        }
        .security-note {
            background: linear-gradient(135deg, #dbeafe 0%, #bfdbfe 100%);
            padding: 18px;
            border-radius: 8px;
            margin-top: 20px;
            font-size: 14px;
            border-left: 4px solid #3b82f6;
        }
        .security-note p {
            margin: 8px 0;
            color: #1e3a8a;
        }
        .security-note strong {
            color: #1e40af;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <div class="logo">🎮 Самогочи</div>
        </div>
        
        <div class="content">
            <h2>Восстановление пароля</h2>
            <p>Здравствуйте, {{ $userName ?? 'Друг' }}!</p>
            <p>Вы запросили восстановление пароля.</p>
            <p><strong>Ваш новый пароль:</strong></p>
            
            <div class="password-box">
                <code>{{ $newPassword }}</code>
            </div>
            
            <div class="note">
                <p><strong>💡 Рекомендация:</strong> Смените пароль после входа в систему на более запоминающийся.</p>
            </div>
            
            <div class="security-note">
                <p><strong>🔒 Безопасность:</strong></p>
                <p>Если вы не запрашивали восстановление пароля, срочно свяжитесь с поддержкой.</p>
            </div>
        </div>
        
        <div class="footer">
            <p>С уважением,<br>Команда СтрессХелп</p>
        </div>
    </div>
</body>
</html>

