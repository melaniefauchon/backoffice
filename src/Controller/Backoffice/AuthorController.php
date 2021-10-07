<?php

namespace App\Controller\Backoffice;

use App\Entity\Author;
use App\Form\AuthorType;
use App\Repository\AuthorRepository;
use DateTimeImmutable;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;

/**
 * @Route("/backoffice/author", name="backoffice_author_")
 */
class AuthorController extends AbstractController
{
    /**
     * @Route("/", name="browse")
     * @IsGranted("ROLE_USER")
     */
    public function browse(AuthorRepository $authorRepository): Response
    {
        $authors = $authorRepository->findAll();
        return $this->render('backoffice/author/browse.html.twig', [
            'authors' => $authors,
        ]);
    }

    /**
     * @Route("/read/{id}", name="read", requirements={"id"="\d+"})
     * @IsGranted("ROLE_USER")
     */
    public function read(Author $author): Response
    {
        return $this->render('backoffice/author/read.html.twig', [
            'author' => $author,
        ]);
    }

    /**
     * @Route("/edit/{id}", name="edit", methods={"GET", "POST"}, requirements={"id"="\d+"})
     * @IsGranted("ROLE_USER")
     */
    public function edit(Request $request, Author $author): Response
    {
        $authorForm = $this->createForm(AuthorType::class, $author);

        $authorForm->handleRequest($request);

        if ($authorForm->isSubmitted() && $authorForm->isValid()) {
            $entityManager = $this->getDoctrine()->getManager();

            $entityManager->flush();

            $this->addFlash('success', "`{$author->getFirstname()} {$author->getLastname()}` a bien été mis à jour");

            return $this->redirectToRoute('backoffice_author_browse');
        }

        return $this->render('backoffice/author/add.html.twig', [
            'author_form' => $authorForm->createView(),
            'author' => $author,
            'page' => 'edit'
        ]);
    }

    /**
     * @Route("/add", name="add", methods={"GET", "POST"})
     * @IsGranted("ROLE_USER")
     */
    public function add(Request $request): Response
    {
        $author = new Author();

        $authorForm = $this->createForm(AuthorType::class, $author);

        $authorForm->handleRequest($request);

        if ($authorForm->isSubmitted() && $authorForm->isValid()) {

            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($author);
            $entityManager->flush();

            $this->addFlash('success', "`{$author->getFirstname()} {$author->getLastname()}` a bien été ajoutée");

            return $this->redirectToRoute('backoffice_author_browse');
        }

        return $this->render('backoffice/author/add.html.twig', [
            'author_form' => $authorForm->createView(),
            'page' => 'add'
        ]);
    }

    /**
     * @Route("/delete/{id}", name="delete", requirements={"id"="\d+"})
     * @IsGranted("ROLE_ADMIN")
     */
    public function delete(Author $author)
    {

        $entityManager = $this->getDoctrine()->getManager();
        $entityManager->remove($author);
        $entityManager->flush();

        $this->addFlash('success', "`{$author->getFirstname()} {$author->getLastname()}` a bien été supprimé");

        return $this->redirectToRoute('backoffice_author_browse');
    }
}
