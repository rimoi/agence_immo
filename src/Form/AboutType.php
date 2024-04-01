<?php

namespace App\Form;

use App\Entity\About;
use FOS\CKEditorBundle\Form\Type\CKEditorType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;

class AboutType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', TextType::class, [
                'attr' => ['placeholder' => 'Page Apropos']
            ])
            ->add('description', CKEditorType::class, [
                'label' => 'Description',
                'config' => [
                    'uiColor' => '#ffffff',
                    'editorplaceholder' => 'Petite description sur la page a propos...',
                    'defaultLanguage' => 'fr',
                    //...
                ],
                'constraints'=>[
                    new NotBlank([
                        'message' => 'Le contenu ne peut pas être vide',
                    ]),
                    new Length([
                        'min' => 25,
                        'max' => 15000,
                        'minMessage' =>  'La description doit comporter au moins {{ limit }} caractères !',
                        'maxMessage' => 'La description doit comporter au plus {{ limit }} caractères !',
                    ]),
                ]
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => About::class,
        ]);
    }
}
