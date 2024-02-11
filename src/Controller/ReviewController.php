<?php

namespace App\Controller;

use App\Entity\Mission;
use App\Entity\Review;
use App\Form\ReviewType;
use Doctrine\ORM\EntityManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/review", name="review_")
 * @IsGranted("ROLE_USER")
 */
class ReviewController extends AbstractController
{
    /**
     * @Route("/{slug}/new", name="new")
     */
    public function new(Request $request, Mission $mission, EntityManagerInterface $entityManager): Response
    {
        $reviewAlreadyExist = $entityManager->getRepository(Review::class)->findOneBy([
            'user' => $this->getUser(),
            'mission' => $mission
        ]);

        if ($reviewAlreadyExist) {
            $this->addFlash('danger', 'Vous avez déjà un avis publié pour cette mission');

            return $this->redirectToRoute('mission_show', ['slug' => $mission->getSlug()]);
        }

        $review = new Review();
        $form = $this->createForm(ReviewType::class, $review);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $this->addFlash('success', 'Votre avis a bien été publié !');

            $review->setMission($mission);
            $review->setUser($this->getUser());

            $entityManager->persist($review);
            $entityManager->flush();

            return $this->redirectToRoute('mission_show', ['slug' => $mission->getSlug()]);
        }

        return $this->render('review/new.html.twig', [
            'form' => $form->createView(),
            'mission' => $mission
        ]);
    }
}
