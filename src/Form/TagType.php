<?php

declare(strict_types=1);

namespace App\Form;

use App\Entity\Tag;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

final class TagType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', TextType::class, [
                'label' => 'Nom',
                'attr' => ['placeholder' => 'Symfony, IA, DevOps...'],
            ])
            ->add('slug', TextType::class, [
                'label' => 'Slug',
                'help' => 'Identifiant URL : lettres minuscules, chiffres et tirets uniquement. Genere automatiquement si laisse vide.',
                'required' => false,
                'attr' => ['placeholder' => 'symfony'],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Tag::class,
        ]);
    }
}
