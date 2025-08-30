<?php

namespace Database\Seeders;

use App\Models\MicroAction;
use Illuminate\Database\Seeder;

class MicroActionSeeder extends Seeder
{
    public function run(): void
    {
        $microActions = [
            // Relaxation (Релаксация)
            [
                'name' => 'Глубокое дыхание 4-7-8',
                'description' => 'Техника дыхания: вдох на 4 счета, задержка на 7, выдох на 8. Повторить 4 раза.',
                'category' => 'relaxation',
                'energy_reward' => 15,
                'experience_reward' => 8,
                'cooldown_minutes' => 30,
                'unlock_level' => 1,
            ],
            [
                'name' => 'Прогрессивная мышечная релаксация',
                'description' => '5-минутная техника напряжения и расслабления мышц всего тела.',
                'category' => 'relaxation',
                'energy_reward' => 20,
                'experience_reward' => 12,
                'cooldown_minutes' => 60,
                'unlock_level' => 2,
            ],
            [
                'name' => 'Медитация осознанности',
                'description' => '10-минутная медитация с фокусом на настоящем моменте.',
                'category' => 'relaxation',
                'energy_reward' => 25,
                'experience_reward' => 15,
                'cooldown_minutes' => 120,
                'unlock_level' => 3,
            ],
            [
                'name' => 'Визуализация спокойного места',
                'description' => 'Мысленное путешествие в место, которое вас успокаивает.',
                'category' => 'relaxation',
                'energy_reward' => 18,
                'experience_reward' => 10,
                'cooldown_minutes' => 45,
                'unlock_level' => 2,
            ],
            
            // Exercise (Физические упражнения)
            [
                'name' => 'Растяжка шеи и плеч',
                'description' => 'Простые упражнения для снятия напряжения в шее и плечах.',
                'category' => 'exercise',
                'energy_reward' => 12,
                'experience_reward' => 6,
                'cooldown_minutes' => 15,
                'unlock_level' => 1,
            ],
            [
                'name' => 'Прогулка на свежем воздухе',
                'description' => '15-минутная прогулка на улице с осознанным наблюдением за окружением.',
                'category' => 'exercise',
                'energy_reward' => 22,
                'experience_reward' => 14,
                'cooldown_minutes' => 60,
                'unlock_level' => 1,
            ],
            [
                'name' => 'Йога для начинающих',
                'description' => '20-минутный комплекс простых асан йоги для расслабления.',
                'category' => 'exercise',
                'energy_reward' => 30,
                'experience_reward' => 18,
                'cooldown_minutes' => 120,
                'unlock_level' => 3,
            ],
            [
                'name' => 'Легкие приседания',
                'description' => '10 медленных приседаний с правильной техникой для активации тела.',
                'category' => 'exercise',
                'energy_reward' => 16,
                'experience_reward' => 8,
                'cooldown_minutes' => 30,
                'unlock_level' => 2,
            ],
            [
                'name' => 'Танцы под любимую музыку',
                'description' => '5-10 минут свободного танца под музыку, которая вам нравится.',
                'category' => 'exercise',
                'energy_reward' => 20,
                'experience_reward' => 12,
                'cooldown_minutes' => 45,
                'unlock_level' => 2,
            ],
            
            // Creativity (Творчество)
            [
                'name' => 'Свободное рисование',
                'description' => '10 минут рисования без цели и критики - просто позвольте руке двигаться.',
                'category' => 'creativity',
                'energy_reward' => 14,
                'experience_reward' => 10,
                'cooldown_minutes' => 60,
                'unlock_level' => 1,
            ],
            [
                'name' => 'Письмо благодарности',
                'description' => 'Написать короткое письмо или сообщение с благодарностью кому-то важному.',
                'category' => 'creativity',
                'energy_reward' => 16,
                'experience_reward' => 12,
                'cooldown_minutes' => 120,
                'unlock_level' => 1,
            ],
            [
                'name' => 'Ведение дневника',
                'description' => '10 минут написания о своих мыслях, чувствах или событиях дня.',
                'category' => 'creativity',
                'energy_reward' => 12,
                'experience_reward' => 8,
                'cooldown_minutes' => 480, // 8 часов
                'unlock_level' => 1,
            ],
            [
                'name' => 'Создание музыкального плейлиста',
                'description' => 'Составить плейлист из песен, которые отражают ваше текущее настроение.',
                'category' => 'creativity',
                'energy_reward' => 10,
                'experience_reward' => 6,
                'cooldown_minutes' => 180,
                'unlock_level' => 1,
            ],
            [
                'name' => 'Мозговой штурм идей',
                'description' => '15 минут записывания любых приходящих в голову идей без оценки.',
                'category' => 'creativity',
                'energy_reward' => 18,
                'experience_reward' => 14,
                'cooldown_minutes' => 120,
                'unlock_level' => 3,
            ],
            
            // Social (Социальные активности)
            [
                'name' => 'Позвонить другу или близкому',
                'description' => 'Короткий звонок человеку, с которым давно не общались.',
                'category' => 'social',
                'energy_reward' => 20,
                'experience_reward' => 15,
                'cooldown_minutes' => 180,
                'unlock_level' => 1,
            ],
            [
                'name' => 'Отправить поддерживающее сообщение',
                'description' => 'Написать кому-то приятное сообщение или слова поддержки.',
                'category' => 'social',
                'energy_reward' => 12,
                'experience_reward' => 8,
                'cooldown_minutes' => 60,
                'unlock_level' => 1,
            ],
            [
                'name' => 'Поделиться своими чувствами',
                'description' => 'Рассказать доверенному человеку о том, что вас беспокоит.',
                'category' => 'social',
                'energy_reward' => 25,
                'experience_reward' => 18,
                'cooldown_minutes' => 240,
                'unlock_level' => 2,
            ],
            [
                'name' => 'Сделать комплимент незнакомцу',
                'description' => 'Искренне похвалить или поблагодарить кого-то (продавца, водителя, коллегу).',
                'category' => 'social',
                'energy_reward' => 15,
                'experience_reward' => 12,
                'cooldown_minutes' => 120,
                'unlock_level' => 3,
            ],
            [
                'name' => 'Активное слушание',
                'description' => '15 минут полного внимания к разговору с кем-то без отвлечений.',
                'category' => 'social',
                'energy_reward' => 18,
                'experience_reward' => 14,
                'cooldown_minutes' => 180,
                'unlock_level' => 2,
            ],
        ];

        foreach ($microActions as $microActionData) {
            MicroAction::create($microActionData);
        }
    }
}
