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

namespace Validus\Tests\Cache;

use Doctrine\DBAL\Connection;
use PDO;
use PHPUnit\Framework\TestCase;
use Prophecy\Prophecy\ObjectProphecy;
use Psr\Cache\CacheItemPoolInterface;
use Psr\Container\ContainerInterface;
use Symfony\Component\Cache\Adapter\ApcuAdapter;
use Symfony\Component\Cache\Adapter\ArrayAdapter;
use Symfony\Component\Cache\Adapter\ChainAdapter;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;
use Symfony\Component\Cache\Adapter\PdoAdapter;
use Symfony\Component\Cache\Adapter\PhpFilesAdapter;
use Symfony\Component\Cache\Adapter\RedisAdapter;
use Validus\Cache\CacheItemPoolFactory;

class CacheItemPoolFactoryTest extends TestCase
{
    /** @var ContainerInterface|ObjectProphecy $container */
    protected $container;

    public function setUp(): void
    {
        $this->container = $this->prophesize(ContainerInterface::class);
        $this->pdo = $this->prophesize(PDO::class);
    }

    public function testEmptyConfigurationBuild(): void
    {
        $pool = $this->buildAdapter([]);
        $this->assertInstanceOf(CacheItemPoolInterface::class, $pool);
        $this->assertInstanceOf(ArrayAdapter::class, $pool);
    }

    public function testArrayAdapter(): void
    {
        $pool = $this->buildAdapter([
            'adapter' => 'array',
            'adapters' => [
                'array' => [
                    'default_lifetime' => 0,
                    'store_serialized' => true,
                ],
            ],
        ]);
        $this->assertInstanceOf(CacheItemPoolInterface::class, $pool);
        $this->assertInstanceOf(ArrayAdapter::class, $pool);
    }

    public function testApcuAdapter(): void
    {
        if (!ApcuAdapter::isSupported()) {
            $this->markTestSkipped('apcu extension is missing');
        }

        $pool = $this->buildAdapter([
            'adapter' => 'apcu',
        ]);

        $this->assertInstanceOf(CacheItemPoolInterface::class, $pool);
        $this->assertInstanceOf(ApcuAdapter::class, $pool);
    }

    public function testFileSystemAdapter(): void
    {
        $pool = $this->buildAdapter([
            'adapter' => 'filesystem',
        ]);

        $this->assertInstanceOf(CacheItemPoolInterface::class, $pool);
        $this->assertInstanceOf(FilesystemAdapter::class, $pool);
    }

    public function testRedisAdapter(): void
    {
        if (!class_exists(\Redis::class)) {
            $this->markTestSkipped('redis extension is missing.');
        }

        $client = $this->prophesize(\Redis::class)->reveal();

        $this->container->has('redis_service')
            ->shouldBeCalledOnce()
            ->willReturn(true);
        $this->container->get('redis_service')
            ->shouldBeCalledOnce()
            ->willReturn($client);

        $pool = $this->buildAdapter([
            'adapter' => 'redis',
            'adapters' => [
                'redis' => [
                    'instance' => 'redis_service',
                ],
            ],
        ]);

        $this->assertInstanceOf(CacheItemPoolInterface::class, $pool);
        $this->assertInstanceOf(RedisAdapter::class, $pool);
    }

    public function testRedisAdapterInstance(): void
    {
        if (!class_exists(\Redis::class)) {
            $this->markTestSkipped('redis extension is missing.');
        }

        $client = $this->prophesize(\Redis::class)->reveal();

        $pool = $this->buildAdapter([
            'adapter' => 'redis',
            'adapters' => [
                'redis' => [
                    'instance' => $client,
                ],
            ],
        ]);

        $this->assertInstanceOf(CacheItemPoolInterface::class, $pool);
        $this->assertInstanceOf(RedisAdapter::class, $pool);
    }

