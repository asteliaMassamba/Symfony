<?php

// src/OC/PlatformBundle/Controller/AdvertController.php

namespace OC\PlatformBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
/*use Symfony\Component\HttpFoundation\RedirectResponse;*/
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use OC\PlatformBundle\Entity\Advert;
use OC\PlatformBundle\Form\AdvertType;


class AdvertController extends Controller {
    
    public function indexAction($page) {
        if ($page < 1) {
            throw new NotFoundHttpException('Page "'.$page.'" inexistante.');
        }
        
        $nbPerPage = 3;
        
        $listAdverts = $this->getDoctrine()
                ->getManager()
                ->getRepository("OCPlatformBundle:Advert")
                ->getAdverts($page, $nbPerPage);
        
        $nbPages = ceil(count($listAdverts) / $nbPerPage);
        
        if ($page > $nbPages) {
            throw $this->createNotFoundException("La page ".$page." n'existe pas.");
        }
        
        return $this->render("@OCPlatform/Advert/index.html.twig"
                           , array("listAdverts" => $listAdverts
                                 , "nbPages" => $nbPages
                                 , "page" => $page)
                            );
    }
    

    public function viewAction($id) {
        $em = $this->getDoctrine()->getManager();
        $advert = $em
                ->getRepository("OCPlatformBundle:Advert")
                ->find($id);
        
        if (null === $advert) {
            throw new NotFoundHttpException("L'annonce d'id ".$id." n'existe pas.");
        }
        
        // On recupere la liste des candidatures de cette annonce
        $listApplications = $em
                            ->getRepository("OCPlatformBundle:Application")
                            ->findBy(array("advert" => $advert));
        
        $listAdvertSkills = $em
                            ->getRepository("OCPlatformBundle:AdvertSkill")
                            ->findBy(array("advert" => $advert));
        
        return $this->render("@OCPlatform/Advert/view.html.twig"
                            , array("advert" => $advert,
                                    "listApplications" => $listApplications,
                                    "listAdvertSkills" => $listAdvertSkills
                                ));
    }
    
    public function addAction(Request $request) {
        // On crée un objet Advert
        $advert = new Advert();
        
        /*$advert->setDate(new \DateTime());*/
        
        $form = $this->get("form.factory")
                     ->create(AdvertType::class, $advert); 
        // <=> $form = $this->createForm(AdvertType::class, $advert);
        
        // Si la requête est en POST et
            // On fait le lien Requête <-> Formulaire
            // A partir de maintenant, la variable $advert contient les valeurs 
            // entrées dans le formulaire par le visiteur
            // On vérifie que les valeurs entrées sont correctes
            // (Nous verrons la validation des objets en détail dans le prochain chapitre)
        if ($request->isMethod("POST") && $form->handleRequest($request)->isValid()) {
            // On enregistre notre objet $advert dans la base de données, par exemple
            $em = $this->getDoctrine()->getManager();
            $em->persist($advert);
            $em->flush();
                
            // Reste de la methode qu'on avait deja ecrit
            $request->getSession()->getFlashBag()->add("notice", "Annonce bien enregistrée.");
            
            // On redirige vers la page de visualisation de cette annonce
            return $this->redirectToRoute("oc_platform_view", array("id" => $advert->getId()));
        }
        
        // A ce stade, le formulaire n'est pas valide car :
        // - Soit la requête est de type GET, donc le visiteur vient d'arriver sur 
        // la page et veut voir le formulaire
        // - Soit la requête est de type POST, mais le formulaire contient 
        // des valeurs invalides, donc on l'affiche de nouveau
        return $this->render("@OCPlatform/Advert/add.html.twig"
                           , array("form" => $form->createView()));
    }
    
