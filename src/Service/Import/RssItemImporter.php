<?php

declare(strict_types=1);

namespace App\Service\Import;

use App\Entity\RssItem;
use App\Entity\RssSource;
use App\Repository\RssItemRepository;
use App\Repository\RssSourceRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

final class RssItemImporter
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly RssSourceRepository $rssSourceRepository,
        private readonly RssItemRepository $rssItemRepository,
    ) {
    }

    /**
     * @param array<string, mixed> $payload
     *
     * @return array{status: string, id: int}
     */
    public function import(array $payload): array
    {
        $source = $this->findSource($payload['sourceId'] ?? null);
        $title = $this->requireString($payload, 'title');
        $url = $this->requireString($payload, 'url');
        $publishedAt = $this->optionalDateTime($payload['publishedAt'] ?? null, 'publishedAt');

        $hash = $this->optionalString($payload, 'hash')
            ?? $this->buildHash($title, $source, $publishedAt);

        $existingItem = $this->rssItemRepository->findOneByUrl($url) ?? $this->rssItemRepository->findOneByHash($hash);

        if ($existingItem instanceof RssItem) {
            return [
                'status' => 'duplicate',
                'id' => (int) $existingItem->getId(),
            ];
        }

        $rssItem = new RssItem($source, $title, $url, $hash);
        $rssItem->setAuthor($this->optionalString($payload, 'author'));
        $rssItem->setPublishedAt($publishedAt);
        $rssItem->setImportedAt($this->optionalDateTime($payload['importedAt'] ?? null, 'importedAt') ?? new \DateTimeImmutable());
        $rssItem->setRawExcerpt($this->optionalString($payload, 'rawExcerpt'));
        $rssItem->setRawContent($this->optionalString($payload, 'rawContent'));
        $rssItem->setLanguage($this->optionalString($payload, 'language'));

        $this->entityManager->persist($rssItem);
        $this->entityManager->flush();

        return [
            'status' => 'created',
            'id' => (int) $rssItem->getId(),
        ];
    }

    private function buildHash(string $title, RssSource $source, ?\DateTimeImmutable $publishedAt): string
    {
        $datePart = $publishedAt?->format('Y-m-d') ?? 'no-date';

        return hash('sha256', \sprintf('%s|%d|%s', $title, $source->getId(), $datePart));
    }

    private function findSource(mixed $sourceId): RssSource
    {
        if (!is_int($sourceId) && !ctype_digit((string) $sourceId)) {
            throw new BadRequestHttpException('The "sourceId" field must be an integer.');
        }

        $source = $this->rssSourceRepository->find((int) $sourceId);

        if (!$source instanceof RssSource) {
            throw new NotFoundHttpException('RSS source not found.');
        }

        return $source;
    }

    /** @param array<string, mixed> $payload */
    private function requireString(array $payload, string $key): string
    {
        $value = $this->optionalString($payload, $key);

        if ($value === null) {
            throw new BadRequestHttpException(sprintf('The "%s" field is required.', $key));
        }

        return $value;
    }

    /** @param array<string, mixed> $payload */
    private function optionalString(array $payload, string $key): ?string
    {
        if (!array_key_exists($key, $payload) || $payload[$key] === null) {
            return null;
        }

        if (!is_string($payload[$key])) {
            throw new BadRequestHttpException(sprintf('The "%s" field must be a string.', $key));
        }

        $value = trim($payload[$key]);

        return $value === '' ? null : $value;
    }

    private function optionalDateTime(mixed $value, string $field): ?\DateTimeImmutable
    {
        if ($value === null) {
            return null;
        }

        if (!is_string($value)) {
            throw new BadRequestHttpException(sprintf('The "%s" field must be a string date.', $field));
        }

        $formats = [\DateTimeInterface::ATOM, 'Y-m-d\TH:i:sP', '!Y-m-d H:i:s', '!Y-m-d'];

        foreach ($formats as $format) {
            $date = \DateTimeImmutable::createFromFormat($format, $value);

            if ($date instanceof \DateTimeImmutable) {
                return $date;
            }
        }

        try {
            return new \DateTimeImmutable($value);
        } catch (\Exception) {
            throw new BadRequestHttpException(sprintf('The "%s" field contains an invalid date.', $field));
        }
    }
}