<?php

namespace Rygilles\SmartJsonReducer;

class Reducer
{
	/**
	 * Reduce the JSON object size applying truncates on string fields using weights.
	 *
	 * @param string $json JSON object string
	 * @param int $maxSize Desired JSON array object string size in bytes
	 * @param array $weights Keys must be the "dotted" path of fields and values the weight
	 * @param string $encoding Encoding format to use
	 * @return string
	 */
	public static function reduce($json, $maxSize, $weights, $encoding = null)
	{
		if (is_null($encoding)) {
			$encoding = mb_internal_encoding();
		}
		
		if (static::checkJsonStringSize($json, $maxSize)) {
			return $json;
		}
		
		$jsonArray = static::loadJson($json);
		
		if (!is_array($weights)) {
			throw new \RuntimeException('$weights must be an array');
		}
		
		$realWeights = static::computeRealWeights($weights);
		
		$jsonStringSize = mb_strlen($json, '8bit');
		
		$jsonStructureSize = $jsonStringSize - static::computeJsonDataSize($jsonArray, $weights);
		
		$resultDataSize = $maxSize - $jsonStructureSize;

		foreach ($realWeights as $path => $realWeight) {
			$jsonArray = static::reduceJsonArrayField($jsonArray, $path, $resultDataSize, $realWeight, $encoding);
		}
		
		$json = \json_encode($jsonArray);
		
		if ($json === false) {
			throw new \RuntimeException('JSON encode failed');
		}
		
		return $json;
	}
	
	/**
	 * Safe load a JSON string in array.
	 *
	 * @param string $json JSON string
	 * @return array
	 */
	protected static function loadJson($json)
	{
		// Decode JSON
		$jsonArray = \json_decode($json, true);
		
		$json_last_error = json_last_error();
		if ($jsonArray === false && $json_last_error != JSON_ERROR_NONE) {
			throw new \RuntimeException('JSON decode error');
		}
		
		return $jsonArray;
	}
	
	/**
	 * Check the JSON string size.
	 *
	 * @param string $json JSON string
	 * @param int $maxSize Desired JSON string size in bytes
	 * @return bool
	 */
	protected static function checkJsonStringSize($json, $maxSize)
	{
		return (mb_strlen($json, '8bit') <= $maxSize);
	}
	
	/**
	 * Compute the JSON array data size in bytes.
	 *
	 * @param array $jsonArray JSON array
	 * @param array $weights Keys must be the "dotted" path of fields and values the weight
	 * @return int
	 */
	protected static function computeJsonDataSize($jsonArray, $weights)
	{
		$dataTotalSize = 0;
		
		foreach ($weights as $path => $weight) {
			$dataTotalSize += mb_strlen(Arr::get($jsonArray, $path), '8bit');
		}
		
		return $dataTotalSize;
	}
	
	/**
	 * Compute the real weights, normalizing them.
	 *
	 * @param array $weights Keys must be the "dotted" path of fields and values the weight
	 * @return array
	 */
	protected static function computeRealWeights($weights)
	{
		$totalWeight = static::computeTotalWeight($weights);
		$realWeights = [];
		
		foreach ($weights as $path => $weight) {
			$realWeights[$path] = $weight / $totalWeight;
		}
		
		return $realWeights;
	}
	
	/**
	 * Compute the total fields weight.
	 *
	 * @param array $weights Keys must be the "dotted" path of fields and values the weight
	 * @return float
	 */
	protected static function computeTotalWeight($weights)
	{
		$totalWeight = 0;
		
		foreach ($weights as $weight) {
			$totalWeight += $weight;
		}
		
		return $totalWeight;
	}
	
	
	/**
	 * Apply reduction on JSON array field using the path, difference size and weight.
	 *
	 * @param array $jsonArray JSON array
	 * @param string $path Field "dotted" path
	 * @param int $resultDataSize result JSON string size in bytes
	 * @param float $weight Field weight
	 * @param string $encoding Encoding format to use
	 * @return array
	 */
	protected static function reduceJsonArrayField($jsonArray, $path, $resultDataSize, $weight, $encoding = null)
	{
		if (is_null($encoding)) {
			$encoding = mb_internal_encoding();
		}
		
		if (!Arr::has($jsonArray, $path)) {
			throw new \RuntimeException('"' . $path . '" path not found');
		}
		
		$value = Arr::get($jsonArray, $path);
		
		$originalStringLength = mb_strlen($value, '8bit');
		$stringLength = max(0, min($originalStringLength, floor($resultDataSize * $weight)));
		
		Arr::set(
			$jsonArray,
			$path,
			mb_substr(
				$value,
				0,
				$stringLength,
				$encoding
			)
		);
	
		return $jsonArray;
	}
}