<?php

declare(strict_types=1);

namespace AntidotTest\SymfonyConfigTranslator;

use Antidot\SymfonyConfigTranslator\AliasTranslator;
use PHPUnit\Framework\TestCase;

class AliasTranslatorTest extends TestCase
{
    /** @var array */
    private $symfonyAlias;
    /** @var array */
    private $expectedAlias;
    /** @var array */
    private $obtainedAlias;

    public function testItShouldTranslateAnAliasTypeDependencies(): void
    {
        $this->givenASymfonyStyleAlias();
        $this->havingTheExpectedStyleAlias();
        $this->whenTheAliasIsProcessed();
        $this->thenExpectedAliasShouldBeEqualToObtainedAlias();
        $this->andThenGivenSymfonyStyleAliasShouldBeEmpty();
    }

    private function givenASymfonyStyleAlias(): void
    {
        $this->symfonyAlias = [
            'some.class.alias' => [
                'alias' => 'some.class.class',
            ],
            'other.class.alias' => [
                'alias' => 'some.other.class',
            ],
        ];
    }

    private function havingTheExpectedStyleAlias(): void
    {
        $this->expectedAlias = [
            'dependencies' => [
                'aliases' => [
                    'some.class.alias' => 'some.class.class',
                    'other.class.alias' => 'some.other.class',
                ],
            ],
        ];
    }

    private function whenTheAliasIsProcessed(): void
    {
        $translator = new AliasTranslator();

        $this->obtainedAlias = $translator->process($this->symfonyAlias);
    }

    private function thenExpectedAliasShouldBeEqualToObtainedAlias(): void
    {
        $this->assertEquals($this->expectedAlias, $this->obtainedAlias);
    }

    private function andThenGivenSymfonyStyleAliasShouldBeEmpty(): void
    {
        $this->assertEmpty($this->symfonyAlias);
    }
}
