<?php

namespace Rygilles\SmartJsonReducer;

use ArrayAccess;
use Closure;

class Arr
{
	/**
	 * Set an array item to a given value using "dot" notation.
	 *
	 * Return the initial array (by reference) modified.
	 *
	 * @param array $array
	 * @param string $key
	 * @param mixed $value
	 * @return array
	 */
	public static function set(&$array, $key, $value)
	{
		if (is_null($key)) {
			throw new \RuntimeException('Invalid null key provided');
		}
		
		$initialArray = &$array;
		
		$keys = explode('.', $key);
		
		while (count($keys) > 1) {
			$key = array_shift($keys);
			
			// If the key doesn't exist at this depth, we will just create an empty array
			// to hold the next value, allowing us to create the arrays to hold final
			// values at the correct depth. Then we'll keep digging into the array.
			if (! isset($array[$key]) || ! is_array($array[$key])) {
				$array[$key] = [];
			}
			
			$array = &$array[$key];
		}
		
		$array[array_shift($keys)] = $value;
		
		return $initialArray;
	}
	
	/**
	 * Check if an item or items exist in an array using "dot" notation.
	 *
	 * @param ArrayAccess|array $array
	 * @param string|array $keys
	 * @return bool
	 */
	public static function has($array, $keys)
	{
		if (is_null($keys)) {
			return false;
		}
		
		$keys = (array) $keys;
		
		if (! $array) {
			return false;
		}
		
		if ($keys === []) {
			return false;
		}
		
		foreach ($keys as $key) {
			$subKeyArray = $array;
			
			if (static::exists($array, $key)) {
				continue;
			}
			
			foreach (explode('.', $key) as $segment) {
				if (static::accessible($subKeyArray) && static::exists($subKeyArray, $segment)) {
					$subKeyArray = $subKeyArray[$segment];
				} else {
					return false;
				}
			}
		}
		
		return true;
	}
	
	/**
	 * Get an item from an array using "dot" notation.
	 *
	 * @param \ArrayAccess|array $array
	 * @param string $key
	 * @param mixed $default
	 * @return mixed
	 */
	public static function get($array, $key, $default = null)
	{
		if (! static::accessible($array)) {
			return $default instanceof Closure ? $default() : $default;
		}
		
		if (is_null($key)) {
			return $array;
		}
		
		if (static::exists($array, $key)) {
			return $array[$key];
		}
		
		if (strpos($key, '.') === false) {
			return $array[$key] ? $array[$key] : ($default instanceof Closure ? $default() : $default);
		}
		
		foreach (explode('.', $key) as $segment) {
			if (static::accessible($array) && static::exists($array, $segment)) {
				$array = $array[$segment];
			} else {
				return $default instanceof Closure ? $default() : $default;
			}
		}
		
		return $array;
	}
	
	/**
	 * Determine if the given key exists in the provided array.
	 *
	 * @param \ArrayAccess|array $array
	 * @param string|int $key
	 * @return bool
	 */
	public static function exists($array, $key)
	{
		if ($array instanceof ArrayAccess) {
			return $array->offsetExists($key);
		}
		
		return array_key_exists($key, $array);
	}
	
	/**
	 * Determine whether the given value is array accessible.
	 *
	 * @param mixed $value
	 * @return bool
	 */
	public static function accessible($value)
	{
		return is_array($value) || $value instanceof ArrayAccess;
	}
}