<?php

namespace AIDemoData\Repository;

use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;
use Shopware\Core\System\Tax\TaxEntity;

class TaxRepository
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
     * @param int $taxRate
     * @return TaxEntity
     */
    public function getTaxEntity(int $taxRate): TaxEntity
    {
        $criteria = (new Criteria())
            ->addFilter(new EqualsFilter('taxRate', $taxRate))
            ->setLimit(1);

        return $this->repository
            ->search($criteria, Context::createDefaultContext())
            ->first();
    }


}