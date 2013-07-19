<?php

// namespace Devbin\libs\ToObject;
use    Devbin\libs\ToObject\ObjectClass, 
    Devbin\libs\ToObject\StringClass,
    Devbin\libs\ToObject\ArrayClass,
    Devbin\libs\ToObject\HashClass;


require_once 'PHPUnit/Autoload.php';
require_once(dirname(__FILE__) . '/../../src/ToObject.php');


class HashClassTest extends \PHPUnit_Framework_TestCase
{
    public function __construct() 
    {
        $this->hsh = new HashClass((object) array('name' => 'John', 'last' => 'Doe', 'test' => 'foobar'));
    }
    
    public function test_tostring()
    {
        $expected = "John\nDoe\nfoobar"; 
        $this->assertSame($expected, $this->hsh->__tostring());
    }
    
    public function test_to_s()
    {
        $this->assertSame("{ :name => \"John\", :last => \"Doe\", :test => \"foobar\" }", $this->hsh->to_s());
        
        $actual = new HashClass((object) ["age" => 23]);
        $this->assertSame("{ :age => 23 }", $actual->to_s());
        
        $actual = new HashClass((object) ["foo" => ["bar"], "x" => new HashClass((object) ["hello" => "world"])]);
        $this->assertSame("{ :foo => [\"bar\"], :x => { :hello => \"world\" } }", $actual->to_s());
    }
    
    public function test_to_native()
    {
        $a = (object) ["foo" => "bar"];
        $b = (object) ["foo" => ["bar", "baz"]];
        
        $x = new HashClass($a);
        $y = new HashClass($b);
    
        $this->assertEquals($a, $x->to_native(true));
        $this->assertEquals($a, $x->to_native(false));
        
        $this->assertEquals($b, $y->to_native(true));
        $this->assertNotEquals($b, $y->to_native(false));
        
        $n = (object) ["age" => 23];
        $o = new HashClass($n);
        $this->assertEquals($n, $o->to_native(true));
    }
    
    public function test_to_a()
    {
        $input = new HashClass((object) ["p" => "q", "r" => "s"]);
        $expected = new ArrayClass([["p", "q"], ["r", "s"]]);
        $this->assertEquals($expected, $input->to_a());
    }
    
    public function test_to_native_a()
    {
        $input = new HashClass((object) ["p" => "q", "r" => "s", "misc" => (object) ["a" => 23]]);
        
        $expected = ["p" => "q", "r" => "s", "misc" => ["a" => 23]];
        $this->assertEquals($expected, $input->to_native_a(true));
        
        $expected = (array) $input->getStorage();
        $this->assertEquals($expected, $input->to_native_a(false));
    }
    
    public function test_all()
    {
        $this->assertTrue($this->hsh->all(function($e) { return strlen($e) <= 10; }));
        $this->assertFalse($this->hsh->all(function($e) { return $e == "5"; }));
    }
    
    public function test_any()
    {
        $this->assertTrue($this->hsh->any(function($e) { return $e == "John"; }));
        $this->assertFalse($this->hsh->any(function($e) { return is_int($e); }));
    }
    
    public function test_collect()
    {
        $expected = new ArrayClass(["John__", "Doe__", "foobar__"]);
        $actual = $this->hsh->collect(function($e, $i) { return $e.='__'; });
        $this->assertEquals($expected, $actual);
    }
    
    public function test_delete_if()
    {
        $expected = new HashClass((object) ['name' => 'John', 'last' => 'Doe']);
        $this->assertEquals($expected, $this->hsh->delete_if(function($e) { return strlen($e) == 6; }));
    }
    
    public function test_detect()
    {
        $expected = new HashClass((object) ["test" => "foobar"]);
        $this->assertEquals($expected, $this->hsh->detect(function($e) { return strlen($e) > 5; }));
        
        // null
        $this->assertSame(null, $this->hsh->detect(function($e) { return strlen($e) == 50; }));
    }
    
    public function test_drop()
    {
        $expected = new HashClass((object) ["last" => "Doe", "test" => "foobar"]);
        $this->assertEquals($expected, $this->hsh->drop(1));
    }
    
    public function test_drop_while()
    {
        $expected = new HashClass((object) ["test" => "foobar"]);
        $this->assertEquals($expected, $this->hsh->drop_while(function($e) { return $e[0] != "f"; }));
    }
    
    public function test_includes()
    {
        $this->assertTrue($this->hsh->includes("name"));
        $this->assertTrue($this->hsh->includes("last"));
        $this->assertFalse($this->hsh->includes("foo"));
    }
    
    public function test_inject()
    {
        $expected = "name::John__last::Doe__test::foobar__";
        $this->assertEquals($expected, $this->hsh->inject(function($mem, $var) { return $mem .= $var[0].'::'.$var[1].'__'; }));
    }
    
    public function test_keep_if()
    {
        $expected = new HashClass((object) ["name" => "John", "last" => "Doe"]);
        $this->assertEquals($expected, $this->hsh->keep_if(function($e) { return strlen($e) != 6; }));
    }
    
    public function test_none()
    {
        $this->assertTrue($this->hsh->none(function($e) { return $e == "foo"; }));
        $this->assertFalse($this->hsh->none(function($e) { return $e == "Doe"; }));
    }
    // 
    public function test_one()
    {
        $this->assertFalse($this->hsh->one(function($e) { return $e == "foo"; }));
        $this->assertTrue($this->hsh->one(function($e) { return $e == "Doe"; }));
    }
    // 
    public function test_select()
    {
        $expected = new HashClass((object) ["test" => "foobar"]);
        $this->assertEquals($expected, $this->hsh->select(function($e) { return (strlen($e) > 5 && strlen($e) < 8); }));
    }
}




?>
