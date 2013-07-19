<?php

// namespace Devbin\libs\ToObject;
use    Devbin\libs\ToObject\ReflectionCallback;

require_once 'PHPUnit/Autoload.php';
require_once(dirname(__FILE__) . '/../../src/utils/EnumerableReflection.php');

require_once 'SomeRandomMockObject.php';

function actual_function() { }

use \Devbin\libs\ToObject\Utils\EnumerableReflection;

class EnumerbleReflectionTest extends PHPUnit_Framework_TestCase
{
    // 
    // parsing the callback
    // 
    
    public function testActualFunction()
    {
        EnumerableReflection::parse_callback('actual_function');
    }
    
    public function testStaticMethodStringformat()
    {
        EnumerableReflection::parse_callback('SomeRandomMockObject::staticPublicMethod');
    }
    
    public function testStaticMethodArrayFormat()
    {
        EnumerableReflection::parse_callback(['SomeRandomMockObject', 'staticPublicMethod']);
    }
    
    public function testDynamicMethodArrayFormat()
    {
        $mock = new SomeRandomMockObject();
        EnumerableReflection::parse_callback([$mock, 'dynamicPublicMethod']);
    }
    
    public function testAnonymousFunction()
    {
        $closure = function() { return; };
        EnumerableReflection::parse_callback(function() { return; });
        EnumerableReflection::parse_callback($closure);
        
        $x = 123;
        $using = function() use ($x) { return $x; };
        EnumerableReflection::parse_callback($using);
        EnumerableReflection::parse_callback(function() use ($x) { return; });
    }
    
    // 
    // parsing the arguments
    // 
    
    public function testCallbackArgumentsOne()
    {
        $reflection = EnumerableReflection::parse_callback(function($e) { return; });
        EnumerableReflection::callback_arguments($reflection, [1, "foo", []]);
    }
    
    public function testCallbackArgumentsOneArray()
    {
        $reflection = EnumerableReflection::parse_callback(function(array $e) { return; });
        EnumerableReflection::callback_arguments($reflection, [1, "foo", []]);
    }
    
    public function testCallbackArgumentsTwoWithoutCustom()
    {
        $reflection = EnumerableReflection::parse_callback(function($e, $i) { return; });
        EnumerableReflection::callback_arguments($reflection, [1, "foo", []]);
    }
    
    public function testCallbackArgumentsTwoWithCustom()
    {
        $reflection = EnumerableReflection::parse_callback(function($e, $custom) { return; });
        EnumerableReflection::callback_arguments($reflection, [1, "foo", [true, false]]);
    }
    
    public function testCallbackArgumentsThree()
    {
        $reflection = EnumerableReflection::parse_callback(function($e, $i, $custom) { return; });
        EnumerableReflection::callback_arguments($reflection, [1, "foo", [true, false]]);
    }
    
    public function testCallbackArgumentsDefault()
    {
        $reflection = EnumerableReflection::parse_callback(function() { return; });
        EnumerableReflection::callback_arguments($reflection, [1, "foo", [true, false]]);
    }
    
    // 
    // calling the block
    // 
    
    public function testCallbackBlockStatic()
    {
        $reflection = EnumerableReflection::parse_callback('SomeRandomMockObject::staticPublicMethod');
        $args = EnumerableReflection::callback_arguments($reflection, [1, "foo", [true, false]]);
        
        EnumerableReflection::callback_block($reflection, 'SomeRandomMockObject::staticPublicMethod', $args);
    }
    
    public function testCallbackBlockDynamic()
    {
        $obj = new SomeRandomMockObject();
        
        $reflection = EnumerableReflection::parse_callback([$obj, 'dynamicPublicMethod']);
        $args = EnumerableReflection::callback_arguments($reflection, [1, "foo", [true, false]]);
        EnumerableReflection::callback_block($reflection, [$obj, 'dynamicPublicMethod'], $args);
    }
}

?>
