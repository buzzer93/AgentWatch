<?php

declare(strict_types=1);

namespace App\Service\Import;

use App\Entity\AiAnalysis;
use App\Entity\RssItem;
use App\Repository\RssItemRepository;
use App\Repository\TagRepository;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

final class AiAnalysisImporter
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly RssItemRepository $rssItemRepository,
        private readonly TagRepository $tagRepository,
        private readonly LoggerInterface $logger,
    ) {
    }

    /**
     * @param array<string, mixed> $payload
     *
     * @return array{status: string, id: int}
     */
    public function import(array $payload): array
    {
        $rssItem = $this->findRssItem($payload['rssItemId'] ?? $payload['itemId'] ?? null);
        $summary = $this->requireString($payload, 'summary');
        $reasoning = $this->optionalString($payload, 'reasoning');
        $modelUsed = $this->optionalString($payload, 'modelUsed') ?? 'qwen';
        $tagSlugs = $this->parseTagSlugs($payload['tags'] ?? null);
        $translatedTitle = $this->optionalString($payload, 'translatedTitle');

        $analysis = $rssItem->getAnalysis();
        $status = 'updated';

        if (!$analysis instanceof AiAnalysis) {
            $analysis = new AiAnalysis(
                $rssItem,
                $summary,
                $this->requireInt($payload, 'relevanceScore'),
                $this->requireInt($payload, 'businessScore'),
                $this->requireInt($payload, 'learningScore'),
                $this->requireInt($payload, 'contentScore'),
                $this->requireInt($payload, 'finalScore'),
                $reasoning,
                $modelUsed,
            );
            $rssItem->setAnalysis($analysis);
            $this->entityManager->persist($analysis);
            $status = 'created';
        } else {
            $analysis
                ->setSummary($summary)
                ->setRelevanceScore($this->requireInt($payload, 'relevanceScore'))
                ->setBusinessScore($this->requireInt($payload, 'businessScore'))
                ->setLearningScore($this->requireInt($payload, 'learningScore'))
                ->setContentScore($this->requireInt($payload, 'contentScore'))
                ->setFinalScore($this->requireInt($payload, 'finalScore'))
                ->setReasoning($reasoning)
                ->setModelUsed($modelUsed);
        }

        $analysis->setTranslatedTitle($translatedTitle);

        $this->syncTags($rssItem, $tagSlugs);

        $rssItem->setIsProcessed(true);
        $this->entityManager->flush();

        return [
            'status' => $status,
            'id' => (int) $analysis->getId(),
        ];
    }

    /** @param list<string> $slugs */
    private function syncTags(RssItem $rssItem, array $slugs): void
    {
        foreach ($rssItem->getTags() as $existingTag) {
            $rssItem->removeTag($existingTag);
        }

        foreach ($slugs as $slug) {
            $tag = $this->tagRepository->findOneBySlug($slug);

            if ($tag === null) {
                $this->logger->warning('Unknown tag slug returned by AI, ignored.', [
                    'slug' => $slug,
                    'rssItemId' => $rssItem->getId(),
                ]);
                continue;
            }

            $rssItem->addTag($tag);
        }
    }

    private function findRssItem(mixed $rssItemId): RssItem
    {
        if (!\is_int($rssItemId) && !\ctype_digit((string) $rssItemId)) {
            throw new BadRequestHttpException('The "rssItemId" field must be an integer.');
        }

        $rssItem = $this->rssItemRepository->find((int) $rssItemId);

        if (!$rssItem instanceof RssItem) {
            throw new NotFoundHttpException('RSS item not found.');
        }

        return $rssItem;
    }

    /** @return list<string> */
    private function parseTagSlugs(mixed $value): array
    {
        if ($value === null) {
            return [];
        }

        if (!\is_array($value)) {
            throw new BadRequestHttpException('The "tags" field must be an array of strings.');
        }

        $slugs = [];

        foreach ($value as $tag) {
            if (!\is_string($tag) || trim($tag) === '') {
                continue;
            }

            $slugs[] = strtolower(trim($tag));
        }

        return array_values(array_unique($slugs));
    }

    /** @param array<string, mixed> $payload */
    private function requireString(array $payload, string $key): string
    {
        $value = $this->optionalString($payload, $key);

        if ($value === null) {
            throw new BadRequestHttpException(\sprintf('The "%s" field is required.', $key));
        }

        return $value;
    }

    /** @param array<string, mixed> $payload */
    private function optionalString(array $payload, string $key): ?string
    {
        if (!\array_key_exists($key, $payload) || $payload[$key] === null) {
            return null;
        }

        if (!\is_string($payload[$key])) {
            throw new BadRequestHttpException(\sprintf('The "%s" field must be a string.', $key));
        }

        $value = trim($payload[$key]);

        return $value === '' ? null : $value;
    }

    /** @param array<string, mixed> $payload */
    private function requireInt(array $payload, string $key): int
    {
        if (!\array_key_exists($key, $payload)) {
            throw new BadRequestHttpException(\sprintf('The "%s" field is required.', $key));
        }

        $value = $payload[$key];

        if (!\is_int($value) && !\ctype_digit((string) $value)) {
            throw new BadRequestHttpException(\sprintf('The "%s" field must be an integer.', $key));
        }

        return (int) $value;
    }
}
