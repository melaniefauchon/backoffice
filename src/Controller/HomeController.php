<?php

namespace App\Controller;

use App\Repository\BookRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class HomeController extends AbstractController
{
    /**
     * @Route("/", name="homepage")
     */
    public function home(BookRepository $bookRepository): Response
    {
        $books = $bookRepository->findByLastUpdated();

        return $this->render('home/homepage.html.twig', [
            'books'=>$books
        ]);
    }
}
