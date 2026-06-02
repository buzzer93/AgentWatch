<?php

declare(strict_types=1);

namespace App\Service\Import;

use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

final class InternalApiTokenGuard
{
    public function __construct(
        #[Autowire('%env(string:APP_INTERNAL_TOKEN)%')]
        private readonly string $expectedToken,
    ) {
    }

    public function assertValid(Request $request): void
    {
        $providedToken = trim((string) $request->headers->get('X-Internal-Token', ''));

        if ($this->expectedToken === '' || $providedToken === '' || !hash_equals($this->expectedToken, $providedToken)) {
            throw new AccessDeniedHttpException('Invalid internal API token.');
        }
    }
}