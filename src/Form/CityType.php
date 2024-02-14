<?php

namespace App\Form;

use App\Entity\City;
use App\Entity\Country;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CityType extends AbstractType
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
        $city = $options['data'];

        $builder
            ->add('name', TextType::class, [
                'label' => 'Nom ville',
                'attr' => [
                    'placeholder' => 'Zouératte'
                ]
            ])
            ->add('country', ChoiceType::class, [
                'choice_label' => function ($entity) {
                    return $entity->getName();
                },
                'choice_value' => function ($item) {
                    return is_object($item) ? $item->getId() : $item;
                },
                'label' => 'Pays',
                'by_reference' => false,
                'choices' => $this->entityManager->getRepository(Country::class)->findAll(),
                'attr' => [
                    'placeholder' => 'Veuillez choisir un pays...',
                    'class' => 'js-select2',
                    'style' => "width: 100%",
                ],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => City::class,
            'country' => null,
        ]);
    }
}
