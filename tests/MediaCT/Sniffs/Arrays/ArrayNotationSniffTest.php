<?php
/**
 * Copyright MediaCT. All rights reserved.
 * https://www.mediact.nl
 */

namespace MediaCT\Test\Sniffs\Arrays;

class ArrayNotationSniffTest
{
    /**
     * Test to see if array() gets a fixable error message.
     *
     * @return void
     */
    public function testLongNotation()
    {
        array();
        [];
    }
}
