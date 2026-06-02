<?php

declare(strict_types=1);

namespace App\Controller;

use App\Repository\PromptRepository;
use App\Repository\RssItemRepository;
use App\Repository\RssSourceRepository;
use App\Repository\TagRepository;
use App\Service\Import\AiAnalysisImporter;
use App\Service\Import\DailySelectionImporter;
use App\Service\Import\InternalApiTokenGuard;
use App\Service\Import\RssItemImporter;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/internal')]
final class InternalApiController extends AbstractController
{
    public function __construct(
        private readonly InternalApiTokenGuard $tokenGuard,
        private readonly RssSourceRepository $rssSourceRepository,
        private readonly RssItemRepository $rssItemRepository,
        private readonly TagRepository $tagRepository,
        private readonly PromptRepository $promptRepository,
        private readonly RssItemImporter $rssItemImporter,
        private readonly AiAnalysisImporter $aiAnalysisImporter,
        private readonly DailySelectionImporter $dailySelectionImporter,
    ) {
    }

    #[Route('/prompts', name: 'app_internal_prompts', methods: ['GET'])]
    public function prompts(Request $request): JsonResponse
    {
        return $this->handleRequest($request, function (): array {
            $prompts = $this->promptRepository->findAllOrderedByKey();

            return [
                'prompts' => array_map(static fn ($prompt): array => [
                    'key' => $prompt->getPromptKey(),
                    'name' => $prompt->getName(),
                    'content' => $prompt->getContent(),
                ], $prompts),
            ];
        });
    }

    #[Route('/tags', name: 'app_internal_tags', methods: ['GET'])]
    public function tags(Request $request): JsonResponse
    {
        return $this->handleRequest($request, function (): array {
            $tags = $this->tagRepository->findAllOrderedByName();

            return [
                'tags' => array_map(static fn ($tag): array => [
                    'slug' => $tag->getSlug(),
                    'name' => $tag->getName(),
                ], $tags),
            ];
        });
    }

    #[Route('/rss-items/unprocessed', name: 'app_internal_rss_items_unprocessed', methods: ['GET'])]
    public function unprocessedItems(Request $request): JsonResponse
    {
        return $this->handleRequest($request, function () use ($request): array {
            $limit = (int) ($request->query->get('limit') ?? 25);
            $limit = max(1, min(100, $limit));

            $items = $this->rssItemRepository->findLatestUnprocessed($limit);

            return [
                'items' => array_map(static fn ($item): array => [
                    'id' => $item->getId(),
                    'sourceId' => $item->getSource()->getId(),
                    'sourceName' => $item->getSource()->getName(),
                    'title' => $item->getTitle(),
                    'url' => $item->getUrl(),
                    'publishedAt' => $item->getPublishedAt()?->format(\DateTimeInterface::ATOM),
                    'rawExcerpt' => $item->getRawExcerpt(),
                    'rawContent' => $item->getRawContent(),
                ], $items),
            ];
        });
    }

    #[Route('/rss-sources', name: 'app_internal_rss_sources', methods: ['GET'])]
    public function rssSources(Request $request): JsonResponse
    {
        return $this->handleRequest($request, function (): array {
            $sources = $this->rssSourceRepository->findActiveOrderedByPriority();

            return [
                'sources' => array_map(static fn ($source): array => [
                    'id' => $source->getId(),
                    'name' => $source->getName(),
                    'feedUrl' => $source->getFeedUrl(),
                    'websiteUrl' => $source->getWebsiteUrl(),
                    'tags' => array_map(
                        static fn ($tag): array => ['slug' => $tag->getSlug(), 'name' => $tag->getName()],
                        $source->getTags()->toArray(),
                    ),
                    'priority' => $source->getPriority(),
                ], $sources),
            ];
        });
    }

    #[Route('/rss-items', name: 'app_internal_rss_items_create', methods: ['POST'])]
    public function createRssItem(Request $request): JsonResponse
    {
        return $this->handleRequest($request, function () use ($request): array {
            $result = $this->rssItemImporter->import($request->toArray());

            return [
                'payload' => [
                    'status' => $result['status'],
                    'id' => $result['id'],
                ],
                'statusCode' => $result['status'] === 'created' ? Response::HTTP_CREATED : Response::HTTP_OK,
            ];
        });
    }

    #[Route('/ai-analysis', name: 'app_internal_ai_analysis_create', methods: ['POST'])]
    public function createAiAnalysis(Request $request): JsonResponse
    {
        return $this->handleRequest($request, function () use ($request): array {
            $result = $this->aiAnalysisImporter->import($request->toArray());

            return [
                'payload' => [
                    'status' => $result['status'],
                    'id' => $result['id'],
                ],
                'statusCode' => $result['status'] === 'created' ? Response::HTTP_CREATED : Response::HTTP_OK,
            ];
        });
    }

    #[Route('/daily-selection', name: 'app_internal_daily_selection_create', methods: ['POST'])]
    public function createDailySelection(Request $request): JsonResponse
    {
        return $this->handleRequest($request, function () use ($request): array {
            $result = $this->dailySelectionImporter->import($request->toArray());

            return [
                'payload' => [
                    'status' => $result['status'],
                    'id' => $result['id'],
                ],
                'statusCode' => $result['status'] === 'created' ? Response::HTTP_CREATED : Response::HTTP_OK,
            ];
        });
    }

    /**
     * @param callable(): array{payload?: array<string, mixed>, statusCode?: int, sources?: array<int, array<string, mixed>>, tags?: array<int, array<string, mixed>>, items?: array<int, array<string, mixed>>} $callback
     */
    private function handleRequest(Request $request, callable $callback): JsonResponse
    {
        try {
            $this->tokenGuard->assertValid($request);
            $result = $callback();

            foreach (['sources', 'tags', 'items', 'prompts'] as $listKey) {
                if (\array_key_exists($listKey, $result)) {
                    return $this->json($result, Response::HTTP_OK);
                }
            }

            return $this->json($result['payload'] ?? [], $result['statusCode'] ?? Response::HTTP_OK);
        } catch (\JsonException $exception) {
            return $this->json([
                'error' => 'Invalid JSON payload.',
                'details' => $exception->getMessage(),
            ], Response::HTTP_BAD_REQUEST);
        } catch (HttpExceptionInterface $exception) {
            return $this->json([
                'error' => $exception->getMessage(),
            ], $exception->getStatusCode());
        }
    }
}