<?php

declare(strict_types=1);

namespace Validus\Cache\Middleware;

use Psr\Cache\CacheItemPoolInterface;
use Psr\Container\ContainerInterface;

class CacheResetMiddlewareFactory
{
    /**
     * @param ContainerInterface $container
     *
     * @return CacheResetMiddleware
     */
    public function __invoke(ContainerInterface $container)
    {
        return new CacheResetMiddleware(
            $container->get(CacheItemPoolInterface::class)
        );
    }
}
