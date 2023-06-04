<?php

namespace AIDemoData\Command;


use AIDemoData\Service\Generator\ProductGenerator;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class GenerateCommand extends Command
{

    public static $defaultName = 'ai-demo-data:generate';

    /**
     * @var ProductGenerator
     */
    private $productGenerator;


    /**
     * @param ProductGenerator $productGenerator
     */
    public function __construct(ProductGenerator $productGenerator)
    {
        parent::__construct();

        $this->productGenerator = $productGenerator;
    }


    /**
     * @return void
     */
    protected function configure(): void
    {
        $this
            ->setName((string)self::$defaultName)
            ->setDescription('Generator AI Demo data with the help of OpenAI.')
            ->addOption('count', 'c', InputOption::VALUE_REQUIRED, 'Number of products to generate')
            ->addOption('keywords', 'k', InputOption::VALUE_REQUIRED, 'Keywords to generate products for');
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int
     * @throws \Exception
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $io->title('AI Demo Data Generator');

        $count = $input->getOption('count');
        $keyWords = $input->getOption('keywords');

        if ($count === false) {
            $count = 1;
        }

        if ($keyWords === null) {
            throw new \Exception('No keywords given.');
        }


        $this->productGenerator->generate($keyWords, $count);

        return 1;
    }
}
