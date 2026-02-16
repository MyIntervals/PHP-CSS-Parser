<?php

declare(strict_types=1);

namespace Sabberworm\CSS\Tests\RuleSet;

use PHPUnit\Framework\TestCase;
use Sabberworm\CSS\OutputFormat;
use Sabberworm\CSS\Parser;
use Sabberworm\CSS\Property\Declaration;
use Sabberworm\CSS\RuleSet\DeclarationBlock;
use Sabberworm\CSS\Settings as ParserSettings;
use Sabberworm\CSS\Value\Size;

/**
 * @covers \Sabberworm\CSS\RuleSet\DeclarationBlock
 */
final class DeclarationBlockTest extends TestCase
{
    /**
     * @test
     */
    public function overrideDeclarations(): void
    {
        $css = '.wrapper { left: 10px; text-align: left; }';
        $parser = new Parser($css);
        $document = $parser->parse();
        $declaration = new Declaration('right');
        $declaration->setValue('-10px');
        $contents = $document->getContents();
        $wrapper = $contents[0];

        self::assertInstanceOf(DeclarationBlock::class, $wrapper);
        self::assertCount(2, $wrapper->getDeclarations());
        $wrapper->setDeclarations([$declaration]);

        $declarations = $wrapper->getDeclarations();
        self::assertCount(1, $declarations);
        self::assertSame('right', $declarations[0]->getPropertyName());
        self::assertSame('-10px', $declarations[0]->getValue());
    }

    /**
     * @test
     */
    public function declarationInsertion(): void
    {
        $css = '.wrapper { left: 10px; text-align: left; }';
        $parser = new Parser($css);
        $document = $parser->parse();
        $contents = $document->getContents();
        $wrapper = $contents[0];

        self::assertInstanceOf(DeclarationBlock::class, $wrapper);

        $leftDeclarations = $wrapper->getDeclarations('left');
        self::assertCount(1, $leftDeclarations);
        $firstLeftDeclaration = $leftDeclarations[0];

        $textDeclarations = $wrapper->getDeclarations('text-');
        self::assertCount(1, $textDeclarations);
        $firstTextDeclaration = $textDeclarations[0];

        $leftPrefixDeclaration = new Declaration('left');
        $leftPrefixDeclaration->setValue(new Size(16, 'em'));

        $textAlignDeclaration = new Declaration('text-align');
        $textAlignDeclaration->setValue(new Size(1));

        $borderBottomDeclaration = new Declaration('border-bottom-width');
        $borderBottomDeclaration->setValue(new Size(1, 'px'));

        $wrapper->addDeclaration($borderBottomDeclaration);
        $wrapper->addDeclaration($leftPrefixDeclaration, $firstLeftDeclaration);
        $wrapper->addDeclaration($textAlignDeclaration, $firstTextDeclaration);

        $declarations = $wrapper->getDeclarations();

        self::assertSame($leftPrefixDeclaration, $declarations[0]);
        self::assertSame($firstLeftDeclaration, $declarations[1]);
        self::assertSame($textAlignDeclaration, $declarations[2]);
        self::assertSame($firstTextDeclaration, $declarations[3]);
        self::assertSame($borderBottomDeclaration, $declarations[4]);

        self::assertSame(
            '.wrapper {left: 16em;left: 10px;text-align: 1;text-align: left;border-bottom-width: 1px;}',
            $document->render()
        );
    }

    /**
     * @return array<string, array{0: non-empty-string, 1: non-empty-string}>
     */
    public static function declarationBlocksWithCommentsProvider(): array
    {
        return [
            'CSS comments with one asterisk' => ['p {color: #000;/* black */}', 'p {color: #000;}'],
            'CSS comments with two asterisks' => ['p {color: #000;/** black */}', 'p {color: #000;}'],
        ];
    }

    /**
     * @test
     * @dataProvider declarationBlocksWithCommentsProvider
     */
    public function canRemoveCommentsFromDeclarationsUsingLenientParsing(
        string $cssWithComments,
        string $cssWithoutComments
    ): void {
        $parserSettings = ParserSettings::create()->withLenientParsing(true);
        $document = (new Parser($cssWithComments, $parserSettings))->parse();

        $outputFormat = (new OutputFormat())->setRenderComments(false);
        $renderedDocument = $document->render($outputFormat);

        self::assertSame($cssWithoutComments, $renderedDocument);
    }

    /**
     * @test
     * @dataProvider declarationBlocksWithCommentsProvider
     */
    public function canRemoveCommentsFromDeclarationsUsingStrictParsing(
        string $cssWithComments,
        string $cssWithoutComments
    ): void {
        $parserSettings = ParserSettings::create()->withLenientParsing(false);
        $document = (new Parser($cssWithComments, $parserSettings))->parse();

        $outputFormat = (new OutputFormat())->setRenderComments(false);
        $renderedDocument = $document->render($outputFormat);

        self::assertSame($cssWithoutComments, $renderedDocument);
    }
}
