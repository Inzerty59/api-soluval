<?php

namespace App\Controller\Shop;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ShopController extends AbstractController
{
    #[Route('/', name: 'shop_index')]
    public function index(): Response
    {

        //Il faut comprendre la hiérarchie de notre nomenclature de la façon suivante : 
        //Marque > Gamme > Modèle > Version (VehicleBrand > VehicleRange > VehicleModel > VehicleVersion).
 
        $parts = [
            [
                'name' => 'Filtre à huile', #A chercher bonne dénomination
                'modelName' => 'PEUGEOT 206 PHASE 2',
                'ManufacturerReference' => '6242C3',
                'Id' => '98071900',
                'Name' => 'Com (Bloc Contacteur Tournant+Commodo Essuie Glace+Commodo Phare) PEUGEOT 206 PHASE 2 Diesel',
                'Category' => 'Com (Bloc Contacteur Tournant+Commodo Essuie Glace+Commodo Phare)',
                'Description' => 'Description non renseignée.',
                'Price' => '90,00',
                'PartType' => 'Occasion - Bon état - En stock',
                'Warranty' => '6 mois',
                'AdaptableReference' => '97803040'
            ],

        $vehicleBrand = 
            [
                'Name' => 'PEUGEOT',
            ],

        $vehicleRange = 
            [
                'Range' => '206'
            ],

        $vehicleVersion = 
            [
                'Finish' => '206 PHASE 2 1.4 HDI - 8V TURBO',
                'CommercialDesignation' => '206 PHASE 2 1.4 HDI - 8V TURBO',
                'Displacement' => '4',
                'Power' => '68',
                'Energy' => 'Diesel',  
                'GearboxType' => 'Manuelle',
                'EngineCode' => 'DV4TD',
                'GearboxCode' => 'MA5 / O',
                'DoorNumber' => '5'
            ],

        $vehicleModel = 
            [
                'Model' => '206 PHASE 2',
                'Begin' => '2006'
            ],    

        $vehicle = 
            [
                'Mileage' => '265370',
                'Vignette' => 'https://opisto-prod-pic.opisto.s3.eu-west-1.bso.st/4672/vhu_photo/2024/10/Vehicule-PEUGEOT-206%20PHASE%202-2006-93a221912243fa960f52ec459daa6cbe2914fc0a51c9d58a4fb25a8966fd312a_o.jpg',
                'ScaledPhotos' => 
                    [   
                        'https://opisto-prod-pic.opisto.s3.eu-west-1.bso.st/4672/vhu_photo/2024/10/Vehicule-PEUGEOT-206%20PHASE%202-2006-78c8663be43a095a2e533dd400e96eefffad89a709f8e005076c1fd948d15c8f_s.jpg',
                        'https://opisto-prod-pic.opisto.s3.eu-west-1.bso.st/4672/vhu_photo/2024/10/Vehicule-PEUGEOT-206%20PHASE%202-2006-d14af51e98f82fa65efed7e7f4eeb0da69eb55d3500d6838d1f57bba520ab3d3_s.jpg',
                        'https://opisto-prod-pic.opisto.s3.eu-west-1.bso.st/4672/vhu_photo/2024/10/Vehicule-PEUGEOT-206%20PHASE%202-2006-e0bc877b88f51a810cbb92fd36694ddd69f7aa19f4711286b7c98b955af89929_l.jpg'
                    ]
            ],
        $color = 
            [
                'Name' => 'Gris clair',
            ],

        $partPrice = 
            [
                'OriginPrice' => '90',
                'Quantity' => '2',
                'VATRate' => '5.5'
            ],
        $partLight = 
            [
                'CasseId' => '4672',
                'Price' => '90',
                'Shipping' => '1'
            ]
    ];
        return $this->render('shop/shop.html.twig', ['parts' => $parts]);
    }
}
