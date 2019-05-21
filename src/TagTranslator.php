<?php

declare(strict_types=1);

namespace Antidot\SymfonyConfigTranslator;

class TagTranslator
{
    public function process(array &$symfonyServices): array
    {
        $arguments = [[]];

        foreach ($symfonyServices ?? [] as $serviceName => $service) {
            foreach ($service['tags'] ?? [] as $tag) {
                if ('console.command' === $tag['name']) {
                    $arguments[] = [
                        'console' => [
                            'commands' => [
                                $tag['command'] => $serviceName,
                            ],
                        ]
                    ];
                }

                if ('event_listener' === $tag['name']) {
                    $arguments[] = [
                        'app-events' => [
                            'event-listeners' => [
                                $tag['event'] => [
                                    $serviceName
                                ],
                            ],
                        ]
                    ];
                }

                unset($symfonyServices[$serviceName]['tags']);
            }
        }

        return array_merge_recursive(...$arguments);
    }
}
