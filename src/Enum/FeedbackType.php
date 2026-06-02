<?php

declare(strict_types=1);

namespace App\Enum;

enum FeedbackType: string
{
    case BEST_OF_DAY = 'BEST_OF_DAY';
    case USEFUL = 'USEFUL';
    case NOT_USEFUL = 'NOT_USEFUL';
    case BUSINESS_OPPORTUNITY = 'BUSINESS_OPPORTUNITY';
    case CONTENT_IDEA = 'CONTENT_IDEA';
    case READ_LATER = 'READ_LATER';
    case SAVED = 'SAVED';
}