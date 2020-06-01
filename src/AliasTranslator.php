<?php

declare(strict_types=1);

namespace Antidot\SymfonyConfigTranslator;

use function array_key_exists;
use function is_array;

class AliasTranslator
{
    public function process(array &$symfonyAlias): array
    {
        $aliases = [];

        foreach ($symfonyAlias as $alias => $service) {
            if (!is_array($service) || array_key_exists('factory', $service)) {
                continue;
            }

            if (array_key_exists('alias', $service)) {
                $aliases[$alias] = $service['alias'];
                unset($symfonyAlias[$alias]);
            }
        }

        return [
            'dependencies' => [
                'services' => $aliases,
            ],
        ];
    }
}
