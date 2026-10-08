<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Extensions conformes au cahier des charges — sans destruction de données.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bureaux_ordre', function (Blueprint $table) {
            $table->id();
            $table->string('code', 50)->unique();
            $table->string('libelle');
            $table->string('ville')->nullable();
            $table->foreignId('unite_id')->nullable()->constrained('unites')->nullOnDelete();
            $table->boolean('actif')->default(true);
            $table->timestamps();
        });

        Schema::table('users', function (Blueprint $table) {
            $table->string('ppr', 30)->nullable()->unique()->after('id');
            $table->string('prenom')->nullable()->after('name');
            $table->foreignId('bureau_ordre_id')
                ->nullable()
                ->after('unite_id')
                ->constrained('bureaux_ordre')
                ->nullOnDelete();
        });

        Schema::table('contribuables', function (Blueprint $table) {
            $table->string('type_contribuable', 40)->nullable()->after('nom_raison_sociale');
        });

        Schema::table('unites', function (Blueprint $table) {
            $table->string('ville')->nullable()->after('description');
            $table->boolean('est_bureau_ordre')->default(false)->after('actif');
        });

        Schema::table('courriers', function (Blueprint $table) {
            $table->boolean('necessite_reponse')->default(true)->after('objet');
            $table->timestamp('date_annulation')->nullable()->after('code_unite_depart');
            $table->text('motif_annulation')->nullable()->after('date_annulation');
            $table->foreignId('annule_par_id')
                ->nullable()
                ->after('motif_annulation')
                ->constrained('users')
                ->nullOnDelete();

            $table->string('mandataire_nom')->nullable()->after('mandataire_piece');
            $table->string('mandataire_prenom')->nullable()->after('mandataire_nom');
            $table->string('mandataire_cin', 50)->nullable()->after('mandataire_prenom');
            $table->date('date_procuration')->nullable()->after('mandataire_cin');
        });

        // Créer le BO Marrakech à partir de BO001 s'il existe.
        $boUnite = DB::table('unites')->where('code', 'BO001')->first();
        if ($boUnite) {
            DB::table('unites')->where('id', $boUnite->id)->update([
                'est_bureau_ordre' => true,
                'ville' => 'Marrakech',
                'updated_at' => now(),
            ]);

            $boId = DB::table('bureaux_ordre')->insertGetId([
                'code' => 'BO-MAR',
                'libelle' => $boUnite->nom,
                'ville' => 'Marrakech',
                'unite_id' => $boUnite->id,
                'actif' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            DB::table('users')
                ->where('unite_id', $boUnite->id)
                ->update(['bureau_ordre_id' => $boId]);
        }

        // Profils CDC Traitement / Consultation (en plus des rôles métier existants).
        foreach ([
            ['name' => 'Traitement', 'description' => 'Profil CDC — traitement des courriers'],
            ['name' => 'Consultation', 'description' => 'Profil CDC — consultation et statistiques'],
        ] as $role) {
            $exists = DB::table('roles')->where('name', $role['name'])->exists();
            if (! $exists) {
                DB::table('roles')->insert([
                    'name' => $role['name'],
                    'description' => $role['description'],
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
    }

    public function down(): void
    {
        Schema::table('courriers', function (Blueprint $table) {
            $table->dropConstrainedForeignId('annule_par_id');
            $table->dropColumn([
                'necessite_reponse',
                'date_annulation',
                'motif_annulation',
                'mandataire_nom',
                'mandataire_prenom',
                'mandataire_cin',
                'date_procuration',
            ]);
        });

        Schema::table('unites', function (Blueprint $table) {
            $table->dropColumn(['ville', 'est_bureau_ordre']);
        });

        Schema::table('contribuables', function (Blueprint $table) {
            $table->dropColumn('type_contribuable');
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropConstrainedForeignId('bureau_ordre_id');
            $table->dropColumn(['ppr', 'prenom']);
        });

        Schema::dropIfExists('bureaux_ordre');
    }
};
