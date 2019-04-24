<?php

declare(strict_types=1);

namespace AntidotTest\SymfonyConfigTranslator;

use Antidot\SymfonyConfigTranslator\FactoryTranslator;
use PHPUnit\Framework\TestCase;

class FactoryTranslatorTest extends TestCase
{
    /** @var array */
    private $symfonyFactory;
    /** @var array */
    private $expectedFactory;
    /** @var array */
    private $obtainedFactory;

    public function testItShouldTranslateAFactoryTypeDependencies(): void
    {
        $this->givenASymfonyStyleFactory();
        $this->havingTheExpectedStyleFactory();
        $this->whenTheFactoryIsProcessed();
        $this->thenExpectedAliasShouldBeEqualToObtainedAlias();
        $this->andThenGivenSymfonyStyleServiceShouldHaveOnlyOptions();
    }

    private function givenASymfonyStyleFactory(): void
    {
        $this->symfonyFactory = [
            'some.string.value' => 'Hello World!',
            'some.array.value' => [
                'foo' => 'bar',
                'bar' => 'baz',
            ],
            'services' => [
                'some.class' => [
                    'class' => 'Antidot\\SomeClass',
                    'factory' => 'Antidot\\SomeClassFactory',
                    'arguments' => [
                        '$foo' => '@Some\\Other\\Class',
                        '$bar' => '%some.string.value%',
                    ],
                ],
                'some.other.class' => [
                    'class' => 'Antidot\\SomeOtherClass',
                    'factory' => 'Antidot\\SomeOtherClassFactory',
                    'arguments' => [
                        '$foo' => '%some.array.value%',
                        '$bar' => '@Some\\Class',
                    ],
                ],
                'Antidot\\SomeClassInvokable' => [
                    'factory' => ['Antidot\\SomeClassInvokableFactory', 'getInvokable'],
                    'arguments' => [
                        '$foo' => '%some.array.value%',
                        '$bar' => '@Some\\Class',
                        '$baz' => '%some.string.value%',
                    ],
                ],
                'Antidot\\SomeFooClass' => [
                    'factory' => 'Antidot\\SomeFooClassFactory',
                ],
            ],
        ];
    }

    private function havingTheExpectedStyleFactory(): void
    {
        $this->expectedFactory = [
            'dependencies' => [
                'factories' => [
                    'some.class' => [
                        'Antidot\\SomeClassFactory',
                        '__invoke',
                        [
                            'foo' => 'Some\\Other\\Class',
                            'bar' => 'Hello World!',
                        ],
                    ],
                    'some.other.class' => [
                        'Antidot\\SomeOtherClassFactory',
                        '__invoke',
                        [
                            'foo' => [
                                'foo' => 'bar',
                                'bar' => 'baz',
                            ],
                            'bar' => 'Some\\Class',
                        ],
                    ],
                    'Antidot\\SomeClassInvokable' => [
                        'Antidot\\SomeClassInvokableFactory',
                        'getInvokable',
                        [
                            'foo' => [
                                'foo' => 'bar',
                                'bar' => 'baz',
                            ],
                            'bar' => 'Some\\Class',
                            'baz' => 'Hello World!',
                        ],
                    ],
                    'Antidot\\SomeFooClass' => 'Antidot\\SomeFooClassFactory',
                ],
            ],
        ];
    }

    private function whenTheFactoryIsProcessed(): void
    {
        $translator = new FactoryTranslator();

        $this->obtainedFactory = $translator->process($this->symfonyFactory);
    }

    private function thenExpectedAliasShouldBeEqualToObtainedAlias(): void
    {
        $this->assertEquals($this->expectedFactory, $this->obtainedFactory);
    }

    private function andThenGivenSymfonyStyleServiceShouldHaveOnlyOptions(): void
    {
        $this->assertEquals($this->symfonyFactory, [
            'some.string.value' => 'Hello World!',
            'some.array.value' => [
                'foo' => 'bar',
                'bar' => 'baz',
            ],
            'services' => [],
        ]);
    }
}
