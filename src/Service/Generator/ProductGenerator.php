<?php

namespace AIDemoData\Service\Generator;

use AIDemoData\Repository\CategoryRepository;
use AIDemoData\Repository\CurrencyRepository;
use AIDemoData\Repository\ProductRepository;
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
     * @param Client $client
     * @param ProductRepository $repoProducts
     * @param TaxRepository $repoTaxes
     * @param SalesChannelRepository $repoSalesChannel
     * @param CurrencyRepository $repoCurrency
     * @param CategoryRepository $repoCategory
     * @param ImageUploader $imageUploader
     */
    public function __construct(Client $client, ProductRepository $repoProducts, TaxRepository $repoTaxes, SalesChannelRepository $repoSalesChannel, CurrencyRepository $repoCurrency, CategoryRepository $repoCategory, ImageUploader $imageUploader)
    {
        $this->openAI = $client;
        $this->repoProducts = $repoProducts;
        $this->repoTaxes = $repoTaxes;
        $this->repoSalesChannel = $repoSalesChannel;
        $this->repoCurrency = $repoCurrency;
        $this->repoCategory = $repoCategory;
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
     */
    public function setGenerateImages(bool $generateImages): void
    {
        $this->generateImages = $generateImages;
    }


    /**
     * @param string $keywords
     * @param int $count
     * @param string $category
     * @throws \Exception
     * @return void
     */
    public function generate(string $keywords, int $count, string $category)
    {
        $prompt = 'Create a list of demo products with these properties, separated values with ";". Only write down values and no property names ' . PHP_EOL;
        $prompt .= PHP_EOL;
        $prompt .= 'the following properties should be generated.' . PHP_EOL;
        $prompt .= 'Every resulting line should be in the order and sort provided below:' . PHP_EOL;
        $prompt .= PHP_EOL;
        $prompt .= 'product number' . PHP_EOL;
        $prompt .= 'name of the product' . PHP_EOL;
        $prompt .= 'description (about 400 characters)' . PHP_EOL;
        $prompt .= 'price value (no currency just number)' . PHP_EOL;
        $prompt .= PHP_EOL;
        $prompt .= 'product number should be 20 unique random letters.' . PHP_EOL;
        $prompt .= 'Please only create this number of products: ' . $count . PHP_EOL;
        $prompt .= 'The industry of the products should be: ' . $keywords;


        $choice = $this->openAI->generateText($prompt);

        $text = $choice->getText();


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

                $id = Uuid::randomHex();
                $number = (string)$parts[0];
                $name = (string)$parts[1];
                $description = (string)$parts[2];
                $price = (string)$parts[3];


                if (empty($name)) {
                    continue;
                }

                if (empty($price)) {
                    $price = 50;
                } else {
                    $price = (float)$price;
                }

                if ($this->generateImages) {
                    $temp_file = $this->generateImage($name, $description);
                } else {
                    $temp_file = '';
                }

                $this->createProduct(
                    $id,
                    $name,
                    $number,
                    $category,
                    $description,
                    $price,
                    $temp_file
                );

                if ($this->callback !== null) {
                    $this->callback->onProductGenerated($name);
                }
            } catch (\Exception $ex) {
                if ($this->callback !== null) {
                    $this->callback->onProductGenerationFailed($ex->getMessage());
                }
            }
        }
    }

    /**
     * @param string $id
     * @param string $name
     * @param string $number
     * @param string $categoryName
     * @param string $description
     * @param float $price
     * @param string $image
     * @return void
     */
    private function createProduct(string $id, string $name, string $number, string $categoryName, string $description, float $price, string $image): void
    {
        # just reuse the product one ;)
        $mediaId = $id;
        $visibilityID = $id;
        $coverId = $id;

        $salesChannel = $this->repoSalesChannel->getStorefrontSalesChannel();
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
        ];

        if (!empty($categoryName)) {
            $category = $this->repoCategory->getByName($categoryName);
            $productData['categories'] = [
                [
                    'id' => $category->getId(),
                ]
            ];
        }

        $this->repoProducts->upsert(
            [
                $productData
            ],
            Context::createDefaultContext()
        );
    }

    /**
     * @param string $productName
     * @param string $productDescription
     * @throws \Exception
     * @return string
     */
    private function generateImage(string $productName, string $productDescription): string
    {
        $url = $this->openAI->generateImage($productName . ' ' . $productDescription);

        $tmpFile = tempnam(sys_get_temp_dir(), 'ai-product');

        $ch = curl_init($url);

        /* @phpstan-ignore-next-line */
        $fp = fopen($tmpFile, 'wb');
        /* @phpstan-ignore-next-line */
        curl_setopt($ch, CURLOPT_FILE, $fp);
        /* @phpstan-ignore-next-line */
        curl_setopt($ch, CURLOPT_HEADER, 0);
        /* @phpstan-ignore-next-line */
        curl_exec($ch);
        /* @phpstan-ignore-next-line */
        curl_close($ch);
        /* @phpstan-ignore-next-line */
        fclose($fp);

        return (string)$tmpFile;
    }
}
