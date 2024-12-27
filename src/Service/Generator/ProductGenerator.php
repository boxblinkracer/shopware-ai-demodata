<?php

namespace AIDemoData\Service\Generator;

use AIDemoData\Repository\CategoryRepository;
use AIDemoData\Repository\CurrencyRepository;
use AIDemoData\Repository\ProductRepository;
use AIDemoData\Repository\PropertyRepository;
use AIDemoData\Repository\SalesChannelRepository;
use AIDemoData\Repository\TaxRepository;
use AIDemoData\Service\Media\ImageUploader;
use AIDemoData\Service\OpenAI\Client;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\Uuid\Uuid;

class ProductGenerator
{

    /**
     * @var Client
     */
    private $openAI;

    /**
     * @var ProductRepository
     */
    private $repoProducts;

    /**
     * @var TaxRepository
     */
    private $repoTaxes;

    /**
     * @var SalesChannelRepository
     */
    private $repoSalesChannel;

    /**
     * @var CurrencyRepository
     */
    private $repoCurrency;

    /**
     * @var CategoryRepository
     */
    private $repoCategory;

    /**
     * @var PropertyRepository
     */
    private $repoProperties;

    /**
     * @var ImageUploader
     */
    private $imageUploader;

    /**
     * @var ProductGeneratorInterface
     */
    private $callback;

    /**
     * @var bool
     */
    private $generateImages;

    /**
     * @var string
     */
    private $imageSize;


    /**
     * @param Client $client
     * @param ProductRepository $repoProducts
     * @param TaxRepository $repoTaxes
     * @param SalesChannelRepository $repoSalesChannel
     * @param CurrencyRepository $repoCurrency
     * @param CategoryRepository $repoCategory
     * @param ImageUploader $imageUploader
     */
    public function __construct(Client $client, ProductRepository $repoProducts, TaxRepository $repoTaxes, SalesChannelRepository $repoSalesChannel, CurrencyRepository $repoCurrency, CategoryRepository $repoCategory, PropertyRepository $repoPropertyGroup, ImageUploader $imageUploader)
    {
        $this->openAI = $client;
        $this->repoProducts = $repoProducts;
        $this->repoTaxes = $repoTaxes;
        $this->repoSalesChannel = $repoSalesChannel;
        $this->repoCurrency = $repoCurrency;
        $this->repoCategory = $repoCategory;
        $this->repoProperties = $repoPropertyGroup;
        $this->imageUploader = $imageUploader;

        $this->generateImages = true;
    }

    /**
     * @param ProductGeneratorInterface $callback
     * @return void
     */
    public function setCallback(ProductGeneratorInterface $callback): void
    {
        $this->callback = $callback;
    }

    /**
     * @param bool $generateImages
     * @param string $imageSize
     * @return void
     */
    public function setGenerateImages(bool $generateImages, string $imageSize): void
    {
        $this->generateImages = $generateImages;
        $this->imageSize = $imageSize;
    }


    /**
     * @param string $keywords
     * @param int $maxCount
     * @param string $category
     * @param string $salesChannel
     * @param int $descriptionLength
     * @throws \JsonException
     * @return void
     */
    public function generate(string $keywords, int $maxCount, string $category, string $salesChannel, int $descriptionLength, string $variantPropertyGroupId)
    {
        if (empty($keywords)) {
            throw new \Exception('No keywords provided. Please tell the plugin what to generate.');
        }

        if (empty($variantPropertyGroupId)) {
            throw new \Exception('No variant property group provided. Please open the plugin configuration and configure what property group to use when generating variants!');
        }

        $prompt = 'Create a list of demo products with these properties, separated values with ";". Only write down values and no property names ' . PHP_EOL;
        $prompt .= PHP_EOL;
        $prompt .= 'the following properties should be generated.' . PHP_EOL;
        $prompt .= 'Every resulting line should be in the order and sort provided below:' . PHP_EOL;
        $prompt .= PHP_EOL;
        $prompt .= 'product count.' . PHP_EOL;
        $prompt .= 'product number code. should be 16 unique random alphanumeric.' . PHP_EOL;
        $prompt .= 'name of the product.' . PHP_EOL;
        $prompt .= 'description (about ' . $descriptionLength . ' characters).' . PHP_EOL;
        $prompt .= 'price value (no currency just number).' . PHP_EOL;
        $prompt .= 'EAN code.' . PHP_EOL;
        $prompt .= 'SEO description (max 100 characters).' . PHP_EOL;
        $prompt .= 'variant indicator (1 if variants make sense for the product, 0 if it does not make sense).' . PHP_EOL;
        $prompt .= PHP_EOL;
        $prompt .= 'Please only create exactly this number of products: ' . $maxCount . PHP_EOL;
        $prompt .= PHP_EOL;
        $prompt .= 'The industry of the products should be: ' . $keywords;


        $choice = $this->openAI->generateText($prompt);

        $text = $choice->getText();

        $currentCount = 0;

        /* @phpstan-ignore-next-line */
        foreach (preg_split("/((\r?\n)|(\r\n?))/", $text) as $line) {
            if (empty($line)) {
                continue;
            }

            try {
                $parts = explode(';', $line);

                if (count($parts) < 4) {
                    continue;
                }

                $currentCount++;

                $id = Uuid::randomHex();
                $number = (string)$parts[1];
                $name = (string)$parts[2];
                $description = (string)$parts[3];
                $price = (string)$parts[4];
                $ean = (string)$parts[5];
                $metaDescription = (string)$parts[6];
                $isVariant = (bool)$parts[7];

                if (empty($name)) {
                    continue;
                }

                if (empty($price)) {
                    $price = 50;
                } else {
                    $price = (float)$price;
                }

                if ($this->callback !== null) {
                    $this->callback->onProductGenerating($number, $name, $currentCount, $maxCount);
                }

                if ($this->generateImages) {
                    if ($this->callback !== null) {
                        $this->callback->onProductImageGenerating();
                    }

                    $tmpImageFile = $this->generateImage($name, $description);
                } else {
                    $tmpImageFile = '';
                }

                $this->createProduct(
                    $id,
                    $name,
                    $number,
                    $category,
                    $salesChannel,
                    $description,
                    $price,
                    $tmpImageFile,
                    $ean,
                    $metaDescription,
                    $isVariant,
                    $variantPropertyGroupId
                );

                if ($this->callback !== null) {
                    $this->callback->onProductGenerated($number, $name, $currentCount, $maxCount);
                }
            } catch (\Exception $ex) {
                var_dump($ex->getMessage());
                var_dump($ex->getTraceAsString());

                if ($this->callback !== null) {
                    $this->callback->onProductGenerationFailed($ex->getMessage(), $currentCount, $maxCount);
                }
            }
        }
    }

