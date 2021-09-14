<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class TestController extends AbstractController
{
    #[Route('/test', name: 'test')]
    public function index(): Response
    {
        return $this->json([
            'message' => 'Welcome to your new controller!',
            'path' => 'src/Controller/TestController.php',
        ]);
    }

    //Il ne peut pas y avoir 2 routes avec le même "name". 
    #[Route('/test/nouvelle-route', name: 'test_nouveau')]
    public function nouvelle_route(): Response
    {
        /* La méthode 'render' permet de générer l'affichage d'un fichier vue qui se trouve dans le dossier 'templates'.
            Le 1er paramètre est le nom du fichier,
            Le 2ème paramètre n'est pas obligatoire. Il doit être de type array et contiendra toutes les variables que l'on veut transmettre à la vue. */
        return $this->render("base.html.twig", [ "prenom" => "Novelaine" ]);
    }


    #[Route('/test/tableau', name: 'test_tableau')]
    public function tableau()
    {
        $tableau = [ "un", 2, true ];
        $tableau1 = [ "nom" => "Cérien", "prenom" => "Jean", "age" => 30 ];

        // Je veux transmettre la valeur de la variable $tableau1 à ma vue dans une variable nommée "personne".
        // Ensuite afficher, "Je m'appelle " suivi du prenom, nom, age.
        return $this->render("test/tableau.html.twig", [ "tableau" => $tableau, "personne" => $tableau1 ]);
    }

    #[Route('/test/objet')]
    public function objet()
    {
        // on créer un objet dans une class :
        $objet = new \stdClass();
        $objet->nom = "Mentor";
        $objet->prenom = "Gérard";
        $objet->age = "54";

        return $this->render("test/tableau.html.twig", [ "personne" => $objet ]);
    }

    #[Route('/test/salut/{prenom}')]
    // dans le chemin, les {} signifient que cette partie du chemin est variable.
    // Ca peut être n'importe quel chaîne de caractères. Le nom mis entre {} est le nom de la variable passé en paramètre.
    public function prenom($prenom)
    {
        return $this->render("base.html.twig", ["prenom" => $prenom]);
    }

    /*
        Exo : Vous allez ajouter une route, "/test/liste/{nombre}"
            Le nombre passé en paramètre devra être envoyé à une vue qui étend base.html.twig.
            Cette vue va afficher la liste des nombres de 1 jusqu'au nombre passé dans le chemin dans une table HTML.
            Dans la première colonne, le nombre
            Dans la deuxième colonne, le nombre multiplié par 2
    */

    #[Route('/test/liste/{nombre?}')]
    public function nombre($nombre)
    {
        return $this->render("test/liste.html.twig", ["nombre" => $nombre]);
    }

    /*
        Exo 2 :  Créer une nouvelle route qui prend un nombre dans l'url et qui affiche le resultat de ce nombre au carré.

    */

}
