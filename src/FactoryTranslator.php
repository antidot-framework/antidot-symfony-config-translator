<?php

declare(strict_types=1);

namespace Antidot\SymfonyConfigTranslator;

use function array_key_exists;
use function array_shift;
use function dump;
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
                if (is_string($service['factory'])) {
                    $factories[$serviceName] = [
                        $service['factory'],
                        '__invoke',
                        (new ArgumentTranslator())->process(
                            $symfonyFactory,
                            $service
                        ),
                    ];
                    unset($symfonyFactory['services'][$serviceName]);
                    continue;
                }
                if (is_array($service['factory'])) {
                    $factory = array_shift($service['factory']);
                    $method = array_shift($service['factory']);
                    $factories[$serviceName] = [
                        $factory,
                        $method,
                        (new ArgumentTranslator())->process(
                            $symfonyFactory,
                            $service
                        ),
                    ];
                    unset($symfonyFactory['services'][$serviceName]);
                    continue;
                }
            }
            if (array_key_exists('factory', $service)) {
                if (is_string($service['factory'])) {
                    $factories[$serviceName] = $service['factory'];
                    unset($symfonyFactory['services'][$serviceName]);
                    continue;
                }

                if (is_array($service['factory'])) {
                    $factories[$serviceName] = $service['factory'];
                    unset($symfonyFactory['services'][$serviceName]);
                    continue;
                }
            }
        }

        return [
            'dependencies' => [
                'factories' => $factories,
            ],
        ];
    }
}
