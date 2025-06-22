# 📦 events_backend

Une API REST développée avec Symfony pour gérer des événements, avec authentification JWT.

---

## 🚀 Fonctionnalités principales

- ✅ Authentification par JWT (`/api/login`, `/api/register`)
- 🎫 CRUD complet d'événements
- 📢 Publication d’un événement par un organisateur
- 🔐 Rôles utilisateur (`ROLE_USER`, `ROLE_ORGANIZER`)
- 🛡️ Sécurisation des endpoints selon le rôle

---

## 📁 Structure du projet

- `api/src/Controller/` – Contrôleurs pour les routes API
- `api/src/Entity/` – Entités Doctrine (`User`, `Event`)
- `api/src/Repository/` – Requêtes personnalisées
- `api/config/packages/lexik_jwt_authentication.yaml` – Configuration JWT
- `docker-compose.yml` – Configuration des services Docker
- `entrypoint.sh` – Script d'initialisation (install, migrations, serveur)

---

## 🔐 Authentification

### Endpoints publics :

- `POST /api/register` – Créer un compte (`email`, `password`, `roles[]`)
- `POST /api/login` – Récupérer un token JWT (`username`, `password`)

> Toutes les routes suivantes nécessitent le header :
>  
> `Authorization: Bearer <token>`

---

## 📡 Endpoints principaux

### 🎫 Événements

| Méthode | Endpoint                   | Description                          |
|--------:|----------------------------|--------------------------------------|
| GET     | `/api/events`              | Récupérer tous les événements        |
| POST    | `/api/events`              | Créer un événement (organisateur)   |
| GET     | `/api/events/{id}`         | Voir les détails d’un événement      |
| PUT     | `/api/events/{id}`         | Mettre à jour un événement           |
| DELETE  | `/api/events/{id}`         | Supprimer un événement               |
| POST    | `/api/events/{id}/publish` | Publier un événement (organisateur)  |

---

## 🧪 Exemple de payload

```json
// Connexion
{
  "username": "test@example.com",
  "password": "test123"
}

// Enregistrement
{
  "email": "user1@example.com",
  "password": "pass123",
  "roles": ["ROLE_USER"]
}
```

---

## 🛠️ Installation & Lancement

### 1. Cloner le projet

```bash
git clone https://github.com/marius-kengne/events_backend.git
cd events_backend
```

### 2. Lancer avec Docker

```bash
docker-compose up --build -d
```

👉 Ce script :
- installe les dépendances (`composer install`)
- exécute les migrations (prod & test)
- génère les clés JWT (si non présentes)
- lance le serveur Symfony sur `http://localhost:8000`

---

## 🙋 Rôles et permissions

| Rôle             | Droits                                               |
|------------------|------------------------------------------------------|
| `ROLE_USER`      | Voir les événements                                 |
| `ROLE_ORGANIZER` | Créer, publier, modifier et supprimer ses événements |

---

## 🧩 Stack utilisée

- Symfony 6.x
- Doctrine ORM
- LexikJWTAuthenticationBundle
- MySQL 8
- Docker / Docker Compose

---

## ✨ Auteur

Développé par **Marius Kengne** – 2025
