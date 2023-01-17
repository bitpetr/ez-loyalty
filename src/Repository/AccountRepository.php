<?php

namespace App\Repository;

use App\Entity\Account;
use App\Entity\DeletedAccount;
use DateTime;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;
use Exception;

/**
 * @method Account|null find($id, $lockMode = null, $lockVersion = null)
 * @method Account|null findOneBy(array $criteria, array $orderBy = null)
 * @method Account[]    findAll()
 * @method Account[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class AccountRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Account::class);
    }

    public function countExpired(): int
    {
        return $this->getExpiredQueryBuilder()->select('COUNT(a.id)')->getQuery()->getSingleScalarResult();
    }

    public function deleteExpired(): int
    {
        $accounts = $this->getExpiredQueryBuilder()->select()->getQuery()->getResult();
        /** @var Account[] $accounts */
        foreach ($accounts as $account) {
            $this->getEntityManager()->persist((new DeletedAccount(
                $account->getId(),
                $account->getEmail(),
                $account->getCoins(),
                $account->getCreationDate(),
            )));
            $this->getEntityManager()->remove($account);
        }

        $this->getEntityManager()->flush();
        return count($accounts);
    }

    private function getExpiredQueryBuilder(): QueryBuilder
    {
        return $this->createQueryBuilder('a')
            ->andWhere('a.expiryDate < :now')
            ->setParameters(['now' => new DateTime()])
            ->setMaxResults(50);
    }

    /**
     * @return Account[]
     * @throws Exception
     */
    public function getExpiringForNotice(?string $interval = '1 week', ?int $level = 0): array
    {
        $accounts = $this->createQueryBuilder('a')
            ->andWhere('a.expiryNoticeLevel = :level')
            ->andWhere('a.expiryDate < :date')
            ->setParameters(['date' => new DateTime($interval), 'level' => $level])
            ->setMaxResults(50)
            ->getQuery()
            ->getResult();

        return array_filter($accounts, fn(Account $a) => $a->getCoins());
    }

    public function getEntityManager(): EntityManagerInterface
    {
        return parent::getEntityManager();
    }
}
