<?php

namespace App\Form;

use App\Constant\UserConstant;
use App\Entity\Mission;
use App\Entity\Reservation;
use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ReservationType extends AbstractType
{

    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        /**
         * @var Reservation $reservation
         */
        $reservation = $options['data'];

        $start = $reservation->getStartAt() ? $reservation->getStartAt()->format('d/m/Y H:i') : '';
        $ended = $reservation->getEndAt() ? $reservation->getEndAt()->format('d/m/Y H:i') : '';

        $builder

            ->add('price', IntegerType::class, [
                'label' => 'Prix (*)'
            ])
            ->add('owner', EntityType::class, [
                'class' => User::class,
                'choice_label' => static function (User $choice) {
                    return sprintf('%s %s ( %s )', $choice->getFirstName() , $choice->getLastName(), $choice->getEmail());
                },
                'query_builder' => static function (UserRepository $repository) {
                    return $repository->createQueryBuilder('u')
                        ->where('u.roles LIKE :role')
                        ->setParameter('role', '%'.UserConstant::ROLE_OWNER.'%')
                        ->addOrderBy('u.id', 'ASC');
                },
                'label' => 'Propriétaire (*)',
                'multiple' => false,
                'required' => true,
                'placeholder' => 'Merci de choisir un propriétaire',
                'attr' => [
                    'class' => 'js-select2',
                    'style' => "width: 100%",
                ],
            ])
            ->add('locataire', EntityType::class, [
                'class' => User::class,
                'choice_label' => static function (User $choice) {
                    return sprintf('%s %s ( %s )', $choice->getFirstName() , $choice->getLastName(), $choice->getEmail());
                },
                'query_builder' => static function (UserRepository $repository) {
                    return $repository->createQueryBuilder('u')
                        ->where('u.roles LIKE :role')
                        ->setParameter('role', '%'.UserConstant::ROLE_CLIENT.'%')
                        ->addOrderBy('u.id', 'ASC');
                },
                'label' => 'Locataire (*)',
                'multiple' => false,
                'required' => true,
                'placeholder' => 'Merci de choisir un locataire',
                'attr' => [
                    'class' => 'js-select2',
                    'style' => "width: 100%",
                ],
            ])

            ->add('debut', TextType::class, [
                'label' => 'Début location (*)',
                'attr' => [
                    'class' => 'datepicker'
                ],
                "data" => $start,
                'mapped' => false
            ])
            ->add('fin', TextType::class, [
                'label' => 'Fin location (*)',
                'attr' => [
                    'class' => 'datepicker'
                ],
                "data" => $ended,
                'mapped' => false
            ])
        ;


        $formModifier = function (FormInterface $form, ?User $user = null) {

            $form->add('mission', ChoiceType::class, [
                'choice_label' => 'title',
                'label' => 'Annonce (*)',
                'required' => true,
                'choice_value' => function ($item) {
                    return is_object($item) ? $item->getId() : $item;
                },
                'placeholder' => 'Choisir une quartier...',
                'choices' => $this->getMissions($user),
            ]);

        };

        $builder->addEventListener(
            FormEvents::PRE_SET_DATA,
            function (FormEvent $event) use ($formModifier) {
                // this would be your entity, i.e. SportMeetup
                $data = $event->getData();

                $formModifier($event->getForm(), $data->getOwner());
            }
        );

        $builder->get('owner')->addEventListener(
            FormEvents::POST_SUBMIT,
            function (FormEvent $event) use ($formModifier) {
                // It's important here to fetch $event->getForm()->getData(), as
                // $event->getData() will get you the client data (that is, the ID)
                $owner = $event->getForm()->getData();

                // since we've added the listener to the child, we'll have to pass on
                // the parent to the callback function!
                $formModifier($event->getForm()->getParent(), $owner);
            }
        );

    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Reservation::class,
        ]);
    }

    private function getMissions(?User $user)
    {
        if ($user) {
            return $this->entityManager->getRepository(Mission::class)->findBy(['user' => $user]);
        }

        return [];
    }
}
