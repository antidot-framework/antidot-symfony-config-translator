<?php

declare(strict_types=1);

namespace Antidot\SymfonyConfigTranslator;

use function array_key_exists;
use function array_shift;
use function is_array;
use function is_string;

class FactoryTranslator
{
    public function process(array &$symfonyFactory): array
    {
        $factories = [];

        foreach ($symfonyFactory['services'] ?? [] as $serviceName => $service) {
            if (!is_array($service)) {
                continue;
            }
            if (array_key_exists('factory', $service) && array_key_exists('arguments', $service)) {
                $factories[$serviceName] = $this->getFactoryWithArguments($symfonyFactory, $serviceName, $service);
                unset($symfonyFactory['services'][$serviceName]);
                continue;
            }
            if (array_key_exists('factory', $service)) {
                $factories[$serviceName] = $service['factory'];
                unset($symfonyFactory['services'][$serviceName]);
                continue;
            }
        }

        return [
            'dependencies' => [
                'factories' => $factories,
            ],
        ];
    }

    private function getFactoryWithArguments(array &$symfonyFactory, string $serviceName, array $service): array
    {
        $config = [];
        if (is_string($service['factory'])) {
            $arguments = (new ArgumentTranslator())->process(
                $symfonyFactory,
                $service
            );
            unset($symfonyFactory['services'][$serviceName]);
            $config = [$service['factory'], '__invoke', $arguments];
        }
        if (is_array($service['factory'])) {
            $factory = array_shift($service['factory']);
            $method = array_shift($service['factory']);
            $arguments = (new ArgumentTranslator())->process(
                $symfonyFactory,
                $service
            );
            unset($symfonyFactory['services'][$serviceName]);
            $config = [$factory, $method, $arguments];
        }

        return $config;
    }
}
