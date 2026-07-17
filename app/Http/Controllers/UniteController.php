<?php

namespace App\Http\Controllers;

use App\Models\TypeCourrier;
use App\Models\Unite;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class UniteController extends Controller
{
    public function types(): JsonResponse
    {
        $types = Unite::query()
            ->actives()
            ->select('type_unite')
            ->distinct()
            ->orderBy('type_unite')
            ->pluck('type_unite');

        return response()->json($types);
    }

    public function parType(string $type): JsonResponse
    {
        $unites = Unite::query()
            ->actives()
            ->parType($type)
            ->orderBy('nom')
            ->get(['id', 'nom', 'code', 'type_unite']);

        return response()->json($unites);
    }

    public function typesCourrier(int $categorieId): JsonResponse
    {
        $types = TypeCourrier::query()
            ->actifs()
            ->where('categorie_courrier_id', $categorieId)
            ->orderBy('nom')
            ->get(['id', 'nom']);

        return response()->json($types);
    }
}
