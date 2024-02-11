<?php

namespace App\Form;

use App\Constant\UserConstant;
use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\TelType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\File as FileConstraint;
use Symfony\Component\Validator\Constraints\Image;
use Symfony\Component\Validator\Constraints\IsTrue;
use Symfony\Component\Validator\Constraints\Regex;
use Symfony\Component\Form\Extension\Core\Type\FileType;
class RegistrationFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('status', ChoiceType::class, [
                    'choices' => [
                        'Freelances' => UserConstant::ROLE_FREELANCE,
                        'Autres' => UserConstant::ROLE_CLIENT,
                    ],
                    'label' => "S'incrire en tant que (*) :",
                    'mapped' => false
                ]
            )
            ->add('email', EmailType::class, [
                'label' => 'E-mail (*)',
                'attr' => [
                    'placeholder' => 'jean.dupond@gmail.com'
                ]
            ])
            ->add('phone', TelType::class, [
                'label' => "Numéro de téléphone",
                'required' => false,
                'attr' => [
                    'placeholder' => '0606060606'
                ],
                'constraints' => [
                    new Regex('/0\d{9}/')
                ],
            ])
            ->add('firstname', TextType::class, [
                'label' => 'Prénom',
                'required' => true,
                'attr' => [
                    'placeholder' => 'Jean'
                ]
            ])
            ->add('lastname', TextType::class, [
                'label' => 'Nom',
                'required' => true,
                'attr' => [
                    'placeholder' => 'Dupond'
                ]
            ])
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
            ->add('plainPassword', RepeatedType::class, [
                'mapped' => false,
                'type' => PasswordType::class,
                'invalid_message' => 'Les mots de passe doivent correspondre.',
                'options' => ['attr' => ['class' => 'password-field']],
                'required' => true,
                'first_options'  => ['label' => 'Mot de passe (*)', 'attr' => ['placeholder' => '*******']],
                'second_options' => ['label' => 'Confirmer le mot de passe (*)', 'attr' => ['placeholder' => '*******']],
            ])
            ->add('agreeTerms', CheckboxType::class, [
                'label_attr' => ['class' => 'switch-custom'],
                'label'    => "J'ai lu, compris et accepté les politiques et conditions du site",
                'mapped' => false,
                'constraints' => [
                    new IsTrue([
                        'message' => 'Vous devez acceptez les CGU du site.',
                    ]),
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
