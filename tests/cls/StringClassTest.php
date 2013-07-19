<?php

// namespace Devbin\libs\ToObject;

use Devbin\libs\ToObject\ObjectClass, 
    Devbin\libs\ToObject\StringClass,
    Devbin\libs\ToObject\ArrayClass,
    Devbin\libs\ToObject\HashClass;


require_once 'PHPUnit/Autoload.php';
require_once(dirname(__FILE__) . '/../../src/ToObject.php');


class StringClassTest extends \PHPUnit_Framework_TestCase
{
    public function test_tostring()
    {
        $str = new StringClass("foobar");
        $this->assertSame("foobar", $str->__tostring());
    }
    
    public function test_to_native()
    {
        $str = new StringClass("foobar");
        $this->assertSame("foobar", $str->to_native());
    }
    
    public function test__()
    {
        $str = new StringClass("foobar");
        $this->assertEquals(new StringClass("foo"), $str->_(0, 3));
        $this->assertEquals(new StringClass("oba"), $str->_(2, 3));
        
        $this->assertEquals(new StringClass("oobar"), $str->_(1));
        $this->assertEquals(new StringClass("bar"), $str->_(-3));
        $this->assertEquals(new StringClass("b"), $str->_(-3, 1));
    }
    
    public function test_offset_exists()
    {
        $str = new StringClass("foobar");
        $this->assertTrue(isset($str[0]));
        $this->assertTrue(isset($str[5]));
        $this->assertFalse(isset($str[6]));
    }
    
    public function test_offset_unset()
    {
        $str = new StringClass("foobar");
        unset($str[4]);
        $this->assertEquals(new StringClass("foob"), $str);
        
        unset($str[7]);
        $this->assertEquals(new StringClass("foob"), $str);
    }
    
    public function test_offste_set()
    {
        $str = new StringClass("foo");
        $str[] = "bar";
        $this->assertEquals(new StringClass("foobar"), $str);
        
        $str[2] = "x";
        $this->assertEquals(new StringClass("foxbar"), $str);
        $str[3] = null;
        $this->assertEquals(new StringClass("fox"), $str);
    }
    
    public function test_offset_get()
    {
        $str = new StringClass("foobar");
        $this->assertSame("f", $str[0]);
        $this->assertSame("b", $str[3]);
        $this->assertSame(null, $str[7]);
    }
    
    public function test_between()
    {
        $str = new StringClass("foobar");
        $this->assertTrue($str->between("e", "g"));
        $this->assertFalse($str->between("E", "G"));
    }
    
    public function test_betweeni()
    {
        $str = new StringClass("foobar");
        $this->assertTrue($str->betweeni("e", "g"));
        $this->assertTrue($str->betweeni("E", "G"));
        $this->assertFalse($str->betweeni("g", "i"));
    }
    
    public function test_count()
    {
        $str = new StringClass("foobr");
        $this->assertEquals(6, $str->count());
    }
    
    public function test_capitalize()
    {
        $str = new StringClass("this is  string.");
        $this->assertEquals(new StringClass("This is  string."), $str->capitalize());
        $this->assertNotEquals(new StringClass("this is  string."), $str->capitalize());
    }
    
    public function test_delete()
    {
        $str = new StringClass("this is  string.");
        $this->assertEquals(new StringClass("ths s  strng."), $str->delete('i', false));
        
        $str = new StringClass("this is   string.");
        $this->assertEquals(new StringClass("ths s  strng."), $str->delete('/i|\s(?=\s[^\s])/', true));
    }
    
    public function test_each_byte()
    {
        $str = new StringClass("a");
        
        $str->each_byte(function($e, $i) use ($str) {
            $this->assertSame(ord(substr($str->to_native(), $i, 1)), ord($e));
        });
    }
    
    public function test_each_char()
    {
        $str = new StringClass("a");
        
        $str->each_char(function($e, $i) use ($str) {
            $this->assertSame(mb_substr($str->to_native(), $i, 1, $str->getEncoding()), $e);
        });
    }
    
