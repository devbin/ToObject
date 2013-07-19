<?php

namespace Devbin\libs\ToObject;

use \Devbin\libs\ToObject\Utils\EnumerableReflection,
    \ArrayIterator,
    \ReflectionFunction,
    \ReflectionMethod,
    \ReflectionFunctionAbstract;

/**
 * Enumerable
 * 
 * Enumerable is a self-contained Enumerable and should be usable without
 * any of the other files of ToObject. It is however solely created for
 * ToObject.
 * 
 * This trait contains much of the ruby-like enumerable methods.
 * 
 * Any class incorporating this trait should have access to all of its
 * methods in a way as such: $obj-><any method>($callback[, $args]);
 * where $callback also may be an anonymous function, closure, lambda or
 * whichever term you prefer.
*/
trait Enumerable
{
    /**
     * getIterator
     * Returns the iterator of `self'.
    */
    abstract protected function getIterator();
    
    /**
     * all
     * Passes each element of the collection to `$callback'. The method returns true if every callback returns true.
     * Stops iterating on the first occurrence of false.
     * 
     * @access    public
     * @param     callable    $callback    Callback (static) method or function.
     * @param     array       $args        Array with params the user wants to pass on to the callback method.
     * @return    bool
    */
    public function all(callable $callback, array $args = array())
    {
        $reflection = EnumerableReflection::parse_callback($callback);
        $return = false;
        
        $it = $this->getIterator();
        foreach ($it as $key => $value)
        {
            $result = EnumerableReflection::callback_block($reflection, $callback, EnumerableReflection::callback_arguments($reflection, [$key, $value, $args]));
            
            if (!is_bool($result))
                $this->bool_exception(__METHOD__);
                
            if (!$result)
                return false;
            else
                $return = true;
        }
        
        return $return;
    }
    
    /**
     * any
     * Passes each element of the collection to `$callback'. The method returns true if callback returns true at least once.
     * Stops iterating on the first occurrence of true.
     * 
     * @access  public
     * @param   callable    $callback    Callback (static) method or function.
     * @param   array       $args        Array with params the user wants to pass on to the callback method.
     * @return  bool
    */
    public function any(callable $callback, array $args = array())
    {
        $reflection = EnumerableReflection::parse_callback($callback);
        
        $it = $this->getIterator();
        foreach ($it as $key => $value)
        {
            $result = EnumerableReflection::callback_block($reflection, $callback, EnumerableReflection::callback_arguments($reflection, [$key, $value, $args]));    
            
            if (!is_bool($result))
                $this->bool_exception(__METHOD__);
            
            if ($result)
                return true;
        }
        
        return false;
    }

    /**
     * collect
     * Passes each element of the collection to `$callback'. Returns an array with the results of the callback.
     * 
     * @access  public
     * @param   callable    $callback   Callback method.
     * @param   array       $args       Array with addition parameters (specified by the user).
     * @return  array
    */
    public function collect(callable $callback, array $args = array())
    {
        $reflection = EnumerableReflection::parse_callback($callback);
        $collection = array();
        
        $it = $this->getIterator();
        foreach ($it as $key => $value)
        {
            $result = EnumerableReflection::callback_block($reflection, $callback, EnumerableReflection::callback_arguments($reflection, [$key, $value, $args]));
            $collection[] = $result;
        }
        
        return $collection;
    }

    /**
     * delete_if
     * Passes each element of the collection to `$callback'. Returns an array with all elements for which the callback returns true.
     * 
     * @access  public
     * @param   callable    $callback   Callback (static) method or function.
     * @param   array       $args       Array with addition parameters (specified by the user).
     * @return  array
    */
    public function delete_if(callable $callback, array $args = array())
    {
        $reflection = EnumerableReflection::parse_callback($callback);
        $collection = array();

        $it = $this->getIterator();    
        foreach ($it as $key => $value) 
        {
            $result = EnumerableReflection::callback_block($reflection, $callback, EnumerableReflection::callback_arguments($reflection, [$key, $value, $args]));
            
            if (!is_bool($result))
                $this->bool_exception(__METHOD__);
                
            if (!$result)
                $collection[$key] = $value;
        }
        
        return $collection;
    }

    /**
     * detect
     * Passes each element of the collection to `$callback'. Returns the first element for which the callback returns true.
     * 
     * @access  public
     * @param   callable    $callback   Callback (static) method or function.
     * @param   array       $args       Array with addition parameters (specified by the user).
     * @return  mixed       Whatever `$callback' returns.
    */
    public function detect(callable $callback, array $args = array())
    {
        $reflection = EnumerableReflection::parse_callback($callback);
        
        $it = $this->getIterator();    
        foreach ($it as $key => $value) 
        {
            $result = EnumerableReflection::callback_block($reflection, $callback, EnumerableReflection::callback_arguments($reflection, [$key, $value, $args]));
            
            if (!is_bool($result))
                $this->bool_exception(__METHOD__);
            
            if ($result)
                return [$key => $value];
        }
        
        return null;
    }
    
    /**
     * drop
     * Drops the first n items of the object.
     * 
     * @access  public
     * @param   int     $n      Amount of items to drop (shift).
     * @return  array
    */
    public function drop($n = 1)
    {
        $collection = array();
        
        $it = $this->getIterator();
        foreach ($it as $key => $value)
        {
            if ($n) { $n--; continue; }
            $collection[$key] = $value;
        }
        
        return $collection;
    }
    
