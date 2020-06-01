<?php

declare(strict_types=1);

namespace Antidot\SymfonyConfigTranslator\Container\Config;

use Antidot\SymfonyConfigTranslator\AliasTranslator;
use Antidot\SymfonyConfigTranslator\ConditionalTranslator;
use Antidot\SymfonyConfigTranslator\FactoryTranslator;
use Antidot\SymfonyConfigTranslator\InvokableTranslator;
use Antidot\SymfonyConfigTranslator\TagTranslator;
use DateTimeImmutable;
use RuntimeException;
use Laminas\ConfigAggregator\ConfigAggregator as BaseAggregator;

use function array_replace_recursive;
use function date;
use function file_exists;
use function file_put_contents;
use function get_class;
use function is_string;
use function json_decode;
use function json_encode;
use function sprintf;
use function str_replace;
use function var_export;

class ConfigAggregator extends BaseAggregator
{
    public const CACHE_TEMPLATE = <<< 'EOT'
<?php
/**
 * This configuration cache file was generated by %s
 * at %s
 */
return %s;

EOT;

    private array $config;

    public function __construct(array $providers = [], $cachedConfigFile = null, array $postProcessors = [])
    {
        if (is_string($cachedConfigFile)) {
            if (file_exists($cachedConfigFile)) {
                $this->config = include $cachedConfigFile;
                return;
            }
            parent::__construct($providers);
            $this->checkCacheConfig($cachedConfigFile);
        } else {
            parent::__construct($providers, $cachedConfigFile, $postProcessors);
            $this->config = $this->mergeConfig(parent::getMergedConfig());
        }
    }

    public function getMergedConfig(): array
    {
        return $this->config;
    }

    private function checkCacheConfig(string $cachedConfigFile): void
    {
        $this->config = $this->mergeConfig(parent::getMergedConfig());

        $this->cacheConfig(
            $this->config,
            $cachedConfigFile
        );
    }

    private function parse(array $defaultConfig): array
    {
        $config = array_replace_recursive(
            (new TagTranslator())->process($defaultConfig['services']),
            (new FactoryTranslator())->process($defaultConfig),
            (new ConditionalTranslator())->process($defaultConfig),
            (new InvokableTranslator())->process($defaultConfig['services']),
            (new AliasTranslator())->process($defaultConfig['services']),
            $defaultConfig
        ) ?? [];
        $config = array_replace_recursive($config, $config['dependencies'] ?? []);
        unset($config['dependencies']);

        return $config ?? [];
    }

    private function cacheConfig(array $config, string $cachedConfigFile): void
    {
        if (true === $config['config_cache_enabled']) {
            if ($this->canNotCreateCacheDirectory($cachedConfigFile)) {
                throw new RuntimeException(sprintf(
                    'Cache file "%s" was not created, because cannot create cache directory',
                    $cachedConfigFile
                ));
            }

            file_put_contents($cachedConfigFile, sprintf(
                self::CACHE_TEMPLATE,
                get_class($this),
                date('c'),
                var_export($config, true)
            ));
        }
    }

    private function mergeConfig(array $config): array
    {
        if (false === empty($config['parameters'])) {
            $config['parameters'] = json_decode(
                str_replace(
                    '%date%',
                    (new DateTimeImmutable())->format('Y-m-d'),
                    json_encode($config['parameters']) ?: ''
                ),
                true,
                16,
                JSON_THROW_ON_ERROR
            );
            $config = array_replace_recursive($config, $config['parameters'] ?? []);
            unset($config['parameters']);
        }

        return $this->parse($config ?? []);
    }

    private function canNotCreateCacheDirectory(string $cachedConfigFile): bool
    {
        $concurrentDirectory = dirname($cachedConfigFile);
        if (file_exists($cachedConfigFile) || is_dir($concurrentDirectory)) {
            return false;
        }

        return !mkdir($concurrentDirectory, 0755, true)
            && !is_dir($concurrentDirectory);
    }
}
