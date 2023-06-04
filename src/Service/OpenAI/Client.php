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
     * @return Choice
     * @throws \Exception
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

        $json = json_decode($complete, true);

        if (!is_array($json)) {
            return '';
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
            return '';
        }

        if (!isset($choices[0]['text'])) {
            return '';
        }

        return new Choice($choices[0]);
    }

    /**
     * @param string $prompt
     * @return string
     * @throws \Exception
     */
    public function generateImage(string $prompt): string
    {
        $complete = $this->openAi->image([
            "prompt" => $prompt,
            "n" => 1,
            "size" => "1024x1024",
            "response_format" => "url",
        ]);

        $json = json_decode($complete, true);

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
