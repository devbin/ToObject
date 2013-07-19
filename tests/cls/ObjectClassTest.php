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
  * @depends implementation_test::testTotalImplementationIntegrity
 */
class ObjectClassTest extends PHPUnit_Framework_TestCase
{

    public function testObjectClassKlass()
    {
        $str = new StringClass("");
        $ary = new ArrayClass([]);
        $hsh = new HashClass((object) []);
        
        $this->assertEquals('StringClass', $str->klass());
        $this->assertEquals('ArrayClass', $ary->klass());
        $this->assertEquals('HashClass', $hsh->klass());
    }
    
    public function testObjectClassIsA()
    {
        $str = new StringClass("");
        $ary = new ArrayClass([]);
        $hsh = new HashClass((object) []);
        
        $this->assertTrue($str->is_a('StringClass'));
        $this->assertTrue($ary->is_a('ArrayClass'));
        $this->assertTrue($hsh->is_a('HashClass'));
        
        $this->assertTrue($str->is_a('ObjectClass'));
        $this->assertTrue($ary->is_a('ObjectClass'));
        $this->assertTrue($hsh->is_a('ObjectClass'));
        
    }
    
    public function testObjectClassMethods()
    {
        // silly test but this will give Code Coverage its cookie
        // methods() does: `return new ArrayClass(get_class_methods($this));'
        
        $str = new StringClass("");
        $ary = new ArrayClass([]);
        $hsh = new HashClass((object) []);
        $this->assertTrue($str->methods() instanceof ArrayClass);
        $this->assertTrue($ary->methods() instanceof ArrayClass);
        $this->assertTrue($hsh->methods() instanceof ArrayClass);
    }
    
    public function testObjectClassEqlString()
    {
        $str = new StringClass("");
        $this->assertTrue($str->eql(new StringClass("")));
        
        $this->assertFalse($str->eql(new StringClass("foo")));
        $this->assertFalse($str->eql(new ArrayClass([])));
        $this->assertFalse($str->eql(new HashClass((object) [])));
        
        $str = new StringClass("foo");
        $this->assertTrue($str->eql(new StringClass("foo")));
        $this->assertFalse($str->eql(new StringClass("")));
    }
    
    public function testObjectClassEqlArray()
    {
        $ary = new ArrayClass([]);
        $cpy = $ary;
        
        $this->assertTrue($ary->eql(new ArrayClass([])));
        $this->assertTrue($ary->eql($cpy));
        
        $this->assertFalse($ary->eql(new ArrayClass(["foo"])));
        $this->assertFalse($ary->eql(new StringClass("foo")));
    }
    
    public function testObjectClassEqlHash()
    {
        $hsh = new HashClass((object) []);
        $cpy = $hsh;
        
        $this->assertTrue($hsh->eql(new HashClass((object) [])));
        $this->assertTrue($hsh->eql($cpy));
        
        $this->assertFalse($hsh->eql(new ArrayClass([])));
        $this->assertFalse($hsh->eql(new StringClass("")));
    }
    
    public function testObjectClassEqualString()
    {
        $str = new StringClass("");
        $cpy = $str;
        
        $this->assertTrue($str->equal($cpy));
        $this->assertFalse($str->equal(new StringClass("")));
        $this->assertFalse($str->equal(new ArrayClass([])));
        $this->assertFalse($str->equal(new HashClass((object) [])));
    }
    
    public function testObjectClassEqualArray()
    {
        $ary = new ArrayClass([]);
        $cpy = $ary;
        
        $this->assertTrue($ary->equal($cpy));
        $this->assertFalse($ary->equal(new ArrayClass([])));
        $this->assertFalse($ary->equal(new HashClass((object) [])));
        $this->assertFalse($ary->equal(new StringClass("")));
    }
    
    public function testObjectClassEqualHash()
    {
        $hsh = new HashClass((object) []);
        $cpy = $hsh;
        
        $this->assertTrue($hsh->equal($cpy));
        $this->assertFalse($hsh->equal(new HashClass((object) [])));
        $this->assertFalse($hsh->equal(new ArrayClass([])));
        $this->assertFalse($hsh->equal(new StringClass("")));
    }
}

?>
