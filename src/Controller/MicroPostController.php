<?php

namespace App\Controller;

use DateTime;
use App\Entity\User;
use App\Entity\Comment;
use App\Entity\MicroPost;
use App\Form\CommentType;
use App\Form\MicroPostType;
use App\Repository\CommentRepository;
use App\Repository\MicroPostRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class MicroPostController extends AbstractController
{
    #[Route('/micro-post', name: 'app_micro_post')]
    public function index(MicroPostRepository $posts): Response
    {
        // $microPost = new MicroPost;
        // $microPost->setTitle('Uwaga!');
        // $microPost->setText('Ta modyfikacja pochodzi z poziomu kontrollera w php !!');
        // $microPost->setCreated(new DateTime());
        // $posts->add($microPost, true);

        // dd($posts->findAll());
        return $this->render('micro_post/index.html.twig', [
            'posts' => $posts->findAllWithComments()
        ]);
    }

    #[Route('/micro-post/top-liked', name: 'app_micro_post_topliked')]
    public function topLiked(MicroPostRepository $posts): Response
    {
        return $this->render('micro_post/top_liked.html.twig', [
            'posts' => $posts->findAllWithMinLikes(1)
        ]);
    }

    #[Route('/micro-post/follows', name: 'app_micro_post_follows')]
    #[IsGranted('IS_AUTHENTICATED_FULLY')]
    public function follows(MicroPostRepository $posts): Response
    {
        /** @var User $currentUser */
        $currentUser = $this->getUser();

        return $this->render('micro_post/follows.html.twig', [
            'posts' => $posts->findAllByAuthors($currentUser->getFollows())
        ]);
    }

    #[Route('/micro-post/{post}', name: 'app_micro_post_show')]
    #[IsGranted(MicroPost::VIEW, 'post')] 
    public function showOne(MicroPost $post): Response
    {
        // dd($post);
        return $this->render('micro_post/show.html.twig', [
            'post' => $post,
        ]);
    }

    #[Route('/micro-post/add', name: 'app_micro_post_add', priority: 2)]
    #[IsGranted('ROLE_WRITER')]
    public function add(Request $request, MicroPostRepository $posts): Response
    {
        // $microPost = new MicroPost;
        // $form = $this->createFormBuilder($microPost)
        // ->add('title')
        // ->add('text')
        // // ->add('submit', SubmitType::class, ['label' => 'save'])
        // ->getForm();

        $form = $this->createForm(MicroPostType::class, new MicroPost);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $post = $form->getData();
            //$post->setCreated(new DateTime()); ustawienia daty ew kontruktorsze
            $post->setAuthor($this->getUser());
            $posts->add($post, true);

            //add flash
            $this -> addFlash('success', 'Your micro post has been add');
            //redirect
            return $this -> redirectToRoute('app_micro_post');
        }

        return $this->render('micro_post/add.html.twig', [
            'form' => $form
        ]);
    }

    #[Route('/micro-post/{post}/edit', name: 'app_micro_post_edit')]
    #[IsGranted(MicroPost::EDIT, 'post')]
    public function edit(MicroPost $post, Request $request, MicroPostRepository $posts): Response
    {
        // $form = $this->createFormBuilder($post)
        // ->add('title')
        // ->add('text')
        // ->getForm();
        $form = $this->createForm(MicroPostType::class, $post);

        $form->handleRequest($request);

        $this->denyAccessUnlessGranted(MicroPost::EDIT, $post);

        if ($form->isSubmitted() && $form->isValid()) {
            $post = $form->getData(); 
            $posts->add($post, true);

            //add flash
            $this -> addFlash('success', 'Your micro post has been changed');
            //redirect
            return $this -> redirectToRoute('app_micro_post');
        }

        return $this->render('micro_post/edit.html.twig', [
            'form' => $form,
            'post' => $post
        ]);
    }

    #[Route('/micro-post/{post}/comment', name: 'app_micro_post_comment')]
    #[IsGranted('ROLE_COMMENTER')]
    public function addComment(MicroPost $post, Request $request, CommentRepository $comments): Response
    {
        $form = $this->createForm(CommentType::class, new Comment());
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $comment = $form->getData();
            $comment->setPost($post);
            $comment->setAuthor($this->getUser());
            $comments->add($comment, true);

            //add flash
            $this -> addFlash('success', 'Your comment has been added');
            //redirect
            return $this -> redirectToRoute('app_micro_post_show',[
                'post' => $post->getId()
            ]);
        }

        return $this->render('micro_post/comment.html.twig', [
            'form' => $form,
            'post' => $post
        ]);
    }
}
