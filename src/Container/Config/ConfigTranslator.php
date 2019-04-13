<?php

declare(strict_types=1);

namespace Antidot\SymfonyConfigTranslator\Container\Config;

use DateTimeImmutable;

use function array_key_exists;
use function array_merge_recursive;
use function array_search;
use function json_decode;
use function json_encode;
use function str_replace;

class ConfigTranslator
{
    private const CONFIG = [
        'dependencies' => [
            'aliases' => [],
            'services' => [],
            'invokables' => [],
            'factories' => [],
            'conditionals' => [],
        ],
    ];

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
            $config = array_merge_recursive(
                $config,
                $this->parse($config)
            );
            unset($config['services']);
        }

        return $config;
    }

    private function parse(array $defaultConfig): array
    {
        $config = self::CONFIG;
        foreach ($defaultConfig['services'] ?? [] as $serviceName => $service) {
            if (empty($service)) {
                $config['dependencies']['invokables'][$serviceName] = $serviceName;
                continue;
            }

            if (array_key_exists('alias', $service)) {
                $config['dependencies']['aliases'][$serviceName] = str_replace('@', '', $service['alias']);
                continue;
            }

            if (array_key_exists('factory', $service)) {
                $config['dependencies']['factories'][$serviceName] = $service['factory'][0];
                continue;
            }

            if (array_key_exists('arguments', $service)) {
                $arguments = [];
                /**
                 * @var string $argument
                 * @var string $value
                 */
                foreach ($service['arguments'] as $argument => $value) {
                    $isService = 0 === \strpos('@', $value);

                    if ($isService) {
                        $arguments[str_replace('$', '', $argument)] = $this->searchDependency(
                            $defaultConfig,
                            $value
                        );
                    } else {
                        $index = str_replace(['%config%', '%config.', '%'], '', $value);
                        $arguments[str_replace('$', '', $argument)] = empty($index)
                            ? $defaultConfig
                            : $defaultConfig[$index];
                    }
                }
                $config['dependencies']['conditionals'][$serviceName] = [
                    'class' => $service['class'] ?? $serviceName,
                    'arguments' => $arguments,
                ];
                continue;
            }

            if (array_key_exists('class', $service)) {
                $config['dependencies']['invokables'][$serviceName] = $service['class'];
                continue;
            }
        }

        return $config;
    }

    private function searchDependency(array $config, $value): string
    {
        foreach (self::CONFIG['dependencies'] as $item) {
            $index = array_search($value, $config['dependencies'][$item], true);
            if (false === $index) {
                continue;
            }

            return (string)$index;
        }
    }
}
