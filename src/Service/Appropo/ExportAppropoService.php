<?php

namespace App\Service\Appropo;

use Doctrine\DBAL\Connection;
use Psr\Log\LoggerInterface;

/**
 * Génère appropo-pieces.csv : 21 colonnes lues par position, séparateur ";", UTF-8,
 * photo(s) séparées par "|". Voir la notice Appropo (notice-appropo-pieces.xlsx).
 */
class ExportAppropoService
{
    private const HEADER = [
        'pre_id', 'type_id', 'type_name', 'quantity', 'price', 'delivery_price', 'manufacturer_ref',
        'immatriculation', 'warranty', 'brand', 'model', 'version', 'couleur', 'km', 'vin', 'cnit',
        'engine_code', 'gear_code', 'photos', 'opisto_id', 'description',
    ];

    public function __construct(
        private Connection $connection,
        private LoggerInterface $logger,
    ) {
    }

    /**
     * @return array{written:int, skipped:array<string,int>}
     * @throws \RuntimeException
     */
    public function export(string $filePath): array
    {
        $out = fopen($filePath, 'w');
        if ($out === false) {
            throw new \RuntimeException('Impossible de créer ' . $filePath);
        }

        fwrite($out, implode(';', self::HEADER) . "\n");

        $rows = $this->connection->executeQuery(
            'SELECT external_id, category_id, category_name, price, warranty, brand_name, model_name,
                    finish_name, commercial_designation, color_name, mileage, vin, type_mine,
                    engine_code, gearbox_code, photos, vignette, description, manufacturer_reference
             FROM part
             WHERE available = 1 AND category_id IS NOT NULL
             ORDER BY external_id'
        );

        $written = 0;
        $skipped = ['no_ref' => 0, 'no_price' => 0, 'no_photo' => 0, 'duplicate' => 0];
        $seen = [];

        while (($p = $rows->fetchAssociative()) !== false) {
            $id = (int) $p['external_id'];
            $ref = trim((string) $p['manufacturer_reference']);
            $price = $this->priceHt($p['price']);
            $photos = $this->photos($p['photos'], $p['vignette']);

            // Règles de mise en vente Appropo : sinon la ligne serait ignorée côté import.
            if (isset($seen[$id])) {
                $skipped['duplicate']++;
                continue;
            }
            if ($ref === '') {
                $skipped['no_ref']++;
                continue;
            }
            if ($price <= 0) {
                $skipped['no_price']++;
                continue;
            }
            if ($photos === '') {
                $skipped['no_photo']++;
                continue;
            }
            $seen[$id] = true;

            $color = @unserialize((string) $p['color_name'], ['allowed_classes' => false]);

            fwrite($out, implode(';', array_map([$this, 'clean'], [
                $id,
                $p['category_id'],
                $p['category_name'],
                1,
                number_format($price, 2, '.', ''),
                0,
                $ref,
                '', // immatriculation : non stockée
                $p['warranty'],
                $p['brand_name'],
                $p['model_name'],
                $p['finish_name'] ?: $p['commercial_designation'],
                is_array($color) ? ($color['Name'] ?? '') : '',
                $p['mileage'],
                $p['vin'],
                $p['type_mine'],
                $p['engine_code'],
                $p['gearbox_code'],
                $photos,
                $id,
                $p['description'],
            ])) . "\n");
            $written++;
        }

        fclose($out);
        $this->logger->info('[Appropo] Export catalogue généré', ['file' => $filePath, 'written' => $written, 'skipped' => $skipped]);

        return ['written' => $written, 'skipped' => $skipped];
    }

    /** Prix HT = OriginPrice du champ sérialisé "price". */
    private function priceHt(?string $serialized): float
    {
        $price = $serialized ? @unserialize($serialized, ['allowed_classes' => false]) : null;

        return is_array($price) ? round((float) ($price['OriginPrice'] ?? 0), 2) : 0.0;
    }

    private function photos(?string $serialized, ?string $vignette): string
    {
        $photos = $serialized ? @unserialize($serialized, ['allowed_classes' => false]) : null;
        $urls = array_filter(array_map('trim', is_array($photos) ? $photos : []));
        if (!$urls && $vignette) {
            $urls = [trim($vignette)];
        }

        // "|" sépare les photos : une URL ne doit pas en contenir.
        return implode('|', array_map(static fn ($u) => str_replace(['|', ';'], ['%7C', '%3B'], $u), $urls));
    }

    /** Ni ";" ni retour à la ligne ni guillemet dans un champ (non gérés par l'import Appropo). */
    private function clean(mixed $value): string
    {
        return trim(str_replace([';', "\r", "\n", "\t", '"'], [',', ' ', ' ', ' ', "'"], (string) $value));
    }
}
