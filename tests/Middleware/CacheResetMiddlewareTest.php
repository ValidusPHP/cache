<?php

declare(strict_types=1);

namespace Validus\Tests\Cache\Middleware;

use PHPUnit\Framework\TestCase;
use Psr\Cache\CacheItemPoolInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Symfony\Component\Cache\ResettableInterface;
use Validus\Cache\Middleware\CacheResetMiddleware;

class CacheResetMiddlewareTest extends TestCase
{
    public function testResettableCacheItemPool(): void
    {
        $pool = $this->prophesize(CacheItemPoolInterface::class);
        $pool->willImplement(ResettableInterface::class);
        $pool->reset()
            ->shouldBeCalledOnce();

        $middleware = new CacheResetMiddleware($pool->reveal());

        $handler = $this->prophesize(RequestHandlerInterface::class);

        $request = $this->prophesize(ServerRequestInterface::class)->reveal();
        $handler->handle(
            $request
        )->shouldBeCalledOnce()->willReturn(
            $this->prophesize(ResponseInterface::class)->reveal()
        );

        $middleware->process($request, $handler->reveal());
    }

    public function testNonResettableCacheItemPool(): void
    {
        $pool = $this->prophesize(CacheItemPoolInterface::class);

        $middleware = new CacheResetMiddleware($pool->reveal());

        $handler = $this->prophesize(RequestHandlerInterface::class);

        $request = $this->prophesize(ServerRequestInterface::class)->reveal();
        $handler->handle(
            $request
        )->shouldBeCalledOnce()->willReturn(
            $this->prophesize(ResponseInterface::class)->reveal()
        );

        $middleware->process($request, $handler->reveal());
    }
}
