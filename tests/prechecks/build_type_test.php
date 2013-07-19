<?php

use Devbin\libs\ToObject\ObjectClass, 
    Devbin\libs\ToObject\EnumerableClass,
    Devbin\libs\ToObject\StringClass,
    Devbin\libs\ToObject\ArrayClass,
    Devbin\libs\ToObject\HashClass;

require_once 'PHPUnit/Autoload.php';
require_once __DIR__ . '/../SomeRandomMockObject.php';
require_once __DIR__ . '/../../src/ToObject.php';

/**
 * BasicTests tests the bare minimum of ToObject
 * in order to determine if it will pass the dependencies.
 * This is necessary due the way ToObject works.
 * 
 * ToObject, at first, tries to guess what data is given to it, so it
 * can determine whether ToObject should handle it, or leave it as is.
 * Therefor, we first need to test whether its guess-work is correct.
*/

/**
 * @depends get_type_test::testTotalGetTypeIntegrity
*/
class build_type_test extends PHPUnit_Framework_TestCase
{
    public function testStringClassCreation()
    {
        $pass = [];
        
        try
        {
            $test = true;
            new StringClass("");
        } catch (Exception $e) {
            $test = false;
        }
        
        $pass[] = $test;        
        
        try
        {
            $test = false;
            new StringClass(1);
        } catch (Exception $e) {
            $test = true;
        }
        $pass[] = $test;
        
        $this->assertTrue(new StringClass("") instanceof StringClass);
        foreach ($pass as $value)
        {
            $this->assertTrue($value);
        }
    }
    
    /**
     * @depends testStringClassCreation
    */
    public function testStringClassIntegrity()
    {
        $str = new StringClass("");
        $this->assertSame("", $str->getStorage());
        
        $str = new StringClass("foo");
        $this->assertSame("foo", $str->getStorage());
        
        $str = new StringClass("‡°ﬂ‡ﬁ€›´Ò");
        $this->assertSame("‡°ﬂ‡ﬁ€›´Ò", $str->getStorage());
        
        $this->assertTrue(is_string($str->getStorage()));
    }
    
    /**
     * @depends get_type_test::testGetTypeOfArrayClass
    */
    public function testArrayClassCreation()
    {
        try
        {
            $pass = true;
            new ArrayClass([]);
        } catch (Exception $e) {
            $pass = false;
        }
        
        $this->assertTrue(new ArrayClass([]) instanceof ArrayClass);
        $this->assertTrue($pass);
    }
    
    /**
     * @depends testArrayClassCreation
     * @depends testStringClassIntegrity
    */
    public function testArrayClassIntegrity()
    {
        $ary = new ArrayClass([]);
        $this->assertSame([], $ary->getStorage());
        
        $ary = new ArrayClass([1, 2, 3]);
        $this->assertSame([1, 2, 3], $ary->getStorage());
        
        $ary = new ArrayClass([true, false, null]);
        $this->assertSame([true, false, null], $ary->getStorage());
        
        $input = [new StringClass("foo"), new StringClass("bar")];
        $ary = new ArrayClass($input);
        $this->assertSame($input, $ary->getStorage());
        
        // create toobject
        $input = new ArrayClass(["foo"]);
        $this->assertEquals(new ArrayClass([new StringClass("foo")]), $input);
        
        // $input = new ArrayClass([(object) ["foo" => new StringClass("bar")]]);
        // $this->assertEquals(new ArrayClass([new HashClass((object) ["foo" => new StringClass("bar")])  ] ), $input);
    }
    
    /**
     * @depends get_type_test::testGetTypeOfHashClass
    */
    public function testHashClassCreation()
    {
        try
        {
            $pass = true;
            new HashClass((object) []);
        } catch (Exception $e) {
            $pass = false;
        }
        
        $this->assertTrue(new HashClass((object) []) instanceof HashClass);
        $this->assertTrue($pass);
    }
    
