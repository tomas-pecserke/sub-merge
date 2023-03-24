<?php

namespace App\Repository;

use App\Entity\VideoFile;
use App\Entity\VideoFileStatus;
use DateTimeImmutable;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Query;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<VideoFile>
 *
 * @method VideoFile|null find($id, $lockMode = null, $lockVersion = null)
 * @method VideoFile|null findOneBy(array $criteria, array $orderBy = null)
 * @method VideoFile[]    findAll()
 * @method VideoFile[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class VideoFileRepository extends ServiceEntityRepository {
    public function __construct(ManagerRegistry $registry) {
        parent::__construct($registry, VideoFile::class);
    }

    public function save(VideoFile $entity, bool $flush = false): void {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(VideoFile $entity, bool $flush = false): void {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    /**
     * @param DateTimeImmutable $dateTime
     * @return VideoFile[]
     */
    public function findByLastSyncedBefore(DateTimeImmutable $dateTime): array {
        return $this->createQueryBuilder('v')
            ->where('v.status <> :status')
            ->andWhere('v.lastSyncedAt < :dateTime')
            ->setParameter('status', VideoFileStatus::Deleted)
            ->setParameter('dateTime', $dateTime)
            ->getQuery()
            ->getArrayResult();
    }

    public function findAllQuery(string $sort, string $direction = null): Query {
        return $this->createQueryBuilder('v')
            ->orderBy($sort, $direction)
            ->getQuery();
    }
}
