<?php

declare(strict_types=1);

namespace Sabberworm\CSS\Property;

use Sabberworm\CSS\Comment\Comment;
use Sabberworm\CSS\Comment\Commentable;
use Sabberworm\CSS\Comment\CommentContainer;
use Sabberworm\CSS\CSSElement;
use Sabberworm\CSS\OutputFormat;
use Sabberworm\CSS\Parsing\ParserState;
use Sabberworm\CSS\Parsing\UnexpectedEOFException;
use Sabberworm\CSS\Parsing\UnexpectedTokenException;
use Sabberworm\CSS\Position\Position;
use Sabberworm\CSS\Position\Positionable;
use Sabberworm\CSS\Value\RuleValueList;
use Sabberworm\CSS\Value\Value;

use function Safe\preg_match;

/**
 * `Declaration`s just have a string key (the property name) and a 'Value'.
 *
 * In CSS, `Declaration`s are expressed as follows: “key: value[0][0] value[0][1], value[1][0] value[1][1];”
 */
class Declaration implements Commentable, CSSElement, Positionable
{
    use CommentContainer;
    use Position;

    /**
     * @var non-empty-string
     */
    private $propertyName;

    /**
     * @var RuleValueList|string|null
     */
    private $value;

    /**
     * @var bool
     */
    private $isImportant = false;

    /**
     * @param non-empty-string $propertyName
     * @param int<1, max>|null $lineNumber
     * @param int<0, max>|null $columnNumber
     */
    public function __construct(string $propertyName, ?int $lineNumber = null, ?int $columnNumber = null)
    {
        $this->propertyName = $propertyName;
        $this->setPosition($lineNumber, $columnNumber);
    }

    /**
     * @param list<Comment> $commentsBefore
     *
     * @throws UnexpectedEOFException
     * @throws UnexpectedTokenException
     *
     * @internal since V8.8.0
     */
    public static function parse(ParserState $parserState, array $commentsBefore = []): self
    {
        $comments = $commentsBefore;
        $parserState->consumeWhiteSpace($comments);
        $declaration = new self(
            $parserState->parseIdentifier(!$parserState->comes('--')),
            $parserState->currentLine(),
            $parserState->currentColumn()
        );
        $parserState->consumeWhiteSpace($comments);
        $declaration->setComments($comments);
        $parserState->consume(':');
        $value = Value::parseValue($parserState, self::getDelimitersForPropertyValue($declaration->getPropertyName()));
        $declaration->setValue($value);
        $parserState->consumeWhiteSpace();
        if ($parserState->comes('!')) {
            $parserState->consume('!');
            $parserState->consumeWhiteSpace();
            $parserState->consume('important');
            $declaration->setIsImportant(true);
        }
        $parserState->consumeWhiteSpace();
        while ($parserState->comes(';')) {
            $parserState->consume(';');
        }

        return $declaration;
    }

    /**
     * Returns a list of delimiters (or separators).
     * The first item is the innermost separator (or, put another way, the highest-precedence operator).
     * The sequence continues to the outermost separator (or lowest-precedence operator).
     *
     * @param non-empty-string $propertyName
     *
     * @return list<non-empty-string>
     */
    private static function getDelimitersForPropertyValue(string $propertyName): array
    {
        if (preg_match('/^font($|-)/', $propertyName) === 1) {
            return [',', '/', ' '];
        }

        switch ($propertyName) {
            case 'src':
                return [' ', ','];
            default:
                return [',', ' ', '/'];
        }
    }

    /**
     * @param non-empty-string $propertyName
     */
    public function setPropertyName(string $propertyName): void
    {
        $this->propertyName = $propertyName;
    }

    /**
     * @return non-empty-string
     */
    public function getPropertyName(): string
    {
        return $this->propertyName;
    }

    /**
     * @param non-empty-string $propertyName
     *
     * @deprecated in v9.2, will be removed in v10.0; use `setPropertyName()` instead.
     */
    public function setRule(string $propertyName): void
    {
        $this->propertyName = $propertyName;
    }

    /**
     * @return non-empty-string
     *
     * @deprecated in v9.2, will be removed in v10.0; use `getPropertyName()` instead.
     */
    public function getRule(): string
    {
        return $this->propertyName;
    }

    /**
     * @return RuleValueList|string|null
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * @param RuleValueList|string|null $value
     */
    public function setValue($value): void
    {
        $this->value = $value;
    }

    /**
     * Adds a value to the existing value. Value will be appended if a `RuleValueList` exists of the given type.
     * Otherwise, the existing value will be wrapped by one.
     *
     * @param RuleValueList|array<int, RuleValueList> $value
     */
    public function addValue($value, string $type = ' '): void
    {
        if (!\is_array($value)) {
            $value = [$value];
        }
        if (!($this->value instanceof RuleValueList) || $this->value->getListSeparator() !== $type) {
            $currentValue = $this->value;
            $this->value = new RuleValueList($type, $this->getLineNumber());
            if ($currentValue !== null && $currentValue !== '') {
                $this->value->addListComponent($currentValue);
            }
        }
        foreach ($value as $valueItem) {
            $this->value->addListComponent($valueItem);
        }
    }

    public function setIsImportant(bool $isImportant): void
    {
        $this->isImportant = $isImportant;
    }

    public function getIsImportant(): bool
    {
        return $this->isImportant;
    }

    /**
     * @return non-empty-string
     */
    public function render(OutputFormat $outputFormat): string
    {
        $formatter = $outputFormat->getFormatter();
        $result = "{$formatter->comments($this)}{$this->propertyName}:{$formatter->spaceAfterRuleName()}";
        if ($this->value instanceof Value) { // Can also be a ValueList
            $result .= $this->value->render($outputFormat);
        } else {
            $result .= $this->value;
        }
        if ($this->isImportant) {
            $result .= ' !important';
        }
        $result .= ';';
        return $result;
    }

    /**
     * @return array<string, bool|int|float|string|array<mixed>|null>
     *
     * @internal
     */
    public function getArrayRepresentation(): array
    {
        throw new \BadMethodCallException('`getArrayRepresentation` is not yet implemented for `' . self::class . '`');
    }
}
