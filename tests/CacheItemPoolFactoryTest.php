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

    /** @var \Redis|ObjectProphecy|null $redis */
    protected $redis;

    /** @var PDO|ObjectProphecy|null */
    protected $pdo;

    /** @var Connection|ObjectProphecy */
    protected $connection;

    public function setUp(): void
    {
        $this->container = $this->prophesize(ContainerInterface::class);

        if (class_exists(PDO::class)) {
            $this->pdo = $this->prophesize(PDO::class);
        }

        if (class_exists(\Redis::class)) {
            $this->redis = $this->prophesize(\Redis::class);
        }

        $this->connection = $this->prophesize(Connection::class);
    }

    public function testEmptyConfigurationBuild(): void
    {
        $pool = $this->buildAdapter([]);
        static::assertInstanceOf(CacheItemPoolInterface::class, $pool);
        static::assertInstanceOf(ArrayAdapter::class, $pool);
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
        static::assertInstanceOf(CacheItemPoolInterface::class, $pool);
        static::assertInstanceOf(ArrayAdapter::class, $pool);
    }

    public function testApcuAdapter(): void
    {
        if (!ApcuAdapter::isSupported()) {
            static::markTestSkipped('apcu extension is missing');
        }

        $pool = $this->buildAdapter([
            'adapter' => 'apcu',
        ]);

        static::assertInstanceOf(CacheItemPoolInterface::class, $pool);
        static::assertInstanceOf(ApcuAdapter::class, $pool);
    }

    public function testFileSystemAdapter(): void
    {
        $pool = $this->buildAdapter([
            'adapter' => 'filesystem',
        ]);

        static::assertInstanceOf(CacheItemPoolInterface::class, $pool);
        static::assertInstanceOf(FilesystemAdapter::class, $pool);
    }

    public function testRedisAdapter(): void
    {
        if (!class_exists(\Redis::class)) {
            static::markTestSkipped('redis extension is missing.');
        }

        $client = $this->redis->reveal();

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

        static::assertInstanceOf(CacheItemPoolInterface::class, $pool);
        static::assertInstanceOf(RedisAdapter::class, $pool);
    }

    public function testRedisAdapterInstance(): void
    {
        if (!class_exists(\Redis::class)) {
            static::markTestSkipped('redis extension is missing.');
        }

        $client = $this->redis->reveal();

        $pool = $this->buildAdapter([
            'adapter' => 'redis',
            'adapters' => [
                'redis' => [
                    'instance' => $client,
                ],
            ],
        ]);

        static::assertInstanceOf(CacheItemPoolInterface::class, $pool);
        static::assertInstanceOf(RedisAdapter::class, $pool);
    }

    public function testPdoAdapter(): void
    {
        if (!class_exists(PDO::class)) {
            static::markTestSkipped('pdo extension is missing.');
        }

        $this->pdo->getAttribute(PDO::ATTR_ERRMODE)
            ->shouldBeCalledOnce()
            ->willReturn(PDO::ERRMODE_EXCEPTION);

        $this->container->has('pdo_service_alias')
            ->shouldBeCalledOnce()
            ->willReturn(true);
        $this->container->get('pdo_service_alias')
            ->shouldBeCalledOnce()
            ->willReturn($this->pdo->reveal());

        $pool = $this->buildAdapter([
            'adapter' => 'pdo',
            'adapters' => [
                'pdo' => [
                    'instance' => 'pdo_service_alias',
                ],
            ],
        ]);

        static::assertInstanceOf(CacheItemPoolInterface::class, $pool);
        static::assertInstanceOf(PdoAdapter::class, $pool);
    }

    public function testPdoAdapterInstance(): void
    {
        if (!class_exists(PDO::class)) {
            static::markTestSkipped('pdo extension is missing.');
        }

        $this->pdo->getAttribute(PDO::ATTR_ERRMODE)
            ->shouldBeCalledOnce()
            ->willReturn(PDO::ERRMODE_EXCEPTION);

        $pool = $this->buildAdapter([
            'adapter' => 'pdo',
            'adapters' => [
                'pdo' => [
                    'instance' => $this->pdo->reveal(),
                ],
            ],
        ]);

        static::assertInstanceOf(CacheItemPoolInterface::class, $pool);
        static::assertInstanceOf(PdoAdapter::class, $pool);
    }

    public function testPdoAdapterAcceptsDoctrineDbalConnection(): void
    {
        $pool = $this->buildAdapter([
            'adapter' => 'pdo',
            'adapters' => [
                'pdo' => [
                    'instance' => $this->connection->reveal(),
                ],
            ],
        ]);

        static::assertInstanceOf(CacheItemPoolInterface::class, $pool);
        static::assertInstanceOf(PdoAdapter::class, $pool);
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

        static::assertInstanceOf(CacheItemPoolInterface::class, $pool);
        static::assertInstanceOf(PdoAdapter::class, $pool);
    }

    public function testPhpFilesAdapter(): void
    {
        $pool = $this->buildAdapter([
            'adapter' => 'php_files',
        ]);

        static::assertInstanceOf(CacheItemPoolInterface::class, $pool);
        static::assertInstanceOf(PhpFilesAdapter::class, $pool);
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

        static::assertInstanceOf(CacheItemPoolInterface::class, $pool);
        static::assertInstanceOf(ChainAdapter::class, $pool);
    }

    /**
     * @param array $config
     *
     * @return CacheItemPoolInterface
     */
    protected function buildAdapter(array $config): CacheItemPoolInterface
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
