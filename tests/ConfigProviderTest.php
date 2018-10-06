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

use PHPStan\Testing\TestCase;
use Validus\Cache\ConfigProvider;

class ConfigProviderTest extends TestCase
{
    /** @var ConfigProvider */
    private $provider;

    public function setUp(): void
    {
        $this->provider = new ConfigProvider();
    }

    public function testInvocationReturnsArray(): array
    {
        $config = ($this->provider)();

        static::assertInternalType('array', $config);

        return $config;
    }

    /**
     * @depends testInvocationReturnsArray
     *
     * @param array $config
     */
    public function testReturnedArrayContainsDependencies(array $config): void
    {
        static::assertArrayHasKey('dependencies', $config);
        static::assertInternalType('array', $config['dependencies']);
    }
}
