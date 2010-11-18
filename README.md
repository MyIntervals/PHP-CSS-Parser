PHP CSS Parser
--------------

A Parser for CSS Files written in PHP. Allows extraction of CSS files into a data structure, manipulation of said structure and output as (optimized) CSS.

## Usage

### Installation

Include the `CSSParser.php` file somewhere in your code using `require_once` (or `include_once`, if you prefer), it does not have any other dependencies.

### Extraction

To use the CSS Parser, create a new instance. The constructor takes the following form:

	new CSSParser($sCssContents, $sCharset = 'utf-8');

The charset is used only if no @charset declaration is found in the CSS file.

To read a file, for example, you’d do the following:

	$oCssParser = new CSSParser(file_get_contents('somefile.css'));
	$oCssDocument = $oCssParser->parse();

The resulting CSS document structure can be manipulated prior to being output.

### Manipulation

The resulting data structure consists mainly of four basic types: `CSSList`, `CSSRuleSet`, `CSSRule` and `CSSValue`. There are two additional types used: `CSSImport` and `CSSCharset` which you won’t use often.

#### CSSList

`CSSList` represents a generic CSS container, most likely containing selectors but it may also contain at-rules, charset declarations, etc. `CSSList` has the following concrete subtypes:

* `CSSDocument` – representing the root of a CSS file.
* `CSSMediaQuery` – represents a subsection of a CSSList that only applies to a output device matching the contained media query.

#### CSSRuleSet

`CSSRuleSet` is a container for individual rules. The most common form of a rule set is one constrained by a selector. The following concrete subtypes exist:

* `CSSAtRule` – for generic at-rules which do not match the ones specifically mentioned like @import, @charset or @media. A common example for this is @font-face.
* `CSSSelector` – a selector; contains an array of selector strings (comma-separated in the CSS) as well as the rules to be applied to the matching elements.

Note: A `CSSList` can contain other `CSSList`s (and `CSSImport`s as well as a `CSSCharset`) while a `CSSRuleSet` can only contain `CSSRule`s.

#### CSSRule

`CSSRule`s just have a key (the rule) and multiple values (the part after the colon in the CSS file). This means the `values` attribute is an array consisting of arrays. The inner level of arrays is comma-separated in the CSS file while the outer level is whitespace-separated.

#### CSSValue

`CSSValue` is an abstract class that only defines the `__toString` method. The concrete subclasses are:

* `CSSSize` – consists of a numeric `size` value and a unit.
* `CSSColor` – colors can be input in the form #rrggbb, #rgb or schema(val1, val2, …) but are alwas stored as an array of ('s' => val1, 'c' => val2, 'h' => val3, …) and output in the second form.
* `CSSString` – this is just a wrapper for quoted strings to distinguish them from keywords; always output with double quotes.
* `CSSURL` – URLs in CSS; always output in URL("") notation.

To access the items stored in a `CSSList` – like the document you got back when calling `$oCssParser->parse()` –, use `getContents()`, then iterate over that collection and use instanceof to check whether you’re dealing with another `CSSList`, a `CSSRuleSet`, a `CSSImport` or a `CSSCharset`.

To append a new item (selector, media query, etc.) to an existing `CSSList`, construct it using the constructor for this class and use the `append($oItem)` method.

If you want to manipulate a `CSSRuleSet`, use the methods `addRule(CSSRule $oRule)`, `getRules()` and `removeRule($mRule)` (which accepts either a CSSRule instance or a rule name; optionally suffixed by a dash to remove all related rules).

#### Convenience methods

There are a few convenience methods on CSSDocument to ease finding, manipulating and deleting rules:

* `getAllSelectors()` – does what it says; no matter how deeply nested your selectors are.
* `getAllRuleSets()` – does what it says; no matter how deeply nested your rule sets are.
* `getAllValues()` – finds all `CSSValue` objects inside `CSSRule`s.

### Use cases

#### Use `CSSParser` to prepend an id to all selectors

	$sMyId = "#my_id";
	$oParser = new CSSParser($sCssContents);
	$oCss = $oParser->parse();
	foreach($oCss->getAllSelectors() as $oSelector) {
		$aSelector = $oSelector->getSelector();
		foreach($aSelector as $iKey => $sSelector) {
			//Loop over all selector parts (the comma-separated strings in a selector) and prepend the id
			$aSelector[$iKey] = "$sMyId $sSelector";
		}
		$oSelector->setSelector($aSelector);
	}
	
#### Shrink all absolute sizes to half

	$oParser = new CSSParser($sCssContents);
	$oCss = $oParser->parse();
	foreach($oCss->getAllValues() as $mValue) {
		if($mValue instanceof CSSSize && !$mValue->isRelative()) {
			$mValue->setSize($mValue->getSize()/2);
		}
	}

#### Remove unwanted rules

	$oParser = new CSSParser($sCssContents);
	$oCss = $oParser->parse();
	foreach($oCss->getAllRuleSets() as $oRuleSet) {
		$oRuleSet->removeRule('font-'); //Note that the added dash will make this remove all rules starting with font- (like font-size, font-weight, etc.) as well as a potential font-rule
		$oRuleSet->removeRule('cursor');
	}

### Output

