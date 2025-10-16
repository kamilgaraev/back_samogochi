<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Подтверждение Email</title>
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
        .button-wrapper {
            text-align: center;
            margin: 30px 0;
        }
        .button {
            display: inline-block;
            padding: 12px 30px;
            background: #455a64;
            color: #ffffff !important;
            text-decoration: none;
            border-radius: 4px;
            font-weight: 500;
            font-size: 15px;
        }
        .button:hover {
            background: #37474f;
        }
        .info-block {
            background: #f5f5f5;
            padding: 20px;
            margin-top: 25px;
            border-radius: 4px;
        }
        .info-block p {
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
                <h2>Здравствуйте, {{ $userName ?? 'Друг' }}!</h2>
                <p>Благодарим вас за регистрацию в Самогочи!</p>
                <p>Для подтверждения вашего email адреса нажмите на кнопку ниже:</p>
            </div>
            
            <div class="button-wrapper">
                <a href="{{ $verificationUrl }}" class="button">Подтвердить Email</a>
            </div>
            
            <div class="info-block">
                <p><strong>Важно:</strong> Эта ссылка действительна в течение 24 часов.</p>
                <p>Если вы не регистрировались на нашем сайте, просто проигнорируйте это письмо.</p>
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
