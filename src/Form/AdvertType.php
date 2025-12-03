<?php

namespace App\Form;

use App\Entity\Advert;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class AdvertType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('title', TextType::class, [
                'label' => 'Title'
            ])
            ->add('category', ChoiceType::class, [
                'label' => 'Category',
                'choices' => [
                    'Electronics' => 'Electronics',
                    'Fashion' => 'Fashion',
                    'Home' => 'Home',
                    'Sports' => 'Sports',
                    'Vehicles' => 'Vehicles',
'Real Estate' => 'Real Estate',
'Services' => 'Services',
'Animals' => 'Animals',
'Art' => 'Art',
'Industrial' => 'Industrial',
'Agriculture' => 'Agriculture',
'Health' => 'Health',
'Others' => 'Others',
                ],
                'placeholder' => 'Select category'
            ])
            ->add('price', NumberType::class, [
                'label' => 'Price (£)'
            ])
            ->add('location', TextType::class, [
                'label' => 'Location'
            ])
            ->add('description', TextareaType::class, [
                'label' => 'Description'
            ])
//            ->add('imageFile', FileType::class, [
//                'label' => 'Upload Images',
//                'mapped' => false,
//                'required' => false,
//                'attr' => [
//                    'accept' => 'image/*'
//                ]
//            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Advert::class,
        ]);
    }
}
