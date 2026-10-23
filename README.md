# DevCoffee

Projet DevCoffee : 

● Back-end : PHP 8+, Slim Framework 4, PHP-DI (IoC Container), Monolog, PDO/ MySQL.

● Front-end : Vue 3 (Composition API avec <script setup>).

● Format des données : application/json et application/problem+json (RFC7807).

>[!warning]
>EN CAS D'ERREUR
>
>Dans backend faire `composer install`, dans frontend faire `npm install` et  `bun install` > 


## Demarrage sous Windows

1. Demarre MySQL.
2. La base `devcoffee` et les tables doivent deja exister. Si besoin, execute `backend/schema.sql` manuellement dans MySQL Workbench.

  <img width="786" height="523" alt="image" src="https://github.com/user-attachments/assets/8afc3225-5d90-4717-9326-9c55febde3e4" />

3. Verifie `backend/.env` (par defaut : `127.0.0.1:3306`, utilisateur `root`, mot de passe vide).

```env
DB_HOST=127.0.0.1
DB_PORT=3306
DB_NAME=devcoffee
DB_USERNAME=votre_username
DB_PASSWORD=votre_mdp
APP_TIMEZONE=Europe/Paris
```

4. Ajouter un frontend/.env

```env

VITE_API_BASE_URL=http://127.0.0.1:8000/api

```

5. Lance `launchServers.bat`.

- Interface : http://127.0.0.1:5173
- API : http://127.0.0.1:8000/api/drinks (TESTABLE AVEC BRUNO)

## Prerequis

PHP 8.1+ avec `pdo_mysql`, MySQL demarre, et Bun ou Node.js. Si ton installation MySQL utilise d'autres identifiants, modifie `backend/.env`.

Deux fichiers .env a créer


## Fonctionnalites du TP

- Boissons et consommations via l'API REST
- Limite de 400 mg de cafeine par developpeur et par jour
- Erreurs au format RFC 7807 et liens HATEOAS
- En-tetes de sobriete numerique (temps, taille du payload, CO2 estime)
- CORS et journalisation des erreurs



# Équipe

- Bogdane BRAOUN (Bo-Br) | Backend/LeadDev
- Nathanael LECRON       | Frontend
- Louis BOSSOUTROT       | Backend
