<?php

namespace App\Form;

use App\Entity\Image;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

use Symfony\Component\Form\Extension\Core\Type\FileType as FileTypeBase;
use Symfony\Component\Validator\Constraints\File as FileConstraint;

class ImageType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('label', TextType::class, [
                'attr' => [
                    'placeholder' => "Label de l'image",
                    'class' => 'col-12'
                ],
                'label' => false,
                'mapped' => true,
                'required' => false,
            ])
            ->add('file', FileTypeBase::class, [
                'data_class' => null,
                'required' => false,
                'label' => false,
                'mapped' => false,
                'constraints' => [
                    new FileConstraint([
                        'maxSize' => '2M',
                        'maxSizeMessage' => 'Le fichier est trop volumineux ({{ taille }} {{ suffixe }}). La taille maximale autorisée est de {{ limite }}. {{ suffixe }}'
                    ])
                ],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Image::class,
        ]);
    }
}
