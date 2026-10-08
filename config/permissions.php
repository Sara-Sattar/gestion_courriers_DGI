<?php

/**
 * Catalogue des permissions de l'application.
 * L'admin assigne ces droits aux profils (rôles) via l'interface.
 */
return [

    'groupes' => [
        'menu' => [
            'label' => 'Menus affichés',
            'description' => 'Éléments visibles dans la barre de navigation',
        ],
        'courriers' => [
            'label' => 'Courriers',
            'description' => 'Actions sur le cycle de vie des courriers',
        ],
        'delegations' => [
            'label' => 'Délégations',
            'description' => 'Gestion des remplacements en cas d\'absence',
        ],
        'administration' => [
            'label' => 'Administration',
            'description' => 'Gestion des utilisateurs, profils et référentiels',
        ],
    ],

    'permissions' => [
        // Menus
        ['code' => 'menu.dashboard', 'label' => 'Accueil', 'groupe' => 'menu'],
        ['code' => 'menu.enregistrer_courrier', 'label' => 'Enregistrer un courrier', 'groupe' => 'menu'],
        ['code' => 'menu.reception', 'label' => 'Réception', 'groupe' => 'menu'],
        ['code' => 'menu.traitement', 'label' => 'Traitement', 'groupe' => 'menu'],
        ['code' => 'menu.delivrance', 'label' => 'Délivrance', 'groupe' => 'menu'],
        ['code' => 'menu.consultation', 'label' => 'Statistiques', 'groupe' => 'menu'],
        ['code' => 'menu.utilisateurs', 'label' => 'Gestion des utilisateurs', 'groupe' => 'menu'],
        ['code' => 'menu.referentiels', 'label' => 'Référentiels', 'groupe' => 'menu'],

        // Courriers
        ['code' => 'courriers.creer', 'label' => 'Créer / importer un courrier', 'groupe' => 'courriers'],
        ['code' => 'courriers.receptionner', 'label' => 'Confirmer une réception', 'groupe' => 'courriers'],
        ['code' => 'courriers.annuler_reception', 'label' => 'Annuler une réception', 'groupe' => 'courriers'],
        ['code' => 'courriers.reaffecter', 'label' => 'Réaffecter un courrier', 'groupe' => 'courriers'],
        ['code' => 'courriers.delivrer', 'label' => 'Délivrer un document (Accueil)', 'groupe' => 'courriers'],
        ['code' => 'courriers.annuler_depot', 'label' => 'Annuler un dépôt', 'groupe' => 'courriers'],
        ['code' => 'courriers.refuser_reception', 'label' => 'Refuser une réception (retour B.O.)', 'groupe' => 'courriers'],
        ['code' => 'stats.exporter', 'label' => 'Exporter les statistiques', 'groupe' => 'courriers'],

        // Délégations (responsables hiérarchiques — pas l'Accueil ni l'Admin technique)
        ['code' => 'delegations.voir', 'label' => 'Consulter les délégations', 'groupe' => 'delegations'],
        ['code' => 'delegations.creer', 'label' => 'Créer une délégation', 'groupe' => 'delegations'],
        ['code' => 'delegations.modifier', 'label' => 'Modifier une délégation planifiée', 'groupe' => 'delegations'],
        ['code' => 'delegations.annuler', 'label' => 'Annuler une délégation', 'groupe' => 'delegations'],

        // Administration
        ['code' => 'users.gerer', 'label' => 'Gérer les utilisateurs', 'groupe' => 'administration'],
        ['code' => 'categories.gerer', 'label' => 'Gérer les catégories', 'groupe' => 'administration'],
        ['code' => 'types_courrier.gerer', 'label' => 'Gérer les types de courrier', 'groupe' => 'administration'],
        ['code' => 'unites.gerer', 'label' => 'Gérer les unités', 'groupe' => 'administration'],
    ],

    /**
     * Droits par défaut (comportement historique de l'application).
     * Utilisés uniquement au seed initial.
     */
    'par_profil' => [
        'Admin' => [
            'menu.dashboard',
            'menu.utilisateurs',
            'menu.referentiels',
            'users.gerer',
            'categories.gerer',
            'types_courrier.gerer',
            'unites.gerer',
        ],
        // Accueil : dépôt + délivrance uniquement (ni réception ni traitement — agents métier).
        'Accueil' => [
            'menu.dashboard',
            'menu.enregistrer_courrier',
            'menu.delivrance',
            'menu.consultation',
            'courriers.creer',
            'courriers.delivrer',
            'courriers.annuler_depot',
            'stats.exporter',
        ],
        'Traitement' => [
            'menu.dashboard',
            'menu.reception',
            'menu.traitement',
            'menu.consultation',
            'courriers.receptionner',
            'courriers.annuler_reception',
            'courriers.reaffecter',
            'courriers.refuser_reception',
        ],
        'Consultation' => [
            'menu.dashboard',
            'menu.consultation',
            'stats.exporter',
        ],
        'Service' => [
            'menu.dashboard',
            'menu.reception',
            'menu.traitement',
            'menu.consultation',
            'courriers.receptionner',
            'courriers.annuler_reception',
            'courriers.reaffecter',
            'courriers.refuser_reception',
            'delegations.voir',
            'delegations.creer',
            'delegations.modifier',
            'delegations.annuler',
        ],
        'Brigade' => [
            'menu.dashboard',
            'menu.reception',
            'menu.traitement',
            'menu.consultation',
            'courriers.receptionner',
            'courriers.annuler_reception',
            'courriers.reaffecter',
            'courriers.refuser_reception',
            'delegations.voir',
            'delegations.creer',
            'delegations.modifier',
            'delegations.annuler',
        ],
        'Bureau' => [
            'menu.dashboard',
            'menu.reception',
            'menu.traitement',
            'menu.consultation',
            'courriers.receptionner',
            'courriers.annuler_reception',
            'courriers.reaffecter',
            'courriers.refuser_reception',
            'delegations.voir',
            'delegations.creer',
            'delegations.modifier',
            'delegations.annuler',
        ],
        'Section' => [
            'menu.dashboard',
            'menu.reception',
            'menu.traitement',
            'menu.consultation',
            'courriers.receptionner',
            'courriers.annuler_reception',
            'courriers.reaffecter',
            'courriers.refuser_reception',
            'delegations.voir',
            'delegations.creer',
            'delegations.modifier',
            'delegations.annuler',
        ],
        'Secteur' => [
            'menu.dashboard',
            'menu.reception',
            'menu.traitement',
            'menu.consultation',
            'courriers.receptionner',
            'courriers.annuler_reception',
            'courriers.reaffecter',
            'courriers.refuser_reception',
            'delegations.voir',
            'delegations.creer',
            'delegations.modifier',
            'delegations.annuler',
        ],
    ],
];
