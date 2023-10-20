<?php

namespace App\Controller;

use App\Entity\Role;
use App\Entity\User;
use App\Form\UserType;
use App\Repository\BooksRepository;
use App\Repository\CommentsRepository;
use App\Repository\RoleRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AdminController extends AbstractController
{
    public function __construct(
        private UserRepository $userRepo,
        private RoleRepository $roleRepo,
        private CommentsRepository $commentRepo,
        private UserPasswordHasherInterface $passwordEncoder
    )
    {
        
    }
    #[Route('/admin', name: 'app_admin')]
    public function index(): Response
    {
        if($this->isGranted('ROLE_ADMIN')){
          //return $this->redirectToRoute("app_showbooks");
        }
        $usersList = $this->userRepo->findAll();

        
        return $this->render('admin/index.html.twig', [
            'users' => $usersList,
            'role_author' => $_ENV["ROLE_AUTHOR"],
            'role_admin' => $_ENV["ROLE_ADMIN"]
        ]);
    }

    #[Route('/admin/register', name: 'app_admin_user_register')]
    public function register(Request $request,EntityManagerInterface $em):Response
    {
        $user = new User();
        //$user = $this->getUser();

        $form = $this->createForm(UserType::class,$user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $data=$form->getData();

            $password = $form->get('password')->getData();

            $hashedPassword = $this->passwordEncoder->hashPassword(
                $user,
                $password
            );
            $user->setPassword($hashedPassword);

            $em->persist($data);
            $em->flush();

            $this->roleRepo->updateAutherRole($_ENV['ROLE_USER'],$user->getId());

            $this->addFlash('success', 'user.updated_successfully');

            return $this->redirectToRoute('app_admin');
        }
  
        return $this->render('admin/register.html.twig', [
            'form' => $form->createView()
        ]);

    }

    #[Route('/admin/enableauthrole', name: 'enable_author')]
    public function enableAuthor(Request $request): Response
    {
        //$userId = $this->getUser()->getId();
        $userId = $request->query->get('id');
        $this->roleRepo->updateAutherRole($_ENV['ROLE_AUTHOR'],$userId);

        return $this->redirectToRoute("app_admin");
    }

    #[Route('/admin/removeauthrole', name: 'remove_author')]
    public function removeAuthor(Request $request): Response
    {
        //$userId = $this->getUser()->getId();
        $userId = $request->query->get('id');
        $this->roleRepo->removeAutherRole($_ENV['ROLE_AUTHOR'],$userId);
        return $this->redirectToRoute("app_admin");
    }


    


    #[Route('/admin/removeuser/{userid}', name: 'admin_user_delete')]
    public function removeUser(
        User $userid,EntityManagerInterface $em,
        BooksRepository $bookRepo): Response
    {
        //$userId = $request->query->get('id');
        //$single_user = $em->find(User::class, $userId);
       
        $autherId = $userid->getId();
        $books =$bookRepo->findBy(['auther_id'=>$autherId]);

        //$bookidList = [];

        foreach($books as $book){
            $comment = $this->commentRepo->findBy(['booksid'=>$book->getId()]);
            if(!empty($comment)){
                $em->remove($comment[0]);
                $em->flush();
            }
        }

        foreach($books as $book){
            $em->remove($book);
            $em->flush();
        }

        $em->remove($book);
        $em->flush();

        $em->remove($userid);
        
       

        $em->flush();
        return $this->redirectToRoute("app_admin");
    }
    


    #[Route('/admin/edituser/{id}', name: 'admin_user_edit')]
    public function simpleeditform(User $user,EntityManagerInterface $em,Request $request): Response
    {

        $user_password = $user->getPassword();

        $form = $this->createFormBuilder($user)
            ->add("username",TextType::class)
            ->add("fullName",TextType::class)
            ->add("email",TextType::class)
            ->add('password',PasswordType::class,
                [
                    'empty_data' => '',
                    'required' => false,
                ]
            )
            ->add('Submit',SubmitType::class)
            ->getForm();

        $form->handleRequest($request);
        if($form->isSubmitted() && $form->isValid()){
            $data = $form->getData();
            $password = $form->get('password')->getData();
            if(!empty($password )) {
                $hashedPassword = $this->passwordEncoder->hashPassword(
                    $user,
                    $password
                );
                $user->setPassword($hashedPassword);
            }
            else {
                $user->setPassword($user_password);
            }

            $em->persist($data);
            $em->flush();
            return $this->redirectToRoute("app_admin");
        }

        return $this->render('admin/edituser.html.twig', [
            'form' => $form
        ]);
    }
    

}
