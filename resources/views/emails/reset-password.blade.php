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
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
        }
        .container {
            background: #ffffff;
            border-radius: 8px;
            padding: 40px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
        }
        .logo {
            font-size: 32px;
            font-weight: bold;
            color: #6366f1;
            margin-bottom: 10px;
        }
        .content {
            margin: 30px 0;
        }
        .button {
            display: inline-block;
            padding: 12px 30px;
            background: #6366f1;
            color: #ffffff;
            text-decoration: none;
            border-radius: 6px;
            margin: 20px 0;
            font-weight: 500;
        }
        .button:hover {
            background: #4f46e5;
        }
        .footer {
            margin-top: 40px;
            padding-top: 20px;
            border-top: 1px solid #e5e7eb;
            font-size: 14px;
            color: #6b7280;
            text-align: center;
        }
        .note {
            background: #fef3c7;
            padding: 15px;
            border-radius: 6px;
            margin-top: 20px;
            font-size: 14px;
            border-left: 4px solid #f59e0b;
        }
        .security-note {
            background: #f3f4f6;
            padding: 15px;
            border-radius: 6px;
            margin-top: 20px;
            font-size: 14px;
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
            
            <div style="text-align: center; background: #f3f4f6; padding: 20px; border-radius: 6px; margin: 20px 0;">
                <code style="font-size: 18px; font-weight: bold; color: #6366f1; letter-spacing: 2px;">{{ $newPassword }}</code>
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

