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
   docker exec -it www_api-soluval bash
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


🚨 **Ce projet est sous licence propriétaire** 🚨  
Toute utilisation, modification ou redistribution est **strictement interdite** sans autorisation.  
Consultez le fichier `LICENCE` pour plus de détails.