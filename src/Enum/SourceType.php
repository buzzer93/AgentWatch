<?php

declare(strict_types=1);

namespace App\Enum;

enum SourceType: string
{
    case RSS = 'RSS';
    case HTML_SCRAPE = 'HTML_SCRAPE';

    public function label(): string
    {
        return match ($this) {
            self::RSS => 'Flux RSS',
            self::HTML_SCRAPE => 'Scraping HTML',
        };
    }
}
