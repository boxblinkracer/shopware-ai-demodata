<?php

namespace AIDemoData\Repository;

use Shopware\Core\Defaults;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Sorting\FieldSorting;
use Shopware\Core\System\SalesChannel\SalesChannelEntity;
use Shopware\Core\System\Tax\TaxEntity;

class SalesChannelRepository
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
     * @return SalesChannelEntity
     */
    public function getStorefrontSalesChannel(): SalesChannelEntity
    {
        $criteria = (new Criteria())
            ->addFilter(new EqualsFilter('typeId', Defaults::SALES_CHANNEL_TYPE_STOREFRONT))
            ->addSorting(new FieldSorting('createdAt', FieldSorting::ASCENDING))
            ->setLimit(1);

        return $this->repository
            ->search($criteria, Context::createDefaultContext())
            ->first();
    }

    public function getByName(string $name): SalesChannelEntity
    {
        $criteria = new Criteria();
        $criteria->addFilter(new EqualsFilter('name', $name));
        $criteria->setLimit(1);

        return $this->repository
            ->search($criteria, Context::createDefaultContext())
            ->first();
    }
}
