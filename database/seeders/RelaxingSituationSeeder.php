<?php

namespace Database\Seeders;

use App\Models\Situation;
use App\Models\SituationOption;
use Illuminate\Database\Seeder;

class RelaxingSituationSeeder extends Seeder
{
    public function run(): void
    {
        $relaxingSituations = [
            [
                'title' => 'Медитация в парке',
                'description' => 'Прекрасный солнечный день. Вы находите тихое место в парке, садитесь под деревом и решаете провести время в медитации.',
                'category' => 'health',
                'difficulty_level' => 1,
                'min_level_required' => 1,
                'stress_impact' => -10,
                'experience_reward' => 15,
                'position' => 'phone',
                'options' => [
                    [
                        'text' => 'Сосредоточиться на дыхании и звуках природы',
                        'stress_change' => -15,
                        'experience_reward' => 20,
                        'energy_cost' => 5,
                        'order' => 1
                    ],
                    [
                        'text' => 'Использовать приложение для медитации',
                        'stress_change' => -10,
                        'experience_reward' => 15,
                        'energy_cost' => 3,
                        'order' => 2
                    ],
                    [
                        'text' => 'Просто наслаждаться природой без концентрации',
                        'stress_change' => -8,
                        'experience_reward' => 12,
                        'energy_cost' => 2,
                        'order' => 3
                    ]
                ]
            ],
            [
                'title' => 'Вечерняя прогулка',
                'description' => 'После долгого дня вы решаете прогуляться по тихим улицам района, подышать свежим воздухом.',
                'category' => 'health',
                'difficulty_level' => 1,
                'min_level_required' => 1,
                'stress_impact' => -8,
                'experience_reward' => 12,
                'position' => 'smartwatch',
                'options' => [
                    [
                        'text' => 'Прогуляться в спокойном темпе, наблюдая за окружением',
                        'stress_change' => -12,
                        'experience_reward' => 18,
                        'energy_cost' => 5,
                        'order' => 1
                    ],
                    [
                        'text' => 'Слушать успокаивающую музыку во время прогулки',
                        'stress_change' => -10,
                        'experience_reward' => 15,
                        'energy_cost' => 3,
                        'order' => 2
                    ],
                    [
                        'text' => 'Позвонить другу и поболтать во время прогулки',
                        'stress_change' => -6,
                        'experience_reward' => 12,
                        'energy_cost' => 4,
                        'order' => 3
                    ]
                ]
            ],
            [
                'title' => 'Чтение любимой книги',
                'description' => 'У вас есть свободный вечер, и вы решаете посвятить его чтению интересной книги в уютной обстановке.',
                'category' => 'personal',
                'difficulty_level' => 1,
                'min_level_required' => 1,
                'stress_impact' => -6,
                'experience_reward' => 10,
                'position' => 'tablet',
                'options' => [
                    [
                        'text' => 'Устроиться с книгой и чаем в любимом кресле',
                        'stress_change' => -12,
                        'experience_reward' => 16,
                        'energy_cost' => 2,
                        'order' => 1
                    ],
                    [
                        'text' => 'Читать на улице в кафе или парке',
                        'stress_change' => -8,
                        'experience_reward' => 12,
                        'energy_cost' => 3,
                        'order' => 2
                    ],
                    [
                        'text' => 'Послушать аудиокнигу, лежа с закрытыми глазами',
                        'stress_change' => -10,
                        'experience_reward' => 14,
                        'energy_cost' => 1,
                        'order' => 3
                    ]
                ]
            ],
            [
                'title' => 'Встреча с близкими друзьями',
                'description' => 'Давно не виделись с хорошими друзьями. Решаете встретиться в неформальной обстановке и просто хорошо провести время.',
                'category' => 'personal',
                'difficulty_level' => 1,
                'min_level_required' => 1,
                'stress_impact' => -12,
                'experience_reward' => 18,
                'position' => 'phone',
                'options' => [
                    [
                        'text' => 'Сходить в уютное кафе и поболтать за чашкой кофе',
                        'stress_change' => -15,
                        'experience_reward' => 22,
                        'energy_cost' => 5,
                        'order' => 1
                    ],
                    [
                        'text' => 'Организовать домашний вечер с играми и снэками',
                        'stress_change' => -18,
                        'experience_reward' => 25,
                        'energy_cost' => 8,
                        'order' => 2
                    ],
                    [
                        'text' => 'Прогуляться вместе по центру города',
                        'stress_change' => -12,
                        'experience_reward' => 20,
                        'energy_cost' => 6,
                        'order' => 3
                    ]
                ]
            ],
            [
                'title' => 'Творческое хобби',
                'description' => 'Выходные - отличное время для занятия любимым творчеством. Вы достаете материалы и готовитесь создать что-то прекрасное.',
                'category' => 'personal',
                'difficulty_level' => 1,
                'min_level_required' => 1,
                'stress_impact' => -9,
                'experience_reward' => 16,
                'position' => 'desktop',
                'options' => [
                    [
                        'text' => 'Рисовать или писать красками без цели, просто для удовольствия',
                        'stress_change' => -14,
                        'experience_reward' => 20,
                        'energy_cost' => 6,
                        'order' => 1
                    ],
                    [
                        'text' => 'Заняться рукоделием: вязанием, вышивкой или оригами',
                        'stress_change' => -12,
                        'experience_reward' => 18,
                        'energy_cost' => 4,
                        'order' => 2
                    ],
                    [
                        'text' => 'Играть на музыкальном инструменте или петь',
                        'stress_change' => -16,
                        'experience_reward' => 22,
                        'energy_cost' => 8,
                        'order' => 3
                    ]
                ]
            ],
            [
                'title' => 'Релаксация дома',
                'description' => 'Дома никого нет, и у вас есть возможность полностью расслабиться и заняться уходом за собой.',
                'category' => 'health',
                'difficulty_level' => 1,
                'min_level_required' => 1,
                'stress_impact' => -7,
                'experience_reward' => 12,
                'position' => 'smartwatch',
                'options' => [
                    [
                        'text' => 'Принять расслабляющую ванну с ароматическими маслами',
                        'stress_change' => -16,
                        'experience_reward' => 18,
                        'energy_cost' => 3,
                        'order' => 1
                    ],
                    [
                        'text' => 'Сделать себе домашний spa-день с масками и массажем',
                        'stress_change' => -14,
                        'experience_reward' => 20,
                        'energy_cost' => 5,
                        'order' => 2
                    ],
                    [
                        'text' => 'Заварить травяной чай и посмотреть любимый фильм',
                        'stress_change' => -10,
                        'experience_reward' => 14,
                        'energy_cost' => 2,
                        'order' => 3
                    ]
                ]
            ],
            [
                'title' => 'Время с животными',
                'description' => 'Вы проводите время с домашними питомцами или идете в зоопарк. Общение с животными всегда поднимает настроение.',
                'category' => 'personal',
                'difficulty_level' => 1,
                'min_level_required' => 1,
                'stress_impact' => -11,
                'experience_reward' => 15,
                'position' => 'phone',
                'options' => [
                    [
                        'text' => 'Играть с домашним питомцем и гладить его',
                        'stress_change' => -18,
                        'experience_reward' => 20,
                        'energy_cost' => 4,
                        'order' => 1
                    ],
                    [
                        'text' => 'Сходить в приют и помочь волонтерам',
                        'stress_change' => -15,
                        'experience_reward' => 25,
                        'energy_cost' => 8,
                        'order' => 2
                    ],
                    [
                        'text' => 'Посетить контактный зоопарк или ферму',
                        'stress_change' => -12,
                        'experience_reward' => 18,
                        'energy_cost' => 6,
                        'order' => 3
                    ]
                ]
            ],
            [
                'title' => 'Приготовление любимого блюда',
                'description' => 'У вас есть время и вдохновение приготовить что-то вкусное. Процесс готовки может быть очень медитативным.',
                'category' => 'personal',
                'difficulty_level' => 2,
                'min_level_required' => 1,
                'stress_impact' => -5,
                'experience_reward' => 14,
                'position' => 'tablet',
                'options' => [
                    [
                        'text' => 'Испечь домашний хлеб или пирог, наслаждаясь процессом',
                        'stress_change' => -12,
                        'experience_reward' => 20,
                        'energy_cost' => 10,
                        'order' => 1
                    ],
                    [
                        'text' => 'Приготовить сложное блюдо по новому рецепту',
                        'stress_change' => -8,
                        'experience_reward' => 18,
                        'energy_cost' => 12,
                        'order' => 2
                    ],
                    [
                        'text' => 'Сделать простой, но любимый ужин под музыку',
                        'stress_change' => -10,
                        'experience_reward' => 15,
                        'energy_cost' => 6,
                        'order' => 3
                    ]
                ]
            ]
        ];

        foreach ($relaxingSituations as $situationData) {
            $options = $situationData['options'];
            unset($situationData['options']);

            $situation = Situation::create($situationData);

            foreach ($options as $optionData) {
                $optionData['situation_id'] = $situation->id;
                SituationOption::create($optionData);
            }
        }
    }
}