    public function test_gsub()
    {
        $str = new StringClass("this is  string.");
        $f =  $str->gsub('i', "_");
        $this->assertEquals(new StringClass("th_s _s  str_ng."), $f);
        
        $str = new StringClass("this is   string.");
        $this->assertEquals(new StringClass("th_s _s_  str_ng."), $str->gsub('/i|\s(?=\s[^\s])/', '_', [], true));
    }
    // 
    public function test_includes()
    {
        $str = new StringClass("this is  string.");
        
        $this->assertTrue($str->includes("", false));
        $this->assertTrue($str->includes("i", false));
        $this->assertFalse($str->includes("q", false));
        
        $this->assertTrue($str->includes("/i[sn ]/", true));
        $this->assertFalse($str->includes("/i[q]/", true));
    }
    
    public function test_is_empty()
    {
        $str = new StringClass("");
        $this->assertTrue($str->is_empty());
        
        $str = new StringClass("foo");
        $this->assertFalse($str->is_empty());
    }
    
    public function test_is_lower()
    {
        $str = new StringClass("contains only lower");
        $this->assertTrue($str->is_lower());
        
        $str = new StringClass("Contains only lower");
        $this->assertFalse($str->is_lower());
    }
    
    public function test_is_upper()
    {
        $str = new StringClass("CONTAINS ONLY UPPER");
        $this->assertTrue($str->is_upper());
        
        $str = new StringClass("Contains only upper");
        $this->assertFalse($str->is_upper());
    }
    
    public function test_ltrim()
    {
        $str = new StringClass(" foo bar");
        $this->assertEquals(new StringClass("foo bar"), $str->ltrim());
        $this->assertEquals(new StringClass("oo bar"), $str->ltrim("f "));
        $this->assertEquals(new StringClass("oo bar"), $str->ltrim(" f"));
    
    }
    
    public function test_rtrim()
    {
        $str = new StringClass("foo bar ");
        $this->assertEquals(new StringClass("foo bar"), $str->rtrim());
        $this->assertEquals(new StringClass("foo ba"), $str->rtrim("r "));
        $this->assertEquals(new StringClass("foo ba"), $str->rtrim(" r"));
    }
    
    public function test_size()
    {
        $str = new StringClass("foobr");
        $this->assertEquals(8, $str->size());
    }
    
    public function test_split()
    {
        $str = new StringClass("this is  string.");
        
        $expected = new ArrayClass(["this", "is", "", "string."]);
        $this->assertEquals($expected, $str->split(" ", false));
        $this->assertEquals($expected, $str->split("/\s/", true));
        
    }
    
    public function test_stripos()
    {
        $str = new StringClass("this is  strIng.");
    
        $this->assertSame(2, $str->stripos("i", 0, 1));
        $this->assertSame(5, $str->stripos("i", 0, 2));
        $this->assertSame(13, $str->stripos("i", 0, 3));
        $this->assertSame(false, $str->stripos("i", 0, 4));
    }
    
    public function test_strpos()
    {
        $str = new StringClass("this Is  strIng.");
        
        $this->assertSame(2, $str->strpos("i", 0, 1));
        $this->assertSame(false, $str->strpos("i", 0, 2));
        $this->assertSame(false, $str->strpos("i", 0, 3));
    }
    
    public function test_strripos()
    {
        $str = new StringClass("this is  strIng.");
        
        $this->assertSame(13, $str->strripos("i", 0, 1));
        $this->assertSame(5, $str->strripos("i", 0, 2));
        $this->assertSame(2, $str->strripos("i", 0, 3));
        $this->assertSame(false, $str->strripos("i", 0, 4));
            
            // $needle, $offset = 0, $nth = 1, &$matches = null)
    }
    
