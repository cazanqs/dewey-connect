<?php

namespace App\Repository;

use App\Entity\Reservation;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class ReservationRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Reservation::class);
    }

    public function findOneByUtilisateurEtTrajet($utilisateur, $trajet): ?Reservation
    {
        $qb = $this->createQueryBuilder('r')
            ->andWhere('r.utilisateur = :utilisateur')
            ->andWhere('r.trajet = :trajet')
            ->setParameter('utilisateur', $utilisateur)
            ->setParameter('trajet', $trajet)
            ->setMaxResults(1);

        return $qb->getQuery()->getOneOrNullResult();
    }

    public function findByUtilisateurTriesParDate($utilisateur, \DateTimeInterface $maintenant): array
    {
        return $this->createQueryBuilder('r')
            ->join('r.trajet', 't')
            ->where('r.utilisateur = :utilisateur')
            ->setParameter('utilisateur', $utilisateur)
            ->addOrderBy('CASE WHEN t.date_et_heure >= :maintenant THEN 0 ELSE 1 END', 'ASC')
            ->addOrderBy('t.date_et_heure', 'ASC')
            ->setParameter('maintenant', $maintenant)
            ->getQuery()
            ->getResult();
    }
}
