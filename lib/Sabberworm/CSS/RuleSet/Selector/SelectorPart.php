<?php

namespace Sabberworm\CSS\RuleSet\Selector;

use Sabberworm\CSS\OutputFormat;
use Sabberworm\CSS\Renderable;
use Sabberworm\CSS\Parsing\ParserState;
use Sabberworm\CSS\Value\Value;

abstract class SelectorPart implements Renderable {
	private $iLineNo;

	public function __construct($iLineNo = 0) {
		$this->iLineNo = $iLineNo;
	}

	public static function parse(ParserState $oParserState) {
		if($oParserState->comes('*')) {
			$oParserState->consume('*');
			return new UniversalSelector($oParserState->currentLine());
		}
		if($oParserState->comes('.')) {
			$oParserState->consume('.');
			return new ClassSelector($oParserState->parseIdentifier(false), $oParserState->currentLine());
		}
		if($oParserState->comes('#')) {
			$oParserState->consume('#');
			return new IDSelector($oParserState->parseIdentifier(false), $oParserState->currentLine());
		}
		if($oParserState->comes(':')) {
			$oParserState->consume(':');
			$bIsElement = $oParserState->comes(':');
			if($bIsElement) {
				$oParserState->consume(':');
			}
			return new PseudoSelector(Value::parseIdentifierOrFunction($oParserState), $bIsElement, $oParserState->currentLine());
		}
		if($oParserState->comes('+')) {
			$oParserState->consume('+');
			return new AdjacentSiblingCombinator($oParserState->currentLine());
		}
		if($oParserState->comes('~')) {
			$oParserState->consume('~');
			return new GeneralSiblingCombinator($oParserState->currentLine());
		}
		if($oParserState->comes('>')) {
			$oParserState->consume('>');
			return new ChildCombinator($oParserState->currentLine());
		}
	}

	public function __toString() {
		return $this->render(new OutputFormat());
	}

	public function getLineNo() {
		return $this->iLineNo;
	}
}