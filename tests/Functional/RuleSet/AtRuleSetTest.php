<?php

declare(strict_types=1);

namespace Sabberworm\CSS\Tests\Functional\RuleSet;

use PHPUnit\Framework\TestCase;
use Sabberworm\CSS\OutputFormat;
use Sabberworm\CSS\Property\Declaration;
use Sabberworm\CSS\RuleSet\AtRuleSet;

/**
 * @covers \Sabberworm\CSS\RuleSet\AtRuleSet
 */
final class AtRuleSetTest extends TestCase
{
    /**
     * @test
     */
    public function renderWithoutArgumentsRendersTypeAndDeclarations(): void
    {
        $subject = new AtRuleSet('font-face');

        self::assertSame('@font-face {}', $subject->render(OutputFormat::create()));
    }

    /**
     * @test
     */
    public function renderWithArgumentsRendersTypeArgumentsAndDeclarations(): void
    {
        $subject = new AtRuleSet('page', ':first');
        $declaration = new Declaration('margin');
        $declaration->setValue('1in');
        $subject->addDeclaration($declaration);

        self::assertSame('@page :first {margin: 1in;}', $subject->render(OutputFormat::create()));
    }
}
