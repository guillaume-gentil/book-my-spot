# API Symfony : système de réservation

## Pré-requis

`Symfony 5.4`, `PHP 8`, `MariaDB 10.6`, `Composer`, `Ubuntu 22.04`

## Installation

- cloner le projet
  ```sh
  git clone git@github.com:guillaume-gentil/book-my-spot.git
  ```

- installer les dépendances
  ```sh
  cd book-my-spot
  composer install
  ```

- configurer la connection à la Base de Données
  - ajouter dans le fichier `.env`
    ```ini
    DATABASE_URL="mysql://<username>:<password>@127.0.0.1:3306/<DB-name>?serverVersion=mariadb-<#-version>"
    ```
    - < username >, < password > : les identifiants de connexion à la BDD
    - < DB-name > : le nom de la BDD (que l'on peut créer via Symfony/Doctrine)
    - < #-version > : le numéro de version de mariaDB

- créer la BDD
  ```sh
  bin/console d:d:c
  ```

- exécuter les migrations
  ```sh
  bin/console d:m:m
  # [yes]
  ```

- générer les clés JWT
  ```sh
  bin/console lexik:jwt:generate-keypair
  ```

- créer un nouvel utilisateur
  ```sh
  bin/console app:create-user <email> <password>
  ```
  - < email > : l'email de l'utilisateur, ex: `01@food.truck`
  - < password > : le mot de passe de l'utilisateur, ex: `123`

## Utilisation

### Endpoints

| Route | HTTP Method | Controller | paramètres d'URL | Body (JSON) | Auth (Bearer) |
|---|---|---|---|---|---|
| /api/login_check | POST | - | - | {"username": "01@food.truck", "password":"123"} | - |
| /api/v1/bookings | GET | BookingController | - | - | Bearer Token (JWT) |
| /api/v1/bookings/{date} | GET | BookingController | format de la {date} : yyyymmdd (20230327) | - | Bearer Token (JWT) |
| /api/v1/bookings | POST | BookingController | - | {"date": "20230407"} | Bearer Token (JWT) |
| /api/v1/bookings/{id} | DELETE | BookingController | format de l'{id} : `integer` (1) | - | Bearer Token (JWT) |


## Dictionnaire de données

### Entité `User`

| Champ | Type | Spécificités | Description |
|---|---|---|---|
| `id` | INT | PRIMARY KEY, NOT NULL, UNSIGNED, AUTO_INCREMENT | Identifiant unique |
| `email` | VARCHAR(180) | NOT NULL | Email unique pour l'authentification de l'utilisateur (ie: un foodtruck) |
| `roles` | JSON | NOT NULL | Par défaut ["ROLE_USER"] |
| `password` | VARCHAR(255) | NOT NULL | Le mot de passe de l'utilisateur, hashé dans le base de donnée |

### Entité `Booking`

| Champ | Type | Spécificités | Description |
|---|---|---|---|
| `id` | INT | PRIMARY KEY, NOT NULL, UNSIGNED, AUTO_INCREMENT | Identifiant unique |
| `date` | DATETIME_IMMUTABLE | NOT NULL | La date de la réservation |
| `foodtruck_id` | INT | FOREIGN KEY | L'id de l'utilisateur qui a effectué la réservation |
