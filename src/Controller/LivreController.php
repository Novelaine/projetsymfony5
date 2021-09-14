<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use App\Entity\Livre;
use App\Form\LivreType;
use App\Repository\CategorieRepository;
use Doctrine\ORM\EntityManagerInterface as EntityManager;
use App\Repository\LivreRepository;

#[Route('/admin')]


class LivreController extends AbstractController
{

    #[Route('/livre', name: 'livre')]
    public function index(LivreRepository $lr): Response
    {

        return $this->render("livre/index.html.twig", [
            // retourne la liste de tout les livres
            "livres" => $lr->findAll(),
            "livres_empruntes" => $lr->LivresEmpruntes()
        ]);
    }

    #[Route('/mes-livres', name: 'livre_mes_livres')]
    public function meslivres(LivreRepository $lr): Response
    {
        /*$mesLivres = {#[
            [ "titre" => "Dune", "auteur" => "Franck Herbert" ],
            [ "titre" => "1984", "auteur" => "George Orwell" ],
            [ "titre" => "Le Seigneur des Anneaux", "auteur" => "J.R.R. Tolkien" #}]
        ];*/
        
        // echo $mesLivres[1]["auteur"];

        return $this->render("livre/meslivres.html.twig", [ "livres" => $lr->findAll() ]);
    }
    #[Route('/livre/nouveau', name: 'livre_nouveau')]
    public function nouveau( EntityManager $em, Request $request,)
    {
        $livre = new Livre();
        $form = $this->createForm(LivreType::class, $livre);
        $form->handleRequest($request);
        if( $form->isSubmitted() && $form->isValid()){
            if( $fichier = $form->get("photo")->getData() ){

                $nomFichier = pathinfo($fichier->getClientOriginalName(), PATHINFO_FILENAME);
                // On remplace les espaces par des _ 
                $nomFichier = str_replace(" ", "_", $nomFichier);

                // je concatène une chaîne de caractères unique pour éviter
                // d'avoir 2 photos avec le même nom (sinon, la photo précédente sera écrasée)
                // On ajoute donc un string unique avec uniqid() au nom du fichier pour éviter les doublons
                $nomFichier .= uniqid() . "." . $fichier->guessExtension();

                // On copie le fichier uploadé dans un dossier du dossier public avec le nouveau nom de fichier.
                $fichier->move($this->getParameter( "dossier_images" ), $nomFichier);

                // On modifie l'entité $livre
                $livre->setPhoto($nomFichier);
            }
            $em->persist($livre);
            // Toutes les modifications des objets Entity qui ont été instancié à partir de la BDD vont être enregistrées en BDD quand on va utilisé $em->flush().
            $em->flush();
            return $this->redirectToRoute("livre");
        }

        return $this->render("livre/form.html.twig", ["formLivre" => $form->createView() ]);
    
    }

    #[Route('/livre/ajouter', name: 'livre_ajouter')]
    // Pour instancier un objet de la classe Request, onva utiliser l'injection de dépendance.
    // On définit un paramètre dans une méthode d'un contrôleur de la classe Request et dans cette méthode, on pourra utiliser l'objet, qui contiendra des propriétés avec toutes les valeurs des superglobales de PHP.
    // $request->query : cette propriété est l'objet qui a les valeurs de $_GET.
    // $request->request : propriéte qui a les valeurs de $_POST.
    public function ajouter(Request $request, EntityManager $em, CategorieRepository $cr)
    {
        //dump($request);
        if( $request->isMethod("POST")){
            $titre = $request->request->get("titre"); // la méthode 'get' permet de récuperer les valeurs des inputs du formulaire /!\ Ne pas confondre avec $_GET, 'get' est une autre méthode.
            $auteur = $request->request->get("auteur");
            $categorie_id = $request->request->get("categorie");
            $description = $request->request->get("description");
            $fichier = $request->request->get("photo");

            if( $titre && $auteur ){ // Si $titre et $auteur ne sont pas vide
                $nouveauLivre = new Livre; // faire un use (en haut de page) pour eviter de faire appel a tout le champs "\App\Entity\Livre"
                $nouveauLivre->setTitre($titre);
                $nouveauLivre->setAuteur($auteur);
                $nouveauLivre->setCategorie($cr->find($categorie_id));
                $nouveauLivre->setDescription($description);
                $nouveauLivre->setPhoto($fichier);


                // on va utiliser l'objet $em de la classe EntityManager pour enregistrer en BDD.
                // La méthode 'persist' permet de préparer une requête INSERT INTO. Le paramètre DOIT être un objet d'une classe Entity.
                $em->persist($nouveauLivre);
                // La méthode 'flush' exécute toutes les requêtes en attente. La BDD est modifiée quand cette méthode est lancé(et pas avant).
                $em->flush();

                return $this->redirectToRoute("livre"); // redirection vers la liste des livres.
            }
        }


        // Exo: La route doit afficher un formulaire pour pouvoir ajouter un livre
        //      Ajouter un lien dans le menu pour accéder à cette route
        return $this->render("livre/ajouter.html.twig", [
            "categories" => $cr->findAll()
        ]);
    }

