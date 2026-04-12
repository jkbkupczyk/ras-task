<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Photo;
use App\Service\PhotosListFilter;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;

class PhotoRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Photo::class);
    }

    public function findAllWithUsers(?PhotosListFilter $filter = null): array
    {
        $queryBuilder = $this->createQueryBuilder('p')
            ->leftJoin('p.user', 'u')
            ->addSelect('u')
            ->orderBy('p.id', 'ASC');

        return $this->getQueryBuilderForFilters($queryBuilder, $filter)
            ->getQuery()
            ->getResult();
    }

    private function getQueryBuilderForFilters(QueryBuilder      $queryBuilder,
                                               ?PhotosListFilter $filter): QueryBuilder
    {
        if (!$filter) {
            return $queryBuilder;
        }

        if ($filter->location !== null) {
            $queryBuilder
                ->andWhere('lower(p.location) LIKE lower(:location)')
                ->setParameter('location', '%' . $filter->location . '%');
        }

        if ($filter->camera !== null) {
            $queryBuilder
                ->andWhere('lower(p.camera) LIKE lower(:camera)')
                ->setParameter('camera', '%' . $filter->camera . '%');
        }

        if ($filter->description !== null) {
            $queryBuilder
                ->andWhere('lower(p.description) LIKE lower(:description)')
                ->setParameter('description', '%' . $filter->description . '%');
        }

        if ($filter->username !== null) {
            $queryBuilder
                ->andWhere('lower(u.username) LIKE lower(:username)')
                ->setParameter('username', '%' . $filter->username . '%');
        }

        if ($filter->takenAt !== null) {
            $takenAtFrom = $filter->takenAt->setTime(0, 0, 0);
            $takenAtTo = $takenAtFrom->modify('+1 day');

            $queryBuilder
                ->andWhere('p.takenAt >= :takenAtStart')
                ->andWhere('p.takenAt < :takenAtEnd')
                ->setParameter('takenAtStart', $takenAtFrom)
                ->setParameter('takenAtEnd', $takenAtTo);
        }

        return $queryBuilder;
    }

    // TODO: refactor to bulk op
    public function saveAll(array $values): void
    {
        foreach ($values as $value) {
            $this->_em->persist($value);
        }
        $this->_em->flush();
    }

}
