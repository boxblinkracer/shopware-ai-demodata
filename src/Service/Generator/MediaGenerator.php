<?php

namespace AIDemoData\Service\Generator;

use AIDemoData\Service\Media\ImageUploader;
use AIDemoData\Service\OpenAI\Client;
use Shopware\Core\Framework\Uuid\Uuid;

class MediaGenerator
{

    /**
     * @var Client
     */
    private $openAI;

    /**
     * @var ImageUploader
     */
    private $imageUploader;

    /**
     * @var MediaGeneratorInterface
     */
    private $callback;


    /**
     * @param Client $client
     * @param ImageUploader $imageUploader
     */
    public function __construct(Client $client, ImageUploader $imageUploader)
    {
        $this->openAI = $client;
        $this->imageUploader = $imageUploader;
    }

    /**
     * @param MediaGeneratorInterface $callback
     */
    public function setCallback(MediaGeneratorInterface $callback): void
    {
        $this->callback = $callback;
    }


    /**
     * @param string $keywords
     * @param string $size
     * @param int $maxCount
     * @return void
     */
    public function generate(string $keywords, string $size, int $maxCount)
    {
        for ($i = 0; $i < $maxCount; $i++) {
            try {
                $tmpImageFile = $this->generateImage($keywords, $size);
                $this->uploadMedia($tmpImageFile);

                if ($this->callback !== null) {
                    $this->callback->onMediaGenerated(($i + 1), $maxCount);
                }
            } catch (\Exception $ex) {
                if ($this->callback !== null) {
                    $this->callback->onMediaGenerationFailed($ex->getMessage(), ($i + 1), $maxCount);
                }
            }
        }
    }

    /**
     * @param string $image
     * @return void
     */
    private function uploadMedia(string $image): void
    {
        $mediaId = Uuid::randomHex();


        # we have to avoid duplicate images (shopware has a problem with it in media) so lets copy it for our id
        $imagePath = __DIR__ . '/../../Resources/files/' . $mediaId . '_tmp.png';
        copy($image, $imagePath);

        $productFolder = $this->imageUploader->getDefaultFolder('cms_page');

        $this->imageUploader->upload(
            $mediaId,
            $productFolder->getId(),
            $imagePath,
            'png',
            'image/png',
        );

        # delete our temp file again
        unlink($imagePath);
    }

    /**
     * @param string $keyword
     * @param string $size
     * @throws \JsonException
     * @return string
     */
    private function generateImage(string $keyword, string $size): string
    {
        $url = $this->openAI->generateImage($keyword, $size);

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
