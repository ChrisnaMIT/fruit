<?php

namespace App\Controller;

use App\Entity\Like;
use App\Entity\Post;
use App\Repository\LikeRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class LikeController extends AbstractController
{
    #[Route('/post/like/{id}', name: 'app_like_post')]
    public function index(Post $post, LikeRepository $likeRepository, EntityManagerInterface $manager): Response
    {
       if(!$this->getUser()){
           return $this->redirectToRoute('app_login');
       }

       if($post->isLikedBy($this->getUser())){
           $like = $likeRepository->findOneBy([
               'post' => $post,
               'author' => $this->getUser()
           ]);
           $manager->remove($like);
           $isLiked =false;
       } else{
           $like = new Like();
           $like->setPost($post);
           $like->setAuthor($this->getUser());
           $manager->persist($like);
           $isLiked =true;
       }
       $manager->flush();

       $data =[
           'isLiked' => $isLiked,
           'count' =>$likeRepository->count(['post' => $post])
       ];

       return $this->json($data, 200);
    }
}
