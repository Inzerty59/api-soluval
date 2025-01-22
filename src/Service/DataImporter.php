<?php
namespace App\Service;
 
class DataImporter
{
    public function fetchData(): array
    {
        return [
            'products' => [
                [
                    'part' => [
                        'name' => 'Filtre à huile',
                        'modelName' => 'PEUGEOT 206 PHASE 2',
                        'ManufacturerReference' => '6242C3', //OK1 part manufacturer reference
                        'Id' => '1',
                        'Name' => 'Com (Bloc Contacteur Tournant+Commodo Essuie Glace+Commodo Phare) PEUGEOT 206 PHASE 2 Diesel', //OK3 catégorie du produit + marque du véhicule + modèle du véhicule
                        'Category' => 'Com (Bloc Contacteur Tournant+Commodo Essuie Glace+Commodo Phare)', //OK4 Category Name
                        'Description' => 'Description non renseignée.', //OK5
                        'Price' => '90,00', //OK6 Price origine price + VATRate = TTC
                        'PartType' => 'Occasion - Bon état - En stock', // Ok7   XXXXXX
                        'Warranty' => '6 mois', //OK9
                        'AdaptableReference' => '97803040', //OK2 part adaptable reference
                        //Quantité : 1 OK8 Price quantity
                    ],
                    'vehicleBrand' => [
                        'Name' => 'PEUGEOT', //OK10  part vehicle identification brand standard name
                    ],
                    'vehicleRange' => [
                        'Range' => '206', //OK11 part véhicle identification range standard name
                    ],
                    'vehicleVersion' => [
                        'Finish' => '206 PHASE 2 1.4 HDI - 8V TURBO', //OK13 part vehicle identification finish
                        'CommercialDesignation' => '206 PHASE 2 1.4 HDI - 8V TURBO', //OK14 part vehicle identification commercial designation
                        'Displacement' => '4',
                        'Power' => '68', //OK18 part vehicle identification power
                        'Energy' => 'Diesel', //OK19 energy name
                        'GearboxType' => 'Manuelle', //OK20 vehicle identification energy name
                        'EngineCode' => 'DV4TD', //OK21 vehicle identification engine code
                        'GearboxCode' => 'MA5 / O', //OK22 vehicle identification gearbox code
                        'DoorNumber' => '5', //OK23 vehicle identification door number
                        //OK16 couleur : part vehicle color name
                        //OK17 cylindrée : part vehicle identification displacement
                    ],
                    'vehicleModel' => [
                        'Model' => '206 PHASE 2', //OK12 part vehicle identification model standard name
                        'Begin' => '2006', //OK14 part vehicle year
                    ],
                    'vehicle' => [
                        'Mileage' => '265370', //OK15 part vehicle mileage
                        'Color' => 'Gris', //OK16 couleur : part vehicle color name
                        'Vignette' => 'https://opisto-prod-pic.opisto.s3.eu-west-1.bso.st/4672/vhu_photo/2024/10/Vehicule-PEUGEOT-206%20PHASE%202-2006-93a221912243fa960f52ec459daa6cbe2914fc0a51c9d58a4fb25a8966fd312a_o.jpg',
                        //OK24 part vignette
                        //OK25 PHOTO : part photo
                    ],
                ],
            ],
        ];
    }
}
 
 //0ok 00 part Id => external_id ok
//OK1 part manufacturer reference => manufacturer_reference ok
//OK2 part adaptable reference => opisto_reference
//OK3 catégorie du produit + marque du véhicule + modèle du véhicule + carburant =>
//OK4 Category Name
//OK5 part description
//OK6 Price origine price + VATRate = TTC (ENTITY PRICE)
//OK7 part condition (code selon l'état)
//OK8 Price quantity (ENTITY PRICE)
//OK9 part warranty
//OK10 part vehicle identification brand standard name
//OK11 part véhicle identification range standard name
//OK12 part vehicle identification model standard name
//OK13 part vehicle identification finish
//OK14 part vehicle identification commercial designation
//OK14 part vehicle year
//OK15 part vehicle mileage
//OK16 couleur : part vehicle color name
//OK17 cylindrée : part vehicle identification displacement
//OK18 part vehicle identification power
//OK19 energy name
//OK20 vehicle identification energy name
//OK21 vehicle identification engine code
//OK22 vehicle identification gearbox code
//OK23 vehicle identification door number
//OK24 part vignette
//OK25 PHOTO : part photo