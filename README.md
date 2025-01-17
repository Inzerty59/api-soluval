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
   4.Installer webpack
   npm install @symfony/webpack-encore --save-dev

Une fois dans le conteneur - build le front
   php bin/console tailwind:build -w