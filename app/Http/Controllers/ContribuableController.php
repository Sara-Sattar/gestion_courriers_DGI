<?php

namespace App\Http\Controllers;

use App\Models\Contribuable;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ContribuableController extends Controller
{
    public function recherche(Request $request): JsonResponse
    {
        $terme = $request->string('q')->trim()->value();

        if (strlen($terme) < 2) {
            return response()->json([]);
        }

        $contribuables = Contribuable::query()
            ->recherche($terme)
            ->limit(10)
            ->get([
                'id',
                'nom_raison_sociale',
                'ifu',
                'cin',
                'ice',
                'numero_tp',
                'numero_th',
                'adresse',
                'ville',
                'telephone',
                'email',
            ]);

        return response()->json($contribuables);
    }

    public function show(Contribuable $contribuable): JsonResponse
    {
        return response()->json($contribuable);
    }
}
