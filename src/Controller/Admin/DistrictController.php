<?php

namespace App\Controller\Admin;

use App\Entity\District;
use App\Form\DistrictType;
use App\Repository\DistrictRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/admin/district")
 */
class DistrictController extends AbstractController
{
    /**
     * @Route("/", name="admin_district_index", methods={"GET"})
     */
    public function index(DistrictRepository $districtRepository): Response
    {
        return $this->render('admin/district/index.html.twig', [
            'districts' => $districtRepository->findBy([], ['id' => 'DESC']),
        ]);
    }

    /**
     * @Route("/new", name="admin_district_new", methods={"GET","POST"})
     */
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $district = new District();
        $form = $this->createForm(DistrictType::class, $district);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($district);
            $entityManager->flush();

            return $this->redirectToRoute('admin_district_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('admin/district/new.html.twig', [
            'district' => $district,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="admin_district_show", methods={"GET"})
     */
    public function show(District $district): Response
    {
        return $this->render('admin/district/show.html.twig', [
            'district' => $district,
        ]);
    }

    /**
     * @Route("/{id}/edit", name="admin_district_edit", methods={"GET","POST"})
     */
    public function edit(Request $request, District $district, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(DistrictType::class, $district);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('admin_district_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('admin/district/edit.html.twig', [
            'district' => $district,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="admin_district_delete", methods={"POST"})
     */
    public function delete(Request $request, District $district, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$district->getId(), $request->request->get('_token'))) {
            $entityManager->remove($district);
            $entityManager->flush();
        }

        return $this->redirectToRoute('admin_district_index', [], Response::HTTP_SEE_OTHER);
    }
}
