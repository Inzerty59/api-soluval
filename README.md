# API Soluval

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