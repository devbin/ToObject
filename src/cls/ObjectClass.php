<?php

namespace Devbin\libs\ToObject; 

/**
 * ObjectClass
 * 
 * ObjectClass is the root of ToObject. Its methods are available in all
 * ToObject's classes except when explicitly overridden. Traits and Interfaces
 * are considered not to be part of this rule. ObjectClass is used to 
 * instantiate any of its classes in a recursive way so one can benefit from 
 * its methods in every sub-element (e/g: nested arrays).
 * 
 * @abstract
*/
abstract class ObjectClass implements \Countable
{
    /**
     * Default encoding of `self'.
    */
    const DEFAULT_ENCODING = 'UTF-8';

    /**
     * @see self::get_type()
    */
    const TYPE_STRINGCLASS  = 0x01;
    const TYPE_ARRAYCLASS   = 0x02;
    const TYPE_HASHCLASS    = 0x04;
    const TYPE_OKAY         = 0x08;
    const TYPE_SKIP         = 0x10;
    
    /**
     * Content of `self'
    */
    protected $_object;
    
    /**
     * Encoding of `self'
    */
    protected $_encoding;
    
    /**
     * __tostring
     * Returns `self' as a string.
     * 
     * @access  public
     * @return  string  
     * @see object specific notes.
    */
    abstract public function __tostring();
    
    /**
     * to_native
     * Returns a native PHP data type.
     * 
     * @access  public
     * @param   bool        $recursive  Whether to return everything or just the first level as native PHP.
     * @return  mixed
    */
    public abstract function to_native($recursive = true);
    
    /**
     * to_s
     * Returns a string presentation of `self'.
     * 
     * @access  public
     * @return  string
    */
    public abstract function to_s();
    
    // 
    // methods to build the ToObject objects
    // 

    /**
     * builder
     * Recursively builds an object to use with ToObject and all its goodies.
     * 
     * @access  public
     * @param   mixed       $object     The input object.
     * @param   string      $encoding   Encoding to use (necessary for StringClass' multibyte string functions)
     * @return  array       used for internal storage of the appropriate *Class object.
     * @uses    ObjectClass::build_arrayClass()
     * @uses    ObjectClass::build_hashClass()
    */
    protected static function builder($object, $encoding = self::DEFAULT_ENCODING)
    {
        $new = $object;
        if (is_array($object))
        {
            $new = self::build_arrayClass($object, $encoding);
        } elseif ($object instanceof \stdClass) {
            $new = self::build_hashClass($object, $encoding);
        } elseif (ObjectClass::get_type($object) == self::TYPE_STRINGCLASS) {
            $new = self::get_instance($object, self::TYPE_STRINGCLASS, $encoding);
        }
        return $new;
    }
    
    /**
     * build_arrayClass
     * Builds an array to use as internal storage for an ArrayClass.
     * 
     * @access  public
     * @param   array       $object     The input object.
     * @param   string      $encoding   Encoding to use (necessary for StringClass' multibyte string functions)
     * @return  array       used for internal storage of the appropriate *Class object.
     * @used-by ObjectClass::builder()
    */
    private static function build_arrayClass(array $object, $encoding)
    {
        $new = array();
        foreach ($object as $key => $value) 
        {
            if (!is_int($key))
            {
                continue;
            }
            
            // get type
            $type = self::get_type($value);

             // @see self::get_type() for the logic here
            if ($type == self::TYPE_OKAY || $type == self::TYPE_SKIP)
                $new[$key] = $value;
            else
                $new[$key] = self::get_instance($value, $type, $encoding);
        }
        return $new;
    }
    
    /**
     * build_arrayClass
     * Builds an stdClass to use as internal storage for an HashClass.
     * 
     * @access  public
     * @param   \stdClass   $object     The input object.
     * @param   string      $encoding   Encoding to use (necessary for StringClass' multibyte string functions)
     * @return  \stdClass   used for internal storage of the appropriate *Class object.
     * @used-by ObjectClass::builder()
    */
    private static function build_hashClass(\stdClass $object, $encoding)
    {
        $new = new \stdClass;
        foreach ($object as $key => $value) 
        {
            if (is_numeric($key))
            {
                continue;
            }
            
            // get type
            $type = self::get_type($value);

             // @see self::get_type() for the logic here
            if ($type == self::TYPE_OKAY || $type == self::TYPE_SKIP)
                $new->{$key} = $value;
            else
                $new->{$key} = self::get_instance($value, $type, $encoding);
        }
        return $new;
    }

