<?php

namespace App\Controller;

use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
#[Route('/admin/users')]
final class AdminController extends AbstractController
{
    #[Route('', name: 'app_admin')]
    public function index(UserRepository $userRepository): Response
    {
        return $this->render('admin/index.html.twig', [
            'users' => $userRepository->findAll(),
        ]);
    }

    #[Route('profile', name: 'app_profile_admin')]
    public function profile(UserRepository $userRepository): Response
    {
        return $this->render('profile/admin.html.twig');
    }




    #[Route('promote/{id}', name: 'app_promote_admin')]
public function promoteAdmin(User $user, EntityManagerInterface $manager): Response

    {
        if(!in_array('ROLE_ADMIN', $user->getRoles())){
            $user->setRoles(['ROLE_ADMIN']);
            $manager->persist($user);
            $manager->flush();
        }
        return $this->redirectToRoute('app_profile_admin');
    }

    #[Route('demote/{id}', name: 'app_demote_admin')]
    public function demoteAdmin(User $user, EntityManagerInterface $manager): Response

    {
        if(!in_array('ROLE_ADMIN', $user->getRoles())){
            $user->setRoles([]);
            $manager->persist($user);
            $manager->flush();
        }
        return $this->redirectToRoute('app_admin');
    }
}