    public function editAction($id, Request $request) {
        $advert = $this->getDoctrine()->getManager()
                       ->getRepository("OCPlatformBundle:Advert")
                       ->find($id);
        
        if (null === $advert) {
            throw new NotFoundHttpException("L'annonce d'id ".$id." n'existe pas.");
        }
        
        $form = $this->get("form.factory")
                     ->create(\OC\PlatformBundle\Form\AdvertEditType::class, $advert);
        
        if ($request->isMethod("POST") && $form->handleRequest($request)->isValid()) {            
            $em = $this->getDoctrine()->getManager();
            $em->persist($advert);
            $em->flush();
                
            $request->getSession()->getFlashBag()->add("notice", "Annonce bien enregistrée.");
            
            return $this->redirectToRoute("oc_platform_view", array("id" => $advert->getId()));
        }
        
        return $this->render("@OCPlatform/Advert/edit.html.twig"
                            , array("advert" => $advert
                                  , "form" => $form->createView()));
    }

    public function deleteAction(Request $request, $id) {
        $em = $this->getDoctrine()->getManager();
        
        $advert = $em->getRepository("OCPlatformBundle:Advert")->find($id);
        
        if (null === $advert) {
            throw new NotFoundHttpException("L'annonce d'id ".$id." n'existe pas.");
        }
        
        // On crée un formulaire vide, qui ne contiendra que le champ CRSF
        // Cela permet de protéger la suppression d'annonce contre cette faille
        $form = $this->get("form.factory")->create();
        
        if ($request->isMethod("POST") && $form->handleRequest($request)->isValid()) {
            $em->remove($advert);
            $em->flush();
            
            $request->getSession()->getFlashBag()->add("notice", "L'annonce a bien été supprimée.");
            return $this->redirectToRoute("oc_platform_home");
        }
        
        
        return $this->render("@OCPlatform/Advert/delete.html.twig"
                           , array("advert" => $advert
                                 , "form" => $form->createView()
                            ));
    }
    
    public function menuAction($limit) {
        $em = $this->getDoctrine()->getManager();
        $listAdverts = $em->getRepository("OCPlatformBundle:Advert")
                          ->findBy(
                                array(),                 // Pas de critère
                                array("date" => "desc"), // On trie par date décroissante
                                $limit,                  // On sélectionne $limit annonces
                                0                        // A partir du premier
                            );
        
        return $this->render("@OCPlatform/Advert/menu.html.twig"
                    , array("listAdverts" => $listAdverts));
    }

    
    
    
    
    
    
    /* Méthodes de tutoriel */
    
    public function test2Action() {
        $advert = new Advert();
        
        $advert->setDate(new \DateTime()); // Champ << date >> OK
        $advert->setTitle("abc"); // Champ << title >> incorrect : 
        // moins de 10 caractères
        //$advert->setcontent("blabla"); // Champ << content >> incorrect : 
        // on ne le définit pas
        $advert->setAuthor("A"); // Champ << author >> incorrect : 
        // moins de 2 caractères
        
        // On récupère le service validator
        $validator = $this->get("validator");
        
        // On déclenche la validation sur notre object
        $listErrors = $validator->validate($advert);
        
        // Si $listErrors n'est pas vide, on affiche les erreurs
        if (count($listErrors) > 0) {
            // $listErrors est un objet, sa méthode __toString permet
            // de lister joliement les erreurs
            return new Response((string) $listErrors);
        }
        else {
            return new Response("L'annonce est valide !");
        }
    }

    public function listAction() {
        $listAdverts = $this
                ->getDoctrine()
                ->getManager()
                ->getRepository("OCPlatformBundle:Advert")
                ->getAdvertWithApplications();
        
        foreach ($listAdverts as $advert) {
            // Ne declenche pas de requete : les candidatures sont deja chargees ! 
            // Vous pourriez faire une boucle dessus pour les afficher toutes
            $advert->getApplications();
        }
        
        return $this->render("@OCPlatform/Advert/view_slug.html.twig"
                            , array("listAdverts" => $listAdverts, ));
    }

