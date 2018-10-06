# Validus Cache

Provides caching implementations for zend expressive projects.

---
[![Packagist](https://img.shields.io/packagist/dm/validus/cache.svg)](https://packagist.org/packages/validus/cache) [![GitHub license](https://img.shields.io/github/license/ValidusPHP/cache.svg)](https://github.com/ValidusPHP/cache/blob/master/LICENSE) [![Build Status](https://travis-ci.org/ValidusPHP/cache.svg?branch=master)](https://travis-ci.org/ValidusPHP/cache) [![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/ValidusPHP/cache/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/ValidusPHP/cache/?branch=master) [![Code Intelligence Status](https://scrutinizer-ci.com/g/ValidusPHP/translation/badges/code-intelligence.svg?b=master)](https://scrutinizer-ci.com/code-intelligence) [![Coverage Status](https://coveralls.io/repos/github/ValidusPHP/cache/badge.svg)](https://coveralls.io/github/ValidusPHP/cache)

---

PSR-6 and PSR-16 Cache factories for PSR-11 with Zend configuration provider.

## Installation

The easiest way to install this package is through composer:
```bash
$ composer require validus/cache
```

## Configuration
  
A complete example configuration can be found in example/full-config.php. 
Please note that the values in there are the defaults, and don't have to be supplied when you are not changing them. Keep your own configuration as minimal as possible. A minimal configuration can be found in example/simple-config.php

If your application uses the zend-component-installer Composer plugin, your configuration is complete; the shipped `Validus\Cache\ConfigProvider` registers the cache service.

#### Accessing the translator 
you can access the cache implementation via the container :
```php
use Psr\Cache\CacheItemPoolInterface;
use Psr\SimpleCache\CacheInterface;

// $pool instanceof CacheItemPoolInterface
$pool = $container->get(CacheItemPoolInterface::class);

// $cache instanceof CacheInterface
$cache = $container->get(CacheInterface::class);
```

