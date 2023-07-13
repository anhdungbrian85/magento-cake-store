<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace X247Commerce\Staging\Model;

use Magento\Framework\Api\SearchCriteria\CollectionProcessorInterface;
use Magento\Staging\Api\Data\UpdateSearchResultInterfaceFactory as SearchResultFactory;
use Magento\Staging\Api\UpdateRepositoryInterface;

use Magento\Staging\Model\Entity\PeriodSync\Scheduler as PeriodSyncScheduler;
use Magento\Staging\Model\ResourceModel\Update as UpdateResource;
use X247Commerce\Staging\Model\Update\Validator;
use Magento\Staging\Model\UpdateFactory;
use Magento\Staging\Model\VersionHistoryInterface;
use Magento\Staging\Model\UpdateRepository as StagingUpdateRepository;

/**
 * Represents UpdateRepository class
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class UpdateRepository extends StagingUpdateRepository implements UpdateRepositoryInterface
{
    // Magento\Staging\Model\Update\Validator -> X247Commerce\Staging\Model\Update\Validator
    public function __construct(
        SearchResultFactory $searchResultFactory,
        UpdateResource $resource,
        UpdateFactory $updateFactory,
        Validator $validator,
        VersionHistoryInterface $versionHistory,
        CollectionProcessorInterface $collectionProcessor,
        PeriodSyncScheduler $periodSyncScheduler
    ) {
        parent::__construct($searchResultFactory, $resource, $updateFactory, $validator, $versionHistory, $collectionProcessor, $periodSyncScheduler);
    }
}

?>