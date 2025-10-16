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
            color: #333;
            margin: 0;
            padding: 0;
            background: #eceff1;
        }
        .email-wrapper {
            max-width: 600px;
            margin: 40px auto;
            background: #ffffff;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }
        .header {
            background: #eceff1;
            padding: 30px 20px;
            text-align: center;
        }
        .header h1 {
            margin: 0;
            font-size: 22px;
            font-weight: 600;
            color: #37474f;
        }
        .container {
            padding: 40px 50px;
        }
        @media only screen and (max-width: 600px) {
            .container {
                padding: 30px 20px;
            }
        }
        .content h2 {
            color: #212121;
            font-size: 20px;
            font-weight: 600;
            margin: 0 0 20px 0;
        }
        .content p {
            color: #616161;
            font-size: 15px;
            margin: 15px 0;
            line-height: 1.6;
        }
        .password-box {
            background: #f5f5f5;
            padding: 25px;
            margin: 25px 0;
            border-radius: 4px;
            text-align: center;
            border: 2px solid #455a64;
        }
        .password-box code {
            font-size: 20px;
            font-weight: bold;
            color: #455a64;
            letter-spacing: 3px;
            font-family: 'Courier New', monospace;
        }
        .info-block {
            background: #fff3e0;
            padding: 20px;
            margin-top: 25px;
            border-radius: 4px;
            border-left: 4px solid #ff9800;
        }
        .info-block p {
            margin: 10px 0;
            color: #616161;
            font-size: 14px;
        }
        .security-note {
            background: #e3f2fd;
            padding: 20px;
            margin-top: 20px;
            border-radius: 4px;
            border-left: 4px solid #2196f3;
        }
        .security-note p {
            margin: 10px 0;
            color: #616161;
            font-size: 14px;
        }
        .footer {
            background: #eceff1;
            padding: 25px 20px;
            text-align: center;
        }
        .footer p {
            margin: 5px 0;
            font-size: 13px;
            color: #78909c;
        }
        .footer-links {
            margin: 15px 0;
        }
        .footer-links a {
            color: #455a64;
            text-decoration: none;
            font-size: 14px;
            margin: 0 10px;
            font-weight: 500;
        }
        .footer-links a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="email-wrapper">
        <div class="header">
            <h1>Самогочи</h1>
        </div>
        
        <div class="container">
            <div class="content">
                <h2>Восстановление пароля</h2>
                <p>Здравствуйте, {{ $userName ?? 'Друг' }}!</p>
                <p>Вы запросили восстановление пароля.</p>
                <p><strong>Ваш новый пароль:</strong></p>
            </div>
            
            <div class="password-box">
                <code>{{ $newPassword }}</code>
            </div>
            
            <div class="info-block">
                <p><strong>💡 Рекомендация:</strong> Смените пароль после входа в систему на более запоминающийся.</p>
            </div>
            
            <div class="security-note">
                <p><strong>🔒 Безопасность:</strong></p>
                <p>Если вы не запрашивали восстановление пароля, срочно свяжитесь с поддержкой.</p>
            </div>
            
            <div style="margin-top: 30px; padding-top: 20px; border-top: 1px solid #e0e0e0; text-align: center;">
                <p style="margin: 10px 0; font-size: 14px; color: #757575;">С уважением, команда СтрессХелп</p>
            </div>
        </div>
        
        <div class="footer">
            <div class="footer-links">
                <a href="https://stresshelp.ru">stresshelp.ru</a>
                <span style="color: #b0bec5;">|</span>
                <a href="https://t.me/trevogabutton">Telegram</a>
            </div>
            <p>© 2025 Самогочи. Все права защищены.</p>
        </div>
    </div>
</body>
</html>
