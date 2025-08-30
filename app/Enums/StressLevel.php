<?php

namespace App\Enums;

enum StressLevel: string
{
    case LOW = 'low';
    case NORMAL = 'normal';
    case ELEVATED = 'elevated';
    case HIGH = 'high';

    public function getLabel(): string
    {
        return match ($this) {
            self::LOW => 'Низкий',
            self::NORMAL => 'Нормальный',
            self::ELEVATED => 'Повышенный',
            self::HIGH => 'Высокий',
        };
    }

    public function getDescription(): string
    {
        return match ($this) {
            self::LOW => 'Спокойное состояние, можно браться за сложные задачи',
            self::NORMAL => 'Обычное состояние, всё под контролем',
            self::ELEVATED => 'Повышенная тревожность, стоит обратить внимание на релаксацию',
            self::HIGH => 'Высокий стресс, необходимы техники снижения напряжения',
        };
    }

    public function getColor(): string
    {
        return match ($this) {
            self::LOW => '#4CAF50',      // Зеленый
            self::NORMAL => '#2196F3',   // Синий
            self::ELEVATED => '#FF9800', // Оранжевый
            self::HIGH => '#F44336',     // Красный
        };
    }

    public function getMinValue(): int
    {
        return match ($this) {
            self::LOW => 0,
            self::NORMAL => 21,
            self::ELEVATED => 51,
            self::HIGH => 81,
        };
    }

    public function getMaxValue(): int
    {
        return match ($this) {
            self::LOW => 20,
            self::NORMAL => 50,
            self::ELEVATED => 80,
            self::HIGH => 100,
        };
    }

    public static function fromValue(int $stressValue): self
    {
        return match (true) {
            $stressValue <= 20 => self::LOW,
            $stressValue <= 50 => self::NORMAL,
            $stressValue <= 80 => self::ELEVATED,
            default => self::HIGH,
        };
    }

    public function getRecommendedActions(): array
    {
        return match ($this) {
            self::LOW => [
                'Можете браться за сложные задачи',
                'Хорошее время для изучения нового',
                'Подумайте о профилактических техниках релаксации'
            ],
            self::NORMAL => [
                'Поддерживайте текущий ритм',
                'Не забывайте о перерывах',
                'Следите за балансом работы и отдыха'
            ],
            self::ELEVATED => [
                'Сделайте паузу и глубоко подышите',
                'Попробуйте технику прогрессивной мышечной релаксации',
                'Уделите время физической активности'
            ],
            self::HIGH => [
                'СРОЧНО нужен отдых и релаксация',
                'Попробуйте медитацию или дыхательные упражнения',
                'Рассмотрите возможность обращения за профессиональной помощью'
            ],
        };
    }
}