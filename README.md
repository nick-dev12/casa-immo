# casa-immo

# Zig Imobilier — Casamance

Plateforme immobilière dédiée à la **Casamance** (Ziguinchor, Cap Skirring, Oussouye, Bignona, Sedhiou…). PHP natif MVC, MySQL/PDO.

## Accès au site

```
http://localhost/zig-imobilier/
```

Le point d'entrée est **`index.php`** à la racine du projet.

## Structure du projet

```
zig-imobilier/
├── index.php          → Point d'entrée principal
├── app/               → Controllers, Models, Services, Core, Middleware, Helpers
├── config/            → Configuration (app, database, mail)
├── routes/            → Routes web
├── views/             → Templates PHP
├── assets/            → CSS, JS, images (publics)
├── uploads/           → Fichiers uploadés (logements, terrains)
├── database/          → Schéma SQL, migrations, seeders
├── admin/             → Administration (étape 13)
├── api/               → API REST (étape 14)
├── storage/           → Logs et cache
└── vendor/            → Autoload Composer
```

## Installation

```bash
cd /Applications/XAMPP/xamppfiles/htdocs/zig-imobilier
cp .env.example .env
composer install   # optionnel
```

Importer la base de données :

```bash
mysql -u root < database/schema.sql
mysql -u root ariaqqrw_casa_immo < database/seeders/demo_data.sql
```

## Prérequis

- PHP 8.3+
- MySQL / MariaDB
- Apache avec `mod_rewrite`

## Compte test

- Email : `admin@zig-imobilier.sn`
- Mot de passe : `Admin@123`
