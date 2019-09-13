<?php
// src/OC/PlatformBundle/Controller/AdvertController.php

namespace OC\PlatformBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Cookie;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RedirectResponse; // N'oubliez pas ce use
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use OC\PlatformBundle\Entity\Advert1;
use Doctrine\ORM\EntityRepository;

/**
 * @Route("/platform", name="oc_platform")
 */
class AdvertController extends Controller
{
    /**
     * @Route("/{page}", name="oc_platform_home", requirements={"page"="\d*"}, defaults={"page"=1})
     */
    public function indexAction($page)
    {
        // On ne sait pas combien de pages il y a
        // Mais on sait qu'une page doit être supérieure ou égale à 1
        if ($page < 1) {
            // On déclenche une exception NotFoundHttpException, cela va afficher
            // une page d'erreur 404 (qu'on pourra personnaliser plus tard d'ailleurs)
            throw new NotFoundHttpException('Page "' . $page . '" inexistante.');
        }

        // Ici, on récupérera la liste des annonces, puis on la passera au template
        $listAdverts = array(
            array(
                'title' => 'Recherche développpeur Symfony2',
                'id' => 1,
                'author' => 'Alexandre',
                'content' => 'Nous recherchons un développeur Symfony2 débutant sur Lyon. Blabla…',
                'date' => new \Datetime()),
            array(
                'title' => 'Mission de webmaster',
                'id' => 2,
                'author' => 'Hugo',
                'content' => 'Nous recherchons un webmaster capable de maintenir notre site internet. Blabla…',
                'date' => new \Datetime()),
            array(
                'title' => 'Offre de stage webdesigner',
                'id' => 3,
                'author' => 'Mathieu',
                'content' => 'Nous proposons un poste pour webdesigner. Blabla…',
                'date' => new \Datetime())
        );

        // Et modifiez le 2nd argument pour injecter notre liste
        return $this->render('OCPlatformBundle:Advert:index.html.twig', array(
            'listAdverts' => $listAdverts
        ));

    }

    /**
     * @Route("/advert/{id}", name="oc_platform_view")
     */
    public function viewAction($id)
    {
        // On récupère le repository
        //$repository = $this->getDoctrine()
        //    ->getManager()
        //    ->getRepository('OCPlatformBundle:Advert')
        //;
        $repository = $this
            ->getDoctrine()
            ->getManager()
            ->getRepository('OCPlatformBundle:Advert')
        ;
        $advert = $repository->find(1);

        // $advert est donc une instance de OC\PlatformBundle\Entity\Advert
        // ou null si l'id $id  n'existe pas, d'où ce if :
        if (null === $advert) {
            throw new NotFoundHttpException("L'annonce d'id ".$id." n'existe pas.");
        }

        // Le render ne change pas, on passait avant un tableau, maintenant un objet
        return $this->render('OCPlatformBundle:Advert:view.html.twig', array(
            'advert' => $advert
        ));
    }

    /**
     * @Route("/query")
     */
    public function testAction()
    {
        $repository = $this
            ->getDoctrine()
            ->getManager()
            ->getRepository('OCPlatformBundle:Advert')
        ;

        $listAdverts = $repository->query();

        return $this->render('OCPlatformBundle:Advert:query.html.twig', array(
            'listAdverts' => $listAdverts
        ));
    }

    /**
     * @Route("/query2")
     */
    public function test2Action()
    {
        $advert = new Advert1();
        $advert->setTitle("Recherche développeur !");
        $advert->setAuthor('Marine');
        $advert->setContent("Nous recherchons un développeur Symfony débutant sur Lyon. Blabla…");
        $advert->setDate(new \DateTime());
        $advert->setCategory("Java");

        $em = $this->getDoctrine()->getManager();
        $em->persist($advert);
        $em->flush(); // C'est à ce moment qu'est généré le slug

        return new Response('Slug généré : '.$advert->getSlug());
        // Affiche « Slug généré : recherche-developpeur »
    }

    /**
     *@Route("/cat")
     */
    public function catAction(){
        $repository = $this
            ->getDoctrine()
            ->getManager()
            ->getRepository('OCPlatformBundle:Advert1');


        $listAdverts = $repository->getAdvertWithCategories(array ('Amar','GilbErt'));


        foreach ($listAdverts as $advert) {
            // $advert est une instance de Advert
            $advert->getContent();
            $advert->getAuthor();
            $advert->getTitle();
            $advert->getDate();
        }

        return $this->render('OCPlatformBundle:Advert:find2.html.twig', array(
            'listAdverts' =>$listAdverts));
    }


    /**
    *@Route("/limite")
    */
    public function limiteAction(){
        $repository = $this
            ->getDoctrine()
            ->getManager()
            ->getRepository('OCPlatformBundle:Advert1');

        $listAdverts = $repository->getApplicationsWithAdvert(10);

        foreach ($listAdverts as $advert) {
            // $advert est une instance de Advert
            $advert->getContent();
            $advert->getAuthor();
            $advert->getTitle();
        }

        return $this->render('OCPlatformBundle:Advert:find2.html.twig', array(
            'listAdverts' =>$listAdverts));
    }

