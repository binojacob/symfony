<?php

namespace App\Controller;

use App\Entity\Books;
use App\Entity\Comment;
use App\Entity\Comments;
use App\Form\CommentType;
use App\Repository\BooksRepository;
use App\Repository\CommentsRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Routing\Annotation\Route;

class CommentsController extends AbstractController
{
    public function __construct(
        private CommentsRepository $comRepo,
        private EntityManagerInterface $em
    )
    {
        
    }

    #[Route('/comments', name: 'app_comments')]
    public function index(): Response
    {
        return $this->render('comments/index.html.twig', [
            'controller_name' => 'CommentsController',
        ]);
    }


    #[Route('/addcomments/{book}', name: 'app_add_comment')]
    public function addComments(
        Books $book,
        Request $request,
        BooksRepository $bookrepo,
        EntityManagerInterface $em):Response
    {
        $session = $request->getSession();

        // $authorId = $request->query->get("auther_id");
        // $bookId =  $request->query->get("book_id");
        //$bookdata = $bookrepo->findOneBy(['auther_id'=>$authorId,'id'=>$bookId]);
        //dd($book->getId());


        $book_id = $book->getId();
        $comment = new Comments();
        $form =  $this->createForm(CommentType::class, $comment);
        
        $form->handleRequest($request);
        $user = $this->getUser();

        if(!empty($session->get('username'))) {
            $username = $session->get("username");
        }
        else {
            
            $username = "";
            if(!empty($user)){
                $username = $user->getUserIdentifier();
                $session->set("username",$username);
            }
        }
        
        if ($form->isSubmitted() && $form->isValid()) { 
            $data = $form->getData();

            $form_user = $form->get('username')->getData();
            if(!empty($form_user)) {
                $session->set("username",$form_user);
            }
            
            $comment->setBooksid($book);
   
            $em->persist($data);
            $em->flush();
            return $this->redirectToRoute("app_list_books");
        }
        // print "username->".$username;
        $booksComments = $this->comRepo->findBy(['booksid'=>$book_id ]);
        return $this->render('comments/comments.html.twig', [
           'form'=>$form,
           'bookdata'=>$book,
           'comments'=>$booksComments,
           'username'=>$username,
           'user'=>$user
           //'authorId' =>$authorId,
           //'bookId' =>$bookId
        ]);
    }

    #[Route('/delcomments/{commentid}', name: 'app_del_comment')]
    public function deleteComments(Comments $commentid,Request $request):Response
     {
    //     $authorId = $request->query->get("auther_id");
    //     $bookId =  $request->query->get("bookid");
    //     $commentid =  $request->query->get("commentid");
         
        $bookId = $commentid->getBooksid()->getId();

        //$comment =  $this->comRepo->find(["id"=>$commentid]);
        // Remove it and flush
        $this->em->remove($commentid);
        $this->em->flush();
        return $this->redirectToRoute("app_add_comment",['book'=>$bookId]);
    }


    
}