    /**
     * @param string $id
     * @param string $name
     * @param string $number
     * @param string $categoryName
     * @param string $salesChannelName
     * @param string $description
     * @param float $price
     * @param string $image
     * @param string $ean
     * @param string $metaDescription
     * @param bool $isColorVariant
     * @param string $variantPropertyGroupId
     * @throws \Exception
     * @return void
     */
    private function createProduct(string $id, string $name, string $number, string $categoryName, string $salesChannelName, string $description, float $price, string $image, string $ean, string $metaDescription, bool $isColorVariant, string $variantPropertyGroupId): void
    {
        # just reuse the product one ;)
        $mediaId = $id;
        $visibilityID = $id;
        $coverId = $id;

        if (!empty($salesChannelName)) {
            $salesChannel = $this->repoSalesChannel->getByName($salesChannelName);
        } else {
            $salesChannel = $this->repoSalesChannel->getStorefrontSalesChannel();
        }
        $tax = $this->repoTaxes->getTaxEntity(19);
        $currency = $this->repoCurrency->getCurrencyEuro();


        $imageSource = __DIR__ . '/../../Resources/files/product/default.png';

        if (!empty($image)) {
            $imageSource = $image;
        }

        # we have to avoid duplicate images (shopware has a problem with it in media) so lets copy it for our id
        $imagePath = __DIR__ . '/../../Resources/files/' . $id . '_tmp.png';
        copy($imageSource, $imagePath);

        $productFolder = $this->imageUploader->getDefaultFolder('product');

        $this->imageUploader->upload(
            $mediaId,
            $productFolder->getId(),
            $imagePath,
            'png',
            'image/png',
        );

        # delete our temp file again
        unlink($imagePath);

        $productData = [
            'id' => $id,
            'name' => $name,
            'taxId' => $tax->getId(),
            'productNumber' => $number,
            'description' => $description,
            'ean' => $ean,
            'metaDescription' => substr($metaDescription, 0, 255),
            'visibilities' => [
                [
                    'id' => $visibilityID,
                    'salesChannelId' => $salesChannel->getId(),
                    'visibility' => 30,
                ]
            ],
            'stock' => 99,
            'price' => [
                [
                    'currencyId' => $currency->getId(),
                    'gross' => $price,
                    'net' => $price,
                    'linked' => true,
                ]
            ],
            'media' => [
                [
                    'id' => $coverId,
                    'mediaId' => $mediaId,
                ]
            ],
            'coverId' => $coverId,
            'options' => [],
            'properties' => []
        ];

        if (!empty($categoryName)) {
            $category = $this->repoCategory->getByNameAndSalesChannel($categoryName, $salesChannel->getId());

            $productData['categories'] = [
                [
                    'id' => $category->getId(),
                ]
            ];
        }

        $colors = $this->repoProperties->findOptions($variantPropertyGroupId);

        if ($isColorVariant) {
            foreach ($colors as $color) {
                $productData['configuratorSettings'][] = [
                    "optionId" => $color->getId(),
                ];
            }
        }

        $this->repoProducts->upsert(
            [
                $productData
            ],
            Context::createDefaultContext()
        );

        if ($isColorVariant) {
            $variantData = [];
            $variantCount = 1;

            foreach ($colors as $color) {
                $variantData[] = [
                    "parentId" => $id,
                    "productNumber" => $number . '.' . $variantCount,
                    "productStates" => [],
                    "stock" => 99,
                    "downloads" => [],
                    "options" => [
                        [
                            "id" => $color->getId(),
                            "entity" => $color,
                        ]
                    ]
                ];

                $variantCount++;
            }

            $this->repoProducts->upsert($variantData, Context::createDefaultContext());
        }
    }

    /**
     * @param string $productName
     * @param string $productDescription
     * @throws \Exception
     * @return string
     */
    private function generateImage(string $productName, string $productDescription): string
    {
        $prompt = $productName . ' ' . $productDescription;

        $url = $this->openAI->generateImage($prompt, $this->imageSize);

        $tmpFile = tempnam(sys_get_temp_dir(), 'ai-product');

        $ch = curl_init($url);

        /** @var resource $fp */
        $fp = fopen($tmpFile, 'wb');
        curl_setopt($ch, CURLOPT_FILE, $fp);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_exec($ch);
        curl_close($ch);
        fclose($fp);

        return (string)$tmpFile;
    }
}
