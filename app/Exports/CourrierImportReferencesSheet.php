<?php

namespace App\Exports;

use App\Enums\TypeImpot;
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
 * Feuille de références : valeurs acceptées pour les colonnes contrôlées.
 */
class CourrierImportReferencesSheet implements FromArray, ShouldAutoSize, WithHeadings, WithStyles, WithTitle
{
    public function title(): string
    {
        return 'Références';
    }

    /**
     * @return list<string>
     */
    public function headings(): array
    {
        return ['Colonne', 'Valeur à saisir', 'Détail'];
    }

    /**
     * @return list<list<string>>
     */
    public function array(): array
    {
        $lignes = [];

        foreach (CategorieCourrier::query()->where('actif', true)->orderBy('nom')->get() as $categorie) {
            $lignes[] = ['Catégorie', $categorie->nom, $categorie->description ?: ''];
        }

        $types = TypeCourrier::query()
            ->where('actif', true)
            ->with('categorie')
            ->orderBy('nom')
            ->get();

        foreach ($types as $type) {
            $lignes[] = ['Type de courrier', $type->nom, 'Catégorie : '.($type->categorie?->nom ?? '')];
        }

        foreach (Unite::query()->where('actif', true)->orderBy('code')->get() as $unite) {
            $lignes[] = ['Code unité destinataire', $unite->code, $unite->nom.' ('.$unite->type_unite.')'];
        }

        foreach (TypeImpot::cases() as $impot) {
            $lignes[] = ['Impôts', $impot->value, $impot->libelle()];
        }

        $lignes[] = ['Années', '2023, 2024', 'Séparez plusieurs années par une virgule'];
        $lignes[] = ['Impôts', 'IR, TVA', 'Séparez plusieurs impôts par une virgule'];

        return $lignes;
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function styles(Worksheet $sheet): array
    {
        $sheet->getStyle('A1:C1')->getFill()
            ->setFillType(Fill::FILL_SOLID)
            ->getStartColor()->setRGB('0D6EFD');

        $sheet->getStyle('A1:C1')->getFont()
            ->setBold(true)
            ->getColor()->setRGB('FFFFFF');

        $sheet->freezePane('A2');

        return [];
    }
}
