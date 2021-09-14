<?php

namespace App\Controller;

use App\Entity\Emprunt;
use App\Entity\Livre;
use App\Repository\LivreRepository;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ProfilController extends AbstractController
{
    #[Route('/profil', name: 'profil_index')]
    public function index(): Response
    {
        /* Pour avoir les information de l'utilisateur connecté :
            dans twig            : app.user
            dans le controller   : $this->getUser() */
            $abonneConnecte = $this->getUser();
        return $this->render('profil/index.html.twig');
    }

    #[Route('/profil/emprunter/{id}', name: 'profil_emprunter')]
    public function emprunter( LivreRepository $lr, EntityManagerInterface $em, Livre $livre)
    {
        // redirection pour pas qu'un utilisateur accede au livre emprunter
        $livresEmpruntes = $lr->livresEmpruntes(); // on accede au livre
        if(in_array($livre, $livresEmpruntes )){ // on fait passer livre dans livreEmpruntes
            $this->addFlash("danger", "Le livre <strong>" . $livre->getTitre() . "</strong> n'est pas disponible !");
            return $this->redirectToRoute("accueil"); // on redirige vers l'acceuil
        }

        $emprunt = new Emprunt;
        $emprunt->setDateEmprunt(new DateTime()); // new DateTime() cr&e un objet DateTime avec la date du jour
        $emprunt->setLivre($livre); // $livre a été récupérer de la bdd avec l'id qui est pasé dans le chemin
        $emprunt->setAbonne( $this->getUser() ); // $this->getUser() retourne un objet Abone contenant les infos de l'abonné actuellement connecté


        $em->persist($emprunt);  // comme $emprunt est un nouvel emprunt à insérer dans la bdd, il faut utiliser $em->persist()
        $em->flush();   // em->flush() enregistre en bdd 
        $this->addFlash("success", "Le nouveau livre à été enregistré");
        return $this->redirectToRoute('profil_index');
    }

}
