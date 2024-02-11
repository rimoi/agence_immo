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
            ->add('email', EmailType::class)
            ->add('type', ChoiceType::class, [
                    'choices' => [
                        'Freelances' => UserConstant::ROLE_FREELANCE,
                        'Admin' => UserConstant::ROLE_ADMIN,
                        'Autres' => UserConstant::ROLE_CLIENT,
                    ],
                    'label' => "RôLE :",
                    'mapped' => false
                ]
            )
            ->add('phone', TelType::class, [
                'required' => false
            ])
            ->add('plainPassword', RepeatedType::class, [
                'mapped' => false,
                'type' => PasswordType::class,
                'invalid_message' => 'Les mots de passe doivent correspondre.',
                'options' => ['attr' => ['class' => 'password-field']],
                'required' => false,
                'first_options'  => ['label' => 'Changer de Mot de passe', 'attr' => ['placeholder' => '*******']],
                'second_options' => ['label' => 'Confirmer le changement de mot de passe', 'attr' => ['placeholder' => '*******']],
            ])
            ->add('firstName', TextType::class)
            ->add('lastName', TextType::class)
            ->add('imageFile', FileType::class, [
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
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
}
