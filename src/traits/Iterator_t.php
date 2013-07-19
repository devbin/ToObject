<?php

namespace Devbin\libs\ToObject;

/**
 * Iterator_t
 * Implementation of the \Iterator Interface conforming the structure
 * of ToObject Classes.
 * 
 * {@link http://php.net/manual/en/class.iterator.php}
*/
trait Iterator_t
{
    /**
     * rewind
     * Sets (rewinds) the internal Iterator pointer to the first element.
     * 
     * @access  public
     * @return  void
    */
    public function rewind()
    {
        reset($this->_object);
    }
    
    /**
     * current
     * Returns the value of the current element pointed to by the Iterator.
     * 
     * @access  public
     * @return  mixed
    */
    public function current()
    {
        return current($this->_object);
    }
    
    /**
     * key
     * Returns the key of the current element pointed to by the Iterator.
    */
    public function key()
    {
        return key($this->_object);
    }
    
    /**
     * next
     * Enhances the internal Iterator pointer by one.
     * 
     * @access  public
     * @return  void
    */
    public function next()
    {
        next($this->_object);
    }
    
    /**
     * valid
     * Checks whether the current position of the Iterator is valid.
     * 
     * @access  public
     * @return  bool    true when valid, false otherwise.
    */
    public function valid()
    {
        $key = key($this->_object);
        return ($key !== null && $key !== false);
    }
}

?>
