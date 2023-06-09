<?php

namespace AIDemoData\Service\Config;

use Shopware\Core\System\SystemConfig\SystemConfigService;

class ConfigService
{

    /**
     * @var string
     */
    private $openAiKey;

    /**
     * @var bool
     */
    private $productImageEnabled;

    /**
     * @var string
     */
    private $productImageSize;

    /**
     * @var string
     */
    private $mediaImageSize;

    /**
     * @var int
     */
    private $productDescriptionLength;


    /**
     * @param SystemConfigService $configService
     */
    public function __construct(SystemConfigService $configService)
    {
        $this->openAiKey = $configService->getString('AIDemoData.config.apiKey');

        $this->productImageEnabled = $configService->getBool('AIDemoData.config.productImageEnabled');
        $this->productImageSize = $configService->getString('AIDemoData.config.productImageSize');

        $this->mediaImageSize = $configService->getString('AIDemoData.config.mediaImageSize');

        $this->productDescriptionLength = $configService->getInt('AIDemoData.config.productDescriptionLength');
    }

    /**
     * @return string
     */
    public function getOpenAiKey(): string
    {
        return $this->openAiKey;
    }

    /**
     * @return bool
     */
    public function isProductImageEnabled(): bool
    {
        return $this->productImageEnabled;
    }

    /**
     * @return string
     */
    public function getProductImageSize(): string
    {
        if (empty($this->productImageSize)) {
            return '1024x1024';
        }

        return $this->productImageSize;
    }

    /**
     * @return int
     */
    public function getProductDescriptionLength(): int
    {
        if (empty($this->productDescriptionLength)) {
            return 400;
        }

        return (int)$this->productDescriptionLength;
    }

    /**
     * @return string
     */
    public function getMediaImageSize(): string
    {
        if (empty($this->mediaImageSize)) {
            return '1024x1024';
        }

        return $this->mediaImageSize;
    }
}
