<?php
 
namespace X247Commerce\Yext\Controller\Adminhtml\Entity;

use Magento\Framework\Controller\ResultFactory;
 
class MassSync extends \Amasty\Storelocator\Controller\Adminhtml\Location
{
    protected $filter;
    protected $yextApi;
    protected $resource;
    protected $connection;
    protected $yextAttribute;

    public function __construct (
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\Registry $coreRegistry,
        \Magento\Backend\Model\View\Result\ForwardFactory $resultForwardFactory,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Magento\Framework\Filesystem $filesystem,
        \Magento\MediaStorage\Model\File\UploaderFactory $fileUploaderFactory,
        \Amasty\Base\Model\Serializer $serializer,
        \Magento\Framework\Filesystem\Io\File $ioFile,
        \Amasty\Storelocator\Model\Location $locationModel,
        \Psr\Log\LoggerInterface $logger,
        \Magento\Ui\Component\MassAction\Filter $filter,
        \Amasty\Storelocator\Model\ResourceModel\Location\Collection $locationCollection,
        \X247Commerce\Yext\Service\YextApi $yextApi,
        \Magento\Framework\App\ResourceConnection $resource,
        \X247Commerce\Yext\Model\YextAttribute $yextAttribute
    ) {
        parent::__construct($context, $coreRegistry, $resultForwardFactory, $resultPageFactory, $filesystem, $fileUploaderFactory, $serializer, $ioFile, $locationModel, $logger, $filter, $locationCollection);
        $this->yextApi = $yextApi;
        $this->resource = $resource;
        $this->connection = $resource->getConnection();
        $this->yextAttribute = $yextAttribute;
    }

 
    /**
     * Execute action
     *
     * @return \Magento\Backend\Model\View\Result\Redirect
     * @throws \Magento\Framework\Exception\LocalizedException | \Exception
     */
    public function execute()
    {
        $collection = $this->filter->getCollection($this->locationCollection);
        $collectionSize = $collection->getSize();

        $allYextEntityIdValue = array_column($this->yextAttribute->getAllYextEntityIdValue(),'value', 'store_id');
        $yextEntityIds = [];
        if ($collectionSize) {
            foreach ($collection as $location) {
                if (array_key_exists($location->getId(), $allYextEntityIdValue)) {
                    $yextEntityIds[] = $allYextEntityIdValue[$location->getId()];
                }
            }
        }
        $filterParams = [];
        foreach ($yextEntityIds as $id) {
            $filterParams['$or'][] = ['entityId' => ['$eq' => $id]];
        }
        $listResponse = json_decode($this->yextApi->getList(['filter'=> json_encode($filterParams)]), true);

        foreach ($listResponse['response']['entities'] as $locationData) {
            if (in_array($locationData['meta']['id'], $allYextEntityIdValue)) {
                $locationId = (int) array_search($locationData['meta']['id'], $allYextEntityIdValue);
                $location = $this->locationModel->load($locationId);
                $syncData = $this->yextAttribute->responseDataProcess($locationData);
                $location->addData($syncData);
                $location->save();
            }
        }
        $this->messageManager->addSuccess(__('A total of %1 record(s) have been modified.', count($listResponse['response']['entities'])));

        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        return $resultRedirect->setPath('amasty_storelocator/location/');
    }
}