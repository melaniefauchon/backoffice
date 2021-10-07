<?php

namespace App\Controller\Backoffice;

use App\Entity\Genre;
use App\Form\GenreType;
use App\Repository\GenreRepository;
use DateTimeImmutable;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/backoffice/genre", name="backoffice_genre_")
 */
class GenreController extends AbstractController
{
    /**
     * @Route("/", name="browse")
     */
    public function browse(GenreRepository $genreRepository): Response
    {
        $genres = $genreRepository->findAll();
        return $this->render('backoffice/genre/browse.html.twig', [
            'genres' => $genres,
        ]);
    }


    /**
     * @Route("/edit/{id}", name="edit", methods={"GET", "POST"}, requirements={"id"="\d+"})
     */
    public function edit(Request $request, Genre $genre): Response
    {
        $genreForm = $this->createForm(GenreType::class, $genre);

        $genreForm->handleRequest($request);

        if ($genreForm->isSubmitted() && $genreForm->isValid()) {
            $entityManager = $this->getDoctrine()->getManager();

            $entityManager->flush();

            $this->addFlash('success', "Le genre `{$genre->getName()}` a bien été mis à jour");

            return $this->redirectToRoute('backoffice_genre_browse');
        }

        return $this->render('backoffice/genre/add.html.twig', [
            'genre_form' => $genreForm->createView(),
            'genre' => $genre,
            'page' => 'edit'
        ]);
    }

    /**
     * @Route("/add", name="add", methods={"GET", "POST"})
     */
    public function add(Request $request): Response
    {
        $genre = new Genre();

        $genreForm = $this->createForm(GenreType::class, $genre);

        $genreForm->handleRequest($request);

        if ($genreForm->isSubmitted() && $genreForm->isValid()) {

            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($genre);
            $entityManager->flush();

            $this->addFlash('success', " Le genre `{$genre->getName()}` a bien été ajoutée");

            return $this->redirectToRoute('backoffice_genre_browse');
        }

        return $this->render('backoffice/genre/add.html.twig', [
            'genre_form' => $genreForm->createView(),
            'page' => 'add'
        ]);
    }

    /**
     * @Route("/delete/{id}", name="delete", requirements={"id"="\d+"})
     */
    public function delete(Genre $genre)
    {

        $entityManager = $this->getDoctrine()->getManager();
        $entityManager->remove($genre);
        $entityManager->flush();

        $this->addFlash('success', "Le genre `{$genre->getName()}` a bien été supprimé");

        return $this->redirectToRoute('backoffice_genre_browse');
    }
}
