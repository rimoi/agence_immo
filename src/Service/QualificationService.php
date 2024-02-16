<?php

namespace App\Service;

use App\Entity\File;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class QualificationService
{
    private  $entityManager;
    private  $fileUploader;

    public function __construct(
        EntityManagerInterface $entityManager,
        UploaderHelper $uploaderHelper
    ){
        $this->entityManager = $entityManager;
        $this->fileUploader = $uploaderHelper;
    }

    public function addElement(FormInterface $form, string $type): void
    {
        $user = $form->getData();

        $getter = 'get' . ucfirst($type);
        $setter = 'set' . ucfirst($type);

        if (
            $form->has($type)
            && $form->get($type)->getData()
            && ($file = $form->get($type)->get('name')->getData())
        ) {
            $filename = $this->fileUploader->uploadMissionImage($file);

            if ($user->$getter()) {
                $newFile = $user->$getter();
            } else {
                $newFile = new File();
                $this->entityManager->persist($newFile);
            }
            $newFile->setName($filename);
            $user->$setter($newFile);
        }
    }

    public function addExperience(FormInterface $form, string $type): void
    {
        $mission = $form->getData();

        foreach ($form->get($type) as $formExp) {

            $experience = $formExp->getData();


            if ( $formExp->has('file') && !$formExp->get('file')->getData()) {
                continue;
            }

            if (
                $formExp->has('file')
                && ($file = $formExp->get('file')->getData())
            ) {
                $newFile = null;
                if ($formExp->getData()->getId()) {

                    if (!($newFile = $formExp->getData()->getFile())) {
                        $newFile = $this->entityManager->getRepository(File::class)->find($formExp->getData()->getId());
                    }
                }

                $filename = $this->fileUploader->uploadMissionImage($file,
                    $newFile ? $newFile->getName() : null
                );

                if (!$formExp->getData()->getId()) {
                    $newFile = new File();
                    $this->entityManager->persist($newFile);
                }
                $newFile->setName($filename);
                $experience->setFile($newFile);
            }

            $getter = ucfirst(substr($type,0,-1));

            $mission->{'add' . $getter}($experience);
            $experience->setMission($mission);
        }

    }

    public function getTargetDirectory()
    {
        return $this->targetDirectory;
    }
}
