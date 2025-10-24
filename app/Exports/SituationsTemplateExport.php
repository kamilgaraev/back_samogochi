<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Color;

class SituationsTemplateExport implements FromCollection, WithHeadings, WithStyles, ShouldAutoSize, WithEvents
{
    public function collection()
    {
        $example1 = [
            'Конфликт на работе',
            'Вы столкнулись с конфликтом с коллегой по важному проекту',
            'work',
            '2',
            '1',
            '10',
            '20',
            'phone',
            'Character_1_1',
            'https://example.com/help',
            'Как решать конфликты на работе',
            '1',
        ];

        $example1Options = [
            ['Открыто обсудить проблему', '-5', '25', '5', '1'],
            ['Игнорировать ситуацию', '10', '10', '0', '1'],
            ['Обратиться к руководителю', '0', '15', '3', '1'],
        ];

        foreach ($example1Options as $option) {
            $example1 = array_merge($example1, $option);
        }
        for ($i = count($example1Options); $i < 10; $i++) {
            $example1 = array_merge($example1, ['', '', '', '', '']);
        }

        $example2 = [
            'Сложный экзамен',
            'Предстоит важный экзамен, к которому вы не успели подготовиться',
            'study',
            '3',
            '5',
            '15',
            '30',
            'table',
            '',
            '',
            '',
            '1',
        ];

        $example2Options = [
            ['Заниматься всю ночь', '5', '40', '20', '5'],
            ['Попросить перенести', '-3', '15', '5', '1'],
        ];

        foreach ($example2Options as $option) {
            $example2 = array_merge($example2, $option);
        }
        for ($i = count($example2Options); $i < 10; $i++) {
            $example2 = array_merge($example2, ['', '', '', '', '']);
        }

        return collect([$example1, $example2]);
    }

    public function headings(): array
    {
        $baseHeadings = [
            'Название*',
            'Описание*',
            'Категория* (work/study/personal/health)',
            'Сложность* (1-5)',
            'Мин. уровень',
            'Влияние на стресс (-50 до +50)',
            'Награда опытом (1-100)',
            'Позиция (phone/table/tv/wallClock/lapTop/fridge/trashCan/bed/mirror)',
            'Привязка к кастомизации (Character_1_1 и т.д.)',
            'Ссылка',
            'Название статьи',
            'Активна (1/0)',
        ];

        for ($i = 1; $i <= 10; $i++) {
            $baseHeadings[] = "Вариант {$i}: Текст" . ($i === 1 ? '*' : '');
            $baseHeadings[] = "Вариант {$i}: Изм. стресса" . ($i === 1 ? '*' : '');
            $baseHeadings[] = "Вариант {$i}: Опыт" . ($i === 1 ? '*' : '');
            $baseHeadings[] = "Вариант {$i}: Энергия";
            $baseHeadings[] = "Вариант {$i}: Мин. уровень";
        }

        return $baseHeadings;
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => [
                'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['rgb' => '4472C4']
                ]
            ]
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function(AfterSheet $event) {
                $lastColumn = $this->numberToColumn(12 + 10 * 5);
                $event->sheet->getDelegate()->getStyle("A1:{$lastColumn}1")->getAlignment()->setWrapText(true);
                $event->sheet->getDelegate()->getRowDimension('1')->setRowHeight(50);
                
                $event->sheet->getDelegate()->getComment('C2')->getText()->createTextRun(
                    "Допустимые значения:\n- work (Работа)\n- study (Учёба)\n- personal (Личное)\n- health (Здоровье)"
                );
                
                $event->sheet->getDelegate()->getComment('D2')->getText()->createTextRun(
                    "Уровень сложности от 1 (лёгкая) до 5 (очень сложная)"
                );
                
                $event->sheet->getDelegate()->getComment('I2')->getText()->createTextRun(
                    "Оставьте пустым, чтобы ситуация показывалась всем.\nУкажите название кастомизации (например Character_1_1), чтобы показывать только игрокам с этим скином."
                );

                $event->sheet->getDelegate()->getComment('J2')->getText()->createTextRun(
                    "Ссылка произвольного формата (например URL для дополнительной информации или действий)"
                );

                $event->sheet->getDelegate()->getComment('K2')->getText()->createTextRun(
                    "Название статьи или материала, связанного с ситуацией"
                );

                $event->sheet->getDelegate()->getComment('M2')->getText()->createTextRun(
                    "Минимум 1 вариант обязателен.\nМожно добавить до 10 вариантов действий.\nОставьте пустыми ячейки для неиспользуемых вариантов."
                );
            }
        ];
    }

    private function numberToColumn($num)
    {
        $str = '';
        while ($num > 0) {
            $num--;
            $str = chr(65 + ($num % 26)) . $str;
            $num = intdiv($num, 26);
        }
        return $str;
    }
}

