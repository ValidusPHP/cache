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
use Prophecy\Prophecy\ObjectProphecy;
use Psr\Cache\CacheItemPoolInterface;
use Psr\Container\ContainerInterface;
use Psr\SimpleCache\CacheInterface;
use Symfony\Component\Cache\Simple\Psr6Cache;
use Validus\Cache\Simple\CacheFactory;

class CacheFactoryTest extends TestCase
{
    /** @var ContainerInterface|ObjectProphecy $container */
    protected $container;

    /** @var CacheItemPoolInterface|ObjectProphecy $pool */
    protected $pool;

    public function setUp(): void
    {
        $this->container = $this->prophesize(ContainerInterface::class);
        $this->pool = $this->prophesize(CacheItemPoolInterface::class);
    }

    public function testPsr6CacheBridge(): void
    {
        $factory = new CacheFactory();

        $this->container->get(CacheItemPoolInterface::class)
            ->shouldBeCalled()
            ->willReturn(
                $this->pool->reveal()
            );

        $cache = $factory($this->container->reveal());
        static::assertInstanceOf(CacheInterface::class, $cache);
        static::assertInstanceOf(Psr6Cache::class, $cache);
    }
}
