PHP CSS Parser
--------------

A Parser for CSS Files written in PHP. Allows extraction of CSS files into a data structure, manipulation of said structure and output as (optimized) CSS.

## Usage

### Installation using composer

Add php-css-parser to your composer.json

        {
            "require": {
                "sabberworm/php-css-parser": "*"
            }
        }

### Extraction

To use the CSS Parser, create a new instance. The constructor takes the following form:

	new Sabberworm\CSS\Parser($sText, $sDefaultCharset = 'utf-8');

The charset is used only if no @charset declaration is found in the CSS file.

To read a file, for example, you’d do the following:

	$oCssParser = new Sabberworm\CSS\Parser(file_get_contents('somefile.css'));
	$oCssDocument = $oCssParser->parse();

The resulting CSS document structure can be manipulated prior to being output.

### Manipulation

The resulting data structure consists mainly of five basic types: `CSSList`, `RuleSet`, `Rule`, `Selector` and `Value`. There are two additional types used: `Import` and `Charset` which you won’t use often.

#### CSSList

`CSSList` represents a generic CSS container, most likely containing declaration blocks (rule sets with a selector) but it may also contain at-rules, charset declarations, etc. `CSSList` has the following concrete subtypes:

* `Document` – representing the root of a CSS file.
* `MediaQuery` – represents a subsection of a CSSList that only applies to a output device matching the contained media query.

#### RuleSet

`RuleSet` is a container for individual rules. The most common form of a rule set is one constrained by a selector. The following concrete subtypes exist:

* `AtRule` – for generic at-rules which do not match the ones specifically mentioned like @import, @charset or @media. A common example for this is @font-face.
* `DeclarationBlock` – a RuleSet constrained by a `Selector`; contains an array of selector objects (comma-separated in the CSS) as well as the rules to be applied to the matching elements.

Note: A `CSSList` can contain other `CSSList`s (and `Import`s as well as a `Charset`) while a `RuleSet` can only contain `Rule`s.

#### Rule

`Rule`s just have a key (the rule) and multiple values (the part after the colon in the CSS file). This means the `values` attribute is an array consisting of arrays. The inner level of arrays is comma-separated in the CSS file while the outer level is whitespace-separated.

#### Value

`Value` is an abstract class that only defines the `__toString` method. The concrete subclasses are:

* `Size` – consists of a numeric `size` value and a unit.
* `Color` – colors can be input in the form #rrggbb, #rgb or schema(val1, val2, …) but are alwas stored as an array of ('s' => val1, 'c' => val2, 'h' => val3, …) and output in the second form.
* `String` – this is just a wrapper for quoted strings to distinguish them from keywords; always output with double quotes.
* `URL` – URLs in CSS; always output in URL("") notation.

To access the items stored in a `CSSList` – like the document you got back when calling `$oCssParser->parse()` –, use `getContents()`, then iterate over that collection and use instanceof to check whether you’re dealing with another `CSSList`, a `RuleSet`, a `Import` or a `Charset`.

To append a new item (selector, media query, etc.) to an existing `CSSList`, construct it using the constructor for this class and use the `append($oItem)` method.

If you want to manipulate a `RuleSet`, use the methods `addRule(Rule $oRule)`, `getRules()` and `removeRule($mRule)` (which accepts either a Rule instance or a rule name; optionally suffixed by a dash to remove all related rules).

#### Convenience methods

There are a few convenience methods on Document to ease finding, manipulating and deleting rules:

* `getAllDeclarationBlocks()` – does what it says; no matter how deeply nested your selectors are. Aliased as `getAllSelectors()`.
* `getAllRuleSets()` – does what it says; no matter how deeply nested your rule sets are.
* `getAllValues()` – finds all `Value` objects inside `Rule`s.

### Use cases

#### Use `Parser` to prepend an id to all selectors

	$sMyId = "#my_id";
	$oParser = new Sabberworm\CSS\Parser($sText);
	$oCss = $oParser->parse();
	foreach($oCss->getAllDeclarationBlocks() as $oBlock) {
		foreach($oBlock->getSelectors() as $oSelector) {
			//Loop over all selector parts (the comma-separated strings in a selector) and prepend the id
			$oSelector->setSelector($sMyId.' '.$oSelector->getSelector());
		}
	}
	
