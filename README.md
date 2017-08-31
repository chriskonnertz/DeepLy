# StringCalc

[![Build Status](https://travis-ci.org/chriskonnertz/string-calc.png)](https://travis-ci.org/chriskonnertz/string-calc)
[![GitHub license](https://img.shields.io/badge/license-MIT-blue.svg)](https://raw.githubusercontent.com/chriskonnertz/string-calc/master/LICENSE)

StringCalc is a PHP calculator library for mathematical terms (expressions) passed as strings.

## Installation

Through Composer:

```
composer require chriskonnertz/string-calc
```

From then on you may run `composer update` to get the latest version of this library.

It is possible to use this library without using Composer but then it is necessary to register an 
[autoloader function](https://github.com/php-fig/fig-standards/blob/master/accepted/PSR-0.md#example-implementation).

> This library requires PHP 5.6 or higher.

## Usage example

Here is a minimalistic example of PHP code that calculates a term. It assumes that there is an autoloader.

```php
$stringCalc = new ChrisKonnertz\StringCalc\StringCalc();

$term = '1+2';

$result = $stringCalc->calculate($term);
```

> There is a PHP demo script included. It is located at `dev/demo.php`.

## Motivation

Imagine you are building a web application that allows users to enter mathematical terms in a text field.
 How would you calculate the results of such terms? PHP's [eval](http://php.net/manual/de/function.eval.php) function
 is not the answer. The official documentation recommends not to use this function: 
 _The eval() language construct is very dangerous because it allows execution of arbitrary PHP code. 
 Its use thus is discouraged._ StringCalc has its own parser implementation and therefore is a better and 
 save alternative. StringCalc follows modern coding principles, is extensible and well documented. 
 If you have any suggestions how to improve this library, feel free to discuss them in the [issue](https://github.com/chriskonnertz/string-calc/issues) section.

## The term

Example, we need an example! Here we go: `2*(pi-abs(-0.4))`

This is a mathematical term following syntactical and grammatical rules that StringCalc understands. 
Syntax and grammar of these terms are very similar to what you would write in PHP code. 
To be more precise, there is an intersecting set of syntactical and grammatical rules. 
There are some exceptions but usually you will be able to write terms for StringCalc 
by pretending that you are writing PHP code. 

### Term examples

Here are some unspectacular examples:

```
1+2*3-4
1 + 2 * 3 - 4
pi * 2
PI * 2
abs(1) + min(1,2) * max(1,2,3)
min(1+2, abs(-1))
1 + ((2 - 3) * (5 - 7))
2 * (-3)
```

Here is a list that shows examples with more exotic syntax:

```
1 // A term can consist of just a number
(1+((2))) //  Usage of obsolete brackets is allowed
00001 // Prefixing a number with obsolete zero digits is possible
.1 // Ommiting a zero digit before a period charcter is okay
```

To see a list of all available types of mathematical symbols (parts of a term) follow this link:
[Symbols/Concrete classes](src/ChrisKonnertz/StringCalc/Symbols/Concrete)

## The StringCalc class

The `StringCalc` is the  is the API frontend of the StringCalc library. 
This section describes the public methods of this class.

### Constructor

The constructor has one optional parameter named `$container` that implements the `Container\ContainerInterface`. 
This is the service container used by StringCalc. 
If no argument is passed, the constructor will create a new container object of type `Container\Container` on its own.
The container interface ensures that the container implements the 
[PSR-11 standard](https://github.com/php-fig/fig-standards/blob/master/accepted/PSR-11-container.md). Therefore you can
replace the default container with every other container that implements the PSR-11 standard, but you have to wrap it 
in a wrapper class that makes it compatible to the `Container\Contaner` class. We recommend to avoid the extra afford 
and to extend the `Container\Contaner` class instead.

### calculate

The `calculate()` method is the most important method of this class. 
It expects one parameter of type string called `$term`.
It returns a number of type float or int. We strongly recommend to wrap any calls of this method in a `try-catch`-block 
and to write a `catch`-statement that catches all exceptions of type `Exceptions\StringCalcException`.
 
```php
try {
    $result = $stringCalc->calculate('min()');
} catch (Exceptions\StringCalcException $exception) {
    ... // Handle exception
} catch (\Exception $exception) {
    ... // Handle exception. 
}
```

In the example an exception of class `StringCalcException` will be thrown, 
because the `min` method has to be called with one parameter at least. Exceptions of the type `StringCalcException` 
are usually thrown when grammar or syntax of a given term are invalid. They have two additional properties: `position` 
is an integer telling you where the problem occurred in the term, and `subject` is a string that might contain 
additional data, especially unfiltered user input that you should never print to a website without filtering!
 
### tokenize

The `tokenize($term)` method tokenizes the passed term. It returns an array with the tokens. 
Tokens are the parts of a term or to be more precise the mathematical symbols of a term. A token is an object
that has the `Tokenizer\Token` class as its parent. it implements the `__toString()` method 
so you can do something like this:

```php
$term = '1+(2+max(-3,3))';

$tokens = $stringCalc->tokenize($term);

foreach ($tokens as $token) {
    echo ' '.$token.' ';
}
```

This will print the tokens of the term aka a string representation the whole term. A token consists of tree properties: 
The value, the type and the position. The value is returned by the  `__toString()` method. The type is a constant 
that represents one of these types: characters, words or numbers. 
The position is the index of the value string in the term string. Tokens do not have a semantic meaning.

### parse

The `parse(array $tokens)` method parses an array of tokens. It returns an array of nodes. Internally it uses the
parser aka `Parser\Parser` to parse the tokens. It transforms the tokens to nodes of the syntax tree. These nodes have
a semantic meaning, for example they are numbers or operators 
(take a look at the [Types of symbols](#types-of-symbols) section for a full list of symbol types). 
Also they have a hierarchy, also known as the "tree" in the "syntax tree". 
Brackets in a term create a node in the syntax tree. 

Usage example:

```php
$term = '1+(2+max(-3,3))';

$tokens = $stringCalc->tokenize($term);

$rootNode = $stringCalc->parse($tokens);

$rootNode->traverse(function($node, $level)
{
    echo str_repeat('__', $level).' ['.get_class($node).']<br>';
});
```

This example code will visualize the syntax tree. It uses the `traverse(Closure $callback)` method to go through all
nodes of the tree. The level of the node is visualised by indentation and the name of the class of the node 
object is printed to display the type of the node. A node implements the abstract `Parser\Nodes\AbstractNode` class.
There are three types of nodes: Container nodes (representing what is inside brackets), function nodes (representing
a mathematical function and its arguments) and symbol nodes that represent mathematical symbols of certain types
(numbers, operators, ...). These classes live in the `Parser\Nodes` namespace.
 
### addSymbol

Call the `addSymbol()` if you want to add custom symbols to the list of symbols. It has two parameters. 
The first one named `$symbol` is the symbol object. 
Therefore the object has to extend the abstract class `Symbol\AbstractSymbol`.
The second parameter named `$replaceSymbol` is optional and allows you to replace a symbol in the symbol list. 
If you want to use this parameter you have to pass the full name of the class that you want to replace.

Example:
```php
$symbol = new ExampleClassOne();
$replaceSymbol = ExampleClassTwo::class;

$stringCalc->addSymbol($symbol, $replaceSymbol);
```

The `addSymbol()` method is just a shortcut, you can call this method on the symbol container object as well. 
This object also has a `remove` method that removes a symbol from the container.

If you want to add a new symbol, it cannot directly extend from the `Symbol\AbstractSymbol` class but has to extend one
of the abstract symbol type classes that extend the `Symbol\AbstractSymbol` class. The reason for this constraint is
that these classes have a semantic meaning that is not implemented in the classes themselves but in other classes 
such as the tokenizer and the parser. Take a look at the [Types of symbols](#Types-of-symbols) section to familiarize 
with the symbol type classes.

### getSymbolContainer

The `getSymbolContainer()` method is a getter method that returns the symbol container. 
The symbol container implements the `Symbols\SymbolContainerInterface` and contains instances of all registered
symbols. It has several methods such as `add()`, `remove()`, `size()` and `getAll()`.

### getContainer

The `getContainer()` method is a getter method that returns the service container. 
Take a look at the notes about the constructor for more details. There is not setter method for the container, 
you can only set it via the constructor.

## Types of symbols

A term consists of symbols that are of a specific type. This section lists all available symbol types.

### Numbers

Numbers in a term always consist of digits and may include exactly one period. Good usage examples:

```
0
00
123
4.56
.7
```

Bad usage examples:

```
0.1.2         // Two periods
2.2e3         // "e" will work in PHP code but not in a term
7E-10         // "E" will work in PHP code but not in a term
0xf4c3b00c    // Hexadecimal notation is not allowed
0b10100111001 // Binary notation is not allowed
-1            // This is not a number but the "-" operator plus the number "1"
```

Just for your information: From the tokenizer's point of view, numbers in a term are always positive. 
This means that the tokenizer will split the term `-1` in two parts: `-` and `1`. 

> Notice: The fractional part of a PHP float can only have a limited length. If a number in a term has a longer 
fractional part, the fractional part will be cut somewhere.

#### Number implementation

There is only one concrete number class: `Symbols\Concrete\Number`. 
It extends the abstract class `Symbols\AbstractNumber`. It does not implement any behaviour. 
It is basically a placeholder for concrete numbers in the term.

### Brackets

There are two types of brackets in a term: Opening and closing brackets. There is no other typification. For example 
there can be classes that implement support for parentheses `()` and square brackets `[]` 
but they will be treated equally. Therefore this is a valid term even though it might not be valid 
from a mathematical point of view: `[1+)`

For every opening brackets there must be a closing bracket and vice versa. Good usage examples:
                                                                           
```
(1+1)
(1)
((1+2)*(3+4))
```

Bad usage examples:

```
()      // Empty brackets
(1+1    // Missing closing bracket
1+1)    // Missing opening bracket
)1+1(   // Missing opening bracket for the closing bracket, missing closing bracket for the open bracket
```

#### Bracket implementation

The `Symbols\AbstractBracket` class is the base class for all brackets. It is extended by the abstract classes
`Symbols\AbstractOpeningBracket` and `Symbols\AbstractClosingBracket`. These are extended by concrete classes: 
`Symbols\Concrete\OpeningBracket` and `Symbols\Concrete\ClosingBracket`. These classes do not implement behaviour.

### Constants

Constants in a term typically represent mathematical constants, for example pi.
 
Usage examples:

```
pi
PI
1+pi*2
```

#### Constant implementation

The `Symbols\AbstractConstant` class is the base class for all constants. 
There are several concrete constants that extend this class.

Constants classes have a property called `value` that stores the value of the constant. It is possible to overwrite this
value in a concrete constant class or to overwrite the getter method `getValue()`.

### Operators

Operators in a term can be unary or binary or even both. However, if they are unary, they have to follow
 the prefix notation (example: `-1`). 
 
Unary operator example: `-1`
Binary operator example: `2-1`

#### Operator implementation

The `Symbols\AbstractOperator` class is the base class for all operators. 
There are several concrete operators that extend this class.

Please be aware that operators are closely related to functions. Functions are at least as powerful as operators are.
If an operator does not seem suitable for a purpose, a function might be an appropriate alternative.

Operator classes implement the `operate($leftNumber, $rightNumber)` method. Its parameters represent the operands.
It might be confusing that even if the operator is a unary operator its `operate` method needs to have offer
both parameters. The `$rightNumber` parameter will contain the operand of the unary operation while the left will 
contain 0.

### Functions

Functions in a term represent mathematical functions. Typically the textual representation of a function consists of 
two or more letters, for example: `min`

Good usage examples of using functions:
                                                                           
```
abs(-1)
ABS(-1)
abs(1+abs(2))
min(1,2)
min(1,2,3)
```

Bad usage examples:

```
abs-1 // Missing brackets
min(1,) // Missing argument
```

> Attention: The comma character is used exclusively as a separator of function arguments. 
It is never interpreted as a decimal mark! Example for the former: max(1,2)

#### Function implementation

The `Symbols\AbstractFunction` class is the base class for all functions. 
There are several concrete functions that extend this class.

Please be aware that operators are closely related to functions. Functions are at least as powerful as operators are.
If an operator does not seem suitable for a purpose, a function might be an appropriate alternative.

Function classes implement the `execute(array $arguments)` method. The arguments are passed as an array to this method. 
The size of the arguments array can be 0-n. The implementation of this method is responsible to validate the number of 
arguments. If the number of arguments is improper, throw an `Exceptions\NumberOfArgumentsException`. Example:

```php
if (sizeof($arguments) < 1) {
    throw new NumberOfArgumentsException('Error: Expected at least one argument, none given.');
}
```

The items of the `$arguments` array will always be of type int or float. They will never be null.

### Separators

A separator separates the arguments of a (mathematical) function. 
Out-of-the-box there is one separator symbol with one identifier: `,`
 
Good usage examples:
 
```
max(1,2)
max(1,2,3) 
```

Bad usage examples:
 
```
3+1,2 // Usage out of scope / missusage as decimal mark
max(1,,3) // Missing calculable expression between separators
```

#### Separator implementation

The `Symbols\AbstractSeparator` class is the base class for all separators. 
There is only one concrete functions that extend this class: `Symbols\Concrete\Separator`

## Grammar

This section deals with the grammar for terms that StringCalc can process.

### Grammar vs Implementation

It is important that you notice that the implementations of the 
parser (`Parser\Parser` class) and the calculator (`Calculator\Calculator` class) 
do not mimic the production rules defined below exactly.
So don't be irritated if you compare the actual implementation with the
grammar rules.

### Grammar Definition

````
S := expression

expression := number | constant | function
expression := openingBracket expression closingBracket
expression := [unaryOperator] simpleExpression (operator [unaryOperator] simpleExpression)*

simpleExpression := number | constant | function
simpleExpression := openingBracket expression closingBracket
simpleExpression := simpleExpression (operator [unaryOperator] simpleExpression)*

function := functionBody openingBracket closingBracket
function := functionBody openingBracket expression (argumentSeparator expression)* closingBracket
````

Remember that numbers are always positive! The term "-1" consists of a unary operator followed by a number.

## General notes

* Internally this library uses PHP's mathematical constants, operators and functions to calculate the term. 
Therefore - as a rule of thumb - please transfer your knowledge about mathematics in PHP to the mathematics 
in StringCalc. This is also true about PHP's problems with floating point precision. 
For example `(0.1 + 0.7) * 10` is not 8 or 8.0 but 7.9999999999999991118... in PHP in general and in StringCalc.

* PHP's `intdiv` function is missing, because it is not supported by PHP 5.6. 
In case you need it, you may want to take a look at its [original implementation](https://github.com/chriskonnertz/string-calc/blob/ed7dda7ec9f36b35eec22d2af6c7fbac620bb382/src/ChrisKonnertz/StringCalc/Symbols/Concrete/Functions/IntDivFunction.php).

* This class does not offer support for any other numeral system than the decimal numeral system. 
It is not intended to provide such support so if you need support of other numeral systems 
(such as the binary numeral system) this might not be the library of your choice.
 
* Namespaces in this documentation are relative. For example the namespace `Exceptions\StringCalcException` refers to
`\ChrisKonnertz\StringCalc\Exceptions\StringCalcException`.

* [Releases](https://github.com/chriskonnertz/string-calc/releases) of this library are 100% free of _known_ bugs. 
There are some TODO notes in the code but these refer to possible improvements, not to bugs. 

* General advice: The code of this library is well documented. Therefore, do not hesitate to take a closer 
look at the [implementation](src/ChrisKonnertz/StringCalc).

* The code of this library is formatted according to the code style defined by the 
[PSR-2](https://github.com/php-fig/fig-standards/blob/master/accepted/PSR-2-coding-style-guide.md) standard.

* Status of this repository: _Maintained_. Create an [issue](https://github.com/chriskonnertz/string-calc/issues) 
and you will get a response, usually within 48 hours.
