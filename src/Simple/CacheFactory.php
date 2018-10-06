<?php
/**
 * This File is Part of the Validus package.
 *
 * @copyright (c) 2018 Validus <https://github.com/ValidusPHP/>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Validus\Cache\Simple;

use Psr\Cache\CacheItemPoolInterface;
use Psr\Container\ContainerInterface;
use Psr\SimpleCache\CacheInterface;
use Symfony\Component\Cache\Simple\Psr6Cache;

class CacheFactory
{
    /**
     * @param ContainerInterface $container
     *
     * @return CacheInterface
     */
    public function __invoke(ContainerInterface $container): CacheInterface
    {
        return new Psr6Cache(
            $container->get(CacheItemPoolInterface::class)
        );
    }
}
