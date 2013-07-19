<?php

// namespace Devbin\libs\ToObject;
use    Devbin\libs\ToObject\ObjectClass, 
    Devbin\libs\ToObject\StringClass,
    Devbin\libs\ToObject\ArrayClass,
    Devbin\libs\ToObject\HashClass;


require_once 'PHPUnit/Autoload.php';
require_once(dirname(__FILE__) . '/../../src/ToObject.php');


class ArrayClassTest extends \PHPUnit_Framework_TestCase
{
    public function __construct() 
    {
        $this->ary1 = new ArrayClass([1, 2, 3, 4, 5, 6, 7, 8, 9]);
        $this->ary2 = new ArrayClass(["foo", "bar", "baz"]);
    }
    
    public function test_tostring()
    {
        $expected = "1\n2\n3\n4\n5\n6\n7\n8\n9"; 
        $this->assertSame($expected, $this->ary1->__tostring());
    }
    
    public function test_to_s()
    {
        $this->assertSame("[1, 2, 3, 4, 5, 6, 7, 8, 9]", $this->ary1->to_s());
        $this->assertSame('["foo", "bar", "baz"]', $this->ary2->to_s());
        
        $actual = new ArrayClass(["foo", ["bar"], new HashClass((object) ["hello" => "world"])]);
        $this->assertSame("[\"foo\", [\"bar\"], { :hello => \"world\" }]", $actual->to_s());
    }
    
    public function test_to_native()
    {
        $a = [1, 2, 3, 4, 5, 6, 7, 8, 9];
        $b = [1, 2, 3, [4, 5], 6, 7, 8, 9];
        
        $x = new ArrayClass($a);
        $y = new ArrayClass($b);

        $this->assertSame($a, $x->to_native(true));
        $this->assertSame($a, $x->to_native(false));
        
        $this->assertSame($b, $y->to_native(true));
        $this->assertNotSame($b, $y->to_native(false));
    }
    
    public function test_to_hsh()
    {
        $input = new ArrayClass([["p", "q"], ["r", "s"]]);
        $expected = new HashClass((object) ["p" => "q", "r" => "s"]);
        $this->assertEquals($expected, $input->to_hsh());
    }
    
    public function test_all()
    {
        $this->assertTrue($this->ary1->all(function($e) { return is_int($e); }));
        $this->assertFalse($this->ary1->all(function($e) { return $e == 5; }));
    }
    
    public function test_any()
    {
        $this->assertTrue($this->ary1->any(function($e) { return $e == 5; }));
        $this->assertFalse($this->ary1->any(function($e) { return is_string($e); }));
    }
    
    public function test_collect()
    {
        // echo "Test that the returned collection contains".PHP_EOL.
            // "[0, 1, 0, 1, 0, 1, 0, 1, 0], which is: (int) \$e % 2 == 0";
        
        $expected = [0, 1, 0, 1, 0, 1, 0, 1, 0];
        $actual = $this->ary1->collect(function($e, $i) { return (int) ($e % 2 == 0); })->getStorage();
        $this->assertSame($expected, $actual);
        
        $expected = [1, 4, 9, 16, 25, 36, 49, 64, 81];
        $actual = $this->ary1->collect(function($e, $i) { return $e*$e; })->getStorage();
        $this->assertSame($expected, $actual);
    }
    
    public function test_delete_if()
    {
        $expected = [0 => 1, 2 => 3, 4 => 5, 6 => 7, 8 => 9];
        $this->assertSame($expected, $this->ary1->delete_if(function($e) { return $e % 2 == 0; })->getStorage());
    }
    
    public function test_detect()
    {
        $expected = [5 => 6];
        $this->assertSame($expected, $this->ary1->detect(function($e) { return $e > 5; })->getStorage());
        
        // detect nothing (null)
        $this->assertSame(null, $this->ary1->detect(function($e) { return $e == "foo"; }));
    }
    
    public function test_drop()
    {
        $expected = [2 => 3, 3 => 4, 4 => 5, 5 => 6, 6 => 7, 7 => 8, 8 => 9];
        $this->assertSame($expected, $this->ary1->drop(2)->getStorage());
    }
    
    public function test_drop_while()
    {
        $expected = [4 => 5, 5 => 6, 6 => 7, 7 => 8, 8 => 9];
        $this->assertSame($expected, $this->ary1->drop_while(function($e) { return $e < 5; })->getStorage());
    }
    
    public function test_join()
    {
        $expected = new StringClass("foobarbaz");
        $this->assertEquals($expected, $this->ary2->join(""));
        
        $expected = "foobarbaz";
        $this->assertSame($expected, $this->ary2->join("")->__tostring());
    }
    
    public function test_includes()
    {
        $this->assertTrue($this->ary1->includes(1));
        $this->assertFalse($this->ary1->includes(10));
        
        $this->assertTrue($this->ary2->includes("foo"));
        $this->assertFalse($this->ary2->includes("hello world"));
    }
    
    public function test_inject()
    {
        $expected = 45;
        $this->assertSame($expected, $this->ary1->inject(function($mem, $var) { return $mem += $var[1]; }));
    }
    
