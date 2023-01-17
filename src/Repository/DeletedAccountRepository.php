<?php

namespace App\Repository;

use App\Entity\DeletedAccount;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method DeletedAccount|null find($id, $lockMode = null, $lockVersion = null)
 * @method DeletedAccount|null findOneBy(array $criteria, array $orderBy = null)
 * @method DeletedAccount[]    findAll()
 * @method DeletedAccount[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class DeletedAccountRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, DeletedAccount::class);
    }
}
