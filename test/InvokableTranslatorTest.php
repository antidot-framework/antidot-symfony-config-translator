<?php

declare(strict_types=1);

namespace AntidotTest\SymfonyConfigTranslator;

use Antidot\SymfonyConfigTranslator\InvokableTranslator;
use PHPUnit\Framework\TestCase;

class InvokableTranslatorTest extends TestCase
{
    /** @var array */
    private $symfonyService;
    /** @var array */
    private $expectedInvokable;
    /** @var array */
    private $obtainedInvokable;

    public function testItShouldTranslateAnInvokableTypeDependencies(): void
    {
        $this->givenASymfonyStyleService();
        $this->havingTheExpectedStyleInvokable();
        $this->whenTheServiceIsProcessed();
        $this->thenExpectedInvokableShouldBeEqualToObtainedInvokable();
        $this->andThenGivenSymfonyStyleServiceShouldBeEmpty();
    }

    private function givenASymfonyStyleService(): void
    {
        $this->symfonyService = [
            'some.class' => [
                'class' => 'Antidot\\SomeClass',
            ],
            'some.other.class' => [
                'class' => 'Antidot\\SomeOtherClass',
            ],
            'Antidot\\SomeClassInvokable' => []
        ];
    }

    private function havingTheExpectedStyleInvokable(): void
    {
        $this->expectedInvokable = [
            'dependencies' => [
                'services' => [
                    'some.class' => 'Antidot\\SomeClass',
                    'some.other.class' => 'Antidot\\SomeOtherClass',
                    'Antidot\\SomeClassInvokable' => 'Antidot\\SomeClassInvokable',
                ],
            ],
        ];
    }

    private function whenTheServiceIsProcessed(): void
    {
        $translator = new InvokableTranslator();

        $this->obtainedInvokable = $translator->process($this->symfonyService);
    }

    private function thenExpectedInvokableShouldBeEqualToObtainedInvokable(): void
    {
        $this->assertEquals($this->expectedInvokable, $this->obtainedInvokable);
    }

    private function andThenGivenSymfonyStyleServiceShouldBeEmpty(): void
    {
        $this->assertEmpty($this->symfonyService);
    }
}
