<?php

namespace Popnikos\RegularExpressionBuilder\Tests;

use PHPUnit\Framework\TestCase;
use Popnikos\RegularExpressionBuilder\PatternBuilder\PatternBuilder;
/**
 * Test class PatternBuilderTest
 * Tests for Popnikos\RegularExpressionBuilder\PatternBuilder
 * @author popnikos
 */
class PatternBuilderTest extends TestCase
{
    /**
     * @cover PatternBuilder::__construct
     */
    public function test__construct()
    {
        $pattern = new PatternBuilder();
        $this->assertInstanceOf('Popnikos\RegularExpressionBuilder\PatternBuilder\PatternBuilder', $pattern);
    }
    
    /**
     * @covers PatternBuilder::setParent
     * @covers PatternBuilder::getParent
     */
    public function testGetSetParent()
    {
        $pattern = new PatternBuilder();
        $this->assertNull($pattern->getParent());
        $this->assertInstanceOf('Popnikos\RegularExpressionBuilder\PatternBuilder\PatternBuilder', $pattern->setParent(new PatternBuilder())->getParent());
    }
    
    /**
     * @covers PatternBuilder::escape
     */
    public function testEscape()
    {
        $pattern = new PatternBuilder();
        $reflex = new \ReflectionClass($pattern);
        $method = $reflex->getMethod('escape');
        $method->setAccessible(true);
        $this->assertEquals('basic string test',$method->invoke($pattern,'basic string test'));
        $this->assertEquals('basic \/string test',$method->invoke($pattern,'basic /string test'));
        $this->assertEquals('basic \\\\\/string test',$method->invoke($pattern,'basic \/string test'));
    }
    
    /**
     * @covers PatternBuilder::startWith
     * @covers PatternBuilder::getFragments
     */
    public function testStartWith()
    {
        $pattern = new PatternBuilder();
        $startFragment = $pattern->contains('toto')->startWith('patata')->getFragments()[0];
        $this->assertEquals('^patata', $startFragment);
    }
    
    /**
     * @covers PatternBuilder::__toString
     */
    public function test__toString()
    {
        $pattern = new PatternBuilder();
        $pattern
                ->startWith('toto')
                ->startCapture()
                    ->add('capture1')
                    ->ou('capture2')
                ->endCapture()
                ->endsWith('tata');
        $this->assertEquals("/^toto(capture1|capture2)tata$/", strval($pattern));
    }
    
    public function testRepeated()
    {
        $pattern = new PatternBuilder();
        $pattern->add('a');
        $pattern->repeated(0, 3);
        $this->assertRegExp("{$pattern}", "blabla");
        $this->assertRegExp("{$pattern}", "blablaa");
        $this->assertRegExp("{$pattern}", "blablaaa");
        $this->assertRegExp("{$pattern}", "blablaaa");
    }
}
