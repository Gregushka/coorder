<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\File;
use Symfony\Component\Validator\Constraints\NotBlank;

class MediaType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('file', FileType::class, [
                'label' => 'Select XML File',
                'mapped' => false, // Not mapped to an entity property directly in this form
                'required' => true,
                'constraints' => [
                    new NotBlank(['message' => 'Please select a file']),
                    new File([
                        'maxSize' => '1024k',
                        'mimeTypes' => [
                            'text/xml',
                            'application/xml',
                        ],
                        'mimeTypesMessage' => 'Please upload a valid XML file',
                    ])
                ],
            ])
            ->add('userMediaName', TextType::class, [
                'label' => 'User Media Name (Optional)',
                'mapped' => false,
                'required' => false,
            ]);
    }
}