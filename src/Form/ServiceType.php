<?php

namespace App\Form;

use App\DTO\CityDTO;
use App\DTO\CountryDTO;
use App\Entity\City;
use App\Entity\Country;
use App\Entity\Mission;
use FOS\CKEditorBundle\Form\Type\CKEditorType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Constraints\File as FileConstraint;
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
     * @var HttpClientInterface
     */
    private $client;
    /**
     * @var ParameterBagInterface
     */
    private $parameterBag;
    /**
     * @var SerializerInterface
     */
    private $serializer;

    public function __construct(
        HttpClientInterface $client,
        ParameterBagInterface $parameterBag,
        SerializerInterface $serializer
    )
    {
        $this->client = $client;
        $this->parameterBag = $parameterBag;
        $this->serializer = $serializer;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {

        /** @var Mission|null $mission */
        $mission = $options['data'] ?? null;
        $isEdit = $mission && $mission->getId();

        $imageConstraints = [
            new Image([
                'maxSize' => '2M',
                'maxSizeMessage' => 'Le fichier est trop volumineux. La taille maximale autorisée est de 2Mo'
            ])
        ];

        if (!$isEdit || !$mission->getImageFile()) {
            $imageConstraints[] = new NotNull([
                'message' => 'Merci de charger une image.$',
            ]);
        }

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
                        'max' => 1000,
                        'minMessage' =>  'La description doit comporter au moins {{ limit }} caractères !',
                        'maxMessage' => 'La description doit comporter au plus {{ limit }} caractères !',
                    ]),
                ]
            ])
            ->add('price', IntegerType::class, [
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
            ->add('image', FileType::class, [
                'required' => false,
                'label' => false,
                'mapped' => false,
                'attr' => [
                    'class' => 'form-control'
                ],
                'constraints' => $imageConstraints,
            ])
        ;

        if (!$isEdit) {

            $builder->add('country', ChoiceType::class, [
                'choices' => $this->listOfCountries(),
                'choice_label' => 'name',
                'required' => false,
                'label' => 'Pays',
                'placeholder' => 'Choisir un pays...',
            ]);

            $formModifier = function (FormInterface $form, ?Country $country) {

                $form->add('city', ChoiceType::class, [
                    'choice_label' => 'name',
                    'required' => false,
                    'placeholder' => 'Choisir une ville...',
                    'choices' => $this->getCitiesByCountry($country),
                ]);
            };

            $builder->addEventListener(
                FormEvents::PRE_SET_DATA,
                function (FormEvent $event) use ($formModifier) {
                    // this would be your entity, i.e. SportMeetup
                    $data = $event->getData();

                    $formModifier($event->getForm(), $data->getCountry());
                }
            );

            $builder->get('country')->addEventListener(
                FormEvents::POST_SUBMIT,
                function (FormEvent $event) use ($formModifier) {
                    // It's important here to fetch $event->getForm()->getData(), as
                    // $event->getData() will get you the client data (that is, the ID)
                    $country = $event->getForm()->getData();

                    // since we've added the listener to the child, we'll have to pass on
                    // the parent to the callback function!
                    $formModifier($event->getForm()->getParent(), $country);
                }
            );
        }
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Mission::class,
        ]);
    }

    /** @return Country[] */
    private function listOfCountries()
    {
        $request = $this->client->request('GET', 'https://api.countrystatecity.in/v1/countries', [
            'headers' => [
                'X-CSCAPI-KEY' => $this->parameterBag->get('token_api_country'),
            ]
        ]);

        if ($request->getStatusCode() !== Response::HTTP_OK) {
            throw new \Exception('An error occurs while communicating (Methode GET getResults) with API countrystatecity');
        }
        /** @var CountryDTO[] $countryDTOs */
        $countryDTOs = $this->serializer->deserialize($request->getContent(), CountryDTO::class . '[]', 'json');

        $countries = [];

        foreach ($countryDTOs as $countryDTO) {
            $country = new Country();
            $country->setName($countryDTO->getName());
            $country->setIso2($countryDTO->getIso2());
            $country->setReference($countryDTO->getId());

            $countries[] = $country;
        }

        return $countries;
    }

    /** @return City[] */
    private function getCitiesByCountry(?Country $country)
    {
        if (!$country) {
            return [];
        }

        $url = sprintf('https://api.countrystatecity.in/v1/countries/%s/cities', $country->getIso2());

        $request = $this->client->request('GET', $url, [
            'headers' => [
                'X-CSCAPI-KEY' => $this->parameterBag->get('token_api_country'),
            ]
        ]);

        if ($request->getStatusCode() !== Response::HTTP_OK) {
            throw new \Exception('An error occurs while communicating (Methode GET getResults) with API getCitiesByCountry');
        }

        /** @var CityDTO[] $citiesDTO */
        $citiesDTO = $this->serializer->deserialize($request->getContent(), CityDTO::class . '[]', 'json');

        $cities = [];

        foreach ($citiesDTO as $cityDTO) {
            $city = new City();
            $city->setName($cityDTO->getName());
            $city->setReference($cityDTO->getId());
            $city->setCountry($country);

            $cities[] = $city;
        }

        return $cities;
    }
}
