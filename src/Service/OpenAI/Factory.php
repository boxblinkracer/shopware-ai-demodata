<?php

namespace AIDemoData\Service\OpenAI;

use AIDemoData\Component\OpenAI\OpenAI;
use AIDemoData\Service\Config\ConfigService;

class Factory
{

    /**
     * @var ConfigService
     */
    private $configService;

    /**
     * @param ConfigService $configService
     */
    public function __construct(ConfigService $configService)
    {
        $this->configService = $configService;
    }

    /**
     * @throws \Exception
     * @return Client
     */
    public function create(): Client
    {
        $apiKey = $this->configService->getOpenAiKey();

        if (empty($apiKey)) {
            throw new \Exception('No API Key found in plugin configuration. Please provide your key');
        }

        return new Client($apiKey);
    }
}
