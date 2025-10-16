<?php

namespace Database\Seeders;

use App\Models\MicroAction;
use Illuminate\Database\Seeder;

class MicroActionSeederNew extends Seeder
{
    public function run(): void
    {
        $microActions = [
            ['name' => 'Утренний контрастный душ', 'description' => 'Чередование теплой и холодной воды для бодрости', 'category' => 'exercise', 'energy_reward' => 25, 'experience_reward' => 10, 'cooldown_minutes' => 240, 'unlock_level' => 1, 'position' => 'phone'],
            ['name' => 'Планирование дня в блокноте', 'description' => 'Составить список приоритетных задач на сегодня', 'category' => 'creativity', 'energy_reward' => 15, 'experience_reward' => 8, 'cooldown_minutes' => 1440, 'unlock_level' => 1, 'position' => 'desktop'],
            ['name' => 'Послушать подкаст о саморазвитии', 'description' => '20 минут образовательного контента', 'category' => 'relaxation', 'energy_reward' => 18, 'experience_reward' => 12, 'cooldown_minutes' => 180, 'unlock_level' => 1, 'position' => 'speaker'],
            ['name' => 'Приготовить смузи из свежих фруктов', 'description' => 'Полезный витаминный напиток своими руками', 'category' => 'exercise', 'energy_reward' => 20, 'experience_reward' => 10, 'cooldown_minutes' => 360, 'unlock_level' => 1, 'position' => 'kitchen'],
            ['name' => 'Прочитать главу книги', 'description' => '15-20 минут чтения художественной или научной литературы', 'category' => 'creativity', 'energy_reward' => 12, 'experience_reward' => 15, 'cooldown_minutes' => 120, 'unlock_level' => 1, 'position' => 'bookshelf'],
            
            ['name' => 'Техника "5-4-3-2-1" для заземления', 'description' => 'Назвать 5 вещей, которые видишь, 4 - слышишь, 3 - чувствуешь, 2 - пахнут, 1 - на вкус', 'category' => 'relaxation', 'energy_reward' => 14, 'experience_reward' => 8, 'cooldown_minutes' => 45, 'unlock_level' => 1, 'position' => 'phone'],
            ['name' => 'Упражнения для глаз', 'description' => 'Гимнастика для снятия напряжения при работе за компьютером', 'category' => 'exercise', 'energy_reward' => 10, 'experience_reward' => 5, 'cooldown_minutes' => 120, 'unlock_level' => 1, 'position' => 'desktop'],
            ['name' => 'Написать список благодарностей', 'description' => '5 вещей, за которые вы благодарны сегодня', 'category' => 'creativity', 'energy_reward' => 16, 'experience_reward' => 10, 'cooldown_minutes' => 1440, 'unlock_level' => 1, 'position' => 'tablet'],
            ['name' => 'Погладить питомца или посмотреть милые видео', 'description' => 'Терапия милотой для поднятия настроения', 'category' => 'relaxation', 'energy_reward' => 12, 'experience_reward' => 6, 'cooldown_minutes' => 60, 'unlock_level' => 1, 'position' => 'phone'],
            ['name' => 'Позвонить родителям', 'description' => 'Узнать как дела, поговорить по душам', 'category' => 'social', 'energy_reward' => 22, 'experience_reward' => 14, 'cooldown_minutes' => 720, 'unlock_level' => 1, 'position' => 'phone'],
            
            ['name' => 'Массаж кистей рук', 'description' => 'Самомассаж для снятия напряжения', 'category' => 'relaxation', 'energy_reward' => 10, 'experience_reward' => 5, 'cooldown_minutes' => 30, 'unlock_level' => 2, 'position' => 'desktop'],
            ['name' => 'Приседания с прыжком', 'description' => '3 подхода по 10 раз для энергии', 'category' => 'exercise', 'energy_reward' => 28, 'experience_reward' => 15, 'cooldown_minutes' => 180, 'unlock_level' => 2, 'position' => 'phone'],
            ['name' => 'Скетчинг простых объектов', 'description' => 'Быстрые зарисовки предметов вокруг', 'category' => 'creativity', 'energy_reward' => 14, 'experience_reward' => 10, 'cooldown_minutes' => 120, 'unlock_level' => 2, 'position' => 'tablet'],
            ['name' => 'Приготовить травяной чай', 'description' => 'Заварить успокаивающий напиток из трав', 'category' => 'relaxation', 'energy_reward' => 15, 'experience_reward' => 8, 'cooldown_minutes' => 120, 'unlock_level' => 2, 'position' => 'kitchen'],
            ['name' => 'Помедитировать с приложением', 'description' => 'Guided медитация с голосовым сопровождением', 'category' => 'relaxation', 'energy_reward' => 20, 'experience_reward' => 12, 'cooldown_minutes' => 240, 'unlock_level' => 2, 'position' => 'speaker'],
            
            ['name' => 'Написать стихотворение или хайку', 'description' => 'Выразить эмоции в поэтической форме', 'category' => 'creativity', 'energy_reward' => 18, 'experience_reward' => 15, 'cooldown_minutes' => 240, 'unlock_level' => 2, 'position' => 'desktop'],
            ['name' => 'Поучаствовать в онлайн-викторине', 'description' => 'Интеллектуальная игра для развития', 'category' => 'social', 'energy_reward' => 15, 'experience_reward' => 12, 'cooldown_minutes' => 180, 'unlock_level' => 2, 'position' => 'tablet'],
            ['name' => 'Организовать рабочее место', 'description' => 'Навести порядок на столе для продуктивности', 'category' => 'exercise', 'energy_reward' => 12, 'experience_reward' => 8, 'cooldown_minutes' => 480, 'unlock_level' => 2, 'position' => 'desktop'],
            ['name' => 'Посмотреть TED выступление', 'description' => 'Вдохновляющая лекция на 15-20 минут', 'category' => 'creativity', 'energy_reward' => 16, 'experience_reward' => 14, 'cooldown_minutes' => 240, 'unlock_level' => 2, 'position' => 'tv'],
            ['name' => 'Сделать комплимент себе перед зеркалом', 'description' => 'Практика самопринятия и позитива', 'category' => 'social', 'energy_reward' => 14, 'experience_reward' => 10, 'cooldown_minutes' => 1440, 'unlock_level' => 2, 'position' => 'phone'],
            
            ['name' => 'Техника EFT-tapping', 'description' => 'Постукивание по акупунктурным точкам для снятия стресса', 'category' => 'relaxation', 'energy_reward' => 18, 'experience_reward' => 10, 'cooldown_minutes' => 120, 'unlock_level' => 3, 'position' => 'phone'],
            ['name' => 'Планка 60 секунд', 'description' => 'Статическое упражнение для укрепления кора', 'category' => 'exercise', 'energy_reward' => 25, 'experience_reward' => 12, 'cooldown_minutes' => 180, 'unlock_level' => 3, 'position' => 'phone'],
            ['name' => 'Изучить 10 слов на иностранном языке', 'description' => 'Расширение словарного запаса', 'category' => 'creativity', 'energy_reward' => 20, 'experience_reward' => 18, 'cooldown_minutes' => 240, 'unlock_level' => 3, 'position' => 'tablet'],
            ['name' => 'Испечь что-то простое', 'description' => 'Кексы или печенье для терапии выпечкой', 'category' => 'creativity', 'energy_reward' => 22, 'experience_reward' => 15, 'cooldown_minutes' => 480, 'unlock_level' => 3, 'position' => 'kitchen'],
            ['name' => 'Прослушать классическую музыку', 'description' => '30 минут спокойной инструментальной музыки', 'category' => 'relaxation', 'energy_reward' => 16, 'experience_reward' => 10, 'cooldown_minutes' => 180, 'unlock_level' => 3, 'position' => 'speaker'],
            
            ['name' => 'Написать письмо своему будущему Я', 'description' => 'Послание себе через год', 'category' => 'creativity', 'energy_reward' => 20, 'experience_reward' => 16, 'cooldown_minutes' => 2880, 'unlock_level' => 3, 'position' => 'desktop'],
            ['name' => 'Поиграть в настольную игру онлайн', 'description' => 'Социальная активность с друзьями удаленно', 'category' => 'social', 'energy_reward' => 18, 'experience_reward' => 12, 'cooldown_minutes' => 240, 'unlock_level' => 3, 'position' => 'tablet'],
            ['name' => 'Сделать маску для лица', 'description' => 'Домашний уход за кожей', 'category' => 'relaxation', 'energy_reward' => 14, 'experience_reward' => 8, 'cooldown_minutes' => 360, 'unlock_level' => 3, 'position' => 'phone'],
            ['name' => 'Посмотреть документальный фильм', 'description' => 'Образовательный контент на 40-60 минут', 'category' => 'creativity', 'energy_reward' => 18, 'experience_reward' => 20, 'cooldown_minutes' => 480, 'unlock_level' => 3, 'position' => 'tv'],
            ['name' => 'Помочь незнакомцу онлайн', 'description' => 'Ответить на вопрос на форуме или в соцсетях', 'category' => 'social', 'energy_reward' => 16, 'experience_reward' => 14, 'cooldown_minutes' => 240, 'unlock_level' => 3, 'position' => 'desktop'],
            
            ['name' => 'Ведение трекера привычек', 'description' => 'Отметить выполненные привычки за день', 'category' => 'creativity', 'energy_reward' => 12, 'experience_reward' => 8, 'cooldown_minutes' => 1440, 'unlock_level' => 4, 'position' => 'phone'],
            ['name' => 'Попробовать новый рецепт', 'description' => 'Приготовить блюдо, которое никогда не готовили', 'category' => 'creativity', 'energy_reward' => 24, 'experience_reward' => 18, 'cooldown_minutes' => 480, 'unlock_level' => 4, 'position' => 'kitchen'],
            ['name' => 'Прыжки на скакалке', 'description' => '5 минут кардио для бодрости', 'category' => 'exercise', 'energy_reward' => 30, 'experience_reward' => 14, 'cooldown_minutes' => 240, 'unlock_level' => 4, 'position' => 'phone'],
            ['name' => 'Звуковая ванна с поющими чашами', 'description' => 'Прослушивание целительных звуковых частот', 'category' => 'relaxation', 'energy_reward' => 22, 'experience_reward' => 12, 'cooldown_minutes' => 240, 'unlock_level' => 4, 'position' => 'speaker'],
            ['name' => 'Создать мудборд желаний', 'description' => 'Визуализация целей через коллаж изображений', 'category' => 'creativity', 'energy_reward' => 20, 'experience_reward' => 16, 'cooldown_minutes' => 360, 'unlock_level' => 4, 'position' => 'tablet'],
            
            ['name' => 'Пересмотреть любимое кино детства', 'description' => 'Ностальгическая терапия и приятные воспоминания', 'category' => 'relaxation', 'energy_reward' => 18, 'experience_reward' => 10, 'cooldown_minutes' => 1440, 'unlock_level' => 4, 'position' => 'tv'],
            ['name' => 'Написать отзыв на книгу', 'description' => 'Поделиться впечатлениями о прочитанном', 'category' => 'social', 'energy_reward' => 14, 'experience_reward' => 12, 'cooldown_minutes' => 480, 'unlock_level' => 4, 'position' => 'desktop'],
            ['name' => 'Упражнения с эспандером', 'description' => '15 минут силовых упражнений для рук', 'category' => 'exercise', 'energy_reward' => 26, 'experience_reward' => 14, 'cooldown_minutes' => 240, 'unlock_level' => 4, 'position' => 'phone'],
            ['name' => 'Послушать аудиокнигу', 'description' => 'Глава художественного произведения', 'category' => 'relaxation', 'energy_reward' => 16, 'experience_reward' => 14, 'cooldown_minutes' => 240, 'unlock_level' => 4, 'position' => 'speaker'],
            ['name' => 'Поделиться достижением в соцсетях', 'description' => 'Отпраздновать свой успех публично', 'category' => 'social', 'energy_reward' => 18, 'experience_reward' => 10, 'cooldown_minutes' => 720, 'unlock_level' => 4, 'position' => 'phone'],
            
            ['name' => 'Цифровой детокс на 30 минут', 'description' => 'Отключить все гаджеты и побыть в тишине', 'category' => 'relaxation', 'energy_reward' => 25, 'experience_reward' => 15, 'cooldown_minutes' => 480, 'unlock_level' => 5, 'position' => 'phone'],
            ['name' => 'Отжимания с коленей', 'description' => '3 подхода по 8-10 раз', 'category' => 'exercise', 'energy_reward' => 24, 'experience_reward' => 12, 'cooldown_minutes' => 180, 'unlock_level' => 5, 'position' => 'phone'],
            ['name' => 'Каллиграфия или леттеринг', 'description' => 'Практика красивого письма для расслабления', 'category' => 'creativity', 'energy_reward' => 16, 'experience_reward' => 12, 'cooldown_minutes' => 240, 'unlock_level' => 5, 'position' => 'desktop'],
            ['name' => 'Приготовить ферментированный продукт', 'description' => 'Начать процесс квашения или брожения', 'category' => 'creativity', 'energy_reward' => 20, 'experience_reward' => 16, 'cooldown_minutes' => 2880, 'unlock_level' => 5, 'position' => 'kitchen'],
            ['name' => 'Послушать биографию успешного человека', 'description' => 'Вдохновляющая история жизни', 'category' => 'creativity', 'energy_reward' => 18, 'experience_reward' => 16, 'cooldown_minutes' => 360, 'unlock_level' => 5, 'position' => 'speaker'],
            
            ['name' => 'Создать список антицелей', 'description' => 'Написать, чего вы точно НЕ хотите в жизни', 'category' => 'creativity', 'energy_reward' => 16, 'experience_reward' => 14, 'cooldown_minutes' => 1440, 'unlock_level' => 5, 'position' => 'tablet'],
            ['name' => 'Марафон короткометражек', 'description' => 'Посмотреть 3-4 короткометражных фильма', 'category' => 'relaxation', 'energy_reward' => 20, 'experience_reward' => 16, 'cooldown_minutes' => 480, 'unlock_level' => 5, 'position' => 'tv'],
            ['name' => 'Организовать виртуальную встречу с друзьями', 'description' => 'Видеозвонок с несколькими людьми', 'category' => 'social', 'energy_reward' => 22, 'experience_reward' => 18, 'cooldown_minutes' => 720, 'unlock_level' => 5, 'position' => 'desktop'],
            ['name' => 'Вакуумное дыхание для живота', 'description' => 'Упражнение для укрепления пресса', 'category' => 'exercise', 'energy_reward' => 18, 'experience_reward' => 10, 'cooldown_minutes' => 240, 'unlock_level' => 5, 'position' => 'phone'],
            ['name' => 'Разобрать книжную полку', 'description' => 'Навести порядок в библиотеке, отложить ненужное', 'category' => 'exercise', 'energy_reward' => 16, 'experience_reward' => 10, 'cooldown_minutes' => 2880, 'unlock_level' => 5, 'position' => 'bookshelf'],
            
            ['name' => 'Сделать коллаж из старых фото', 'description' => 'Творческая работа с воспоминаниями', 'category' => 'creativity', 'energy_reward' => 18, 'experience_reward' => 14, 'cooldown_minutes' => 480, 'unlock_level' => 6, 'position' => 'tablet'],
            ['name' => 'Берпи - 3 подхода по 5', 'description' => 'Интенсивное комплексное упражнение', 'category' => 'exercise', 'energy_reward' => 35, 'experience_reward' => 18, 'cooldown_minutes' => 240, 'unlock_level' => 6, 'position' => 'phone'],
            ['name' => 'Практика loving-kindness медитации', 'description' => 'Медитация любящей доброты к себе и другим', 'category' => 'relaxation', 'energy_reward' => 22, 'experience_reward' => 16, 'cooldown_minutes' => 240, 'unlock_level' => 6, 'position' => 'speaker'],
            ['name' => 'Заморозить фрукты для смузи', 'description' => 'Подготовка полезных заготовок', 'category' => 'creativity', 'energy_reward' => 14, 'experience_reward' => 8, 'cooldown_minutes' => 1440, 'unlock_level' => 6, 'position' => 'kitchen'],
            ['name' => 'Составить список книг к прочтению', 'description' => 'Планирование литературного развития', 'category' => 'creativity', 'energy_reward' => 12, 'experience_reward' => 10, 'cooldown_minutes' => 720, 'unlock_level' => 6, 'position' => 'bookshelf'],
            
            ['name' => 'Посмотреть старый сериал ностальгии', 'description' => 'Несколько серий из детства/юности', 'category' => 'relaxation', 'energy_reward' => 20, 'experience_reward' => 12, 'cooldown_minutes' => 1440, 'unlock_level' => 6, 'position' => 'tv'],
            ['name' => 'Написать план на неделю', 'description' => 'Структурировать задачи на 7 дней вперед', 'category' => 'creativity', 'energy_reward' => 18, 'experience_reward' => 14, 'cooldown_minutes' => 10080, 'unlock_level' => 6, 'position' => 'desktop'],
            ['name' => 'Поучаствовать в онлайн-дискуссии', 'description' => 'Обсудить интересную тему в комментариях', 'category' => 'social', 'energy_reward' => 16, 'experience_reward' => 12, 'cooldown_minutes' => 240, 'unlock_level' => 6, 'position' => 'tablet'],
            ['name' => 'Стойка на одной ноге с закрытыми глазами', 'description' => 'Упражнение на баланс и координацию', 'category' => 'exercise', 'energy_reward' => 14, 'experience_reward' => 10, 'cooldown_minutes' => 120, 'unlock_level' => 6, 'position' => 'phone'],
            ['name' => 'Техника автоматического письма', 'description' => '10 минут письма без остановок и правок', 'category' => 'creativity', 'energy_reward' => 16, 'experience_reward' => 14, 'cooldown_minutes' => 240, 'unlock_level' => 6, 'position' => 'desktop'],
            
            ['name' => 'Прослушать бинауральные ритмы', 'description' => 'Звуковая терапия для концентрации', 'category' => 'relaxation', 'energy_reward' => 20, 'experience_reward' => 12, 'cooldown_minutes' => 240, 'unlock_level' => 7, 'position' => 'speaker'],
            ['name' => 'Силовая тренировка с собственным весом', 'description' => '20 минут комплекса упражнений', 'category' => 'exercise', 'energy_reward' => 32, 'experience_reward' => 18, 'cooldown_minutes' => 360, 'unlock_level' => 7, 'position' => 'phone'],
            ['name' => 'Создать интеллект-карту идеи', 'description' => 'Майндмэп для структурирования мыслей', 'category' => 'creativity', 'energy_reward' => 18, 'experience_reward' => 16, 'cooldown_minutes' => 240, 'unlock_level' => 7, 'position' => 'tablet'],
            ['name' => 'Приготовить домашний соус', 'description' => 'Песто, сальса или другой соус с нуля', 'category' => 'creativity', 'energy_reward' => 22, 'experience_reward' => 14, 'cooldown_minutes' => 720, 'unlock_level' => 7, 'position' => 'kitchen'],
            ['name' => 'Послушать стендап комедию', 'description' => 'Смех - лучшее лекарство от стресса', 'category' => 'relaxation', 'energy_reward' => 18, 'experience_reward' => 10, 'cooldown_minutes' => 360, 'unlock_level' => 7, 'position' => 'speaker'],
            
            ['name' => 'Написать рецензию на фильм', 'description' => 'Проанализировать просмотренное', 'category' => 'creativity', 'energy_reward' => 16, 'experience_reward' => 14, 'cooldown_minutes' => 480, 'unlock_level' => 7, 'position' => 'desktop'],
            ['name' => 'Организовать фотоархив', 'description' => 'Разложить фото по папкам и датам', 'category' => 'creativity', 'energy_reward' => 14, 'experience_reward' => 10, 'cooldown_minutes' => 720, 'unlock_level' => 7, 'position' => 'tablet'],
            ['name' => 'Присоединиться к онлайн-сообществу по интересам', 'description' => 'Найти единомышленников в интернете', 'category' => 'social', 'energy_reward' => 20, 'experience_reward' => 16, 'cooldown_minutes' => 2880, 'unlock_level' => 7, 'position' => 'desktop'],
            ['name' => 'Посмотреть образовательный Youtube-канал', 'description' => '2-3 видео для расширения кругозора', 'category' => 'creativity', 'energy_reward' => 16, 'experience_reward' => 14, 'cooldown_minutes' => 240, 'unlock_level' => 7, 'position' => 'tv'],
            ['name' => 'Челлендж "Книга за день"', 'description' => 'Прочитать небольшую книгу целиком', 'category' => 'creativity', 'energy_reward' => 30, 'experience_reward' => 25, 'cooldown_minutes' => 1440, 'unlock_level' => 7, 'position' => 'bookshelf'],
            
            ['name' => 'Провести мини-уборку 15 минут', 'description' => 'Быстрая уборка одной зоны', 'category' => 'exercise', 'energy_reward' => 18, 'experience_reward' => 8, 'cooldown_minutes' => 480, 'unlock_level' => 8, 'position' => 'phone'],
            ['name' => 'Техника Помодоро - 1 сессия', 'description' => '25 минут концентрированной работы', 'category' => 'creativity', 'energy_reward' => 20, 'experience_reward' => 12, 'cooldown_minutes' => 120, 'unlock_level' => 8, 'position' => 'desktop'],
            ['name' => 'Приготовить домашнюю пасту', 'description' => 'Сделать тесто для пасты с нуля', 'category' => 'creativity', 'energy_reward' => 26, 'experience_reward' => 18, 'cooldown_minutes' => 1440, 'unlock_level' => 8, 'position' => 'kitchen'],
            ['name' => 'Выполнить HIIT тренировку', 'description' => '10 минут высокоинтенсивной интервальной тренировки', 'category' => 'exercise', 'energy_reward' => 38, 'experience_reward' => 20, 'cooldown_minutes' => 360, 'unlock_level' => 8, 'position' => 'phone'],
            ['name' => 'Послушать ambient музыку для фокуса', 'description' => 'Фоновая музыка для концентрации', 'category' => 'relaxation', 'energy_reward' => 14, 'experience_reward' => 8, 'cooldown_minutes' => 180, 'unlock_level' => 8, 'position' => 'speaker'],
            
            ['name' => 'Создать цифровую иллюстрацию', 'description' => 'Порисовать в графическом редакторе', 'category' => 'creativity', 'energy_reward' => 20, 'experience_reward' => 16, 'cooldown_minutes' => 360, 'unlock_level' => 8, 'position' => 'tablet'],
            ['name' => 'Посмотреть иностранный фильм с субтитрами', 'description' => 'Погружение в другую культуру', 'category' => 'creativity', 'energy_reward' => 18, 'experience_reward' => 16, 'cooldown_minutes' => 1440, 'unlock_level' => 8, 'position' => 'tv'],
            ['name' => 'Написать анонимное ободряющее письмо', 'description' => 'Поддержать незнакомца через сервис писем', 'category' => 'social', 'energy_reward' => 18, 'experience_reward' => 14, 'cooldown_minutes' => 720, 'unlock_level' => 8, 'position' => 'desktop'],
            ['name' => 'Сортировка книг по жанрам', 'description' => 'Систематизация домашней библиотеки', 'category' => 'creativity', 'energy_reward' => 16, 'experience_reward' => 10, 'cooldown_minutes' => 2880, 'unlock_level' => 8, 'position' => 'bookshelf'],
            ['name' => 'Практика gratitude journaling', 'description' => 'Расширенная запись благодарностей с деталями', 'category' => 'creativity', 'energy_reward' => 18, 'experience_reward' => 14, 'cooldown_minutes' => 1440, 'unlock_level' => 8, 'position' => 'tablet'],
            
            ['name' => 'Изучить новый танец по видео', 'description' => 'Разучить простую хореографию', 'category' => 'exercise', 'energy_reward' => 28, 'experience_reward' => 18, 'cooldown_minutes' => 480, 'unlock_level' => 9, 'position' => 'tv'],
            ['name' => 'Создать капсулу времени', 'description' => 'Собрать предметы и записи для будущего', 'category' => 'creativity', 'energy_reward' => 20, 'experience_reward' => 16, 'cooldown_minutes' => 4320, 'unlock_level' => 9, 'position' => 'desktop'],
            ['name' => 'Приготовить домашний хлеб', 'description' => 'Испечь хлеб на закваске или дрожжах', 'category' => 'creativity', 'energy_reward' => 28, 'experience_reward' => 20, 'cooldown_minutes' => 1440, 'unlock_level' => 9, 'position' => 'kitchen'],
            ['name' => 'Ароматерапия с эфирными маслами', 'description' => 'Сеанс с успокаивающими ароматами', 'category' => 'relaxation', 'energy_reward' => 18, 'experience_reward' => 10, 'cooldown_minutes' => 240, 'unlock_level' => 9, 'position' => 'phone'],
            ['name' => 'Послушать джазовую импровизацию', 'description' => 'Сложная музыка для ценителей', 'category' => 'relaxation', 'energy_reward' => 16, 'experience_reward' => 12, 'cooldown_minutes' => 240, 'unlock_level' => 9, 'position' => 'speaker'],
            
            ['name' => 'Написать манифест личных ценностей', 'description' => 'Определить свои принципы и убеждения', 'category' => 'creativity', 'energy_reward' => 22, 'experience_reward' => 18, 'cooldown_minutes' => 2880, 'unlock_level' => 9, 'position' => 'tablet'],
            ['name' => 'Начать читать серию книг', 'description' => 'Погрузиться в многотомную историю', 'category' => 'creativity', 'energy_reward' => 20, 'experience_reward' => 16, 'cooldown_minutes' => 480, 'unlock_level' => 9, 'position' => 'bookshelf'],
            ['name' => 'Провести домашнюю фотосессию', 'description' => 'Творческая съемка себя или предметов', 'category' => 'creativity', 'energy_reward' => 18, 'experience_reward' => 14, 'cooldown_minutes' => 720, 'unlock_level' => 9, 'position' => 'phone'],
            ['name' => 'Посмотреть театральную постановку онлайн', 'description' => 'Трансляция спектакля из театра', 'category' => 'creativity', 'energy_reward' => 22, 'experience_reward' => 18, 'cooldown_minutes' => 1440, 'unlock_level' => 9, 'position' => 'tv'],
            ['name' => 'Модерировать онлайн-встречу сообщества', 'description' => 'Организовать и провести групповое мероприятие', 'category' => 'social', 'energy_reward' => 24, 'experience_reward' => 20, 'cooldown_minutes' => 1440, 'unlock_level' => 9, 'position' => 'desktop'],
            
            ['name' => 'Челлендж "30 дней благодарности"', 'description' => 'Начать марафон ежедневной благодарности', 'category' => 'creativity', 'energy_reward' => 18, 'experience_reward' => 16, 'cooldown_minutes' => 43200, 'unlock_level' => 10, 'position' => 'tablet'],
            ['name' => 'Кроссфит-комплекс для продвинутых', 'description' => '30 минут интенсивных упражнений', 'category' => 'exercise', 'energy_reward' => 45, 'experience_reward' => 25, 'cooldown_minutes' => 480, 'unlock_level' => 10, 'position' => 'phone'],
            ['name' => 'Приготовить блюдо из другой культуры', 'description' => 'Экзотическое блюдо с аутентичными ингредиентами', 'category' => 'creativity', 'energy_reward' => 26, 'experience_reward' => 20, 'cooldown_minutes' => 1440, 'unlock_level' => 10, 'position' => 'kitchen'],
            ['name' => 'Составить личный манифест успеха', 'description' => 'Написать свою философию достижений', 'category' => 'creativity', 'energy_reward' => 24, 'experience_reward' => 20, 'cooldown_minutes' => 2880, 'unlock_level' => 10, 'position' => 'desktop'],
            ['name' => 'Провести домашний кинофестиваль', 'description' => 'Марафон фильмов одного режиссера/жанра', 'category' => 'relaxation', 'energy_reward' => 25, 'experience_reward' => 18, 'cooldown_minutes' => 2880, 'unlock_level' => 10, 'position' => 'tv'],
            
            ['name' => 'Создать подкаст эпизод', 'description' => 'Записать аудио на интересную тему', 'category' => 'creativity', 'energy_reward' => 28, 'experience_reward' => 22, 'cooldown_minutes' => 1440, 'unlock_level' => 10, 'position' => 'speaker'],
            ['name' => 'Написать эссе о жизненном опыте', 'description' => 'Философское размышление на 3-5 страниц', 'category' => 'creativity', 'energy_reward' => 22, 'experience_reward' => 20, 'cooldown_minutes' => 1440, 'unlock_level' => 10, 'position' => 'desktop'],
            ['name' => 'Организовать буккроссинг', 'description' => 'Обменяться книгами с друзьями или сообществом', 'category' => 'social', 'energy_reward' => 20, 'experience_reward' => 18, 'cooldown_minutes' => 2880, 'unlock_level' => 10, 'position' => 'bookshelf'],
            ['name' => 'Создать видеоблог-влог дня', 'description' => 'Снять и смонтировать короткое видео о дне', 'category' => 'creativity', 'energy_reward' => 26, 'experience_reward' => 20, 'cooldown_minutes' => 1440, 'unlock_level' => 10, 'position' => 'tablet'],
            ['name' => 'Менторство онлайн', 'description' => 'Провести сессию помощи новичку в вашей области', 'category' => 'social', 'energy_reward' => 28, 'experience_reward' => 24, 'cooldown_minutes' => 1440, 'unlock_level' => 10, 'position' => 'desktop'],
        ];

        foreach ($microActions as $microActionData) {
            MicroAction::updateOrCreate(
                ['name' => $microActionData['name']],
                array_merge($microActionData, ['is_active' => true])
            );
        }
    }
}

