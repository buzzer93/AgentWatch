<?php

declare(strict_types=1);

namespace App\DataFixtures;

use App\Entity\RssSource;
use App\Entity\Tag;
use App\Enum\SourceType;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

/**
 * Sources RSS de demarrage.
 *
 * URLs verifiees le 2026-05-26 (liste initiale) : seules les URLs qui repondent
 * en 200 et servent effectivement un feed XML/Atom ont ete conservees.
 *
 * D autres sources ont ete ajoutees ensuite (cf. scrapeConfig.category = PRIORITE_1).
 *
 * URLs qui etaient dans la liste initiale mais qui sont cassees ou ont change
 * d adresse (a corriger manuellement avant de les ajouter) :
 *   - Symfony Blog          https://symfony.com/blog/rss.xml          (404)
 *   - PHP.Watch             https://php.watch/feed                    (404)
 *   - Stitcher.io           https://stitcher.io/blog/feed.xml         (404)
 *   - Kevin Dunglas         https://dunglas.dev/feed.xml              (404)
 *   - JoliCode              https://jolicode.com/blog/feed.xml        (404)
 *   - Anthropic News        https://www.anthropic.com/news/rss.xml    (404)
 *   - Caddy Blog            https://caddyserver.com/feed.xml          (404)
 *   - LangChain Blog        https://blog.langchain.dev/rss/           (redirige vers HTML)
 *   - LinuxServer.io        https://www.linuxserver.io/blog/rss.xml   (404)
 *   - Indie Hackers         https://www.indiehackers.com/feed         (HTML, pas RSS)
 *   - Zapier Engineering    https://zapier.com/engineering/rss/       (404)
 *
 * URLs retirees car bloquent le noeud rssFeedRead de n8n (User-Agent filtre) :
 *   - InfoQ Architecture    https://feed.infoq.com/architecture-design (406 sur n8n)
 *     Le feed repond 200 avec un UA navigateur classique mais 406 avec le UA n8n.
 *     A remettre quand on migrera vers HTTP Request + XML parse dans le workflow.
 *
 * URLs retirees pour cause de bruit ou hors-scope :
 *   - Hacker News           https://hnrss.org/frontpage               (30+ articles/h, hors-sujet frequent)
 *   - SaaStr                https://www.saastr.com/feed/              (marketing SaaS souvent superficiel)
 *   - GitHub Engineering    https://github.blog/engineering/feed/     (long-form trop theorique Microsoft-scale)
 */
