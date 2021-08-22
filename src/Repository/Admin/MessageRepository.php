<?php

namespace App\Repository\Admin;

use App\Entity\Admin\Message;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Message|null find($id, $lockMode = null, $lockVersion = null)
 * @method Message|null findOneBy(array $criteria, array $orderBy = null)
 * @method Message[]    findAll()
 * @method Message[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class MessageRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Message::class);
    }

    public function MessagesByUser($user)
    {
        return $this->createQueryBuilder('m')
            ->leftJoin('m.recipient', 'u')
            ->andWhere('u.id = :user')
            ->setParameter('user', $user)
            ->orderBy('m.id', 'ASC')
            ->getQuery()
            ->getResult()
            ;
    }

    public function newMessage($user)
    {
        return $this->createQueryBuilder('m')
            ->andWhere('m.author = :author AND m.isRead = :isRead')
            ->setParameter('author', $user)
            ->setParameter('isRead', 0)
            ->orderBy('m.id', 'ASC')
            ->getQuery()
            ->getResult()
            ;
    }

    // /**
    //  * @return Message[] Returns an array of Message objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('m')
            ->andWhere('m.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('m.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?Message
    {
        return $this->createQueryBuilder('m')
            ->andWhere('m.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