    public function viewSlugAction($slug, $year, $_format) {
        $content = "On pourrait afficher l'annonce correspondant "
                  ."au slug '".$slug."', créée en ".$year." et au "
                  ."format ".$_format.".";
        
        
        /*$liste_membres = Array();
        for ($i=10; $i<20; $i++) {
            $liste_membres[$i] = "membre_$i";
        }*/
        
        /*
        $antispam = $this->container->get("oc_platform.antispam");
        
        $text = "bidule";
                /*"Alexandre et Emilie joyeuse petite souris. "
               ."De la ville et de la campagne c'est une équipe qui gagne. "
               ."Quelque soit la ville le pays; On se fait plein d'amis. "
               ."Quelques gros chiens quelques gros rats du moment que ce n'est pas un chat. "
               ."On veut voir l'univers; Decouvrir ses mystères. "
               ."On vous emmène chaque matin. L'aventure est au bout du chemin. "
               ."Alexandre et Emilie joyeuse petite souris. "
               ."De la ville et de la campagne c'est une équipe qui gagne. "
               ."Alexandre et Emilie !!!";
        if ($antispam->isSpam($text)) {
            throw new \Exception("Votre message a été détecté comme un spam !");
        }
        
        if ($request->isMethod("POST")) {
            $request->getSession()->getFlashBag()->add("notice", "Annonce bien enregistrée.");
            return $this->redirectToRoute("oc_platform_view", array("id" => 5));
        }
         */
        
        /*
        // La methode findAll() retourne toutes les categories de la BDD
        $listCategories = $em
                        ->getRepository("OCPlatformBundle:Category")
                        ->findAll();
        
        // On boucle sur les categories pour les lier a l'annonce
        foreach ($listCategories as $category) {
            $advert->addCategory($category);
        }
        
        $em->flush();
        */
        return $this->render("@OCPlatform/Advert/view_slug.html.twig"
                            , array("content" => $content, ));
    }
    
    public function tutoAction($id/*, Request $request*/) {
        /* Différentes méthodes de réponse */
        /*
        Méthode 1 :
        $response = new Response();
        $response->setContent("Ceci est une page d'erreur 404");
        $response->setStatusCode(Response::HTTP_NOT_FOUND);
        return $response;
        */
        /*
        Méthode 2 :
        Si par la méthode GET :
        $tag = $request->query->get("tag");
        Si par la méthode POST :
        $tag = $request->request->get("tag");
        return $this->get("templating")
                    ->renderResponse("@OCPlatform/Advert/view.html.twig"
                                    , array("id" => $id, "tag" => $tag));
        
        */
        /*
        Méthode 3 : la plus utilisée
        $tag = $request->query->get("tag");
        return $this->render("@OCPlatform/Advert/view.html.twig"
                                    , array("id" => $id, "tag" => $tag));
        */
        
        /* Méthodes de redirection */
        /*
        Méthode 1 :
        $url = $this->get("router")->generate("oc_platform_home");
        return new RedirectResponse($url);
        */
        /*
        Méthode 2 :
        return $this->redirectToRoute("oc_platform_home");
        */
        
        /* Méthode de changement du Content-type */
        $response = new Response(json_encode(array("id" => $id)));
        $response->headers->set("Content-Type", "application/json");
        return $response;
        /*return new JsonResponse(array("id" => $id))*/
        /*
        Avec une Session
        $session = $request->getSession();
        $userId = $session->get("user_id");
        $session->set("user_id", 91);
        return new Response("<body>Je suis une page de test, je n'ai rien à dire</body>");
        */
        
        /*$content = $this->get("templating")
                        ->render("@OCPlatform/Advert/index.html.twig"
                                , array("nom" => "winzou"));
        return new Response($content);
        */
        /*$url = $this->get("router")
                    ->generate("oc_platform_view" // 1er argument : le nom de la route
                              , array("id" => 5)); // 2e argument : les valeurs des parametres
        return new Response("L'URL de l'annonce d'id 5 est : ".$url);
        */
        /* Pour générer une URL absolue :
         * en 3e argument : URLGeneratorInterface::ABSOLUTE_URL
         */
        
        /* $url = $this->get("router")->generate("oc_platform_home");
         * <=>
         * $url = $this->generateurUrl("oc_platform_home");
         */
    }
    
