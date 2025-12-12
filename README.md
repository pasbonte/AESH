# Gestion AESH - Application PHP/MySQL

## Installation

### 1. Copier les fichiers
Copier tout le dossier `gestion_aesh` dans votre répertoire web (ex: `C:\xampp\htdocs\` ou `/var/www/html/`)

### 2. Créer la base de données
- Ouvrir phpMyAdmin
- Importer le fichier `database.sql` ou copier/coller son contenu dans l'onglet SQL

### 3. Configurer la connexion
Éditer `config.php` si nécessaire :
```php
define('DB_HOST', 'localhost');
define('DB_NAME', 'gestion_aesh');
define('DB_USER', 'root');
define('DB_PASS', '');
```

### 4. Accéder à l'application
- URL : `http://localhost/gestion_aesh/`

---

## Identifiants de connexion

### Compte Administrateur
- Login : `admin`
- Mot de passe : `aesh2025`

### Comptes Établissements
Chaque établissement se connecte avec :
- Login : Le nom de l'établissement en majuscules (ex: `PONSARD`, `ROBIN`, `SAINT-CHARLES`)
- Mot de passe : Code à 5 chiffres unique

#### Liste des mots de passe par défaut :
| Établissement | Login | Mot de passe |
|---------------|-------|--------------|
| BELLERIVE Lycée pro | BELLERIVE | 10137 |
| BELLERIVE ULIS Lycée | BELLERIVE | 10274 |
| BERT Ecole | BERT | 10411 |
| BUISSON Ecole | BUISSON | 10548 |
| CHORIER Ecole | CHORIER | 10685 |
| EYZIN-PINET Ecole | EYZIN-PINET | 10822 |
| LAFAYETTE Ecole | LAFAYETTE | 10959 |
| MARCEL Ecole | MARCEL | 11096 |
| PONSARD Collège | PONSARD | 11233 |
| PONSARD ULIS Collège | PONSARD | 11370 |
| ROBIN BTS | ROBIN | 11507 |
| ROBIN Collège | ROBIN | 11644 |
| ROBIN Ecole | ROBIN | 11781 |
| ROBIN Lycée | ROBIN | 11918 |
| ROBIN Lycée pro | ROBIN | 12055 |
| ROBIN ULIS Collège | ROBIN | 12192 |
| ROBIN ULIS Lycée pro | ROBIN | 12329 |
| ROSTAND Ecole | ROSTAND | 12466 |
| ROSTAND ULIS | ROSTAND | 12603 |
| RÉP Ecole maternelle | RÉP | 12740 |
| RÉP Ecole élémentaire | RÉP | 12877 |
| SAINT-CHARLES Collège | SAINT-CHARLES | 13014 |
| SAINT-CHARLES Ecole | SAINT-CHARLES | 13151 |
| SAINT-CHARLES Lycée | SAINT-CHARLES | 13288 |
| SAINT-LOUIS Ecole | SAINT-LOUIS | 13425 |
| SEPTÈME Ecole | SEPTÈME | 13562 |
| TABLE-RONDE Ecole | TABLE-RONDE | 13699 |

**Note** : Les établissements avec le même login (ex: PONSARD, ROBIN) verront tous leurs élèves (collège + ULIS, etc.)

---

## Fonctionnalités

### Pour les établissements
- Voir uniquement LEURS élèves
- Champs en **lecture seule** : Élève, Classe, Notif. I, Notif. M, Plan B
- Champs **modifiables** : AESH, Heures effectives, Remarques

### Pour l'administrateur
- Voir et modifier TOUS les champs de TOUS les élèves
- Ajouter / Supprimer des élèves
- Modifier les mots de passe des établissements
- Filtrer par établissement ou rechercher un élève
- Exporter les données en CSV (compatible Excel)

---

## Fichiers

```
gestion_aesh/
├── config.php        # Configuration BDD et fonctions
├── index.php         # Page de connexion
├── etablissement.php # Interface établissement
├── admin.php         # Interface administration
├── export.php        # Export CSV
├── logout.php        # Déconnexion
├── style.css         # Feuille de style
├── database.sql      # Script création BDD + données
└── README.md         # Ce fichier
```
