<?php

namespace Tests;

use PHPUnit\Framework\TestCase;
use Rygilles\SmartJsonReducer\Reducer;

class ReducerTest extends TestCase
{
	/**
	 * @test
	 */
	public function it_reduce_with_faker()
	{
		$faker = \Faker\Factory::create();
		
		$foo = new \stdClass();
		
		$foo->foo1 = substr($faker->text(500), 0, 200);
		$foo->foo2 = substr($faker->text(500), 0, 100);
		$foo->foo = [
			'bar' => substr($faker->text(500), 0, 50),
			'bar2' => substr($faker->text(500), 0, 75)
		];
		
		$weights = [
			'foo1' => 2,
			'foo2' => 5,
			'foo.bar' => 3,
		];
		
		$expectedFoo = clone $foo;
		
		$expectedFoo->foo1 = substr($expectedFoo->foo1, 0, 15);
		$expectedFoo->foo2 = substr($expectedFoo->foo2, 0, 38);
		$expectedFoo->foo['bar'] = substr($expectedFoo->foo['bar'], 0, 23);
		$expectedFoo->foo['bar2'] = substr($expectedFoo->foo['bar2'], 0, 75);
		
		$maxSize = 200;
		
		$json = json_encode($foo);
		
		$resultJsonString = Reducer::reduce($json, $maxSize, $weights);

		$jsonResult = json_decode($resultJsonString, true);
		
		$this->assertEquals(strlen($expectedFoo->foo1), strlen($jsonResult['foo1']));
		$this->assertEquals(strlen($expectedFoo->foo2), strlen($jsonResult['foo2']));
		$this->assertEquals(strlen($expectedFoo->foo['bar']), strlen($jsonResult['foo']['bar']));
		$this->assertEquals(strlen($expectedFoo->foo['bar2']), strlen($jsonResult['foo']['bar2']));
		
		$this->assertEquals($expectedFoo->foo1, $jsonResult['foo1']);
		$this->assertEquals($expectedFoo->foo2, $jsonResult['foo2']);
		$this->assertEquals($expectedFoo->foo['bar'], $jsonResult['foo']['bar']);
		$this->assertEquals($expectedFoo->foo['bar2'], $jsonResult['foo']['bar2']);
		
		$this->assertTrue(mb_strlen($resultJsonString, '8bit') <= $maxSize);
	}
	
	/**
	 * @test
	 */
	public function it_reduce_hardcoded()
	{
		$foo = new \stdClass();
		
		$foo->foo1 = 'Sapiente repellat consectetur tempore ut omnis error voluptate ipsum. ' .
			'Cum nihil temporibus vel sunt deserunt nisi unde. Ipsa aperiam qui sed harum molestiae qui consectetur. ' .
			'Commodi voluptatem maiores';
		
		$foo->foo2 = 'Eligendi officiis sed aspernatur totam quia explicabo. ' .
			'Doloribus eum quisquam officiis. Error pariat';
		
		$foo->foo = [
			'bar' => 'Non qui molestias aliquam laboriosam. Nulla omnis ',
			'bar2' => 'Ut rerum velit eum commodi. Qui ea et enim cupiditate. Sequi nisi iure qui '
		];
		
		$weights = [
			'foo1' => 2,
			'foo2' => 5,
			'foo.bar' => 3,
		];
		
		$expectedFoo = clone $foo;
		
		$expectedFoo->foo1 = substr($expectedFoo->foo1, 0, 15);
		$expectedFoo->foo2 = substr($expectedFoo->foo2, 0, 38);
		$expectedFoo->foo['bar'] = substr($expectedFoo->foo['bar'], 0, 23);
		$expectedFoo->foo['bar2'] = substr($expectedFoo->foo['bar2'], 0, 75);
		
		$maxSize = 200;
		
		$json = json_encode($foo);
		
		$resultJsonString = Reducer::reduce($json, $maxSize, $weights);
		
		$jsonResult = json_decode($resultJsonString, true);
		
		$this->assertEquals(strlen($expectedFoo->foo1), strlen($jsonResult['foo1']));
		$this->assertEquals(strlen($expectedFoo->foo2), strlen($jsonResult['foo2']));
		$this->assertEquals(strlen($expectedFoo->foo['bar']), strlen($jsonResult['foo']['bar']));
		$this->assertEquals(strlen($expectedFoo->foo['bar2']), strlen($jsonResult['foo']['bar2']));
		
		$this->assertEquals($expectedFoo->foo1, $jsonResult['foo1']);
		$this->assertEquals($expectedFoo->foo2, $jsonResult['foo2']);
		$this->assertEquals($expectedFoo->foo['bar'], $jsonResult['foo']['bar']);
		$this->assertEquals($expectedFoo->foo['bar2'], $jsonResult['foo']['bar2']);
		
		$this->assertTrue(mb_strlen($resultJsonString, '8bit') <= $maxSize);
	}
}