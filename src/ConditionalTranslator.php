<?php

declare(strict_types=1);

namespace Antidot\SymfonyConfigTranslator;

use function array_key_exists;
use function is_array;
use function str_replace;
use function strpos;

class ConditionalTranslator
{

    public function process(array &$symfonyService): array
    {
        $conditionals = [];

        foreach ($symfonyService['services'] ?? [] as $name => $service) {
            if (!is_array($service)) {
                continue;
            }

            if (array_key_exists('arguments', $service)) {
                $arguments = (new ArgumentTranslator())->process($symfonyService, $service);
                $conditionals[$name] = [
                    'class' => $service['class'] ?? $name,
                    'arguments' => $arguments,
                ];

                unset($symfonyService['services'][$name]);
            }
        }

        return [
            'dependencies' => [
                'conditionals' => $conditionals
            ]
        ];
    }
}