    public function test_strrpos()
    {
        $str = new StringClass("this Is  strIng.");
        $this->assertSame(2, $str->strrpos("i", 0, 1));
        
        
        $this->assertSame(false, $str->strrpos("i", 0, 2));
        $this->assertSame(false, $str->strrpos("i", 0, 3));
    }
    
    public function test_sub()
    {
        $str = new StringClass("this is  string.");
        $this->assertEquals(new StringClass("th_s is  string."), $str->sub('i', '_', [], false));
        
        $str = new StringClass("this is   string.");
        $this->assertEquals(new StringClass("th_s is   string."), $str->sub('/i|\s(?=\s[^\s])/', '_', [], true));
    }
    
    public function test_titlecase()
    {
        $str = new StringClass("foo bar  baz");
        $this->assertEquals(new StringClass("Foo Bar  Baz"), $str->titlecase());
    }
    
    public function test_to_hex()
    {
        $str = new StringClass("foo  bar");
        
        $expected = new ArrayClass(["66", "6f", "6f", "20", ["ef", "a3", "bf"], "20", "62", "61", "72"]);
        $this->assertEquals($expected, $str->to_hex(true));
        
        $expected = new ArrayClass(["66", "6f", "6f", "20", "ef", "a3", "bf", "20", "62", "61", "72"]);
        $this->assertEquals($expected, $str->to_hex(false));
    }
    
    public function test_to_lower()
    {
        $str = new StringClass("fOo Bar  BAZ");
        $this->assertEquals(new StringClass("foo bar  baz"), $str->to_lower());
    }
    
    public function test_to_oct()
    {
        $str = new StringClass("foo  bar");
        
        $expected = new ArrayClass(["146", "157", "157", "40", ["357", "243", "277"], "40", "142", "141", "162"]);
        $this->assertEquals($expected, $str->to_oct(true));
                
        $expected = new ArrayClass(["146", "157", "157", "40", "357", "243", "277", "40", "142", "141", "162"]);
        $this->assertEquals($expected, $str->to_oct(false));
    }
    
    public function test_to_upper()
    {
        $str = new StringClass("fOo Bar  BAZ");
        $this->assertEquals(new StringClass("FOO BAR  BAZ"), $str->to_upper());
    }
    
    public function test_trim()
    {
        $str = new StringClass(" foo bar ");
        $this->assertEquals(new StringClass("foo bar"), $str->trim());
        $this->assertEquals(new StringClass("oo bar"), $str->trim("f "));
        $this->assertEquals(new StringClass("foo ba"), $str->trim(" r"));
    }
    
    public function test_gsub_regex_callback()
    {
        $str = new StringClass("this is   string.");
        $actual = $str->gsub('/i|\s(?=[^\s])/', function() { return "_"; }, [], true);
        $this->assertEquals(new StringClass("th_s__s __str_ng."), $actual);
    }
    
    public function test_gsub_pattern_callback()
    {
        $str = new StringClass("this is  string.");
        $actual = $str->gsub('i', function($pattern, $match, $newstr) { return "_"; });
        $this->assertEquals(new StringClass("th_s _s  str_ng."), $actual);
         
        $str = new StringClass("this is  string.");
        $actual = $str->gsub('i', ['StringClassTest', 'gsub_static_pattern_callback']);
        $this->assertEquals(new StringClass("th*s *s  str*ng."), $actual);
    }
    
    public function test_sub_regex_callback()
    {
        $str = new StringClass("this is  string.");
        $actual = $str->sub('/i|\s(?=[^\s])/', function() { return "_"; }, [], true);
        $this->assertEquals(new StringClass("th_s is  string."), $actual);
    }
    
    public function test_sub_pattern_callback()
    {
        $str = new StringClass("this is  string.");
        $actual = $str->sub('i', function($pattern, $match, $newstr) { return "_"; });
        $this->assertEquals(new StringClass("th_s is  string."), $actual);
    }
    
    public static function gsub_static_pattern_callback()
    {
        return "*";
    }
    
    public static function gsub_static_regex_callback()
    {
        return "*";
    }
}




?>
