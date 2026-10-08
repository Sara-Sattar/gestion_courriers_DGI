# Gestion DGI2 — Gestion du courrier

Application web de gestion du courrier entrant pour une Direction Régionale des Impôts (DGI),
réalisée dans le cadre d'un stage. Elle suit chaque courrier déposé par un contribuable, de son
enregistrement au bureau d'ordre jusqu'à la délivrance de la réponse, avec une traçabilité
complète des actions.

## Fonctionnalités

- **Enregistrement** des courriers au guichet (saisie manuelle ou import Excel), avec numéro unique
  au format `AAAA-NNNNNN-CODE` (ex. `2026-000007-BO001`) et reçu de dépôt PDF.
- **Catégories et types de courrier** ; chaque catégorie indique si le courrier nécessite une
  réponse (sinon il est archivé dès le dépôt).
- **Réception** par les agents de l'unité destinataire : confirmation, refus (retour au bureau
  d'ordre) ou refus de réaffectation (retour à l'unité précédente).
- **Réaffectation** vers une autre unité, possible uniquement après confirmation de la réception
  et par l'agent responsable du courrier.
- **Traitement** : favorable, rejet ou suspension (avec reprise).
- **Délivrance** des documents (intéressé, mandataire, envoi postal) et clôture du dossier.
- **Délégations** : remplacement temporaire d'un agent absent, avec traçabilité « pour le compte de ».
- **Statistiques** et exports (PDF, Excel/CSV).
- **Administration** : utilisateurs, profils et permissions, unités, catégories et types de courrier.

## Profils

| Profil | Rôle principal |
|---|---|
| Admin | Utilisateurs, permissions et référentiels |
| Accueil | Enregistrement, délivrance, statistiques |
| Traitement | Réception, réaffectation et traitement des courriers de son unité |
| Service, Brigade, Bureau, Section, Secteur | Responsables d'unité : comme Traitement + gestion des délégations |
| Consultation | Statistiques en lecture seule |

Les droits de chaque profil sont définis dans `config/permissions.php` et peuvent être ajustés
par utilisateur depuis l'administration.

## Technologies

- PHP 8.2+, [Laravel 12](https://laravel.com)
- MySQL / MariaDB
- Blade, Bootstrap 5, Bootstrap Icons, Alpine.js (Vite)
- [barryvdh/laravel-dompdf](https://github.com/barryvdh/laravel-dompdf) (PDF),
  [maatwebsite/excel](https://laravel-excel.com) (import / export Excel)
- PHPUnit (tests sur SQLite en mémoire)

## Prérequis

- PHP ≥ 8.2 avec les extensions `pdo_mysql`, `mbstring`, `gd`, `zip`, `xml`
- Composer 2
- Node.js ≥ 18 et npm
- MySQL 8 ou MariaDB 10.4+ (par exemple via [XAMPP](https://www.apachefriends.org))

## Installation

```bash
git clone https://github.com/<votre-compte>/gestion_courrier.git
cd gestion_courrier

composer install
npm install
npm run build

cp .env.example .env
php artisan key:generate
```

Adapter ensuite la section base de données de `.env` si nécessaire (par défaut : base
`gestion_dgi2`, utilisateur `root` sans mot de passe).

### Base de données

Créer la base puis lancer les migrations et les données de démonstration :

```bash
mysql -u root -e "CREATE DATABASE gestion_dgi2 CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci"
php artisan migrate --seed
```

Alternative sans ligne de commande Laravel : importer `database/sql/gestion_dgi2.sql` dans
phpMyAdmin (voir [database/sql/README.md](database/sql/README.md)).

## Lancement

```bash
php artisan serve
```

L'application est disponible sur <http://127.0.0.1:8000>.

Les délégations planifiées sont activées et clôturées automatiquement par la commande
`delegations:expirer`, programmée chaque jour. En local, lancer le planificateur avec :

```bash
php artisan schedule:work
```

## Comptes de démonstration

Tous les comptes créés par les seeders utilisent le mot de passe **`password`**.

| Compte | Profil | Unité |
|---|---|---|
| `admin@dgi.ma` | Admin | DR001 |
| `accueil@dgi.ma` | Accueil | BO001 (bureau d'ordre) |
| `agent.bureau1@dgi.ma`, `agent.bureau2@dgi.ma` | Traitement | BO001 |
| `agent.service1@dgi.ma`, `agent.section1@dgi.ma` | Traitement | SERV002 |
| `agent.brigade1@dgi.ma` | Traitement | BRIG001 |
| `bureau@dgi.ma` | Bureau (chef) | BO001 |
| `service.it@dgi.ma` | Service (chef) | SERV002 |
| `brigade@dgi.ma` | Brigade (chef) | BRIG001 |
| `secteur.gueliz@dgi.ma` | Secteur (chef) | SECT001 |
| `recouvrement@dgi.ma` | Bureau (chef) | BUR003 |

> Ces comptes sont destinés à la démonstration uniquement : changez les mots de passe avant tout
> déploiement réel. Les contribuables de démonstration sont fictifs.

## Tests

```bash
php artisan test
```

Les tests utilisent une base SQLite en mémoire (`phpunit.xml`) et ne modifient jamais la base
MySQL de développement.

## Structure du projet

```
app/
├── Console/Commands/   Commandes artisan (expiration des délégations, génération de rapports PDF)
├── Enums/              Statuts, actions d'historique, types d'impôt…
├── Exports/ Imports/   Import / export Excel
├── Http/               Contrôleurs et Form Requests (validation)
├── Models/             Modèles Eloquent
├── Policies/           Autorisations (courriers, utilisateurs)
└── Services/           Logique métier (enregistrement, réception, traitement, délivrance, délégation…)
config/
├── dgi.php             Libellés et logos de la direction
└── permissions.php     Catalogue des permissions et droits par profil
database/
├── migrations/         Structure de la base
├── seeders/            Données de démonstration
└── sql/                Export SQL complet (installation via phpMyAdmin)
resources/views/        Vues Blade
public/css, public/js   Styles et scripts propres à l'application
tests/Feature/          Tests fonctionnels du circuit courrier
Rapport/                Rapport de stage (PDF)
```