    public function testPdoAdapter(): void
    {
        if (!class_exists(PDO::class)) {
            $this->markTestSkipped('pdo extension is missing.');
        }

        $pdo = $this->prophesize(PDO::class);
        $pdo->getAttribute(PDO::ATTR_ERRMODE)
            ->shouldBeCalledOnce()
            ->willReturn(PDO::ERRMODE_EXCEPTION);

        $this->container->has('pdo_service_alias')
            ->shouldBeCalledOnce()
            ->willReturn(true);
        $this->container->get('pdo_service_alias')
            ->shouldBeCalledOnce()
            ->willReturn($pdo->reveal());

        $pool = $this->buildAdapter([
            'adapter' => 'pdo',
            'adapters' => [
                'pdo' => [
                    'instance' => 'pdo_service_alias',
                ],
            ],
        ]);

        $this->assertInstanceOf(CacheItemPoolInterface::class, $pool);
        $this->assertInstanceOf(PdoAdapter::class, $pool);
    }

    public function testPdoAdapterInstance(): void
    {
        if (!class_exists(PDO::class)) {
            $this->markTestSkipped('pdo extension is missing.');
        }

        $pdo = $this->prophesize(PDO::class);
        $pdo->getAttribute(PDO::ATTR_ERRMODE)
            ->shouldBeCalledOnce()
            ->willReturn(PDO::ERRMODE_EXCEPTION);

        $pool = $this->buildAdapter([
            'adapter' => 'pdo',
            'adapters' => [
                'pdo' => [
                    'instance' => $pdo->reveal(),
                ],
            ],
        ]);

        $this->assertInstanceOf(CacheItemPoolInterface::class, $pool);
        $this->assertInstanceOf(PdoAdapter::class, $pool);
    }

    public function testPdoAdapterAcceptsDoctrineDbalConnection(): void
    {
        $connection = $this->prophesize(Connection::class);

        $pool = $this->buildAdapter([
            'adapter' => 'pdo',
            'adapters' => [
                'pdo' => [
                    'instance' => $connection->reveal(),
                ],
            ],
        ]);

        $this->assertInstanceOf(CacheItemPoolInterface::class, $pool);
        $this->assertInstanceOf(PdoAdapter::class, $pool);
    }

    public function testPdoAdapterAcceptsDnsString(): void
    {
        $db = tempnam(sys_get_temp_dir(), 'foo.db');

        $pool = $this->buildAdapter([
            'adapter' => 'pdo',
            'adapters' => [
                'pdo' => [
                    'dns' => "sqlite:{$db}",
                ],
            ],
        ]);

        $this->assertInstanceOf(CacheItemPoolInterface::class, $pool);
        $this->assertInstanceOf(PdoAdapter::class, $pool);
    }

    public function testPhpFilesAdapter(): void
    {
        $pool = $this->buildAdapter([
            'adapter' => 'php_files',
        ]);

        $this->assertInstanceOf(CacheItemPoolInterface::class, $pool);
        $this->assertInstanceOf(PhpFilesAdapter::class, $pool);
    }

    public function testChainAdapter(): void
    {
        $this->container->has('config')
            ->shouldBeCalledTimes(3)
            ->willReturn(true);
        $this->container->get('config')
            ->shouldBeCalledTimes(3)
            ->willReturn(['cache' => ['adapter' => 'chain']]);
        $pool = (new CacheItemPoolFactory())($this->container->reveal());

        $this->assertInstanceOf(CacheItemPoolInterface::class, $pool);
        $this->assertInstanceOf(ChainAdapter::class, $pool);
    }

    protected function buildAdapter(array $config)
    {
        $this->container->has('config')
            ->shouldBeCalledTimes(2)
            ->willReturn(true);
        $this->container->get('config')
            ->shouldBeCalledTimes(2)
            ->willReturn(['cache' => $config]);

        return (new CacheItemPoolFactory())($this->container->reveal());
    }
}
