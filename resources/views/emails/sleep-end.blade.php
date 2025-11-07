<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Время возвращаться в игру!</title>
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
            margin: 25px 0;
            border-radius: 4px;
            border-left: 4px solid #455a64;
        }
        .info-block p {
            margin: 10px 0;
            color: #616161;
            font-size: 14px;
        }
        .highlight-box {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            padding: 25px;
            margin: 25px 0;
            border-radius: 8px;
            text-align: center;
        }
        .highlight-box h3 {
            color: #ffffff;
            font-size: 18px;
            font-weight: 600;
            margin: 0 0 10px 0;
        }
        .highlight-box p {
            color: #f0f0f0;
            font-size: 15px;
            margin: 5px 0;
            line-height: 1.6;
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
                <h2>Доброе утро, {{ $userName ?? 'Друг' }}! ☀️</h2>
                <p>Ваш персонаж в Самогочи выспался и полон энергии!</p>
                <p>Пока вы отдыхали, мир игры ждал вашего возвращения. Самое время продолжить свой путь к гармонии и балансу.</p>
            </div>
            
            <div class="highlight-box">
                <h3>🌟 Вас ждут новые приключения!</h3>
                <p>Готовы снова погрузиться в увлекательные ситуации и принимать важные решения?</p>
            </div>
            
            <div class="button-wrapper">
                <a href="{{ $gameUrl ?? 'https://game.stresshelp.ru' }}" class="button">Вернуться в игру</a>
            </div>
            
            <div class="info-block">
                <p><strong>💡 Что вас ждет:</strong></p>
                <p>• Новые игровые ситуации для прохождения</p>
                <p>• Возможность улучшить показатели вашего персонажа</p>
                <p>• Интересные решения, влияющие на развитие истории</p>
            </div>
            
            <div class="content">
                <p>Помните: регулярные сеансы игры помогают лучше понимать себя и развивать навыки управления стрессом.</p>
                <p><strong>Не упустите момент — вернитесь в игру прямо сейчас!</strong></p>
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

