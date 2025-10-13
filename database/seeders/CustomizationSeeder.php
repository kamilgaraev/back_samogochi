<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\CustomizationItem;
use App\Enums\CustomizationCategory;

class CustomizationSeeder extends Seeder
{
    public function run(): void
    {
        $items = [
            [
                'category_key' => 'wardrobe_shirt',
                'category' => CustomizationCategory::WARDROBE->value,
                'name' => 'Базовая футболка',
                'description' => 'Простая белая футболка',
                'unlock_level' => 1,
                'order' => 0,
                'is_default' => true,
                'is_active' => true,
            ],
            [
                'category_key' => 'wardrobe_shirt',
                'category' => CustomizationCategory::WARDROBE->value,
                'name' => 'Красная футболка',
                'description' => 'Стильная красная футболка',
                'unlock_level' => 3,
                'order' => 1,
                'is_default' => false,
                'is_active' => true,
            ],
            [
                'category_key' => 'wardrobe_shirt',
                'category' => CustomizationCategory::WARDROBE->value,
                'name' => 'Синяя рубашка',
                'description' => 'Элегантная синяя рубашка',
                'unlock_level' => 5,
                'order' => 2,
                'is_default' => false,
                'is_active' => true,
            ],
            [
                'category_key' => 'wardrobe_shirt',
                'category' => CustomizationCategory::WARDROBE->value,
                'name' => 'Чёрная футболка',
                'description' => 'Классическая чёрная футболка',
                'unlock_level' => 7,
                'order' => 3,
                'is_default' => false,
                'is_active' => true,
            ],
            [
                'category_key' => 'wardrobe_shirt',
                'category' => CustomizationCategory::WARDROBE->value,
                'name' => 'Полосатая футболка',
                'description' => 'Модная полосатая футболка',
                'unlock_level' => 10,
                'order' => 4,
                'is_default' => false,
                'is_active' => true,
            ],

            [
                'category_key' => 'wardrobe_pants',
                'category' => CustomizationCategory::WARDROBE->value,
                'name' => 'Базовые джинсы',
                'description' => 'Классические синие джинсы',
                'unlock_level' => 1,
                'order' => 0,
                'is_default' => true,
                'is_active' => true,
            ],
            [
                'category_key' => 'wardrobe_pants',
                'category' => CustomizationCategory::WARDROBE->value,
                'name' => 'Чёрные джинсы',
                'description' => 'Стильные чёрные джинсы',
                'unlock_level' => 4,
                'order' => 1,
                'is_default' => false,
                'is_active' => true,
            ],
            [
                'category_key' => 'wardrobe_pants',
                'category' => CustomizationCategory::WARDROBE->value,
                'name' => 'Спортивные штаны',
                'description' => 'Удобные спортивные штаны',
                'unlock_level' => 6,
                'order' => 2,
                'is_default' => false,
                'is_active' => true,
            ],

            [
                'category_key' => 'furniture_table',
                'category' => CustomizationCategory::FURNITURE->value,
                'name' => 'Деревянный стол',
                'description' => 'Простой деревянный стол',
                'unlock_level' => 1,
                'order' => 0,
                'is_default' => true,
                'is_active' => true,
            ],
            [
                'category_key' => 'furniture_table',
                'category' => CustomizationCategory::FURNITURE->value,
                'name' => 'Стеклянный стол',
                'description' => 'Современный стеклянный стол',
                'unlock_level' => 5,
                'order' => 1,
                'is_default' => false,
                'is_active' => true,
            ],
            [
                'category_key' => 'furniture_table',
                'category' => CustomizationCategory::FURNITURE->value,
                'name' => 'Мраморный стол',
                'description' => 'Роскошный мраморный стол',
                'unlock_level' => 10,
                'order' => 2,
                'is_default' => false,
                'is_active' => true,
            ],

            [
                'category_key' => 'furniture_chair',
                'category' => CustomizationCategory::FURNITURE->value,
                'name' => 'Деревянный стул',
                'description' => 'Простой деревянный стул',
                'unlock_level' => 1,
                'order' => 0,
                'is_default' => true,
                'is_active' => true,
            ],
            [
                'category_key' => 'furniture_chair',
                'category' => CustomizationCategory::FURNITURE->value,
                'name' => 'Офисное кресло',
                'description' => 'Удобное офисное кресло',
                'unlock_level' => 3,
                'order' => 1,
                'is_default' => false,
                'is_active' => true,
            ],
            [
                'category_key' => 'furniture_chair',
                'category' => CustomizationCategory::FURNITURE->value,
                'name' => 'Игровое кресло',
                'description' => 'Современное игровое кресло',
                'unlock_level' => 7,
                'order' => 2,
                'is_default' => false,
                'is_active' => true,
            ],
            [
                'category_key' => 'furniture_chair',
                'category' => CustomizationCategory::FURNITURE->value,
                'name' => 'Кожаное кресло',
                'description' => 'Премиальное кожаное кресло',
                'unlock_level' => 12,
                'order' => 3,
                'is_default' => false,
                'is_active' => true,
            ],

            [
                'category_key' => 'furniture_lamp',
                'category' => CustomizationCategory::FURNITURE->value,
                'name' => 'Настольная лампа',
                'description' => 'Простая настольная лампа',
                'unlock_level' => 1,
                'order' => 0,
                'is_default' => true,
                'is_active' => true,
            ],
            [
                'category_key' => 'furniture_lamp',
                'category' => CustomizationCategory::FURNITURE->value,
                'name' => 'LED лампа',
                'description' => 'Современная LED лампа с регулировкой яркости',
                'unlock_level' => 4,
                'order' => 1,
                'is_default' => false,
                'is_active' => true,
            ],
            [
                'category_key' => 'furniture_lamp',
                'category' => CustomizationCategory::FURNITURE->value,
                'name' => 'Дизайнерская лампа',
                'description' => 'Стильная дизайнерская лампа',
                'unlock_level' => 8,
                'order' => 2,
                'is_default' => false,
                'is_active' => true,
            ],

            [
                'category_key' => 'wardrobe_accessory',
                'category' => CustomizationCategory::WARDROBE->value,
                'name' => 'Базовая кепка',
                'description' => 'Простая чёрная кепка',
                'unlock_level' => 2,
                'order' => 0,
                'is_default' => false,
                'is_active' => true,
            ],
            [
                'category_key' => 'wardrobe_accessory',
                'category' => CustomizationCategory::WARDROBE->value,
                'name' => 'Солнцезащитные очки',
                'description' => 'Стильные солнцезащитные очки',
                'unlock_level' => 5,
                'order' => 1,
                'is_default' => false,
                'is_active' => true,
            ],
            [
                'category_key' => 'wardrobe_accessory',
                'category' => CustomizationCategory::WARDROBE->value,
                'name' => 'Наушники',
                'description' => 'Беспроводные наушники',
                'unlock_level' => 8,
                'order' => 2,
                'is_default' => false,
                'is_active' => true,
            ],
        ];

        foreach ($items as $item) {
            CustomizationItem::create($item);
        }

        $this->command->info('Создано ' . count($items) . ' элементов кастомизации');
    }
}

