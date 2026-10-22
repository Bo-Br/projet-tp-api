# DevCoffee

Projet DevCoffee adapte a la structure existante : Slim 4 / PHP-DI et Vue 3 / TypeScript. Les dependances PHP (`backend/vendor`) et frontend (`frontend/node_modules`) sont incluses.

## Demarrage sous Windows

1. Demarre MySQL.
2. La base `devcoffee` et les tables doivent deja exister. Si besoin, execute `backend/schema.sql` manuellement dans MySQL Workbench.
3. Verifie `backend/.env` (par defaut : `127.0.0.1:3306`, utilisateur `root`, mot de passe vide).
4. Lance `launchServers.bat`.

Le script demarre simplement l'API et l'interface. Il ne cree ni ne modifie la base de donnees.

- Interface : http://127.0.0.1:5173
- API : http://127.0.0.1:8000/api/drinks

## Prerequis

PHP 8.1+ avec `pdo_mysql`, MySQL demarre, et Bun ou Node.js. Si ton installation MySQL utilise d'autres identifiants, modifie `backend/.env`.

## Fonctionnalites du TP

- Boissons et consommations via l'API REST
- Limite de 400 mg de cafeine par developpeur et par jour
- Erreurs au format RFC 7807 et liens HATEOAS
- En-tetes de sobriete numerique (temps, taille du payload, CO2 estime)
- CORS et journalisation des erreurs
