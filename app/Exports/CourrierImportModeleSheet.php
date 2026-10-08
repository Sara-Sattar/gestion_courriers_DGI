<?php

namespace App\Exports;

use App\Models\CategorieCourrier;
use App\Models\TypeCourrier;
use App\Models\Unite;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

/**
 * Feuille de saisie du modèle d'import : en-têtes et une ligne d'exemple.
 */
class CourrierImportModeleSheet implements FromArray, ShouldAutoSize, WithHeadings, WithStyles, WithTitle
{
    public function title(): string
    {
        return 'Courriers';
    }

    /**
     * @return list<string>
     */
    public function headings(): array
    {
        return CourrierImportColonnes::entetes();
    }

    /**
     * @return list<list<string>>
     */
    public function array(): array
    {
        $categorie = CategorieCourrier::query()->where('actif', true)->orderBy('nom')->first();
        $type = TypeCourrier::query()
            ->where('actif', true)
            ->when($categorie, fn ($query) => $query->where('categorie_courrier_id', $categorie->id))
            ->orderBy('nom')
            ->first();
        $unite = Unite::query()->where('actif', true)->orderBy('code')->first();

        return [[
            'SOCIETE EXEMPLE SARL',
            '12345678',
            '',
            '001234567000089',
            '',
            '',
            '12 avenue Mohammed V',
            'Marrakech',
            '0524000000',
            'contact@exemple.ma',
            $categorie?->nom ?? 'Réclamation',
            $type?->nom ?? 'Réclamation contentieuse',
            'Réclamation relative à la taxe professionnelle',
            'Personne morale',
            $unite?->code ?? 'DR001',
            'IR, TVA',
            '2023, 2024',
            'Ligne d\'exemple : supprimez-la avant l\'import',
        ]];
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function styles(Worksheet $sheet): array
    {
        $sheet->getStyle('A1:R1')->getFill()
            ->setFillType(Fill::FILL_SOLID)
            ->getStartColor()->setRGB('0D6EFD');

        $sheet->getStyle('A1:R1')->getFont()
            ->setBold(true)
            ->getColor()->setRGB('FFFFFF');

        $sheet->freezePane('A2');

        return [
            2 => ['font' => ['italic' => true, 'color' => ['rgb' => '6C757D']]],
        ];
    }
}
