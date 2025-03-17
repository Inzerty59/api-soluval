# API Soluval
Créer le .env
Créer le fichier public.key + private.key dans le config/keys
## Démarrer le projet

   1.Lancer les conteneurs Docker en arrière-plan
   ```bash
   docker compose up -d --build
   ```
   2. Accéder au conteneur
   ```bash
   docker exec -it www-api bash
   ```
   3. Installer les dépendances Composer
   ```bash
   composer install
   ```
   4. Installer webpack et tailwind 
   ```bash
   composer require symfonycasts/tailwind-bundle
   ```
   ```bash
   php bin/console tailwind:build -w
   ```
   ```bash
   npm install @symfony/webpack-encore --save-dev
   ```
   ```bash
   npm run dev
   ```
php bin/console doctrine:fixtures:load

php bin/console make:migration
php bin/console doctrine:migrations:migrate

composer require stripe/stripe-php

# Si erreur cache
chown -R www-data:www-data /var/www/api-soluval/var/cache
chmod -R 775 /var/www/api-soluval/var/cache
php bin/console cache:clear --env=prod

🚨 **Ce projet est sous licence propriétaire** 🚨  
Toute utilisation, modification ou redistribution est **strictement interdite** sans autorisation.  
Consultez le fichier `LICENCE` pour plus de détails.