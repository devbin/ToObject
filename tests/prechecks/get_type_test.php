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
  
class get_type_test extends PHPUnit_Framework_TestCase
{
    public function testGetTypeOfStringClass()
    {
        $this->assertEquals(ObjectClass::TYPE_OKAY, ObjectClass::get_type(new StringClass("")));
    }
    
    public function testGetTypeOfArrayClass()
    {
        $this->assertEquals(ObjectClass::TYPE_OKAY, ObjectClass::get_type(new ArrayClass([])));
    }
    
    public function testGetTypeOfHashClass()
    {
        $this->assertEquals(ObjectClass::TYPE_OKAY, ObjectClass::get_type(new HashClass((object) [])));
    }
    
    public function testGetTypeOfNonToObjectInstances()
    {
        $this->assertEquals(ObjectClass::TYPE_SKIP, ObjectClass::get_type(new SplFixedArray(0)));
        $this->assertEquals(ObjectClass::TYPE_SKIP, ObjectClass::get_type(new SomeRandomMockObject()));
    }
    
    public function testGetTypeOfUnsupportedPrimitiveTypes()
    {
        $this->assertEquals(ObjectClass::TYPE_SKIP, ObjectClass::get_type(-1));
        $this->assertEquals(ObjectClass::TYPE_SKIP, ObjectClass::get_type(0));
        $this->assertEquals(ObjectClass::TYPE_SKIP, ObjectClass::get_type(1));
        $this->assertEquals(ObjectClass::TYPE_SKIP, ObjectClass::get_type(12.34));
        $this->assertEquals(ObjectClass::TYPE_SKIP, ObjectClass::get_type(1.844674407371E+19));
        $this->assertEquals(ObjectClass::TYPE_SKIP, ObjectClass::get_type(true));
        $this->assertEquals(ObjectClass::TYPE_SKIP, ObjectClass::get_type(false));
        $this->assertEquals(ObjectClass::TYPE_SKIP, ObjectClass::get_type(null));
    }
    
    public function testGetTypeOfSupportedEmptyPrimitiveTypes()
    {
        $this->assertEquals(ObjectClass::TYPE_STRINGCLASS, ObjectClass::get_type(""));
        
        $this->assertEquals(ObjectClass::TYPE_ARRAYCLASS, ObjectClass::get_type([]));
        $this->assertEquals(ObjectClass::TYPE_ARRAYCLASS, ObjectClass::get_type(array()));
        
        $this->assertEquals(ObjectClass::TYPE_HASHCLASS, ObjectClass::get_type((object) []));
        $this->assertEquals(ObjectClass::TYPE_HASHCLASS, ObjectClass::get_type(new \stdClass));
    }
    
    public function testGetTypOfSupportedNonEmptyPrimitiveTypes()
    {
        $this->assertEquals(ObjectClass::TYPE_STRINGCLASS, ObjectClass::get_type("foo"));
        $this->assertEquals(ObjectClass::TYPE_STRINGCLASS, ObjectClass::get_type("1234"));
        $this->assertEquals(ObjectClass::TYPE_STRINGCLASS, ObjectClass::get_type("12.34"));
        $this->assertEquals(ObjectClass::TYPE_STRINGCLASS, ObjectClass::get_type("1.844674407371E+19"));
        
        $this->assertEquals(ObjectClass::TYPE_ARRAYCLASS, ObjectClass::get_type(["foo"]));
        
        $this->assertEquals(ObjectClass::TYPE_HASHCLASS, ObjectClass::get_type((object) ["foo" => "bar"]));
    }
    
