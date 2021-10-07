<?php

namespace App\Controller\Backoffice;

use App\Entity\Book;
use App\Form\BookType;
use App\Repository\BookRepository;
use DateTimeImmutable;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;

/**
 * @Route("/backoffice/book", name="backoffice_book_")
 */

class BookController extends AbstractController
{
    /**
     * @Route("/", name="browse")
     * @IsGranted("ROLE_USER")
     */
    public function browse(BookRepository $bookRepository): Response
    {
        $books = $bookRepository->findAll();
        return $this->render('backoffice/book/browse.html.twig', [
            'books' => $books,
        ]);
    }

    /**
     * @Route("/read/{id}", name="read", requirements={"id"="\d+"})
     * @IsGranted("ROLE_USER")
     */
    public function read(Book $book): Response
    {
        return $this->render('backoffice/book/read.html.twig', [
            'book' => $book,
        ]);
    }

    /**
     * @Route("/edit/{id}", name="edit", methods={"GET", "POST"}, requirements={"id"="\d+"})
     * @IsGranted("ROLE_USER")
     */
    public function edit(Request $request, Book $book): Response
    {
        $bookForm = $this->createForm(BookType::class, $book);

        $bookForm->handleRequest($request);

        if ($bookForm->isSubmitted() && $bookForm->isValid()) {
            $entityManager = $this->getDoctrine()->getManager();
            $book->setUpdatedAt(new DateTimeImmutable());

            $entityManager->flush();

            $this->addFlash('success', "`{$book->getTitle()}` a bien été mis à jour");

            return $this->redirectToRoute('backoffice_book_browse');
        }

        return $this->render('backoffice/book/add.html.twig', [
            'book_form' => $bookForm->createView(),
            'book' => $book,
            'page' => 'edit'
        ]);
    }

    /**
     * @Route("/add", name="add", methods={"GET", "POST"})
     * @IsGranted("ROLE_USER")
     */
    public function add(Request $request): Response
    {
        $book = new Book();

        $bookForm = $this->createForm(BookType::class, $book);

        $bookForm->handleRequest($request);

        if ($bookForm->isSubmitted() && $bookForm->isValid()) {

            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($book);
            $entityManager->flush();

            $this->addFlash('success', "Le livre `{$book->getTitle()}` a bien été ajoutée");

            return $this->redirectToRoute('backoffice_book_browse');
        }

        return $this->render('backoffice/book/add.html.twig', [
            'book_form' => $bookForm->createView(),
            'page' => 'add'
        ]);
    }

    /**
     * @Route("/delete/{id}", name="delete", requirements={"id"="\d+"})
     * @IsGranted("ROLE_ADMIN")
     */
    public function delete(Book $book)
    {

        $entityManager = $this->getDoctrine()->getManager();
        $entityManager->remove($book);
        $entityManager->flush();

        $this->addFlash('success', "Le livre `{$book->getTitle()}` a bien été supprimé");

        return $this->redirectToRoute('backoffice_book_browse');
    }
}
