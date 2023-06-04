<?php

namespace AIDemoData\Repository;

use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;
use Shopware\Core\System\Tax\TaxEntity;

class ProductRepository
{

    /**
     * @var EntityRepository
     */
    private $repository;


    /**
     * @param EntityRepository $repository
     */
    public function __construct(EntityRepository $repository)
    {
        $this->repository = $repository;
    }


    /**
     * @param array<mixed> $data
     * @param Context $context
     * @return void
     */
    public function upsert(array $data, Context $context): void
    {
        $this->repository->upsert($data, $context);
    }
}
