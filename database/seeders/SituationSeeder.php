<?php

namespace Database\Seeders;

use App\Models\Situation;
use App\Models\SituationOption;
use Illuminate\Database\Seeder;

class SituationSeeder extends Seeder
{
    public function run(): void
    {
        $situations = [
            [
                'title' => 'Горящий дедлайн на работе',
                'description' => 'Ваш руководитель просит срочно закончить проект до конца дня, но времени катастрофически не хватает.',
                'category' => 'work',
                'difficulty_level' => 2,
                'min_level_required' => 1,
                'stress_impact' => 15,
                'experience_reward' => 20,
                'position' => 'desktop',
                'options' => [
                    [
                        'text' => 'Спокойно составить план действий и работать по приоритетам',
                        'stress_change' => -5,
                        'experience_reward' => 25,
                        'energy_cost' => 10,
                        'order' => 1
                    ],
                    [
                        'text' => 'Паниковать и пытаться сделать всё одновременно',
                        'stress_change' => 20,
                        'experience_reward' => 5,
                        'energy_cost' => 20,
                        'order' => 2
                    ],
                    [
                        'text' => 'Попросить помощи у коллег',
                        'stress_change' => -10,
                        'experience_reward' => 15,
                        'energy_cost' => 5,
                        'order' => 3
                    ]
                ]
            ],
            [
                'title' => 'Важный экзамен через неделю',
                'description' => 'Приближается экзамен по важному предмету, а материала для изучения очень много.',
                'category' => 'study',
                'difficulty_level' => 1,
                'min_level_required' => 1,
                'stress_impact' => 10,
                'experience_reward' => 15,
                'position' => 'tablet',
                'options' => [
                    [
                        'text' => 'Составить подробный план подготовки и следовать ему',
                        'stress_change' => -8,
                        'experience_reward' => 20,
                        'energy_cost' => 8,
                        'order' => 1
                    ],
                    [
                        'text' => 'Заниматься по 12 часов в день без перерывов',
                        'stress_change' => 15,
                        'experience_reward' => 10,
                        'energy_cost' => 25,
                        'order' => 2
                    ],
                    [
                        'text' => 'Организовать группу для совместной подготовки',
                        'stress_change' => -5,
                        'experience_reward' => 18,
                        'energy_cost' => 5,
                        'order' => 3
                    ]
                ]
            ],
            [
                'title' => 'Конфликт с другом',
                'description' => 'Ваш близкий друг обиделся на вас из-за недоразумения, и теперь не отвечает на сообщения.',
                'category' => 'personal',
                'difficulty_level' => 2,
                'min_level_required' => 1,
                'stress_impact' => 12,
                'experience_reward' => 18,
                'position' => 'phone',
                'options' => [
                    [
                        'text' => 'Искренне извиниться и объяснить свою позицию',
                        'stress_change' => -10,
                        'experience_reward' => 22,
                        'energy_cost' => 5,
                        'order' => 1
                    ],
                    [
                        'text' => 'Подождать, пока друг сам не успокоится',
                        'stress_change' => 5,
                        'experience_reward' => 8,
                        'energy_cost' => 0,
                        'order' => 2
                    ],
                    [
                        'text' => 'Обратиться к общим друзьям за советом',
                        'stress_change' => -3,
                        'experience_reward' => 15,
                        'energy_cost' => 3,
                        'order' => 3
                    ]
                ]
            ],
            [
                'title' => 'Бессонная ночь перед важным днем',
                'description' => 'Завтра важная презентация, но заснуть никак не получается из-за волнения.',
                'category' => 'health',
                'difficulty_level' => 1,
                'min_level_required' => 1,
                'stress_impact' => 8,
                'experience_reward' => 12,
                'position' => 'smartwatch',
                'options' => [
                    [
                        'text' => 'Выполнить дыхательные упражнения и медитацию',
                        'stress_change' => -12,
                        'experience_reward' => 18,
                        'energy_cost' => 5,
                        'order' => 1
                    ],
                    [
                        'text' => 'Принять снотворное и попытаться заснуть',
                        'stress_change' => -5,
                        'experience_reward' => 6,
                        'energy_cost' => 10,
                        'order' => 2
                    ],
                    [
                        'text' => 'Встать и заняться подготовкой к презентации',
                        'stress_change' => 10,
                        'experience_reward' => 12,
                        'energy_cost' => 15,
                        'order' => 3
                    ]
                ]
            ],
            [
                'title' => 'Публичное выступление',
                'description' => 'Вам предстоит выступить с докладом перед большой аудиторией, а вы очень волнуетесь.',
                'category' => 'work',
                'difficulty_level' => 3,
                'min_level_required' => 2,
                'stress_impact' => 20,
                'experience_reward' => 30,
                'options' => [
                    [
                        'text' => 'Тщательно отрепетировать выступление перед зеркалом',
                        'stress_change' => -15,
                        'experience_reward' => 35,
                        'energy_cost' => 12,
                        'min_level_required' => 2,
                        'order' => 1
                    ],
                    [
                        'text' => 'Представить аудиторию в нижнем белье',
                        'stress_change' => -8,
                        'experience_reward' => 20,
                        'energy_cost' => 5,
                        'order' => 2
                    ],
                    [
                        'text' => 'Сосредоточиться на сути доклада, а не на страхах',
                        'stress_change' => -12,
                        'experience_reward' => 30,
                        'energy_cost' => 8,
                        'min_level_required' => 2,
                        'order' => 3
                    ]
                ]
            ],
            [
                'title' => 'Семейные разногласия',
                'description' => 'В семье возникли серьезные разногласия по важному вопросу, и атмосфера накаляется.',
                'category' => 'personal',
                'difficulty_level' => 3,
                'min_level_required' => 2,
                'stress_impact' => 18,
                'experience_reward' => 25,
                'position' => 'tv',
                'options' => [
                    [
                        'text' => 'Организовать семейный совет для обсуждения',
                        'stress_change' => -15,
                        'experience_reward' => 30,
                        'energy_cost' => 10,
                        'min_level_required' => 2,
                        'order' => 1
                    ],
                    [
                        'text' => 'Избегать обсуждения и надеяться, что всё само решится',
                        'stress_change' => 8,
                        'experience_reward' => 8,
                        'energy_cost' => 0,
                        'order' => 2
                    ],
                    [
                        'text' => 'Попытаться найти компромисс между всеми сторонами',
                        'stress_change' => -10,
                        'experience_reward' => 28,
                        'energy_cost' => 12,
                        'min_level_required' => 2,
                        'order' => 3
                    ]
                ]
            ],
            [
                'title' => 'Проблемы с концентрацией',
                'description' => 'Последнее время вам трудно сосредоточиться на работе, мысли постоянно отвлекаются.',
                'category' => 'health',
                'difficulty_level' => 2,
                'min_level_required' => 1,
                'stress_impact' => 10,
                'experience_reward' => 16,
                'position' => 'notification',
                'options' => [
                    [
                        'text' => 'Проанализировать причины и устранить отвлекающие факторы',
                        'stress_change' => -12,
                        'experience_reward' => 22,
                        'energy_cost' => 8,
                        'order' => 1
                    ],
                    [
                        'text' => 'Попробовать технику помодоро для концентрации',
                        'stress_change' => -8,
                        'experience_reward' => 18,
                        'energy_cost' => 5,
                        'order' => 2
                    ],
                    [
                        'text' => 'Выпить больше кофе и заставить себя работать',
                        'stress_change' => 5,
                        'experience_reward' => 10,
                        'energy_cost' => 15,
                        'order' => 3
                    ]
                ]
            ],
            [
                'title' => 'Сложный экзамен по математике',
                'description' => 'Завтра экзамен по высшей математике, а некоторые темы вы еще не до конца понимаете.',
                'category' => 'study',
                'difficulty_level' => 3,
                'min_level_required' => 2,
                'stress_impact' => 16,
                'experience_reward' => 28,
                'position' => 'desktop',
                'options' => [
                    [
                        'text' => 'Сосредоточиться на основных формулах и разобрать типовые задачи',
                        'stress_change' => -10,
                        'experience_reward' => 32,
                        'energy_cost' => 15,
                        'min_level_required' => 2,
                        'order' => 1
                    ],
                    [
                        'text' => 'Обратиться к однокурсникам за объяснениями',
                        'stress_change' => -6,
                        'experience_reward' => 25,
                        'energy_cost' => 8,
                        'order' => 2
                    ],
                    [
                        'text' => 'Попытаться выучить всё наизусть за ночь',
                        'stress_change' => 12,
                        'experience_reward' => 15,
                        'energy_cost' => 25,
                        'order' => 3
                    ]
                ]
            ]
        ];

        foreach ($situations as $situationData) {
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
