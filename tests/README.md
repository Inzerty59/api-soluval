# API Soluval

## Lancer les tests unitaires

   1.Lancer les conteneurs Docker en arrière-plan
   ```bash
   docker compose up -d --build
   ```
   2. Accéder au conteneur
   ```bash
   docker exec -it www_api-soluval bash
   ```
   3. Lancer le test PHPUNIT
   ```bash
   ./vendor/bin/phpunit <Chemin du fichier>

   

   ```