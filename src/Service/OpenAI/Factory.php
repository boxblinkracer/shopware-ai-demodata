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
     * @return Client
     * @throws \Exception
     */
    public function create(): Client
    {
        $apiKey = (string)$this->configService->get('AIDemoData.config.apiKey');

        if (empty($apiKey)) {
            throw new \Exception('No API Key found in plugin configuration. Please provide your key');
        }

        return new Client($apiKey);
    }

}
