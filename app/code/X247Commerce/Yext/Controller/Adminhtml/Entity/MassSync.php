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
    protected $gallery;
    protected $galleryCollection;
    protected $locationResource;
    protected $yextHelper;
    protected $adminSource;
    protected $locatorSourceResolver;

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
        \X247Commerce\Yext\Model\YextAttribute $yextAttribute,
        \Amasty\Storelocator\Model\GalleryFactory $gallery,
        \Amasty\Storelocator\Model\ResourceModel\Gallery\Collection $galleryCollection,
        \Amasty\Storelocator\Model\ResourceModel\Location $locationResource,
        \X247Commerce\StoreLocatorSource\Model\AdminSource $adminSource,
        \X247Commerce\StoreLocatorSource\Model\ResourceModel\LocatorSourceResolver $locatorSourceResolver,
        \X247Commerce\Yext\Helper\YextHelper $yextHelper
    ) {
        parent::__construct($context, $coreRegistry, $resultForwardFactory, $resultPageFactory, $filesystem, $fileUploaderFactory, $serializer, $ioFile, $locationModel, $logger, $filter, $locationCollection);
        $this->yextApi = $yextApi;
        $this->resource = $resource;
        $this->connection = $resource->getConnection();
        $this->yextAttribute = $yextAttribute;
        $this->gallery = $gallery;
        $this->galleryCollection = $galleryCollection;
        $this->locationResource = $locationResource;
        $this->adminSource = $adminSource;
        $this->locatorSourceResolver = $locatorSourceResolver;
        $this->yextHelper = $yextHelper;
    }

 
    /**
     * Execute action
     *
     * @return \Magento\Backend\Model\View\Result\Redirect
     * @throws \Magento\Framework\Exception\LocalizedException | \Exception
     */
    public function execute()
    {
                   
        $locationsCollection = $this->filter->getCollection($this->locationCollection);
        $collectionSize = $locationsCollection->getSize();

        $allYextEntityIdValue = array_column($this->yextAttribute->getAllYextEntityIdValue(),'value', 'store_id');

        $yextEntityIds = [];
        if ($collectionSize) {
            foreach ($locationsCollection as $location) {
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
                    
            $syncData = [];
            if (in_array($locationData['meta']['id'], $allYextEntityIdValue)) {
                // try {                        
                    $locationId = (int) array_search($locationData['meta']['id'], $allYextEntityIdValue);
                    
                    $location = $this->locationModel->load($locationId);
                    $syncData = $this->yextAttribute->responseDataProcess($locationData);                    
                    $locationAdmin = $this->yextAttribute->editAdminUser($syncData, $location->getId());
                    $locationSourceCodes = $this->yextHelper->getSourceCodeByAmastyLocationId($location->getId());
                    
                    if (!$locationSourceCodes) {
                        $newSource = $this->yextAttribute->editSource($syncData, $location->getId());
                    
                        $defaultAssignStockId = $this->yextHelper->getDefaultAssignStock();

                        if (!is_null($newSource)) {
                            $this->yextAttribute->assignSourceToStock($newSource->getSourceCode(), $defaultAssignStockId, $location->getId());
                        }

                        if (!is_null($locationAdmin) && !is_null($newSource)) {
                            $this->adminSource->setData(['user_id' => $locationAdmin->getUserId(), 'source_code' => $newSource->getSourceCode()]);
                            $this->adminSource->save();
                            $this->locatorSourceResolver->assignAmLocatorStoreToSource($location->getId(), $newSource->getSourceCode());
                        }
                    } else {
                        foreach ($locationSourceCodes as $source)
                        {
                            $usersAssignToSource = $this->locatorSourceResolver->getUserBySource($source);
                            if (!$usersAssignToSource) {
                                $this->adminSource->setData(['user_id' => $locationAdmin->getUserId(), 'source_code' => $source]);
                                $this->adminSource->save();
                            } else {
                                if (!in_array($locationAdmin->getId(), $usersAssignToSource)) {
                                    $this->adminSource->setData(['user_id' => $locationAdmin->getUserId(), 'source_code' => $source]);
                                    $this->adminSource->save();
                                }
                            }
                        }
                    }
                    if (isset($locationData['hours'])) {
                        $locationSchedule = $this->yextAttribute->editLocationSchedule($location, $locationData['hours']);
                        $syncData['schedule'] = $locationSchedule->getId();
                    }
                    //@todo sync Photo gallery from Yext, download image and link to store location
                    // $data = [];
                    // $data['id'] = $locationId;
                    // foreach ($syncData['photoGallery'] as $imageUrl) {
                        //download image from Yext to sever
                        // $img = $this->yextAttribute->downloadLocationImageToLocal($imageUrl, $locationId);
                        // if ($img) {                            
                        //     $data["gallery_image"][] = ['name' => $img,
                        //                'full_path' => $img,
                        //                'type' => "image/jpeg",
                        //                "tmp_name" => "/tmp/phpW2tbW5",
                        //                "file" => $img,
                        //                "error" => 0,
                        //                "size" => 116753,
                        //                 "cookie" => ["name" => "admin",
                        //                              "value" => "2dq6ffho7b0n480bu9m68san32",
                        //                              "lifetime" => "900000",
                        //                              "path" => "/admin",
                        //                              "domain" => "cong-cake-box.247vn.asia"
                        //                             ],
                        //                "url" => "https://cong-cake-box.247vn.asia/media/amasty/amlocator/tmp/859/".$img,
                        //                "previewType" => "image",
                        //                "id" => "UjMyOV8xNC5qcGc,"
                        //            ];
                        // }
                    // }
                    // $this->locationResource->saveGallery($data);
                    // var_dump($locationSchedule);die();
                    $location->addData($syncData);
                    $location->save();

                    // $gallery = $this->gallery->create();
                    // $gallery->setData($data)->save();
                    $this->messageManager->addSuccess(__('A total of %1 record(s) have been modified.', count($listResponse['response']['entities'])));
                // } catch (\Exception $e) {
                //     $this->logger->error($e->getMessage());
                //     $this->messageManager->addError(__('Something wrong').$e->getMessage());
                // }
            }
        }

        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        return $resultRedirect->setPath('amasty_storelocator/location/');
    }
}