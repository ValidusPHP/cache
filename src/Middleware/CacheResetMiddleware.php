<?php

declare(strict_types=1);

namespace Validus\Cache\Middleware;

use Psr\Cache\CacheItemPoolInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Symfony\Component\Cache\ResettableInterface;

class CacheResetMiddleware implements MiddlewareInterface
{
    /** @var CacheItemPoolInterface $itemPool */
    protected $itemPool;

    /**
     * CacheResetMiddleware constructor.
     *
     * @param CacheItemPoolInterface $itemPool
     */
    public function __construct(CacheItemPoolInterface $itemPool)
    {
        $this->itemPool = $itemPool;
    }

    /**
     * Process an incoming server request and return a response, optionally delegating
     * response creation to a handler.
     *
     * @param ServerRequestInterface  $request
     * @param RequestHandlerInterface $handler
     *
     * @return ResponseInterface
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        if ($this->itemPool instanceof ResettableInterface) {
            $this->itemPool->reset();
        }

        return $handler->handle($request);
    }
}
