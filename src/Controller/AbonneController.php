<?php

namespace App\Controller;

use App\Entity\Abonne;
use App\Entity\Livre;
use App\Form\AbonneType;
use App\Repository\AbonneRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/admin/abonne')]
class AbonneController extends AbstractController
{
    #[Route('/', name: 'abonne_index', methods: ['GET'])]
    public function index(AbonneRepository $abonneRepository): Response
    {
        return $this->render('abonne/index.html.twig', [
            'abonnes' => $abonneRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'abonne_new', methods: ['GET', 'POST'])]
    public function new(Request $request, UserPasswordHasherInterface $hasher): Response
    {
        $abonne = new Abonne();
        $form = $this->createForm(AbonneType::class, $abonne);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager = $this->getDoctrine()->getManager();
            $mdp = $form->get("password")->getData(); // il faut utilisier getData pour avoir la 'value' de l'input password
            $mdp = $hasher->hashPassword($abonne, $mdp);
            $abonne->setPassword($mdp);
            $entityManager->persist($abonne);
            $entityManager->flush();
            $this->addFlash("success", "Le nouveau abonné : <strong>" . $abonne->getPseudo() . "</strong> à été enregistré !");
            return $this->redirectToRoute('abonne_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('abonne/new.html.twig', [
            'abonne' => $abonne,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'abonne_show', methods: ['GET'])]
    public function show(Abonne $abonne): Response
    {
        return $this->render('abonne/show.html.twig', [
            'abonne' => $abonne,
        ]);
    }

    #[Route('/{id}/edit', name: 'abonne_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, UserPasswordHasherInterface $hasher, Abonne $abonne): Response
    {
        $form = $this->createForm(AbonneType::class, $abonne);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $mdp = $form->get("password")->getData();
            if( $mdp ){ // Si mot de passe n'est pas vide, on change le mot de passse, sinon on laisse le champs vide.
            $mdp = $hasher->hashPassword($abonne, $mdp);
            $abonne->setPassword($mdp);
            }
            $this->getDoctrine()->getManager()->flush();
            $this->addFlash("success", "L'abonné : <strong>" . $abonne->getPseudo() . "</strong> à été mis à jour !");
            return $this->redirectToRoute('abonne_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('abonne/edit.html.twig', [
            'abonne' => $abonne,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'abonne_delete', methods: ['POST'])]
    public function delete(Request $request, Abonne $abonne): Response
    {
        if ($this->isCsrfTokenValid('delete'.$abonne->getId(), $request->request->get('_token'))) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($abonne);
            $entityManager->flush();
        }
        $this->addFlash("success", "L'abonné : <strong>" . $abonne->getPseudo() . "</strong> à été supprimer !");
        return $this->redirectToRoute('abonne_index', [], Response::HTTP_SEE_OTHER);
    }
}