    /**
     * @depends testHashClassCreation
     * @depends testStringClassIntegrity
    */
    public function testHashClassIntegrity()
    {
        $input = (object) ["a" => 1, "b" => 2];
        $hsh = new HashClass($input);
        $this->assertEquals($input, $hsh->getStorage());
        
        $input = (object) ["fname" => new StringClass("John"), "lname" => new StringClass("Doe"), "misc" => true];
        $expected = $input;
        $hsh = new HashClass($input);
        $this->assertEquals($expected, $hsh->getStorage());
        
        
        // create toobject
        $input = new HashClass((object) ["foo" => "bar"]);
        $this->assertEquals(new HashClass((object) ["foo" => new StringClass("bar")]), $input);
        
        // $input = new HashClass((object) ["foo" => new StringClass("bar")]);
        // $this->assertEquals(new HashClass((object) ["foo" => new StringClass("bar")]), $input);
        
    }
    
    public function testArrayClassKeys()
    {
        // accept int keys
        $input = new ArrayClass([5 => new StringClass("b")]);
        $this->assertEquals([5 => new StringClass("b")], $input->getStorage());
        
        // dont accept non-int keys
        $input = new ArrayClass(["foo" => new StringClass("bar")]);
        $this->assertEquals([], $input->getStorage());
    }
    
    public function testHashClassKeys()
    {
        // accept non-int keys
        $input = new HashClass((object) ["foo" => new StringClass("bar")]);
        $this->assertEquals((object) ["foo" => new StringClass("bar")], $input->getStorage());
        
        // dont accept int keys
        $input = new HashClass((object) [5 => new StringClass("bar")]);
        $this->assertEquals(new stdClass, $input->getStorage());
    }
        
    /**
     * @depends testArrayClassIntegrity
     * @depends testHashClassIntegrity
     * @depends testArrayClassKeys
    */
    public function testArrayClassMixedContent()
    {
        $records = [
            (object) ['fname' => 'John', 'lname' => 'Doe', 'age' => 34, 'driverslicense' => false],
            (object) ['fname' => 'Sarah', 'lname' => 'Stone', 'age' => 36, 'driverslicense' => true]
        ];
        $ary = new ArrayClass(["should_be_skipped" => "foobar", "This is a string", "another string", 1234, 12.34, [6 => true, false, [null]], $records, new SomeRandomMockObject()]);
            
        /**
         * [0] StringClass : This is a string
         * [1] StringClass : another string
         * [2] int         : 1234
         * [3] float       : 12.34
         * [4] ArrayClass  : [6] bool        : true
         *                   [7] bool        : false
         *                   [8] ArrayClass  : [0] null : null
         * 
         * [5] ArrayClass  : [0] HashClass 
         *                         [fname] StringClass   : John
         *                         [lname] StringClass   : Doe
         *                         [age]   int           : 34
         *                         [driverslicense] bool : false
         *                   [1] HashClass
         *                         [fname] StringClass   : Sarah
         *                         [lname] StringClass   : Stone
         *                         [age]   int           : 36
         *                         [driverslicense] bool : true
         * 
         * [6] SomeRandomMockObject : << empty >>
        */
            
        $ary = $ary->getStorage();
        $hsh0 = $ary[5][0]->getStorage();
        $hsh1 = $ary[5][1]->getStorage();
            
        $this->assertTrue($ary[0] instanceof StringClass);
        $this->assertTrue($ary[1] instanceof StringClass);
             
        $this->assertTrue(is_int($ary[2]));
        $this->assertTrue(is_double($ary[3]));
             
        $this->assertTrue($ary[4] instanceof ArrayClass);
        $this->assertTrue($ary[5] instanceof ArrayClass);
        $this->assertFalse($ary[6] instanceof ObjectClass); // Mock
             
        $this->assertTrue($ary[4][6]);
        $this->assertFalse($ary[4][7]);
             
        $this->assertTrue($ary[4][8] instanceof ArrayClass);
        $this->assertTrue(is_null($ary[4][8][0]));
             
        $this->assertTrue($ary[5][0] instanceof HashClass);
        $this->assertTrue($hsh0->fname instanceof StringClass);
        $this->assertTrue($hsh0->lname instanceof StringClass);
        $this->assertTrue(is_int($hsh0->age));
        $this->assertTrue(is_bool($hsh0->driverslicense));
                
        $this->assertTrue($ary[5][1] instanceof HashClass);
        $this->assertTrue($hsh1->fname instanceof StringClass);
        $this->assertTrue($hsh1->lname instanceof StringClass);
        $this->assertTrue(is_int($hsh1->age));
        $this->assertTrue(is_bool($hsh1->driverslicense));

    }
    