#### Shrink all absolute sizes to half

	$oParser = new Sabberworm\CSS\Parser($sText);
	$oCss = $oParser->parse();
	foreach($oCss->getAllValues() as $mValue) {
		if($mValue instanceof CSSSize && !$mValue->isRelative()) {
			$mValue->setSize($mValue->getSize()/2);
		}
	}

#### Remove unwanted rules

	$oParser = new Sabberworm\CSS\Parser($sText);
	$oCss = $oParser->parse();
	foreach($oCss->getAllRuleSets() as $oRuleSet) {
		$oRuleSet->removeRule('font-'); //Note that the added dash will make this remove all rules starting with font- (like font-size, font-weight, etc.) as well as a potential font-rule
		$oRuleSet->removeRule('cursor');
	}

### Output

To output the entire CSS document into a variable, just use `->__toString()`:

	$oCssParser = new Sabberworm\CSS\Parser(file_get_contents('somefile.css'));
	$oCssDocument = $oCssParser->parse();
	print $oCssDocument->__toString();

## Examples

### Example 1 (At-Rules)

#### Input

	@charset "utf-8";

	@font-face {
	  font-family: "CrassRoots";
	  src: url("../media/cr.ttf")
	}
	
	html, body {
		font-size: 1.6em
	}
	
#### Structure (`var_dump()`)

	object(CSSDocument)#2 (1) {
		["aContents":"CSSList":private]=>
		array(3) {
			[0]=>
			object(CSSCharset)#4 (1) {
				["sCharset":"CSSCharset":private]=>
				object(CSSString)#3 (1) {
					["sString":"CSSString":private]=>
					string(5) "utf-8"
				}
			}
			[1]=>
			object(CSSAtRule)#5 (2) {
				["sType":"CSSAtRule":private]=>
				string(9) "font-face"
				["aRules":"CSSRuleSet":private]=>
				array(2) {
					["font-family"]=>
					object(CSSRule)#6 (3) {
						["sRule":"CSSRule":private]=>
						string(11) "font-family"
						["mValue":"CSSRule":private]=>
						object(CSSString)#7 (1) {
							["sString":"CSSString":private]=>
							string(10) "CrassRoots"
						}
						["bIsImportant":"CSSRule":private]=>
						bool(false)
					}
					["src"]=>
					object(CSSRule)#8 (3) {
						["sRule":"CSSRule":private]=>
						string(3) "src"
						["mValue":"CSSRule":private]=>
						object(CSSURL)#9 (1) {
							["oURL":"CSSURL":private]=>
							object(CSSString)#10 (1) {
								["sString":"CSSString":private]=>
								string(15) "../media/cr.ttf"
							}
						}
						["bIsImportant":"CSSRule":private]=>
						bool(false)
					}
				}
			}
			[2]=>
			object(CSSDeclarationBlock)#11 (2) {
				["aSelectors":"CSSDeclarationBlock":private]=>
				array(2) {
					[0]=>
					object(CSSSelector)#12 (2) {
						["sSelector":"CSSSelector":private]=>
						string(4) "html"
						["iSpecificity":"CSSSelector":private]=>
						NULL
					}
					[1]=>
					object(CSSSelector)#13 (2) {
						["sSelector":"CSSSelector":private]=>
						string(4) "body"
						["iSpecificity":"CSSSelector":private]=>
						NULL
					}
				}
				["aRules":"CSSRuleSet":private]=>
				array(1) {
					["font-size"]=>
					object(CSSRule)#14 (3) {
						["sRule":"CSSRule":private]=>
						string(9) "font-size"
						["mValue":"CSSRule":private]=>
						object(CSSSize)#15 (3) {
							["fSize":"CSSSize":private]=>
							float(1.6)
							["sUnit":"CSSSize":private]=>
							string(2) "em"
							["bIsColorComponent":"CSSSize":private]=>
							bool(false)
						}
						["bIsImportant":"CSSRule":private]=>
						bool(false)
					}
				}
			}
		}
	}

#### Output (`__toString()`)

	@charset "utf-8";@font-face {font-family: "CrassRoots";src: url("../media/cr.ttf");}html, body {font-size: 1.6em;}

### Example 2 (Values)

#### Input

	#header {
		margin: 10px 2em 1cm 2%;
		font-family: Verdana, Helvetica, "Gill Sans", sans-serif;
		color: red !important;
	}
	
