<?php

namespace Devbin\libs\ToObject;

use \Devbin\libs\ToObject\Utils\EnumerableReflection;

/**
 * StringClass
 * 
 * StringClass holds and manipulates an arbitrary sequence of bytes, typically 
 * representing characters. It does so by acting as a wrapper around PHP's
 * native string. Because StringClass is a sequence of bytes, individual 
 * characters can be access via a subscript. 
 * 
 * To instantiate a StringClass object one has to pass in a native PHP string
 * and optionally an encoding for this string which defaults to `UTF-8'.
 * 
 * StringClass uses:
 * StringClass does not use any traits.
 * StringClass implements \ArrayAccess and IComparable
*/
class StringClass extends ObjectClass implements \ArrayAccess, IComparable
{    
    /**
     * __construct
     *
     * @access  public
     * @param   string    $object           The input string.
     * @param   string    $encoding         Encoding for the input string.
     * @throws  UnexpectedValueException    When `$object' is not a string.
    */
    public function __construct($object, $encoding = ObjectClass::DEFAULT_ENCODING)
    {
        if (!is_string($object))
            throw new \UnexpectedValueException(sprintf("Unexpected value in %s. Value must be of type string but is of type %s", __METHOD__, gettype($object)));
        
        $this->_object = $object;
        $this->_encoding = $encoding;
        mb_regex_encoding($encoding);
    }
    
    /**
     * __tostring
     * Returns `self' as a string.
     *
     * @access  public
     * @return  string
    */
    public function __tostring()
    {
        return $this->_object;
    }
    
    /**
     * __new__
     * Creates a new instance of self in a safe manner.
     *
     * @access  protected
     * @param   string      $data   The data to create a new StringClass for/with.
     * @return  StringClass
    */
    protected function __new__($data)
    {
        return new self($data, $this->_encoding);
    }
    
    /**
     * to_s
     * Returns a string presentation of `self'.
     * 
     * @access  public
     * @return  string
    */
    public function to_s()
    {
        return $this->__tostring();
    }

    /**
     * to_native
     * Returns `self' as string.
     * 
     * @access  public
     * @param   bool        $recursive    Whether to return everything or just the first level as native PHP.
     *                                   Since it's a string, the param is ignored. (suppresses a `Strict' message)
     * @return  string
    */
    public function to_native($recursive = true)
    {
        return $this->_object;
    }
    
    /**
     * Behaves exactly like mb_substr. {@link http://php.net/mb_substr}
     *
     * @access  public
     * @param   int        $offset        Position of first character to use from str.
     * @param   int        $length        Maximum number of characters to use from str.
     * @return  StringClass
    */
    public function _($offset, $length = null)
    {
        if (is_null($length))
            $length = mb_strlen($this->_object, $this->_encoding) - $offset;

        return $this->__new__(mb_substr($this->_object, $offset, $length, $this->_encoding));
    }
    
    // 
    // implementation of ArrayAccess
    // 
    
    /**
     * offsetSet
     * Sets the given offset to the given value.
     * Note that assigning NULL, int 0, 0x00 or anything else
     * resulting to 0 in "ord(value) | 0" will be the equivalent of terminating
     * the string at the given offset. C style :)!
     * When appending, thus $str[], the whole $value is used.
     * Otherwise, thus $str[$n], only the first character of $value is used.
     * 
     * @access  public
     * @param   int     $offset     Offset to alter.
     * @param   string  $value      Value to set $offset to.
     * @return  void
    */
    public function offsetSet($offset, $value)
    {
        $sub = substr($value, 0, 1);
        $is_null = (($value === 0) || ((ord($sub) | 0) === 0));
        
        if (is_null($offset))
        {
            if (!$is_null)
                $this->_object .= $value;
        } else {
            if ($is_null)
            {
                // \0 the string
                $this->_object = mb_substr($this->_object, 0, $offset, $this->_encoding);
            } else {
                $before = mb_substr($this->_object, 0, $offset, $this->_encoding);
                $after = mb_substr($this->_object, $offset+1, $this->count(), $this->_encoding);
                $this->_object = $before.$sub.$after;
            }
        }
    }
    
