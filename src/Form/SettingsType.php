<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateIntervalType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\RadioType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class SettingsType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('interval', NumberType::class, [
                'label' => 'Interval',
                'html5' => true,
                'scale' => 2,
                'attr' => [
                    'step' => '0.01',
                    'min' => '0'
                ]
            ])
            ->add('period', ChoiceType::class, [
                'label' => 'Period',
                'choices' => [
                    'Daily' => 'daily',
                    'Weekly' => 'weekly',
                    'Monthly' => 'monthly',
                    'Yearly' => 'yearly'
                ],
                'placeholder' => 'Select a period...'
            ])
            ->add('latitude', NumberType::class, [
                'label' => 'Latitude',
                'html5' => true,
                'scale' => 6,
                'attr' => [
                    'step' => '0.000001',
                    'min' => '-90',
                    'max' => '90'
                ]
            ])
            ->add('longitude', NumberType::class, [
                'label' => 'Longitude',
                'html5' => true,
                'scale' => 6,
                'attr' => [
                    'step' => '0.000001',
                    'min' => '-180',
                    'max' => '180'
                ]
            ])
            ->add('radio_choice', ChoiceType::class, [
                'label' => 'Yes, No, Dunno',
                'choices' => [
                    'Yes' => 'yes',
                    'No' => 'no',
                    'Dunno' => 'dunno'
                ],
                'expanded' => true,
                'multiple' => false
            ])
            ->add('fresh', CheckboxType::class, [
                'label' => 'Fresh',
                'required' => false
            ])
            ->add('date_range', DateIntervalType::class, [
                'label' => 'From - To',
                'widget' => 'choice',
                'with_years' => true,
                'with_months' => true,
                'with_days' => true,
                'with_hours' => false,
                'with_minutes' => false,
                'with_seconds' => false,
                'labels' => [
                    'years' => 'Years',
                    'months' => 'Months',
                    'days' => 'Days'
                ]
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            // Configure your form options here
        ]);
    }
}