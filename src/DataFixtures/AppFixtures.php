<?php

namespace App\DataFixtures;

use App\Entity\User;
use App\Entity\BillingAdress;
use App\Entity\DeliveryAdress;
// use App\Entity\Part;
// use App\Entity\MangoPay;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;

class AppFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $faker = Factory::create();

        // Ajouter des utilisateurs
        for ($i = 1; $i <= 10; $i++) {
              $user = new User();
            $user->setName($faker->firstName);
            $user->setSurname($faker->lastName);
            $user->setEmail($faker->unique()->email);
            $user->setPassword(password_hash('password', PASSWORD_BCRYPT)); // Utilisez un encodeur en production
            $user->setRoles(['ROLE_USER']); // JSON
            $user->setAccountType('basic'); // Type de compte obligatoire
            $user->setCompanyName($faker->company);
            $user->setSiretNumber($faker->numerify('##############')); // 14 chiffres
            $user->setVatNumber($faker->numerify('FR###########')); // Numéro TVA fictif
            $manager->persist($user);
        }

        // Ajouter des adresses de facturation
        for ($i = 1; $i <= 10; $i++) {
            $billingAddress = new BillingAdress();
            $billingAddress->setFirstname($faker->firstName);
            $billingAddress->setLastname($faker->lastName);
            $billingAddress->setPhone($faker->numerify('06########')); // Numéro de téléphone français
            $billingAddress->setStreet($faker->streetAddress);
            $billingAddress->setStreetAdditionnal($faker->secondaryAddress); // Optionnel
            $billingAddress->setPostCode($faker->postcode);
            $billingAddress->setCity($faker->city);
            $billingAddress->setCountryId($faker->numberBetween(1, 250)); // Simuler un ID de pays
            $billingAddress->setEmail($faker->email);
            $manager->persist($billingAddress);
         }

        // Ajouter des adresses de livraison
        for ($i = 1; $i <= 10; $i++) {
            $deliveryAddress = new DeliveryAdress();
            $deliveryAddress->setFirstname($faker->firstName);
            $deliveryAddress->setLastname($faker->lastName);
            $deliveryAddress->setPhone($faker->numerify('06########')); // Numéro de téléphone simulé
            $deliveryAddress->setStreet($faker->streetAddress);
            $deliveryAddress->setStreetAdditionnal($faker->secondaryAddress); // Optionnel
            $deliveryAddress->setPostCode($faker->postcode);
            $deliveryAddress->setCity($faker->city);
            $deliveryAddress->setCountryId($faker->numberBetween(1, 250)); // Simuler un ID de pays
            $deliveryAddress->setEmail($faker->email);
            $manager->persist($deliveryAddress);
        }

        //  // Ajouter des transactions MangoPay
        //  for ($i = 1; $i <= 10; $i++) {
        //      $mangoPay = new MangoPay();
        //      $mangoPay->setTransactionId($faker->uuid);
        //      $mangoPay->setAmount($faker->randomFloat(2, 10, 100));
        //      $manager->persist($mangoPay);
        //  }

        // Sauvegarder en base de données
        $manager->flush();
    }
} 
