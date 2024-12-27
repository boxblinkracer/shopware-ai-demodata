<?php

namespace AIDemoData\Service\Generator;

interface ProductGeneratorInterface
{

    /**
     * @param int $productCount
     * @return void
     */
    public function onProductGenerationStarted(int $productCount): void;

    /**
     * @param string $number
     * @param string $name
     * @param int $count
     * @param int $maxCount
     * @return void
     */
    public function onProductGenerated(string $number, string $name, int $count, int $maxCount): void;

    /**
     * @param string $number
     * @param string $name
     * @param int $count
     * @param int $maxCount
     * @return void
     */
    public function onProductGenerating(string $number, string $name, int $count, int $maxCount): void;

    /**
     * @return void
     */
    public function onProductImageGenerating(): void;

    /**
     * @param string $error
     * @param int $count
     * @param int $maxCount
     * @return void
     */
    public function onProductGenerationFailed(string $error, int $count, int $maxCount): void;
}
