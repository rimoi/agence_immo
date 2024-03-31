<?php

namespace App\Controller;

use App\Entity\City;
use App\Entity\District;
use App\Entity\Like;
use App\Entity\Mission;
use App\Entity\Tag;
use App\helper\ArrayHelper;
use App\Repository\LikeRepository;
use App\Repository\MissionRepository;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
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


        $cityName = '';
        if ($city = $request->get('city')) {
            $city = $entityManager->getRepository(City::class)->find($city);
            $cityName = $city->getName();
        }

        $districtName = '';
        if ($district = $request->get('district')) {
            $district = $entityManager->getRepository(District::class)->find($district);
            $districtName = $district->getName();
        }

        return $this->render('home/index.html.twig', [
            'missions' => $pagination->paginate(
                $qb,
                $request->get('page', 1),
                15
            ),
            'tags' => $entityManager->getRepository(Tag::class)->findAll(),
            'city_name' => $cityName,
            'district_name' => $districtName
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

    /** @Route("/city/all", name="home_city") */
    public function city(Request $request, EntityManagerInterface $entityManager): Response
    {
        $cities = $entityManager->getRepository(City::class)->getCityHaveAd();

        $cities = ArrayHelper::createAssociativeArray($cities, 'id', 'name');

        return $this->json($cities);
    }

    /** @Route("/district/all", name="home_district") */
    public function district(Request $request, EntityManagerInterface $entityManager): Response
    {
        $city = $request->get('cityId');

        $district = $entityManager->getRepository(District::class)->findBy(['city' => $city]);

        $district = ArrayHelper::createAssociativeArray($district, 'id', 'name');

        return $this->json($district);
    }
}
