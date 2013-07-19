# ToObject

ToObject is a library written in PHP. It wraps around a few primitives types and provides a more OOP-like feel by doing so. ToObject uses conventions which derive from several other languages such as C (C zero-terminated strings) and Ruby. In fact, the way in which Ruby Strings(-literals), Arrays and Hashes are used is the main inspiration for creating this library. ToObject thus contains a class for Strings, Arrays (numerical indices) and Hashes (non-numerical indices).

ToObject consists of four primarily classes which together provide all features. It is important to note that all of these classes rely on each other and thus should not be used separately. The four classes are:

	- ObjectClass
	- ArrayClass
	- HashClass
	- StringClass

Of those four, ObjectClass functions as a Base class. It's what java.lang.Object is in Java and what Object (or BasicObject) is in Ruby. However, ObjectClass is not as fancy as the other two :).
The other three classes are used for arrays, hashes and strings respectively.

##tl;dr
How do I get this to work?  
All you need are the contents of the `src` folder. Include `ToObject.php` and you're good to go. Be sure to use `use` and/or `namespace` though.

## StringClass
Starting with the most easy one, StringClass. One only has to instantiate a StringClass by passing a string to the constructor. 

	$str = new StringClass("hello, World!");

This will create a new instance with the content "hello, World!".  
One could then call capitalize to make it "Hello, World!".

	$str->capitalize(); // Hello, World!

Consult the class documentation to see what methods this class has.

## Collections
Slightly more advanced are the Array- and Hash- Classes, as they both deal with collections of data. And because they both deal with collections of data, something needs to distinct them from each other. An easy solution is to distinct in what is passed to the constructor and that is exactly how it is done.

An ArrayClass needs to be passed an array to its constructor, whereas a HashClass needs to be passed an instance of stdClass. This, at the same time, solves another problem.  
As mentioned above, both are collections of data. It would be nice for ToObject to recognize the elements inside the collection and determine whether it should be a nested set of ToObject classes.

Lets just jump into an example for clarity. Imagine a database just returned a few records of employees. In native php, fetched and well, it would (or at least could) look like this.

	array(
		0 => stdClass(
			[name] => john
			[age] => 34
		)
		1 => stdClass(
			[name] => sarah
			[age] => 40
		)
	)

**Note however that these records already are of the stdClass type, this is for the purpose of the example.**

Instantiating an ArrayClass of this result-set would do a few things.  
First, ArrayClass iterates through its collection and determines these elements are stdClasses. Whenever it encounters an stdClass, it will instantiate a HashClass for it.  
Then, HashClass also iterates through its collection and determines it has a string for "name". When encountering a string, it will instantiate a StringClass for it. Integers are not dealt with and will therefore remain as is. After all progressing is done, the result-set will look like this.

	ArrayClass (
		0 => HashClass (
			[name] => StringClass: john
			[age] => 34
		)
		1 => HashClass (
			[name] => StringClass: sarah
			[age] => 40
		)
	)

Accessing the elements is as if it really just is an array or stdClass:

	$person = $arrayclass[0]; // HashClass of john/34
	$person->name; // StringClass "john"

In short this can be formulated as: "Both ArrayClass and HashClass iterate through their elements and try to determine whether any of these elements is to be another instance of ToObject". This ensures recursive functionality.

Consult the source-code of "ObjectClass" and see the "builder" method for information on how this is determined.

## A Note On HashClass
Though it is called a hash, it actually is not.
A real hash, like a HashTable, computes a key for its data and stores its data in an internal array. HashClass DOES NOT DO THIS. HashClass is merely a key-value storage with no extra internal computations. Internally it uses an stdClass and uses its getters and setters. HashClass basically adds functionalities to the stdClass by applying methods.

# Communal Methods & Similarities

ToObject allows method chaining - which is also known as the [fluent interface](). Meaning that most methods will return either the current instance or a new instance of the class of which they reside, depending on what the method needs to achieve.

