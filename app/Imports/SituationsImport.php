<?php

namespace App\Imports;

use App\Models\Situation;
use App\Models\SituationOption;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class SituationsImport implements ToCollection, WithHeadingRow
{
    protected $errors = [];
    protected $imported = 0;
    protected $skipped = 0;

    public function collection(Collection $rows)
    {
        foreach ($rows as $index => $row) {
            try {
                DB::beginTransaction();

                $rowNumber = $index + 2;

                if (empty($row['nazvanie']) || empty($row['opisanie'])) {
                    $this->skipped++;
                    $this->errors[] = "Строка {$rowNumber}: пропущена (отсутствуют обязательные поля)";
                    DB::rollBack();
                    continue;
                }

                $categoryMap = [
                    'work' => 'work',
                    'study' => 'study',
                    'personal' => 'personal',
                    'health' => 'health',
                    'работа' => 'work',
                    'учёба' => 'study',
                    'личное' => 'personal',
                    'здоровье' => 'health'
                ];

                $category = strtolower(trim($row['kategoriia_workstudypersonalhealth'] ?? 'personal'));
                $category = $categoryMap[$category] ?? 'personal';

                $validPositions = array_column(\App\Enums\Position::cases(), 'value');
                $position = trim($row['pozitsiia_desktopphoneta blettvets'] ?? 'desktop');
                $position = in_array($position, $validPositions) ? $position : 'desktop';

                $situation = Situation::create([
                    'title' => $row['nazvanie'],
                    'description' => $row['opisanie'],
                    'category' => $category,
                    'difficulty_level' => max(1, min(5, intval($row['sloznost_1_5'] ?? 1))),
                    'min_level_required' => max(1, intval($row['min_uroven'] ?? 1)),
                    'stress_impact' => max(-50, min(50, intval($row['vliianie_na_stress_50_do_50'] ?? 0))),
                    'experience_reward' => max(1, min(100, intval($row['nagrada_opytom_1_100'] ?? 10))),
                    'position' => $position,
                    'required_customization_key' => !empty($row['priviazka_k_kastomizatsii_character_1_1_i_td']) 
                        ? $row['priviazka_k_kastomizatsii_character_1_1_i_td'] 
                        : null,
                    'is_active' => !empty($row['aktivna_10']) && $row['aktivna_10'] == '1',
                ]);

                $optionsCreated = 0;
                for ($i = 1; $i <= 10; $i++) {
                    $textKey = "variant_{$i}_tekst";
                    $stressKey = "variant_{$i}_izm_stressa_50_do_50";
                    $expKey = "variant_{$i}_opyt_0_100";
                    $energyKey = "variant_{$i}_energiia_0_50";
                    $levelKey = "variant_{$i}_min_uroven";

                    if (!empty($row[$textKey])) {
                        SituationOption::create([
                            'situation_id' => $situation->id,
                            'text' => $row[$textKey],
                            'stress_change' => max(-50, min(50, intval($row[$stressKey] ?? 0))),
                            'experience_reward' => max(0, min(100, intval($row[$expKey] ?? 0))),
                            'energy_cost' => max(0, min(50, intval($row[$energyKey] ?? 0))),
                            'min_level_required' => max(1, intval($row[$levelKey] ?? 1)),
                            'order' => $optionsCreated + 1,
                        ]);
                        $optionsCreated++;
                    }
                }

                if ($optionsCreated === 0) {
                    $this->errors[] = "Строка {$rowNumber}: ситуация создана, но без вариантов действий";
                }

                $this->imported++;
                DB::commit();

            } catch (\Exception $e) {
                DB::rollBack();
                $this->skipped++;
                $this->errors[] = "Строка {$rowNumber}: {$e->getMessage()}";
                Log::error("Import error at row {$rowNumber}: " . $e->getMessage());
            }
        }
    }

    public function getImported(): int
    {
        return $this->imported;
    }

    public function getSkipped(): int
    {
        return $this->skipped;
    }

    public function getErrors(): array
    {
        return $this->errors;
    }
}

