<?php

declare(strict_types=1);

namespace App\Tests\Service\Import;

use App\Service\Import\InternalApiTokenGuard;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

final class InternalApiTokenGuardTest extends TestCase
{
    public function testAcceptsValidToken(): void
    {
        $guard = new InternalApiTokenGuard('my-token');
        $request = new Request();
        $request->headers->set('X-Internal-Token', 'my-token');

        $guard->assertValid($request);

        self::assertTrue(true);
    }

    public function testRejectsMissingToken(): void
    {
        $guard = new InternalApiTokenGuard('my-token');
        $request = new Request();

        $this->expectException(AccessDeniedHttpException::class);

        $guard->assertValid($request);
    }

    public function testRejectsInvalidToken(): void
    {
        $guard = new InternalApiTokenGuard('my-token');
        $request = new Request();
        $request->headers->set('X-Internal-Token', 'wrong-token');

        $this->expectException(AccessDeniedHttpException::class);

        $guard->assertValid($request);
    }
}
