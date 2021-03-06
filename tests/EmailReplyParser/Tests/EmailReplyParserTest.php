<?php

namespace EmailReplyParser\Tests;

use EmailReplyParser\EmailReplyParser;

class EmailReplyParserTest extends TestCase
{
    public function testReadWithNullContent()
    {
        $reply = EmailReplyParser::read(null);

        $this->assertTrue(is_array($reply));
        $this->assertEquals(1, count($reply));
        $this->assertEmpty($reply[0]->__toString());
    }

    public function testReadWithEmptyContent()
    {
        $reply = EmailReplyParser::read('');

        $this->assertTrue(is_array($reply));
        $this->assertEquals(1, count($reply));
        $this->assertEmpty($reply[0]->__toString());
    }
}
