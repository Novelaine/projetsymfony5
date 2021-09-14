<?php

namespace App\Repository;

use App\Entity\Emprunt;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Emprunt|null find($id, $lockMode = null, $lockVersion = null)
 * @method Emprunt|null findOneBy(array $criteria, array $orderBy = null)
 * @method Emprunt[]    findAll()
 * @method Emprunt[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class EmpruntRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Emprunt::class);
    }

    /*
    Exemple en sql pour recuperer le livre donc l'id et 1 et qui a une date retour null
    SELECT * 
    FROM emprunt e JOIN livre l ON e.livre_id = l.id 
    WHERE date_retour IS NULL

    Exo : écrivez la fonction empruntsNonRendus qui retourne les emprunts qui ont une date_retour nulle
    */

    /**
      * @return Emprunt[] Returns an array of Livre objects
      */
    public function empruntsNonRendus()
    {
        return $this->createQueryBuilder('e') // SELECT * FROM emprunt e
        ->where('e.date_retour IS NULL') // WHERE date_retour IS NULL
        ->orderBy('e.date_emprunt', 'DESC') // ordre du plus recent au plus ancien (facultatif)
        ->getQuery() 
        ->getResult()
        ;
    }
    
    // /**
    //  * @return Emprunt[] Returns an array of Emprunt objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('e')
            ->andWhere('e.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('e.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?Emprunt
    {
        return $this->createQueryBuilder('e')
            ->andWhere('e.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