    #[Route('/livre/modifier/{id}', name: 'livre_modifier')]
    public function modifier(entityManager $em, Request $request, LivreRepository $lr, $id)
    {
        $livre = $lr->find($id); // Find retourne l'objet Livre dont l'id vaut $id en BDD.
        // 'createForm' va créer un objet représentant le formulaire créé à partir de la classe LivreType
        // le 2éme paramètre est un objet Entity qui sera lié au formulaire.
        $form = $this->createForm(LivreType::class, $livre);

        // La méthode 'handleequest' permet à $form de gérer les informations venant de la requête HTTP
        // Ex: est-ce qe le formulaire a été soumis ? ...
        $form->handleRequest($request);
        if( $form->isSubmitted() && $form->isValid() ){ // si le formulaire a été soumis et s'il est valide, on éxecute le code.
            
            // je mets les informations de la photo téléchargée dans la variable $fichier
            // et je vérifié s'il y a bien une photo téléchargée
            if( $fichier = $form->get("photo")->getData() ){
                // je récupère le nom de la photo dans $nomPhoto 
                $nomFichier = pathinfo($fichier->getClientOriginalName(), PATHINFO_FILENAME);
                // On remplace les espaces par des _ 
                $nomFichier = str_replace(" ", "_", $nomFichier);

                // je concatène une chaîne de caractères unique pour éviter
                // d'avoir 2 photos avec le même nom (sinon, la photo précédente sera écrasée)
                // On ajoute donc un string unique avec uniqid() au nom du fichier pour éviter les doublons
                $nomFichier .= uniqid() . "." . $fichier->guessExtension();

                // On copie le fichier uploadé dans un dossier du dossier public avec le nouveau nom de fichier.
                $fichier->move($this->getParameter( "dossier_images" ), $nomFichier);

                // On modifie l'entité $livre
                $livre->setPhoto($nomFichier);
            }

            
            // Toutes les modifications des objets Entity qui ont été instancié à partir de la BDD vont être enregistrées en BDD quand on va utilisé $em->flush().
            $em->flush();
            return $this->redirectToRoute("livre");
        }

        return $this->render("livre/form.html.twig", ["formLivre" => $form->createView() ]);
    }

    #[Route('/livre/supprimer/{id}', name: 'livre_supprimer')]
    public function supprimer(Request $request, EntityManager $em, Livre $livre)
    {
        // Si le paramètre placé dans le chemin est une propriété d'une classe Entity, on peut récupérer directement l'objet dont la propriété vaut ce qui sera passé dans l'URL ($livre contiendra le livre dont l'id sera passé dans l'url)
        //dd($livre); //dump & die : var_dump et l'execution de code est arrété 
        if( $request->isMethod("POST")){
            $em->remove($livre); // Au lieu d'utiliser un 'persist()' car on ne veut pas recupérer de données on va faire un 'remove()' qui lui va supprimer la requête demander 
            $em->flush(); // Toutes les requête en attente sont exécutées
            return $this->redirectToRoute("livre");
        }
        return $this->render("livre/supprimer.html.twig", ["livre" => $livre]);
    }

    #[Route('/livre/fiche/{id}', name: 'livre_fiche')]
    public function fiche(Livre $livre)
    {
        // La fonction compact() de PHP retourne un array associatif à partir des variables qui ont le même nom que les paramètres passés à compact.
        // Par exemple, si j'ai 2 variables :
        // $nom = "Ayeur";
        // $prenom = "Nordine";
        //      $personne = compact("nom", "prenom");
        // est équivalent à 
        //      $personne = [ "nom" => "Ayeur", "prenom" => "Nordine"];
        return $this->render("livre/fiche.html.twig", compact("livre"));
    }
   

}
