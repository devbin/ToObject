<?php

use Devbin\libs\ToObject\ObjectClass, 
    Devbin\libs\ToObject\EnumerableClass,
    Devbin\libs\ToObject\StringClass,
    Devbin\libs\ToObject\ArrayClass,
    Devbin\libs\ToObject\HashClass;

require_once 'PHPUnit/Autoload.php';
require_once __DIR__ . '/../SomeRandomMockObject.php';
require_once __DIR__ . '/../../src/ToObject.php';

// StringClass:     ArrayAccess, Countable, __tostring
// ArrayAcccess:    Traversable, Countable, ArrayAccess, __tostring
// HashClass:       Traversable, Countable, __get, __set, __unset, __tostring

/**
 * @depends build_type_test::testTotalCreationIntegrity
*/
class implementation_test extends PHPUnit_Framework_TestCase
{
    // 
    // StringClass
    // 
    
    public function testStringClassArrayAccess()
    {
        $this->assertTrue(in_array("ArrayAccess", class_implements(new StringClass(""))));
    }
    
    public function testStringClassCountable()
    {
        $this->assertTrue(in_array("Countable", class_implements(new StringClass(""))));
    }
    
    public function testStringClassToString()
    {
        $this->assertTrue(in_array('__tostring', get_class_methods(new StringClass(""))));
    }
    
    // usae implementation
    
    /**
     * @depends testStringClassArrayAccess
    */
    public function testStringClassArrayAccessUsage()
    {
        $str = new StringClass("foobar");
        $str[2] = "x";
            
        $this->assertEquals(new StringClass("foxbar"), $str);
            
        $str[] = "derp";
        $this->assertEquals(new StringClass("foxbarderp"), $str);
    }
    
    /**
     * @depends testStringClassCountable
    */
    public function testStringClassCountableUsage()
    {
        $str = new StringClass("foobar");
        $this->assertEquals(count($str), strlen("foobar"));
        $this->assertEquals(count($str), mb_strlen("foobar", 'ASCII'));
            
        $str = new StringClass("‹‡");
        $this->assertEquals(count($str), mb_strlen("‹‡", 'UTF-8'));
    }
    
    /**
     * @depends testStringClassArrayAccess
    */
    public function testStringClassAssignNull()
    {
        $s1 = new StringClass("foobar");
        $s2 = new StringClass("foobar");
        $s3 = new StringClass("foobar");
        $s4 = new StringClass("foobar");
        
        $s1[2] = null;
        $s2[3] = 0;
        $s3[4] = 0x00;
        $s4[2] = "***";
        
        $this->assertEquals("fo", $s1);
        $this->assertEquals("foo", $s2);
        $this->assertEquals("foob", $s3);
        $this->assertEquals("fo*bar", $s4);
    }
    
    // 
    // ArrayClass
    // 
    
    public function testArrayClassTraversable()
    {
        $this->assertTrue(in_array("Traversable", class_implements(new ArrayClass([]))));
    }
    
    public function testArrayClassCountable()
    {
        $this->assertTrue(in_array("Countable", class_implements(new ArrayClass([]))));
    }
    
    public function testArrayClassArrayAccess()
    {
        $this->assertTrue(in_array("ArrayAccess", class_implements(new ArrayClass([]))));
    }
    
    public function testArrayClassToString()
    {
        $this->assertTrue(in_array('__tostring', get_class_methods(new ArrayClass([]))));
    }
    
    // usage implementation
    
    /**
     * @depends testArrayClassArrayAccess
    */
    public function testArrayClassArrayAccessUsage()
    {
        $ary = new ArrayClass(["bar", "baz"]);
        $count = count($ary);
        $ary[] = "foo";
        
        $this->assertTrue(isset($ary[2]));
        $this->assertTrue(count($ary) == ($count +1));
        $this->assertTrue($ary[count($ary) -1] instanceof StringClass);
    }
    
    /**
     * @depends testArrayClassArrayAccess
    */
    public function testArrayClassAssignNullUsage()
    {
        $ary = new ArrayClass([1, 2, 3]);
        $ary[1] = null;
        
        $this->assertEquals(null, $ary[1]);
        $this->assertEquals(3, count($ary));
    }
    
    /**
     * @depends testArrayClassArrayAccess
    */
    public function testArrayClassUnsetUsage()
    {
        $ary = new ArrayClass([1, 2, 3]);
        unset($ary[1]);
        
        $this->assertTrue(isset($ary[0]));
        $this->assertFalse(isset($ary[1]));
        $this->assertTrue(isset($ary[2]));
        
        $this->assertEquals(1, $ary[0]);
        $this->assertEquals(3, $ary[2]);
        $this->assertEquals(2, count($ary));
    }

    
    // 
    // HashClass
    // 
    
    public function testHashClassTraversable()
    {
        $this->assertTrue(in_array("Traversable", class_implements(new HashClass(new \stdClass))));
    }
    
    public function testHashClassCountable()
    {
        $this->assertTrue(in_array("Countable", class_implements(new HashClass(new \stdClass))));
    }
    
    public function testHashClassGet()
    {
        $this->assertTrue(in_array('__get', get_class_methods(new HashClass(new \stdClass))));
    }
    
    public function testHashClassSet()
    {
        $this->assertTrue(in_array('__set', get_class_methods(new HashClass(new \stdClass))));
    }
    
    public function testHashClassUnset()
    {
        $this->assertTrue(in_array('__unset', get_class_methods(new HashClass(new \stdClass))));
    }
    
    public function testHashClassToString()
    {
        $this->assertTrue(in_array('__tostring', get_class_methods(new HashClass(new \stdClass))));
    }
    
    // usage implementation
    
    public function testHashClassAddValueUsage()
    {
        $hsh = new HashClass((object) []);
        $hsh->foo = "bar";
        
        $this->assertEquals(new HashClass((object) ["foo" => "bar"]), $hsh);
    }
    
    public function testHashClassAssignNullUsage()
    {
        $hsh = new HashClass((object) []);
        $hsh->foo = "bar";
        
        $hsh->foo = null;
        
        $this->assertEquals(null, $hsh->foo);
    }
    
    public function testHashClassUnsetUsage()
    {
        $hsh = new HashClass((object) []);
        $hsh->foo = "bar";
        
        $this->assertEquals(new HashClass((object) ["foo" => "bar"]), $hsh);
        
        unset($hsh->foo);
        $this->assertEquals(new HashClass((object) []), $hsh);
    }
    
    /**
    * @depends testStringClassArrayAccess
    * @depends testStringClassCountable
    * @depends testStringClassToString
    * @depends testStringClassArrayAccessUsage
    * @depends testStringClassCountableUsage
    * @depends testStringClassArrayAccess
    * @depends testStringClassAssignNull
    * @depends testArrayClassTraversable
    * @depends testArrayClassCountable
    * @depends testArrayClassArrayAccess
    * @depends testArrayClassToString
    * @depends testArrayClassArrayAccessUsage
    * @depends testArrayClassAssignNullUsage
    * @depends testArrayClassUnsetUsage
    * @depends testHashClassTraversable
    * @depends testHashClassCountable
    * @depends testHashClassGet
    * @depends testHashClassSet
    * @depends testHashClassUnset
    * @depends testHashClassToString
    * @depends testHashClassAddValueUsage
    * @depends testHashClassAssignNullUsage
    * @depends testHashClassUnsetUsage
    */
    public function testTotalImplementationIntegrity()
    {
        # code...
    }
}

?>
