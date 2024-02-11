<?php

namespace App\Controller\Admin;

use App\Constant\LinkingTypeConstant;
use App\Entity\Linking;
use App\Form\LinkingType;
use App\Repository\LinkingRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/admin/linking")
 */
class LinkingController extends AbstractController
{
    /**
     * @Route("/", name="admin_linking_index", methods={"GET"})
     */
    public function index(LinkingRepository $linkingRepository): Response
    {
        return $this->render('admin/linking/index.html.twig', [
            'linkings' => $linkingRepository->findAll(),
        ]);
    }

    /**
     * @Route("/new", name="admin_linking_new", methods={"GET","POST"})
     */
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $linking = new Linking();
        $form = $this->createForm(LinkingType::class, $linking);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            if ($linking->getScope() === LinkingTypeConstant::CONTACT) {
                $linking->setLink(null);
            } else {
                $linking->setPhone(null);
            }

            $entityManager->persist($linking);
            $entityManager->flush();

            $this->addFlash('success', 'Votre contact a bien été créé avec succès.');

            return $this->redirectToRoute('admin_linking_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('admin/linking/new.html.twig', [
            'linking' => $linking,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}/show", name="admin_linking_show", methods={"GET"})
     */
    public function show(Linking $linking): Response
    {
        return $this->render('admin/linking/show.html.twig', [
            'linking' => $linking,
        ]);
    }

    /**
     * @Route("/{id}/edit", name="admin_linking_edit", methods={"GET","POST"})
     */
    public function edit(Request $request, Linking $linking, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(LinkingType::class, $linking);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            if ($linking->getScope() === LinkingTypeConstant::CONTACT) {
                $linking->setLink(null);
            } else {
                $linking->setPhone(null);
            }

            $entityManager->flush();

            $this->addFlash('success', 'Votre contact a été modifié avec succès');

            return $this->redirectToRoute('admin_linking_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('admin/linking/edit.html.twig', [
            'linking' => $linking,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}/delete", name="admin_linking_delete", methods={"POST"})
     */
    public function delete(Request $request, Linking $linking, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$linking->getId(), $request->request->get('_token'))) {
            $entityManager->remove($linking);
            $entityManager->flush();


            $this->addFlash('success', 'Votre contact a été supprimé avec succès');
        }

        return $this->redirectToRoute('admin_linking_index', [], Response::HTTP_SEE_OTHER);
    }
}
