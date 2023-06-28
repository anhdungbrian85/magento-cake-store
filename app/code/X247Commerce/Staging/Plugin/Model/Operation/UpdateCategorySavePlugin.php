<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace X247Commerce\Staging\Plugin\Model\Operation;

class UpdateCategorySavePlugin
{
    protected $request;
    public function __construct(
        \Magento\Framework\App\RequestInterface $request
    ) {
        $this->request = $request;
    }
    public function beforeExecute($subject, $entity, $arguments = [])
    {
        $categoryPostData = $this->request->getPostValue();
        if (isset($categoryPostData['entity_id'])) {
            $_objectManager = \Magento\Framework\App\ObjectManager::getInstance();
            $object_manager = $_objectManager->create('Magento\Catalog\Model\Category')->load($categoryPostData['entity_id']);
            $entity->updated_in = $object_manager->getUpdatedIn();
            $entity->setData('updated_in', $object_manager->getUpdatedIn());
        }
        return [$entity, $arguments];
    }
}