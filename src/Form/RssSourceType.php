<?php

declare(strict_types=1);

namespace App\Form;

use App\Entity\RssSource;
use App\Entity\Tag;
use App\Enum\SourceType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\CallbackTransformer;
use Symfony\Component\Form\Exception\TransformationFailedException;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\EnumType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\UrlType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

final class RssSourceType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', TextType::class, [
                'label' => 'Nom',
                'attr' => ['placeholder' => 'Symfony Blog'],
            ])
            ->add('type', EnumType::class, [
                'label' => 'Type de source',
                'class' => SourceType::class,
                'choice_label' => fn (SourceType $type) => $type->label(),
                'help' => 'Garde "Flux RSS" pour le MVP. Le mode "Scraping HTML" sera actif en Phase 13.',
            ])
            ->add('feedUrl', UrlType::class, [
                'label' => 'URL du flux ou de la page à scraper',
                'default_protocol' => 'https',
                'attr' => ['placeholder' => 'https://example.com/feed.xml'],
                'help' => 'Flux XML (RSS/Atom) ou URL de la page listant les articles si type = Scraping HTML.',
            ])
            ->add('websiteUrl', UrlType::class, [
                'label' => 'URL du site',
                'required' => false,
                'default_protocol' => 'https',
                'attr' => ['placeholder' => 'https://example.com'],
            ])
            ->add('tags', EntityType::class, [
                'label' => 'Tags',
                'class' => Tag::class,
                'choice_label' => 'name',
                'multiple' => true,
                'expanded' => false,
                'required' => false,
                'by_reference' => false,
                'autocomplete' => true,
                'query_builder' => fn ($repo) => $repo->createQueryBuilder('tag')->orderBy('tag.name', 'ASC'),
                'help' => 'Tape pour filtrer. Selectionne plusieurs themes couverts par la source.',
            ])
            ->add('priority', IntegerType::class, [
                'label' => 'Priorite',
                'help' => 'Entre 0 et 100. Plus le score est haut, plus la source est mise en avant.',
                'attr' => ['min' => 0, 'max' => 100],
            ])
            ->add('scrapeConfig', TextareaType::class, [
                'label' => 'Configuration de scraping (JSON)',
                'required' => false,
                'attr' => [
                    'rows' => 8,
                    'class' => 'font-mono text-sm',
                    'placeholder' => "{\n  \"itemSelector\": \"article\",\n  \"titleSelector\": \"h2 a\",\n  \"linkSelector\": \"h2 a\",\n  \"linkAttribute\": \"href\",\n  \"dateSelector\": \"time\",\n  \"dateAttribute\": \"datetime\",\n  \"excerptSelector\": \"p\"\n}",
                ],
                'help' => 'Utilise uniquement si type = Scraping HTML. Selecteurs CSS pour extraire les articles.',
            ])
            ->add('isActive', CheckboxType::class, [
                'label' => 'Source active',
                'required' => false,
            ])
        ;

        $builder->get('scrapeConfig')->addModelTransformer(new CallbackTransformer(
            static fn (?array $array): string => $array === null
                ? ''
                : (string) json_encode($array, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
            static function (?string $string): ?array {
                if ($string === null || trim($string) === '') {
                    return null;
                }
                try {
                    $decoded = json_decode($string, true, flags: JSON_THROW_ON_ERROR);
                } catch (\JsonException $e) {
                    throw new TransformationFailedException('JSON invalide : '.$e->getMessage());
                }
                if (!is_array($decoded)) {
                    throw new TransformationFailedException('Le JSON doit etre un objet.');
                }

                return $decoded;
            },
        ));
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => RssSource::class,
        ]);
    }
}
