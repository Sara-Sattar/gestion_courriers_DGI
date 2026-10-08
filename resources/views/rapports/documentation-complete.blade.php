<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <title>Documentation complète — Gestion des courriers DGI</title>
    <style>
        @@page { margin: 1.8cm 1.6cm 1.8cm 1.6cm; }
        body { font-family: DejaVu Sans, sans-serif; font-size: 10pt; line-height: 1.45; color: #1a1a1a; }
        h1 { font-size: 16pt; text-align: center; text-transform: uppercase; color: #0d47a1; margin: 0 0 0.8em; }
        h2 { font-size: 13pt; color: #0d47a1; margin: 1.2em 0 0.45em; border-bottom: 1px solid #c5d4eb; padding-bottom: 0.15em; }
        h3 { font-size: 11pt; color: #1565c0; margin: 0.95em 0 0.35em; }
        h4 { font-size: 10.5pt; color: #1a237e; margin: 0.8em 0 0.3em; }
        p { margin: 0 0 0.55em; text-align: justify; }
        ul, ol { margin: 0.2em 0 0.7em 1.2em; padding: 0; }
        li { margin-bottom: 0.22em; }
        .page { page-break-after: always; }
        .page-last { page-break-after: auto; }
        .cover { text-align: center; padding-top: 1.2cm; }
        .cover .etab { font-size: 11pt; font-weight: bold; text-transform: uppercase; }
        .cover .titre { font-size: 14.5pt; font-weight: bold; color: #0d47a1; margin: 1.2cm 0.8cm; line-height: 1.35; }
        .muted { color: #555; }
        .small { font-size: 8.5pt; }
        .bold { font-weight: bold; }
        .box { border: 1px solid #bbb; padding: 0.4cm 0.5cm; margin: 0.6em 0 0.9em; background: #f8f9fb; font-size: 9.5pt; }
        table.doc { width: 100%; border-collapse: collapse; margin: 0.45em 0 0.9em; font-size: 8.6pt; }
        table.doc th, table.doc td { border: 1px solid #ccc; padding: 0.28em 0.35em; text-align: left; vertical-align: top; }
        table.doc th { background: #e8eef8; color: #0d47a1; }
        .toc-line { margin: 0.22em 0; }
        .toc-h2 { font-weight: bold; margin-top: 0.45em; }
        .toc-h3 { margin-left: 1em; font-size: 9.5pt; }
        .caption { font-size: 8.5pt; font-style: italic; text-align: center; margin: 0.15em 0 0.8em; color: #333; }
        .badge { font-weight: bold; }
        code { font-size: 8.5pt; }
    </style>
</head>
<body>

{{-- ===================== COUVERTURE ===================== --}}
<div class="page cover">
    <div class="etab">Royaume du Maroc</div>
    <div class="etab">Direction Générale des Impôts</div>
    <p class="muted">Direction Régionale des Impôts de Marrakech</p>
    @if (! empty($logoPath) && file_exists($logoPath))
        <p><img src="{{ $logoPath }}" alt="Logo DGI" style="height: 68px;"></p>
    @endif
    <div style="font-size: 12pt; font-weight: bold; letter-spacing: 0.08em; text-transform: uppercase; margin: 1cm 0 0.6cm;">
        Documentation fonctionnelle complète
    </div>
    <div class="titre">
        Application web de gestion du cycle de vie des courriers<br>
        gestion_DGI2
    </div>
    <div class="box" style="text-align: left; display: inline-block; width: 85%;">
        <strong>Objet :</strong> description détaillée de l’ensemble de l’application
        (comptes, pages, menus, fonctionnalités, règles métier).<br>
        <strong>Périmètre :</strong> version livrée du projet (Laravel 12 / PHP 8 / MySQL).<br>
        <strong>Public :</strong> encadrants, recette métier, prise en main des profils de démonstration.<br>
        <strong>Date :</strong> {{ now()->locale('fr')->translatedFormat('d F Y') }}
    </div>
    <p class="muted small" style="margin-top: 1.4cm;">
        Document généré à partir du code source. Les comptes indiqués sont ceux du jeu de données de démonstration.
    </p>
</div>

{{-- ===================== SOMMAIRE ===================== --}}
<div class="page">
    <h1>Sommaire</h1>
    <p class="toc-line toc-h2">1. Présentation générale de l’application</p>
    <p class="toc-line toc-h3">1.1 Objet et cycle de vie</p>
    <p class="toc-line toc-h3">1.2 Accès technique</p>
    <p class="toc-line toc-h3">1.3 Stack et architecture logique</p>
    <p class="toc-line toc-h2">2. Comptes de démonstration</p>
    <p class="toc-line toc-h3">2.1 Mot de passe commun</p>
    <p class="toc-line toc-h3">2.2 Liste exhaustive des comptes</p>
    <p class="toc-line toc-h3">2.3 Qui utiliser pour quelle recette</p>
    <p class="toc-line toc-h2">3. Profils, permissions et menus</p>
    <p class="toc-line toc-h2">4. Unités, catégories, types et contribuables</p>
    <p class="toc-line toc-h2">5. Catalogue de toutes les pages</p>
    <p class="toc-line toc-h2">6. Authentification, profil et espace de travail</p>
    <p class="toc-line toc-h2">7. Accueil (tableau de bord)</p>
    <p class="toc-line toc-h2">8. Enregistrement d’un courrier (dépôt)</p>
    <p class="toc-line toc-h2">9. Réception, refus et réaffectation</p>
    <p class="toc-line toc-h2">10. Traitement</p>
    <p class="toc-line toc-h2">11. Délivrance</p>
    <p class="toc-line toc-h2">12. Fiche courrier, reçu et pièces jointes</p>
    <p class="toc-line toc-h2">13. Statistiques et exports</p>
    <p class="toc-line toc-h2">14. Délégations</p>
    <p class="toc-line toc-h2">15. Administration (utilisateurs et référentiels)</p>
    <p class="toc-line toc-h2">16. Statuts, historique et règles métier</p>
    <p class="toc-line toc-h2">17. Synthèse « qui voit quoi »</p>
</div>

{{-- ===================== 1 ===================== --}}
<div class="page">
    <h1>1. Présentation générale de l’application</h1>

    <h2>1.1 Objet et cycle de vie</h2>
    <p>
        <strong>gestion_DGI2</strong> est une application intranet destinée à la Direction Régionale des Impôts
        de Marrakech. Elle gère le courrier administratif des contribuables, de l’enregistrement au guichet
        jusqu’à la clôture, en passant par la réception en unité, l’instruction et la délivrance éventuelle
        d’un document.
    </p>
    <div class="box">
        Dépôt (Accueil / Bureau d’ordre)
        → Réception (unité destinataire)
        → Réaffectation éventuelle
        → Traitement (favorable / rejet / suspension)
        → Délivrance (Accueil) si document
        → Clôture / consultation statistique
    </div>
    <p class="caption">Figure 1 — Circuit métier du courrier</p>
    <p>
        L’application n’est pas un simple registre : chaque action est historisée, les écrans dépendent des
        <strong>permissions</strong> du profil, et la visibilité des dossiers est limitée au
        <strong>périmètre</strong> de l’unité (hiérarchie + responsabilité).
    </p>
    <p>
        Point d’organisation important : le profil <strong>Accueil</strong> enregistre et délivre.
        Il n’a <strong>ni</strong> le menu Réception <strong>ni</strong> le menu Traitement
        (réservés aux agents des unités métier).
    </p>

    <h2>1.2 Accès technique</h2>
    <table class="doc">
        <tr><th style="width:32%;">Élément</th><th>Valeur</th></tr>
        <tr><td>Application</td><td>gestion_DGI2 (Laravel 12)</td></tr>
        <tr><td>URL XAMPP (typique)</td><td>http://localhost/gestion_DGI2/public</td></tr>
        <tr><td>URL artisan serve</td><td>http://127.0.0.1:8000</td></tr>
        <tr><td>Base de données</td><td>MySQL — schéma gestion_dgi2 (fichier .env)</td></tr>
        <tr><td>Page d’entrée</td><td>« / » redirige vers /dashboard ; si non connecté → /login</td></tr>
        <tr><td>Langue interface</td><td>Français</td></tr>
    </table>

    <h2>1.3 Stack et architecture logique</h2>
    <ul>
        <li><strong>Backend :</strong> Laravel 12, PHP 8, Eloquent, Gates / policies, services métier (enregistrement, réception, traitement, délivrance, import, délégation).</li>
        <li><strong>Frontend :</strong> Blade, Bootstrap 5, CSS spécifiques (dashboard, formulaires, navbar DGI, etc.).</li>
        <li><strong>PDF :</strong> reçus de dépôt, exports statistiques, rapports (DomPDF).</li>
        <li><strong>Excel :</strong> modèle d’import, import de courriers, export statistiques (Maatwebsite Excel).</li>
    </ul>
</div>

{{-- ===================== 2 COMPTES ===================== --}}
<div class="page">
    <h1>2. Comptes de démonstration</h1>

    <h2>2.1 Mot de passe commun</h2>
    <div class="box">
        Tous les comptes du seeder ont le mot de passe : <strong>password</strong><br>
        Politique à la création / modification : au moins 8 caractères, majuscule, minuscule, chiffre et symbole.
        Le mot de passe de démo « password » est accepté pour les comptes déjà seedés ; il ne convient pas en production.
    </div>

    <h2>2.2 Liste exhaustive des comptes (UserSeeder)</h2>
    <table class="doc">
        <tr>
            <th>Nom affiché</th>
            <th>E-mail (login)</th>
            <th>Profil</th>
            <th>Unité</th>
        </tr>
        <tr><td>Admin System</td><td>admin@@dgi.ma</td><td>Admin</td><td>DR001 — Direction Régionale</td></tr>
        <tr><td>Agent Accueil</td><td>accueil@@dgi.ma</td><td>Accueil</td><td>BO001 — Bureau d’ordre</td></tr>
        <tr><td>Chef de Service IT</td><td>service.it@@dgi.ma</td><td>Service</td><td>SERV002 — Service IT</td></tr>
        <tr><td>Chef de Brigade</td><td>brigade@@dgi.ma</td><td>Brigade</td><td>BRIG001 — Brigade de vérification</td></tr>
        <tr><td>Chef de Bureau</td><td>bureau@@dgi.ma</td><td>Bureau</td><td>BO001 — Bureau d’ordre</td></tr>
        <tr><td>Chef de Secteur Guéliz</td><td>secteur.gueliz@@dgi.ma</td><td>Secteur</td><td>SECT001 — Secteur fiscal Guéliz</td></tr>
        <tr><td>Chef Bureau Recouvrement</td><td>recouvrement@@dgi.ma</td><td>Bureau</td><td>BUR003 — Bureau de recouvrement</td></tr>
        <tr><td>Agent Bureau 1</td><td>agent.bureau1@@dgi.ma</td><td>Traitement</td><td>BO001</td></tr>
        <tr><td>Agent Bureau 2</td><td>agent.bureau2@@dgi.ma</td><td>Traitement</td><td>BO001</td></tr>
        <tr><td>Agent Service 1</td><td>agent.service1@@dgi.ma</td><td>Traitement</td><td>SERV002</td></tr>
        <tr><td>Agent Brigade 1</td><td>agent.brigade1@@dgi.ma</td><td>Traitement</td><td>BRIG001</td></tr>
        <tr><td>Agent Section 1</td><td>agent.section1@@dgi.ma</td><td>Traitement</td><td>SERV002</td></tr>
    </table>
    <p class="small muted">L’ancien compte dr@@dgi.ma a été migré vers accueil@@dgi.ma (rôle Accueil, plus Directeur).</p>

    <h2>2.3 Qui utiliser pour quelle recette</h2>
    <table class="doc">
        <tr><th>Objectif de test</th><th>Compte recommandé</th></tr>
        <tr><td>Dépôt, reçu PDF, import Excel, délivrance, stats Accueil</td><td>accueil@@dgi.ma</td></tr>
        <tr><td>Réception et traitement au bureau d’ordre</td><td>agent.bureau1@@dgi.ma</td></tr>
        <tr><td>Délégation (créer / modifier) — chef d’unité</td><td>bureau@@dgi.ma ou service.it@@dgi.ma</td></tr>
        <tr><td>Agir en remplacement (si délégation active)</td><td>agent.bureau2@@dgi.ma (remplaçant typique des tests)</td></tr>
        <tr><td>Utilisateurs, permissions, catégories, types, unités</td><td>admin@@dgi.ma</td></tr>
        <tr><td>Vérifier qu’un agent d’une autre unité ne voit pas hors périmètre</td><td>agent.service1@@dgi.ma vs agent.bureau1@@dgi.ma</td></tr>
    </table>
    <p>
        Le profil <strong>Consultation</strong> existe dans le catalogue des rôles (statistiques, sans circuit métier)
        mais aucun compte Consultation n’est créé par défaut dans le seeder : l’administrateur peut en créer un via
        Gestion des utilisateurs.
    </p>
</div>

{{-- ===================== 3 PERMISSIONS ===================== --}}
<div class="page">
    <h1>3. Profils, permissions et menus</h1>
    <p>
        Les droits sont définis dans <strong>config/permissions.php</strong>, assignés aux rôles au seed,
        et peuvent être affinés par utilisateur. Les menus de la barre de navigation s’affichent via
        <code>@@can('menu.…')</code>. L’administrateur n’a pas le circuit courrier.
    </p>

    <h2>3.1 Catalogue des permissions</h2>
    <table class="doc">
        <tr><th>Code</th><th>Libellé</th><th>Groupe</th></tr>
        <tr><td>menu.dashboard</td><td>Accueil</td><td>Menus</td></tr>
        <tr><td>menu.enregistrer_courrier</td><td>Enregistrer un courrier</td><td>Menus</td></tr>
        <tr><td>menu.reception</td><td>Réception</td><td>Menus</td></tr>
        <tr><td>menu.traitement</td><td>Traitement</td><td>Menus</td></tr>
        <tr><td>menu.delivrance</td><td>Délivrance</td><td>Menus</td></tr>
        <tr><td>menu.consultation</td><td>Statistiques</td><td>Menus</td></tr>
        <tr><td>menu.utilisateurs</td><td>Gestion des utilisateurs</td><td>Menus</td></tr>
        <tr><td>menu.referentiels</td><td>Référentiels</td><td>Menus</td></tr>
        <tr><td>courriers.creer</td><td>Créer / importer un courrier</td><td>Courriers</td></tr>
        <tr><td>courriers.receptionner</td><td>Confirmer une réception</td><td>Courriers</td></tr>
        <tr><td>courriers.annuler_reception</td><td>Annuler une réception</td><td>Courriers</td></tr>
        <tr><td>courriers.reaffecter</td><td>Réaffecter un courrier</td><td>Courriers</td></tr>
        <tr><td>courriers.delivrer</td><td>Délivrer un document (Accueil)</td><td>Courriers</td></tr>
        <tr><td>courriers.annuler_depot</td><td>Annuler un dépôt</td><td>Courriers</td></tr>
        <tr><td>courriers.refuser_reception</td><td>Refuser une réception (retour B.O.)</td><td>Courriers</td></tr>
        <tr><td>stats.exporter</td><td>Exporter les statistiques</td><td>Courriers</td></tr>
        <tr><td>delegations.voir / creer / modifier / annuler</td><td>Gestion des délégations</td><td>Délégations</td></tr>
        <tr><td>users.gerer</td><td>Gérer les utilisateurs</td><td>Administration</td></tr>
        <tr><td>categories.gerer</td><td>Gérer les catégories</td><td>Administration</td></tr>
        <tr><td>types_courrier.gerer</td><td>Gérer les types de courrier</td><td>Administration</td></tr>
        <tr><td>unites.gerer</td><td>Gérer les unités</td><td>Administration</td></tr>
    </table>

    <h2>3.2 Matrice profils × fonctions</h2>
    <table class="doc">
        <tr>
            <th>Fonction</th>
            <th>Admin</th>
            <th>Accueil</th>
            <th>Traitement</th>
            <th>Consultation</th>
            <th>Chefs (Service, Brigade, Bureau, Section, Secteur)</th>
        </tr>
        <tr><td>Accueil (dashboard)</td><td>oui</td><td>oui</td><td>oui</td><td>oui</td><td>oui</td></tr>
        <tr><td>Enregistrer / importer</td><td>—</td><td>oui</td><td>—</td><td>—</td><td>—</td></tr>
        <tr><td>Délivrance</td><td>—</td><td>oui</td><td>—</td><td>—</td><td>—</td></tr>
        <tr><td>Annuler un dépôt</td><td>—</td><td>oui</td><td>—</td><td>—</td><td>—</td></tr>
        <tr><td>Réception / refus / réaffectation</td><td>—</td><td>—</td><td>oui</td><td>—</td><td>oui</td></tr>
        <tr><td>Traitement / reprise</td><td>—</td><td>—</td><td>oui</td><td>—</td><td>oui</td></tr>
        <tr><td>Statistiques</td><td>—</td><td>oui</td><td>oui</td><td>oui</td><td>oui</td></tr>
        <tr><td>Export stats</td><td>—</td><td>oui</td><td>—</td><td>oui</td><td>—</td></tr>
        <tr><td>Délégations</td><td>—</td><td>—</td><td>—</td><td>—</td><td>oui</td></tr>
        <tr><td>Utilisateurs / référentiels</td><td>oui</td><td>—</td><td>—</td><td>—</td><td>—</td></tr>
    </table>
    <p class="small">Les chefs d’unité ont le circuit métier (comme Traitement) plus les délégations. L’Accueil n’a pas les délégations.</p>
</div>

{{-- ===================== 4 REFERENTIELS ===================== --}}
<div class="page">
    <h1>4. Unités, catégories, types et contribuables</h1>

    <h2>4.1 Unités structurelles seedées</h2>
    <table class="doc">
        <tr><th>Code</th><th>Nom</th><th>Type</th><th>Parent</th><th>Particularité</th></tr>
        <tr><td>DR001</td><td>Direction Régionale des Impôts de Marrakech</td><td>DR</td><td>—</td><td>Racine</td></tr>
        <tr><td>BO001</td><td>Bureau d’ordre</td><td>Bureau</td><td>DR001</td><td>Bureau d’ordre / Accueil (ville Marrakech)</td></tr>
        <tr><td>SERV002</td><td>Service IT</td><td>Service</td><td>DR001</td><td>—</td></tr>
        <tr><td>BRIG001</td><td>Brigade de vérification</td><td>Brigade</td><td>DR001</td><td>—</td></tr>
        <tr><td>SECT001</td><td>Secteur fiscal Guéliz</td><td>Secteur</td><td>SERV002</td><td>—</td></tr>
        <tr><td>BUR003</td><td>Bureau de recouvrement</td><td>Bureau</td><td>BO001</td><td>Unité fille du B.O.</td></tr>
    </table>
    <p>Le code Accueil pour la file de délivrance est paramétré : <strong>BO001</strong> (config dgi.unite_accueil_code).</p>

    <h2>4.2 Catégories et types de courrier (seeder)</h2>
    <table class="doc">
        <tr><th>Catégorie</th><th>Types</th></tr>
        <tr><td>Attestation</td><td>Attestation d’inscription à la TP ; Attestation de régularité fiscale</td></tr>
        <tr><td>Réclamation</td><td>Réclamation sur avis d’imposition ; Réclamation sur pénalités</td></tr>
        <tr><td>Demande</td><td>Demande de remise gracieuse ; Demande de dégrèvement</td></tr>
        <tr><td>Recours</td><td>Recours gracieux ; Recours hiérarchique</td></tr>
    </table>
    <p>L’administrateur peut créer, modifier, activer / désactiver des catégories et types. Le formulaire de dépôt n’affiche que les éléments <strong>actifs</strong>.</p>

    <h2>4.3 Types de contribuable</h2>
    <p>Physique professionnel ; Physique particulier ; Personne morale ; Autre.</p>
    <p>Identifiants possibles : IFU, CIN, ICE, n° TP, n° TH, adresse, ville, téléphone, e-mail, nom / raison sociale.</p>

    <h2>4.4 Impôts (années concernées)</h2>
    <p>IR, IS, TVA, TP, TSC, TH, DE, DT, TSAVA, CSS, TL — sélection multi-années de 1990 à l’année en cours.</p>
</div>

{{-- ===================== 5 PAGES ===================== --}}
<div class="page">
    <h1>5. Catalogue de toutes les pages</h1>
    <p>Liste des écrans utilisateur (hors API JSON internes). Les POST ne sont pas des « pages » mais des actions déclenchées depuis ces écrans.</p>

    <h2>5.1 Pages publiques (invité)</h2>
    <table class="doc">
        <tr><th>URL</th><th>Nom de route</th><th>Écran</th></tr>
        <tr><td>/login</td><td>login</td><td>Connexion (e-mail + mot de passe)</td></tr>
        <tr><td>/register</td><td>register</td><td>Inscription (route Breeze conservée ; usage interne / tests)</td></tr>
    </table>
    <p>La réinitialisation de mot de passe par e-mail a été retirée du projet (pas de page mot de passe oublié).</p>

    <h2>5.2 Pages authentifiées — métier</h2>
    <table class="doc">
        <tr><th>URL</th><th>Écran</th><th>Permission typique</th></tr>
        <tr><td>/dashboard</td><td>Accueil : cartes stats + liste des courriers du périmètre</td><td>menu.dashboard</td></tr>
        <tr><td>/courriers/create</td><td>Enregistrer un courrier (onglets saisie / import Excel)</td><td>courriers.creer</td></tr>
        <tr><td>/courriers/{id}</td><td>Fiche détail + suivi + historique</td><td>voir le courrier (périmètre)</td></tr>
        <tr><td>/courriers/{id}/recu</td><td>Aperçu / impression du reçu de dépôt (2 exemplaires)</td><td>dépôt</td></tr>
        <tr><td>/reception</td><td>File de réception (Déposé / Réaffecté)</td><td>menu.reception</td></tr>
        <tr><td>/traitement</td><td>File de traitement + historique personnel</td><td>menu.traitement</td></tr>
        <tr><td>/courriers/{id}/traitement</td><td>Formulaire de résultat (favorable / rejet / suspendu)</td><td>responsable du dossier</td></tr>
        <tr><td>/delivrance</td><td>File des documents à délivrer</td><td>menu.delivrance</td></tr>
        <tr><td>/courriers/{id}/delivrance</td><td>Formulaire de délivrance / clôture</td><td>courriers.delivrer</td></tr>
        <tr><td>/consultation</td><td>Statistiques multi-axes</td><td>menu.consultation</td></tr>
        <tr><td>/consultation/export</td><td>Téléchargement CSV / Excel / JSON / PDF</td><td>stats.exporter</td></tr>
    </table>

    <h2>5.3 Pages authentifiées — administration et compte</h2>
    <table class="doc">
        <tr><th>URL</th><th>Écran</th></tr>
        <tr><td>/users, /users/create, /users/{id}, /users/{id}/edit</td><td>Liste, création, fiche, édition utilisateur + permissions</td></tr>
        <tr><td>/categories, /create, /{id}/edit</td><td>Catégories de courrier</td></tr>
        <tr><td>/types-courrier, /create, /{id}/edit</td><td>Types de courrier</td></tr>
        <tr><td>/unites, /create, /{id}/edit</td><td>Unités destinataires (hiérarchie, codes, bureau d’ordre)</td></tr>
        <tr><td>/delegations, /create, /{id}/edit</td><td>Délégations de remplacement</td></tr>
        <tr><td>/profile</td><td>Profil : identité + mot de passe</td></tr>
    </table>

    <h2>5.4 Éléments d’interface non « pages »</h2>
    <ul>
        <li><strong>Sélecteur d’espace</strong> dans la navbar si l’utilisateur est remplaçant d’une délégation active.</li>
        <li><strong>Recherche contribuable</strong> (AJAX) depuis le formulaire de dépôt : /contribuables/recherche.</li>
        <li><strong>Listes chaînées</strong> : types selon catégorie, unités selon type, code suivant, etc.</li>
        <li><strong>Pièces jointes</strong> : affichage et téléchargement depuis la fiche.</li>
        <li>Téléchargement du <strong>modèle Excel</strong> d’import : /courriers/modele-import.</li>
    </ul>
</div>

{{-- ===================== 6 AUTH ===================== --}}
<div class="page">
    <h1>6. Authentification, profil et espace de travail</h1>

    <h2>6.1 Page de connexion</h2>
    <p>
        URL <strong>/login</strong>. Champs : e-mail, mot de passe, option « se souvenir de moi ».
        Habillage DGI (logo, couleurs). Après succès : redirection vers l’Accueil (/dashboard).
        Un utilisateur sans session qui vise une page protégée est renvoyé sur /login.
    </p>

    <h2>6.2 Profil</h2>
    <p>
        Menu utilisateur (haut droite) → <strong>Profil</strong>. Deux blocs : informations du compte
        (nom, e-mail) et changement de mot de passe (actuel + nouveau, avec politique de complexité).
        L’unité et le rôle sont affichés (non modifiables par l’agent lui-même : c’est l’admin qui les gère).
    </p>
    <p>Menu <strong>Déconnexion</strong> : POST /logout, retour à l’écran de login.</p>

    <h2>6.3 Espace de travail (délégation)</h2>
    <p>
        Si l’utilisateur connecté est <strong>remplaçant</strong> d’au moins une délégation en cours,
        la navbar affiche « Espace actif ». Il peut choisir :
    </p>
    <ul>
        <li><strong>Mon espace</strong> — il agit en son nom, sur son unité ;</li>
        <li><strong>Espace de l’agent absent</strong> — il agit dans le périmètre du remplacé.
            L’historique conserve l’acteur réel et le champ « pour le compte de ».</li>
    </ul>
    <p>L’Accueil et l’Admin n’ont pas le module délégations. Les chefs d’unité (Bureau, Service, Brigade, Section, Secteur) l’ont.</p>
</div>

{{-- ===================== 7 ACCUEIL ===================== --}}
<div class="page">
    <h1>7. Accueil (tableau de bord)</h1>
    <p>
        Page <strong>/dashboard</strong>. C’est l’écran d’atterrissage. Il n’affiche pas tout le parc national :
        uniquement le <strong>périmètre</strong> de l’utilisateur (unité, filles, dossiers dont il est responsable,
        files Déposé/Réaffecté de la hiérarchie — même logique que les cartes).
    </p>

    <h2>7.1 Cartes cliquables</h2>
    <table class="doc">
        <tr><th>Carte</th><th>Signification</th><th>Filtre appliqué à la liste</th></tr>
        <tr><td>Total courriers</td><td>Ensemble du périmètre Accueil</td><td>aucun</td></tr>
        <tr><td>Entrants</td><td>À traiter dans le périmètre</td><td>statuts Déposé, Réaffecté, Réceptionné, Suspendu</td></tr>
        <tr><td>Sortants</td><td>Traités / clôturés / archivés</td><td>Traité favorable, Rejeté, Clôturé, Archivé</td></tr>
        <tr><td>Aujourd’hui</td><td>Dépôts du jour</td><td>date_depot = aujourd’hui</td></tr>
        <tr><td>Ce mois</td><td>Dépôts du mois civil</td><td>mois / année courants</td></tr>
    </table>

    <h2>7.2 Liste</h2>
    <ul>
        <li>Pagination (8 lignes), recherche (numéro, objet, contribuable, catégorie).</li>
        <li>Filtre par statut, tri récent / ancien.</li>
        <li>Pour l’Accueil : bouton / accès <strong>Nouveau courrier</strong> (si droit creer).</li>
        <li>Clic sur un dossier → fiche /courriers/{id}.</li>
        <li>Rafraîchissement AJAX du tableau lors des filtres (partial _table).</li>
    </ul>
</div>

{{-- ===================== 8 DEPOT ===================== --}}
<div class="page">
    <h1>8. Enregistrement d’un courrier (dépôt)</h1>
    <p>Page <strong>/courriers/create</strong> — profil Accueil. Deux onglets.</p>

    <h2>8.1 Saisie manuelle</h2>
    <p>Champs principaux :</p>
    <ul>
        <li><strong>Contribuable :</strong> recherche (IFU, CIN, ICE, nom) avec remplissage automatique, ou saisie manuelle (type, identifiants, coordonnées).</li>
        <li><strong>Catégorie</strong> puis <strong>type</strong> (liste filtrée par catégorie, AJAX).</li>
        <li><strong>Objet</strong> (texte libre obligatoire).</li>
        <li><strong>Nécessitant une réponse :</strong> oui → statut initial Déposé ; non → classement / archivage selon la règle d’enregistrement.</li>
        <li><strong>Type d’unité + unité destinataire</strong> (listes chaînées).</li>
        <li><strong>Années concernées</strong> et <strong>types d’impôt</strong> (IR, IS, TVA, TP, etc.).</li>
        <li><strong>Priorité</strong>, pièce jointe (document Word / formats acceptés selon validation).</li>
    </ul>
    <p>
        À l’enregistrement, le système attribue un <strong>numéro unique</strong> au format
        AAAA-NNNNNN-CODE (ex. 2026-000007-BO001) : année, séquence propre à l’unité destinataire, code de l’unité.
        Statut <strong>Déposé</strong>. Redirection vers le <strong>reçu PDF</strong> (deux exemplaires A4 :
        contribuable et administration).
    </p>

    <h2>8.2 Import Excel</h2>
    <ul>
        <li>Téléchargement d’un <strong>modèle</strong> (feuille de saisie + feuille de références : catégories, types, unités).</li>
        <li>Upload du fichier ; contrôle ligne à ligne ; rapport : créés / ignorés / erreurs.</li>
        <li>Les lignes invalides n’empêchent pas nécessairement tout le lot (selon le service d’import) : un compte-rendu est affiché.</li>
    </ul>

    <h2>8.3 Annulation de dépôt</h2>
    <p>
        Depuis la fiche, l’Accueil peut <strong>annuler le dépôt</strong> avec motif (droit courriers.annuler_depot),
        tant que les règles de statut le permettent. Le courrier passe à <strong>Annulé</strong> ; l’action est historisée.
    </p>
</div>

{{-- ===================== 9 RECEPTION ===================== --}}
<div class="page">
    <h1>9. Réception, refus et réaffectation</h1>
    <p>Page <strong>/reception</strong> — agents métier (pas l’Accueil). File des courriers <strong>Déposé</strong> ou <strong>Réaffecté</strong> destinés à l’unité (ou au responsable).</p>

    <h3>Réceptionner</h3>
    <p>
        Confirmation individuelle ou groupée. Le statut devient <strong>Réceptionné</strong>.
        L’agent qui confirme devient <strong>responsable actuel</strong>. Date et heure de réception sont enregistrées.
        Historique : action « Réception ».
    </p>

    <h3>Annuler une réception</h3>
    <p>Possible selon le droit courriers.annuler_reception, tant que le dossier n’est pas déjà traité / bloqué. Retour vers un état d’attente de réception.</p>

    <h3>Refuser une réception</h3>
    <p>Motif obligatoire. Retour vers le Bureau d’ordre. Historique « Refus de réception ».</p>

    <h3>Réaffecter</h3>
    <p>
        Transmission vers une autre unité autorisée (hiérarchie). Motif enregistré.
        Statut <strong>Réaffecté</strong> : l’unité cible doit à nouveau confirmer la réception.
        Un refus de réaffectation est également prévu.
    </p>
    <p class="box">
        Règle : réception et réaffectation sont <strong>bloquées</strong> si le courrier est déjà
        traité favorablement, rejeté, suspendu, clôturé, archivé ou annulé.
    </p>
</div>

{{-- ===================== 10 TRAITEMENT ===================== --}}
<div class="page">
    <h1>10. Traitement</h1>
    <p>Page <strong>/traitement</strong> — file des dossiers <strong>Réceptionné</strong> et <strong>Suspendu</strong> dont l’utilisateur (ou le sujet de délégation) est responsable.</p>

    <h2>10.1 Traiter</h2>
    <p>Page /courriers/{id}/traitement. <strong>Seul le responsable</strong> peut valider. Trois résultats :</p>
    <table class="doc">
        <tr><th>Résultat</th><th>Statut cible</th><th>Effet</th></tr>
        <tr><td>Favorable</td><td>Traité favorable</td><td>Si document généré : retour Accueil (BO001) pour délivrance. Date de traitement, agent traiteur.</td></tr>
        <tr><td>Rejet</td><td>Rejeté</td><td>Motif de rejet obligatoire. Retour Accueil selon le workflow.</td></tr>
        <tr><td>Suspendu</td><td>Suspendu (en cours de traitement)</td><td>Motif de suspension. Le dossier reste dans la file du responsable ; il n’est pas « traité » tant qu’il n’est pas repris.</td></tr>
    </table>
    <p>Un dossier déjà suspendu ne peut pas être « re-traité » directement : message d’erreur métier, il faut d’abord <strong>Reprendre</strong>.</p>

    <h2>10.2 Reprendre</h2>
    <p>Remet le statut à <strong>Réceptionné</strong>. Le dossier réapparaît comme traitable. Historique : « Reprise ».</p>

    <h2>10.3 Historique de mes traitements</h2>
    <p>
        Sur la page Traitement, une zone liste les dossiers déjà instruits par l’agent
        (traite_par_id + statuts d’historique : favorable, rejet, suspendu, clôturé, archivé…).
        Pas de second système d’historique : on réutilise CourrierHistorique / les champs du courrier.
    </p>
</div>

{{-- ===================== 11 DELIVRANCE ===================== --}}
<div class="page">
    <h1>11. Délivrance</h1>
    <p>Page <strong>/delivrance</strong> — Accueil uniquement. File des courriers <strong>Traité favorable</strong> revenus à l’unité Accueil (BO001).</p>

    <h2>11.1 Délivrer</h2>
    <p>Formulaire /courriers/{id}/delivrance :</p>
    <ul>
        <li>Date de délivrance ;</li>
        <li><strong>Mode de livraison :</strong> livré à l’intéressé, mandataire, envoi postal ;</li>
        <li>En cas de mandataire : identité / pièces selon les champs CDC ;</li>
        <li>Attribution automatique d’un <strong>numéro de départ</strong> ;</li>
        <li>Statut final <strong>Clôturé</strong>.</li>
    </ul>

    <h2>11.2 Clôturer sans document</h2>
    <p>Si le traitement favorable n’a pas généré de document à remettre, l’Accueil peut clôturer sans délivrance physique. Historique : Clôture.</p>
</div>

{{-- ===================== 12 FICHE ===================== --}}
<div class="page">
    <h1>12. Fiche courrier, reçu et pièces jointes</h1>
    <p>Page <strong>/courriers/{id}</strong>. Accessible dans le périmètre (Gate / policy). Contenu type :</p>
    <ul>
        <li>Numéro, statut (badge couleur), dates (dépôt, réception, traitement, délivrance) ;</li>
        <li>Contribuable, objet, catégorie / type, impôts et années, unité actuelle, responsable ;</li>
        <li><strong>Suivi de dossier</strong> (timeline) ;</li>
        <li><strong>Historique</strong> des actions (voir § 17) avec acteur et « pour le compte de » ;</li>
        <li>Pièces jointes : ouvrir / télécharger ;</li>
        <li>Actions contextuelles selon le profil et le statut (réceptionner, réaffecter, traiter, délivrer, annuler dépôt, reçu PDF).</li>
    </ul>
    <p>
        Le reçu de dépôt s’ouvre en PDF (écran d’impression / téléchargement). Deux blocs : exemplaire contribuable
        et exemplaire administration, en-têtes DGI Marrakech.
    </p>
</div>

{{-- ===================== 13 STATS ===================== --}}
<div class="page">
    <h1>13. Statistiques et exports</h1>
    <p>Page <strong>/consultation</strong>. Périodes : semaine, mois (défaut), année, tout le parc, ou intervalle personnalisé. Le filtre s’applique à la date de dépôt, dans le périmètre de l’unité (hiérarchie).</p>

    <h2>13.1 Axes</h2>
    <p>Synthèse ; type de courrier ; bureau d’ordre ; unité de service ; mois ; catégorie ; statut ; priorité ; nécessité de réponse ; mode de délivrance.</p>
    <p>La synthèse affiche totaux, indicateurs de performance, comparaison à la période précédente, croisement type × statut.</p>

    <h2>13.2 Exports</h2>
    <table class="doc">
        <tr><th>Format</th><th>Usage</th></tr>
        <tr><td>CSV</td><td>Tableur, encodage UTF-8 BOM, séparateur point-virgule</td></tr>
        <tr><td>Excel (xlsx)</td><td>Classeur structuré</td></tr>
        <tr><td>JSON</td><td>Réemploi informatique</td></tr>
        <tr><td>PDF</td><td>Rapport imprimable (vue consultation.pdf-statistiques)</td></tr>
    </table>
    <p>Droit <strong>stats.exporter</strong> : Accueil et Consultation par défaut (pas les agents Traitement ni l’Admin).</p>
</div>

{{-- ===================== 14 DELEGATIONS ===================== --}}
<div class="page">
    <h1>14. Délégations</h1>
    <p>Pages /delegations — chefs d’unité uniquement. Objectif : remplacer un agent absent sans perdre la trace.</p>
    <ul>
        <li><strong>Création :</strong> agent absent, remplaçant, unité, date début / fin, motif (congé, maladie, mission, formation, autre).</li>
        <li><strong>Statuts :</strong> Planifiée, Active, Terminée, Annulée. Une commande planifiée expire les délégations (ExpirerDelegationsCommand).</li>
        <li>On ne modifie qu’une délégation <strong>planifiée</strong> ; on peut annuler planifiée ou active.</li>
        <li>Pas de chevauchement de délégations « vivantes » pour le même absent.</li>
        <li>Le remplaçant choisit l’espace (navbar). Les requêtes métier utilisent le <strong>sujet</strong> (absent) pour le périmètre et l’<strong>acteur</strong> (remplaçant) pour l’audit.</li>
    </ul>
</div>

{{-- ===================== 15 ADMIN ===================== --}}
<div class="page">
    <h1>15. Administration (utilisateurs et référentiels)</h1>
    <p>Réservé à <strong>admin@@dgi.ma</strong> (profil Admin).</p>

    <h2>15.1 Utilisateurs</h2>
    <p>
        Liste paginée, recherche, création / édition / fiche.
        Champs : nom, e-mail, mot de passe, rôle, unité, PPR / nom / prénom selon le modèle étendu,
        activation. Attribution de permissions individuelles en plus du profil (checklist).
        L’API /roles/{id}/permissions alimente le formulaire (droits du rôle).
    </p>

    <h2>15.2 Catégories</h2>
    <p>CRUD, actif / inactif, description, compteurs de types et de courriers. Suppression contrôlée si des courriers y sont liés.</p>

    <h2>15.3 Types de courrier</h2>
    <p>CRUD rattaché à une catégorie, actif / inactif.</p>

    <h2>15.4 Unités</h2>
    <p>
        CRUD : code (proposé automatiquement selon le type), libellé, type (DR, Service, Bureau, Brigade, Secteur, Section, etc.),
        unité mère, ville, flag bureau d’ordre, actif. La hiérarchie sert au périmètre des agents.
    </p>
</div>

{{-- ===================== 16 REGLES ===================== --}}
<div class="page">
    <h1>16. Statuts, historique et règles métier</h1>

    <h2>16.1 Statuts de courrier</h2>
    <table class="doc">
        <tr><th>Statut</th><th>Signification</th></tr>
        <tr><td>Déposé</td><td>Enregistré au guichet, en attente de réception US</td></tr>
        <tr><td>Réceptionné</td><td>Prise en charge ; traitable par le responsable</td></tr>
        <tr><td>Réaffecté</td><td>Transmis ; nouvelle réception requise</td></tr>
        <tr><td>Traité favorable</td><td>Issue positive ; file délivrance Accueil si document</td></tr>
        <tr><td>Rejeté</td><td>Issue négative, motif conservé</td></tr>
        <tr><td>Suspendu</td><td>Pause chez le responsable ; reprise obligatoire avant nouveau traitement</td></tr>
        <tr><td>Clôturé</td><td>Fin de circuit (délivrance ou clôture sans document)</td></tr>
        <tr><td>Archivé / classé</td><td>Sans circuit de réponse</td></tr>
        <tr><td>Annulé</td><td>Dépôt annulé</td></tr>
        <tr><td>Traité (ancien)</td><td>Statut historique conservé, remplacé par Traité favorable</td></tr>
    </table>

    <h2>16.2 Actions d’historique</h2>
    <p>
        Dépôt, affectation, réaffectation, réception, annulation de réception, refus de réception,
        refus de réaffectation, annulation de dépôt, début traitement, suspension, reprise,
        traitement favorable, traitement rejeté, archivage, retour Accueil, délivrance, clôture.
    </p>

    <h2>16.3 Règles clés</h2>
    <ol>
        <li>Le numéro de courrier est attribué par le système, jamais saisi.</li>
        <li>L’Accueil n’accède pas à Réception ni Traitement.</li>
        <li>Seul le responsable actuel traite ou reprend le dossier.</li>
        <li>Un dossier suspendu doit être repris avant un nouveau résultat.</li>
        <li>Réception / réaffectation interdites après traitement, rejet, suspension, clôture, archivage, annulation.</li>
        <li>Toute action sensible est historisée (acteur + pour-compte-de).</li>
        <li>La visibilité est limitée au périmètre (unité, filles, responsabilité).</li>
    </ol>
</div>

{{-- ===================== 17 SYNTHESE ===================== --}}
<div class="page-last">
    <h1>17. Synthèse « qui voit quoi »</h1>
    <table class="doc">
        <tr>
            <th>Écran / fonction</th>
            <th>accueil@</th>
            <th>agent.bureau1@</th>
            <th>bureau@ (chef)</th>
            <th>admin@</th>
        </tr>
        <tr><td>Login / profil / déconnexion</td><td>oui</td><td>oui</td><td>oui</td><td>oui</td></tr>
        <tr><td>Accueil (cartes + liste)</td><td>oui</td><td>oui</td><td>oui</td><td>oui (périmètre DR)</td></tr>
        <tr><td>Nouveau courrier + import + reçu</td><td>oui</td><td>—</td><td>—</td><td>—</td></tr>
        <tr><td>Réception</td><td>—</td><td>oui</td><td>oui</td><td>—</td></tr>
        <tr><td>Traitement</td><td>—</td><td>oui</td><td>oui</td><td>—</td></tr>
        <tr><td>Délivrance</td><td>oui</td><td>—</td><td>—</td><td>—</td></tr>
        <tr><td>Statistiques</td><td>oui + export</td><td>oui</td><td>oui</td><td>—</td></tr>
        <tr><td>Délégations + espace</td><td>—</td><td>espace si remplaçant</td><td>oui (CRUD)</td><td>—</td></tr>
        <tr><td>Utilisateurs / référentiels</td><td>—</td><td>—</td><td>—</td><td>oui</td></tr>
    </table>

    <h2>Parcours de démonstration recommandé</h2>
    <ol>
        <li>Se connecter en <strong>accueil@@dgi.ma</strong> / password → Accueil, cartes, nouveau courrier, reçu PDF.</li>
        <li>Se connecter en <strong>agent.bureau1@@dgi.ma</strong> → Réception du dossier déposé, puis Traitement favorable.</li>
        <li>Revenir en Accueil → menu Délivrance → clôturer le dossier.</li>
        <li>Statistiques → export PDF ou Excel.</li>
        <li>Se connecter en <strong>admin@@dgi.ma</strong> → utilisateurs, catégories, unités.</li>
        <li>(Option) Chef <strong>bureau@@dgi.ma</strong> → créer une délégation vers agent.bureau2, puis tester le changement d’espace.</li>
    </ol>

    <div class="box">
        <strong>Fin du document.</strong> Cette documentation décrit l’application telle qu’implémentée
        dans le dépôt gestion_DGI2 (menus, routes, seeders, services et règles de statut).
        Pour régénérer ce PDF : <code>php artisan doc:application</code>
    </div>
</div>

</body>
</html>
