<?php

declare(strict_types=1);

namespace AntidotTest\SymfonyConfigTranslator;

use Antidot\SymfonyConfigTranslator\ConditionalTranslator;
use PHPUnit\Framework\TestCase;

class ConditionalTranslatorTest extends TestCase
{
    /** @var array */
    private $symfonyService;
    /** @var array */
    private $expectedConditional;
    /** @var array */
    private $obtainedConditional;

    public function testItShouldTranslateAConditionalTypeDependencies(): void
    {
        $this->givenASymfonyStyleService();
        $this->havingTheExpectedStyleConditional();
        $this->whenTheServiceIsProcessed();
        $this->thenExpectedConditionalShouldBeEqualToObtainedConditional();
        $this->andThenGivenSymfonyStyleServiceShouldHaveOnlyOptions();
    }

    private function givenASymfonyStyleService(): void
    {
        $this->symfonyService = [
            'some.string.value' => 'Hello World!',
            'some.array.value' => [
                'foo' => 'bar',
                'bar' => 'baz',
            ],
            'services' => [
                'some.class' => [
                    'class' => 'Antidot\\SomeClass',
                    'arguments' => [
                        '$foo' => '@Some\\Other\\Class',
                        '$bar' => '%some.string.value%',
                    ],
                ],
                'some.other.class' => [
                    'class' => 'Antidot\\SomeOtherClass',
                    'arguments' => [
                        '$foo' => '%some.array.value%',
                        '$bar' => '@Some\\Class',
                    ],
                ],
                'Antidot\\SomeClassInvokable' => [
                    'arguments' => [
                        '$foo' => '%some.array.value%',
                        '$bar' => '@Some\\Class',
                        '$baz' => '%some.string.value%',
                    ],
                ],
            ],
        ];
    }

    private function havingTheExpectedStyleConditional(): void
    {
        $this->expectedConditional = [
            'dependencies' => [
                'conditionals' => [
                    'some.class' => [
                        'class' => 'Antidot\\SomeClass',
                        'arguments' => [
                            'foo' => 'Some\\Other\\Class',
                            'bar' => 'Hello World!',
                        ],
                    ],
                    'some.other.class' => [
                        'class' => 'Antidot\\SomeOtherClass',
                        'arguments' => [
                            'foo' => [
                                'foo' => 'bar',
                                'bar' => 'baz',
                            ],
                            'bar' => 'Some\\Class',
                        ],
                    ],
                    'Antidot\\SomeClassInvokable' => [
                        'class' => 'Antidot\\SomeClassInvokable',
                        'arguments' => [
                            'foo' => [
                                'foo' => 'bar',
                                'bar' => 'baz',
                            ],
                            'bar' => 'Some\\Class',
                            'baz' => 'Hello World!',
                        ],
                    ],
                ],
            ],
        ];
    }

    private function whenTheServiceIsProcessed(): void
    {
        $translator = new ConditionalTranslator();

        $this->obtainedConditional = $translator->process($this->symfonyService);
    }

    private function thenExpectedConditionalShouldBeEqualToObtainedConditional(): void
    {
        $this->assertEquals($this->expectedConditional, $this->obtainedConditional);
    }

    private function andThenGivenSymfonyStyleServiceShouldHaveOnlyOptions(): void
    {
        $this->assertEquals($this->symfonyService, [
            'some.string.value' => 'Hello World!',
            'some.array.value' => [
                'foo' => 'bar',
                'bar' => 'baz',
            ],
            'services' => [],
        ]);
    }
}
