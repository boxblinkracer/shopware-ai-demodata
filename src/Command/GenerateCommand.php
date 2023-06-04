<?php

namespace AIDemoData\Command;


use AIDemoData\Service\Generator\ProductGenerator;
use AIDemoData\Service\Generator\ProductGeneratorInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class GenerateCommand extends Command implements ProductGeneratorInterface
{

    public static $defaultName = 'ai-demo-data:generate';

    /**
     * @var ProductGenerator
     */
    private $productGenerator;

    /**
     * @var SymfonyStyle
     */
    private $io;

    /**
     * @var int
     */
    private $generatedCount = 0;
    /**
     * @var int
     */
    private $errorCount = 0;


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
            ->addOption('count', null, InputOption::VALUE_REQUIRED, 'Number of products to generate')
            ->addOption('keywords', null, InputOption::VALUE_REQUIRED, 'Keywords to generate products for')
            ->addOption('category', null, InputOption::VALUE_REQUIRED, 'The name of your category in the Storefront to append the products to.')
            ->addOption('with-images', null, InputOption::VALUE_REQUIRED, 'Indicates if images should be generated for the products.');
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int
     * @throws \Exception
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->io = new SymfonyStyle($input, $output);
        $this->io->title('AI Demo Data Generator');

        $count = $input->getOption('count');
        $keyWords = $input->getOption('keywords');
        $category = $input->getOption('category');
        $withImages = $input->getOption('with-images');

        if ($count === false) {
            $count = 1;
        }

        if ($keyWords === null) {
            throw new \Exception('No keywords given.');
        }

        if ($category === null) {
            $category = '';
        }

        if ($withImages === null) {
            $withImages = true;
        } else {
            $withImages = (bool)$withImages;
        }


        $this->productGenerator->setCallback($this);

        $this->productGenerator->setGenerateImages($withImages);


        $this->io->progressStart($count);

        $this->productGenerator->generate($keyWords, $count, $category);

        $this->io->progressFinish();


        if ($this->errorCount <= 0) {
            $this->io->success('Generated ' . $this->generatedCount . ' products for keywords');
        } else {
            $this->io->warning('Generated ' . $this->generatedCount . ' products. Errors: ' . $this->errorCount);
        }

        return 0;
    }

    /**
     * @param string $name
     * @return void
     */
    public function onProductGenerated(string $name): void
    {
        $this->io->comment($name);
        $this->io->progressAdvance();

        $this->generatedCount++;
    }

    /**
     * @param string $error
     * @return void
     */
    public function onProductGenerationFailed(string $error): void
    {
        $this->io->error($error);

        $this->errorCount++;
    }

}
