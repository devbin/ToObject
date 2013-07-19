<?php

namespace Devbin\libs\ToObject; 

use \stdClass; 
use \ReflectionFunction;

/**
 *
 * HashClass
 * 
 * a Hash (HashClass) is a collection of key-value pairs. It is similar to
 * an array (ArrayClass), except that indexing is done via arbitrary keys
 * whereas an array uses an integer index. Integer indices are ignored (whereas
 * ArrayClass ignores non-integer key indices).
 * It's important to note that this HashClass IS MOST CERTAINLY NOT a HashTable
 * like one would find in Java. HashClass does NOT compute a hash value and
 * does NOT keeps track of some internal table. HashClass merely is an stdClass
 * on the inside.
 * 
 * HashClass uses:
 * - Iterator_t trait to implement \Iterator
 * - Count_t trait to implement \Countable
 * - Enumerable trait
*/
class HashClass extends ObjectClass implements \Iterator
{
    use Count_t, Iterator_t, Enumerable
    {
        Enumerable::collect as enum_collect;
        Enumerable::delete_if as enum_delete_if;
        Enumerable::detect as enum_detect;
        Enumerable::drop as enum_drop;
        Enumerable::drop_while as enum_drop_while;
        Enumerable::keep_if as enum_keep_if;
        Enumerable::select as enum_select;
    }
    
    /**
     * Constructor
     *
     * @access  public
     * @param   stdClass    $object     The input stdClass.
     * @param   string      $encoding   Encoding for the hash contents.
    */
    public function __construct(\stdClass $object, $encoding = ObjectClass::DEFAULT_ENCODING)
    {
        $this->_object = ObjectClass::builder($object, $encoding);
        $this->_encoding = $encoding;
    }
    
    /**
     * __tostring
     * Iterates each element and returns it with a newline appended.
     * 
     * @access  public
     * @return  string
    */

    public function __tostring()
    {
        $return = array();
        foreach ($this->_object as $key => $value)
        {
            $return[] = $value;
        }
        return implode("\n", $return);
    }
    
    /**
     * __get
     * Returns the value for `$key'.
     * 
     * @access  public
     * @param   string  $key    Key to return the value of.
     * @return  mixed   Value of `$key' or null.
    */
    public function __get($key)
    {
        return isset($this->_object->{$key}) 
            ? $this->_object->{$key}
            : null;
    }
    
    /**
     * __set
     * Sets or creates `$key' with value `$value'.
     * 
     * @access  public
     * @param   string  $key    Key to set.
     * @param   mixed   $value  Value to assign to key.
     * @return  void
    */
    public function __set($key, $value)
    {
        $this->_object->{$key} = ObjectClass::builder($value, $this->_encoding);
    }
    
    /**
     * __unset
     * Unsets (removes) an element from `self'.
     * This is not the same as assigning null to $key.
     * 
     * @access  public
     * @param   string  $key    Key of the value to unset.
     * @return  void
    */
    public function __unset($key)
    {
        unset($this->_object->{$key});
    }
    
    /**
     * __new__
     * Constructs a new object related to ToObject
     * 
     * @access  protected
     * @param   ObjectClass     $data    Data for the new object.
     * @param   int             $type    Type of the new object.
     * @return  mixed           ObjectClass or a native PHP type.
    */
    protected function __new__($data, $type)
    {
        $ret = $data;
        switch($type)
        {
            // case self::TYPE_STRINGCLASS:
            //     $ret =  new StringClass($data, $this->_encoding);
            //     break;
            case self::TYPE_ARRAYCLASS:
                $ret = new ArrayClass($data, $this->_encoding);
                break;
            case self::TYPE_HASHCLASS:
                $ret = new HashClass($data, $this->_encoding);
                break;
            // default:
            //     return $data;
        }
        return $ret;
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
        $collection = array();
        foreach ($this->_object as $key => $value)
        {
            if (is_object($value) && $value instanceof ObjectClass)
            {
                if ($value->is_a('ArrayClass') || $value->is_a('HashClass'))
                {
                    $collection[] = sprintf(":%s => %s", $key, $value->to_s());
                } elseif ($value->is_a('StringClass')) {
                    $collection[] = sprintf(":%s => \"%s\"", $key, addslashes($value->to_s()));
                }
            } else {
                $collection[] = sprintf(":%s => %s", $key, $value);
            }
        }
        return sprintf("{ %s }", implode(', ', $collection));
    }
    
    /**
     * to_native
     * Creates a stdClass with all elements of `self' and returns it.
     * 
     * @access  public
     * @param   bool         $recursive  Whether to return everything or just the first level as native PHP.
     * @return  stdClass
    */
    public function to_native($recursive = true)
    {
        $return = new stdClass;
        if ($recursive)
        {
            foreach ($this as $key => $value)
            {
                if (is_object($value) && $value instanceof ObjectClass)
                    $return->{$key} = $value->to_native(true);
                else
                    $return->{$key} = $value;
            }
        } else {
            foreach ($this as $key => $value)
            {
                $return->{$key} = $value;
            }
        }
        return $return;
    }

    /**
     * to_a
     * Assumes `self' is built up in the format: { :p => q, :r => s }
     * and returns an ArrayClass containing the [[key, value]] pairs.
     * 
     * @access  public
     * @return  ArrayClass
     * @example { :name => 'john', :age => 36} would return 
     *             [['name', 'john'], ['age', 36]]
    */
    public function to_a()
    {
        $collection = array();
        foreach ($this as $key => $value)
        {
            $collection[] = [$key, $value];
        }
        return $this->__new__($collection, ObjectClass::TYPE_ARRAYCLASS);
    }
    