    /**
     * offsetExists
     * Checks whether the given offset exists in `self'.
     * 
     * @access  public
     * @param   int     $offset     Offset to check its existence of.
     * @return  bool
    */
    public function offsetExists($offset)
    {
        $tmp = mb_substr($this->_object, $offset, 1, $this->_encoding);
        return !empty($tmp);
    }
    
    /**
     * offsetUnset
     * Unsets the given offset. Note that unsetting an offset is the equivalent
     * of setting it to `null'.
     * @see StringClass::offsetSet()
     * 
     * @access  public
     * @param   int     $offset     Offset to unset.
     * @return  void
    */
    public function offsetUnset($offset)
    {
        $this[$offset] = null;
    }
    
    /**
     * offsetGet
     * Return the value of the given offset.
     * 
     * @access  public
     * @param   int     $offset     Offset to return its value of.
     * @return  string or `null'
    */
    public function offsetGet($offset)
    {
        return isset($this->_object[$offset]) ? mb_substr($this->_object, $offset, 1, $this->_encoding) : null;
    }
    
    // 
    // implementation of IComparable
    // 
    
    /**
     * between
     * Checks whether `self' is between two values.
     * Between is an exclusive and case-sensitive check, thus > and < instead of >= and <=.
     * 
     * @access  public
     * @param   string  $a  A string ought to be smaller than `self'.
     * @param   string  $b  A string ought to be greater than `self'.
     * @return  bool
    */
    public function between($a, $b)
    {
        return (
            (strcmp($this->to_s(), $a) > 0) && 
            (strcmp($this->to_s(), $b) < 0)
        );
    }
    
    /**
     * betweeni
     * Checks whether `self' is between two values.
     * Between is an exclusive and case-insensitive check, thus > and < instead of >= and <=.
     * 
     * @access  public
     * @param   string  $a  A string ought to be smaller than `self'.
     * @param   string  $b  A string ought to be greater than `self'.
     * @return  bool
    */
    public function betweeni($a, $b)
    {
        return (
            (strcasecmp($this->to_s(), $a) > 0) && 
            (strcasecmp($this->to_s(), $b) < 0)
        );
    }
    
    // 
    // implementation of \Countable
    // 
    
    /**
     * count
     * Returns the amount of characters in `self'.
     * 
     * @access  public
     * @return  int
    */
    public function count()
    {
        return $this->length();
    }
    
    // 
    // let there be methods
    // 
    
    /**
     * capitalize
     * Capitalizes `self'.
     * 
     * @access  public
     * @return  StringClass
    */
    public function capitalize()
    {
        $length = mb_strlen($this->_object, $this->_encoding);
        $str = mb_strtoupper(mb_substr($this->_object, 0, 1, $this->_encoding), $this->_encoding).mb_substr($this->_object, 1, ($length - 1), $this->_encoding);
        return $this->__new__($str);
    }
    
    /**
     * delete
     * Deletes all occurrences of `$pattern'.
     * 
     * @access  public
     * @param   string  $pattern    Pattern to look for.
     * @param   bool    $regex      Whether pattern is a regex.
     * @return  StringClass     
    */
    public function delete($pattern, $regex = false)
    {
        return $this->gsub($pattern, '', array(), $regex);
    }
    
    /**
     * each_byte
     * Passes each byte of the collection to `$callback'.
     * 
     * @access  public
     * @param   callable    $callback   Callback (static) method or function.
     * @param   array       $args       Array with params the user wants to pass on to the callback method.
     * @return  self
    */
    public function each_byte(callable $callback, array $args = array())
    {
        $reflection = EnumerableReflection::parse_callback($callback);
        $size = $this->size();
        
        for ($i = 0; $i < $size; $i++)
        {
            $val = substr($this->_object, $i, 1);
            EnumerableReflection::callback_block($reflection, $callback, EnumerableReflection::callback_arguments($reflection, [$i, $val, $args]));
        }
        
        return $this;
    }

    /**
     * each_char
     * Passes each char of the collection to `$callback'.
     * 
     * @access  public
     * @param   callable    $callback   Callback (static) method or function.
     * @param   array       $args       Array with params the user wants to pass on to the callback method.
     * @return  self
    */
    public function each_char(callable $callback, array $args = array())
    {
        $reflection = EnumerableReflection::parse_callback($callback);
        $length = $this->length();
        
        for ($i = 0; $i < $length; $i++)
        {
            $val = mb_substr($this->_object, $i, 1, $this->_encoding);
            EnumerableReflection::callback_block($reflection, $callback, EnumerableReflection::callback_arguments($reflection, [$i, $val, $args]));
        }
        
        return $this;
    }

