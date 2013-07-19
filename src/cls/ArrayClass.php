<?php

namespace Devbin\libs\ToObject;

/**
 * ArrayClass
 * 
 * An array (ArrayClass) is both an ordered and unordered collection of data.
 * It is ordered in the way that it will preserve the order in which data is
 * provided during instantiating. 
 * It is unordered in the way that one should
 * not want to know in which order their data is, just that it *is* present.
 * Therefor ArrayClass, unlike native PHP arrays, will ignore 
 * any non-integer key indices during instantiating (whereas HashClass ignores 
 * integer key indices).
 * 
 * ArrayClass uses:
 * - Iterator_t trait to implement \Iterator
 * - Count_t trait to implement \Countable
 * - ArrayAccess_t trait to implement \ArrayAccess
 * - Enumerable trait
*/
class ArrayClass extends ObjectClass implements \Iterator, \ArrayAccess
{
    use ArrayAccess_t, Count_t, Iterator_t, Enumerable
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
     * @param   array   $object     The input array.
     * @param   string  $encoding   Encoding for the array contents.
    */
    public function __construct(array $object, $encoding = ObjectClass::DEFAULT_ENCODING)
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
     * __new__
     * Constructs a new object related to ToObject.
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
                // $ret = new StringClass($data, $this->_encoding);
                // break;
            case self::TYPE_ARRAYCLASS:
                $ret = new ArrayClass($data, $this->_encoding);
                break;
            case self::TYPE_HASHCLASS:
                $ret = new HashClass($data, $this->_encoding);
                break;
            // default:
                // return $data;
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
                    $collection[] = $value->to_s();
                } elseif ($value->is_a('StringClass')) {
                    $collection[] = sprintf("\"%s\"", addslashes($value->to_s()));
                }
            } else {
                $collection[] = $value;
            }
        }
        return sprintf("[%s]", implode(', ', $collection));
    }
    
    /**
     * to_native
     * Creates an array with all elements of `self' and returns it.
     * 
     * @access  public
     * @param   bool    $recursive    Whether to return everything or just the first level as native PHP.
     * @return  array
    */
    public function to_native($recursive = true)
    {
        $return = array();
        if ($recursive)
        {
            foreach ($this as $key => $value)
            {
                if (is_object($value) && $value instanceof ObjectClass)
                    $return[$key] = $value->to_native();
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
     * to_hsh
     * Assumes `self' is built up in the format [[p, q], [r, s]]
     * and returns a HashClass containing the :key => value pairs.
     * 
     * @access  public
     * @return  HashClass
     * @example [['name', 'john'], ['age', 36]] would return 
     *             { :name => 'john', :age => 36}
     *             john would be of StringClass
    */
    public function to_hsh()
    {
        $collection = array();
        foreach ($this as $value)
        {
            list($k, $v) = $value->to_native();
            $collection[$k] = $v;
        }
        return $this->__new__((object) $collection, ObjectClass::TYPE_HASHCLASS);
    }
    
    /**
     * includes
     * Checks for existence of a value and returns true if it does,
     * false otherwise
     * 
     * @access  public
     * @param   mixed    $value    Value to look for.
     * @return  bool
     * @see Enumerable::includes
    */
    public function includes($value)
    {
        $collection = $this->getStorage();
        return in_array($value, $collection);
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
     * @return  ArrayClass
     * @see     Enumerable::delete_if
    */
    public function delete_if($callback, array $args = array())
    {
        $result = $this->enum_delete_if($callback, $args);
        return $this->__new__($result, ObjectClass::TYPE_ARRAYCLASS);
    }
    
    /**
     * detect
     * Passes each element of the collection to `$callback'. Returns the first element for which the callback returns true.
     * 
     * @access  public
     * @param   callable    $callback   Callback (static) method or function.
     * @param   array       $args       Array with addition parameters (specified by the user).
     * @return  ArrayClass
     * @see     Enumerable::detect
    */
    public function detect($callback, array $args = array())
    {
        $result = $this->enum_detect($callback, $args);
        if (is_null($result))
            return null;
        return $this->__new__($result, ObjectClass::TYPE_ARRAYCLASS);
    }
    
    /**
     * drop
     * Drops the first n items of the object.
     * 
     * @access  public
     * @param   int     $n      Amount of items to drop (shift).
     * @return  ArrayClass
     * @see     Enumerable::drop
    */
    public function drop($n = 1)
    {
        $result = $this->enum_drop($n);
        return $this->__new__($result, ObjectClass::TYPE_ARRAYCLASS);
    }
    
    /**
     * drop_while
     * Drops items from Enum while callback yields true.
     * 
     * @access  public
     * @param   callable    $callback   Callback (static) method or function.
     * @param   array       $args       Array with addition parameters (specified by the user).
     * @return  ArrayClass
     * @see     Enumerable::drop_while
    */
    public function drop_while($callback, array $args = array())
    {
        $result = $this->enum_drop_while($callback, $args);
        return $this->__new__($result, ObjectClass::TYPE_ARRAYCLASS);
    }
    
    /**
     * join
     * Joins, implodes, the array together with a concatenator.
     * 
     * @access  public
     * @param   string  $concatenator   The `glue' string.
     * @return  StringClass
    */
    public function join($concatenator = ' ')
    {
        return new StringClass(implode($concatenator, $this->getStorage()));
    }
    
    /**
     * keep_if
     * Passes each element of the collection to `$callback'. Returns an array with all elements for which the callback returns false.
     * 
     * @access  public
     * @param   callable    $callback   Callback (static) method or function.
     * @param   array       $args       Array with addition parameters (specified by the user).
     * @return  ArrayClass
     * @see     Enumerable::keep_if
    */
    public function keep_if($callback, array $args = array())
    {
        $result = $this->enum_keep_if($callback, $args);
        return $this->__new__($result, ObjectClass::TYPE_ARRAYCLASS);
    }
    
    /**
     * select
     * Passes each element of the collection to `$callback'. Returns an array with all elements for which the callback returns true.
     * 
     * @access  public
     * @param   callable    $callback   Callback (static) method or function.
     * @param   array       $args       Array with addition parameters (specified by the user).
     * @return  ArrayClass
     * @see     Enumerable::select
    */
    public function select($callback, array $args = array())
    {
        $result = $this->enum_select($callback, $args);
        return $this->__new__($result, ObjectClass::TYPE_ARRAYCLASS);
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
