<?php

namespace AIDemoData\Service\OpenAI;

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
        $textModel = $this->configService->getOpenAiTextModel();
        $imageModel = $this->configService->getOpenAiImageModel();

        return new Client($apiKey, $textModel, $imageModel);
    }
}