    /**
     * gsub
     * Replaces each match on `$pattern' with `$replacement'.
     * 
     * @access  public
     * @param   string  $pattern        Pattern to look for.
     * @param   string  $replacement    Replacement string or a callback.
     * @param   array   $args           Array with params the user wants to pass on to the callback method.
     * @param   bool    $regex          Whether pattern is a regex.
     * @return  StringClass
    */
    public function gsub($pattern, $replacement, array $args = array(), $regex = false)
    {
        try
        {
            $reflection = EnumerableReflection::parse_callback($replacement);

            if ($regex)
            {
                $new = $this->regex_callback($pattern, $replacement, -1);
            } else {
                $new = $this->pattern_callback($reflection, $pattern, $replacement, -1);
            }
        } catch (\UnexpectedValueException $e) {
            // replacement is not callable
            if ($regex)
            {
                $new = $this->regex_replace($pattern, $replacement, -1);
            } else {
                $new = $this->pattern_replace($pattern, $replacement, -1);
            }
        }
        
        return $new;
    }

    /**
     * includes
     * Returns true if `self' includes `$pattern'.
     *
     * @access  public
     * @param   string  $pattern    Pattern to look for.
     * @param   bool    $regex      Whether pattern is a regex.
     * @return  bool
    */
    public function includes($pattern, $regex = false)
    {
        if ($regex)
        {
            $ret = (bool) preg_match($pattern, $this->to_native());
        } else {
            $ret = (mb_strpos($this->to_native(), $pattern, 0, $this->_encoding) !== false)? true : false;
        }
        
        return $ret;
    }

    /**
     * is_empty
     * Returns true if `self' does not contain any chars.
     * 
     * @access  public
     * @return  bool
    */
    public function is_empty()
    {
        return (strcmp($this->_object, "") === 0);
    }

    /**
     * is_lower
     * Returns true if `self' only contains lowercase chars.
     * 
     * @access  public
     * @return  bool
    */
    public function is_lower()
    {
        return (strcmp(mb_strtolower($this->_object, $this->_encoding), $this->_object) === 0);
    }

    /**
     * is_upper
     * Returns true if `self' only contains uppercase chars.
     * 
     * @access  public
     * @return  bool
    */
    public function is_upper()
    {
        return (strcmp(mb_strtoupper($this->_object, $this->_encoding), $this->_object) === 0);
    }

    /**
     * length
     * Returns the amount of characters in `self'.
     * 
     * @access  public
     * @return  int
    */
    public function length()
    {
        return mb_strlen($this->_object, $this->_encoding);
    }

    /**
     * ltrim
     * Behaves exactly like ltrim. {@link http://php.net/ltrim}
     * 
     * @access  public
     * @param   string    $charlist    Character list to strip.
     * @return  StringClass
    */
    public function ltrim($charlist = false)
    {
        $tmp = ($charlist)
            ? ltrim($this->_object, $charlist)
            : ltrim($this->_object);
            
        return $this->__new__($tmp);
    }

    /**
     * rtrim
     * Behaves exactly like rtrim. {@link http://php.net/rtrim}
     * 
     * @access  public
     * @param   string      $charlist   Character list to strip.
     * @return  StringClass
    */
    public function rtrim($charlist = false)
    {
        $tmp = ($charlist)
            ? rtrim($this->_object, $charlist)
            : rtrim($this->_object);
            
        return $this->__new__($tmp);
    }

    /**
     * size
     * Returns the amount of bytes in `self'.
     * 
     * @access  public
     * @return  int
    */
    public function size()
    {
        return strlen($this->_object);
    }

    /**
     * split
     * Splits `self' into an ArrayClass using `$delim' as delimiter.
     * 
     * @access  public
     * @param   sting   $pattern    Delimiter used to split.
     * @param   bool    $regex      Whether delim is a regex.
     * @return  ArrayClass
    */
    public function split($pattern, $regex = false)
    {
        return ($regex)
            ? new ArrayClass(preg_split($pattern, $this->_object))
            : new ArrayClass(explode($pattern, $this->_object));
    }

