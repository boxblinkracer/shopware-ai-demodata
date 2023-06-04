<?php

namespace AIDemoData\Service\OpenAI;

class Choice
{

    /**
     * @var string
     */
    private $text;


    /**
     * @param string $text
     */
    public function __construct(string $text)
    {
        $this->text = $text;
    }

    /**
     * @return string
     */
    public function getText(): string
    {
        return $this->text;
    }
}
