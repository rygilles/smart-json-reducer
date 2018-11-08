<?php

namespace Tests;

use PHPUnit\Framework\TestCase;
use Rygilles\SmartJsonReducer\Arr;

class ArrTest extends TestCase
{
    /**
     * @test
     */
    public function it_get()
    {
        $foo = [
            'foo' => [
                'bar' => 'foobar'
            ]
        ];
        
        $this->assertEquals('foobar', Arr::get($foo, 'foo.bar'));
    }
    
    /**
     * @test
     */
    public function it_has()
    {
        $foo = [
            'foo' => [
                'bar' => 'foobar'
            ]
        ];
        
        $this->assertTrue(Arr::has($foo, 'foo.bar'));
    }
    
    /**
     * @test
     */
    public function it_set()
    {
        $foo = [];
        
        Arr::set($foo, 'foo.bar', 'foobar');
        
        $this->assertArrayHasKey('foo', $foo);
        $this->assertArrayHasKey('bar', $foo['foo']);
        $this->assertEquals('foobar', $foo['foo']['bar']);
    }
    
    /**
     * @test
     */
    public function it_set_return_array()
    {
        $foo = [
            'foo1' => 'bar1',
            'foo2' => [
                'foo22' => 'bar22'
            ]
        ];
        
        $expectedFoo = $foo;
        
        $expectedFoo['foo'] = [
            'bar' => 'foobar'
        ];
        
        $result = Arr::set($foo, 'foo.bar', 'foobar');
        
        $this->assertEquals($expectedFoo, $result);
    }
}
