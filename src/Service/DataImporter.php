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
                        'ManufacturerReference' => '6242C3',
                        'Id' => '1',
                        'Name' => 'Com (Bloc Contacteur Tournant+Commodo Essuie Glace+Commodo Phare) PEUGEOT 206 PHASE 2 Diesel',
                        'Category' => 'Com (Bloc Contacteur Tournant+Commodo Essuie Glace+Commodo Phare)',
                        'Description' => 'Description non renseignée.',
                        'Price' => '90,00',
                        'PartType' => 'Occasion - Bon état - En stock',
                        'Warranty' => '6 mois',
                        'AdaptableReference' => '97803040',
                    ],
                    'vehicleBrand' => [
                        'Name' => 'PEUGEOT',
                    ],
                    'vehicleRange' => [
                        'Range' => '206',
                    ],
                    'vehicleVersion' => [
                        'Finish' => '206 PHASE 2 1.4 HDI - 8V TURBO',
                        'CommercialDesignation' => '206 PHASE 2 1.4 HDI - 8V TURBO',
                        'Displacement' => '4',
                        'Power' => '68',
                        'Energy' => 'Diesel',
                        'GearboxType' => 'Manuelle',
                        'EngineCode' => 'DV4TD',
                        'GearboxCode' => 'MA5 / O',
                        'DoorNumber' => '5',
                    ],
                    'vehicleModel' => [
                        'Model' => '206 PHASE 2',
                        'Begin' => '2006',
                    ],
                    'vehicle' => [
                        'Mileage' => '265370',
                        'Vignette' => 'https://opisto-prod-pic.opisto.s3.eu-west-1.bso.st/4672/vhu_photo/2024/10/Vehicule-PEUGEOT-206%20PHASE%202-2006-93a221912243fa960f52ec459daa6cbe2914fc0a51c9d58a4fb25a8966fd312a_o.jpg',
                    ],
                ],
                [
                    'part' => [
                        'name' => 'Plaquettes de frein',
                        'modelName' => 'PEUGEOT 208',
                        'ManufacturerReference' => '6242D7',
                        'Id' => '2',
                        'Name' => 'Plaquettes de frein avant pour PEUGEOT 208 Diesel',
                        'Category' => 'Freinage',
                        'Description' => 'Plaquettes de frein compatibles avec PEUGEOT 208.',
                        'Price' => '50,00',
                        'PartType' => 'Neuf',
                        'Warranty' => '12 mois',
                        'AdaptableReference' => '97804120',
                    ],
                    'vehicleBrand' => [
                        'Name' => 'PEUGEOT',
                    ],
                    'vehicleRange' => [
                        'Range' => '208',
                    ],
                    'vehicleVersion' => [
                        'Finish' => '208 1.6 HDI',
                        'CommercialDesignation' => '208 1.6 HDI Diesel',
                        'Displacement' => '4',
                        'Power' => '92',
                        'Energy' => 'Diesel',
                        'GearboxType' => 'Manuelle',
                        'EngineCode' => 'DV6ETED',
                        'GearboxCode' => 'BE4 / 5',
                        'DoorNumber' => '5',
                    ],
                    'vehicleModel' => [
                        'Model' => '208',
                        'Begin' => '2015',
                    ],
                    'vehicle' => [
                        'Mileage' => '125000',
                        'Vignette' => 'https://opisto-prod-pic.opisto.s3.eu-west-1.bso.st/4672/vhu_photo/2024/10/Vehicule-PEUGEOT-206%20PHASE%202-2006-93a221912243fa960f52ec459daa6cbe2914fc0a51c9d58a4fb25a8966fd312a_o.jpg',
                    ],
                ],
            ],
        ];
    }
}