#### Structure (`var_dump()`)

	object(CSSDocument)#2 (1) {
		["aContents":"CSSList":private]=>
		array(1) {
			[0]=>
			object(CSSDeclarationBlock)#3 (2) {
				["aSelectors":"CSSDeclarationBlock":private]=>
				array(1) {
					[0]=>
					object(CSSSelector)#4 (2) {
						["sSelector":"CSSSelector":private]=>
						string(7) "#header"
						["iSpecificity":"CSSSelector":private]=>
						NULL
					}
				}
				["aRules":"CSSRuleSet":private]=>
				array(3) {
					["margin"]=>
					object(CSSRule)#5 (3) {
						["sRule":"CSSRule":private]=>
						string(6) "margin"
						["mValue":"CSSRule":private]=>
						object(CSSRuleValueList)#10 (2) {
							["aComponents":protected]=>
							array(4) {
								[0]=>
								object(CSSSize)#6 (3) {
									["fSize":"CSSSize":private]=>
									float(10)
									["sUnit":"CSSSize":private]=>
									string(2) "px"
									["bIsColorComponent":"CSSSize":private]=>
									bool(false)
								}
								[1]=>
								object(CSSSize)#7 (3) {
									["fSize":"CSSSize":private]=>
									float(2)
									["sUnit":"CSSSize":private]=>
									string(2) "em"
									["bIsColorComponent":"CSSSize":private]=>
									bool(false)
								}
								[2]=>
								object(CSSSize)#8 (3) {
									["fSize":"CSSSize":private]=>
									float(1)
									["sUnit":"CSSSize":private]=>
									string(2) "cm"
									["bIsColorComponent":"CSSSize":private]=>
									bool(false)
								}
								[3]=>
								object(CSSSize)#9 (3) {
									["fSize":"CSSSize":private]=>
									float(2)
									["sUnit":"CSSSize":private]=>
									string(1) "%"
									["bIsColorComponent":"CSSSize":private]=>
									bool(false)
								}
							}
							["sSeparator":protected]=>
							string(1) " "
						}
						["bIsImportant":"CSSRule":private]=>
						bool(false)
					}
					["font-family"]=>
					object(CSSRule)#11 (3) {
						["sRule":"CSSRule":private]=>
						string(11) "font-family"
						["mValue":"CSSRule":private]=>
						object(CSSRuleValueList)#13 (2) {
							["aComponents":protected]=>
							array(4) {
								[0]=>
								string(7) "Verdana"
								[1]=>
								string(9) "Helvetica"
								[2]=>
								object(CSSString)#12 (1) {
									["sString":"CSSString":private]=>
									string(9) "Gill Sans"
								}
								[3]=>
								string(10) "sans-serif"
							}
							["sSeparator":protected]=>
							string(1) ","
						}
						["bIsImportant":"CSSRule":private]=>
						bool(false)
					}
					["color"]=>
					object(CSSRule)#14 (3) {
						["sRule":"CSSRule":private]=>
						string(5) "color"
						["mValue":"CSSRule":private]=>
						string(3) "red"
						["bIsImportant":"CSSRule":private]=>
						bool(true)
					}
				}
			}
		}
	}

#### Output (`__toString()`)

	#header {margin: 10px 2em 1cm 2%;font-family: Verdana,Helvetica,"Gill Sans", sans-serif;color: red !important;}

## To-Do

* More convenience methods [like `selectorsWithElement($sId/Class/TagName)`, `removeSelector($oSelector)`, `attributesOfType($sType)`, `removeAttributesOfType($sType)`]
* Options for output (compact, verbose, etc.)
* Support for @namespace
* Named color support (using `Color` instead of an anonymous string literal)
* Test suite
* Adopt lenient parsing rules
* Support for @-rules (other than @media) that are CSSLists (to support @-webkit-keyframes)

## Contributors/Thanks to

* [ju1ius](https://github.com/ju1ius) for the specificity parsing code and the ability to expand/compact shorthand properties.
* [GaryJones](https://github.com/GaryJones) for lots of input and [http://css-specificity.info/](http://css-specificity.info/).
* [docteurklein](https://github.com/docteurklein) for output formatting and `CSSList->remove()` inspiration.

## License

PHP-CSS-Parser is freely distributable under the terms of an MIT-style license.

Copyright (c) 2011 Raphael Schweikert, http://sabberworm.com/

Permission is hereby granted, free of charge, to any person obtaining a copy of this software and associated documentation files (the "Software"), to deal in the Software without restriction, including without limitation the rights to use, copy, modify, merge, publish, distribute, sublicense, and/or sell copies of the Software, and to permit persons to whom the Software is furnished to do so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in all copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.
