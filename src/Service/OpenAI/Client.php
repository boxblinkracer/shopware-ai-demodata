<?php

namespace AIDemoData\Service\OpenAI;

use Orhanerday\OpenAi\OpenAi;

class Client
{

    /**
     * @var OpenAi
     */
    private $openAi;


    /**
     * @param string $apiKey
     */
    public function __construct(string $apiKey)
    {
        $this->openAi = new OpenAi($apiKey);
    }


    /**
     * @param string $prompt
     * @throws \JsonException
     * @return Choice
     */
    public function generateText(string $prompt): Choice
    {
        $params = [
            'model' => "text-davinci-003",
            'prompt' => $prompt,
            'temperature' => 0.3,
            'max_tokens' => 1000,
            'top_p' => 1.0,
            'frequency_penalty' => 0.0,
            'presence_penalty' => 0.0,
        ];


        $complete = (string)$this->openAi->completion($params);

        $json = json_decode($complete, true, 512, JSON_THROW_ON_ERROR);

        if (!is_array($json)) {
            return new Choice('');
        }

        if (isset($json['error'])) {
            $msg = 'OpenAI Error: ' . $json['error']['message'];
            throw new \Exception($msg);
        }

        if (!isset($json['choices'])) {
            throw new \Exception('No choices found in OpenAI response.');
        }

        $choices = $json['choices'];

        if (!is_array($choices) || count($choices) <= 0) {
            return new Choice('');
        }

        if (!isset($choices[0]['text'])) {
            return new Choice('');
        }

        $choiceData = $choices[0];

        $text = trim($choiceData['text']);

        return new Choice($text);
    }

    /**
     * @param string $prompt
     * @param string $size
     * @throws \JsonException
     * @return string
     */
    public function generateImage(string $prompt, string $size): string
    {
        $complete = $this->openAi->image([
            "prompt" => $prompt,
            "n" => 1,
            "size" => $size,
            "response_format" => "url",
        ]);

        $json = json_decode((string)$complete, true, 512, JSON_THROW_ON_ERROR);

        if (!is_array($json) || count($json) <= 0) {
            throw new \Exception('Image not generated');
        }

        if (isset($json['error'])) {
            $msg = 'OpenAI Error: ' . $json['error']['message'];
            throw new \Exception($msg);
        }

        return (string)$json['data'][0]['url'];
    }
}
