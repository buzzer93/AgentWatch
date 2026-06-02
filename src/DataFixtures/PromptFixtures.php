<?php

declare(strict_types=1);

namespace App\DataFixtures;

use App\Entity\Prompt;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;
use Doctrine\Persistence\ObjectManager;

final class PromptFixtures extends Fixture implements FixtureGroupInterface
{
    public static function getGroups(): array
    {
        return ['prompts'];
    }

    public const string KEY_ARTICLE_SUMMARY = 'article_analysis_summary';
    public const string KEY_ARTICLE_TAGS = 'article_analysis_tags';
    public const string KEY_ARTICLE_SCORING = 'article_analysis_scoring';

    /**
     * @var array<string, array{name: string, description: string, content: string}>
     */
    private const array PROMPTS = [
        self::KEY_ARTICLE_SUMMARY => [
            'name' => 'Analyse article — Étape 1 : Résumé + Titre FR',
            'description' => 'Utilise par le workflow n8n Article Analysis (etape 1). Recoit titre + source + extrait + contenu. Doit retourner un JSON {translatedTitle, summary}.',
            'content' => "/no_think You summarize tech articles for a developer technical watch. Return STRICT JSON with exactly two keys: translatedTitle (the article title translated to French, max 250 chars, faithful to the original) and summary (in French, max 900 chars, accessible and conversational tone like explaining to a smart non-specialist friend, briefly explain technical terms when you use them, prefer concrete examples, avoid hype and marketing words). No markdown, no comments, no extra text.",
        ],
        self::KEY_ARTICLE_TAGS => [
            'name' => 'Analyse article — Étape 2 : Tags',
            'description' => 'Utilise par le workflow Article Analysis (etape 2). Recoit titre + source + summary + allowedTags. Doit retourner un JSON {tags: [slugs]}.',
            'content' => '/no_think You pick tags for a tech article based on its summary. Return STRICT JSON with one key: tags (array of 2 to 6 slugs picked STRICTLY from the allowedTags list). Each picked slug MUST exist in the allowedTags list and MUST match the article topic. Do not invent tags. No markdown, no extra text.',
        ],
        self::KEY_ARTICLE_SCORING => [
            'name' => 'Analyse article — Étape 3 : Scoring',
            'description' => 'Utilise par le workflow Article Analysis (etape 3). Recoit profile + summary + tags. Doit retourner un JSON avec 5 scores 0-100.',
            'content' => '/no_think You rate tech articles for a specific developer profile. Return STRICT JSON with these keys: relevanceScore (0-100, match with profile goals), businessScore (0-100, freelance opportunity potential), learningScore (0-100, practical educational value), contentScore (0-100, LinkedIn content potential), finalScore (0-100, weighted overall). All scores are integers. No reasoning, no explanation, no markdown, no extra text.',
        ],
    ];

    public function load(ObjectManager $manager): void
    {
        foreach (self::PROMPTS as $key => $data) {
            $prompt = new Prompt($key, $data['name'], $data['content']);
            $prompt->setDescription($data['description']);
            $manager->persist($prompt);
        }

        $manager->flush();
    }
}
