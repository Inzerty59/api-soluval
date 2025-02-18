| **Section**               | **Critère**                                  | **État**               | **Importance** |**Remarques**              |
|---------------------------|----------------------------------------------|------------------------|----------------|---------------------------|
| **A. CONNEXION**                                                                                                   |                           |
|                           | Visuel                                       | OK                     | Basse          | Bouton deconnexion        |
|                           | Lien de connexion                            | Fonctionnel            | Haute          | Se connecter ?            |
|                           | Bouton se connecter                          | Fonctionnel            | Haute          | /                         |
|                           | Bouton se déconnecter                        | OK                     | Basse          | Peut être pas présent     |
|                           | Pas de Mail                                  | Fonctionnel            | Haute          | (1)                       |
|                           | Pas de mot de passe                          | Fonctionnel            | Haute          | (1)                       |
|                           | Affichage Mot de passe                       | Fonctionnel            | Moyenne        | /                         |
|                           | Mail sans @                                  | Fonctionnel            | Haute          | /                         |
|                           | Mail sans extension                          | Fonctionnel            | Haute          | /                         |
|                           | Mauvais Mot de Passe                         | Fonctionnel            | Haute          | /                         |
|                           | Mot de passe oublié                          | Erreur                 | Moyenne        | Pas d'erreur              |
|                           | Cas passant                                  | Fonctionnel            | Haute          | /                         |
|                           | Bouton Créer un compte                       | Fonctionnel            | Haute          | /                         |
| **B. INSCRIPTION**                                                                                                                             |
|                           | Visuel                                       | Fonctionnel            | Basse          | (1)                       |
|                           | Nom Vide                                     | Fonctionnel            | Haute          | (1)                       |
|                           | Nom avec caractères interdits                | OK                     | Haute          | Pas de message            |
|                           | Prénom Vide                                  | Fonctionnel            | Haute          | (1)                       |
|                           | Prénom avec caractères interdits             | OK                     | Haute          | Pas de message            |
|                           | Mail Vide                                    | Fonctionnel            | Haute          | (1)                       |
|                           | Mail sans @                                  | Fonctionnel            | Haute          | /                         |
|                           | Mail sans extension                          | OK                     | Haute          | Pas de message            |
|                           | Confirmation Mail Vide                       | Fonctionnel            | Haute          | (1)                       |
|                           | Confirmation de Mail sans @                  | Fonctionnel            | Haute          | /                         |
|                           | Confirmation de Mail sans extension          | OK                     | Haute          | Pas de message            |
|                           | Confirmation de Mail ≠ Mail                  | OK                     | Haute          | Pas de message            |
|                           | Confirmation de Mail + Mail sans extension   | Erreur                 | Basse          | Inscription accépté       |
|                           | Mot de passe Vide                            | Fonctionnel            | Haute          | (1)                       |
|                           | Mot de passe peu de caractère                | OK                     | Haute          | Pas de message *          |
|                           | Mot de passe mauvaise forme                  | OK                     | Haute          | Pas de message *          |
|                           | Visualisation du Mot de passe                | Erreur                 | Basse          | Pas d'action              |
|                           | Confirmation Mot de passe Vide               | Fonctionnel            | Haute          | (1)                       |
|                           | Confirmation de Mot de passe ≠ Mot de passe  | Ok                     | Haute          | Pas de message *          |
|                           | Confirmation Mot de passe peu de caractère   | OK                     | Haute          | Pas de message *          |
|                           | Confirmation Mot de passe mauvaise forme     | OK                     | Haute          | Pas de message *          |
|                           | Visualisation de la confirmation  Mdp        | Erreur                 | Basse          | Pas d'action              |
|                           | Cas passant Particulier                      | Fonctionnel            | Haute          | /                         |
|                           | Menu Type de compte                          | Fonctionnel            | Haute          | /                         |
|                           |                                              |                        |                |                           |
|                           | Réception de Mail de confirmation            | Erreur                 | Moyenne        | Pas de Mail               |
|                           |                                              |                        |                |                           |
|                           | Visuel professionnel                         | Fonctionnel            | Basse          | /                         |
|                           | Nom de l'entreprise Vide                     | OK                     | Haute          | /                         |
|                           | Nom de l'entreprise ≠ N° SIRET               | A faire                | Haute          |                           |
|                           | Nom de l'entreprise ≠ N° TVA                 | A faire                | Haute          |                           |
|                           | N° Siret Vide                                | OK                     | Haute          | /                         |
|                           | N° Siret incorrect                           | A faire                | Haute          |                           |
|                           | N° TVA Vide                                  | OK                     | Haute          | /                         |
|                           | N° TVA incorrect                             | A faire                | Haute          |                           |
|                           | N° SIRET ≠ N° TVA                            | A faire                | Haute          |                           |
|                           | Cas passant Professionnel                    | Erreur                 | Haute          | Aucune action             |
|                           | Créer un compte mais déja connecter          | A tester               | Moyenne        | Compte Créer              | 
| **C. BOUTIQUE**                                                                                                                                |
| 1) Entête                                                                                                                                      |
|                           | Logo Soluval                                 | Fonctionnel            | Moyenne        | /                         |
|                           | Se connecter                                 | OK                     | Basse          | Mon compte                |
|                           | Bouton Panier                                | Fonctionnel            | Haute          | /                         |
| 2) Type de pièce                                                                                                                               |
|                           | Recherche dynamique                          | Erreur                 | Moyenne        | Pas de propositions       |
|                           | Recherche                                    | Erreur                 | Haute          | Pas de resultats          |
|                           | Recherche inexistante                        | Erreur                 | Moyenne        | Pas d'action              |
| 3) Filtres                                                                                                                                     |
|                           | Marque                                       | Erreur                 | Haute          | Pas de marque             |
|                           | Modèle Grisée                                | Erreur                 | Basse          | A faire                   |
|                           | Modèle                                       | Erreur                 | Haute          | Pas de modèle             |
| 4) Recherche avancée                                                                                                                           |
|                           | Visuel                                       | Fonctionnel            | Basse          | /                         |
|                           | Code Moteur                                  | A tester               | Basse          |                           |
|                           | Code boîte                                   | A tester               | Basse          |                           |
|                           | Référence adaptable                          | A tester               | Basse          |                           |
|                           | Référence constructeur                       | A tester               | Basse          |                           |
| 5) Liste                                                                                                                                       |
|                           | Pièces trouvées                              | A tester               | Basse          |                           |
|                           | Bouton de pagination 10                      | A tester               | Basse          |                           |
|                           | Bouton de pagination 25                      | A tester               | Basse          |                           |
|                           | Bouton de pagination 50                      | A tester               | Basse          |                           |
|                           | Menu Trier par                               | A tester               | Basse          |                           |
|                           | Menu Trier par "Date"                        | A tester               | Basse          |                           |
|                           | Menu Trier par "Prix_Croissant"              | A tester               | Basse          |                           |
|                           | Menu Trier par "Prix_Décroissant"            | A tester               | Basse          |                           |
|                           | Cliquer sur l'image                          | A tester               | Moyenne        |                           |
|                           | Description précise                          | A tester               | Basse          |                           |
|                           | Bouton Ajout Panier                          | OK                     | Haute          | Dirigé vers le panier     |
|                           | Pagination fin de page                       | A tester               | Basse          |                           |
|                           | Recherche Multiple                           | A tester               | Basse          |                           |
| **D. PANIER**                                                                                                                                  |
|                           | Visuel                                       | Fonctionnel            | Basse          | /                         |
|                           | Description                                  | Fonctionnel            | Moyenne        | /                         |
|                           | Quantité                                     | Fonctionnel            | Moyenne        | /                         |
|                           | Bouton Supprimer                             | Fonctionnel            | Moyenne        | /                         |
|                           | Sous Total                                   | Fonctionnel            | Moyenne        | /                         |
|                           | Continuer vos achats                         | Fonctionnel            | Haute          | /                         |
|                           | Ajout même article de quantité 1             | Fonctionnel            | Moyenne        | /                         |
|                           | Finaliser la commande                        | Fonctionnel            | Haute          | /                         |
| **E. PAIEMENT**                                                                                                                                |
| 1) Page Livraison                                                                                                                              |
|                           | Visuel                                       | Fonctionnel            | Basse          | /                         |
|                           | Tableau des pièces commandées                | Fonctionnel            | Moyenne        | /                         |
|                           | Selection du mode de livraison               | Fonctionnel            | Haute          | /                         |
|                           | Adresse par défaut                           | Non Créer              | Haute          | Voir profil               |
|                           | Adresse de livraison                         | Fonctionnel            | Moyenne        | /                         |
|                           | Adresse de livraison Complément Adresse      | Erreur                 | Moyenne        | Peut être vide            |
|                           | Adresse de facturation                       | Fonctionnel            | Moyenne        | /                         |
|                           | Adresse de facturation Complément Adresse    | Erreur                 | Moyenne        | Peut être vide            |
|                           | Menu déroulant Pays                          | fonctionnel            | Moyenne        | /                         |
|                           | Bouton Continuer vers le paiement            | Fonctionnel            | Haute          | /                         |
|                           | Bouton Retour                                | Fonctionnel            | Haute          | /                         |
| 2) Page Récapitulatif                                                                                                                          |
|                           | Visuel                                       | Fonctionnel            | Basse          | /                         |
|                           | Tableau des pièces commandées                | Fonctionnel            | Moyenne        | /                         |
|                           | Vérifier les Totaux                          | Fonctionnel            | Basse          | /                         |
|                           | Bouton Régler ma commande                    | Fonctionnel            | Haute          | /                         |
|                           | Bouton Retour                                | Fonctionnel            | Haute          | /                         |
| 3) Page Finalisation                                                                                                                           |
|                           | Visuel                                       | Erreur                 | Basse          | /                         |
|                           | Tableau des pièces commandées                | Fonctionnel            | Moyenne        | /                         |
|                           | Mode de livraison                            | Fonctionnel            | Basse          | /                         |
|                           | Recapitulatif des adresses                   | Fonctionnel            | Moyenne        | /                         |
|                           | Bouton Régler la commande                    | Fonctionnel            | Haute          | /                         |
|                           | Bouton Retour                                | Fonctionnel            | Haute          | /                         |
| 4) Page Paiement CB (VISA ou Maesto)                                                                                                           |
|                           | Visuel                                       | Fonctionnel            | Basse          | /                         |
|                           | Remplissage des champs                       | Fonctionnel            | Haute          | /                         |
|                           | Bouton Payer par carte                       | Fonctionnel            | Haute          | /                         |
|                           | Page de confirmation                         | Fonctionnel            | Haute          | /                         |
|                           | Bouton Retour à l'accueil                    | Fonctionnel            | Haute          | /                         |
|                           | Mise à jour des pièces                       | Erreur                 | Haute          | Pièces encore présentes   |
|                           | Selection de pièces réservées (Autre User)   | Erreur                 | Haute          | Possible d'acheter encore |
| **F. PROFIL**                                                                                                                                  |
|                           | Visuel                                       | Erreur                 | Basse          |                           |
| 1) Informations personnel                                                                                                                      |
|                           | Modifier le Prénom                           | A tester               |                |                           |
|                           | Modifier le Nom                              | A tester               |                |                           |
|                           | Menu déroulant de la Nationalité             | A tester               |                |                           |
|                           | Bouton à cocher sur les données              | A tester               |                |                           |
|                           | Bouton à cocher sur les offres               | A tester               |                |                           |
|                           | Modifier l'Email                             | A tester               |                |                           |
|                           | Bouton Modifier les données                  | A tester               |                |                           |
| 2) Commandes et Devis                                                                                                                          |
|                           | Tableau des Commandes                        | A tester               |                |                           |
|                           | Bouton (Loupe) consulation Commandes         | A tester               |                |                           |
|                           | Bouton Imprimer Commandes                    | A tester               |                |                           |
|                           | Tableau des Devis                            | A tester               |                |                           |
|                           | Bouton (Loupe) consulation Devis             | A tester               |                |                           |
|                           | Bouton Imprimer Devis                        | A tester               |                |                           |
|                           | Bouton (Carte) Devis                         | A tester               |                |                           |
| 3) Demandes et Réclamations                                                                                                                    |
|                           | Visuel                                       | A tester               |                |                           |
|                           | Bouton Demander une pièce                    | A tester               |                |                           |
|                           | Bouton Reprise de votre véhicule             | A tester               |                |                           |
|                           | Liste de mes demandes et réclamations        | A tester               |                |                           |
| 4) Adresses de Livraison et Facturation                                                                                                        |
|                           | Visuel                                       | A tester               |                |                           |
|                           | Bouton Ajouter une nouvelle adresse          | A tester               |                |                           |
|                           | Liste des adresses enregistrées              | A tester               |                |                           |
|                           | Bouton Reprise de votre véhicule             | A tester               |                |                           |
| **F. QUOTAS**                                                                                                                                  |
|                           | Visuel                                       | Erreur                 | Basse          |                           |
|                           | A Définir                                    | A tester               |                |                           |
| **G. ADMINISTRATEUR**                                                                                                                          |
|                           | Visuel                                       | Erreur                 | Basse          |                           |
|                           | A Définir                                    | A tester               |                |                           |


(1) Veuillez remplir ce champ