    public function test_keep_if()
    {
        $expected = [1 => 2, 3 => 4, 5 => 6, 7 => 8];
        $this->assertSame($expected, $this->ary1->keep_if(function($e) { return $e % 2 == 0; })->getStorage());
    }
    
    public function test_none()
    {
        $this->assertTrue($this->ary1->none(function($e) { return $e == 40; }));
        $this->assertFalse($this->ary1->none(function($e) { return $e == 4; }));
    }
    
    public function test_one()
    {
        $this->assertFalse($this->ary1->one(function($e) { return $e == 40; }));
        $this->assertTrue($this->ary1->one(function($e) { return $e == 4; }));
    }
    
    public function test_select()
    {
        $expected = [5 => 6, 6 => 7];
        $this->assertSame($expected, $this->ary1->select(function($e) { return ($e > 5 && $e < 8); })->getStorage());
    }
    
    public function test_subseq_keys()
    {
        $input = new ArrayClass([["p", "q"], ["r", "s"]]);
        $expected = [new StringClass("p"), new StringClass("r")];
        $this->assertEquals($expected, $input->subseq_keys());
    }
    
    public function test_subseq_values()
    {
        $input = new ArrayClass([["p", "q"], ["r", "s"]]);
        $expected = [new StringClass("q"), new StringClass("s")];
        $this->assertEquals($expected, $input->subseq_values());
    }
    
    public function test_bool_exception()
    {
        $caught = false;
        try {
            $this->ary1->any(function($e) { return "This is not a boolean."; });
        } catch (Exception $e) {
            $caught = true;
        }
        
        $this->assertTrue($caught);
    }
}



// $hsh = new EnumTest((object) array('name' => 'derpina', 'last' => 'derpinater', 'test' => 'foobar'));
// $arr = new EnumTest([1, 2, 3, 4, 5, 6, 7, 8, 9]);
// 
// var_dump($hsh->all(function($e) { return strlen($e) > 5; }));           // true: every value has more than 5 chars
// var_dump($hsh->any(function($e) { return strlen($e) > 6; }));           // true: two values have more than 6 chars
// print_r($hsh->collect(function($e, $i) { return $e.='__'; }));          // ['derpina__', 'derpinater__', 'foobar__']
// print_r($hsh->delete_if(function($e) { return strlen($e) == 6; }));     // ['name' => 'derpina', 'last' => 'derpinater'] (see keep_if)
// print_r($hsh->detect(function($e) { return strlen($e) > 5; }));         // ['name' => 'derpina']
// print_r($hsh->drop(1));                                                 // ['last' => 'derpinater', 'test' => 'foobar']
// print_r($hsh->drop_while(function($e) { return $e[0] != 'f'; }));       // ['last' => 'foobar']
// print_r($hsh->inject(function($mem, $e) { return $mem .= $e[0].'::'.$e[1].'__'; }));    // name::derpina__last::derpinater__test::foobar__
// print_r($hsh->keep_if(function($e) { return strlen($e) == 6; }));       // ['test' => 'foobar] (see delete_if)
// var_dump($hsh->none(function($e) { return $e == 'abc'; }));             // true: none of the emelemets are equal to 'abc'
// var_dump($hsh->one(function($e) { return $e == 'derpina'; }));          // true: exactly one of the elements is equal to 'derpina'
// print_r($hsh->select(function($e) { return strlen($e) > 6; }));         // ['name' => 'derpina', 'last' => 'derpinater']
// 
// 
// 
// var_dump($arr->all(function($e) { return $e == 5; }));                      // false: not all elements are equal to 5
// var_dump($arr->any(function($e) { return $e == 5; }));                      // true: at leas one of the elements is equal to 5
// print_r($arr->collect(function($e, $i) { return (int) ($e % 2 == 0); }));   // [0, 1, 0, 1, 0, 1, 0, 1, 0]
// print_r($arr->delete_if(function($e) { return $e % 2 == 0; }));             // [0 => 1, 2 => 3, 4 => 5, 6 => 7, 8 => 9]
// print_r($arr->detect(function($e) { return $e > 5; }));                     // [5 => 6]
// print_r($arr->drop(2));                                                     // [2 => 3, 3 => 4, 4 => 5, 5 => 6, 6 => 7, 7 => 8, 8 => 9]
// print_r($arr->drop_while(function($e) { return $e < 5; }));                 // [4 => 5, 5 => 6, 6 => 7, 7 => 8, 8 => 9]


// print_r($arr->inject(function($mem, $e) { return $mem += $e[1]; }));        // 45
// print_r($arr->keep_if(function($e) { return $e % 2 == 0; }));               // [1 => 2, 3 => 4, 5 => 6, 7 => 8]
// var_dump($arr->none(function($e) { return $e == 4; }));                     // false: one if the elements is equal to 4
// var_dump($arr->one(function($e) { return $e == 5; }));                      // true: exactle one of the elements is equal to 5
// print_r($arr->select(function($e) { return ($e > 5 && $e < 8); }));         // [5 => 6, 6 => 7]
// 


?>
