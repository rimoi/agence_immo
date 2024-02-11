<?php

namespace App\Controller;

use App\Entity\Conversation;
use App\Entity\Message;
use App\Entity\Mission;
use App\Entity\User;
use App\Form\ChatMessageType;
use App\Form\MessageType;
use App\Repository\ConversationRepository;
use App\Repository\MessageRepository;
use App\Repository\UserRepository;
use App\Service\NotificationService;
use App\Service\UploaderHelper;
use Doctrine\ORM\EntityManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/conversations")
 * @IsGranted("ROLE_USER")
 */
class ConversationsController extends AbstractController
{
    /**
     * @Route("/", name="conversations")
     */
    public function index(ConversationRepository $conversationsRepository): Response
    {
        return $this->render('conversations/index.html.twig', [
            'conversation' => null,
            'conversations' => $conversationsRepository->findByParticipation($this->getUser()),
        ]);
    }

    /**
     * @Route("/{id}", name="conversations_show")
     */
    public function chat(
        Conversation $conversation,
        ConversationRepository $conversationsRepository,
        Request $request,
        MessageRepository $messageRepository,
        EntityManagerInterface $entityManager,
        UploaderHelper $uploaderHelper
    ): Response {
        /** @var User */
        $user = $this->getUser();

        //*verification des participants
        $participants = [$conversation->getUser1(), $conversation->getUser2()];

        if (!in_array($user, $participants)) {

            return $this->redirectToRoute('conversations', [], Response::HTTP_SEE_OTHER);
        }

        if ($conversation->getLastMessage()->getTarget()->getId() === $user->getId() && !$conversation->getLastMessage()->getSeen()) {

            $message = $messageRepository->find($conversation->getLastMessage()->getId());
            $message->setSeen(true);
            $entityManager->flush();

        }

        $messages = $messageRepository->findBy([
            'conversation' => $conversation
        ], ['id' => 'ASC']);

        $usersconversations = $conversationsRepository->findByParticipation($user);

        // Test de message
        $message = new Message();
        $form = $this->createForm(ChatMessageType::class, $message);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            if ($user->getId() == $conversation->getUser1()->getId()) {
                $destinataire = $conversation->getUser2();
            } else {
                $destinataire = $conversation->getUser1();
            }

            /** @var UploadedFile $uploadedFile */
            $uploadedFile = $form->get('messageFile')->getData();

            if ($uploadedFile) {
                $newFilename = $uploaderHelper->uploadMessageImage($uploadedFile);
                $message->setFile($newFilename);
            }

            $message->setConversation($conversation);
            $message->setOwner($user);
            $message->setTarget($destinataire);
            $message->setSeen(false);

            $conversation->setSender($this->getUser());
            $conversation->setLastMessage($message);

            $entityManager->persist($message);

            $entityManager->flush();


            return $this->redirectToRoute('conversations_show', [
                'id' => $conversation->getId()
            ], Response::HTTP_SEE_OTHER);
        }


        return $this->render('conversations/chat.html.twig', [
            'conversation' => $conversation,
            'conversations' => $usersconversations,
            'messages' => $messages,
            'mission' => $conversation->getMission(),
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/nouveau-message/{slug}", name="conversations_new")
     */
    public function new(
        Request $request,
        User $seller,
        EntityManagerInterface $entityManager,
        ConversationRepository $conversationsRepository
    ): Response {


        $user = $this->getUser();

        $findconversation = $conversationsRepository->findOneBy([
            'user1' => $user,
            'user2' => $seller
        ]);

        if ($findconversation) {
            return $this->redirectToRoute('conversations_show', [
                'id' => $findconversation->getId()
            ], Response::HTTP_SEE_OTHER);
        }

        $conversation = new Conversation();

        $message = new Message();
        $messageParDefaut = "Bonjour, je suis intéressé par votre service. Avant de passer commande, êtes-vous disponible pour me renseigner ?";

        $message->setContent($messageParDefaut);
        $form = $this->createForm(MessageType::class, $message);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $conversation->setUser1($user);
            $conversation->setUser2($seller);
            $conversation->setSender($user);
            $conversation->setTerminate(false);
            $entityManager->persist($conversation);

            $message->setConversation($conversation);
            $message->setOwner($user);
            $message->setTarget($seller);
            $message->setSeen(false);
            $entityManager->persist($message);

            $entityManager->flush();

            $conversation->setLastMessage($message);

            $entityManager->flush();

            return $this->redirectToRoute('conversations_show', [
                'id' => $conversation->getId()
            ], Response::HTTP_SEE_OTHER);
        }

        return $this->render('conversations/new.html.twig', [
            'form' => $form->createView(),
            'vendeur' => $seller,
            'mission' => false
        ]);
    }

    /**
     * @Route("/mission/chat/{slug}/{missionSlug}", name="mission_conversations")
     * @ParamConverter("mission", options={"mapping": {"missionSlug": "slug"}})
     */
    public function addWithmicroservice(
        Request $request,
        User $user,
        Mission $mission,
        EntityManagerInterface $entityManager,
        ConversationRepository $conversationsRepository,
        NotificationService $notificationService
    ): Response {

        $findconversation = $conversationsRepository->findOneBy([
            'user1' => $this->getUser(),
            'user2' => $user,
            'mission' => $mission
        ]);

        if ($findconversation) {
            return $this->redirectToRoute('conversations_show', [
                'id' => $findconversation->getId()
            ], Response::HTTP_SEE_OTHER);
        }

        $conversation = new Conversation();

        $message = new Message();

        $messageParDefaut = "Bonjour, je suis intéressé par votre service. Avant de passer commande, êtes-vous disponible pour me renseigner ?";

        $message->setContent($messageParDefaut);

        $form = $this->createForm(MessageType::class, $message);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $conversation->setUser1($this->getUser());
            $conversation->setUser2($user);
            $conversation->setTerminate(false);
            $conversation->setMission($mission);
            $entityManager->persist($conversation);

            $message->setConversation($conversation);
            $message->setOwner($this->getUser());
            $message->setTarget($user);
            $message->setSeen(false);
            $entityManager->persist($message);

            $entityManager->flush();

            $conversation->setLastMessage($message);

            $entityManager->flush();

            $notificationService->newContactReceived($conversation);

            return $this->redirectToRoute('conversations_show', [
                'id' => $conversation->getId()
            ], Response::HTTP_SEE_OTHER);
        }

        return $this->render('conversations/new.html.twig', [
            'form' => $form->createView(),
            'vendeur' => $user,
            'mission' => $mission
        ]);
    }

    /**
     * @Route("/{id}/terminated", name="conversations_terminee", methods={"POST"})
     */
    public function terminated(Request $request, Conversation $conversation, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('terminee' . $conversation->getId(), $request->request->get('_token'))) {
            $conversation->setTerminate(true);
            $entityManager->persist($conversation);
            $entityManager->flush();
        }

        return $this->redirectToRoute('conversations', [], Response::HTTP_SEE_OTHER);
    }
}
