<?php

namespace Database\Seeders;

use App\Models\MicroAction;
use Illuminate\Database\Seeder;

class MicroActionSeeder100 extends Seeder
{
    public function run(): void
    {
        $microActions = $this->getMicroActionsData();
        
        foreach ($microActions as $actionData) {
            MicroAction::updateOrCreate(
                ['name' => $actionData['name']],
                $actionData
            );
        }
        
        $this->command->info('Создано/обновлено ' . count($microActions) . ' микро-действий');
    }
    
    private function getMicroActionsData(): array
    {
        return [
            ['name' => 'Проверить почту', 'description' => 'Быстро просмотреть входящие письма', 'category' => 'work', 'energy_reward' => 5, 'experience_reward' => 3, 'cooldown_minutes' => 30, 'unlock_level' => 1, 'position' => 'desktop', 'is_active' => true],
            ['name' => 'Написать заметку', 'description' => 'Записать важную мысль', 'category' => 'work', 'energy_reward' => 8, 'experience_reward' => 5, 'cooldown_minutes' => 20, 'unlock_level' => 1, 'position' => 'desktop', 'is_active' => true],
            ['name' => 'Просмотреть задачи', 'description' => 'Проверить список дел на сегодня', 'category' => 'work', 'energy_reward' => 6, 'experience_reward' => 4, 'cooldown_minutes' => 25, 'unlock_level' => 1, 'position' => 'desktop', 'is_active' => true],
            ['name' => 'Обновить документ', 'description' => 'Внести правки в рабочий файл', 'category' => 'work', 'energy_reward' => 10, 'experience_reward' => 8, 'cooldown_minutes' => 40, 'unlock_level' => 3, 'position' => 'desktop', 'is_active' => true],
            ['name' => 'Провести видеозвонок', 'description' => 'Быстрая онлайн-встреча', 'category' => 'social', 'energy_reward' => 12, 'experience_reward' => 10, 'cooldown_minutes' => 60, 'unlock_level' => 5, 'position' => 'desktop', 'is_active' => true],
            ['name' => 'Послушать музыку', 'description' => 'Включить любимый плейлист', 'category' => 'relaxation', 'energy_reward' => 15, 'experience_reward' => 5, 'cooldown_minutes' => 30, 'unlock_level' => 1, 'position' => 'desktop', 'is_active' => true],
            ['name' => 'Посмотреть видео', 'description' => 'Короткий образовательный ролик', 'category' => 'learning', 'energy_reward' => 8, 'experience_reward' => 12, 'cooldown_minutes' => 35, 'unlock_level' => 2, 'position' => 'desktop', 'is_active' => true],

            ['name' => 'Ответить на сообщение', 'description' => 'Написать другу в мессенджере', 'category' => 'social', 'energy_reward' => 7, 'experience_reward' => 4, 'cooldown_minutes' => 15, 'unlock_level' => 1, 'position' => 'phone', 'is_active' => true],
            ['name' => 'Прокрутить ленту', 'description' => 'Посмотреть новости в соцсетях', 'category' => 'relaxation', 'energy_reward' => 5, 'experience_reward' => 2, 'cooldown_minutes' => 20, 'unlock_level' => 1, 'position' => 'phone', 'is_active' => true],
            ['name' => 'Сделать селфи', 'description' => 'Запечатлеть настроение', 'category' => 'creativity', 'energy_reward' => 10, 'experience_reward' => 6, 'cooldown_minutes' => 25, 'unlock_level' => 2, 'position' => 'phone', 'is_active' => true],
            ['name' => 'Позвонить близкому', 'description' => 'Короткий звонок родным', 'category' => 'social', 'energy_reward' => 15, 'experience_reward' => 10, 'cooldown_minutes' => 45, 'unlock_level' => 1, 'position' => 'phone', 'is_active' => true],
            ['name' => 'Прочитать статью', 'description' => 'Изучить интересный материал', 'category' => 'learning', 'energy_reward' => 8, 'experience_reward' => 15, 'cooldown_minutes' => 30, 'unlock_level' => 3, 'position' => 'phone', 'is_active' => true],
            ['name' => 'Установить будильник', 'description' => 'Настроить напоминание', 'category' => 'work', 'energy_reward' => 5, 'experience_reward' => 3, 'cooldown_minutes' => 10, 'unlock_level' => 1, 'position' => 'phone', 'is_active' => true],
            ['name' => 'Послушать подкаст', 'description' => 'Включить образовательный подкаст', 'category' => 'learning', 'energy_reward' => 12, 'experience_reward' => 14, 'cooldown_minutes' => 50, 'unlock_level' => 4, 'position' => 'phone', 'is_active' => true],

            ['name' => 'Порисовать', 'description' => 'Набросать скетч на планшете', 'category' => 'creativity', 'energy_reward' => 15, 'experience_reward' => 12, 'cooldown_minutes' => 40, 'unlock_level' => 3, 'position' => 'tablet', 'is_active' => true],
            ['name' => 'Прочитать книгу', 'description' => 'Несколько страниц любимого романа', 'category' => 'learning', 'energy_reward' => 18, 'experience_reward' => 15, 'cooldown_minutes' => 45, 'unlock_level' => 2, 'position' => 'tablet', 'is_active' => true],
            ['name' => 'Посмотреть рецепты', 'description' => 'Найти идею для ужина', 'category' => 'health', 'energy_reward' => 8, 'experience_reward' => 6, 'cooldown_minutes' => 25, 'unlock_level' => 1, 'position' => 'tablet', 'is_active' => true],
            ['name' => 'Играть в игру', 'description' => 'Расслабляющая мобильная игра', 'category' => 'relaxation', 'energy_reward' => 12, 'experience_reward' => 5, 'cooldown_minutes' => 30, 'unlock_level' => 1, 'position' => 'tablet', 'is_active' => true],
            ['name' => 'Составить план', 'description' => 'Записать цели на неделю', 'category' => 'work', 'energy_reward' => 10, 'experience_reward' => 10, 'cooldown_minutes' => 35, 'unlock_level' => 2, 'position' => 'tablet', 'is_active' => true],
            ['name' => 'Медитировать', 'description' => 'Запустить приложение для медитации', 'category' => 'relaxation', 'energy_reward' => 20, 'experience_reward' => 8, 'cooldown_minutes' => 60, 'unlock_level' => 5, 'position' => 'tablet', 'is_active' => true],
            ['name' => 'Решить судоку', 'description' => 'Тренировка логического мышления', 'category' => 'learning', 'energy_reward' => 10, 'experience_reward' => 12, 'cooldown_minutes' => 30, 'unlock_level' => 3, 'position' => 'tablet', 'is_active' => true],

            ['name' => 'Посмотреть фильм', 'description' => 'Включить интересное кино', 'category' => 'relaxation', 'energy_reward' => 25, 'experience_reward' => 10, 'cooldown_minutes' => 120, 'unlock_level' => 1, 'position' => 'tv', 'is_active' => true],
            ['name' => 'Посмотреть новости', 'description' => 'Узнать о событиях дня', 'category' => 'learning', 'energy_reward' => 8, 'experience_reward' => 8, 'cooldown_minutes' => 30, 'unlock_level' => 1, 'position' => 'tv', 'is_active' => true],
            ['name' => 'Посмотреть сериал', 'description' => 'Продолжить любимый сериал', 'category' => 'relaxation', 'energy_reward' => 20, 'experience_reward' => 8, 'cooldown_minutes' => 60, 'unlock_level' => 2, 'position' => 'tv', 'is_active' => true],
            ['name' => 'Заниматься йогой', 'description' => 'Включить видео с упражнениями', 'category' => 'health', 'energy_reward' => 30, 'experience_reward' => 15, 'cooldown_minutes' => 90, 'unlock_level' => 4, 'position' => 'tv', 'is_active' => true],
            ['name' => 'Посмотреть документалку', 'description' => 'Познавательный фильм', 'category' => 'learning', 'energy_reward' => 15, 'experience_reward' => 20, 'cooldown_minutes' => 75, 'unlock_level' => 3, 'position' => 'tv', 'is_active' => true],
            ['name' => 'Караоке', 'description' => 'Спеть любимую песню', 'category' => 'creativity', 'energy_reward' => 18, 'experience_reward' => 10, 'cooldown_minutes' => 45, 'unlock_level' => 2, 'position' => 'tv', 'is_active' => true],
            ['name' => 'Посмотреть стрим', 'description' => 'Подключиться к трансляции', 'category' => 'social', 'energy_reward' => 12, 'experience_reward' => 7, 'cooldown_minutes' => 40, 'unlock_level' => 1, 'position' => 'tv', 'is_active' => true],

            ['name' => 'Послушать аудиокнигу', 'description' => 'Включить захватывающий роман', 'category' => 'learning', 'energy_reward' => 15, 'experience_reward' => 15, 'cooldown_minutes' => 60, 'unlock_level' => 2, 'position' => 'speaker', 'is_active' => true],
            ['name' => 'Включить радио', 'description' => 'Послушать музыкальную волну', 'category' => 'relaxation', 'energy_reward' => 10, 'experience_reward' => 5, 'cooldown_minutes' => 30, 'unlock_level' => 1, 'position' => 'speaker', 'is_active' => true],
            ['name' => 'Послушать природу', 'description' => 'Звуки дождя и леса', 'category' => 'relaxation', 'energy_reward' => 18, 'experience_reward' => 8, 'cooldown_minutes' => 45, 'unlock_level' => 3, 'position' => 'speaker', 'is_active' => true],
            ['name' => 'Включить лекцию', 'description' => 'Образовательная аудиозапись', 'category' => 'learning', 'energy_reward' => 12, 'experience_reward' => 18, 'cooldown_minutes' => 50, 'unlock_level' => 4, 'position' => 'speaker', 'is_active' => true],
            ['name' => 'Настроить будильник голосом', 'description' => 'Голосовая команда', 'category' => 'work', 'energy_reward' => 5, 'experience_reward' => 3, 'cooldown_minutes' => 10, 'unlock_level' => 1, 'position' => 'speaker', 'is_active' => true],
            ['name' => 'Узнать погоду', 'description' => 'Спросить о прогнозе', 'category' => 'work', 'energy_reward' => 3, 'experience_reward' => 2, 'cooldown_minutes' => 15, 'unlock_level' => 1, 'position' => 'speaker', 'is_active' => true],
            ['name' => 'Послушать комедию', 'description' => 'Аудио стендап', 'category' => 'relaxation', 'energy_reward' => 14, 'experience_reward' => 7, 'cooldown_minutes' => 35, 'unlock_level' => 2, 'position' => 'speaker', 'is_active' => true],

            ['name' => 'Взять книгу с полки', 'description' => 'Достать интересное издание', 'category' => 'learning', 'energy_reward' => 5, 'experience_reward' => 8, 'cooldown_minutes' => 20, 'unlock_level' => 1, 'position' => 'bookshelf', 'is_active' => true],
            ['name' => 'Перечитать любимую главу', 'description' => 'Вернуться к любимому моменту', 'category' => 'relaxation', 'energy_reward' => 15, 'experience_reward' => 10, 'cooldown_minutes' => 40, 'unlock_level' => 2, 'position' => 'bookshelf', 'is_active' => true],
            ['name' => 'Записать цитату', 'description' => 'Выписать мудрую мысль', 'category' => 'creativity', 'energy_reward' => 8, 'experience_reward' => 12, 'cooldown_minutes' => 25, 'unlock_level' => 1, 'position' => 'bookshelf', 'is_active' => true],
            ['name' => 'Организовать книги', 'description' => 'Навести порядок на полке', 'category' => 'work', 'energy_reward' => 12, 'experience_reward' => 8, 'cooldown_minutes' => 35, 'unlock_level' => 3, 'position' => 'bookshelf', 'is_active' => true],
            ['name' => 'Почитать поэзию', 'description' => 'Насладиться стихами', 'category' => 'learning', 'energy_reward' => 14, 'experience_reward' => 15, 'cooldown_minutes' => 30, 'unlock_level' => 2, 'position' => 'bookshelf', 'is_active' => true],
            ['name' => 'Изучить энциклопедию', 'description' => 'Открыть случайную страницу', 'category' => 'learning', 'energy_reward' => 10, 'experience_reward' => 18, 'cooldown_minutes' => 35, 'unlock_level' => 4, 'position' => 'bookshelf', 'is_active' => true],
            ['name' => 'Полистать комиксы', 'description' => 'Легкое чтение с картинками', 'category' => 'relaxation', 'energy_reward' => 12, 'experience_reward' => 6, 'cooldown_minutes' => 25, 'unlock_level' => 1, 'position' => 'bookshelf', 'is_active' => true],

            ['name' => 'Приготовить завтрак', 'description' => 'Сделать полезный завтрак', 'category' => 'health', 'energy_reward' => 20, 'experience_reward' => 10, 'cooldown_minutes' => 45, 'unlock_level' => 1, 'position' => 'kitchen', 'is_active' => true],
            ['name' => 'Заварить чай', 'description' => 'Приготовить ароматный напиток', 'category' => 'health', 'energy_reward' => 12, 'experience_reward' => 5, 'cooldown_minutes' => 20, 'unlock_level' => 1, 'position' => 'kitchen', 'is_active' => true],
            ['name' => 'Приготовить ужин', 'description' => 'Создать кулинарный шедевр', 'category' => 'health', 'energy_reward' => 25, 'experience_reward' => 15, 'cooldown_minutes' => 60, 'unlock_level' => 2, 'position' => 'kitchen', 'is_active' => true],
            ['name' => 'Испечь что-нибудь', 'description' => 'Сладкая выпечка', 'category' => 'creativity', 'energy_reward' => 22, 'experience_reward' => 18, 'cooldown_minutes' => 70, 'unlock_level' => 4, 'position' => 'kitchen', 'is_active' => true],
            ['name' => 'Сделать смузи', 'description' => 'Полезный витаминный коктейль', 'category' => 'health', 'energy_reward' => 18, 'experience_reward' => 8, 'cooldown_minutes' => 25, 'unlock_level' => 2, 'position' => 'kitchen', 'is_active' => true],
            ['name' => 'Помыть посуду', 'description' => 'Навести чистоту', 'category' => 'work', 'energy_reward' => 8, 'experience_reward' => 5, 'cooldown_minutes' => 30, 'unlock_level' => 1, 'position' => 'kitchen', 'is_active' => true],
            ['name' => 'Разобрать холодильник', 'description' => 'Проверить продукты', 'category' => 'work', 'energy_reward' => 10, 'experience_reward' => 7, 'cooldown_minutes' => 35, 'unlock_level' => 3, 'position' => 'kitchen', 'is_active' => true],

            ['name' => 'Позавтракать', 'description' => 'Спокойно поесть за столом', 'category' => 'health', 'energy_reward' => 25, 'experience_reward' => 8, 'cooldown_minutes' => 30, 'unlock_level' => 1, 'position' => 'table', 'is_active' => true],
            ['name' => 'Написать письмо', 'description' => 'От руки, на бумаге', 'category' => 'creativity', 'energy_reward' => 12, 'experience_reward' => 15, 'cooldown_minutes' => 40, 'unlock_level' => 2, 'position' => 'table', 'is_active' => true],
            ['name' => 'Порешать кроссворд', 'description' => 'Головоломка за столом', 'category' => 'learning', 'energy_reward' => 10, 'experience_reward' => 12, 'cooldown_minutes' => 35, 'unlock_level' => 1, 'position' => 'table', 'is_active' => true],
            ['name' => 'Собрать пазл', 'description' => 'Несколько деталей', 'category' => 'relaxation', 'energy_reward' => 15, 'experience_reward' => 10, 'cooldown_minutes' => 45, 'unlock_level' => 3, 'position' => 'table', 'is_active' => true],
            ['name' => 'Разложить карты', 'description' => 'Пасьянс для расслабления', 'category' => 'relaxation', 'energy_reward' => 12, 'experience_reward' => 6, 'cooldown_minutes' => 30, 'unlock_level' => 1, 'position' => 'table', 'is_active' => true],
            ['name' => 'Сделать оригами', 'description' => 'Сложить бумажную фигурку', 'category' => 'creativity', 'energy_reward' => 14, 'experience_reward' => 14, 'cooldown_minutes' => 35, 'unlock_level' => 4, 'position' => 'table', 'is_active' => true],
            ['name' => 'Навести порядок на столе', 'description' => 'Организовать пространство', 'category' => 'work', 'energy_reward' => 10, 'experience_reward' => 8, 'cooldown_minutes' => 25, 'unlock_level' => 1, 'position' => 'table', 'is_active' => true],

            ['name' => 'Посмотреть на часы', 'description' => 'Проверить текущее время', 'category' => 'work', 'energy_reward' => 2, 'experience_reward' => 1, 'cooldown_minutes' => 5, 'unlock_level' => 1, 'position' => 'wallClock', 'is_active' => true],
            ['name' => 'Подвести часы', 'description' => 'Настроить точное время', 'category' => 'work', 'energy_reward' => 5, 'experience_reward' => 4, 'cooldown_minutes' => 20, 'unlock_level' => 1, 'position' => 'wallClock', 'is_active' => true],
            ['name' => 'Послушать тиканье', 'description' => 'Медитативное наблюдение', 'category' => 'relaxation', 'energy_reward' => 8, 'experience_reward' => 5, 'cooldown_minutes' => 15, 'unlock_level' => 2, 'position' => 'wallClock', 'is_active' => true],
            ['name' => 'Запланировать день', 'description' => 'Рассчитать время на дела', 'category' => 'work', 'energy_reward' => 10, 'experience_reward' => 10, 'cooldown_minutes' => 30, 'unlock_level' => 1, 'position' => 'wallClock', 'is_active' => true],
            ['name' => 'Подумать о времени', 'description' => 'Философские размышления', 'category' => 'learning', 'energy_reward' => 12, 'experience_reward' => 12, 'cooldown_minutes' => 25, 'unlock_level' => 3, 'position' => 'wallClock', 'is_active' => true],
            ['name' => 'Поменять батарейку', 'description' => 'Заменить элемент питания', 'category' => 'work', 'energy_reward' => 6, 'experience_reward' => 5, 'cooldown_minutes' => 15, 'unlock_level' => 2, 'position' => 'wallClock', 'is_active' => true],
            ['name' => 'Протереть часы', 'description' => 'Очистить циферблат', 'category' => 'work', 'energy_reward' => 5, 'experience_reward' => 3, 'cooldown_minutes' => 10, 'unlock_level' => 1, 'position' => 'wallClock', 'is_active' => true],

            ['name' => 'Написать код', 'description' => 'Поработать над проектом', 'category' => 'work', 'energy_reward' => 15, 'experience_reward' => 20, 'cooldown_minutes' => 60, 'unlock_level' => 3, 'position' => 'lapTop', 'is_active' => true],
            ['name' => 'Проверить уведомления', 'description' => 'Просмотреть системные сообщения', 'category' => 'work', 'energy_reward' => 5, 'experience_reward' => 3, 'cooldown_minutes' => 15, 'unlock_level' => 1, 'position' => 'lapTop', 'is_active' => true],
            ['name' => 'Обновить систему', 'description' => 'Установить апдейты', 'category' => 'work', 'energy_reward' => 8, 'experience_reward' => 7, 'cooldown_minutes' => 30, 'unlock_level' => 2, 'position' => 'lapTop', 'is_active' => true],
            ['name' => 'Очистить папки', 'description' => 'Удалить ненужные файлы', 'category' => 'work', 'energy_reward' => 10, 'experience_reward' => 8, 'cooldown_minutes' => 35, 'unlock_level' => 1, 'position' => 'lapTop', 'is_active' => true],
            ['name' => 'Пройти онлайн-курс', 'description' => 'Посмотреть урок', 'category' => 'learning', 'energy_reward' => 12, 'experience_reward' => 18, 'cooldown_minutes' => 50, 'unlock_level' => 4, 'position' => 'lapTop', 'is_active' => true],
            ['name' => 'Зарядить ноутбук', 'description' => 'Подключить к питанию', 'category' => 'work', 'energy_reward' => 3, 'experience_reward' => 2, 'cooldown_minutes' => 10, 'unlock_level' => 1, 'position' => 'lapTop', 'is_active' => true],
            ['name' => 'Почистить клавиатуру', 'description' => 'Убрать пыль и крошки', 'category' => 'work', 'energy_reward' => 6, 'experience_reward' => 4, 'cooldown_minutes' => 20, 'unlock_level' => 1, 'position' => 'lapTop', 'is_active' => true],

            ['name' => 'Достать продукты', 'description' => 'Взять ингредиенты для готовки', 'category' => 'health', 'energy_reward' => 5, 'experience_reward' => 3, 'cooldown_minutes' => 15, 'unlock_level' => 1, 'position' => 'fridge', 'is_active' => true],
            ['name' => 'Выпить воды', 'description' => 'Освежиться холодной водой', 'category' => 'health', 'energy_reward' => 10, 'experience_reward' => 4, 'cooldown_minutes' => 20, 'unlock_level' => 1, 'position' => 'fridge', 'is_active' => true],
            ['name' => 'Съесть йогурт', 'description' => 'Полезный перекус', 'category' => 'health', 'energy_reward' => 15, 'experience_reward' => 6, 'cooldown_minutes' => 30, 'unlock_level' => 1, 'position' => 'fridge', 'is_active' => true],
            ['name' => 'Проверить срок годности', 'description' => 'Осмотреть продукты', 'category' => 'work', 'energy_reward' => 8, 'experience_reward' => 7, 'cooldown_minutes' => 25, 'unlock_level' => 2, 'position' => 'fridge', 'is_active' => true],
            ['name' => 'Разморозить продукты', 'description' => 'Подготовить к готовке', 'category' => 'health', 'energy_reward' => 6, 'experience_reward' => 5, 'cooldown_minutes' => 20, 'unlock_level' => 1, 'position' => 'fridge', 'is_active' => true],
            ['name' => 'Помыть холодильник', 'description' => 'Навести чистоту внутри', 'category' => 'work', 'energy_reward' => 12, 'experience_reward' => 10, 'cooldown_minutes' => 45, 'unlock_level' => 3, 'position' => 'fridge', 'is_active' => true],
            ['name' => 'Составить список покупок', 'description' => 'Проверить, чего не хватает', 'category' => 'work', 'energy_reward' => 8, 'experience_reward' => 8, 'cooldown_minutes' => 20, 'unlock_level' => 1, 'position' => 'fridge', 'is_active' => true],

            ['name' => 'Выбросить мусор', 'description' => 'Очистить корзину', 'category' => 'work', 'energy_reward' => 8, 'experience_reward' => 5, 'cooldown_minutes' => 20, 'unlock_level' => 1, 'position' => 'trashCan', 'is_active' => true],
            ['name' => 'Заменить пакет', 'description' => 'Поставить новый пакет', 'category' => 'work', 'energy_reward' => 5, 'experience_reward' => 3, 'cooldown_minutes' => 15, 'unlock_level' => 1, 'position' => 'trashCan', 'is_active' => true],
            ['name' => 'Рассортировать отходы', 'description' => 'Разделить на переработку', 'category' => 'work', 'energy_reward' => 10, 'experience_reward' => 12, 'cooldown_minutes' => 30, 'unlock_level' => 2, 'position' => 'trashCan', 'is_active' => true],
            ['name' => 'Помыть корзину', 'description' => 'Очистить от грязи', 'category' => 'work', 'energy_reward' => 8, 'experience_reward' => 7, 'cooldown_minutes' => 25, 'unlock_level' => 1, 'position' => 'trashCan', 'is_active' => true],
            ['name' => 'Сдать бутылки', 'description' => 'Отнести на переработку', 'category' => 'work', 'energy_reward' => 12, 'experience_reward' => 10, 'cooldown_minutes' => 40, 'unlock_level' => 3, 'position' => 'trashCan', 'is_active' => true],
            ['name' => 'Убрать ненужное', 'description' => 'Разобрать старые вещи', 'category' => 'work', 'energy_reward' => 15, 'experience_reward' => 12, 'cooldown_minutes' => 50, 'unlock_level' => 4, 'position' => 'trashCan', 'is_active' => true],
            ['name' => 'Освежить воздух', 'description' => 'Распылить дезодорант', 'category' => 'work', 'energy_reward' => 5, 'experience_reward' => 3, 'cooldown_minutes' => 15, 'unlock_level' => 1, 'position' => 'trashCan', 'is_active' => true],

            ['name' => 'Поспать', 'description' => 'Короткий дневной сон', 'category' => 'health', 'energy_reward' => 40, 'experience_reward' => 10, 'cooldown_minutes' => 120, 'unlock_level' => 1, 'position' => 'bed', 'is_active' => true],
            ['name' => 'Застелить постель', 'description' => 'Привести кровать в порядок', 'category' => 'work', 'energy_reward' => 8, 'experience_reward' => 6, 'cooldown_minutes' => 15, 'unlock_level' => 1, 'position' => 'bed', 'is_active' => true],
            ['name' => 'Полежать с книгой', 'description' => 'Почитать в кровати', 'category' => 'relaxation', 'energy_reward' => 20, 'experience_reward' => 15, 'cooldown_minutes' => 45, 'unlock_level' => 2, 'position' => 'bed', 'is_active' => true],
            ['name' => 'Сменить белье', 'description' => 'Постелить свежее', 'category' => 'work', 'energy_reward' => 10, 'experience_reward' => 8, 'cooldown_minutes' => 30, 'unlock_level' => 1, 'position' => 'bed', 'is_active' => true],
            ['name' => 'Помедитировать лежа', 'description' => 'Расслабление на кровати', 'category' => 'relaxation', 'energy_reward' => 25, 'experience_reward' => 12, 'cooldown_minutes' => 40, 'unlock_level' => 3, 'position' => 'bed', 'is_active' => true],
            ['name' => 'Потянуться', 'description' => 'Легкая растяжка', 'category' => 'health', 'energy_reward' => 12, 'experience_reward' => 7, 'cooldown_minutes' => 20, 'unlock_level' => 1, 'position' => 'bed', 'is_active' => true],
            ['name' => 'Помечтать', 'description' => 'Предаться мыслям о будущем', 'category' => 'relaxation', 'energy_reward' => 15, 'experience_reward' => 10, 'cooldown_minutes' => 30, 'unlock_level' => 2, 'position' => 'bed', 'is_active' => true],

            ['name' => 'Посмотреть на себя', 'description' => 'Проверить внешний вид', 'category' => 'health', 'energy_reward' => 5, 'experience_reward' => 3, 'cooldown_minutes' => 10, 'unlock_level' => 1, 'position' => 'mirror', 'is_active' => true],
            ['name' => 'Причесаться', 'description' => 'Привести волосы в порядок', 'category' => 'health', 'energy_reward' => 8, 'experience_reward' => 5, 'cooldown_minutes' => 15, 'unlock_level' => 1, 'position' => 'mirror', 'is_active' => true],
            ['name' => 'Улыбнуться себе', 'description' => 'Поднять настроение', 'category' => 'relaxation', 'energy_reward' => 10, 'experience_reward' => 8, 'cooldown_minutes' => 20, 'unlock_level' => 1, 'position' => 'mirror', 'is_active' => true],
            ['name' => 'Сказать аффирмацию', 'description' => 'Позитивная установка', 'category' => 'health', 'energy_reward' => 12, 'experience_reward' => 10, 'cooldown_minutes' => 25, 'unlock_level' => 2, 'position' => 'mirror', 'is_active' => true],
            ['name' => 'Примерить наряд', 'description' => 'Посмотреть как сидит одежда', 'category' => 'creativity', 'energy_reward' => 10, 'experience_reward' => 7, 'cooldown_minutes' => 20, 'unlock_level' => 1, 'position' => 'mirror', 'is_active' => true],
            ['name' => 'Помыть зеркало', 'description' => 'Протереть до блеска', 'category' => 'work', 'energy_reward' => 6, 'experience_reward' => 4, 'cooldown_minutes' => 15, 'unlock_level' => 1, 'position' => 'mirror', 'is_active' => true],
            ['name' => 'Сделать гримасу', 'description' => 'Развеселить себя', 'category' => 'relaxation', 'energy_reward' => 8, 'experience_reward' => 5, 'cooldown_minutes' => 10, 'unlock_level' => 1, 'position' => 'mirror', 'is_active' => true],
        ];
    }
}