    /**
     * get_instance
     * Simply returns an instance of $type with $data.
     * 
     * @access  public
     * @param   mixed       $data        Constructional data for the new instance.
     * @param   int         $type        Instance type, retrieved by self::get_type().
     * @param   string      $encoding    Constructional encoding for the new instance.
     * @return  mixed       Returns the new instance unless $type is either SKIP or OKAY.
    */
    private static function get_instance($data, $type, $encoding)
    {
        switch ($type)
        {
            case self::TYPE_STRINGCLASS:
                $ret = new StringClass($data, $encoding);
                break;
            case self::TYPE_ARRAYCLASS:
                $ret = new ArrayClass($data, $encoding);
                break;
            case self::TYPE_HASHCLASS:
                $ret = new HashClass($data, $encoding);
                break;
            // default: // merely because a default is good practice
            //     $ret = $data;
            //     break;
        }
        return $ret;
    }

    /**
     * get_type
     * Tries to guess which TYPE_* $data will fit.
     * Names are self-explanatory except maybe for OKAY and SKIP:
     * - OKAY: $data already is an instance of String-, Array-, or Hash- Class
     * - SKIP: ToObject has no clue what $data is. This is NOT a bug/error, it 
     *             means $data could be an integer or bool     etc..., which are
     *             not handled by ToObject. Thus, it will be left untouched.
     * 
     * @access  public
     * @param   mixed       $data    Data of which its type should be known.
     * @return  int         One of the TYPE_* constants.
    */
    public static function get_type($data)
    {
        $ret = -1;
        if (is_string($data)) {
            $ret = self::TYPE_STRINGCLASS;
        } elseif (is_array($data)) {
            $ret = self::TYPE_ARRAYCLASS;
        } elseif ($data instanceof \stdClass) {
            $ret = self::TYPE_HASHCLASS;
        } elseif ($data instanceof ObjectClass) {
            $ret = self::TYPE_OKAY;
        } else {
            $ret = self::TYPE_SKIP;
        }
        return $ret;
    }
    
    // 
    // Basic methods
    // 
    
    /**
     * getStorage
     * Returns what is in `self'.
     * 
     * @access  public
     * @return  mixed   Whatever is stored in `self' (string / array).
    */
    public function getStorage()
    {
        return $this->_object;
    }
    
    /**
     * getEncoding
     * Returns the internal encoding.
     * 
     * @access  public
     * @return  string  Internal encoding.
    */
    public function getEncoding()
    {
        return $this->_encoding;
    }
    
    /**
     * klass
     * Returns the type of `self'.
     * 
     * @access  public
     * @param   bool        $full   Whether to return the full path.
     * @return  string      Type of `self'.
    */
    public function klass($full = false)
    {
        $class = get_class($this);
        
        if ($full) return $class;
            
        $exp = explode('\\', $class); // 0x5c
        return end($exp);
    }
    
    /**
     * is_a
     * Checks whether self is of a type.
     * 
     * @access  public
     * @param   ObjectClass     $type   The type to compare `self' to.
     * @param   bool            $ns     Match against this namespace.
     * @return  bool
    */
    public function is_a($type, $ns = true)
    {
        return ($ns)
            ? is_a($this, 'Devbin\libs\ToObject\\'.$type) 
            : is_a($this, $type);
    }
    
    /**
     * methods
     * Merely a `get_class_methods()' wrapped in an ArrayClass.
     * 
     * @access  public
     * @return  ArrayClass  holding the methods of `self'.
    */
    public function methods()
    {
        return new ArrayClass(get_class_methods($this));
    }
    
    /**
     * eql
     * Checks whether two objects have the same content.
     * 
     * @access  public
     * @param   ObjectClass    $otherObject    The object to compare `self' to.
     * @return  bool
    */
    public function eql(ObjectClass $otherObject)
    {
        return ($this == $otherObject);
    }
    
    /**
     * equal
     * Checks whether two objects have the same reference.
     * 
     * @access  public
     * @param   ObjectClass    $otherObject    The object to compare `self' to.
     * @return  bool    
    */
    public function equal(ObjectClass $otherObject)
    {
        return ($this === $otherObject);
    }
}

?>
