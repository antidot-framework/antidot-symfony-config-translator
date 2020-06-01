<?php

declare(strict_types=1);

namespace Antidot\SymfonyConfigTranslator;

class InvokableTranslator
{

    public function process(array &$symfonyService): array
    {
        $invokables = [];

        foreach ($symfonyService as $name => $service) {
            if (is_array($service) && array_key_exists('factory', $service)) {
                continue;
            }

            if (empty($service)) {
                $invokables[$name] = $name;
                unset($symfonyService[$name]);
                continue;
            }

            if (empty($service['arguments']) && isset($service['class'])) {
                $invokables[$name] = $service['class'];
                unset($symfonyService[$name]);
                continue;
            }
        }

        return [
            'dependencies' => [
                'services' => $invokables,
            ],
        ];
    }
}
