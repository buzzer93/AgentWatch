<?php

declare(strict_types=1);

namespace App\Service\Import;

use App\Entity\DailySelection;
use App\Entity\DailySelectionItem;
use App\Entity\RssItem;
use App\Repository\DailySelectionRepository;
use App\Repository\RssItemRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

final class DailySelectionImporter
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly DailySelectionRepository $dailySelectionRepository,
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
        $selectionDate = $this->requireDate($payload['selectionDate'] ?? null, 'selectionDate');
        $title = $this->requireString($payload, 'title');
        $globalSummary = $this->requireString($payload, 'globalSummary');
        $items = $this->requireItems($payload['items'] ?? null);

        $selection = $this->dailySelectionRepository->findOneBySelectionDate($selectionDate);
        $status = 'updated';

        if (!$selection instanceof DailySelection) {
            $selection = new DailySelection($selectionDate, $title, $globalSummary);
            $this->entityManager->persist($selection);
            $status = 'created';
        } else {
            foreach ($selection->getItems()->toArray() as $existingItem) {
                $selection->removeItem($existingItem);
                $this->entityManager->remove($existingItem);
            }

            $selection
                ->setSelectionDate($selectionDate)
                ->setTitle($title)
                ->setGlobalSummary($globalSummary);
        }

        foreach ($items as $itemPayload) {
            $rssItem = $this->findRssItem($itemPayload['rssItemId'] ?? $itemPayload['itemId'] ?? null);
            $dailySelectionItem = new DailySelectionItem(
                $selection,
                $rssItem,
                $this->requireInt($itemPayload, 'rank'),
                $this->requireString($itemPayload, 'selectionReason'),
            );
            $selection->addItem($dailySelectionItem);
        }

        $this->entityManager->flush();

        return [
            'status' => $status,
            'id' => (int) $selection->getId(),
        ];
    }

    private function findRssItem(mixed $rssItemId): RssItem
    {
        if (!is_int($rssItemId) && !ctype_digit((string) $rssItemId)) {
            throw new BadRequestHttpException('Each item must contain an integer "rssItemId" field.');
        }

        $rssItem = $this->rssItemRepository->find((int) $rssItemId);

        if (!$rssItem instanceof RssItem) {
            throw new NotFoundHttpException('One of the RSS items was not found.');
        }

        return $rssItem;
    }

    /** @return list<array<string, mixed>> */
    private function requireItems(mixed $items): array
    {
        if (!is_array($items)) {
            throw new BadRequestHttpException('The "items" field must be an array.');
        }

        $normalizedItems = [];

        foreach ($items as $item) {
            if (!is_array($item)) {
                throw new BadRequestHttpException('Each item in "items" must be an object.');
            }

            $normalizedItems[] = $item;
        }

        return $normalizedItems;
    }

    private function requireDate(mixed $value, string $field): \DateTimeImmutable
    {
        if (!is_string($value) || trim($value) === '') {
            throw new BadRequestHttpException(sprintf('The "%s" field is required.', $field));
        }

        $formats = ['!Y-m-d', \DateTimeInterface::ATOM, 'Y-m-d\TH:i:sP'];

        foreach ($formats as $format) {
            $date = \DateTimeImmutable::createFromFormat($format, $value);

            if ($date instanceof \DateTimeImmutable) {
                return $date;
            }
        }

        throw new BadRequestHttpException(sprintf('The "%s" field contains an invalid date.', $field));
    }

    /** @param array<string, mixed> $payload */
    private function requireString(array $payload, string $key): string
    {
        if (!array_key_exists($key, $payload) || !is_string($payload[$key]) || trim($payload[$key]) === '') {
            throw new BadRequestHttpException(sprintf('The "%s" field is required.', $key));
        }

        return trim($payload[$key]);
    }

    /** @param array<string, mixed> $payload */
    private function requireInt(array $payload, string $key): int
    {
        if (!array_key_exists($key, $payload)) {
            throw new BadRequestHttpException(sprintf('The "%s" field is required.', $key));
        }

        $value = $payload[$key];

        if (!is_int($value) && !ctype_digit((string) $value)) {
            throw new BadRequestHttpException(sprintf('The "%s" field must be an integer.', $key));
        }

        return (int) $value;
    }
}