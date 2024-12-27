<?php

namespace AIDemoData\Repository;

use Shopware\Core\Content\Property\PropertyGroupEntity;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityCollection;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\EntitySearchResult;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;

class PropertyRepository
{

    /**
     * @var EntityRepository
     */
    private $repoGroupOptionRepository;


    /**
     * @param EntityRepository $repoGroupOptionRepository
     */
    public function __construct(EntityRepository $repoGroupOptionRepository)
    {
        $this->repoGroupOptionRepository = $repoGroupOptionRepository;
    }

    /**
     * @param string $groupId
     * @return EntityCollection<PropertyGroupEntity>
     */
    public function findOptions(string $groupId): EntityCollection
    {
        $criteria = (new Criteria())
            ->addFilter(new EqualsFilter('groupId', $groupId));

        /** @var EntitySearchResult<PropertyGroupEntity> $result */
        $result = $this->repoGroupOptionRepository->search(
            $criteria,
            Context::createDefaultContext()
        );

        /** @var EntityCollection<PropertyGroupEntity> $entities */
        $entities = $result->getEntities();

        return $entities;
    }
}
