<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Завершение игры</title>
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
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            padding: 40px 20px;
            text-align: center;
        }
        .header h1 {
            margin: 0;
            font-size: 28px;
            font-weight: 600;
            color: #ffffff;
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
            font-size: 22px;
            font-weight: 600;
            margin: 0 0 20px 0;
        }
        .content p {
            color: #616161;
            font-size: 15px;
            margin: 15px 0;
            line-height: 1.6;
        }
        .highlight-box {
            background: #f5f5f5;
            padding: 25px;
            margin: 25px 0;
            border-radius: 8px;
            border-left: 4px solid #667eea;
        }
        .highlight-box p {
            margin: 10px 0;
            color: #424242;
            font-size: 15px;
        }
        .stats-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 15px;
            margin: 25px 0;
        }
        .stat-card {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 8px;
            text-align: center;
        }
        .stat-value {
            font-size: 24px;
            font-weight: bold;
            color: #667eea;
            margin: 0;
        }
        .stat-label {
            font-size: 13px;
            color: #757575;
            margin: 5px 0 0 0;
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
        .stay-connected-box {
            background: linear-gradient(135deg, #e3f2fd 0%, #f3e5f5 100%);
            padding: 30px;
            margin: 30px 0;
            border-radius: 8px;
            text-align: center;
        }
        .stay-connected-box h3 {
            color: #1a237e;
            font-size: 20px;
            font-weight: 600;
            margin: 0 0 15px 0;
        }
        .stay-connected-box p {
            color: #424242;
            font-size: 15px;
            margin: 10px 0 20px 0;
            line-height: 1.6;
        }
        .resource-links {
            display: flex;
            justify-content: center;
            gap: 15px;
            flex-wrap: wrap;
            margin-top: 20px;
        }
        .resource-link {
            display: inline-block;
            padding: 12px 25px;
            background: #ffffff;
            color: #667eea !important;
            text-decoration: none;
            border-radius: 6px;
            font-weight: 500;
            font-size: 15px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            transition: transform 0.2s;
        }
        .resource-link:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0,0,0,0.15);
        }
    </style>
</head>
<body>
    <div class="email-wrapper">
        <div class="header">
            <h1>🎮 Самогочи</h1>
        </div>
        
        <div class="container">
            <div class="content">
                <h2>Спасибо за участие в игре!</h2>
                <p>Здравствуйте, {{ $userName ?? 'Друг' }}!</p>
                <p>Наша игра завершилась. Это был удивительный путь длиной в 180 дней, и мы благодарны, что вы были частью этого опыта.</p>
            </div>
            
            <div class="highlight-box">
                <p><strong>Это было важно</strong></p>
                <p>Самогочи — это не просто игра. Это инструмент для работы с вашим психологическим состоянием, помогающий лучше понимать себя и справляться со стрессом.</p>
            </div>

            @if(isset($stats))
            <div class="stats-grid">
                <div class="stat-card">
                    <p class="stat-value">{{ $stats['days_played'] ?? 0 }}</p>
                    <p class="stat-label">дней в игре</p>
                </div>
                <div class="stat-card">
                    <p class="stat-value">{{ $stats['total_actions'] ?? 0 }}</p>
                    <p class="stat-label">действий выполнено</p>
                </div>
            </div>
            @endif
            
            <div class="content">
                <p>Мы надеемся, что игра помогла вам научиться лучше справляться с трудностями и обрести больше контроля над своим состоянием.</p>
            </div>
            
            <div class="stay-connected-box">
                <h3>Мы останемся с вами!</h3>
                <p>Хотя игра завершена, наша работа продолжается. Следите за полезными материалами, статьями и новостями о ментальном здоровье на нашем сайте и в Telegram-канале.</p>
                <div class="resource-links">
                    <a href="https://stresshelp.ru" class="resource-link">📖 Читать статьи</a>
                    <a href="https://t.me/trevogabutton" class="resource-link">💬 Подписаться в Telegram</a>
                </div>
            </div>
            
            <div class="content">
                <p><strong>Продолжайте заботиться о себе!</strong></p>
            </div>
            
            <div style="margin-top: 30px; padding-top: 20px; border-top: 1px solid #e0e0e0; text-align: center;">
                <p style="margin: 10px 0; font-size: 14px; color: #757575;">С уважением и благодарностью,<br>команда СтрессХелп</p>
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

