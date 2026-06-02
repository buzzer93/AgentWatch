<?php

declare(strict_types=1);

namespace App\Form;

use App\Entity\Prompt;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

final class PromptType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', TextType::class, [
                'label' => 'Nom',
            ])
            ->add('description', TextareaType::class, [
                'label' => 'Description / Contexte d\'utilisation',
                'required' => false,
                'attr' => ['rows' => 3, 'placeholder' => 'Quel workflow utilise ce prompt, quel est son role...'],
            ])
            ->add('content', TextareaType::class, [
                'label' => 'Prompt systeme',
                'help' => 'Texte envoye dans le role "system" lors de l\'appel a l\'IA. Conserve le /no_think initial pour qwen3.',
                'attr' => ['rows' => 14, 'class' => 'font-mono text-sm'],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Prompt::class,
        ]);
    }
}
