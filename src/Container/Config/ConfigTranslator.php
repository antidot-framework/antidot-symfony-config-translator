<?php

declare(strict_types=1);

namespace Antidot\SymfonyConfigTranslator\Container\Config;

use Antidot\SymfonyConfigTranslator\AliasTranslator;
use Antidot\SymfonyConfigTranslator\ConditionalTranslator;
use Antidot\SymfonyConfigTranslator\FactoryTranslator;
use Antidot\SymfonyConfigTranslator\InvokableTranslator;
use DateTimeImmutable;

use function array_merge_recursive;
use function json_decode;
use function json_encode;
use function str_replace;

class ConfigTranslator
{
    public function __invoke(array $config): array
    {
        if (false === empty($config['parameters'])) {
            $config['parameters'] = json_decode(
                str_replace(
                    '%date%',
                    (new DateTimeImmutable())->format('Y-m-d'),
                    json_encode($config['parameters']) ?: ''
                ),
                true
            );
            $config = array_merge_recursive($config, $config['parameters'] ?? []);
            unset($config['parameters']);
        }

        if (false === empty($config['services'])) {
            $config = $this->parse($config);
            unset($config['services']);
        }

        return $config;
    }

    private function parse(array $defaultConfig): array
    {
        return array_merge_recursive(
            (new FactoryTranslator())->process($defaultConfig),
            (new ConditionalTranslator())->process($defaultConfig),
            (new AliasTranslator())->process($defaultConfig['services']),
            (new InvokableTranslator())->process($defaultConfig['services']),
            $defaultConfig
        );
    }
}
