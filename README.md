

# Comment installer le projet
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

./vendor/bin/sail artisan migrate:fresh
```
# Lancer les services
`cd laravel-application && ./vendor/bin/sail up -d`

# Arrêter les services
`cd laravel-application && ./vendor/bin/sail down`



# Services
## Laravel (Sail)
- http://localhost:8080/
## MySQL
Par défaut :
- localhost:3306
## phpMyAdmin
- http://localhost:8081/
## Selenium
- http://localhost:7900/
- http://localhost:4444/


