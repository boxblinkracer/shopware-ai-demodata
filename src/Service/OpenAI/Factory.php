<?php

namespace AIDemoData\Service\OpenAI;

use AIDemoData\Component\OpenAI\OpenAI;
use Shopware\Core\System\SystemConfig\SystemConfigService;

class Factory
{

    /**
     * @var SystemConfigService
     */
    private $configService;

    /**
     * @param SystemConfigService $configService
     */
    public function __construct(SystemConfigService $configService)
    {
        $this->configService = $configService;
    }

    /**
     * @throws \Exception
     * @return Client
     */
    public function create(): Client
    {
        $apiKey = $this->configService->getString('AIDemoData.config.apiKey');

        if (empty($apiKey)) {
            throw new \Exception('No API Key found in plugin configuration. Please provide your key');
        }

        return new Client($apiKey);
    }
}
