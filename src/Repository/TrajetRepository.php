<?php

namespace App\Repository;

use App\Entity\Trajet;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class TrajetRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Trajet::class);
    }

    public function findFutursTrajetsLibres(\DateTimeInterface $maintenant): array
    {
        return $this->createQueryBuilder('t')
            ->where('t.date_et_heure > :maintenant')
            ->andWhere('t.sieges_libres > 0')
            ->setParameter('maintenant', $maintenant)
            ->orderBy('t.date_et_heure', 'ASC')
            ->getQuery()
            ->getResult();
    }

    public function findByUtilisateurTriesParDate($utilisateur, \DateTimeInterface $maintenant): array
    {
        return $this->createQueryBuilder('t')
            ->where('t.utilisateur = :utilisateur')
            ->setParameter('utilisateur', $utilisateur)
            ->addOrderBy('CASE WHEN t.date_et_heure >= :maintenant THEN 0 ELSE 1 END', 'ASC')
            ->addOrderBy('t.date_et_heure', 'ASC')
            ->setParameter('maintenant', $maintenant)
            ->getQuery()
            ->getResult();
    }
}
