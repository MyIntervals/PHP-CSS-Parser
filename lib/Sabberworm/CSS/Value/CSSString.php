<?php

namespace Sabberworm\CSS\Value;

class CSSString extends PrimitiveValue {

	private $sString;

	public function __construct($sString, $iLineNo = 0) {
		$this->sString = $sString;
		parent::__construct($iLineNo);
	}

	public function setString($sString) {
		$this->sString = $sString;
	}

	public function getString() {
		return $this->sString;
	}

	public function __toString() {
		return $this->render(new \Sabberworm\CSS\OutputFormat());
	}

	public function render(\Sabberworm\CSS\OutputFormat $oOutputFormat) {
		$sQuote = $oOutputFormat->getStringQuotingType();
		$aString = preg_split('//u', $this->sString, null, PREG_SPLIT_NO_EMPTY);
		$iLength = count($aString);
		foreach ($aString as $i => $sChar) {
			if (strlen($sChar) === 1) {
				if ($sChar === $sQuote || $sChar === '\\') {
					// Encode quoting related characters as hex values
				} else {
					$iOrd = ord($sChar);
					if ($iOrd > 31 && $iOrd < 127) {
						// Keep only human readable ascii characters
						continue;
					}
				}
			}

			$sHex = '';
			$sUtf32 = iconv('utf-8', 'utf-32le', $sChar);
			$aBytes = str_split($sUtf32);
			foreach (array_reverse($aBytes) as $sByte) {
				$sHex .= str_pad(dechex(ord($sByte)), 2, '0', STR_PAD_LEFT);
			}
			$sHex = ltrim($sHex, '0');
			if ($i + 1 < $iLength && strlen($sHex) < 6) {
				// Add space after incomplete unicode escape if there can be any confusion
				$sNextChar = $aString[$i + 1];
				if (preg_match('/^[a-fA-F0-9\s]/u', $sNextChar)) {
					$sHex .= ' ';
				}
			}
			$aString[$i] = '\\' . $sHex;
		}

		return $sQuote . implode($aString) . $sQuote;
	}

}