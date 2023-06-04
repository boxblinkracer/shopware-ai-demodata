<?php

namespace AIDemoData\Service\Generator;

interface ProductGeneratorInterface
{

    /**
     * @param string $name
     * @return void
     */
    public function onProductGenerated(string $name): void;

    /**
     * @param string $error
     * @return void
     */
    public function onProductGenerationFailed(string $error): void;
}
