<?php

namespace AIDemoData\Service\OpenAI;

use Orhanerday\OpenAi\OpenAi;

class Client
{

    /**
     * @var string
     */
    private string $apiKey;

    /**
     * @var OpenAi
     */
    private $openAi;


    /**
     * @param string $apiKey
     */
    public function __construct(string $apiKey)
    {
        $this->apiKey = $apiKey;
    }


    /**
     * @param string $prompt
     * @throws \JsonException
     * @return Choice
     */
    public function generateText(string $prompt): Choice
    {
        if (empty($this->apiKey)) {
            throw new \Exception('No API Key found in plugin configuration. Please provide your key');
        }

        $this->openAi = new OpenAi($this->apiKey);

        $model = 'gpt-3.5-turbo-instruct';

        $params = [
            'model' => $model,
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
            $msg = 'OpenAI Error: ' . $json['error']['message'] . '[' . $json['error']['code'] . ']';
            throw new \Exception($msg);
        }

        $this->trackTextCosts($prompt, $json, $model);

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
        if (empty($this->apiKey)) {
            throw new \Exception('No API Key found in plugin configuration. Please provide your key');
        }

        $this->openAi = new OpenAi($this->apiKey);

        $model = "dall-e-3";

        $complete = $this->openAi->image([
            "model" => $model,
            "prompt" => $prompt,
            "n" => 1,
            "size" => $size,
            "style" => "natural",
            "quality" => "standard",
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

        if ($size === "1024x1024") {
            OpenAIUsageTracker::getInstance()->addRequest($prompt, 0.040);
        } else {
            OpenAIUsageTracker::getInstance()->addRequest($prompt, 0.080);
        }

        return (string)$json['data'][0]['url'];
    }


    /**
     * @param string $prompt
     * @param array<mixed> $json
     * @param string $model
     * @return void
     */
    private function trackTextCosts(string $prompt, array $json, string $model): void
    {
        if (!isset($json['usage'])) {
            OpenAIUsageTracker::getInstance()->addMissingPrices($prompt, $model);
            return;
        }

        $pricingData = json_decode((string)file_get_contents(__DIR__ . '/pricing.json'), true);

        if (!isset($pricingData[$model])) {
            OpenAIUsageTracker::getInstance()->addMissingPrices($prompt, $model);
        }

        $costsInputToken = $pricingData[$model]['input'] ?? 0;
        $costsOutputToken = $pricingData[$model]['output'] ?? 0;

        $totalInputTokens = $json['usage']['prompt_tokens'];
        $totalOutputTokens = $json['usage']['completion_tokens'];

        $costUSD = ($totalInputTokens * $costsInputToken) + ($totalOutputTokens * $costsOutputToken);

        OpenAIUsageTracker::getInstance()->addRequest($prompt, $costUSD);
    }
}
