<?php

declare(strict_types=1);

namespace AntidotTest\SymfonyConfigTranslator;

use Antidot\SymfonyConfigTranslator\TagTranslator;
use PHPUnit\Framework\TestCase;

class TagTranslatorTest extends TestCase
{
    /** @var array */
    private $symfonyService;
    /** @var array */
    private $expectedInvokable;
    /** @var array */
    private $obtainedInvokable;

    public function testItShouldTranslateTaggedDependencies(): void
    {
        $this->givenASymfonyStyleTaggedDependency();
        $this->havingTheExpectedStyleDependency();
        $this->whenTheTaggedDependencyIsProcessed();
        $this->thenExpectedDependencyShouldBeEqualToObtainedDependency();
        $this->andThenGivenSymfonyTaggedDependenciessShouldBeUnTagged();
    }

    private function givenASymfonyStyleTaggedDependency(): void
    {
        $this->symfonyService = [
            'some.class' => [
                'tags' => [
                    [
                        'name' => 'console.command',
                        'command' => 'some:command:name'
                    ]
                ]
            ],
            'some.other.class' => [
                'class' => 'Antidot\\SomeOtherClass',
                'tags' => [
                    [
                        'name' => 'event_listener',
                        'event' => 'some.event'
                    ]
                ]
            ],
            'some.other.listener.class' => [
                'class' => 'Antidot\\SomeOtherListenerClass',
                'tags' => [
                    [
                        'name' => 'event_listener',
                        'event' => 'some.event'
                    ]
                ]
            ],
        ];
    }

    private function havingTheExpectedStyleDependency(): void
    {
        $this->expectedInvokable = [
            'console' => [
                'commands' => [
                    'some:command:name' => 'some.class',
                ],
            ],
            'app-events' => [
                'event-listeners' => [
                    'some.event' => [
                        'some.other.class',
                        'some.other.listener.class',
                    ]
                ]
            ],
        ];
    }

    private function whenTheTaggedDependencyIsProcessed(): void
    {
        $translator = new TagTranslator();

        $this->obtainedInvokable = $translator->process($this->symfonyService);
    }

    private function thenExpectedDependencyShouldBeEqualToObtainedDependency(): void
    {
        $this->assertEquals($this->expectedInvokable, $this->obtainedInvokable);
    }

    private function andThenGivenSymfonyTaggedDependenciessShouldBeUnTagged()
    {
        $this->assertEquals([
            'some.class' => [],
            'some.other.class' => [
                'class' => 'Antidot\\SomeOtherClass',
            ],
            'some.other.listener.class' => [
                'class' => 'Antidot\\SomeOtherListenerClass',
            ],
        ], $this->symfonyService);
    }
}
