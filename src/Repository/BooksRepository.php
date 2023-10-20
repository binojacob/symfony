<?php

namespace App\Repository;

use App\Entity\Books;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Security\Http\Attribute\IsGranted;

/**
 * @extends ServiceEntityRepository<Books>
 *
 * @method Books|null find($id, $lockMode = null, $lockVersion = null)
 * @method Books|null findOneBy(array $criteria, array $orderBy = null)
 * @method Books[]    findAll()
 * @method Books[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class BooksRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Books::class);
    }

    public function save(Books $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Books $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function getBookName(int $authorId,int $bookId): array
    {
        return $this->createQueryBuilder('b')
            ->where('b.id = :bookId')
            ->setParameter('bookId', $bookId)
            ->andWhere('b.auther_id = :authId')
            ->setParameter('authId', $authorId)
            ->getQuery()
            ->getResult()
        ;
    }

    public function getBookData(): array
    {

        $query = "SELECT b.id,b.name,b.auther_id,a.full_name FROM books b 
        LEFT JOIN user a ON a.id=b.auther_id GROUP BY b.id";


        $conn = $this->getEntityManager()->getConnection();     
        $stmt = $conn->prepare($query);
    
        return $stmt->executeQuery()->fetchAllAssociative();    
    }


    public function getBookDetails($bookId): ?Books
    {
        return $this->createQueryBuilder('b')
            ->andWhere('b.id = :val')
            ->setParameter('val', $bookId)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }

    public function listBooksDetails(): array
    {
        $entityManager = $this->getEntityManager();

        $query = $entityManager->createQuery(
            'SELECT * FROM  books b  left join user a on a.id=b.auther_id'
        );
        return $query->getResult();
    }


//    /**
//     * @return Books[] Returns an array of Books objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('b')
//            ->andWhere('b.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('b.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?Books
//    {
//        return $this->createQueryBuilder('b')
//            ->andWhere('b.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
