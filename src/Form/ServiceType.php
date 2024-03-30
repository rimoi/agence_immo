<?php

namespace App\Form;

use App\Constant\DeviseConstant;
use App\DTO\CityDTO;
use App\DTO\CountryDTO;
use App\Entity\City;
use App\Entity\Country;
use App\Entity\District;
use App\Entity\Mission;
use App\Entity\Tag;
use Doctrine\ORM\EntityManagerInterface;
use FOS\CKEditorBundle\Form\Type\CKEditorType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Constraints\GreaterThan;
use Symfony\Component\Validator\Constraints\Image;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Validator\Constraints\NotNull;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class ServiceType extends AbstractType
{
    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    public function __construct(
        EntityManagerInterface $entityManager
    )
    {
        $this->entityManager = $entityManager;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('title', TextType::class, [
                'attr' => [
                    'class'=>'form-control'
                ]
            ])
            ->add('content', CKEditorType::class, [
                'label' => 'false',
                'config' => [
                    'uiColor' => '#ffffff',
                    'editorplaceholder' => 'Petite description sur la mission...',
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
            ->add('isFurnished', ChoiceType::class, [
                'label' => false,
                'choices' => [
                    '' => '',
                    'Meublé' => true,
                    'Non meublé' => false,
                ],
                'required' => true,
                'attr' => ['class' => 'border-0']
            ])
            ->add('surface', NumberType::class, [
                'label' => false,
                'constraints' => [
                    new NotBlank([
                        'message' => 'Le champ surface est réquis',
                    ]),
                    new GreaterThan([
                        'value' => 5,
                        'message' => 'La surface doit être supérieur strictement à "5 m²"'
                    ])
                ],
                'attr' => ['class' => 'border-0']
            ])
            ->add('nbRoom', IntegerType::class, [
                'label' => false,
                'required' => false,
                'attr' => ['class' => 'border-0']
            ])
            ->add('nbPiece', IntegerType::class, [
                'label' => false,
                'required' => false,
                'attr' => ['class' => 'border-0']
            ])
            ->add('nbSalleBain', IntegerType::class, [
                'label' => false,
                'required' => false,
                'attr' => ['class' => 'border-0']
            ])
            ->add('price', NumberType::class, [
                'label' => false,
                'constraints' => [
                    new NotBlank([
                        'message' => 'Le champ prix est réquis',
                    ]),
                    new GreaterThan([
                        'value' => 5,
                        'message' => 'Le prix doit être supérieur strictement à "5 €"'
                    ])
                ],
                'attr' => ['class' => 'border-0']
            ])
            ->add('tag', EntityType::class, [
                'class' => Tag::class,
                'choice_label' => 'name',
                'label' => 'Catégorie',
                'multiple' => false,
                'attr' => [
                    'placeholder' => 'Veuillez choisir une catégorie...',
                    'class' => 'js-select2',
                    'style' => "width: 90%",
                ],
            ])
            ->add('published', CheckboxType::class, [
                'required' => false,
                'label' => "📢 Cocher cette case pour publier l'annonce ?",
                'label_attr' => ['class' => 'switch-custom'],
            ])
            ->add('city', ChoiceType::class, [
                'choice_label' => function ($item) {
                    return is_object($item) ? sprintf('%s ( %s )', $item->getName(), $item->getCountry()->getName()) : $item;
                },
                'choice_value' => function ($item) {
                    return is_object($item) ? $item->getId() : $item;
                },
                'required' => true,
                'placeholder' => 'Choisir une ville...',
                'choices' => $this->entityManager->getRepository(City::class)->findBy([], ['name' => 'asc']),
            ])
            ->add('images', CollectionType::class, [
                 'label' => false,
                 'required' => false,
                 'entry_type' => ImageType::class,
                 'allow_add' => true,
                 'prototype' => true,
                'by_reference' => false,
                'entry_options' => [
                     'attr' => ['class' => 'row', 'label' => false],
                 ],
                 'allow_delete' => true,
            ])
            ->add('devise', ChoiceType::class, [
                'choices' => DeviseConstant::MAP,
            ])
        ;


        $formModifier = function (FormInterface $form, ?City $city = null) {

            $form->add('district', ChoiceType::class, [
                'choice_label' => 'name',
                'required' => true,
                'choice_value' => function ($item) {
                    return is_object($item) ? $item->getId() : $item;
                },
                'placeholder' => 'Choisir une quartier...',
                'choices' => $this->getDistricts($city),
            ]);

        };

        $builder->addEventListener(
            FormEvents::PRE_SET_DATA,
            function (FormEvent $event) use ($formModifier) {
                // this would be your entity, i.e. SportMeetup
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
            'data_class' => Mission::class,
        ]);
    }

    private function getDistricts(?City $city)
    {
        if ($city) {
            return $this->entityManager->getRepository(District::class)->findBy(['city' => $city]);
        }

        return [];
    }
}
