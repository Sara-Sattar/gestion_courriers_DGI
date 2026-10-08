<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <title>4.2.4 Base de données et 4.3 Interfaces — Gestion des courriers DGI</title>
    <style>
        @@page { margin: 1.8cm 1.6cm 1.8cm 1.6cm; }
        body { font-family: DejaVu Sans, sans-serif; font-size: 10.5pt; line-height: 1.5; color: #1a1a1a; text-align: justify; }
        h1 { font-size: 15pt; text-align: center; text-transform: uppercase; color: #0d47a1; margin: 0 0 0.9em; }
        h2 { font-size: 13pt; color: #0d47a1; margin: 1.15em 0 0.45em; border-bottom: 1px solid #c5d4eb; padding-bottom: 0.15em; }
        h3 { font-size: 11.2pt; color: #1565c0; margin: 0.95em 0 0.35em; }
        h4 { font-size: 10.6pt; color: #1a237e; margin: 0.85em 0 0.3em; }
        p { margin: 0 0 0.55em; }
        ul { margin: 0.15em 0 0.7em 1.15em; padding: 0; }
        li { margin-bottom: 0.28em; }
        .page { page-break-after: always; }
        .page-last { page-break-after: auto; }
        .cover { text-align: center; padding-top: 1.4cm; }
        .cover .etab { font-size: 11pt; font-weight: bold; text-transform: uppercase; }
        .cover .titre { font-size: 14pt; font-weight: bold; color: #0d47a1; margin: 1.1cm 0.6cm; line-height: 1.35; }
        .muted { color: #555; }
        .small { font-size: 8.6pt; }
        .bold { font-weight: bold; }
        .box { border: 1px solid #bbb; padding: 0.4cm 0.5cm; margin: 0.55em 0 0.85em; background: #f8f9fb; font-size: 9.6pt; text-align: left; }
        table.doc { width: 100%; border-collapse: collapse; margin: 0.4em 0 0.85em; font-size: 8.7pt; }
        table.doc th, table.doc td { border: 1px solid #ccc; padding: 0.28em 0.35em; text-align: left; vertical-align: top; }
        table.doc th { background: #e8eef8; color: #0d47a1; }
        .caption { font-size: 8.4pt; font-style: italic; text-align: center; margin: 0.12em 0 0.75em; color: #333; }
        .capture { width: 100%; border: 1px solid #c5d4eb; margin-top: 0.25em; }
        .keep { page-break-inside: avoid; }
        code { font-size: 8.6pt; }
    </style>
</head>
<body>

@php
    $img = function (string $cle) use ($captures) {
        return $captures[$cle] ?? null;
    };
@endphp

{{-- ===================== COUVERTURE ===================== --}}
<div class="page cover">
    <div class="etab">Royaume du Maroc</div>
    <div class="etab">Direction Générale des Impôts</div>
    <p class="muted">Direction Régionale des Impôts de Marrakech</p>
    @if (! empty($logoPath) && file_exists($logoPath))
        <p><img src="{{ $logoPath }}" alt="Logo DGI" style="height: 68px;"></p>
    @endif
    <div style="font-size: 12pt; font-weight: bold; letter-spacing: 0.08em; text-transform: uppercase; margin: 1cm 0 0.5cm;">
        Extrait du rapport de projet
    </div>
    <div class="titre">
        4.2.4 Base de données<br>
        4.3 Présentation des interfaces de l’application
    </div>
    <div class="box" style="display: inline-block; width: 88%;">
        <strong>Application :</strong> gestion_DGI2 — cycle de vie des courriers (dépôt, réception, traitement, délivrance).<br>
        <strong>Périmètre :</strong> MySQL / Eloquent, rôles métier et écrans capturés sur le jeu de démonstration.<br>
        <strong>Date :</strong> {{ now()->locale('fr')->translatedFormat('d F Y') }}
    </div>
</div>

{{-- ===================== 4.2.4 ===================== --}}
<div class="page">
    <h2>4.2.4 Base de données</h2>

    <p>
        La base de données utilisée est <strong>MySQL</strong>, choisie pour sa robustesse, sa performance
        et sa compatibilité avec l’<strong>ORM Eloquent</strong> de Laravel. Elle constitue le socle de
        persistance de l’application de gestion des courriers de la Direction Régionale des Impôts de Marrakech.
    </p>
    <p>
        Elle contient <strong>20 tables principales</strong>, représentant les entités essentielles du projet
        gestion_DGI2 (identités, organisation, cycle de vie des courriers, traçabilité, et délégations).
        S’y ajoutent des tables techniques Laravel (sessions, cache, files d’attente, jetons) qui ne sont pas
        décrites ici.
    </p>

    <ul>
        <li><strong>users</strong> : gère les agents de la DGI (identité, PPR, courriel, profil principal, unité d’affectation et bureau d’ordre).</li>
        <li><strong>roles</strong> : définit les profils métier (Admin, Accueil, Traitement, Consultation, Service, Brigade, Bureau, Section, Secteur).</li>
        <li><strong>permissions</strong> : catalogue des droits atomiques (menus, actions courrier, délégations, administration).</li>
        <li><strong>permission_role</strong> : associe à chaque profil la liste de ses permissions par défaut.</li>
        <li><strong>permission_user</strong> : permet d’attribuer des droits personnalisés à un utilisateur, au-delà de son profil.</li>
        <li><strong>role_user</strong> : autorise le cumul de profils CDC supplémentaires sur un même compte.</li>
        <li><strong>unites</strong> : représente l’organigramme (direction, services, brigades, bureaux, sections, secteurs) et les destinataires des courriers.</li>
        <li><strong>bureaux_ordre</strong> : identifie les bureaux d’ordre (code, libellé, ville) auxquels sont rattachés les agents d’accueil.</li>
        <li><strong>contribuables</strong> : stocke les usagers et personnes morales (IFU, CIN, ICE, TP, TH, coordonnées) concernés par un dépôt.</li>
        <li><strong>categories_courrier</strong> : classe les familles de courriers (réclamations, demandes, correspondances, etc.).</li>
        <li><strong>types_courrier</strong> : précise le type à l’intérieur d’une catégorie (ex. réclamation IS, demande d’attestation).</li>
        <li><strong>courriers</strong> : entité centrale du cycle de vie : numéro d’arrivée, objet, statut, unité destinataire, dates de dépôt / réception / traitement / délivrance, numéro de départ.</li>
        <li><strong>courrier_impots</strong> : relie un courrier aux impôts concernés (IS, IR, TVA, TP, TH, etc.).</li>
        <li><strong>courrier_annees</strong> : mémorise les années d’imposition visées par le dossier.</li>
        <li><strong>pieces_jointes</strong> : conserve les fichiers déposés avec le courrier (nom, type MIME, taille, chemin de stockage).</li>
        <li><strong>courrier_historiques</strong> : journalise chaque action métier (enregistrement, réception, traitement, délivrance, annulation) et l’auteur.</li>
        <li><strong>historique_affectations</strong> : trace les réaffectations et changements de responsable ou d’unité.</li>
        <li><strong>courrier_sequences</strong> : garantit l’unicité du numéro d’arrivée par année et par unité destinataire.</li>
        <li><strong>depart_sequences</strong> : attribue le numéro de départ annuel lors de la délivrance d’un document.</li>
        <li><strong>delegations</strong> : formalise le remplacement temporaire d’un agent absent (période, motif, statut, unité concernée).</li>
    </ul>
</div>

{{-- ===================== 4.3 / 4.3.1 ===================== --}}
<div class="page">
    <h2>4.3 Présentation des interfaces de l’application</h2>
    <p>
        Cette section présente les rôles qui structurent l’accès à l’application, puis les écrans
        principaux tels qu’ils apparaissent à l’utilisateur. Chaque interface est illustrée par une
        capture réalisée sur le jeu de données de démonstration.
    </p>

    <h3>4.3.1 Présentation des rôles</h3>
    <p>
        L’accès aux menus et aux actions est piloté par des <strong>permissions</strong> rattachées à un
        <strong>profil</strong> (rôle). Un utilisateur possède un profil principal et, le cas échéant,
        des profils CDC additionnels. Les droits effectifs sont l’union de ces profils, sauf si des
        permissions personnelles ont été définies.
    </p>

    <table class="doc">
        <thead>
            <tr>
                <th style="width: 22%;">Rôle</th>
                <th>Rôle dans l’application</th>
                <th style="width: 34%;">Menus et actions principales</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td><strong>Accueil</strong></td>
                <td>Guichet du bureau d’ordre : enregistrement des dépôts, orientation vers l’unité destinataire, délivrance des documents et consultation des statistiques de son périmètre.</td>
                <td>Accueil, Enregistrer un courrier, Délivrance, Statistiques. Ni réception ni traitement.</td>
            </tr>
            <tr>
                <td><strong>Traitement</strong></td>
                <td>Agent métier affecté à une unité. Il confirme la réception des courriers qui lui sont destinés, les traite, les réaffecte ou les refuse.</td>
                <td>Accueil, Réception, Traitement, Statistiques.</td>
            </tr>
            <tr>
                <td><strong>Consultation</strong></td>
                <td>Profil de lecture : suivi des indicateurs et export, sans action sur le cycle de vie.</td>
                <td>Accueil, Statistiques, export.</td>
            </tr>
            <tr>
                <td><strong>Service, Brigade, Bureau, Section, Secteur</strong></td>
                <td>Responsables hiérarchiques. Ils disposent des mêmes actions courrier que l’agent de traitement, plus la gestion des délégations de leur unité (absence, remplaçant, période).</td>
                <td>Accueil, Réception, Traitement, Statistiques, Délégations.</td>
            </tr>
            <tr>
                <td><strong>Admin</strong></td>
                <td>Administrateur technique : comptes, profils, catégories, types de courrier et unités. Il n’intervient pas dans le circuit métier des courriers.</td>
                <td>Accueil, Gestion des utilisateurs, Référentiels.</td>
            </tr>
        </tbody>
    </table>

    <p>
        Les captures des sous-sections suivantes utilisent les comptes de démonstration
        <strong>accueil@@dgi.ma</strong> (guichet), <strong>agent.bureau1@@dgi.ma</strong> (traitement),
        <strong>bureau@@dgi.ma</strong> (chef de bureau) et <strong>admin@@dgi.ma</strong> (administration).
    </p>
</div>

{{-- ===================== 4.3.2 accès ===================== --}}
<div class="page">
    <h3>4.3.2 Interface d’accès à l’application</h3>
    <p>
        L’application n’est accessible qu’après authentification. La page de connexion, unique point
        d’entrée public, demande l’adresse professionnelle et le mot de passe. Une session Laravel
        est alors ouverte ; les menus affichés dépendent exclusivement des permissions du compte.
    </p>

    <h4>4.3.2.1 Page de connexion</h4>
    <p>
        L’écran reprend l’identité visuelle de la DGI (logo, couleurs institutionnelles). L’utilisateur
        saisit son courriel et son mot de passe. Un bouton permet d’afficher temporairement le mot de
        passe. En cas d’échec, un message d’erreur s’affiche sans révéler si l’adresse existe.
    </p>
    @if ($img('01-login'))
        <img class="capture" src="{{ $img('01-login') }}" alt="Page de connexion">
        <p class="caption">Figure 4.3.2.1 — Interface de connexion à l’application</p>
    @endif

    <h4>4.3.2.2 Tableau de bord (Accueil)</h4>
    <p>
        Après connexion, l’agent d’accueil arrive sur le tableau de bord : indicateurs de son
        périmètre (total, entrants, sortants, mois en cours), barre de recherche et liste des
        courriers. La navigation propose Accueil, Délivrance et Statistiques — pas de Réception
        ni de Traitement, conformément au rôle.
    </p>
    @if ($img('02-dashboard-accueil'))
        <img class="capture" src="{{ $img('02-dashboard-accueil') }}" alt="Tableau de bord Accueil">
        <p class="caption">Figure 4.3.2.2 — Tableau de bord de l’agent d’accueil</p>
    @endif
</div>

<div class="page">
    <h4>4.3.2.3 Enregistrement d’un courrier</h4>
    <p>
        Le formulaire de dépôt permet d’identifier le contribuable (recherche IFU, CIN, ICE, nom),
        de choisir la catégorie et le type de courrier, l’unité destinataire, l’objet, les impôts
        et années concernés, puis de joindre des pièces. Un import Excel est également proposé.
        À l’enregistrement, un numéro d’arrivée unique est attribué et un reçu PDF peut être édité.
    </p>
    @if ($img('03-enregistrement'))
        <img class="capture" src="{{ $img('03-enregistrement') }}" alt="Formulaire d'enregistrement">
        <p class="caption">Figure 4.3.2.3 — Formulaire d’enregistrement d’un courrier</p>
    @endif

    <h4>4.3.2.4 File de délivrance</h4>
    <p>
        Lorsque le traitement métier est achevé et qu’un document doit être remis, le courrier
        apparaît dans la file de délivrance du guichet. L’agent d’accueil y prépare la remise
        (retrait au guichet, mandataire ou envoi postal) et le numéro de départ.
    </p>
    @if ($img('04-delivrance'))
        <img class="capture" src="{{ $img('04-delivrance') }}" alt="Liste de délivrance">
        <p class="caption">Figure 4.3.2.4 — Interface de délivrance (liste des dossiers à remettre)</p>
    @endif
</div>

<div class="page">
    <h4>4.3.2.5 Fiche de délivrance</h4>
    <p>
        La fiche détaille le dossier et le mode de livraison. L’agent saisit les informations du
        destinataire ou du mandataire, confirme la remise ou clôture sans document lorsque aucune
        pièce n’est à délivrer. L’action est historisée.
    </p>
    @if ($img('05-delivrance-fiche'))
        <img class="capture" src="{{ $img('05-delivrance-fiche') }}" alt="Fiche de délivrance">
        <p class="caption">Figure 4.3.2.5 — Fiche de délivrance d’un courrier</p>
    @endif

    <h4>4.3.2.6 Statistiques</h4>
    <p>
        L’écran de consultation présente les indicateurs du périmètre de l’utilisateur : volumes
        par type, bureau d’ordre, unité, mois, ainsi que des axes complémentaires. La période est
        filtrable. Un export Excel / PDF est disponible pour les profils habilités.
    </p>
    @if ($img('06-statistiques'))
        <img class="capture" src="{{ $img('06-statistiques') }}" alt="Statistiques">
        <p class="caption">Figure 4.3.2.6 — Interface de statistiques</p>
    @endif
</div>

<div class="page">
    <h4>4.3.2.7 Profil utilisateur</h4>
    <p>
        Chaque agent peut consulter et mettre à jour ses informations personnelles et modifier
        son mot de passe depuis la page Profil, accessible via le menu utilisateur.
    </p>
    @if ($img('07-profil'))
        <img class="capture" src="{{ $img('07-profil') }}" alt="Profil">
        <p class="caption">Figure 4.3.2.7 — Interface du profil utilisateur</p>
    @endif
</div>

<div class="page">
    <h4>4.3.2.8 Fiche d’un courrier</h4>
    <p>
        La fiche synthétise l’identité du dossier (numéro, contribuable, objet, statut), le
        circuit (unité destinataire, responsable) et l’historique des actions. Selon le rôle,
        des actions contextuelles (reçu, pièces jointes, annulation de dépôt) sont proposées.
    </p>
    @if ($img('09-fiche-courrier'))
        <img class="capture" src="{{ $img('09-fiche-courrier') }}" alt="Fiche courrier">
        <p class="caption">Figure 4.3.2.8 — Fiche détaillée d’un courrier</p>
    @endif

    <h4>4.3.2.9 Réception (agent de traitement)</h4>
    <p>
        Connecté avec le profil Traitement, l’agent voit le menu Réception. La liste présente
        les courriers affectés à son unité, en attente de confirmation. Il peut réceptionner,
        refuser (retour bureau d’ordre) ou demander une réaffectation, avec motif.
    </p>
    @if ($img('10-reception'))
        <img class="capture" src="{{ $img('10-reception') }}" alt="Réception">
        <p class="caption">Figure 4.3.2.9 — Interface de réception des courriers</p>
    @endif
</div>

<div class="page">
    <h4>4.3.2.10 File de traitement</h4>
    <p>
        Après réception, le dossier entre dans la file de traitement de l’unité. L’agent filtre
        et recherche les courriers à instruire, puis ouvre la fiche de traitement.
    </p>
    @if ($img('11-traitement'))
        <img class="capture" src="{{ $img('11-traitement') }}" alt="Liste de traitement">
        <p class="caption">Figure 4.3.2.10 — Interface de la file de traitement</p>
    @endif

    <h4>4.3.2.11 Fiche de traitement</h4>
    <p>
        L’agent consigne le résultat (acceptation, rejet, suspension, production d’un document)
        et les motifs le cas échéant. Le statut du courrier évolue ; s’il y a un document à
        remettre, le dossier bascule ensuite vers la délivrance du guichet.
    </p>
    @if ($img('12-traitement-fiche'))
        <img class="capture" src="{{ $img('12-traitement-fiche') }}" alt="Fiche de traitement">
        <p class="caption">Figure 4.3.2.11 — Fiche de traitement d’un courrier</p>
    @endif
</div>

<div class="page">
    <h4>4.3.2.12 Gestion des délégations</h4>
    <p>
        Le chef d’unité (bureau, service, brigade, section ou secteur) gère les remplacements
        d’absence : liste des délégations, statuts (planifiée, active, expirée, annulée) et
        période. Pendant une délégation active, le remplaçant peut basculer dans l’espace de
        l’absent.
    </p>
    @if ($img('13-delegations'))
        <img class="capture" src="{{ $img('13-delegations') }}" alt="Liste des délégations">
        <p class="caption">Figure 4.3.2.12 — Interface de gestion des délégations</p>
    @endif

    <h4>4.3.2.13 Création d’une délégation</h4>
    <p>
        Le formulaire désigne l’agent absent, le remplaçant (parmi les agents de l’unité),
        les dates et le motif. Les règles métier interdisent l’auto-délégation et le
        chevauchement de périodes incompatibles.
    </p>
    @if ($img('14-delegations-create'))
        <img class="capture" src="{{ $img('14-delegations-create') }}" alt="Création d'une délégation">
        <p class="caption">Figure 4.3.2.13 — Formulaire de création d’une délégation</p>
    @endif
</div>

<div class="page">
    <h4>4.3.2.14 Gestion des utilisateurs</h4>
    <p>
        Réservée à l’administrateur, cette interface permet de créer, modifier et consulter
        les comptes : identité, PPR, courriel, profil(s), unité, bureau d’ordre et, si besoin,
        permissions personnelles.
    </p>
    @if ($img('15-users'))
        <img class="capture" src="{{ $img('15-users') }}" alt="Utilisateurs">
        <p class="caption">Figure 4.3.2.14 — Interface d’administration des utilisateurs</p>
    @endif

    <h4>4.3.2.15 Catégories de courrier</h4>
    <p>
        Le référentiel des catégories structure le dépôt. L’administrateur ajoute, active ou
        désactive une catégorie ; chaque catégorie regroupe ensuite des types de courrier.
    </p>
    @if ($img('16-categories'))
        <img class="capture" src="{{ $img('16-categories') }}" alt="Catégories">
        <p class="caption">Figure 4.3.2.15 — Référentiel des catégories de courrier</p>
    @endif
</div>

<div class="page-last">
    <h4>4.3.2.16 Types de courrier</h4>
    <p>
        Les types précisent la nature exacte du dossier à l’intérieur d’une catégorie. Ils
        alimentent les listes déroulantes du formulaire d’enregistrement et les axes des
        statistiques.
    </p>
    @if ($img('17-types-courrier'))
        <img class="capture" src="{{ $img('17-types-courrier') }}" alt="Types de courrier">
        <p class="caption">Figure 4.3.2.16 — Référentiel des types de courrier</p>
    @endif

    <h4>4.3.2.17 Unités destinataires</h4>
    <p>
        L’écran des unités décrit l’organigramme (code, libellé, type, rattachement, ville,
        activité). C’est vers ces unités que l’accueil oriente les courriers et que les
        séquences de numérotation sont calculées.
    </p>
    @if ($img('18-unites'))
        <img class="capture" src="{{ $img('18-unites') }}" alt="Unités">
        <p class="caption">Figure 4.3.2.17 — Référentiel des unités destinataires</p>
    @endif

    <p class="muted small" style="margin-top: 1.1em;">
        Les captures reproduisent l’état de l’application sur l’environnement local de démonstration
        à la date de génération du document. Les volumes affichés dépendent des données chargées
        (seeders / scripts SQL).
    </p>
</div>

</body>
</html>