    /**
     * @depends testGetTypeOfStringClass
     * @depends testGetTypeOfArrayClass
     * @depends testGetTypeOfHashClass
     * @depends testGetTypeOfNonToObjectInstances
     * @depends testGetTypeOfUnsupportedPrimitiveTypes
     * @depends testGetTypeOfSupportedEmptyPrimitiveTypes
     * @depends testGetTypOfSupportedNonEmptyPrimitiveTypes
    */
    public function testTotalGetTypeIntegrity()
    {
        $this->assertTrue(true);
    }
    
    
    /**
     * this seems the best time to check for traversable classes
     * since we'll be checking if what toobject CONTAINS is also of
     * the correct type (::get_type())
     * 
     * for ArrayClass, also test for ArrayAcccess
     * also, check for Countable
     * 
     * StringClass: __tostring, ArrayAccess, Countable
     * ArrayAcccess: Traversable, ArrayAccess, Countable
     * HashClass: Traversable, Countable, __get, __set, __unset
    */
    
    // 
    // StringClass
    // 
    // 
    // public function testStringClassHasToStringMagicMethod()
    // {
    //     $this->assertTrue(in_array('__tostring', get_class_methods(new StringClass(""))));
    // }
    // 
    // public function testStringClassToStringUsage()
    // {
    //     $str = new StringClass("foobar");
    //     $this->assertEquals("foobar", $str->__tostring());
    //     $this->assertEquals("foobar", $str);
    // }
    // 
    // public function testStringClassImplementsArrayAccess()
    // {
    //     $this->assertTrue(in_array("ArrayAccess", class_implements(new StringClass(""))));
    // }
    // 
    // public function testStringClassArrayAccessUsage()
    // {
    //     $str = new StringClass("foobar");
    //     $str[2] = "x";
    //         
    //     $this->assertEquals(new StringClass("foxbar"), $str);
    //         
    //     $str[] = "derp";
    //     $this->assertEquals(new StringClass("foxbarderp"), $str);
    // }
    // 
    // public function testStringClassImplementsCountable()
    // {
    //     $this->assertTrue(in_array("Countable", class_implements(new StringClass(""))));
    // }
    // 
    // public function testStringClassCountableUsage()
    // {
    //     $str = new StringClass("foobar");
    //     $this->assertEquals(count($str), strlen("foobar"));
    //     $this->assertEquals(count($str), mb_strlen("foobar", 'ASCII'));
    //         
    //     $str = new StringClass("‹‡");
    //     $this->assertEquals(count($str), mb_strlen("‹‡", 'UTF-8'));
    // }
    // 
    // public function testStringClassAssignNull()
    // {
    //     $s1 = new StringClass("foobar");
    //     $s2 = new StringClass("foobar");
    //     $s3 = new StringClass("foobar");
    //     $s4 = new StringClass("foobar");
    //     
    //     $s1[2] = null;
    //     $s2[3] = 0;
    //     $s3[4] = 0x00;
    //     $s4[2] = "***";
    //     
    //     $this->assertEquals("fo", $s1);
    //     $this->assertEquals("foo", $s2);
    //     $this->assertEquals("foob", $s3);
    //     $this->assertEquals("fo*bar", $s4);
    // }
    // 
    // // 
    // // ArrayClass
    // // 
    // 
    // public function testArrayClassIsTraversable()
    // {
    //     $this->assertTrue(in_array("Traversable", class_implements(new ArrayClass([]))));
    // }
    // 
    // public function testArrayClassImplementsCountable()
    // {
    //     $this->assertTrue(in_array("Countable", class_implements(new ArrayClass([]))));
    // }
    // 
    // public function testArrayClassImplementsArrayAccess()
    // {
    //     $this->assertTrue(in_array("ArrayAccess", class_implements(new ArrayClass([]))));
    // }
    // 
    // public function testAppendToArrayClass()
    // {
    //     $ary = new ArrayClass(["bar", "baz"]);
    //     $count = count($ary);
    //     $ary[] = "foo";
    //     
    //     $this->assertTrue(isset($ary[2]));
    //     $this->assertTrue(count($ary) == ($count +1));
    //     $this->assertTrue($ary[count($ary) -1] instanceof StringClass);
    // }
    // 
    // public function testArrayClassAssignNull()
    // {
    //     $ary = new ArrayClass([1, 2, 3]);
    //     $ary[1] = null;
    //     
    //     $this->assertEquals(null, $ary[1]);
    //     $this->assertEquals(3, count($ary));
    // }
    // 
    // public function testArrayClassUnset()
    // {
    //     $ary = new ArrayClass([1, 2, 3]);
    //     unset($ary[1]);
    //     
    //     $this->assertTrue(isset($ary[0]));
    //     $this->assertFalse(isset($ary[1]));
    //     $this->assertTrue(isset($ary[2]));
    //     
    //     $this->assertEquals(1, $ary[0]);
    //     $this->assertEquals(3, $ary[2]);
    //     $this->assertEquals(2, count($ary));
    // }
    // 
    // // 
    // // HashClass
    // // 
    // 
    // public function testHashClassIsTraversable()
    // {
    //     $this->assertTrue(in_array("Traversable", class_implements(new HashClass((object) []))));
    // }
    // 
    // public function testHashClassImplementsCountable()
    // {
    //     $this->assertTrue(in_array("Countable", class_implements(new HashClass((object) []))));
    // }
    // 
    // public function testHashClassHasGetAndSetMagicMethods()
    // {
    //     $clsm = get_class_methods(new HashClass((object) []));
    //     $this->assertTrue(in_array('__get', $clsm));
    //     $this->assertTrue(in_array('__set', $clsm));
    // }
    // 
    // public function testHashClassHasUnsetMagicMethods()
    // {
    //     $this->assertTrue(in_array('__unset', get_class_methods(new HashClass((object) []))));
    // }
    // 
    // public function testHashClassAddValue()
    // {
    //     $hsh = new HashClass((object) []);
    //     $hsh->foo = "bar";
    //     
    //     $this->assertEquals(new HashClass((object) ["foo" => "bar"]), $hsh);
    // }
    // 
    // public function testHashClassAssignNull()
    // {
    //     $hsh = new HashClass((object) []);
    //     $hsh->foo = "bar";
    //     
    //     $hsh->foo = null;
    //     
    //     $this->assertEquals(null, $hsh->foo);
    // }
    // 
    // public function testHashClassUnset()
    // {
    //     $hsh = new HashClass((object) []);
    //     $hsh->foo = "bar";
    //     
    //     $this->assertEquals(new HashClass((object) ["foo" => "bar"]), $hsh);
    //     
    //     unset($hsh->foo);
    //     $this->assertEquals(new HashClass((object) []), $hsh);
    // }
    // 
    // /**
    //  * now lets test if we can build some ObjectClass things
    // */
    // 
    // public function testArrayClassWithIntegersIteratively()
    // {
    //     $ary = new ArrayClass([1, 2, 3]);
    //     $k = 0;
    //     $this->assertTrue($ary instanceof ArrayClass);
    //     
    //     foreach ($ary as $key => $value)
    //     {
    //         $this->assertEquals($key, $k++); // POST increment, capiche?
    //         $this->assertTrue(is_int($value));
    //     }
    // }
    // 

