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
		$oSelector->setSelector($sMyId.' '.$aSelector);
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

### Parsed structure

#### Input

	html, body {
		font-size: 1.6em
	}
	
#### Structure (var_dump flavoured)

	object(CSSDocument)#2 (1) {
	  ["aContents":"CSSList":private]=>
	  array(1) {
	    [0]=>
	    object(CSSSelector)#3 (2) {
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
	        object(CSSRule)#4 (3) {
	          ["sRule":"CSSRule":private]=>
	          string(9) "font-size"
	          ["aValues":"CSSRule":private]=>
	          array(1) {
	            [0]=>
	            array(1) {
	              [0]=>
	              object(CSSSize)#5 (2) {
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

#### `__toString()` output

	html, body {font-size: 1.6em;}


## To-Do

* More convenience methods [like `selectorsWithElement($sId/Class/TagName)`, `removeSelector($oSelector)`, `attributesOfType($sType)`, `removeAttributesOfType($sType)`]
* Options for output (compact, verbose, etc.)
* Support for @namespace
* Test suite
