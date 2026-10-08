<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <title>Rapport de projet — Application de gestion des courriers DGI</title>
    <style>
        @page { margin: 2.2cm 2cm 2.2cm 2.2cm; }
        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 11pt;
            line-height: 1.55;
            color: #1a1a1a;
            text-align: justify;
        }
        .page { page-break-after: always; }
        .page-last { page-break-after: auto; }
        h1 {
            font-size: 16pt;
            text-align: center;
            text-transform: uppercase;
            margin: 0 0 1.2em;
            color: #0d47a1;
        }
        h2 {
            font-size: 13pt;
            color: #0d47a1;
            margin: 1.4em 0 0.6em;
            border-bottom: 1px solid #c5d4eb;
            padding-bottom: 0.25em;
        }
        h3 {
            font-size: 11.5pt;
            color: #1565c0;
            margin: 1.1em 0 0.45em;
        }
        p { margin: 0 0 0.75em; }
        .center { text-align: center; }
        .right { text-align: right; }
        .italic { font-style: italic; }
        .bold { font-weight: bold; }
        .small { font-size: 9.5pt; }
        .muted { color: #555; }
        .cover {
            text-align: center;
            padding-top: 0.5cm;
        }
        .cover .logos { margin-bottom: 1.2cm; }
        .cover .logos img { height: 70px; }
        .cover .etab {
            font-size: 11pt;
            font-weight: bold;
            text-transform: uppercase;
            margin-bottom: 0.35em;
        }
        .cover .filiere {
            font-size: 10.5pt;
            margin-bottom: 1.5cm;
        }
        .cover .doc-type {
            font-size: 13pt;
            font-weight: bold;
            letter-spacing: 0.08em;
            margin: 1.2cm 0 0.8cm;
            text-transform: uppercase;
        }
        .cover .titre {
            font-size: 15pt;
            font-weight: bold;
            color: #0d47a1;
            margin: 0.6cm 1cm 1.2cm;
            line-height: 1.35;
        }
        .cover .cadre {
            display: inline-block;
            border: 1.5pt solid #0d47a1;
            padding: 0.6cm 0.9cm;
            margin: 0.4cm 0 1.2cm;
            text-align: left;
            font-size: 10.5pt;
        }
        .cover .bas {
            margin-top: 1.5cm;
            font-size: 10.5pt;
        }
        .dedicace-page, .remerciements-page {
            padding-top: 3.5cm;
        }
        .dedicace-texte {
            font-style: italic;
            font-size: 12pt;
            line-height: 1.8;
            margin: 2cm 1.5cm;
            text-align: center;
        }
        .toc a { color: #1a1a1a; text-decoration: none; }
        .toc-line {
            margin: 0.35em 0;
            font-size: 11pt;
        }
        .toc-h1 { font-weight: bold; margin-top: 0.7em; }
        .toc-h2 { margin-left: 1.2em; }
        .toc-h3 { margin-left: 2.2em; font-size: 10.5pt; }
        .lof-line { margin: 0.4em 0; }
        .chapitre-titre {
            text-align: center;
            margin-top: 2cm;
            margin-bottom: 1.5cm;
        }
        .chapitre-num {
            font-size: 14pt;
            letter-spacing: 0.12em;
            color: #0d47a1;
            text-transform: uppercase;
        }
        .chapitre-intitule {
            font-size: 16pt;
            font-weight: bold;
            color: #0d47a1;
            margin-top: 0.4em;
        }
        .figure-box {
            border: 1px solid #bbb;
            padding: 0.55cm 0.6cm;
            margin: 1em 1.5cm;
            background: #f8f9fb;
            font-size: 9.5pt;
            text-align: center;
        }
        .figure-caption {
            font-size: 9.5pt;
            font-style: italic;
            text-align: center;
            margin: 0.3em 0 1em;
            color: #333;
        }
        ul, ol { margin: 0.3em 0 0.9em 1.4em; padding: 0; }
        li { margin-bottom: 0.3em; }
        table.meta {
            width: 100%;
            border-collapse: collapse;
            margin: 0.8em 0 1em;
            font-size: 10pt;
        }
        table.meta th, table.meta td {
            border: 1px solid #ccc;
            padding: 0.35em 0.5em;
            text-align: left;
        }
        table.meta th { background: #e8eef8; color: #0d47a1; }
        .mots-cles { margin-top: 1em; }
        .keywords { margin-top: 1em; }
    </style>
</head>
<body>

{{-- ===================== PAGE DE GARDE ===================== --}}
<div class="page cover">
    <div class="etab">Royaume du Maroc</div>
    <div class="etab">Direction Générale des Impôts</div>
    <div class="filiere">Direction Régionale des Impôts de Marrakech</div>

    <div class="logos">
        @if (! empty($logoPath) && file_exists($logoPath))
            <img src="{{ $logoPath }}" alt="Logo DGI">
        @endif
    </div>

    <div class="doc-type">Rapport de projet de fin d’études</div>

    <div class="titre">
        Conception et réalisation d’une application web<br>
        de gestion du cycle de vie des courriers<br>
        à la Direction Régionale des Impôts de Marrakech
    </div>

    <div class="cadre">
        <div><span class="bold">Projet :</span> gestion_DGI2</div>
        <div><span class="bold">Domaine :</span> Informatique / Systèmes d’information</div>
        <div><span class="bold">Organisme d’accueil :</span> DRI Marrakech — DGI</div>
        <div><span class="bold">Année universitaire :</span> 2025 – 2026</div>
    </div>

    <table class="meta" style="width: 85%; margin: 0 auto;">
        <tr>
            <th style="width: 40%;">Réalisé par</th>
            <th>Encadré par</th>
        </tr>
        <tr>
            <td>[Nom et prénom de l’étudiant(e)]</td>
            <td>
                Encadrant académique : [Nom]<br>
                Encadrant professionnel (DGI) : [Nom]
            </td>
        </tr>
    </table>

    <div class="bas muted">
        Application web — Laravel 12 / PHP 8 / MySQL<br>
        Marrakech, {{ now()->locale('fr')->translatedFormat('F Y') }}
    </div>
</div>

{{-- ===================== DÉDICACE ===================== --}}
<div class="page dedicace-page">
    <h1>Dédicace</h1>
    <div class="dedicace-texte">
        À mes parents,<br>
        pour leur soutien constant, leur patience et leurs sacrifices<br>
        qui m’ont permis d’atteindre cette étape.<br><br>

        À ma famille et à mes proches,<br>
        pour leur encouragement tout au long de ce parcours.<br><br>

        À tous ceux qui croient en la valeur du travail,<br>
        de la rigueur et du service public.<br><br>

        Je dédie ce travail.
    </div>
</div>

{{-- ===================== REMERCIEMENTS ===================== --}}
<div class="page remerciements-page">
    <h1>Remerciements</h1>

    <p>
        Au terme de ce travail, je tiens à exprimer ma profonde gratitude à toutes les personnes
        qui ont contribué, de près ou de loin, à la réalisation de ce projet.
    </p>

    <p>
        Je remercie tout d’abord mon encadrant académique pour ses orientations méthodologiques,
        ses conseils avisés et le suivi régulier apporté tout au long de l’élaboration de ce rapport
        et de l’application associée.
    </p>

    <p>
        Mes remerciements s’adressent également à l’équipe de la Direction Régionale des Impôts
        de Marrakech, et plus particulièrement à mon encadrant professionnel, pour m’avoir accueilli(e),
        pour le temps consacré à expliquer les processus métier du courrier (dépôt, réception,
        traitement, délivrance) et pour la confiance accordée lors de la mise en œuvre de la solution.
    </p>

    <p>
        Je remercie les agents des unités concernées (Accueil / Bureau d’ordre, services de traitement,
        brigades et bureaux) pour leurs retours utilisateurs, essentiels à l’adaptation de l’outil
        aux réalités du terrain.
    </p>

    <p>
        Enfin, j’adresse mes remerciements à mes enseignants, camarades et proches pour leur soutien
        moral et technique durant la période de réalisation de ce projet.
    </p>
</div>

{{-- ===================== RÉSUMÉ ===================== --}}
<div class="page">
    <h1>Résumé</h1>

    <p>
        Le présent rapport décrit la conception et la réalisation d’une application web destinée à
        digitaliser et sécuriser le cycle de vie des courriers au sein de la Direction Régionale des
        Impôts (DRI) de Marrakech, relevant de la Direction Générale des Impôts (DGI).
    </p>

    <p>
        Dans un contexte où le traitement des dossiers contribuables implique plusieurs unités
        organisationnelles (Direction, Services, Subdivisions, Brigades, Bureaux, Secteurs / Sections),
        la gestion manuelle ou semi-manuelle des courriers expose l’administration à des risques de
        perte de traçabilité, de délais excessifs et de difficultés de pilotage.
    </p>

    <p>
        La solution proposée, nommée <span class="bold">gestion_DGI2</span>, formalise le parcours
        opérationnel : enregistrement du dépôt, réception, éventuellement réaffectation, traitement
        (favorable, rejet, suspension), délivrance et consultation statistique. Elle s’appuie sur une
        architecture web moderne (Laravel), une gestion fine des profils et permissions, un bureau
        d’ordre (BO), des délégations de remplacement, ainsi qu’un module de statistiques exportables
        (type de courrier, BO, unité de service, mois).
    </p>

    <p>
        Ce document présente le contexte général du projet, les enjeux organisationnels et techniques,
        ainsi que les fondements qui justifient le développement de l’application. Les chapitres
        suivants (hors périmètre de cette première partie) détailleront l’analyse des besoins, la
        conception, la réalisation et les perspectives d’évolution.
    </p>

    <p class="mots-cles">
        <span class="bold">Mots-clés :</span>
        Direction Générale des Impôts ; gestion des courriers ; bureau d’ordre ; workflow ;
        traçabilité ; statistiques ; Laravel ; application web ; administration fiscale.
    </p>
</div>

{{-- ===================== ABSTRACT ===================== --}}
<div class="page">
    <h1>Abstract</h1>

    <p>
        This report presents the design and implementation of a web application aimed at digitizing
        and securing the mail lifecycle within the Regional Tax Directorate (DRI) of Marrakech,
        under the Moroccan General Directorate of Taxes (DGI).
    </p>

    <p>
        Given a hierarchical organization (Directorate, Services, Subdivisions, Brigades, Offices,
        Sectors/Sections), paper-based or poorly integrated mail handling increases the risk of
        lost traceability, longer processing times and weak managerial oversight.
    </p>

    <p>
        The proposed system, <span class="bold">gestion_DGI2</span>, covers deposit registration,
        reception, reassignment, processing (approval, rejection, suspension), delivery and
        statistical consultation. It relies on a modern Laravel stack, role-based access control,
        order office (BO) management, temporary delegation of authority, and multi-format exports
        of statistics by mail type, BO, service unit and month.
    </p>

    <p>
        This first part focuses on the general project context and motivations. Subsequent chapters
        will address requirements analysis, system design, implementation and future work.
    </p>

    <p class="keywords">
        <span class="bold">Keywords:</span>
        General Directorate of Taxes; mail management; order office; workflow; traceability;
        statistics; Laravel; web application; tax administration.
    </p>
</div>

{{-- ===================== LISTE DES FIGURES ===================== --}}
<div class="page">
    <h1>Liste des figures</h1>

    <p class="lof-line">Figure 1.1 — Positionnement de la DRI Marrakech dans l’écosystème DGI ................. 1</p>
    <p class="lof-line">Figure 1.2 — Niveaux organisationnels retenus par le cahier des charges ................. 1</p>
    <p class="lof-line">Figure 1.3 — Cycle de vie métier d’un courrier (vue synthétique) ......................... 1</p>
    <p class="lof-line">Figure 1.4 — Acteurs principaux et responsabilités sur le flux courrier ................. 1</p>
    <p class="lof-line">Figure 1.5 — Périmètre fonctionnel cible de l’application gestion_DGI2 ................. 1</p>

    <p class="small muted" style="margin-top: 1.5em;">
        Remarque : la numérotation des pages sera actualisée lors de la compilation complète du mémoire
        (chapitres 2 et suivants).
    </p>
</div>

{{-- ===================== TABLE DES MATIÈRES ===================== --}}
<div class="page">
    <h1>Table des matières</h1>

    <div class="toc">
        <div class="toc-line toc-h1">Dédicace</div>
        <div class="toc-line toc-h1">Remerciements</div>
        <div class="toc-line toc-h1">Résumé</div>
        <div class="toc-line toc-h1">Abstract</div>
        <div class="toc-line toc-h1">Liste des figures</div>
        <div class="toc-line toc-h1">Table des matières</div>
        <div class="toc-line toc-h1">Introduction générale ..................................................................... 1</div>

        <div class="toc-line toc-h1">Chapitre 1 — Contexte général du projet ............................................... 1</div>
        <div class="toc-line toc-h2">1.1 Introduction du chapitre .............................................................. 1</div>
        <div class="toc-line toc-h2">1.2 Présentation de l’organisme d’accueil ............................................. 1</div>
        <div class="toc-line toc-h3">1.2.1 La Direction Générale des Impôts .................................................... 1</div>
        <div class="toc-line toc-h3">1.2.2 La Direction Régionale des Impôts de Marrakech ................................. 1</div>
        <div class="toc-line toc-h2">1.3 Organisation hiérarchique et unités de service ................................... 1</div>
        <div class="toc-line toc-h2">1.4 Problématique et enjeux ................................................................. 1</div>
        <div class="toc-line toc-h2">1.5 Objectifs du projet ....................................................................... 1</div>
        <div class="toc-line toc-h2">1.6 Périmètre fonctionnel et acteurs .................................................... 1</div>
        <div class="toc-line toc-h2">1.7 Contraintes et cahier des charges .................................................... 1</div>
        <div class="toc-line toc-h2">1.8 Conclusion du chapitre ................................................................... 1</div>

        <div class="toc-line toc-h1 muted" style="margin-top: 1em;">
            Chapitre 2 — Analyse et spécification des besoins <span class="italic">(à venir)</span>
        </div>
        <div class="toc-line toc-h1 muted">
            Chapitre 3 — Conception de la solution <span class="italic">(à venir)</span>
        </div>
        <div class="toc-line toc-h1 muted">
            Chapitre 4 — Réalisation et tests <span class="italic">(à venir)</span>
        </div>
        <div class="toc-line toc-h1 muted">
            Conclusion générale et perspectives <span class="italic">(à venir)</span>
        </div>
        <div class="toc-line toc-h1 muted">
            Bibliographie / Webographie <span class="italic">(à venir)</span>
        </div>
        <div class="toc-line toc-h1 muted">
            Annexes <span class="italic">(à venir)</span>
        </div>
    </div>
</div>

{{-- ===================== INTRODUCTION GÉNÉRALE ===================== --}}
<div class="page">
    <h1>Introduction générale</h1>

    <p>
        La modernisation de l’administration publique marocaine s’accompagne d’une digitalisation
        progressive des processus métier. Au sein de la Direction Générale des Impôts, la gestion
        des courriers constitue un levier critique : chaque dépôt ouvre un dossier qui doit circuler
        de manière fiable entre le bureau d’ordre, les unités de traitement et l’accueil chargé de
        la délivrance des documents.
    </p>

    <p>
        Or, lorsque ce parcours repose principalement sur des supports papier, des registres
        dispersés ou des outils hétérogènes, plusieurs difficultés apparaissent : difficulté à
        connaître l’état exact d’un dossier, délais de transmission difficiles à maîtriser,
        absence de vision consolidée pour le pilotage, et risque d’erreurs lors des réaffectations
        ou des remplacements d’agents.
    </p>

    <p>
        Dans ce cadre, le projet <span class="bold">gestion_DGI2</span> vise à concevoir et réaliser
        une application web de gestion du cycle de vie des courriers pour la Direction Régionale
        des Impôts de Marrakech. L’outil doit respecter le cahier des charges métier : identification
        des contribuables, enregistrement du dépôt (numérotation annuelle), réception, réaffectation,
        traitement, délivrance, traçabilité chronologique, délégations, et statistiques
        (par type de courrier, BO, US et mois) exportables sous plusieurs formats.
    </p>

    <p>
        Ce rapport s’organise de la manière suivante. Après la présente introduction, le
        <span class="bold">chapitre 1</span> situe le contexte général du projet (organisme,
        organisation, problématique, objectifs et périmètre). Les chapitres ultérieurs traiteront
        de l’analyse des besoins, de la conception, de la réalisation technique et de l’évaluation
        de la solution.
    </p>
</div>

{{-- ===================== CHAPITRE 1 ===================== --}}
<div class="page">
    <div class="chapitre-titre">
        <div class="chapitre-num">Chapitre 1</div>
        <div class="chapitre-intitule">Contexte général du projet</div>
    </div>

    <h2>1.1 Introduction du chapitre</h2>
    <p>
        Avant d’aborder l’analyse détaillée des besoins et la conception informatique, il est
        indispensable de comprendre le cadre dans lequel s’inscrit le projet. Ce chapitre présente
        l’organisme d’accueil, rappelle l’organisation hiérarchique des unités, formule la
        problématique, précise les objectifs poursuivis et délimite le périmètre fonctionnel de
        l’application. Il s’achève par une conclusion qui prépare le passage à l’étude des besoins.
    </p>

    <h2>1.2 Présentation de l’organisme d’accueil</h2>

    <h3>1.2.1 La Direction Générale des Impôts</h3>
    <p>
        La Direction Générale des Impôts (DGI) est l’administration chargée, au Maroc, de l’assiette,
        du contrôle et du recouvrement de l’impôt, ainsi que de la relation avec les contribuables.
        Elle déploie son action à travers un réseau territorial composé de directions régionales et
        d’unités opérationnelles spécialisées. La qualité du service rendu dépend notamment de la
        capacité à traiter, suivre et clôturer les demandes écrites (courriers) dans des délais
        maîtrisés et avec une traçabilité complète.
    </p>

    <h3>1.2.2 La Direction Régionale des Impôts de Marrakech</h3>
    <p>
        La Direction Régionale des Impôts (DRI) de Marrakech constitue le terrain d’application du
        projet. Elle regroupe des services et unités qui reçoivent, instrisent et restituent des
        dossiers liés aux contribuables. Le projet se concentre sur la digitalisation du circuit
        interne des courriers, depuis le dépôt jusqu’à la délivrance, en cohérence avec les pratiques
        locales et le cahier des charges fourni.
    </p>

    <div class="figure-box">
        <strong>Direction Générale des Impôts (DGI)</strong><br>
        ↓<br>
        <strong>Direction Régionale des Impôts — Marrakech</strong><br>
        ↓<br>
        Unités opérationnelles (Services, Brigades, Bureaux, Secteurs…)
    </div>
    <p class="figure-caption">Figure 1.1 — Positionnement de la DRI Marrakech dans l’écosystème DGI</p>
</div>

<div class="page">
    <h2>1.3 Organisation hiérarchique et unités de service</h2>
    <p>
        Le cahier des charges formalise une structure à quatre niveaux, essentielle pour comprendre
        les droits d’accès, les réaffectations et le périmètre de consultation :
    </p>
    <ol>
        <li><span class="bold">Niveau 1 — Direction</span> : niveau de pilotage régional.</li>
        <li><span class="bold">Niveau 2 — Service</span> : agrégation de plusieurs sous-structures.</li>
        <li><span class="bold">Niveau 3 — Subdivision / RAF / BET / Brigade / Bureau / Centre</span> :
            unités opérationnelles de traitement.</li>
        <li><span class="bold">Niveau 4 — Secteur / Section</span> : maille la plus fine du dispositif.</li>
    </ol>
    <p>
        Une Direction contient plusieurs Services ; un Service contient plusieurs structures de
        niveau 3 ; une structure de niveau 3 contient plusieurs Secteurs ou Sections. Cette
        arborescence conditionne la circulation des dossiers et la notion d’unité de service (US)
        utilisée dans les statistiques.
    </p>

    <div class="figure-box">
        Niveau 1 : Direction<br>
        → Niveau 2 : Service<br>
        → Niveau 3 : Subdivision / RAF / BET / Brigade / Bureau / Centre<br>
        → Niveau 4 : Secteur / Section
    </div>
    <p class="figure-caption">Figure 1.2 — Niveaux organisationnels retenus par le cahier des charges</p>

    <p>
        Par ailleurs, le <span class="bold">Bureau d’ordre (BO)</span> joue un rôle pivot : il assure
        l’entrée des courriers dans le système, contribue à l’orientation vers les US compétentes et
        intervient dans certaines boucles de retour (refus de réception, etc.).
    </p>

    <h2>1.4 Problématique et enjeux</h2>
    <p>
        La problématique peut être formulée ainsi : <span class="italic">comment garantir un suivi
        fiable, sécurisé et mesurable du courrier fiscal régional, tout en respectant l’organisation
        hiérarchique, les profils métiers et les exigences de traçabilité du cahier des charges ?</span>
    </p>
    <p>Les enjeux associés sont multiples :</p>
    <ul>
        <li><span class="bold">Enjeu de service</span> : réduire les délais perçus par le contribuable
            et fiabiliser la délivrance des documents.</li>
        <li><span class="bold">Enjeu de gouvernance</span> : offrir aux responsables une vision par
            type de courrier, par BO, par US et par mois.</li>
        <li><span class="bold">Enjeu de conformité</span> : conserver l’historique des actions
            (dépôt, réception, réaffectation, traitement, délivrance) et respecter les règles de
            numérotation (format AAAA-NNNNNN-CODE, séquence propre à l’unité destinataire).</li>
        <li><span class="bold">Enjeu organisationnel</span> : permettre les délégations en cas
            d’absence et gérer les multi-profils sans compromettre la sécurité.</li>
    </ul>
</div>

<div class="page">
    <h2>1.5 Objectifs du projet</h2>
    <p>L’objectif global est de réaliser une application web métier couvrant le cycle complet du courrier. De manière opérationnelle, le projet vise à :</p>
    <ul>
        <li>enregistrer les dépôts avec identification du contribuable et production d’un accusé / reçu ;</li>
        <li>assurer la réception (individuelle ou groupée), le refus motivé et la réaffectation selon le niveau hiérarchique ;</li>
        <li>permettre le traitement (favorable, rejet, suspension / reprise) par l’unité responsable ;</li>
        <li>gérer la délivrance (retrait, envoi postal, etc.) au niveau Accueil ;</li>
        <li>produire des statistiques paramétrables et exportables (CSV, Excel, JSON, PDF) ;</li>
        <li>administrer les utilisateurs, rôles, unités, catégories / types de courrier et délégations.</li>
    </ul>

    <h2>1.6 Périmètre fonctionnel et acteurs</h2>
    <p>
        Le périmètre cible correspond au workflow suivant, synthétisé ci-dessous.
    </p>

    <div class="figure-box">
        Dépôt (Accueil / BO)
        → Réception (US)
        → [Réaffectation]
        → Traitement
        → Délivrance (Accueil)
        → Clôture / consultation statistique
    </div>
    <p class="figure-caption">Figure 1.3 — Cycle de vie métier d’un courrier (vue synthétique)</p>

    <div class="figure-box" style="text-align: left;">
        <strong>Accueil / BO</strong> : dépôt, délivrance, orientation.<br>
        <strong>Agents de traitement</strong> : instruction des dossiers réceptionnés.<br>
        <strong>Responsables d’unité</strong> : suivi, réaffectation, supervision.<br>
        <strong>Profil Consultation</strong> : statistiques et exports.<br>
        <strong>Administrateur</strong> : référentiels, comptes, permissions.
    </div>
    <p class="figure-caption">Figure 1.4 — Acteurs principaux et responsabilités sur le flux courrier</p>

    <div class="figure-box" style="text-align: left;">
        Modules : Accueil · Réception · Traitement · Délivrance · Statistiques ·
        Utilisateurs · Délégations · Référentiels (catégories, types, unités)
    </div>
    <p class="figure-caption">Figure 1.5 — Périmètre fonctionnel cible de l’application gestion_DGI2</p>
</div>

<div class="page-last">
    <h2>1.7 Contraintes et cahier des charges</h2>
    <p>
        Le développement s’appuie sur un cahier des charges qui fixe notamment :
    </p>
    <ul>
        <li>la structure organisationnelle à quatre niveaux ;</li>
        <li>le format du numéro de dépôt <span class="bold">yyyy</span> suivi de neuf chiffres
            (exemple : <span class="bold">2026000000001</span>) ;</li>
        <li>l’existence d’un menu de statistiques ventilées par type de courrier, BO et US,
            avec une dimension mensuelle et des exports multi-formats ;</li>
        <li>des règles métier de réception, réaffectation, traitement et délivrance, avec
            conservation de la trace des opérations.</li>
    </ul>
    <p>
        Sur le plan technique, le choix d’un cadre web (Laravel) et d’une base relationnelle (MySQL)
        répond aux besoins de robustesse, de maintenabilité et d’intégration dans un environnement
        d’administration déjà familiarisé avec des applications intranet.
    </p>

    <h2>1.8 Conclusion du chapitre</h2>
    <p>
        Ce premier chapitre a permis de situer le projet dans son environnement institutionnel et
        organisationnel. La DRI de Marrakech, structurée selon une hiérarchie à quatre niveaux et
        articulant Bureau d’ordre et unités de service, constitue le cadre opérationnel de
        l’application gestion_DGI2. La problématique de traçabilité et de pilotage du courrier
        justifie pleinement une solution web intégrée, alignée sur le cahier des charges.
    </p>
    <p>
        Sur cette base, le chapitre suivant s’attachera à analyser et spécifier les besoins
        fonctionnels et non fonctionnels, en détaillant les cas d’utilisation, les règles de gestion
        et les exigences de qualité attendues par les métiers.
    </p>
</div>

</body>
</html>