    /**
     * stripos
     * Behaves like mb_stripos. {@link http://php.net/mb_stripos}
     * In addition, returns the position of the `$nth' match or false when there is no `$nth' match.
     * 
     * @access  public
     * @param   string  $needle     String to find.
     * @param   int     $offset     The position to start searching.
     * @param   int     $nth        Position of the Nth occurrence.
     * @param   int     &$matches   If specified, this variable will be filled with the number of matches found.
     * @return  int     position
    */
    public function stripos($needle, $offset = 0, $nth = 1, &$matches = null)
    {
        $i = 0;
        $m = 0;
        
        while ($i < $nth)
        {
            $offset = mb_stripos($this->_object, $needle, $offset, $this->_encoding);
        
            if ($offset === false)
                break;
        
            $offset++;
            $m++;
            $i++;
        }
        
        $matches = $m;
        return ($offset === false)? false : $offset-1;
    }

    /**
     * strpos
     * Behaves like mb_strpos. {@link http://php.net/mb_strpos}
     * In addition, returns the position of the `$nth' match or false when there is no `$nth' match.
     * 
     * @access  public
     * @param   string  $needle     String to find.
     * @param   int     $offset     The position to start searching.
     * @param   int     $nth        Position of the Nth occurrence.
     * @param   int     &$matches   If specified, this variable will be filled with the number of matches found.
     * @return  int     position
    */
    public function strpos($needle, $offset = 0, $nth = 1, &$matches = null)
    {
        $i = 0;
        $m = 0;
        
        while ($i < $nth)
        {
            $offset = mb_strpos($this->_object, $needle, $offset, $this->_encoding);
        
            if ($offset === false)
                break;
        
            $offset++;
            $m++;
            $i++;
        }
        
        $matches = $m;
        return ($offset === false)? false : $offset-1;
        
    }

    /**
     * strripos
     * Behaves like mb_strripos. {@link http://php.net/mb_strripos}
     * In addition, returns the position of the `$nth' match or false when there is no `$nth' match.
     * 
     * @access  public
     * @param   string  $needle     String to find.
     * @param   int     $offset     The position to start searching.
     * @param   int     $nth        Position of the Nth occurrence.
     * @param   int     &$matches   If specified, this variable will be filled with the number of matches found.
     * @return  int     position
    */
    public function strripos($needle, $offset = 0, $nth = 1, &$matches = null)
    {
        $i = 0;
        $m = 0;
        $len = mb_strlen($this->_object, $this->_encoding);
        
        while ($i < $nth)
        {
            $offset = mb_strripos($this->_object, $needle, $offset, $this->_encoding);
        
            if ($offset === false)
                break;
        
            $offset = -($len - $offset +1);
            $m++;
            $i++;
        }
        
        $matches = $m;
        return ($offset === false)? false : $len +1 +$offset;
    }

    /**
     * strrpos
     * Behaves like mb_strrpos. {@link http://php.net/mb_strrpos}
     * In addition, returns the position of the `$nth' match or false when there is no `$nth' match.
     * 
     * @access  public
     * @param   string  $needle     String to find.
     * @param   int     $offset     The position to start searching.
     * @param   int     $nth        Position of the Nth occurrence.
     * @param   int     &$matches   If specified, this variable will be filled with the number of matches found.
     * @return  int     position
    */
    public function strrpos($needle, $offset = 0, $nth = 1, &$matches = null)
    {
        $i = 0;
        $m = 0;
        $len = mb_strlen($this->_object, $this->_encoding);
        
        while ($i < $nth)
        {
            $offset = mb_strrpos($this->_object, $needle, $offset, $this->_encoding);
        
            if ($offset === false)
                break;
        
            $offset = -($len - $offset +1);
            $m++;
            $i++;
        }
        
        $matches = $m;
        return ($offset === false)? false : $len +1 +$offset;
    }

