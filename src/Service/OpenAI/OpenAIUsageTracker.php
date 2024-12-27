<?php

namespace AIDemoData\Service\OpenAI;

class OpenAIUsageTracker
{
    private static ?OpenAIUsageTracker $instance = null;

    /**
     * @var array<mixed>
     */
    private array $requests = [];

    /**
     * @var array<mixed>
     */
    private array $missingModelPrices = [];


    public static function getInstance(): OpenAIUsageTracker
    {
        if (!self::$instance instanceof OpenAIUsageTracker) {
            self::$instance = new self();
        }

        return self::$instance;
    }


    public function addRequest(string $prompt, float $costUSD): void
    {
        $this->requests[] = [
            'prompt' => $prompt,
            'costUSD' => $costUSD,
        ];
    }

    public function addMissingPrices(string $prompt, string $model): void
    {
        $this->requests[] = [
            'prompt' => $prompt,
            'costUSD' => 0,
        ];

        $this->missingModelPrices[] = $model;
    }

    public function getRequestCount(): int
    {
        return count($this->requests);
    }

    /**
     * Gets the total costs rounded to 2 decimal places
     */
    public function getTotalCostsUSD(): float
    {
        $totalCostsUSD = 0;
        foreach ($this->requests as $cost) {
            $totalCostsUSD += $cost['costUSD'];
        }

        return $totalCostsUSD;
    }

    /**
     * @return mixed[]
     */
    public function getMissingModelPrices(): array
    {
        return $this->missingModelPrices;
    }
}
