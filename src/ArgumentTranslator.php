<?php

declare(strict_types=1);

namespace Antidot\SymfonyConfigTranslator;

use function str_replace;
use function strpos;

class ArgumentTranslator
{
    public function process(array $config, array $service): array
    {
        $arguments = [];
        /**
         * @var string $argument
         * @var string $value
         */
        foreach ($service['arguments'] as $argument => $value) {
            $isService = 0 === strpos($value, '@');
            if ($isService) {
                $arguments[str_replace('$', '', $argument)] = str_replace('@', '', $value);
            } else {
                $index = str_replace(['%config%', '%config.', '%'], '', $value);
                $arguments[str_replace('$', '', $argument)] = empty($index)
                    ? $config
                    : $config[$index];
            }
        }

        return $arguments;
    }
}
