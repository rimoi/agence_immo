<?php

namespace App\Form;

use App\Constant\DeviseConstant;
use App\Entity\Mission;
use App\Entity\Tag;
use App\Entity\User;
use App\Repository\TagRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\File as FileConstraint;
use Symfony\Component\Validator\Constraints\Image;

class MissionType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('title', TextType::class)
            ->add('content', TextareaType::class)
            ->add('price', NumberType::class, [
                'grouping' => true,
                'scale' => 0,
                'label' => 'Prix (*)',
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => '150'
                ],
            ])
            ->add('tags', EntityType::class, [
                'class' => Tag::class,
                'choice_label' => static function (Tag $choice) {
                    return $choice->getName();
                },
                'placeholder' => 'Merci de choisir une catégories',
                'label' => 'Catégories',
                'multiple' => true,
                'required' => true,
                'by_reference' => false,
                'attr' => [
                    'class' => 'js-select2',
                    'style' => "width: 100%",
                ],
            ])
            ->add('user', EntityType::class, [
                'class' => User::class,
                'choice_label' => static function (User $choice) {
                    return $choice->getEmail();
                },
                'label' => 'Utilisateur',
                'multiple' => false,
                'required' => true,
                'placeholder' => 'Merci de choisir un utilisateur',
                'attr' => [
                    'class' => 'js-select2',
                    'style' => "width: 100%",
                ],
            ])
            ->add('image', FileType::class, [
                'required' => false,
                'label' => false,
                'mapped' => false,
                'attr' => [
                    'class' => 'form-control'
                ],
                'constraints' => [
                    new Image(),
                    new FileConstraint([
                        'maxSize' => '2M',
                        'maxSizeMessage' => 'Le fichier est trop volumineux. La taille maximale autorisée est de 2Mo'
                    ])
                ],
            ])
            ->add('published', CheckboxType::class, [
                'required' => false
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Mission::class,
        ]);
    }
}
