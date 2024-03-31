<?php

namespace App\Form;

use App\Constant\UserConstant;
use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\TelType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\File as FileConstraint;
use Symfony\Component\Validator\Constraints\Image;

class UserType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('email', EmailType::class, [
                'attr' => [
                    'placeholder' => 'mohamed@live.fr'
                ]
            ])
            ->add('type', ChoiceType::class, [
                    'choices' => [
//                        'Admin' => UserConstant::ROLE_ADMIN,
                        'Locataire' => UserConstant::ROLE_CLIENT,
                        'Propriétaire' => UserConstant::ROLE_OWNER,
                    ],
                    'label' => "Rôles",
                    'mapped' => false
                ]
            )
            ->add('phone', TelType::class, [
                'required' => false,
                'attr' => [
                    'placeholder' => '+22236111924'
                ]
            ])
//            ->add('plainPassword', RepeatedType::class, [
//                'mapped' => false,
//                'type' => PasswordType::class,
//                'invalid_message' => 'Les mots de passe doivent correspondre.',
//                'options' => ['attr' => ['class' => 'password-field']],
//                'required' => false,
//                'first_options'  => ['label' => 'Mot de passe', 'attr' => ['placeholder' => '*******']],
//                'second_options' => ['label' => 'Confirmer le mot de passe', 'attr' => ['placeholder' => '*******']],
//            ])
            ->add('firstName', TextType::class, [
                'label' => 'Prénom',
                'attr' => [
                    'placeholder' => 'Mohamed'
                ]
            ])
            ->add('lastName', TextType::class, [
                'label' => 'Nom',
                'attr' => [
                    'placeholder' => 'Mahmoud'
                ]
            ])
//            ->add('imageFile', FileType::class, [
//                'required' => false,
//                'label' => 'Avatar',
//                'mapped' => false,
//                'attr' => [
//                    'class' => 'form-control'
//                ],
//                'constraints' => [
//                    new Image(),
//                    new FileConstraint([
//                        'maxSize' => '2M',
//                        'maxSizeMessage' => 'Le fichier est trop volumineux. La taille maximale autorisée est de 2Mo'
//                    ])
//                ],
//            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
}
