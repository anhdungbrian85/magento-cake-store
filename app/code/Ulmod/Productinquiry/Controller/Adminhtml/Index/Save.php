<?php
/*** Copyright © Ulmod. All rights reserved. **/
 
namespace Ulmod\Productinquiry\Controller\Adminhtml\Index;

use Magento\Backend\App\Action;
use Magento\TestFramework\ErrorLog\Logger;

class Save extends \Magento\Backend\App\Action
{
    /**
     * @see _isAllowed()
     */
    public const ADMIN_RESOURCE = 'Ulmod_Productinquiry::save';

    /**
     * @var \Ulmod\Productinquiry\Model\Upload
     */
    protected $uploadModel;
    
    /**
     * @var \Ulmod\Productinquiry\Model\Data\Image
     */
    protected $imageModel;

    /**
     * @var \Ulmod\Productinquiry\Model\DataFactory
     */
    protected $dataFactory;
    
    /**
     * @param Action\Context $context
     * @param \Ulmod\Productinquiry\Model\Data\Image $imageModel
     * @param \Ulmod\Productinquiry\Model\DataFactory $dataFactory
     * @param \Ulmod\Productinquiry\Model\Upload $uploadModel
     */
    public function __construct(
        Action\Context $context,
        \Ulmod\Productinquiry\Model\Data\Image $imageModel,
        \Ulmod\Productinquiry\Model\DataFactory $dataFactory,
        \Ulmod\Productinquiry\Model\Upload $uploadModel
    ) {
        $this->uploadModel = $uploadModel;
        $this->dataFactory = $dataFactory;
        $this->imageModel = $imageModel;
        parent::__construct($context);
    }

    /**
     * Save action
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        $data = $this->getRequest()->getPostValue();
        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultRedirectFactory->create();
        if ($data) {
            /** @var \Ulmod\Productinquiry\Model\Data $model */
            $model = $this->dataFactory->create();

            $id = $this->getRequest()->getParam('inquiry_id');
            if ($id) {
                $model->load($id);
            }

            // validate filename characters
            $filesRequest = $this->getRequest()->getFiles('image');
            if (isset($filesRequest) && isset($filesRequest['name'])) {
                $fileName = $this->getRequest()->getFiles('image')['name'];
                if (strlen($fileName) > 90) {
                    $this->messageManager->addError(
                        __('Your filename is too long; must be less than 90 characters')
                    );
                    return $resultRedirect->setPath(
                        '*/*/edit',
                        ['inquiry_id' => $this->getRequest()->getParam('inquiry_id')]
                    );
                }
            }
            
            $model->setData($data);

            $this->_eventManager->dispatch(
                'inquiry_prepare_save',
                ['inquiry' => $model, 'request' => $this->getRequest()]
            );

            $imageName = $this->uploadModel->uploadFileAndGetName(
                'image',
                $this->imageModel->getBaseDir(),
                $data
            );
            $model->setImage($imageName);

            try {
                $model->save();
                $this->messageManager->addSuccess(__('Inquiry has been saved.'));
                $this->_session->setFormData(false);
                if ($this->getRequest()->getParam('back')) {
                    return $resultRedirect->setPath(
                        '*/*/edit',
                        ['inquiry_id' => $model->getId(), '_current' => true]
                    );
                }
                return $resultRedirect->setPath('*/*/');
            } catch (\Magento\Framework\Exception\LocalizedException $e) {
                $this->messageManager->addError($e->getMessage());
            } catch (\RuntimeException $e) {
                $this->messageManager->addError($e->getMessage());
            } catch (\Exception $e) {
                $this->messageManager->addException(
                    $e,
                    __('Something went wrong while saving the inquiry.')
                );
            }

            $this->_getSession()->setFormData($data);
            return $resultRedirect->setPath(
                '*/*/edit',
                ['inquiry_id' => $this->getRequest()->getParam('inquiry_id')]
            );
        }
        return $resultRedirect->setPath('*/*/');
    }
}
