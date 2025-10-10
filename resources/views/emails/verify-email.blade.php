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
        .button {
            display: inline-block;
            padding: 14px 40px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: #ffffff !important;
            text-decoration: none;
            border-radius: 8px;
            margin: 25px 0;
            font-weight: 600;
            font-size: 16px;
            box-shadow: 0 4px 12px rgba(102, 126, 234, 0.4);
            transition: all 0.3s ease;
        }
        .button:hover {
            box-shadow: 0 6px 16px rgba(102, 126, 234, 0.5);
            transform: translateY(-2px);
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
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <div class="logo">🎮 Самогочи</div>
        </div>
        
        <div class="content">
            <h2>Здравствуйте, {{ $userName ?? 'Друг' }}!</h2>
            <p>Спасибо за регистрацию в Самогочи!</p>
            <p>Для завершения регистрации, пожалуйста, подтвердите ваш email адрес:</p>
            
            <div style="text-align: center;">
                <a href="{{ $verificationUrl }}" class="button">Подтвердить Email</a>
            </div>
            
            <div class="note">
                <p><strong>Важно:</strong> Эта ссылка действительна в течение 24 часов.</p>
                <p>Если вы не регистрировались на нашем сайте, просто проигнорируйте это письмо.</p>
            </div>
        </div>
        
        <div class="footer">
            <p>С уважением,<br>Команда СтрессХелп</p>
        </div>
    </div>
</body>
</html>

