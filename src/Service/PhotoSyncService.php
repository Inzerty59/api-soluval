<?php

namespace App\Service;

use App\Repository\PartRepository;
use App\Entity\Part;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;

class PhotoSyncService
{
    private PartRepository $partRepository;
    private EntityManagerInterface $entityManager;
    private LoggerInterface $logger;

    public function __construct(
        PartRepository $partRepository,
        EntityManagerInterface $entityManager,
        LoggerInterface $logger
    ) {
        $this->partRepository = $partRepository;
        $this->entityManager = $entityManager;
        $this->logger = $logger;
    }

    /**
     * Synchronise les photos pour une pièce modifiée
     * Ajoute les nouvelles photos sans supprimer les anciennes
     * @return bool true si la pièce a été synchronisée, false sinon
     */
    public function syncPhotosForPart(array $opisoPart): bool
    {
        $externalId = $opisoPart['Id'] ?? null;
        if (!$externalId) {
            return false;
        }

        $localPart = $this->partRepository->findOneBy(['external_id' => $externalId]);
        if (!$localPart) {
            // Pièce pas en base, on ignore
            return false;
        }

        // Vérifier si la pièce est disponible
        if (!$localPart->isAvailable()) {
            // Pièce non disponible, pas besoin de mettre à jour les photos
            return false;
        }

        // Extraire les URLs des photos depuis OPISTO
        $newPhotoUrls = $this->extractPhotoUrls($opisoPart);
        $existingPhotos = $localPart->getPhotos() ?? [];

        // Comparer et fusionner
        if ($this->hasPhotosDifferences($newPhotoUrls, $existingPhotos)) {
            // Fusionner les photos (nouvelles + anciennes, sans doublons)
            $mergedPhotos = $this->mergePhotos($newPhotoUrls, $existingPhotos);
            
            // Mettre à jour
            $localPart->setPhotos($mergedPhotos);
            
            // Mettre à jour la vignette si présente
            if (isset($opisoPart['Vignette'])) {
                $localPart->setVignette($opisoPart['Vignette']);
            }

            $this->entityManager->persist($localPart);
            $this->entityManager->flush();

            $this->logger->info("Photos synchronisées pour pièce $externalId", [
                'new_photos_count' => count($newPhotoUrls),
                'total_photos_count' => count($mergedPhotos),
            ]);

            return true;
        }

        return false;
    }

    /**
     * Extrait les URLs des photos depuis la réponse OPISTO
     */
    private function extractPhotoUrls(array $opisoPart): array
    {
        $photoUrls = [];

        // Format principal : Photos (array d'URLs directes)
        if (!empty($opisoPart['Photos']) && is_array($opisoPart['Photos'])) {
            $photoUrls = $opisoPart['Photos'];
        }

        // Format alternatif : ScaledPhotos (objets avec Url)
        if (empty($photoUrls) && !empty($opisoPart['ScaledPhotos']) && is_array($opisoPart['ScaledPhotos'])) {
            foreach ($opisoPart['ScaledPhotos'] as $scaledPhoto) {
                if (!empty($scaledPhoto['Url'])) {
                    $photoUrls[] = $scaledPhoto['Url'];
                }
            }
        }

        return array_filter($photoUrls); // Enlever les valeurs vides
    }

    /**
     * Vérifie si les photos ont changé
     */
    private function hasPhotosDifferences(array $newPhotos, $existingPhotos): bool
    {
        $existing = is_array($existingPhotos) ? $existingPhotos : [];
        
        // Normaliser les deux listes
        $newNormalized = array_unique(array_values($newPhotos));
        $existingNormalized = array_unique(array_values($existing));

        // Comparer
        $newCount = count($newNormalized);
        $existingCount = count($existingNormalized);

        if ($newCount !== $existingCount) {
            return true;
        }

        // Comparer les contenu
        sort($newNormalized);
        sort($existingNormalized);

        return $newNormalized !== $existingNormalized;
    }

    /**
     * Fusionne les photos sans créer de doublons
     */
    private function mergePhotos(array $newPhotos, $existingPhotos): array
    {
        $existing = is_array($existingPhotos) ? $existingPhotos : [];

        // Fusionner et dédupliquer
        $merged = array_unique(array_merge($existing, $newPhotos));

        // Réindexer le tableau
        return array_values($merged);
    }
}
