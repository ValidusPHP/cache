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

namespace Validus\Cache;

use Doctrine\DBAL\Connection;
use PDO;
use Psr\Cache\CacheItemPoolInterface;
use Psr\Container\ContainerInterface;
use Symfony\Component\Cache\Adapter\AdapterInterface;
use Symfony\Component\Cache\Adapter\ApcuAdapter;
use Symfony\Component\Cache\Adapter\ArrayAdapter;
use Symfony\Component\Cache\Adapter\ChainAdapter;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;
use Symfony\Component\Cache\Adapter\NullAdapter;
use Symfony\Component\Cache\Adapter\PdoAdapter;
use Symfony\Component\Cache\Adapter\PhpFilesAdapter;
use Symfony\Component\Cache\Adapter\RedisAdapter;

class CacheItemPoolFactory
{
    public const DEFAULT_ADAPTERS_CONFIG = [
        'array' => [
            'default_lifetime' => 0,
            'store_serialized' => true,
        ],
        'apcu' => [
            'namespace' => '',
            'default_lifetime' => 0,
            'version' => null,
        ],
        'filesystem' => [
            'namespace' => '',
            'default_lifetime' => 0,
            'directory' => null,
        ],
        'redis' => [
            'instance' => null,
            'namespace' => '',
            'default_lifetime' => 0,
        ],
        'pdo' => [
            'instance' => null,
            'dns' => null,
            'namespace' => '',
            'default_lifetime' => 0,
            'options' => [],
        ],
        'php_files' => [
            'namespace' => '',
            'default_lifetime' => 0,
            'directory' => null,
        ],
        'chain' => [
            'adapters' => ['array'],
        ],
    ];

    /**
     * @param ContainerInterface $container
     *
     * @return CacheItemPoolInterface
     *
     * @throws \Symfony\Component\Cache\Exception\CacheException
     */
    public function __invoke(ContainerInterface $container): CacheItemPoolInterface
    {
        $config = $container->has('config') ? $container->get('config') : [];

        $adapter = $config['cache']['adapter'] ?? 'array';

        return $this->createAdapter($container, $adapter);
    }

    /**
     * @param ContainerInterface $container
     * @param string             $adapter
     *
     * @return AdapterInterface
     *
     * @throws \Symfony\Component\Cache\Exception\CacheException
     */
    protected function createAdapter(ContainerInterface $container, string $adapter): AdapterInterface
    {
        $config = $this->retrieveAdapterConfig($container, $adapter);

        switch ($adapter) {
            case 'array':
                return new ArrayAdapter($config['default_lifetime'], $config['store_serialized']);
            case 'apcu':
                // @codeCoverageIgnoreStart
                return new ApcuAdapter($config['namespace'], $config['default_lifetime'], $config['version']);
                // @codeCoverageIgnoreEnd
            case 'filesystem':
                return new FilesystemAdapter($config['namespace'], $config['default_lifetime'], $config['directory']);
            case 'redis':
                // @codeCoverageIgnoreStart
                $instance = $config['instance'];

                if (\is_string($instance) && $container->has($instance)) {
                    $instance = $container->get($instance);
                }

                return new RedisAdapter($instance, $config['namespace'], $config['default_lifetime']);
                // @codeCoverageIgnoreEnd
            case 'pdo':
                // @codeCoverageIgnoreStart
                $instance = $config['instance'];

                if (\is_string($instance) && $container->has($instance)) {
                    $instance = $container->get($instance);
                } elseif (!$instance instanceof PDO && !$instance instanceof Connection) {
                    $instance = $config['dns'];
                }

                return new PdoAdapter($instance, $config['namespace'], $config['default_lifetime'], $config['options']);
                // @codeCoverageIgnoreEnd
            case 'php_files':
                return new PhpFilesAdapter($config['namespace'], $config['default_lifetime'], $config['directory']);
            case 'chain':
                    $adapters = [];

                    foreach ($config['adapters'] as $pool) {
                        $adapters[] = $this->createAdapter($container, $pool);
                    }

                    return new ChainAdapter($adapters);
            default:
                return new NullAdapter();
        }
    }

    /**
     * Retrieves the config for a specific adapter.
     *
     * @param ContainerInterface $container
     * @param string             $adapter
     *
     * @return array
     */
    protected function retrieveAdapterConfig(ContainerInterface $container, string $adapter): array
    {
        $applicationConfig = $container->has('config') ? $container->get('config') : [];
        $cacheConfig = $applicationConfig['cache'] ?? [];
        $adaptersConfig = $cacheConfig['adapters'] ?? [];

        return array_merge(
            static::DEFAULT_ADAPTERS_CONFIG[$adapter] ?? [],
            $adaptersConfig[$adapter] ?? []
        );
    }
}
