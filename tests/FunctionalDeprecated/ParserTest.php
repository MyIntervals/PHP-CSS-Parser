<?php

declare(strict_types=1);

namespace Sabberworm\CSS\Tests\FunctionalDeprecated;

use PHPUnit\Framework\TestCase;
use Sabberworm\CSS\CSSList\Document;
use Sabberworm\CSS\Parser;
use Sabberworm\CSS\Settings;
use Sabberworm\CSS\Value\Size;
use Sabberworm\CSS\Value\ValueList;

use function Safe\file_get_contents;

/**
 * @covers \Sabberworm\CSS\Parser
 */
final class ParserTest extends TestCase
{
    /**
     * Parses structure for file.
     *
     * @param string $filename
     * @param Settings|null $settings
     */
    public static function parsedStructureForFile($filename, $settings = null): Document
    {
        $filename = \dirname(__DIR__) . "/fixtures/$filename.css";
        $parser = new Parser(file_get_contents($filename), $settings);
        return $parser->parse();
    }

    /**
     * This exercises the deprecated `Size::setSize()` and `getSize()` which have been replaced with `setNumber()` and
     * `getNumber()` which are exercised in the non-deprecated equivalent `TestCase`.
     *
     * @test
     */
    public function slashedValues(): void
    {
        $document = self::parsedStructureForFile('slashed');
        self::assertSame(
            '.test {font: 12px/1.5 Verdana,Arial,sans-serif;border-radius: 5px 10px 5px 10px/10px 5px 10px 5px;}',
            $document->render()
        );
        foreach ($document->getAllValues(null) as $value) {
            if ($value instanceof Size && $value->isLength() && !$value->isRelative()) {
                $value->setSize($value->getSize() * 3);
            }
        }
        foreach ($document->getAllDeclarationBlocks() as $declarationBlock) {
            $fontDeclarations = $declarationBlock->getDeclarations('font');
            $fontDeclaration = $fontDeclarations[0];
            $fontDeclarationValue = $fontDeclaration->getValue();
            self::assertSame(' ', $fontDeclarationValue->getListSeparator());
            $fontDeclarationValueComponents = $fontDeclarationValue->getListComponents();
            $commaList = $fontDeclarationValueComponents[1];
            self::assertInstanceOf(ValueList::class, $commaList);
            $slashList = $fontDeclarationValueComponents[0];
            self::assertInstanceOf(ValueList::class, $slashList);
            self::assertSame(',', $commaList->getListSeparator());
            self::assertSame('/', $slashList->getListSeparator());
            $borderRadiusDeclarations = $declarationBlock->getDeclarations('border-radius');
            $borderRadiusDeclaration = $borderRadiusDeclarations[0];
            $slashList = $borderRadiusDeclaration->getValue();
            self::assertSame('/', $slashList->getListSeparator());
            $slashListComponents = $slashList->getListComponents();
            $secondSlashListComponent = $slashListComponents[1];
            self::assertInstanceOf(ValueList::class, $secondSlashListComponent);
            $firstSlashListComponent = $slashListComponents[0];
            self::assertInstanceOf(ValueList::class, $firstSlashListComponent);
            self::assertSame(' ', $firstSlashListComponent->getListSeparator());
            self::assertSame(' ', $secondSlashListComponent->getListSeparator());
        }
        self::assertSame(
            '.test {font: 36px/1.5 Verdana,Arial,sans-serif;border-radius: 15px 30px 15px 30px/30px 15px 30px 15px;}',
            $document->render()
        );
    }

    /**
     * This exercises the deprecated `Size::setSize()` and `getSize()` which have been replaced with `setNumber()` and
     * `getNumber()` which are exercised in the non-deprecated equivalent `TestCase`.
     *
     * @test
     */
    public function functionSyntax(): void
    {
        $document = self::parsedStructureForFile('functions');
        $expected = "div.main {background-image: linear-gradient(#000,#fff);}\n"
            . '.collapser::before, .collapser::-moz-before, .collapser::-webkit-before {content: "»";font-size: 1.2em;'
            . 'margin-right: .2em;-moz-transition-property: -moz-transform;-moz-transition-duration: .2s;'
            . "-moz-transform-origin: center 60%;}\n"
            . '.collapser.expanded::before, .collapser.expanded::-moz-before,'
            . " .collapser.expanded::-webkit-before {-moz-transform: rotate(90deg);}\n"
            . '.collapser + * {height: 0;overflow: hidden;-moz-transition-property: height;'
            . "-moz-transition-duration: .3s;}\n"
            . '.collapser.expanded + * {height: auto;}';
        self::assertSame($expected, $document->render());

        foreach ($document->getAllValues(null, null, true) as $value) {
            if ($value instanceof Size && $value->isLength()) {
                $value->setSize($value->getSize() * 3);
            }
        }
        $expected = \str_replace(['1.2em', '.2em', '60%'], ['3.6em', '.6em', '180%'], $expected);
        self::assertSame($expected, $document->render());

        foreach ($document->getAllValues(null, null, true) as $value) {
            if ($value instanceof Size && !$value->isRelative() && !$value->isColorComponent()) {
                $value->setSize($value->getSize() * 2);
            }
        }
        $expected = \str_replace(['.2s', '.3s', '90deg'], ['.4s', '.6s', '180deg'], $expected);
        self::assertSame($expected, $document->render());
    }
}
