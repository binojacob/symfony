<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Entity\Author;
use App\Form\AuthorType;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;

class AuthorController extends AbstractController
{
    #[Route('/author', name: 'app_author')]
    public function index(Request $request,EntityManagerInterface $em): Response
    {
        $author = new Author();
        $form = $this->createForm(AuthorType::class,$author);

         $form->handleRequest($request);
        
        if ($form->isSubmitted() && $form->isValid()) { 
            $data = $form->getData();

            $currentDateTime = new DateTime('now'); 
            $currentDate = $currentDateTime->format('Y-m-d H:i:s');
            $dateLimitReturn = new \DateTimeImmutable($currentDate);
            $author->setCreatedAt($dateLimitReturn);

            
            $em->persist($data);
            $em->flush();
        }
        return $this->render('author/index.html.twig', [
            'form' => $form,
        ]);
    }
}