    /**
     * sub
     * Replaces the first match on `$pattern' with `$replacement'.
     * 
     * @access  public
     * @param   string  $pattern        Pattern to look for.
     * @param   string  $replacement    Replacement string or a callback.
     * @param   array   $args           Array with params the user wants to pass on to the callback method.
     * @param   bool    $regex          Whether pattern is a regex.
     * @return  StringClass
    */
    public function sub($pattern, $replacement, array $args = array(), $regex = false)
    {
        try
        {
            $reflection = EnumerableReflection::parse_callback($replacement);
            
            if ($regex)
            {
                $new = $this->regex_callback($pattern, $replacement, 1);
            } else {
                $new = $this->pattern_callback($reflection, $pattern, $replacement, 1);
            }
        } catch (\Exception $e) {
            // replacement is not callable
            if ($regex)
            {
                $new = $this->regex_replace($pattern, $replacement, 1);
            } else {
                $new = $this->pattern_replace($pattern, $replacement, 1);
            }
        }
        
        return $new;
    }

    /**
     * titlecase
     * Capitalizes each word.
     * 
     * @access  public
     * @return  StringClass
    */
    public function titlecase()
    {
        $exp = new ArrayClass(preg_split("/\s/u", $this->_object), $this->_encoding);
        return $exp->collect(function($e) { return $e->capitalize(); })->join(' ');
    }

    /**
     * to_hex
     * Stores a hexadecimal presentation of each char (or byte) in an array.
     * 
     * @access  public
     * @param   bool    $byte   Whether to store multibyte char in a sub array.
     * @return  ArrayClass
    */
    public function to_hex($byte = false)
    {
        $new = array();

        if ($byte)
        {
            $len = mb_strlen($this->_object, $this->_encoding);
            for ($i=0; $i < $len; $i++)
            {
                $chr = unpack('H*', mb_substr($this->_object, $i, 1, $this->_encoding));
                
                $chunks = str_split(array_shift($chr), 2);
                if (count($chunks) === (int) 1)
                    $new[] = array_shift($chunks);
                else
                    $new[] = $chunks;
            }
        } else {
            $len = strlen($this->_object);
            for ($i=0; $i < $len; $i++)
            {
                $chr = unpack('H*', substr($this->_object, $i, 1));

                $chunks = str_split(array_shift($chr), 2);
                if (count($chunks) === (int) 1)
                    $new[] = array_shift($chunks);
                // else
                    // $new[] = $chunks;
            }
        }

        return new ArrayClass($new, $this->_encoding);
    }

    /**
     * to_lower
     * Changes all chars to lowercase.
     * 
     * @access  public
     * @return  StringClass
    */
    public function to_lower()
    {
        return $this->__new__(mb_strtolower($this->_object, $this->_encoding));
    }

    /**
     * to_oct
     * Stores an octal presentation of each char (or byte) in an array.
     * 
     * @access  public
     * @param   bool    $byte   Whether to store multibyte char in a sub array.
     * @return  ArrayClass
    */
    public function to_oct($byte = false)
    {
        $new = array();

        if ($byte)
        {
            $len = mb_strlen($this->_object, $this->_encoding);
            for ($i=0; $i < $len; $i++)
            {
                $chr = unpack("H*", mb_substr($this->_object, $i, 1, $this->_encoding));

                $chunks = str_split(array_shift($chr), 2);
                
                if (count($chunks) === (int) 1)
                {
                    $tmp = array_shift($chunks);
                    $new[] = decoct(hexdec($tmp));
                } else {
                    $tmp = array();
                    foreach ($chunks as $chunk) 
                    {
                        $tmp[] = decoct(hexdec($chunk));
                    }
                    $new[] = $tmp;
                }
            }
        } else {
            $len = strlen($this->_object);
            for ($i=0; $i < $len; $i++)
            {
                $chr = unpack("H*", substr($this->_object, $i, 1));

                $chunks = str_split(array_shift($chr), 2);
                if (count($chunks) === (int) 1)
                {
                    $tmp = array_shift($chunks);
                    $new[] = decoct(hexdec($tmp));
                } 
                // else {
                //     $tmp = array();
                //     foreach ($chunks as $chunk) 
                //     {
                //         $tmp[] = decoct(hexdec($chunk));
                //     }
                //     $new[] = $tmp;
                // }
            }
        }
        
        return new ArrayClass($new, $this->_encoding);
    }