    public function testAction() {
        // Création de l'entite
        /* $advert = new Advert();
        $advert->setTitle("Recherche developpeur Symfony.");
        $advert->setAuthor("Alexandre");
        $advert->setContent("Nous recherchons un developpeur Symfony debutant sur Lyon. Blabla...");
        // On peut ne pas definir ni la date ni la publication,
        // car ces attributs sont definis automatiquement dans le constructeur
        
        // Creation de l'entite Advert
        $image = new Image();
        $image->setUrl("http://sdz-upload.s3.amazonaws.com/prod/upload/job-de-reve.jpg");
        $image->setAlt("Job de reve");
        
        //On lie l'image a l'annonce
        $advert->setImage($image);
        
        // Creation d'une premiere candidature
        $application1 = new Application();
        $application1->setAuthor("Marine");
        $application1->setContent("J'ai toutes les qualites requises.");
        
        // Creation d'une deuxieme candidature par exemple
        $application2 = new Application();
        $application2->setAuthor("Pierre");
        $application2->setContent("Je suis tres motive.");
        
        // On lie les candidatures a l'annonce
        $application1->setAdvert($advert);
        $application2->setAdvert($advert);
        
        // On recupere l'EntityManager
        $em = $this->getDoctrine()->getManager();
        
        // On recupere toutes les competences possibles
        $listSkills = $em->getRepository("OCPlatformBundle:Skill")->findAll();
        foreach ($listSkills as $skill) {
            $advertSkill = new AdvertSkill();
            $advertSkill->setAdvert($advert);
            $advertSkill->setSkill($skill);
            $advertSkill->setLevel("Expert");
        }
        $em->persist($advertSkill);
        
        // Etape 1 : On << persiste >> l'entite
        // Dit que cette entite (objet) est geree par doctrine
        // Etape 1 bis : si on n'avait pas defini le cascade={"persist"},
        // on devrait persister a la main l'entite $image
        // $em->persist($image);
        $em->persist($advert);
        // La methode 'detach($entite)' a l'effet inverse, il annule le persist
        // La methode 'clear()' annule tous les persist effectues
        // La methode 'contains($entite)' retourne true si $entite est geree par l'EntityManager
        // La methode 'refresh($entite)' met a jour l'entite dans l'etat ou elle est dans la BDD
        // La methode 'remove($entite)' supprime l'entite de la BDD au prochain flush
        
        // Etape 1 ter : pour cette relation pas de cascade lorsqu'on  persiste Advert, car la relation est
        // definie dans l'entite Application  et non Advert. On doit donc tout persister a la main ici.
        $em->persist($application1);
        $em->persist($application2);
        
        // Etape 2 : On << flush >> tout ce qui a ete persite avant
        // Execute la requete SQL associee
        $em->flush();
        */
        
        /*$repository = $this
                ->getDoctrine()
                ->getManager()
                ->getRepository("OCPlatformBundle:Advert");
        
        $listAdverts = $repository->myFindAll();
        
        return $this->render(array("@OCPlatform/Advert/view.html.twig"
                           , "listAdverts" => $listAdverts));
        */
        $advert = new Advert();
        $advert->setTitle("Recherche développeur !");
        
        $em = $this->getDoctrine()->getManager();
        $em->persist($advert);
        $em->flush(); // C'est à ce moment qu'est généré le slug
        
        return new Response("Slug généré : ".$advert->getSlug());
        // Affiche << Slug généré : recherche-developpeur >>
    }
    
    public function editImageAction($advertId) {
        $em = $this->getDoctrine()->getManager();
        
        $advert = $em->getRepository("OCPlatformBundle:Advert")->find($advertId);
    
        $advert->getImage()->setUrl("test.png");
        
        $em->flush();
        
        /*$form = $this->get("form.factory")
                     ->createBuilder(FormType::class, $advert)        
                        ->add("date",      DateType::class)
                        ->add("title",     TextType::class)
                        ->add("content",   TextareaType::class)
                        ->add("author",    TextType::class)
                        ->add("published", CheckboxType::class, array("required" => false))
                        ->add("save",      SubmitType::class)
                            ->getForm();*/
        
        return new Response("OK");
    }
}
