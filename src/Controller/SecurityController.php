<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Controller;

use App\Entity\User;
use App\Form\UserType;
use App\Repository\RoleRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Symfony\Component\Security\Http\Util\TargetPathTrait;

/**
 * Controller used to manage the application security.
 * See https://symfony.com/doc/current/security/form_login_setup.html.
 *
 * @author Ryan Weaver <weaverryan@gmail.com>
 * @author Javier Eguiluz <javier.eguiluz@gmail.com>
 */
final class SecurityController extends AbstractController
{
    use TargetPathTrait;


    #[Route(path: '/logout', name: 'security_logout')]
    public function logout(): Response
    {
        return $this->redirectToRoute('security_login');
       // throw new \LogicException('This method can be blank - it will be intercepted by the logout key on your firewall.');
    }

    #[Route('/register', name: 'app_user_register')]
    public function register(
        RoleRepository $roleRepo,
        Request $request,EntityManagerInterface $em, 
        UserPasswordHasherInterface $passwordEncoder):Response
    {
        $user = new User();
        //$user = $this->getUser();

        $form = $this->createForm(UserType::class,$user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $data=$form->getData();

            $password = $form->get('password')->getData();

            $hashedPassword = $passwordEncoder->hashPassword(
                $user,
                $password
            );
            $user->setPassword($hashedPassword);

            $em->persist($data);
            $em->flush();
            $this->addFlash('success', 'user.updated_successfully');

            $roleRepo->updateAutherRole($_ENV['ROLE_USER'],$user->getId());
            return $this->redirectToRoute('security_login');
        }
  
        return $this->render('security/register.html.twig', [
            'form' => $form->createView()
        ]);
       

    }

    #[Route(path: '/login', name: 'security_login')]
    public function login(AuthenticationUtils $authenticationUtils): Response
    {
        // if ($this->getUser()) {
        //     return $this->redirectToRoute('target_path');
        // }

        // get the login error if there is one
        $error = $authenticationUtils->getLastAuthenticationError();
        // last username entered by the user
        $lastUsername = $authenticationUtils->getLastUsername();

        return $this->render('security/login.html.twig', ['last_username' => $lastUsername, 'error' => $error]);
    }
}
