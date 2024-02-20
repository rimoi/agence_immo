<?php

namespace App\Form;

use App\Constant\LinkingTypeConstant;
use App\Entity\Linking;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class LinkingType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        /** @var ?Linking */
        $data = $options['data'];

        $builder
            ->add('label', TextType::class, [
                'label' => 'Nom',
                'attr' => [
                    'placeholder' => 'Whatsapp, Portable, ...',
                    'class' => 'mb-4 form-control'
                ]
            ])
            ->add('classe', TextType::class, [
                'label' => 'Référence font-aweosome',
                'attr' => [
                    'placeholder' => 'fas fa-whatsapp',
                ],
                'help' => 'Pour accéder à la liste complète des classes disponibles, veuillez vous rendre sur le site : <a href="https://fontawesome.com/search?o=r&m=free">Font-Awesome</a>',
                'help_html' => true
            ])
            ->add('scope', ChoiceType::class, [
                    'choices' => [
                        '' => '',
                        'Lien interne' => LinkingTypeConstant::CONTACT,
                        'Lien externe' => LinkingTypeConstant::FLUX_RSS,
                    ],
                    'label' => 'Scope',
                    'help' => "Ce champ permet de distinguer les contacts utilisés pour la page d'annonce de ceux utilisés dans le pied de page pour les liens externes tels que LinkedIn et Facebook.",
                    'data' => $data ? $data->getScope() : null,
                ]
            )
            ->add('visibility', CheckboxType::class, [
                'required' => false,
                'label' => "👁 Décochez cette case pour rendre ce contact invisible sur le site.",
                'label_attr' => ['class' => 'switch-custom my-3 pl-2'],
            ])
        ;

        $formModifier = function (FormInterface $form, ?string $type) {

            $form->add('phone', TextType::class, [
                'label' => 'Numéro',
                'required' => true,
                'disabled' => $type !== LinkingTypeConstant::CONTACT,
                'attr' => [
                    'placeholder' => '+22236111924',
                    'class' => 'mb-4 form-control',
                ],
                'label_attr' => ['class' => 'mt-2'],
            ]);

            $form->add('link', TextType::class, [
                'label' => 'Lien',
                'required' => true,
                'disabled' => $type !== LinkingTypeConstant::FLUX_RSS,
                'attr' => [
                    'placeholder' => 'https://www.linkedin.com/in/sidi-lekhalifa-63b628ba/',
                    'class' => 'mb-4 form-control',
                ],
                'label_attr' => ['class' => 'mt-2'],
            ]);
        };

        $builder->addEventListener(
            FormEvents::PRE_SET_DATA,
            function (FormEvent $event) use ($formModifier) {
                $data = $event->getData();

                $formModifier($event->getForm(), $data->getScope());
            }
        );

        $builder->get('scope')->addEventListener(
            FormEvents::POST_SUBMIT,
            function (FormEvent $event) use ($formModifier) {
                // It's important here to fetch $event->getForm()->getData(), as
                // $event->getData() will get you the client data (that is, the ID)
                $scope = $event->getForm()->getData();

                // since we've added the listener to the child, we'll have to pass on
                // the parent to the callback function!
                $formModifier($event->getForm()->getParent(), $scope);
            }
        );
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Linking::class,
        ]);
    }
}
