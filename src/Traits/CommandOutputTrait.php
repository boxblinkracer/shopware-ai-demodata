<?php

namespace AIDemoData\Traits;

use AIDemoData\Service\OpenAI\OpenAIUsageTracker;
use Symfony\Component\Console\Output\OutputInterface;

trait CommandOutputTrait
{
    protected function showOpenAIUsageData(OutputInterface $output): void
    {
        $tracker = OpenAIUsageTracker::getInstance();

        $output->writeln("\n=== OpenAI Usage Summary =====================");
        $output->writeln("Total Requests:        " . $tracker->getRequestCount());
        $output->writeln("Estimated Costs:       " . $tracker->getTotalCostsUSD() . " USD (approx.)");
        if (count($tracker->getMissingModelPrices()) > 0) {
            $output->writeln("Missing Model Prices:  " . implode(', ', $tracker->getMissingModelPrices()));
        }
        $output->writeln("==============================================\n");
    }
}
