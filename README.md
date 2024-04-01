# WebScrapBI
### Projet de
- CATOIS Baptiste
- MERLETTE Bastien

# Comment installer le projet
## Commandes
```bash
git clone https://github.com/Lilierd/WebScrapBI laravel-application

cd laravel-application

docker run --rm \
    -u "$(id -u):$(id -g)" \
    -v "$(pwd):/var/www/html" \
    -w /var/www/html \
    laravelsail/php83-composer:latest \
    composer install --ignore-platform-reqs

cp .env.example .env

./vendor/bin/sail artisan key:generate

./vendor/bin/sail artisan migrate:fresh # Création de la base de données (permet une réinitialisation également)

./vendor/bin/sail artisan storage:link  # Permet de lier le stockage local avec le stockage du docker.
```
## Editions particulières
ATTENTION : ne pas oublier de mettre à jour les variables du .env (l'utilisateur Boursorama possède une valeur par défaut et est donc optionnel).

# Exploitation
## Commandes
### Lancer les services
`cd laravel-application && ./vendor/bin/sail up -d`
### Arrêter les services
`cd laravel-application && ./vendor/bin/sail down`
### Commande d'exécution principale du programme
`cd laravel-application && ./vendor/bin/sail boursorama:aggregate`
- `-h` : Obtenir l'aide à l'exécution
- `--fresh` : Récupérer l'ensemble des actions de la page d'accueil
- `-vvv` : Exécuter en mode **très verbeux**
- `--ms=<Nom de l'action>` : Récupérer les valeurs d'une action particulière
## Chemins des fichiers de téléchargement
Toutes les données téléchargées par l'application sont stockées dans :
`laravel-application/storage/app/public`

# Services
## Laravel (Sail)
- http://localhost:8080/ (Page de navigation pour visualiser les données récoltées)
## MySQL
Par défaut :
- localhost:3306
## phpMyAdmin
- http://localhost:8081/
## Selenium
- http://localhost:7900/
- http://localhost:4444/


