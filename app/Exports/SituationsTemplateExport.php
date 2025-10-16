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
        return collect([
            [
                'Конфликт на работе',
                'Вы столкнулись с конфликтом с коллегой по важному проекту',
                'work',
                '2',
                '1',
                '10',
                '20',
                'desktop',
                'Character_1_1',
                '1',
                'Открыто обсудить проблему',
                '-5',
                '25',
                '5',
                '1',
                'Игнорировать ситуацию',
                '10',
                '10',
                '0',
                '1'
            ],
            [
                'Сложный экзамен',
                'Предстоит важный экзамен, к которому вы не успели подготовиться',
                'study',
                '3',
                '5',
                '15',
                '30',
                'desktop',
                '',
                '1',
                'Заниматься всю ночь',
                '5',
                '40',
                '20',
                '5',
                'Попросить перенести',
                '-3',
                '15',
                '5',
                '1'
            ]
        ]);
    }

    public function headings(): array
    {
        return [
            'Название*',
            'Описание*',
            'Категория* (work/study/personal/health)',
            'Сложность* (1-5)',
            'Мин. уровень',
            'Влияние на стресс (-50 до +50)',
            'Награда опытом (1-100)',
            'Позиция (desktop/phone/tablet/tv/etc)',
            'Привязка к кастомизации (Character_1_1 и т.д.)',
            'Активна (1/0)',
            'Вариант 1: Текст*',
            'Вариант 1: Изм. стресса (-50 до +50)*',
            'Вариант 1: Опыт (0-100)*',
            'Вариант 1: Энергия (0-50)',
            'Вариант 1: Мин. уровень',
            'Вариант 2: Текст',
            'Вариант 2: Изм. стресса',
            'Вариант 2: Опыт',
            'Вариант 2: Энергия',
            'Вариант 2: Мин. уровень'
        ];
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
                $event->sheet->getDelegate()->getStyle('A1:T1')->getAlignment()->setWrapText(true);
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
            }
        ];
    }
}

