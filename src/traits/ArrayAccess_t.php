<?php

namespace Devbin\libs\ToObject;

/**
 * ArrayAccess_t
 * Implementation of the ArrayAccess Interface conforming the structure
 * of ToObject classes (ArrayClass only at this moment).
 * 
 * {@link http://php.net/manual/en/class.arrayaccess.php}
*/
trait ArrayAccess_t
{
    /**
     * offsetSet
     * Assigns the given value to the given offset. It will create the offset
     * when it does not exist.
     * 
     * @access  public
     * @param   int     $offset     The offset to assign the value to.
     * @param   mixed   $value      The value to set.
     * @return  void
    */
    public function offsetSet($offset, $value)
    {
        if (is_null($offset))
        {
            $this->_object[] = ObjectClass::builder($value, $this->_encoding);
        } else {
            $this->_object[$offset] = $value;
        }
    }
    
    /**
     * offsetExists
     * Checks whether an offset exists.
     * 
     * @access  public
     * @param   int     $offset     The offset to check for.
     * @return  bool    true if exists, false otherwise.
    */
    public function offsetExists($offset)
    {
        return isset($this->_object[$offset]);
    }
    
    /**
     * offsetUnset
     * Unsets an offset.
     * 
     * @access  public
     * @param   int     $offset     The offset to unset.
     * @return  void
    */
    public function offsetUnset($offset)
    {
        unset($this->_object[$offset]);
    }
    
    /**
     * offsetGet
     * Returns the value of the given offset or null when `offset' does
     * not exist
     * 
     * @access  public
     * @param   int     $offset     The offset to returns its value from.
     * @return  mixed   the value or null
    */
    public function offsetGet($offset)
    {
        return isset($this->_object[$offset]) 
            ? $this->_object[$offset] 
            : null;
    }
}

?>