    /**
     * @depends testArrayClassIntegrity
     * @depends testHashClassIntegrity
     * @depends testHashClassKeys
    */
    public function testHashClassMixedContent()
    {
        $hsh = (object) [
            'hash' => (object) [
                'fname' => 'John',
                'lname' => 'Doe',
                123 => 'should be skipped',
                'hobbies' => ["foo", "bar", "baz"]
            ],
            'array' => [1, new SomeRandomMockObject(), 2, (object) ["hello" => "world"], 3],
            'str' => "This is just a string.",
            'n' => null,
            'int' => 1234,
            'float' => 12.34,
            'bool' => true,
            'other_bool' => false,
        ];
            
        $hsh = new HashClass($hsh);
            
        /**
         * [hash] HashClass   : [fname] StringClass  : John
         *                      [lname] StringClass  : Doe
         *                      [hobbies] ArrayClass : [0] StringClass : foo
         *                                             [1] StringClass : bar
         *                                             [2] StringClass : baz
         * [array] ArrayClass : [0] int       : 1
         *                      [1] SomeRandomMockObject
         *                      [2] int       : 2
         *                      [3] HashClass : [hello] StringClass : world
         *                      [4] int       : 3
         * [str] StringClass  : This is just a string.
         * [n] null           : null
         * [int] int          : 1234
         * [float] float      : 12.34
         * [bool] bool        : true
         * [other_bool] bool  : false
        */
            
        $hsh = $hsh->getStorage();
        $hshh = $hsh->hash->getStorage();
             
        $this->assertTrue($hsh->hash instanceof HashClass);
        $this->assertTrue($hsh->array instanceof ArrayClass);
        $this->assertTrue($hsh->str instanceof StringClass);
             
        $this->assertTrue(is_null($hsh->n));
        $this->assertTrue(is_int($hsh->int));
        $this->assertTrue(is_double($hsh->float));
        $this->assertTrue(is_bool($hsh->bool));
        $this->assertTrue(is_bool($hsh->other_bool));
        
        $this->assertTrue($hshh->fname instanceof StringClass);
        $this->assertTrue($hshh->lname instanceof StringClass);
        $this->assertTrue($hshh->hobbies instanceof ArrayClass);
        
        $this->assertTrue($hshh->hobbies[0] instanceof StringClass);
        $this->assertTrue($hshh->hobbies[1] instanceof StringClass);
        $this->assertTrue($hshh->hobbies[2] instanceof StringClass);
        
        
        $this->assertTrue(is_int($hsh->array[0])); // 1
        $this->assertTrue(is_int($hsh->array[2])); // 2
        $this->assertTrue(is_int($hsh->array[4])); // 4
        
        $this->assertFalse($hsh->array[1] instanceof ObjectClass); // Mock
        $this->assertTrue($hsh->array[3] instanceof HashClass);
        $this->assertTrue($hsh->array[3]->getStorage()->hello instanceof StringClass);
    }
    
    /**
     * @depends testArrayClassMixedContent
     * @depends testHashClassMixedContent
    */
    public function testTotalCreationIntegrity()
    {
        // just a silly test to use as easy access point in other files
        // when this is true with it's dependencies, creation of all *Classes
        // return the desired result
        $this->assertTrue(true);
    }
}

?>
