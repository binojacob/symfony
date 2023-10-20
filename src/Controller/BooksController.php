<?php

namespace App\Controller;

use App\Entity\Books;
use App\Entity\User;
use App\Form\BooksType;
use App\Repository\BooksRepository;
use App\Repository\CommentsRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Security\Http\Attribute\IsGranted as AttributeIsGranted;

;

class BooksController extends AbstractController
{
    #[Route('/books', name: 'app_add_books')]
    public function index(EntityManagerInterface $em,Request $request,UserRepository $userRepo): Response
    {
       
        if(!$this->isGranted('ROLE_AUTHOR') and !$this->isGranted('ROLE_ADMIN')){
            print "You not have permission to access this page";exit;
        }

        $books = new Books();
  
        $user = $this->getUser();
        
        $userId = $user->getId();

        $autherList = $userRepo->findAll();
        
        $UserList = [];
        foreach($autherList as $auther){
            $UserList[$auther->getId()] = $auther->getFullName();
        }

        if($this->isGranted('ROLE_ADMIN') ) {
            
            $form = $this->createFormBuilder($books)
            ->add("name",TextType::class)
            ->add("auther_id",ChoiceType::class,
                [
                    'choices' => array_flip($UserList)
                ]
            )
            ->add('Submit',SubmitType::class)
            ->getForm();
        }
        else {
            $form = $this->createForm(BooksType::class,$books);
        }
        
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) { 
            $data = $form->getData();
            if($this->isGranted('ROLE_ADMIN') ) {
                $books->setAutherId($form->get('auther_id')->getData());
            }
            else {
                $books->setAutherId($userId);
            }
            
            $em->persist($data);
            $em->flush();
            return $this->redirectToRoute("app_list_books");
        }
        return $this->render('books/index.html.twig', [
            'form' => $form,
        ]);
    }

    #[Route('/user/showbooks', name: 'app_showbooks', methods: ['GET'])]
    public function showBooks():Response
    {
        if($this->isGranted('ROLE_ADMIN')){
          return $this->redirectToRoute("app_admin");
        }
        else {
            return $this->redirectToRoute("app_list_books");
        }

    }

    
    #[Route('/listbooks', name: 'app_list_books')]
    public function listBooks(EntityManagerInterface $em):Response
    {
        $books = $em->getRepository(Books::class);
        $user = $this->getUser();
        

        $bookslist = $books->getBookData();

        return $this->render('books/listbooks.html.twig', [
            'listbooks' => $bookslist 
        ]);
    }


    

    #[Route('/delbooks/{id}', name: 'app_del_books')]
    public function deleteBooks(Books $book,Request $request,
    CommentsRepository $commentRepo,EntityManagerInterface $em):Response
    {
        // $authorId = $request->query->get("auther_id");
        // $bookId =  $request->query->get("id");
        //$book =  $bookRepo->find(["id"=>$bookId]);

        $comments = $commentRepo->findBy(['booksid'=>$book->getId()]);

        foreach($comments as $key=>$comment){
            $em->remove($comment);
            $em->flush();
        }

        // Remove it and flush
        $em->remove($book);
        $em->flush();
        return $this->redirectToRoute("app_list_books");
    }

    #[Route('/editbooks/{id}', name: 'app_edit_books')]
    public function editBooks(
        Books $bookdata,
        EntityManagerInterface $em,
        Request $request,
        BooksRepository $BookRepo,
        UserRepository $userRepo):Response
    {

        // $authorId = $request->query->get("auther_id");
        // $bookId =  $request->query->get("id");
        //$bookdata = $BookRepo->findOneBy(['auther_id'=>$authorId,'id'=>$bookId]);

        $books = new Books();
  
        $user = $this->getUser();
        
        $userId = $user->getId();

        $autherList = $userRepo->findAll();
        
        $UserList = [];
        foreach($autherList as $auther){
            $UserList[$auther->getId()] = $auther->getFullName();
        }

        if($this->isGranted('ROLE_ADMIN') ) {
            
            $form = $this->createFormBuilder($bookdata)
            ->add("name",TextType::class)
            ->add("auther_id",ChoiceType::class,
                [
                    'choices' => array_flip($UserList)
                ]
            )
            ->add('Submit',SubmitType::class)
            ->getForm();
        }
        else {
            $form = $this->createForm(BooksType::class,$bookdata);
        }
        
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) { 
            $data = $form->getData();
            if($this->isGranted('ROLE_ADMIN') ) {
                $books->setAutherId($form->get('auther_id')->getData());
            }
            else {
                $books->setAutherId($userId);
            }
            
            $em->persist($data);
            $em->flush();
            return $this->redirectToRoute("app_list_books");
        }
        return $this->render('books/index.html.twig', [
            'form' => $form,
        ]);
    }



}
