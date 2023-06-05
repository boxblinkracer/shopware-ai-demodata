<?php

namespace AIDemoData\Service\Generator;

interface MediaGeneratorInterface
{

    /**
     * @param int $count
     * @param int $maxCount
     * @return void
     */
    public function onMediaGenerated(int $count, int $maxCount): void;

    /**
     * @param string $error
     * @param int $count
     * @param int $maxCount
     * @return void
     */
    public function onMediaGenerationFailed(string $error, int $count, int $maxCount): void;
}
