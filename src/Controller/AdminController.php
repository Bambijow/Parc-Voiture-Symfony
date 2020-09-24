<?php

namespace App\Controller;

use App\Entity\RechercheVoiture;
use App\Entity\Voiture;
use App\Form\RechercheVoitureType;
use App\Form\VoitureType;
use App\Repository\VoitureRepository;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class AdminController extends AbstractController
{
    /**
     * @Route("/admin", name="admin")
     */
    public function index(VoitureRepository $repo, PaginatorInterface $paginator, Request $request)
    {
        $rechercheVoiture = new RechercheVoiture();
        $form = $this->createForm(RechercheVoitureType::class, $rechercheVoiture);
        $form->handleRequest($request);



        $voitures = $paginator->paginate(
            $repo->paginationFindAll($rechercheVoiture),
            $request->query->getInt('page', 1),
            6
        );
        return $this->render('voiture/voitures.html.twig', [
            "voitures" => $voitures,
            "form" => $form->createView(),
            "admin" => true
        ]);
    }

    /**
     * @Route("/admin/creation", name="creationVoiture")
     * @Route("/admin/{id}", name="modifVoiture", methods="POST|GET")
     */
    public function modification(Voiture $voiture = null, Request $request, EntityManagerInterface $manager)
    {
        if(!$voiture) $voiture = new Voiture();
        $form = $this->createForm(VoitureType::class, $voiture);
        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()){
            $manager->persist($voiture);
            $manager->flush();
            $this->addFlash("success", "L'action a été effectuée avec succès.");
            return $this->redirectToRoute("admin");
        }

        return $this->render('admin/modification.html.twig', [
            "voiture" => $voiture,
            "form" => $form->createView()
        ]);
    }

    /**
     * @Route("/admin/{id}", name="deleteVoiture", methods="SUP")
     */
    public function suppression(Voiture $voiture = null, Request $request, EntityManagerInterface $manager)
    {
        if($this->isCsrfTokenValid("SUP".$voiture->getId(), $request->get('_token'))){
            $manager->remove($voiture);
            $manager->flush();
            $this->addFlash("success", "L'action a été effectuée avec succès.");
            return $this->redirectToRoute("admin");
        }
    }
}
