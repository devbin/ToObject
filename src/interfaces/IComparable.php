<?php

namespace Devbin\libs\ToObject; 

/**
 * IComparable
 * The IComparable interface is used by classes that one wants to be
 * comparable to other objects. This interface defines a few methods that
 * provide some sort of comparability.
*/
interface IComparable
{
    /**
     * between
     * Checks whether `self' has a value between a and b.
     * It checks for (>a && <b).
     * 
     * @access  public
     * @param   mixed  $a  First value.
     * @param   mixed  $b  Second value.
     * @return  bool
    */
    public function between($a, $b);
}

?>
