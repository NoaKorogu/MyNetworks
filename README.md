# 🌍 MyNetworks - Système d'Information Géographique

## 📌 Description

L’objectif est de permettre aux entreprises et collectivités de constituer un référentiel géographique informatisé (SIG, Système d’Information Géographique) de leurs différents réseaux et de les rendre accessibles de n’importe où afin d’augmenter les performances de leurs activités.

## 🚀 Fonctionnalités principales

- 📍 **Gestion des réseaux** :
  - Transport en commun
  - Réseaux d'énergie
  - Réseaux d'eau potable
- 🗺️ **Cartographie interactive** avec OpenStreetMap
- 📌 **Géolocalisation des éléments** :
  - Points d'intérêt (ex: fontaines, arrêts de bus, transformateurs...)
  - Tracés linéaires (ex: lignes de bus, câbles électriques, canalisations)
- ✏️ **Gestion des éléments** (CRUD) :
  - Création, modification et suppression des éléments du réseau
  - Ajout d'informations comme le nom, le type et d'autres détails utiles
- 👤 **Gestion des utilisateurs et des rôles**
- ⏳ **Historique des modifications** :
   - Retrouver les versions antérieures d'un élément
- 🔍 **Filtrage des réseaux** :
  - Masquer ou afficher un ou plusieurs réseaux

---

## ⚙️ Technologies utilisées

- **Backend** : Symfony 7.x
- **Base de données** : PostgreSQL + PostGIS
- **Frontend** : Twig et Leaflet.js pour la map
- **Serveur Web** : Nginx
- **Authentification** : CSRF
- **Gestion des requêtes spatiales** : Doctrine avec longitude-one/doctrine-spatial

---

## 📥 Installation

### 📦 Prérequis

- PHP 8.x
- Composer
- PostgreSQL avec PostGIS
- Node.js
- Symfony CLI
- Nginx

### 🛠 Configuration et installation

1. **Cloner le projet**

   ```sh
   https://github.com/NoaKorogu/MyNetworks.git
   cd MyNetworks
   ```

2. **Backend : Installer les dépendances Symfony**

   ```sh
   composer install
   ```

3. **Configurer la base de données**
   - Renommer `.env.local` en `.env`
   - Modifier la variable `DATABASE_URL` :
     ```env
     DATABASE_URL="postgresql://user:password@127.0.0.1:5432/my_network_db?serverVersion=16&charset=utf8"
     ```

4. **Créer la base et les tables**

   ```sh
   php bin/console doctrine:database:create
   php bin/console doctrine:migrations:migrate
   ```

5. **Démarrer le serveur Symfony**

   ```sh
   symfony server:start
   ```

6. **Accéder à l'application**

   - Ouvrez votre navigateur et accédez à http://localhost:8000

---

## 📊 Dump de la base de données (a ajouter)


