<?php

namespace App\Form;

use App\Entity\City;
use App\Entity\Country;
use App\Entity\District;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class DistrictType extends AbstractType
{
    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name', TextType::class, [
                'label' => 'Nom Quartier',
                'attr' => [
                    'placeholder' => 'Draguage'
                ]
            ])
            ->add('city', ChoiceType::class, [
                'choice_label' => function ($entity) {
                    return $entity->getName();
                },
                'choice_value' => function ($item) {
                    return is_object($item) ? $item->getId() : $item;
                },
                'label' => 'Ville',
                'required' => false,
                'by_reference' => false,
                'choices' => $this->entityManager->getRepository(City::class)->findAll(),
                'attr' => [
                    'placeholder' => 'Veuillez choisir un pays...',
                    'class' => 'js-select2',
                    'style' => "width: 100%",
                ],
            ])
        ;

        $formModifier = function (FormInterface $form, ?City $city) {

            $form->add('country', ChoiceType::class, [
                'choice_label' => function ($entity) {
                    return $entity->getName();
                },
                'choice_value' => function ($item) {
                    return is_object($item) ? $item->getId() : $item;
                },
                'label' => 'Pays',
                'by_reference' => false,
                'disabled' => !$city,
                'required' => true,
                'choices' => $city ? [$city->getCountry()] : [],
                'attr' => [
                    'placeholder' => 'Veuillez choisir un pays...',
                    'class' => 'js-select2',
                    'style' => "width: 100%",
                ],
            ]);
        };

        $builder->addEventListener(
            FormEvents::PRE_SET_DATA,
            function (FormEvent $event) use ($formModifier) {
                $data = $event->getData();

                $formModifier($event->getForm(), $data->getCity());
            }
        );

        $builder->get('city')->addEventListener(
            FormEvents::POST_SUBMIT,
            function (FormEvent $event) use ($formModifier) {
                // It's important here to fetch $event->getForm()->getData(), as
                // $event->getData() will get you the client data (that is, the ID)
                $city = $event->getForm()->getData();

                // since we've added the listener to the child, we'll have to pass on
                // the parent to the callback function!
                $formModifier($event->getForm()->getParent(), $city);
            }
        );
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => District::class,
        ]);
    }
}
