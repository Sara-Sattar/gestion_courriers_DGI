# Base de données — import SQL

`gestion_dgi2.sql` contient la structure complète et les données de démonstration de l'application.
Il est généré à partir des migrations et seeders Laravel : c'est une alternative à
`php artisan migrate --seed` pour une installation via **phpMyAdmin**.

> ⚠️ L'import **supprime puis recrée** toutes les tables de la base `gestion_dgi2`.

## Import

- **phpMyAdmin** : onglet *Importer* → choisir `database/sql/gestion_dgi2.sql` → *Exécuter*.
- **Ligne de commande** :

  ```bash
  mysql -u root < database/sql/gestion_dgi2.sql
  ```

Le script crée la base `gestion_dgi2` si elle n'existe pas et enregistre les migrations déjà
appliquées : `php artisan migrate` n'a ensuite plus rien à exécuter.

## Régénérer le fichier

Après une modification des migrations ou des seeders, recréer le fichier depuis une base vide :

```bash
php artisan migrate:fresh --seed
mysqldump -u root --skip-extended-insert --complete-insert gestion_dgi2 > database/sql/gestion_dgi2.sql
```

## Tests

`phpunit.xml` force les tests à utiliser une base **SQLite en mémoire** : `php artisan test`
ne touche jamais la base MySQL de développement.
