# WebScrapBI
### Projet de
- CATOIS Baptiste
- MERLETTE Bastien

# Comment installer le projet
## Commandes
```bash

# Récupère l'application
git clone https://github.com/Lilierd/WebScrapBI laravel-application


# La suite des commandes est à éxecuter dans le répertoire racine de l'application
cd laravel-application

# Installe le container docker 
# composer dans l'instance 
docker run --rm \
    -u "$(id -u):$(id -g)" \
    -v "$(pwd):/var/www/html" \
    -w /var/www/html \
    laravelsail/php83-composer:latest \
    composer install --ignore-platform-reqs

cp .env.example .env

# Génère une clef afin d'encrypter certaines données si souhaité (voir doc Laravel) 
./vendor/bin/sail artisan key:generate

 # Création de la base de données (permet une réinitialisation également)
./vendor/bin/sail artisan migrate:fresh

# Permet de lier le stockage local avec le stockage du docker.
./vendor/bin/sail artisan storage:link  

# Plutôt que de tapper `./vendor/bin/sail` à chaque commande 
# vous pouvez définir un alias global ou local vers sail.
alias sail='sh $([ -f sail ] && echo sail || echo vendor/bin/sail)
```
[Documentation officielle de Laravel pour la configuration de l'alias](https://laravel.com/docs/11.x/sail#configuring-a-shell-alias)

## Editions particulières
ATTENTION : ne pas oublier de mettre à jour les variables du .env (l'utilisateur Boursorama possède une valeur par défaut et est donc optionnel).

# Exploitation
## Commandes
### Lancer les services
`cd laravel-application && ./vendor/bin/sail up -d`
### Arrêter les services
`cd laravel-application && ./vendor/bin/sail down`
### Commande d'exécution principale du programme
`cd laravel-application && ./vendor/bin/sail artisan boursorama:aggregate`
- `-h` : Obtenir l'aide à l'exécution.
- `--url=<url>` : Ne **PAS UTILISER** avec les **OPTIONS** : `--fresh` ou `--ms`. Indexe les actions et capture les données live pour ces urls d'actions particulières.
- `--fresh` : Récupérer l'ensemble des actions de la page d'accueil des actions éligibles au SRD.
- `-vvv` : Exécuter en mode **très verbeux**.
- `--ms=<Nom de l'action>` : Récupérer les valeurs d'une ou plusieurs action(s) particulière(s) (nécessite au moins un jeu de données dans le base).
- `-n` : Exécution en mode **non-interactif** (aucune demande à l'utilisateur).
- `--download` : Exécution avec récupération d'un fichier par action qui répertorie les opération des dernières 24h.
- `--messages` : Exécution avec récupération des messages du forum de(s) l'action(s)

## Exemples de commandes :
### Récupérer les données lives et/ou indexer des actions :
- Si vous n'avez pas déjà indexé des actions, vous pouvez utiliser ces commandes pour indexer des actions et, récupérer leurs données live par la même occasion:
    - En spécifiant l'URL :
        - `sail artisan boursorama:aggregate --url="https://www.boursorama.com/cours/1rPAB/" --url="https://www.boursorama.com/cours/1rPOVH/" -vvv`
    - En crawlant la liste des actions éligibles au SRD pour établier un jeu de donnée arbitraire et,  récupérer leurs données live par la même occasion:
        - `sail artisan boursorama:aggregate` 
        - ou son équivalent avec l'option `--fresh` spécifiée, si ce n'est pas la première éxecution : `sail artisan boursorama:aggregate --fresh`
- Sinon vous pouvez utiliser la commande :
    - En spécifiant l'URL :
        - `sail artisan boursorama:aggregate --url="https://www.boursorama.com/cours/1rPAB/" --url="https://www.boursorama.com/cours/1rPOVH/" -vvv`
    - En précisant le nom de l'action directement (le mode non interactif est conseillé `-n`):
        - `sail artisan boursorama:aggregate --ms="OVHCLOUD" --ms="AB SCIENCE" -n -vvv`
    - `sail artisan boursorama:aggregate -vvv`
        - Vous serez invités à suivre l'éxecution et choisir l'action désirée en saisissant son nom ou son indice dans la liste. Vous pouvez sélectionner de multiples valeurs en les séparant par des virgules. La saisie du nom est autocomplétée (vous pouvez - *normalement* - utiliser la touche TAB).

### Récupérer les conversations des actions :
Peu importe que vous spécifiez l'URL, un nom d'action ou aucune des options vues précedemment, si vous ajoutez l'option `--messages` le scraper ira récupérer les conversations des actions spécifiées.

### Récupérer les fichiers des données précédentes des actions :
Peu importe que vous spécifiez l'URL, un nom d'action ou aucune des options vues précedemment, si vous ajoutez l'option `--download` le scraper ira télécharger le fichier des actions spécifiées.

Le dossier est accessible dans la dossier suivant de l'application :
```txt
laravel-application/
├── app
├── bootstrap
├── config
├── database
├── docker
├── ...
└── storage/
    └── app/
        └── public/
            ├── FR001400CFI7/ <= Code ISIN.
            └── ...
```
- `ls ./storage/app/public/`

Les fichiers sont automatiquement exposés sur le serveur Web, ce qui vous permet de les récupérer en dehors de la machine hôte si le serveur à accès au réseau.

Exemple d'URL pour l'action dont le code ISIN est FR0000054900:
- `http://localhost:8080/storage/FR0000054900/data.csv`
- `http://localhost:8080/storage/FR0000054900/2024-04-09_15-24.txt`

### (Optionnel) Installer un crontab pour éxecuter des commandes programmées :
- `crontab -e`
- `* * * * * cd /path/to/laravel-application && ./vendor/bin/sail artisan schedule:run >> /dev/null 2>&1 `

Vérifiez les commandes programmées dans :
```
laravel-application/
├── app
├── bootstrap
├── config
├── database
├── docker
├── ...
└── routes/
    └── console.php
```


## Chemins des fichiers de téléchargement
Toutes les données téléchargées par l'application sont stockées dans :
- `/path/to/laravel-application/storage/app/public`
```txt
laravel-application/
├── app
├── bootstrap
├── config
├── database
├── docker
├── ...
└── storage/
    └── app/
        └── public/
            ├── FR001400CFI7/ <= Code ISIN.
            └── ...
```

# Services
## Laravel (Sail)
- http://localhost:8080/ (Page de navigation pour visualiser les données récoltées)
## MySQL
Par défaut, sinon révisez votre version du fichier `.env`:
- localhost:3306
Mot de passe et nom d'utilisateur dans le `.env` également, par défaut :
```txt
DB_DATABASE=laravel-app
DB_USERNAME=laravel-sail-user
DB_PASSWORD=password
```

## Selenium
- http://localhost:7900/
- http://localhost:4444/


