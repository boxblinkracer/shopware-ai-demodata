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

        return new Client($apiKey);
    }
}
