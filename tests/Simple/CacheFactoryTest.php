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

namespace Validus\Tests\Cache\Simple;

use PHPStan\Testing\TestCase;
use Psr\Cache\CacheItemPoolInterface;
use Psr\Container\ContainerInterface;
use Psr\SimpleCache\CacheInterface;
use Symfony\Component\Cache\Simple\Psr6Cache;
use Validus\Cache\Simple\CacheFactory;

class CacheFactoryTest extends TestCase
{
    public function testPsr6CacheBridge(): void
    {
        $factory = new CacheFactory();

        $pool = $this->prophesize(CacheItemPoolInterface::class);

        $container = $this->prophesize(ContainerInterface::class);
        $container->get(CacheItemPoolInterface::class)
            ->shouldBeCalled()
            ->willReturn(
                $pool->reveal()
            );

        $cache = $factory($container->reveal());
        $this->assertInstanceOf(CacheInterface::class, $cache);
        $this->assertInstanceOf(Psr6Cache::class, $cache);
    }
}