To output the entire CSS document into a variable, just use `->__toString()`:

	$oCssParser = new CSSParser(file_get_contents('somefile.css'));
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
	          ["aValues":"CSSRule":private]=>
	          array(1) {
	            [0]=>
	            array(1) {
	              [0]=>
	              object(CSSString)#7 (1) {
	                ["sString":"CSSString":private]=>
	                string(10) "CrassRoots"
	              }
	            }
	          }
	          ["bIsImportant":"CSSRule":private]=>
	          bool(false)
	        }
	        ["src"]=>
	        object(CSSRule)#8 (3) {
	          ["sRule":"CSSRule":private]=>
	          string(3) "src"
	          ["aValues":"CSSRule":private]=>
	          array(1) {
	            [0]=>
	            array(1) {
	              [0]=>
	              object(CSSURL)#9 (1) {
	                ["oURL":"CSSURL":private]=>
	                object(CSSString)#10 (1) {
	                  ["sString":"CSSString":private]=>
	                  string(15) "../media/cr.ttf"
	                }
	              }
	            }
	          }
	          ["bIsImportant":"CSSRule":private]=>
	          bool(false)
	        }
	      }
	    }
	    [2]=>
	    object(CSSSelector)#11 (2) {
	      ["aSelector":"CSSSelector":private]=>
	      array(2) {
	        [0]=>
	        string(4) "html"
	        [1]=>
	        string(4) "body"
	      }
	      ["aRules":"CSSRuleSet":private]=>
	      array(1) {
	        ["font-size"]=>
	        object(CSSRule)#12 (3) {
	          ["sRule":"CSSRule":private]=>
	          string(9) "font-size"
	          ["aValues":"CSSRule":private]=>
	          array(1) {
	            [0]=>
	            array(1) {
	              [0]=>
	              object(CSSSize)#13 (2) {
	                ["fSize":"CSSSize":private]=>
	                float(1.6)
	                ["sUnit":"CSSSize":private]=>
	                string(2) "em"
	              }
	            }
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
	    object(CSSSelector)#3 (2) {
	      ["aSelector":"CSSSelector":private]=>
	      array(1) {
	        [0]=>
	        string(7) "#header"
	      }
	      ["aRules":"CSSRuleSet":private]=>
	      array(3) {
	        ["margin"]=>
	        object(CSSRule)#4 (3) {
	          ["sRule":"CSSRule":private]=>
	          string(6) "margin"
	          ["aValues":"CSSRule":private]=>
	          array(4) {
	            [0]=>
	            array(1) {
	              [0]=>
	              object(CSSSize)#5 (2) {
	                ["fSize":"CSSSize":private]=>
	                float(10)
	                ["sUnit":"CSSSize":private]=>
	                string(2) "px"
	              }
	            }
	            [1]=>
	            array(1) {
	              [0]=>
	              object(CSSSize)#6 (2) {
	                ["fSize":"CSSSize":private]=>
	                float(2)
	                ["sUnit":"CSSSize":private]=>
	                string(2) "em"
	              }
	            }
	            [2]=>
	            array(1) {
	              [0]=>
	              object(CSSSize)#7 (2) {
	                ["fSize":"CSSSize":private]=>
	                float(1)
	                ["sUnit":"CSSSize":private]=>
	                string(2) "cm"
	              }
	            }
	            [3]=>
	            array(1) {
	              [0]=>
	              object(CSSSize)#8 (2) {
	                ["fSize":"CSSSize":private]=>
	                float(2)
	                ["sUnit":"CSSSize":private]=>
	                string(1) "%"
	              }
	            }
	          }
	          ["bIsImportant":"CSSRule":private]=>
	          bool(false)
	        }
	        ["font-family"]=>
	        object(CSSRule)#9 (3) {
	          ["sRule":"CSSRule":private]=>
	          string(11) "font-family"
	          ["aValues":"CSSRule":private]=>
	          array(1) {
	            [0]=>
	            array(4) {
	              [0]=>
	              string(7) "Verdana"
	              [1]=>
	              string(9) "Helvetica"
	              [2]=>
	              object(CSSString)#10 (1) {
	                ["sString":"CSSString":private]=>
	                string(9) "Gill Sans"
	              }
	              [3]=>
	              string(10) "sans-serif"
	            }
	          }
	          ["bIsImportant":"CSSRule":private]=>
	          bool(false)
	        }
	        ["color"]=>
	        object(CSSRule)#11 (3) {
	          ["sRule":"CSSRule":private]=>
	          string(5) "color"
	          ["aValues":"CSSRule":private]=>
	          array(1) {
	            [0]=>
	            array(1) {
	              [0]=>
	              string(3) "red"
	            }
	          }
	          ["bIsImportant":"CSSRule":private]=>
	          bool(true)
	        }
	      }
	    }
	  }
	}

#### Output (`__toString()`)

	#header {margin: 10px 2em 1cm 2%;font-family: Verdana, Helvetica, "Gill Sans", sans-serif;color: red !important;}

## To-Do

* More convenience methods [like `selectorsWithElement($sId/Class/TagName)`, `removeSelector($oSelector)`, `attributesOfType($sType)`, `removeAttributesOfType($sType)`]
* Options for output (compact, verbose, etc.)
* Support for @namespace
* Named color support (using `CSSColor` instead of an anonymous string literal)
* Allow for function-like property values other than hsl(), rgb(), rgba(), and url() (like -moz-linear-gradient(), for example).
* Test suite
* Adopt lenient parsing rules
