# Antidot Symfony config translator

## Installation

Using [composer package manager](https://getcomposer.org/download/)

````bash
composer require antidot-fw/symfony-config-translator:dev-master
````

### Antidot framework

````php
<?php

declare(strict_types=1);

use Antidot\SymfonyConfigTranslator\Container\Config\ConfigTranslator;
use Zend\ConfigAggregator\ConfigAggregator;
use Zend\ConfigAggregator\PhpFileProvider;
use Zend\ConfigAggregator\ZendConfigProvider;

// To enable or disable caching, set the `ConfigAggregator::ENABLE_CACHE` boolean in
// `config/autoload/local.php`.
$cacheConfig = [
    'config_cache_path' => 'var/cache/config-cache.php',
];

$aggregator = new ConfigAggregator([
    ...
    new PhpFileProvider(realpath(__DIR__).'/services/{{,*.}prod,{,*.}local,{,*.}dev}.php'),
    new ZendConfigProvider(realpath(__DIR__).'/services/{{,*.}prod,{,*.}local,{,*.}dev}.yaml'),
], $cacheConfig['config_cache_path']);

return (new ConfigTranslator())($aggregator->getMergedConfig());
````

### Zend expressive

````php
````

## Dependency Injection

> https://github.com/antidot-framework/antidot-symfony-config-translator

Auto wired dependency injection system, the only thing you have to do is declare dependencies in `config/autoload/` directory

### Auto-wired Services

<!-- tabs:start -->

#### ** yaml **

````yaml
# config/autoload/dependencies.{prod,local,dev}.yaml
services:
  Full\Qualified\ClassName:  
  some.class:
    class: Full\Qualified\SomeClass

````

#### ** php **

````php
<?php
// config/autoload/dependencies.{prod,dev,local}.php

declare(strict_types=1);

return [
    'services' => [
        Full\Qualified\ClassName::class => [],
        'some.class' => [
            'class' => Full\Qualified\SomeClass::class
        ]
    ]
];
````

<!-- tabs:end -->

### Factory Classes

<!-- tabs:start -->

#### ** yaml **

````yaml
# config/autoload/dependencies.{prod,local,dev}.yaml
services:
  # call to named method
  Full\Qualified\ClassName:  
    factory: [Full\Qualified\ClassNameFactory, 'makeClass']
  # __invoke factory
  some.class:  
    factory: Full\Qualified\ClassNameFactory
    # Factory arguments by default PSR container
    arguments: 
      $foo: '@some.service'
      $bar: '%some.param%'

````

#### ** php **

````php
<?php
// config/autoload/dependencies.{prod,dev,local}.php

declare(strict_types=1);

return [
    'services' => [
        Full\Qualified\ClassName::class => [
            'factory' => [Full\Qualified\ClassNameFactory::class, 'getClass']
        ],
        'some.class' => [
            'factory' => Full\Qualified\ClassNameFactory::class,
            'arguments' => [
                '$foo' => '@some.service',
                '$bar' => '%some.param%',
            ]
        ]
    ]
];
````

<!-- tabs:end -->

### Complex classes with arguments

<!-- tabs:start -->

#### ** yaml **

````yaml
# config/autoload/dependencies.{prod,local,dev}.yaml
services:
  Full\Qualified\ClassName:  
    arguments:
      $foo: '@Full\\Qualified\\ClassNameService'
      $bar: '%config.some_parameter%'

````

#### ** php **

````php
<?php
// config/autoload/dependencies.{prod,dev,local}.php

declare(strict_types=1);

return [
    'services' => [
        Full\Qualified\ClassName::class => [
            'arguments' => [
                '$foo' => '@Full\\Qualified\\ClassNameService',
                '$bar' => 'some.string',
            ]
        ],
    ]
];
````

<!-- tabs:end -->

### Implementing Interfaces

<!-- tabs:start -->

#### ** yaml **

````yaml
# config/autoload/dependencies.{prod,local,dev}.yaml
services:
  Full\Qualified\ClassNameInterface:
    class: Full\Qualified\ClassName
  # Or using aliases
  Full\Qualified\AnotherClassNameInterface:
    alias: 'service.name'
````

#### ** php **

````php
<?php
// config/autoload/dependencies.{prod,dev,local}.php

declare(strict_types=1);

return [
    'services' => [
        Full\Qualified\ClassNameInterface::class => [
            'class' => Full\Qualified\ClassName::class
        ],
        // or using alias
        Full\Qualified\ClassNameInterface::class => [
            'alias' => 'service.name'
        ],
    ]
];
````

<!-- tabs:end -->

## Config

You can use Symfony style config for parameters.

<!-- tabs:start -->

#### ** yaml **

````yaml
# config/autoload/dependencies.{prod,local,dev}.yaml
parameters:
  some: 'value'
  other: [ key: 'keyed value' ]
  more:
    - 'one value'
    - 'another value'
    - [ an_array: 'with value' ]
````

#### ** php **

````php
<?php
// config/autoload/dependencies.{prod,dev,local}.php

declare(strict_types=1);

return [
    'parameters' => [
        'some' => 'value',
        'other' => [
            'key' => 'keyed value'
        ],
        'more' => [
            'one value',
            'another value',
            [
                'an_array' => 'with value'
            ]
        ]    
    ]
];
````

<!-- tabs:end -->

