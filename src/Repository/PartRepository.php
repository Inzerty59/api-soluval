<?php

namespace App\Repository;

use App\Entity\Part;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Part>
 */
class PartRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Part::class);
    }

    public function searchParts(string $query, ?string $brand = null, ?string $model = null): array
    {
        $qb = $this->createQueryBuilder('p')
            ->where('p.category_name LIKE :query')
            ->orWhere('p.manufacturer_reference LIKE :query')
            ->orWhere('p.adaptable_reference LIKE :query')
            ->orWhere('p.description LIKE :query')
            ->orWhere('p.brand_name LIKE :query')
            ->orWhere('p.model_name LIKE :query')
            ->orWhere('p.finish_name LIKE :query')
            ->orWhere('p.commercial_designation LIKE :query')
            ->orWhere('p.energy_name LIKE :query')
            ->setParameter('query', '%' . $query . '%');

        if ($brand) {
            $qb->andWhere('p.brand_name = :brand')
                ->setParameter('brand', $brand);
        }

        if ($model) {
            $qb->andWhere('p.model_name = :model')
                ->setParameter('model', $model);
        }

        return $qb->getQuery()->getResult();
    }

    public function findDistinctBrands(): array
    {
        $qb = $this->createQueryBuilder('p')
            ->select('DISTINCT p.brand_name')
            ->orderBy('p.brand_name', 'ASC');

        $result = $qb->getQuery()->getResult();
        return array_map(fn($brand) => $brand['brand_name'], $result);
    }

    public function findDistinctModels(): array
    {
        $qb = $this->createQueryBuilder('p')
            ->select('DISTINCT p.model_name')
            ->orderBy('p.model_name', 'ASC');

        $result = $qb->getQuery()->getResult();
        return array_map(fn($model) => $model['model_name'], $result);
    }

    //    /**
    //     * @return Part[] Returns an array of Part objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('p')
    //            ->andWhere('p.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('p.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?Part
    //    {
    //        return $this->createQueryBuilder('p')
    //            ->andWhere('p.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