    /**
     * to_native_a
     * Builds up a native PHP array preserving keys.
     * Whereas `to_native()' returns a stdClass, `to_native_a()' returns an array.
     * This method exists because a php array can contain both numerical as non-numerical
     * keys, and so one might want to use data in the form of an array.
     * Naturally, HashClass stores its data in a stdClass.
     * 
     * @access  public
     * @param   bool        $recursive  Whether to return everything or just the first level as native PHP
     * @return  array       Contains data of `self'.
     * @example { :name => 'john', :age => 36} would return 
     *             ['name' => 'john', 'age', => 36]
    */
    public function to_native_a($recursive = true)
    {
        $return = array();
        if ($recursive)
        {
            foreach ($this as $key => $value)
            {
                if (is_object($value) && $value instanceof ObjectClass)
                {
                    if ($value->is_a('HashClass'))
                        $return[$key] = $value->to_native_a(true);
                    else
                        $return[$key] = $value->to_native(true);
                }
                else
                    $return[$key] = $value;
            }
        } else {
            foreach ($this as $key => $value)
            {
                $return[$key] = $value;
            }
        }
        return $return;
    }

    /**
     * includes
     * Checks for existence of a key and returns true if it does,
     * false otherwise.
     * 
     * @access  public
     * @param   mixed    $key    Key to look for.
     * @return  bool
     * @see Enumerable::includes
    */
    public function includes($key)
    {
        $collection = $this->to_native_a(false);
        return array_key_exists($key, $collection);
    }

    /**
     * collect
     * Passes each element of the collection to `$callback'. Returns an array with the results of the callback.
     * 
     * @access  public
     * @param   callable    $callback   Callback method.
     * @param   array       $args       Array with addition parameters (specified by the user).
     * @return  ArrayClass
     * @see     Enumerable::collect
    */
    public function collect($callback, array $args = array())
    {
        $result = $this->enum_collect($callback, $args);
        return $this->__new__($result, ObjectClass::TYPE_ARRAYCLASS);
    }
    
    /**
     * delete_if
     * Passes each element of the collection to `$callback'. Returns an array with all elements for which the callback returns true.
     * 
     * @access  public
     * @param   callable    $callback   Callback (static) method or function.
     * @param   array       $args       Array with addition parameters (specified by the user).
     * @return  HashClass
     * @see     Enumerable::delete_if
    */
    public function delete_if($callback, array $args = array())
    {
        $result = $this->enum_delete_if($callback, $args);
        return $this->__new__((object) $result, ObjectClass::TYPE_HASHCLASS);
    }
    
    /**
     * detect
     * Passes each element of the collection to `$callback'. Returns the first element for which the callback returns true.
     * 
     * @access  public
     * @param   callable    $callback   Callback (static) method or function.
     * @param   array       $args       Array with addition parameters (specified by the user).
     * @return  HashClass
     * @see     Enumerable::detect
    */
    public function detect($callback, array $args = array())
    {
        $result = $this->enum_detect($callback, $args);
        if (is_null($result))
            return null;
        return $this->__new__((object) $result, ObjectClass::TYPE_HASHCLASS);
    }
    
    /**
     * drop
     * Drops the first n items of the object.
     * 
     * @access  public
     * @param   int         $n      Amount of items to drop (shift).
     * @return  HashClass
     * @see     Enumerable::drop
    */
    public function drop($n = 1)
    {
        $result = $this->enum_drop($n);
        return $this->__new__((object) $result, ObjectClass::TYPE_HASHCLASS);
    }
    
    /**
     * drop_while
     * Drops items from Enum while callback yields true.
     * 
     * @access  public
     * @param   callable    $callback   Callback (static) method or function.
     * @param   array       $args       Array with addition parameters (specified by the user).
     * @return  HashClass
     * @see     Enumerable::drop_while
    */
    public function drop_while($callback, array $args = array())
    {
        $result = $this->enum_drop_while($callback, $args);
        return $this->__new__((object) $result, ObjectClass::TYPE_HASHCLASS);
    }
    
    /**
     * keep_if
     * Passes each element of the collection to `$callback'. Returns an array with all elements for which the callback returns false.
     * 
     * @access  public
     * @param   callable    $callback   Callback (static) method or function.
     * @param   array       $args       Array with addition parameters (specified by the user).
     * @return  HashClass
     * @see     Enumerable::keep_if
    */
    public function keep_if($callback, array $args = array())
    {
        $result = $this->enum_keep_if($callback, $args);
        return $this->__new__((object) $result, ObjectClass::TYPE_HASHCLASS);
    }
    
    /**
     * select
     * Passes each element of the collection to `$callback'. Returns an array with all elements for which the callback returns true.
     * 
     * @access  public
     * @param   callable    $callback   Callback (static) method or function.
     * @param   array       $args       Array with addition parameters (specified by the user).
     * @return  HashClass
     * @see     Enumerable::select
    */
    public function select($callback, array $args = array())
    {
        $result = $this->enum_select($callback, $args);
        return $this->__new__((object) $result, ObjectClass::TYPE_HASHCLASS);
    }
    
    /**
     * getIterator
     * Implementation of trait Enumerabe::getIterator()
     * 
     * @access  public
     * @return  Iterator
    */
    public function getIterator()
    {
        return $this;
    }
}

?>
