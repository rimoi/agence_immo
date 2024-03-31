<?php

namespace App\Controller\Admin;

use App\Entity\Reservation;
use App\Form\ReservationType;
use App\Repository\ReservationRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/admin/reservation", name="admin_reservation_")
 */
class ReservationController extends AbstractController
{
    /**
     * @Route("/", name="index", methods={"GET"})
     */
    public function index(Request $request, ReservationRepository $reservationRepository): Response
    {
        return $this->render('admin/reservation/index.html.twig', [
            'reservations' => $reservationRepository->searchByName($request->get('search')),
        ]);
    }

    /**
     * @Route("/new", name="new", methods={"GET", "POST"})
     */
    public function new(Request $request, ReservationRepository $reservationRepository): Response
    {
        $reservation = new Reservation();
        $form = $this->createForm(ReservationType::class, $reservation);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {


            if ($dateDebut = $form->get('debut')->getData()) {
                $dateDebut = str_replace('/', '-', $dateDebut);
                $reservation->setStartAt(new \DateTime($dateDebut, new \DateTimeZone('Europe/Paris')));
            }
            if ($dateFin = $form->get('fin')->getData()) {
                $dateFin = str_replace('/', '-', $dateFin);
                $reservation->setEndAt(new \DateTime($dateFin, new \DateTimeZone('Europe/Paris')));
            }
            if ($reservation->getEndAt() <= $reservation->getStartAt()) {
                $form->get('fin')->addError(new FormError('La date de fin ne peux pas être avant la date de debut !'));

                return $this->render('admin/reservation/new.html.twig', [
                    'reservation' => $reservation,
                    'form' => $form->createView(),
                ]);
            }

            $reservationRepository->add($reservation);
            return $this->redirectToRoute('admin_reservation_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('admin/reservation/new.html.twig', [
            'reservation' => $reservation,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}/show", name="show", methods={"GET"})
     */
    public function show(Reservation $reservation): Response
    {
        return $this->render('admin/reservation/show.html.twig', [
            'reservation' => $reservation,
        ]);
    }

    /**
     * @Route("/{id}/edit", name="edit", methods={"GET", "POST"})
     */
    public function edit(Request $request, Reservation $reservation, ReservationRepository $reservationRepository): Response
    {
        $form = $this->createForm(ReservationType::class, $reservation);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            if ($dateDebut = $form->get('debut')->getData()) {
                $dateDebut = str_replace('/', '-', $dateDebut);
                $reservation->setStartAt(new \DateTime($dateDebut, new \DateTimeZone('Europe/Paris')));
            }
            if ($dateFin = $form->get('fin')->getData()) {
                $dateFin = str_replace('/', '-', $dateFin);
                $reservation->setEndAt(new \DateTime($dateFin, new \DateTimeZone('Europe/Paris')));
            }
            if ($reservation->getEndAt() <= $reservation->getStartAt()) {
                $form->get('fin')->addError(new FormError('La date de fin ne peux pas être avant la date de debut !'));

                return $this->render('admin/reservation/edit.html.twig', [
                    'reservation' => $reservation,
                    'form' => $form->createView(),
                ]);
            }

            $reservationRepository->add($reservation);
            return $this->redirectToRoute('admin_reservation_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('admin/reservation/edit.html.twig', [
            'reservation' => $reservation,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="delete", methods={"POST"})
     */
    public function delete(Request $request, Reservation $reservation, ReservationRepository $reservationRepository): Response
    {
        if ($this->isCsrfTokenValid('delete'.$reservation->getId(), $request->request->get('_token'))) {
            $reservationRepository->remove($reservation);
        }

        return $this->redirectToRoute('admin_reservation_index', [], Response::HTTP_SEE_OTHER);
    }
}
