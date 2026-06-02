<?php

declare(strict_types=1);

namespace App\DataFixtures;

use App\Entity\Tag;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

final class TagFixtures extends Fixture
{
    public const string REFERENCE_PREFIX = 'tag-';

    /** @var array<string, string> map slug => nom affiche */
    public const array TAGS = [
        // Symfony / PHP
        'symfony' => 'Symfony',
        'php' => 'PHP',
        'doctrine' => 'Doctrine',
        'twig' => 'Twig',
        'api-platform' => 'API Platform',
        'assetmapper' => 'AssetMapper',
        'stimulus' => 'Stimulus',
        'turbo' => 'Turbo',
        'messenger' => 'Messenger',
        'security' => 'Security',
        'performance' => 'Performance',
        'testing' => 'Testing',
        'ddd' => 'DDD',
        'clean-architecture' => 'Clean Architecture',

        // IA / LLM / Agents
        'llm' => 'LLM',
        'agent' => 'Agent',
        'mcp' => 'MCP',
        'rag' => 'RAG',
        'embedding' => 'Embedding',
        'vector-db' => 'Vector DB',
        'prompt-engineering' => 'Prompt Engineering',
        'ai-workflow' => 'AI Workflow',
        'multi-agent' => 'Multi-Agent',
        'ollama' => 'Ollama',
        'openai' => 'OpenAI',
        'anthropic' => 'Anthropic',
        'claude' => 'Claude',
        'gpt' => 'GPT',
        'gemini' => 'Gemini',
        'qwen' => 'Qwen',
        'mistral' => 'Mistral',

        // Automatisation / Infra / DevOps
        'automation' => 'Automation',
        'docker' => 'Docker',
        'docker-compose' => 'Docker Compose',
        'kubernetes' => 'Kubernetes',
        'vps' => 'VPS',
        'linux' => 'Linux',
        'ubuntu' => 'Ubuntu',
        'caddy' => 'Caddy',
        'nginx' => 'Nginx',
        'traefik' => 'Traefik',
        'deployment' => 'Deployment',
        'ci-cd' => 'CI/CD',
        'github-actions' => 'GitHub Actions',
        'monitoring' => 'Monitoring',
        'self-hosted' => 'Self-Hosted',
        'networking' => 'Networking',
        'ssl' => 'SSL',

        // Business / Freelance
        'freelance' => 'Freelance',
        'saas' => 'SaaS',
        'startup' => 'Startup',
        'pricing' => 'Pricing',
        'client' => 'Client',
        'prospection' => 'Prospection',
        'marketing' => 'Marketing',
        'productivity' => 'Productivite',
        'automation-business' => 'Automation Business',
        'solopreneur' => 'Solopreneur',

        // Architecture / Engineering
        'architecture' => 'Architecture',
        'microservice' => 'Microservice',
        'monolith' => 'Monolith',
        'event-driven' => 'Event-Driven',
        'clean-code' => 'Clean Code',
        'scalability' => 'Scalability',
        'design-pattern' => 'Design Pattern',
        'software-engineering' => 'Software Engineering',

        // Securite
        'cve' => 'CVE',
        'authentication' => 'Authentication',
        'authorization' => 'Authorization',
        'xss' => 'XSS',
        'csrf' => 'CSRF',
        'sql-injection' => 'SQL Injection',
        'secrets' => 'Secrets',
        'hardening' => 'Hardening',

        // Meta / Scoring
        'advanced' => 'Avance',
        'beginner-friendly' => 'Pour debutants',
        'practical' => 'Pratique',
        'actionable' => 'Actionnable',
        'tutorial' => 'Tutoriel',
        'deep-dive' => 'Deep-Dive',
        'return-of-experience' => 'Retour d experience',
        'business-opportunity' => 'Opportunite business',
        'client-service' => 'Service client',
        'content-idea' => 'Idee de contenu',
        'portfolio-idea' => 'Idee portfolio',
        'high-value' => 'Forte valeur',
        'trend' => 'Tendance',
    ];

    public function load(ObjectManager $manager): void
    {
        foreach (self::TAGS as $slug => $name) {
            $tag = new Tag($name, $slug);
            $manager->persist($tag);
            $this->addReference(self::REFERENCE_PREFIX . $slug, $tag);
        }

        $manager->flush();
    }
}