    /**
     * to_upper
     * Changes all chars to uppercase.
     * 
     * @access  public
     * @return  StringClass
    */
    public function to_upper()
    {
        return $this->__new__(mb_strtoupper($this->_object, $this->_encoding));
    }

    /**
     * trim
     * Behaves exactly like trim. {@link http://php.net/trim}
     * 
     * @access  public
     * @param   string  $charlist   Character list to strip.
     * @return  StringClass
    */
    public function trim($charlist = false)
    {
        $tmp = ($charlist)
            ? trim($this->_object, $charlist)
            : trim($this->_object);
            
        return $this->__new__($tmp);
    }
    
    
    /**
     * regex_callback
     * Applies a callback method on each matched regex.
     *
     * @access  public
     * @param   string      $regex      The regex pattern.
     * @param   callable    $callback   The callback method.
     * @param   int         $times      Amount of times to apply the callback.
     * @return  StringClass
    */
    private function regex_callback($regex, callable $callback, $times = -1)
    {
        return $this->__new__(preg_replace_callback($regex, $callback, $this->_object, $times));
    }

    /**
     * regex_replace
     * Replaces each $regex-match with $replacement.
     *
     * @access  public
     * @param   string  $regex          The regex pattern.
     * @param   string  $replacement    The replacement string.
     * @param   int     $times          Amount of times to apply the callback.
     * @return  StringClass
    */
    private function regex_replace($regex, $replacement, $times = -1)
    {
        return $this->__new__(preg_replace($regex, $replacement, $this->_object, $times));
    }

    /**
     * pattern_callback
     * Applies a callback method on each matched pattern.
     *
     * @access  public
     * @param   \ReflectionFunctionAbstract $reflection ReflectionFunctionAbstract instance of the callback.
     * @param   string                      $pattern    The string pattern.
     * @param   callable                    $callback   The callback method.
     * @param   int                         $times      Amount of times to apply the callback.
     * @return  StringClass
    */
    private function pattern_callback(\ReflectionFunctionAbstract $reflection, $pattern, callable $callback, $times = -1)
    {
        $ptr = 0;
        $offset = 0;
        $newstr = '';
        $match = 0;
        
        while (($offset = mb_strpos($this->_object, $pattern, $offset, $this->_encoding)) !== false && ($match < $times || $times == -1))
        {
            $match++;
            $newstr .= mb_substr($this->_object, $ptr, ($offset - $ptr), $this->_encoding);
            
            // EnumerableReflection::callback_block($reflection, $callback, [$pattern, $match, $newstr]);
            
            $static = ($reflection instanceof \ReflectionMethod)? $reflection->isStatic() : false;
            if ($static)
            {
                $newstr .= forward_static_call_array($callback, [$pattern, $match, $newstr]);
            } else {
                $newstr .= call_user_func_array($callback, [$pattern, $match, $newstr]);
            }

            $ptr = $offset + mb_strlen($pattern, $this->_encoding);
            $offset += mb_strlen($pattern, $this->_encoding);
        }
        
        $newstr .= mb_substr($this->_object, $ptr, ($this->length() - $ptr), $this->_encoding);
        return $this->__new__($newstr);
    }

    /**
     * pattern_replace
     * Replaces each $pattern with $replacement.
     *
     * @access  public
     * @param   string  $pattern        The string pattern.
     * @param   string  $replacement    The replacement string.
     * @param   int     $times          Amount of times to apply the callback.
     * @return  string
    */
    private function pattern_replace($pattern, $replacement, $times = -1)
    {
        // return $this->__new__(preg_replace("/".$regex."/", $regex, $this[0], $times));
        $ptr = 0;
        $offset = 0;
        $newstr = '';
        $match = 0;
        
        while (($offset = mb_strpos($this->_object, $pattern, $offset, $this->_encoding)) !== false && ($match < $times || $times == -1))
        {
            $match++;
            $newstr .= mb_substr($this->_object, $ptr, ($offset - $ptr), $this->_encoding);
            $newstr .= $replacement;
    
            $ptr = $offset + mb_strlen($pattern, $this->_encoding);
            $offset += mb_strlen($pattern, $this->_encoding);
        }
        
        $newstr .= mb_substr($this->_object, $ptr, ($this->length() - $ptr), $this->_encoding);
        return $this->__new__($newstr);
    }
}

?>
