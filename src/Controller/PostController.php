<?php

namespace App\Controller;

use App\Entity\Comment;
use App\Entity\Image;
use App\Entity\Post;
use App\Form\CommentForm;
use App\Form\ImageForm;
use App\Form\PostForm;
use App\Repository\PostRepository;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Liip\ImagineBundle\Form\Type\ImageType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Finder\Exception\AccessDeniedException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class PostController extends AbstractController
{
    #[Route('/', name: 'app_post')]
    public function index(PostRepository $repository, PaginatorInterface $paginator, Request $request): Response
    {


        $pagination = $paginator->paginate(
            $repository->findAll(),
            $request->query->getInt('page', 1),
            2
        );

        return $this->render('post/index.html.twig', [
            'posts' => $repository->findAll(),
            'pagination' => $pagination,
        ]);


    }

    #[Route('/post/show/{id}', name: 'app_post_show', priority: -1)]
    public function show(Post $post): Response
    {
        $comment= new Comment();
        $form = $this->createForm(CommentForm::class, $comment);
        return $this->render('post/show.html.twig', [
            'post' => $post,
            'form' => $form->createView(),
        ]);
    }


    #[Route('/post/create', name: 'app_post_create')]
    public function create(Request $request, EntityManagerInterface $manager):Response
    {

        if(!$this->getUser()){
            return $this->redirectToRoute('app_login');
        }


        $post = new Post();
        $form = $this->createForm(PostForm::class, $post);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $post->setAuthor($this->getUser());
            $post->setCreatedAt(new \DateTime());
            $manager->persist($post);
            $manager->flush();
            return $this->redirectToRoute('app_post_images', ['id' => $post->getId()]);
        }

        return $this->render('post/create.html.twig', [
            'form' => $form,
        ]);
    }

    #[Route('/post/delete/{id}', name: 'app_post_delete')]
    public function delete(Request $request, Post $post, EntityManagerInterface $manager): Response
    {
        if(!$this->getUser()){
            return $this->redirectToRoute('app_login');
        }



        if($post){

            foreach ($post->getLikes() as $like) {
                $manager->remove($like);
            }
            foreach ($post->getImages() as $image) {
                $manager->remove($image);
            }

            $manager->remove($post);
            $manager->flush();
        }
        return $this->redirectToRoute('app_post');
    }

    #[Route('/post/{id}/images', name: 'app_post_images')]
    public function createImage(Post $post, Request $request, EntityManagerInterface $manager): Response
    {
        if(!$this->getUser() || !$post)
        {
            return $this->redirectToRoute('app_login');
        }

        if(!$this->getUser()->getPosts()->contains($post)){
            return $this->redirectToRoute('app_post_show', ['id' => $post->getId()]);
        }

        $image = new Image();
        $form = $this->createForm(ImageForm::class, $image);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $image->setPost($post);

            $manager->persist($image);
            $manager->flush();
            return $this->redirectToRoute('app_post_images', ['id' => $post->getId()]);
        }
        return $this->render('post/createImage.html.twig', [
            'form' => $form->createView(),
            'post' => $post,
        ]);
    }

    #[Route('/post/image/delete/{id}', name: 'app_post_image_delete')]
    public function deleteImage(Image $image, EntityManagerInterface $manager): Response
    {

        if(!$this->getUser()){
            return $this->redirectToRoute('app_login');

        }

        $post = $image->getPost();
        if($post->getAuthor() != $image->getPost()){
            return $this->redirectToRoute('app_post', ['id' => $post->getId()]);
        }

        if(!$this->getUser()->getPosts()->contains($post)){
            return $this->redirectToRoute('app_post', ['id' => $post->getId()]);
        }
        $manager->remove($post);
        $manager->flush();
        return $this->redirectToRoute('app_post', ['id' => $post->getId()]);
    }



}
