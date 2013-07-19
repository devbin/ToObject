<?php

namespace Devbin\libs\ToObject;

/**
 * Count_t
 * A simple implementation of the \Countable Interface conforming the structure
 * of ToObject classes.
 * 
 * {@link http://php.net/manual/en/class.countable.php}
*/
trait Count_t
{
    /**
     * count
     * Counts the elements of an object
     * 
     * @access  public
     * @return  int     number of elements of `self'.
    */
    public function count()
    {
        return count($this->_object);
    }
}

?>