    // 
    // public function testHashClassWithIntegersIteratively()
    // {
    //     $ary = new HashClass((object) [1, 2, 3]);
    //     $this->assertTrue($ary instanceof HashClass);
    //     
    //     foreach ($ary as $value)
    //     {
    //         $this->assertTrue(is_int($value));
    //     }
    // }
    // 

    // 
    // /**
    //  * if no tests failed up to this point, we know ToObject:
    //  * 1) can determine what data type is passed in
    //  * 2) can determine whether it should do something with the data
    //  * 3) if yes, handles it correctly
    //  * 4) if not, doesn't modify it at all
    //  * 5) can recursively create objects of itself
    // */
    // 
    // /**
    //  * test other functionalities of ToObject's base class.
    // */
    // 
    // public function testObjectClassKlass()
    // {
    //     $str = new StringClass("");
    //     $ary = new ArrayClass([]);
    //     $hsh = new HashClass((object) []);
    //     
    //     $this->assertEquals('StringClass', $str->klass());
    //     $this->assertEquals('ArrayClass', $ary->klass());
    //     $this->assertEquals('HashClass', $hsh->klass());
    // }
    // 
    // public function testObjectClassIsA()
    // {
    //     $str = new StringClass("");
    //     $ary = new ArrayClass([]);
    //     $hsh = new HashClass((object) []);
    //     
    //     $this->assertTrue($str->is_a('StringClass'));
    //     $this->assertTrue($ary->is_a('ArrayClass'));
    //     $this->assertTrue($hsh->is_a('HashClass'));
    //     
    //     $this->assertTrue($str->is_a('ObjectClass'));
    //     $this->assertTrue($ary->is_a('ObjectClass'));
    //     $this->assertTrue($hsh->is_a('ObjectClass'));
    //     
    // }
    // 
    // public function testObjectClassMethods()
    // {
    //     // silly test but this will give Code Coverage its cookie
    //     // methods() does: `return new ArrayClass(get_class_methods($this));'
    //     
    //     $str = new StringClass("");
    //     $ary = new ArrayClass([]);
    //     $hsh = new HashClass((object) []);
    //     $this->assertTrue($str->methods() instanceof ArrayClass);
    //     $this->assertTrue($ary->methods() instanceof ArrayClass);
    //     $this->assertTrue($hsh->methods() instanceof ArrayClass);
    // }
    // 
    // public function testObjectClassEqlString()
    // {
    //     $str = new StringClass("");
    //     $this->assertTrue($str->eql(new StringClass("")));
    //     
    //     $this->assertFalse($str->eql(new StringClass("foo")));
    //     $this->assertFalse($str->eql(new ArrayClass([])));
    //     $this->assertFalse($str->eql(new HashClass((object) [])));
    //     
    //     $str = new StringClass("foo");
    //     $this->assertTrue($str->eql(new StringClass("foo")));
    //     $this->assertFalse($str->eql(new StringClass("")));
    // }
    // 
    // public function testObjectClassEqlArray()
    // {
    //     $ary = new ArrayClass([]);
    //     $cpy = $ary;
    //     
    //     $this->assertTrue($ary->eql(new ArrayClass([])));
    //     $this->assertTrue($ary->eql($cpy));
    //     
    //     $this->assertFalse($ary->eql(new ArrayClass(["foo"])));
    //     $this->assertFalse($ary->eql(new StringClass("foo")));
    // }
    // 
    // public function testObjectClassEqlHash()
    // {
    //     $hsh = new HashClass((object) []);
    //     $cpy = $hsh;
    //     
    //     $this->assertTrue($hsh->eql(new HashClass((object) [])));
    //     $this->assertTrue($hsh->eql($cpy));
    //     
    //     $this->assertFalse($hsh->eql(new ArrayClass([])));
    //     $this->assertFalse($hsh->eql(new StringClass("")));
    // }
    // 
    // public function testObjectClassEqualString()
    // {
    //     $str = new StringClass("");
    //     $cpy = $str;
    //     
    //     $this->assertTrue($str->equal($cpy));
    //     $this->assertFalse($str->equal(new StringClass("")));
    //     $this->assertFalse($str->equal(new ArrayClass([])));
    //     $this->assertFalse($str->equal(new HashClass((object) [])));
    // }
    // 
    // public function testObjectClassEqualArray()
    // {
    //     $ary = new ArrayClass([]);
    //     $cpy = $ary;
    //     
    //     $this->assertTrue($ary->equal($cpy));
    //     $this->assertFalse($ary->equal(new ArrayClass([])));
    //     $this->assertFalse($ary->equal(new HashClass((object) [])));
    //     $this->assertFalse($ary->equal(new StringClass("")));
    // }
    // 
    // public function testObjectClassEqualHash()
    // {
    //     $hsh = new HashClass((object) []);
    //     $cpy = $hsh;
    //     
    //     $this->assertTrue($hsh->equal($cpy));
    //     $this->assertFalse($hsh->equal(new HashClass((object) [])));
    //     $this->assertFalse($hsh->equal(new ArrayClass([])));
    //     $this->assertFalse($hsh->equal(new StringClass("")));
    // }
}

?>