Taking the last example of the [Collections](## Collections) section. Retrieving the name can also be done in one step rather than to. What was written as:

	$person = $arrayclass[0]; // HashClass of john/34
	$person->name; // StringClass "john"

Can also be written as:

	$arrayclass[0]->name; // StringClass "john"

One more example to get the hang of it.
As you might have noticed, the names are all lowercase. Of course this should be capitalized. Here it is:

	$arrayclass[0]->name->capitalize(); // StringClass "John"

Enough about the fluent interface. There are some methods they have in common, these are:

* to\_s():  
	Returns a string presentation of the object.
* to\_native([$recursive = true]):  
	Returns (optionally recursive) a native php type. Meaning an ArrayClass will return an array(), HashClass will return an stdClass and StringClass a string.
* \_\_new\_\_():  
	Builds a new instance of the given type. However, this is not a public method.

Some classes have a few more of these conversion methods, specifically tied to their own type.

**ArrayClass**  

* to\_hsh():  
	If the array is in the format of "[ [key1, val1], [key2, val2] ]", to_hsh() will return a HashClass in the form of ":key1 => val1, :key2 => val2".

**HashClass**

* to_a():  
	Returns an ArrayClass in the form of "[ [key1, val1], [key2, val2] ]".
* to\_native\_a([$recursive = true]):  
	Returns a native array in the form of "[ key1 => val1, key2 => val2 ]".

Last but not least, multibyte strings cannot be forgotten. ToObject makes use of the mb\_*() functions. Assuming they do their job, only UTF-8 is tested, which is also the default encoding for instantiating any object.
For the methods returning a new instance of the object, the new object will have the same encoding as the old one is instantiated with.
Instantiating an object with a different encoding is easy:

	$object = new <any of the classes>($data [ , $encoding = 'UTF-8' ]);

# Prerequisites

In order to use this library, [php5.4.0][] or greater is needed. At the time of writing, [php5.4.6][] is the newest version.

PHP needs to be compiled with these options:

    --enable-mbstring
    --with-pcre-regex
    --enable-utf8 *
    --enable-unicode-properties *
    
\*) These actually are not PHP options, but PCRE options. One would have to (re-)compile PCRE with these flags in order to use [these](http://php.net/manual/en/regexp.reference.unicode.php) goods.

Further info on compile options:

- [mbstring](http://php.net/manual/en/intro.mbstring.php) - mbstring is designed to handle Unicode-based encodings.
- [pcre](http://php.net/manual/en/intro.pcre.php) - perl compatible regular expressions.

For the curious ones, this is why ToObject needs PHP5.4.x:

- [namespaces][] - (5.3.0) Namespaces provide a way in which to group related classes, interfaces, functions and constants.
- [traits][] - (5.4.0) Traits intend to reduce single-inheritance limitations.
- [callable][] - (5.4.0) typehint for callbacks.
- [closures][] - Anonymous functions.
- [array short syntax](http://nl.php.net/manual/en/language.types.array.php) - (5.4.0) short syntax support: array() -> [].
- [\_\_DIR\_\_]() - (5.3.0) equivalent of dirname(\_\_FILE\_\_).
 

# Obtaining ToObject

By downloading this package you should have:

- Source code of ToObject
- Source code of the Unit Tests
  
  
# Tests

This package is developed on OSX Lion using php5.4.6 and tested with PHPUnit:

- on a Debian box with:

    * php5.4.6
    * php5.4.7
- on a OSX ML box with:
    * php5.5.0


 
[php5.4.0]:     http://php.net/get/php-5.4.0.tar.gz/from/a/mirror
[php5.4.6]:     http://php.net/get/php-5.4.6.tar.gz/from/a/mirror
[php5.4.7]:     http://php.net/get/php-5.4.7.tar.gz/from/a/mirror
[namespaces]:   ttp://php.net/manual/en/language.namespaces.php
[traits]:       http://php.net/manual/en/language.oop5.traits.php
[callable]:     http://php.net/manual/en/language.types.callable.php
[closures]:     http://nl.php.net/manual/en/functions.anonymous.php 