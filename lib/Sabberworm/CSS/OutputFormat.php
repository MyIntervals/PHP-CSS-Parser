<?php

namespace Sabberworm\CSS;

/**
 * Output format configuration
 */
class OutputFormat {

	/**
	 * String quoting type.
	 * " means double-quote, ' means single-quote
	 * @var string
	 */
	public $sStringQuotingType = '"';

	/**
	 * Output RGB colors in hash notation if possible
	 * @var bool
	 */
	public $bRGBHashNotation = true;

	// Declaration format

	/**
	 * Semicolon after the last rule of a declaration block can be omitted. To do that, set this false.
	 * @var bool
	 */
	public $bSemicolonAfterLastRule = true;

	/**
	 * Spacing
	 * Note that these strings are not sanity-checked: the value should only consist of whitespace
	 * Any newline character will be indented according to the current level.
	 * The triples (After, Before, Between) can be set using a wildcard (e.g. `$oFormat->set('Space*Rules', "\n");`)
	 */

	public $sSpaceAfterRuleName = ' ';

	public $sSpaceBeforeRules = '';
	public $sSpaceAfterRules = '';
	public $sSpaceBetweenRules = '';

	public $sSpaceBeforeBlocks = '';
	public $sSpaceAfterBlocks = '';
	public $sSpaceBetweenBlocks = "\n";

	/**
	 * Printed before and after the comma if a declaration block contains multiple selectors.
	 * @var string
	 */
	public $sSpaceBeforeSelectorSeparator = '';
	/**
	 * Printed after the comma if a declaration block contains multiple selectors.
	 * @var string
	 */
	public $sSpaceAfterSelectorSeparator = ' ';

	/**
	 * Printed before the comma of value lists.
	 * @var string
	 */
	public $sSpaceBeforeListArgumentSeparator = '';

	/**
	 * Printed after the comma of value lists.
	 * @var string
	 */
	public $sSpaceAfterListArgumentSeparator = '';

	public $sSpaceBeforeOpeningBrace = ' ';

	/**
	 * Indentation character(s) per level. Only applicable if newlines are used in any of the spacing settings.
	 * @var string
	 */
	public $sIndentation = "\t";

	/**
	 * Indicates if comments should be kept or thrown away
	 * @var bool
	 */
	private $bKeepComments = false;

	/**
	 * Output exceptions.
	 * @var bool
	 */
	public $bIgnoreExceptions = false;

	private $oFormatter = null;
	private $oNextLevelFormat = null;
	private $iIndentationLevel = 0;

	/**
	 * @param $sName
	 *
	 * @return mixed|null
	 */
	public function get($sName) {
		$aVarPrefixes = array('a', 's', 'm', 'b', 'f', 'o', 'c', 'i');
		foreach($aVarPrefixes as $sPrefix) {
			$sFieldName = $sPrefix.ucfirst($sName);
			if(isset($this->$sFieldName)) {
				return $this->$sFieldName;
			}
		}
		return null;
	}

	/**
	 * @param $aNames
	 * @param $mValue
	 *
	 * @return $this|false
	 */
	public function set($aNames, $mValue)
	{
		$aVarPrefixes = array('a', 's', 'm', 'b', 'f', 'o', 'c', 'i');
		if (is_string($aNames) && strpos($aNames, '*') !== false) {
			$aNames = array(
				str_replace('*', 'Before', $aNames),
				str_replace('*', 'Between', $aNames),
				str_replace('*', 'After', $aNames),
			);
		} elseif (!is_array($aNames)) {
			$aNames = array($aNames);
		}
		foreach ($aVarPrefixes as $sPrefix) {
			$bDidReplace = false;
			foreach ($aNames as $sName) {
				$sFieldName = $sPrefix.ucfirst($sName);
				if (isset($this->$sFieldName)) {
					$this->$sFieldName = $mValue;
					$bDidReplace = true;
				}
			}
			if ($bDidReplace) {
				return $this;
			}
		}
		// Break the chain so the user knows this option is invalid
		return false;
	}

	/**
	 * @param string $sMethodName
	 * @param array $aArguments
	 *
	 * @return false|mixed|null|OutputFormat
	 * @throws \Exception
	 */
	public function __call($sMethodName, $aArguments)
	{
		if (strpos($sMethodName, 'set') === 0) {
			return $this->set(substr($sMethodName, 3), $aArguments[0]);
		} elseif (strpos($sMethodName, 'get') === 0) {
			return $this->get(substr($sMethodName, 3));
		} elseif (method_exists('\\Sabberworm\\CSS\\OutputFormatter', $sMethodName)) {
			return call_user_func_array(array($this->getFormatter(), $sMethodName), $aArguments);
		} else {
			throw new \Exception('Unknown OutputFormat method called: '.$sMethodName);
		}
	}

	/**
	 * Sets indentation to a number of tabs
	 *
	 * @param int $iNumber [default=1] Number of tabs to indent with
	 *
	 * @return $this|false
	 */
	public function indentWithTabs($iNumber = 1)
	{
		return $this->setIndentation(str_repeat("\t", $iNumber));
	}

	/**
	 * Sets indentation to a number of spaces
	 * @param int $iNumber [default=2] Number of spaces to indent with
	 *
	 * @return $this|false
	 */
	public function indentWithSpaces($iNumber = 2)
	{
		return $this->setIndentation(str_repeat(" ", $iNumber));
	}

	/**
	 * @return null|OutputFormat
	 */
	public function nextLevel()
	{
		if ($this->oNextLevelFormat === null) {
			$this->oNextLevelFormat = clone $this;
			$this->oNextLevelFormat->iIndentationLevel++;
			$this->oNextLevelFormat->oFormatter = null;
		}
		return $this->oNextLevelFormat;
	}

	/**
	 * Activates exception ignoring
	 */
	public function beLenient()
	{
		$this->bIgnoreExceptions = true;
	}

	/**
	 * @return null|OutputFormatter
	 */
	public function getFormatter()
	{
		if ($this->oFormatter === null) {
			$this->oFormatter = new OutputFormatter($this);
		}
		return $this->oFormatter;
	}

	/**
	 * @return int
	 */
	public function level()
	{
		return $this->iIndentationLevel;
	}

	/**
	 * Indicates if comments should be kept or thrown away
	 * @param bool $toggle
	 * @return $this
	 */
	public function setKeepComments($toggle)
	{
		$this->bKeepComments = $toggle;
		return $this;
	}

	/**
	 * Indicates if comments should be kept or thrown away
	 * @return bool
	 */
	public function getKeepComments()
	{
		return $this->bKeepComments;
	}

	/**
	 * @return OutputFormat
	 */
	public static function create()
	{
		return new OutputFormat();
	}

	/**
	 * @return OutputFormat|false
	 */
	public static function createCompact()
	{
		return self::create()
			->set('Space*Rules', "")
			->set('Space*Blocks', "")
			->setSpaceAfterRuleName('')
			->setSpaceBeforeOpeningBrace('')
			->setSpaceAfterSelectorSeparator('');
	}

	/**
	 * @return OutputFormat|false
	 */
	public static function createPretty()
	{
		return self::create()
			->setKeepComments(true)
			->set('Space*Rules', "\n")
			->set('Space*Blocks', "\n")
			->setSpaceBetweenBlocks("\n\n")
			->set(
				'SpaceAfterListArgumentSeparator',
				array(
					'default' => '',
					',' => ' '
				)
		);
	}
}