    /**
     * @Route("/findby")
     */
    public function trouverAction()
    {
        $repository = $this
            ->getDoctrine()
            ->getManager()
            ->getRepository('OCPlatformBundle:Advert');

        $listAdverts = $repository->findAll();

        /*$listAdverts = $repository->findBy(
            array('author' => 'Alexandre'), // Critere
            array('date' => 'desc'),        // Tri
            5,                              // Limite
            0                               // Offset
        );*/

        foreach ($listAdverts as $advert) {
            // $advert est une instance de Advert
             $advert->getContent();
             $advert->getAuthor();
        }

        // Le render ne change pas, on passait avant un tableau, maintenant un objet
        return $this->render('OCPlatformBundle:Advert:find.html.twig', array(
             'listAdverts' =>$listAdverts
        ));
    }

    /**
     * @Route("/supprimer")
     */
    public function supprAction(Request $request)
    {
        $advertSuppr = new Advert();
        $advertSuppr -> setTitle('Recherche développeur Symfony3.');
        $advertSuppr->setAuthor('Amar');
        $advertSuppr->setContent("Nous recherchons un développeur Symfony2 débutant sur Lyon. Blabla…");
        $advertSuppr->setDate(new \DateTime());
        $em = $this->getDoctrine()->getManager();

        // Étape 1 : On « persiste » l'entité
        $em->persist($advertSuppr);

        // Étape 2 : On « flush » tout ce qui a été persisté avant
        $em->flush();
        $request->getSession()->getFlashBag()->add('notice', 'Annonce bien enregistrée.');
        return $this->render('::suppr.html.twig');
    }

    /**
     * @Route("/add", name="oc_platform_add")
     */
    public function addAction(Request $request)
    {
        // Création de l'entité
        $advert = new Advert();
        $advert->setTitle('Recherche développeur Symfony3.');
        $advert->setAuthor('Amar');
        $advert->setContent("Nous recherchons un développeur Symfony2 débutant sur Lyon. Blabla…");
        $advert->setDate(new \DateTime());
        // On peut ne pas définir ni la date ni la publication,
        // car ces attributs sont définis automatiquement dans le constructeur

        // On récupère l'EntityManager
        $em = $this->getDoctrine()->getManager();

        // Étape 1 : On « persiste » l'entité
        $em->persist($advert);

        // Étape 2 : On « flush » tout ce qui a été persisté avant
        $em->flush();

        // Reste de la méthode qu'on avait déjà écrit
        if ($request->isMethod('POST')) {
            $request->getSession()->getFlashBag()->add('notice', 'Annonce bien enregistrée.');
            return $this->redirect($this->generateUrl('oc_platform_view', array('id' => $advert->getId())));
        }

        // On récupère le service
        $antispam = $this->container->get('oc_platform.antispam');

        // Je pars du principe que $text contient le texte d'un message quelconque
        $text = '..aaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaa.';
        if ($antispam->isSpam($text)) {
            throw new \Exception('Votre message a été détecté comme spam !');
        }

        // Ici le message n'est pas un spam
        // Si on n'est pas en POST, alors on affiche le formulaire
        return $this->render('OCPlatformBundle:Advert:add.html.twig');
    }

    /**
     * @Route("/edit/{id}", name="oc_platform_edit")
     */
    public function editAction($id, Request $request)
    {
        // Ici, on récupérera l'annonce correspondante à $id

        // Même mécanisme que pour l'ajout
        if ($request->isMethod('POST')) {
            $request->getSession()->getFlashBag()->add('notice', 'Annonce bien modifiée.');

            return $this->redirectToRoute('oc_platform_view', array('id' => 5));
        }

        return $this->render('OCPlatformBundle:Advert:edit.html.twig');
    }

    /**
     * @Route("/delete/{id}", name="oc_platform_delete")
     */
    public function deleteAction($id)
    {
        // Ici, on récupérera l'annonce correspondant à $id

        // Ici, on gérera la suppression de l'annonce en question

        return $this->render('OCPlatformBundle:Advert:delete.html.twig');
    }

    public function menuAction($limit)
    {
        // On fixe en dur une liste ici, bien entendu par la suite
        // on la récupérera depuis la BDD !
        $listAdverts = array(
            array('id' => 2, 'title' => 'Recherche développeur Symfony2'),
            array('id' => 5, 'title' => 'Mission de webmaster'),
            array('id' => 9, 'title' => 'Offre de stage webdesigner')
        );

        return $this->render('OCPlatformBundle:Advert:menu.html.twig', array(
            // Tout l'intérêt est ici : le contrôleur passe
            // les variables nécessaires au template !
            'listAdverts' => $listAdverts
        ));
    }


}
