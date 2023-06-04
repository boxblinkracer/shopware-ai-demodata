<?php

namespace AIDemoData\Service\OpenAI;

class Choice
{

    /**
     * @var string
     */
    private $text;


    /**
     * @param array<mixed> $data
     */
    public function __construct(array $data)
    {
        $this->text = trim($data['text']);
    }

    /**
     * @return string
     */
    public function getText(): string
    {
        return $this->text;
    }

}