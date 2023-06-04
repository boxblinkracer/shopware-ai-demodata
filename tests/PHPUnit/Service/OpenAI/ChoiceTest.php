<?php

namespace AIDemoData\Tests\PHPUnit\Service\OpenAI;

use AIDemoData\Service\OpenAI\Choice;
use PHPUnit\Framework\TestCase;

class ChoiceTest extends TestCase
{

    /**
     * This test verifies that the text is set correctly.
     * @return void
     */
    public function testText(): void
    {
        $choice = new Choice('abc');

        $this->assertEquals('abc', $choice->getText());
    }

}