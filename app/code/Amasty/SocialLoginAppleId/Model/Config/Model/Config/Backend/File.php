<?php

namespace Amasty\SocialLoginAppleId\Model\Config\Model\Config\Backend;

class File extends \Magento\Config\Model\Config\Backend\File
{
    /**
     * @return $this|\Magento\Config\Model\Config\Backend\File
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function beforeSave()
    {
        parent::beforeSave();
        $this->_registry->register('amsocial_apple_key', $this->getValue());
        $this->setValue('');

        return $this;
    }

    /**
     * Getter for allowed extensions of uploaded files
     *
     * @return string[]
     */
    protected function _getAllowedExtensions()
    {
        return ['p8'];
    }
}
