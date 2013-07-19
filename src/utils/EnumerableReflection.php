<?php

namespace Devbin\libs\ToObject\Utils;

use \ReflectionFunction,
    \ReflectionMethod,
    \ReflectionFunctionAbstract;

/**
 * EnumerableReflection
 * 
 * Even though EnumerableReflection is part of ToObject, it's designed to work
 * outsize of ToObject. EnumerableReflection source is included here for
 * the ease of use.
 * 
 * This class is written to make use of callbacks, in conjunction
 * with collections, a breeze. A collection is meant to be a "collection" of 
 * data in key-value pairs.
 * EnumerableReflection uses PHP's Reflection Class on the callback function
 * to determine both what kind of function it is - whether it is procedural, 
 * object-oriented or static - and what arguments the function takes.
 * 
 * For its arguments, a function is expected to meet a specific format which is:
 * [$value, $key, [$user_specific_args]]
 * Whereas $value is the value of the current element of the collection,
 * $key is the key of the current element of the collection
 * and $user_specific_args is an array of any other argument one wants to let
 * the callback know of. 
 * For a detailed usage of this {@see EnumerableReflection::callback_arguments()}
 * 
 * Workflow (in sum):
 * 1) ::parse_callback()
 *      figures out what kind of callback is provided (function and method 
 *      characteristics) and returns an instance of ReflectionFunction or
 *      ReflectionMethod.
 * 2) ::parse_static()
 *      tries to find the PAAMAYIM_NEKUDOTAYIM in the given callback 
 *      (meaning it's form is `Obj::method').
 * 3) ::callback_arguments()
 *      figures out which arguments the given callback needs 
 *      (value, key, additional args).
 * 4) ::callback_block()
 *      invokes the given callback with the arguments it 
 *      needs (as seems to ::callback_arguments()).
 * 
 * @abstract
*/
abstract class EnumerableReflection
{
    /**
     * parse_callback
     * Checks whether callback is a function, method or static method.
     * Be sure to provide __NAMESPACE__ where appropriate.
     * 
     * This method is heavily used by ToObject's replacement methods. With this
     * method it determines whether a `$replacement' is a string pattern
     * or a callback function.
     * 
     * @access  public
     * @static
     * @param   callback    $callback       Callback function.
     * @return  ReflectionFunctionAbstract
     * @throws  UnexpectedValueException    when `$callback' is not callable.
     * @throws  UnexpectedValueException    when `$callback' is `_', which
     *          is an alias for the `gettext()' function. This makes sense
     *          when using ::parse_callback in a try/catch block, specially
     *          when trying to figure out whether a variable is a string pattern
     *          or a callable.
     * {@link http://php.net/_}
    */
    public static function parse_callback($callback)
    {
        if ($callback == "_") // gettext alias...
            throw new \UnexpectedValueException();
        
        if (is_callable($callback))
        {
            // is it notated as string, thus as real function or static method
            if (is_string($callback))
            {
                if (($static = self::parse_static($callback)) === false)  
                {
                    // there is NO ::, real function
                    $reflection = new ReflectionFunction($callback);
                } else {    
                    // static/method ('Obj::m')
                    $reflection = new ReflectionMethod($static[0], $static[1]);
                }
            } elseif (is_array($callback)) { 
                // object/method ([$obj, 'm'] or ['Obj', 'm'])
                $reflection = new ReflectionMethod($callback[0], $callback[1]);
            } elseif (is_a($callback, 'Closure')) { 
                // anonymous function
                $reflection = new ReflectionFunction($callback);
            }
        } else {
            throw new \UnexpectedValueException();
        }
        return $reflection;
    }
    
    /**
     * reflection_parse_static
     * Tries to guess whether a method is static. It does so by trying to find
     * the PAAMAYIM_NEKUDOTAYIM.
     * 
     * @access  public
     * @static
     * @param   string  $str    The string to parse.
     * @return  mixed   false or an array in the form of `[obj, method]'.
    */
    public static function parse_static($str)
    {
        if (strpos($str, '::') === false)
            return false;
        else
            return explode('::', $str);
    }
    
    /**
     * callback_arguments
     * This method tries to figure out what the given callback looks like by 
     * checking the number of its arguments. It then takes
     * the corresponding values from `array $args' and returns the ones
     * to be used as an array.
     * 
     * $args is expected, but is not enforced, to be in the format:
     * - first value: a KEY
     * - second value: a VALUE
     * - third value: an array (CUSTOM) containing any other data one wishes
     *                to pass on to the callback.
     * 
     * @access  public
     * @param   ReflectionFunctionAbstract  $reflection     A reflection method or function.
     * @param   array                       $args           Array with key, value and custom args.
     * @return  array                       Array containing data one wants to pass to their callback.
     * 
     * 
     * @example <pre>a callback which is only interested in VALUE $e:
     *          (this is the minimal form)
     *          function($e) { ... }</pre>
     * 
     * @example <pre>a callback which is interested in VALUE $e and KEY $i:
     *          function($e, $i) { ... }</pre>
     * 
     * @example <pre>a callback which is interested in VALUE $e and CUSTOM $c:
     *          function($e, $c) { ... }
     * 
     *          (it knows whether to use KEY or CUSTOM by counting (>0) the
     *          elements of CUSTOM. If there are any elements, CUSTOM is used,
     *          otherwise KEY is used)</pre>
     * 
     * @example <pre>a callback which is interested in all three arguments
     *          function($e, $i, $c) { ... }</pre>
     * 
     * @example <pre>another way to invoke the second example is to pass
     *          an array a such:
     *          function(array($x)) { ... }
     * 
     *          then $x will be an array with:
     *          $x[0]: value
     *          $x[1]: key</pre>
    */
    public static function callback_arguments(ReflectionFunctionAbstract $reflection, array $args)
    {
        // key, value, custom
        list($i, $e, $custom) = $args;
        
        switch($reflection->getNumberOfParameters())
        {
            case 1: // value
                // Undocumented feature ^_^! 
                // (yay, it's not undocumented anymore. last example!)
                if ($reflection->getParameters()[0]->isArray())
                {
                    $send_with = [[$e, $i]];
                } else {
                    $send_with = [$e];
                }
                break;
            case 2: // value + key or custom
                if (count($custom) > 0)
                {
                    $send_with = [$e, $custom];
                } else {
                    $send_with = [$e, $i];
                }
                break;
            case 3: // value + key + custom
                $send_with = [$e, $i, $custom];
                break;
            default:
                $send_with = [$e];
                break;
        }
        return $send_with;
    }
    
    /**
     * each_callback_block
     * Invokes callback in the appropriate way, provided by `EnumerableReflection::parse_callback()'.
     * 
     * @access  public
     * @static
     * @param   ReflectionFunctionAbstract      $reflection     ReflectionFunctionAbstract.
     * @param   callable                        $callback       Callback method.
     * @param   array                           $args           Arguments to call $callback with.
     * @return  mixed                           Whatever `$callback' returns.
    */
    public static function callback_block(ReflectionFunctionAbstract $reflection, callable $callback, array $args)
    {
        $static = ($reflection instanceof ReflectionMethod)? $reflection->isStatic() : false;
        
        if ($static)
        {
            return forward_static_call_array($callback, $args);
        } else {
            return call_user_func_array($callback, $args);
        }
    }
}

?>
