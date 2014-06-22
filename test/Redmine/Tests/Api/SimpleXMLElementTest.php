<?php

namespace Redmine\Tests\Api;

use Redmine\Api\SimpleXMLElement;

/**
 * @coversDefaultClass Redmine\Api\SimpleXMLElement
 * @author     Malte Gerth <mail@malte-gerth.de>
 */
class SimpleXMLElementTest extends \PHPUnit_Framework_TestCase
{

    /**
     * Test addChild()
     *
     * @covers ::addChild
     * @test
     *
     * @return void
     */
    public function testAddChildWithUnescapedString()
    {
        // Test values
        $newElementName = 'newTestChild';
        $newElementValue = 'Bug & Feature';

        // Perform tests
        $xmlElement = new SimpleXMLElement('<?xml version="1.0"?><issue></issue>');
        $node = $xmlElement->addChild($newElementName, $newElementValue);
        $this->assertSame($newElementName, $node->getName());
        $this->assertSame($newElementValue, current($node));
    }
}