    /**
     * drop_while
     * Drops items from Enum while callback yields true.
     * 
     * @access  public
     * @param   callable    $callback   Callback (static) method or function.
     * @param   array       $args       Array with addition parameters (specified by the user).
     * @return  array
    */
    public function drop_while(callable $callback, array $args = array())
    {
        $reflection = EnumerableReflection::parse_callback($callback);
        $collection = array();
        $it = $this->getIterator();
        foreach ($it as $key => $value) 
        {
            $result = EnumerableReflection::callback_block($reflection, $callback, EnumerableReflection::callback_arguments($reflection, [$key, $value, $args]));

            if (!is_bool($result))
                $this->bool_exception(__METHOD__);

            if ($result)
                continue;

            $collection[$key] = $value;
        }
        
        return $collection;
    }
    
    /**
     * includes
     * Returns true if any member of `self' equals `$obj'.
     * Implement this method to check for keys, values, or whatever floats your boat.
     * 
     * @access  public
     * @param   mixed    $obj    Object to compare to.
     * @return  bool
    */
    abstract public function includes($obj);

    /**
     * inject
     * Behaves exactly like array_reduce. {@link http://php.net/array_reduce}
     * 
     * @access  public
     * @param   callable    $callback    Callback method.
     * @param   mixed       $init        Initial value.
     * @return  mixed       Returns the resulting value.
    */
    public function inject(callable $callback, $init = null)
    {
        $collection = array();
        $it = $this->getIterator();
        foreach ($it as $key => $value) {
            $collection[] = [$key, $value];
        }
        
        return array_reduce($collection, $callback, $init);
    }
    
    /**
     * keep_if
     * Passes each element of the collection to `$callback'. Returns an array with all elements for which the callback returns false.
     * 
     * @access  public
     * @param   callable    $callback   Callback (static) method or function.
     * @param   array       $args       Array with addition parameters (specified by the user).
     * @return  array
    */
    public function keep_if(callable $callback, array $args = array())
    {
        $reflection = EnumerableReflection::parse_callback($callback);
        $collection = array();

        $it = $this->getIterator();    
        foreach ($it as $key => $value) 
        {
            $result = EnumerableReflection::callback_block($reflection, $callback, EnumerableReflection::callback_arguments($reflection, [$key, $value, $args]));
            
            if (!is_bool($result))
                $this->bool_exception(__METHOD__);
                
            if ($result)
                $collection[$key] = $value;
        }
        
        return $collection;
    }

    /**
     * none
     * Passes each element of the collection to `$callback'. The method returns true if callback never returns true.
     * 
     * @access  public
     * @param   callback    $callback   Callback (static) method or function.
     * @param   array       $args       Array with addition parameters (specified by the user).
     * @return  bool
    */
    public function none(callable $callback, array $args = array())
    {
        return (!$this->any($callback, $args));
    }

    /**
     * one
     * Passes each element of the collection to `$callback'. The method returns true if callback returns true exactly once.
     * 
     * @access  public
     * @param   callable    $callback   Callback (static) method or function.
     * @param   array       $args       Array with addition parameters (specified by the user).
     * @return  bool
    */
    public function one(callable $callback, array $args = array())
    {
        $reflection = EnumerableReflection::parse_callback($callback);
        $results = array();
        
        $it = $this->getIterator();    
        foreach ($it as $key => $value)
        {
            $result = EnumerableReflection::callback_block($reflection, $callback, EnumerableReflection::callback_arguments($reflection, [$key, $value, $args]));
            
            if (!is_bool($result))
                $this->bool_exception(__METHOD__);
            
            if ($result)
                $results[] = $result;
        }
        
        return (count($results) === 1);
    }
        
    /**
     * select
     * Passes each element of the collection to `$callback'. Returns an array with all elements for which the callback returns true.
     * 
     * @access  public
     * @param   callable    $callback   Callback (static) method or function.
     * @param   array       $args       Array with addition parameters (specified by the user).
     * @return  array
    */
    public function select(callable $callback, array $args = array())
    {
        $reflection = EnumerableReflection::parse_callback($callback);
        $collection = array();

        $it = $this->getIterator();    
        foreach ($it as $key => $value) 
        {
            $result = EnumerableReflection::callback_block($reflection, $callback, EnumerableReflection::callback_arguments($reflection, [$key, $value, $args]));
            
            if (!is_bool($result))
                $this->bool_exception(__METHOD__);
                
            if ($result)
                $collection[$key] = $value;
        }
        
        return $collection;
    }
    
    /**
     * subseq_keys
     * Assumes an array of the format [[x, y], [x, y]] and will return an array containing the x's.
     * 
     * @access  public
     * @param   array   $stack  Stack to go through.
     * @return  array   Contains keys (x's) of `self'.
    */
    public function subseq_keys()
    {
        $collection = array();
        foreach ($this as $value) { $collection[] = $value[0]; }
        return $collection;
    }
    
    /**
     * subseq_values
     * Assumes an array of the format [[x, y], [x, y]] and will return an array containing the y's.
     * 
     * @access  public
     * @param   array   $stack  Stack to go through.
     * @return  array   Contains values (y's) of `self'.
    */
    public function subseq_values()
    {
        $collection = array();
        foreach ($this as $value) { $collection[] = $value[1]; }
        return $collection;
    }
    
    /**
     * bool_exception
     * Throws an exception when a function expects a bool as return type
     * but got something else instead.
     * 
     * @access  private
     * @param   mixed        $method        Method in which the exception occurs.
     * @return  void
     * @throws  UnexpectedValueException
    */
    private function bool_exception($method)
    {
        throw new \UnexpectedValueException(sprintf("Callback function must return a bool type for %s", $method));
    }
}

?>
