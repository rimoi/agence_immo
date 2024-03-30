<?php

namespace App\Controller;

use App\Entity\Like;
use App\Entity\Mission;
use App\Entity\Tag;
use App\Repository\LikeRepository;
use App\Repository\MissionRepository;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class HomeController extends AbstractController
{
    /** @Route("/", name="home") */
    public function index(
        Request $request,
        MissionRepository $missionRepository,
        EntityManagerInterface $entityManager,
        PaginatorInterface $pagination
    ) {
        $qb = $missionRepository->getMissiosQueryBuilder(
            $request->query->all(),
            $request->getClientIp()
        );

        return $this->render('home/index.html.twig', [
            'missions' => $pagination->paginate(
                $qb,
                $request->get('page', 1),
                15
            ),
            'tags' => $entityManager->getRepository(Tag::class)->findAll()
        ]);
    }

    /** @Route("/like", name="app_like", methods={"POST"}) */
    public function like(
        Request $request,
        LikeRepository $likeRepository,
        EntityManagerInterface $entityManager
    ) {
        if (
            ($missionId = $request->request->get('missionId'))
            &&
            ($ip = $request->getClientIp())
        ) {

            if (
                $like = $likeRepository->findOneBy(['mission' => $missionId, 'ip' => $ip])
            ) {

                $entityManager->remove($like);
                $entityManager->flush();

                return $this->json(['success' => true, 'enable' => false]);
            } else {
                $like = new Like();
                $like->setIp($ip);
                $like->setMission(
                    $entityManager->getRepository(Mission::class)->find($missionId)
                );

                $entityManager->persist($like);
                $entityManager->flush();
            }

            return $this->json(['success' => true, 'enable' => true]);
        }

        return $this->json(['success' => false, 'enable' => false]);
    }
}
