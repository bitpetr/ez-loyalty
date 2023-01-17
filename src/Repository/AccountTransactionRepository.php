<?php

namespace App\Repository;

use App\Entity\AccountTransaction;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method AccountTransaction|null find($id, $lockMode = null, $lockVersion = null)
 * @method AccountTransaction|null findOneBy(array $criteria, array $orderBy = null)
 * @method AccountTransaction[]    findAll()
 * @method AccountTransaction[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class AccountTransactionRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, AccountTransaction::class);
    }

    public function releaseFrozen(?string $interval = '1 hour'): ?int
    {
       return $this->getFrozenQueryBuilder($interval)
            ->update()
            ->set('at.operation', ':new_operation')
            ->set('at.time', ':new_time')
            ->setParameter('new_operation', AccountTransaction::OP_RELEASE)
            ->setParameter('new_time', new \DateTime())
            ->getQuery()
            ->execute();
    }

    public function countFrozen(?string $interval = '1 hour'): int
    {
        return $this->getFrozenQueryBuilder($interval)
            ->select('COUNT(at.id)')
            ->getQuery()
            ->getSingleScalarResult();
    }

    protected function getFrozenQueryBuilder(?string $interval = '1 hour'): QueryBuilder
    {
        return $this->createQueryBuilder('at')
            ->andWhere('at.operation = :operation')
            ->andWhere('at.time < :time')
            ->setParameters(['time' => new \DateTime('-'.$interval), 'operation' => AccountTransaction::OP_FREEZE]);
    }
}
