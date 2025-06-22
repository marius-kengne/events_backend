#!/bin/sh

echo " Installation des dépendances..."
composer install

if [ ! -f config/jwt/private.pem ]; then
  echo " Génération des clés JWT..."
  php bin/console lexik:jwt:generate-keypair
fi

echo " Exécution des migrations sur la base principale..."
php bin/console doctrine:migrations:migrate --no-interaction --env=prod

echo " Exécution des migrations sur la base de test..."
php bin/console doctrine:migrations:migrate --no-interaction --env=test

echo " Lancement du serveur Symfony..."
php -S 0.0.0.0:8000 -t public
