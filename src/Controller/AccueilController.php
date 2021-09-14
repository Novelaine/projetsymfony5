<?php

namespace App\Controller;

use App\Entity\Livre;
use App\Repository\LivreRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class AccueilController extends AbstractController
{
    #[Route('/', name: 'accueil')]
    public function index(LivreRepository $lr, Request $rq): Response
    {
        return $this->render('accueil/index.html.twig', [
            "livres" => $lr->findAll(),
            "livres_empruntes" => $lr->LivresEmpruntes(),
        ]);
    }


    #[Route('/fiche/{id}', name: 'accueil_fiche')]
    public function accueilFiche( Livre $livre)
    {

        return $this->render('accueil/ficheAccueil.html.twig', compact("livre"));
    }


}