final class RssSourceFixtures extends Fixture implements DependentFixtureInterface, FixtureGroupInterface
{
    /**
     * @var list<array{name: string, feedUrl: string, websiteUrl: string|null, priority: int, tags: list<string>, scrapeConfig?: array<string, string>|null}>
     */
    private const array SOURCES = [
        // Symfony / PHP
        [
            'name' => 'SymfonyCasts Blog',
            'feedUrl' => 'https://symfonycasts.com/blog.rss',
            'websiteUrl' => 'https://symfonycasts.com/blog',
            'priority' => 90,
            'tags' => ['symfony', 'php', 'tutorial', 'practical'],
        ],
        [
            'name' => 'Tomas Votruba',
            'feedUrl' => 'https://tomasvotruba.com/rss.xml',
            'websiteUrl' => 'https://tomasvotruba.com',
            'priority' => 70,
            'tags' => ['php', 'symfony', 'clean-code', 'deep-dive'],
        ],

        // IA / Agents / LLM
        [
            'name' => 'Simon Willison',
            'feedUrl' => 'https://simonwillison.net/atom/everything/',
            'websiteUrl' => 'https://simonwillison.net',
            'priority' => 95,
            'tags' => ['llm', 'ai-workflow', 'prompt-engineering', 'practical', 'deep-dive'],
        ],
        [
            'name' => 'OpenAI Blog',
            'feedUrl' => 'https://openai.com/news/rss.xml',
            'websiteUrl' => 'https://openai.com/news',
            'priority' => 70,
            'tags' => ['openai', 'gpt', 'llm', 'trend'],
        ],
        [
            'name' => 'Hugging Face Blog',
            'feedUrl' => 'https://huggingface.co/blog/feed.xml',
            'websiteUrl' => 'https://huggingface.co/blog',
            'priority' => 75,
            'tags' => ['llm', 'embedding', 'rag', 'deep-dive'],
        ],
        [
            'name' => 'Ollama Blog',
            'feedUrl' => 'https://ollama.com/blog/rss.xml',
            'websiteUrl' => 'https://ollama.com/blog',
            'priority' => 80,
            'tags' => ['ollama', 'llm', 'self-hosted', 'practical'],
        ],

        // Docker / DevOps / Infra
        [
            'name' => 'Docker Blog',
            'feedUrl' => 'https://www.docker.com/blog/feed/',
            'websiteUrl' => 'https://www.docker.com/blog',
            'priority' => 60,
            'tags' => ['docker', 'docker-compose', 'deployment'],
        ],

        // Architecture / Engineering
        [
            'name' => 'Martin Fowler',
            'feedUrl' => 'https://www.martinfowler.com/feed.atom',
            'websiteUrl' => 'https://www.martinfowler.com',
            'priority' => 85,
            'tags' => ['architecture', 'clean-architecture', 'design-pattern', 'ddd', 'deep-dive'],
        ],
        [
            'name' => 'ByteByteGo',
            'feedUrl' => 'https://blog.bytebytego.com/feed',
            'websiteUrl' => 'https://blog.bytebytego.com',
            'priority' => 70,
            'tags' => ['architecture', 'scalability', 'design-pattern', 'beginner-friendly'],
        ],

        // n8n / Automatisation
        [
            'name' => 'n8n Blog',
            'feedUrl' => 'https://blog.n8n.io/rss/',
            'websiteUrl' => 'https://blog.n8n.io',
            'priority' => 80,
            'tags' => ['automation', 'ai-workflow', 'practical', 'tutorial'],
        ],

        // FR / Media / Dev
        [
            'name' => 'BLAST — RSS articles',
            'feedUrl' => 'https://api.blast-info.fr/rss_articles.xml',
            'websiteUrl' => 'https://www.blast-info.fr/',
            'priority' => 100,
            'tags' => ['deep-dive', 'high-value', 'trend'],
            'scrapeConfig' => [
                'category' => 'PRIORITE_1',
                'method' => 'HTTP GET (XML) + parse RSS/Atom',
                'frequency' => '1-4x/jour',
                'break_risk' => 'faible',
            ],
        ],
        [
            'name' => 'BLAST — RSS émissions',
            'feedUrl' => 'https://api.blast-info.fr/rss_emissions.xml',
            'websiteUrl' => 'https://www.blast-info.fr/',
            'priority' => 100,
            'tags' => ['deep-dive', 'high-value', 'trend'],
            'scrapeConfig' => [
                'category' => 'PRIORITE_1',
                'method' => 'HTTP GET (XML) + parse RSS/Atom',
                'frequency' => '1-4x/jour',
                'break_risk' => 'faible',
            ],
        ],
        [
            'name' => 'BLAST — RSS tous contenus',
            'feedUrl' => 'https://api.blast-info.fr/rss.xml',
            'websiteUrl' => 'https://www.blast-info.fr/',
            'priority' => 100,
            'tags' => ['deep-dive', 'high-value', 'trend'],
            'scrapeConfig' => [
                'category' => 'PRIORITE_1',
                'method' => 'HTTP GET (XML) + parse RSS/Atom',
                'frequency' => '1-4x/jour',
                'break_risk' => 'faible',
            ],
        ],
        [
            'name' => 'Grafikart — RSS',
            'feedUrl' => 'https://feeds.feedburner.com/Grafikart',
            'websiteUrl' => 'https://grafikart.fr/',
            'priority' => 95,
            'tags' => ['tutorial', 'practical', 'beginner-friendly'],
            'scrapeConfig' => [
                'category' => 'PRIORITE_1',
                'method' => 'HTTP GET (XML) + parse RSS/Atom',
                'frequency' => '1-4x/jour',
                'break_risk' => 'moyen',
            ],
        ],
        [
            'name' => 'FutureRadar — Substack RSS',
            'feedUrl' => 'https://futureradar.substack.com/feed',
            'websiteUrl' => null,
            'priority' => 95,
            'tags' => ['trend', 'high-value', 'automation-business'],
            'scrapeConfig' => [
                'category' => 'PRIORITE_1',
                'method' => 'HTTP GET (XML) + parse RSS/Atom',
                'frequency' => '1-4x/jour',
                'break_risk' => 'faible',
            ],
        ],
        [
            'name' => 'YoanDev — RSS',
            'feedUrl' => 'https://yoandev.co/rss.xml',
            'websiteUrl' => 'https://yoandev.co/',
            'priority' => 95,
            'tags' => ['software-engineering', 'tutorial', 'practical'],
            'scrapeConfig' => [
                'category' => 'PRIORITE_1',
                'method' => 'HTTP GET (XML) + parse RSS/Atom',
                'frequency' => '1-4x/jour',
                'break_risk' => 'faible',
            ],
        ],
    ];

    public function load(ObjectManager $manager): void
    {
        foreach (self::SOURCES as $data) {
            $source = new RssSource($data['name'], $data['feedUrl']);
            $source->setWebsiteUrl($data['websiteUrl']);
            $source->setPriority($data['priority']);
            $source->setIsActive(true);
            $source->setType(SourceType::RSS);
            $source->setScrapeConfig($data['scrapeConfig'] ?? null);

            foreach ($data['tags'] as $tagSlug) {
                $reference = TagFixtures::REFERENCE_PREFIX . $tagSlug;
                if (!$this->hasReference($reference, Tag::class)) {
                    throw new \RuntimeException(\sprintf(
                        'Tag "%s" referenced by source "%s" not found in TagFixtures.',
                        $tagSlug,
                        $data['name'],
                    ));
                }
                $source->addTag($this->getReference($reference, Tag::class));
            }

            $manager->persist($source);
        }

        $manager->flush();
    }

    public function getDependencies(): array
    {
        return [TagFixtures::class];
    }

    public static function getGroups(): array
    {
        return ['default'];
    }
}
