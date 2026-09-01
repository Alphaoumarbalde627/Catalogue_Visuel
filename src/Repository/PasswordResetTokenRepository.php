<?php

namespace App\Repository;

use App\Entity\PasswordResetToken;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<PasswordResetToken>
 */
class PasswordResetTokenRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, PasswordResetToken::class);
    }

    /**
     * @return PasswordResetToken[]
     */
    public function findActiveByUser(
        User $user,
        ?\DateTimeImmutable $now = null
    ): array {
        $now ??= new \DateTimeImmutable();

        return $this->createQueryBuilder('p')
            ->andWhere('p.user = :user')
            ->andWhere('p.expiresAt > :now')
            ->setParameter('user', $user)
            ->setParameter('now', $now)
            ->orderBy('p.expiresAt', 'DESC')
            ->getQuery()
            ->getResult();
    }
}
