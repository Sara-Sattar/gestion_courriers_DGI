<?php

namespace Database\Seeders;

use App\Models\Contribuable;
use Illuminate\Database\Seeder;

class ContribuableSeeder extends Seeder
{
    public function run(): void
    {
        $contribuables = [
            ['nom_raison_sociale' => 'Alaoui Mohammed', 'ifu' => '123456789012345', 'cin' => 'AB123456', 'ice' => null, 'numero_tp' => null, 'numero_th' => 'TH-458921', 'adresse' => 'Quartier Gueliz, Avenue Mohammed V, N° 12', 'ville' => 'Marrakech', 'telephone' => '0661234567', 'email' => 'm.alaoui@example.com'],
            ['nom_raison_sociale' => 'Benjelloun Fatima', 'ifu' => '234567890123456', 'cin' => 'CD789012', 'ice' => null, 'numero_tp' => null, 'numero_th' => 'TH-782341', 'adresse' => 'Hay Riad, Rue des Oliviers, N° 45', 'ville' => 'Rabat', 'telephone' => '0672345678', 'email' => 'fatima.benjelloun@example.com'],
            ['nom_raison_sociale' => 'SARL Atlas Distribution', 'ifu' => '345678901234567', 'cin' => null, 'ice' => '001234567890123', 'numero_tp' => 'TP-10234', 'numero_th' => null, 'adresse' => 'Zone Industrielle Sidi Ghanem, Lot 28', 'ville' => 'Marrakech', 'telephone' => '0524432187', 'email' => 'contact@example.com'],
            ['nom_raison_sociale' => 'EURL Maroc Services Plus', 'ifu' => '456789012345678', 'cin' => null, 'ice' => '001987654321098', 'numero_tp' => 'TP-20567', 'numero_th' => null, 'adresse' => 'Boulevard Zerktouni, Immeuble Al Amal', 'ville' => 'Casablanca', 'telephone' => '0522987654', 'email' => 'info@example.com'],
            ['nom_raison_sociale' => 'Tazi Karim', 'ifu' => '567890123456789', 'cin' => 'EF345678', 'ice' => null, 'numero_tp' => null, 'numero_th' => 'TH-334512', 'adresse' => 'Hay Mohammadi, Rue Ibn Khaldoun, N° 8', 'ville' => 'Fès', 'telephone' => '0653456789', 'email' => 'karim.tazi@example.com'],
            ['nom_raison_sociale' => 'SA Immobilier du Sud', 'ifu' => '678901234567890', 'cin' => null, 'ice' => '001456789012345', 'numero_tp' => 'TP-30112', 'numero_th' => null, 'adresse' => 'Avenue Hassan II, Résidence Al Bahja', 'ville' => 'Agadir', 'telephone' => '0528334455', 'email' => 'direction@example.com'],
            ['nom_raison_sociale' => 'Idrissi Amina', 'ifu' => '789012345678901', 'cin' => 'GH901234', 'ice' => null, 'numero_tp' => null, 'numero_th' => 'TH-556789', 'adresse' => 'Quartier Médina, Derb Sidi Bel Abbès, N° 3', 'ville' => 'Meknès', 'telephone' => '0664567890', 'email' => 'amina.idrissi@example.com'],
            ['nom_raison_sociale' => 'SNC Transport Nord Express', 'ifu' => '890123456789012', 'cin' => null, 'ice' => '001321654987654', 'numero_tp' => 'TP-40889', 'numero_th' => null, 'adresse' => 'Route de Tanger, Km 5, Zone Logistique', 'ville' => 'Tanger', 'telephone' => '0539943210', 'email' => 'logistique@example.com'],
            ['nom_raison_sociale' => 'Chraibi Youssef', 'ifu' => '901234567890123', 'cin' => 'IJ567890', 'ice' => null, 'numero_tp' => null, 'numero_th' => 'TH-667234', 'adresse' => 'Hay Salam, Avenue Al Massira, N° 102', 'ville' => 'Oujda', 'telephone' => '0675678901', 'email' => 'youssef.chraibi@example.com'],
            ['nom_raison_sociale' => 'Groupe Ouarzazate Trading', 'ifu' => '012345678901234', 'cin' => null, 'ice' => '001789012345678', 'numero_tp' => 'TP-50234', 'numero_th' => null, 'adresse' => 'Avenue Moulay Abdellah, Complexe Commercial N° 7', 'ville' => 'Ouarzazate', 'telephone' => '0524887766', 'email' => 'commercial@example.com'],
        ];

        foreach ($contribuables as $data) {
            Contribuable::query()->updateOrCreate(
                ['ifu' => $data['ifu']],
                $data
            );
        }
    }
}
