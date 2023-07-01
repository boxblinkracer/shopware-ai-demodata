<?php

namespace AIDemoData\Repository;

use Shopware\Core\Content\Category\CategoryCollection;
use Shopware\Core\Content\Category\CategoryEntity;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\EntitySearchResult;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\ContainsFilter;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;

class CategoryRepository
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
     * @return CategoryEntity
     */
    public function getFirst(): CategoryEntity
    {
        $criteria = (new Criteria())->addFilter(
            new EqualsFilter('level', '1')
        )->setLimit(1);

        return $this->repository
            ->search($criteria, Context::createDefaultContext())
            ->first();
    }

    /**
     * @param string $name
     * @return EntitySearchResult
     */
    public function getByName(string $name): EntitySearchResult
    {
        $criteria = new Criteria();
        $criteria->addFilter(new EqualsFilter('name', $name));
        $criteria->addAssociation('mainCategories');

        return $this->repository->search($criteria, Context::createDefaultContext());
    }

    /**
     * @param string $name
     * @param string $salesChannelId
     * @throws \Exception
     * @return CategoryEntity
     */
    public function getByNameAndSalesChannel(string $name, string $salesChannelId): CategoryEntity
    {
        $criteria = new Criteria();
        $criteria->addFilter(new EqualsFilter('name', $name));

        $collection = $this->repository->search($criteria, Context::createDefaultContext())->getEntities();

        /** @var CategoryEntity $category */
        foreach ($collection as $category) {
            $path = $category->getPath();
            $paths = explode('|', $path);

            $rootPath = $paths[1];
            $catRoot = $this->getById($rootPath);

            $scID = $catRoot->getNavigationSalesChannels()->first()->getId();

            if ($scID === $salesChannelId) {
                return $category;
            }
        }

        throw new \Exception('Category not found');
    }

    /**
     * @param string $id
     * @return CategoryEntity
     */
    public function getById(string $id): CategoryEntity
    {
        $criteria = new Criteria();
        $criteria->addFilter(new EqualsFilter('id', $id));
        $criteria->setLimit(1); 

        return $this->repository
            ->search($criteria, Context::createDefaultContext())
            ->first();
    }
}
