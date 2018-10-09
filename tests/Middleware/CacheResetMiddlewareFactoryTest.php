<?php

declare(strict_types=1);

namespace Validus\Tests\Cache\Middleware;

use PHPUnit\Framework\TestCase;
use Psr\Cache\CacheItemPoolInterface;
use Psr\Container\ContainerInterface;
use Validus\Cache\Middleware\CacheResetMiddleware;
use Validus\Cache\Middleware\CacheResetMiddlewareFactory;

class CacheResetMiddlewareFactoryTest extends TestCase
{
    public function testInvoke(): void
    {
        $container = $this->prophesize(ContainerInterface::class);
        $container->get(CacheItemPoolInterface::class)
            ->shouldBeCalledOnce()
            ->willReturn(
                $this->prophesize(CacheItemPoolInterface::class)->reveal()
            );

        $factory = new CacheResetMiddlewareFactory();
        $middleware = $factory($container->reveal());

        $this->assertInstanceOf(CacheResetMiddleware::class, $middleware);
    }
}
