<?php
/*** Copyright © Ulmod. All rights reserved. **/
 
namespace Ulmod\Productinquiry\Model;

use Ulmod\Productinquiry\Api\Data\DataInterface;
use Magento\Framework\DataObject\IdentityInterface;

class Data extends \Magento\Framework\Model\AbstractModel implements DataInterface, IdentityInterface
{
    public const STATUS_NEW = 1;
    public const STATUS_PROCESSING = 2;
    public const STATUS_COMPLETED = 3;
    public const CACHE_TAG = 'productinquiry_data';

    /**
     * @var string
     */
    protected $_cacheTag = 'productinquiry_data';

    /**
     * @var string
     */
    protected $_eventPrefix = 'productinquiry_data';

    /**
     * @inheritDoc
     */
    protected function _construct()
    {
        $this->_init(\Ulmod\Productinquiry\Model\ResourceModel\Data::class);
    }

    /**
     * Prepare productinquiry's statuses.
     *
     * @return array
     */
    public function getAvailableStatuses()
    {
        return [
        self::STATUS_NEW => __('New'),
        self::STATUS_PROCESSING => __('Processing'),
        self::STATUS_COMPLETED => __('Completed')
        ];
    }

    /**
     * Return unique ID(s) for each object in system
     *
     * @return array
     */
    public function getIdentities()
    {
        return [self::CACHE_TAG . '_' . $this->getInquiryId()];
    }

    /**
     * Get inquiry_id
     *
     * @return int
     */
    public function getInquiryId()
    {
        return $this->getData(self::INQUIRY_ID);
    }

    /**
     * Get status
     *
     * @return int
     */
    public function getStatus()
    {
        return $this->getData(self::STATUS);
    }

    /**
     * Get date
     *
     * @return string
     */
    public function getDate()
    {
        return $this->getData(self::DATE);
    }

    /**
     * Get name
     *
     * @return string
     */
    public function getName()
    {
        return $this->getData(self::NAME);
    }

    /**
     * Get email
     *
     * @return string
     */
    public function getEmail()
    {
        return $this->getData(self::EMAIL);
    }

    /**
     * Get message
     *
     * @return string
     */
    public function getMessage()
    {
        return $this->getData(self::MESSAGE);
    }

    /**
     * Get telephone
     *
     * @return string
     */
    public function getTelephone()
    {
        return $this->getData(self::TELEPHONE);
    }

    /**
     * Get subject
     *
     * @return string
     */
    public function getSubject()
    {
        return $this->getData(self::SUBJECT);
    }

    /**
     * Get image
     *
     * @return string
     */
    public function getImage()
    {
        return $this->getData(self::IMAGE);
    }

    /**
     * Set inquiry_id
     *
     * @param int $inquiryId
     * @return \Ulmod\Productinquiry\Api\Data\DataInterface
     */
    public function setInquiryId($inquiryId)
    {
        return $this->setData(self::TESTIMONIAL_ID, $inquiryId);
    }

    /**
     * Set status
     *
     * @param int $status
     * @return \Ulmod\Productinquiry\Api\Data\DataInterface
     */
    public function setStatus($status)
    {
        return $this->setData(self::STATUS, $status);
    }

    /**
     * Set date
     *
     * @param string $date
     * @return \Ulmod\Productinquiry\Api\Data\DataInterface
     */
    public function setDate($date)
    {
        return $this->setData(self::DATE, $date);
    }

    /**
     * Set name
     *
     * @param string $name
     * return \Ulmod\Productinquiry\Api\Data\DataInterface
     */
    public function setName($name)
    {
        return $this->setData(self::NAME, $name);
    }

    /**
     * Set email
     *
     * @param string $email
     * @return \Ulmod\Productinquiry\Api\Data\DataInterface
     */
    public function setEmail($email)
    {
        return $this->setData(self::EMAIL, $email);
    }

    /**
     * Set message
     *
     * @param string $message
     * @return \Ulmod\Productinquiry\Api\Data\DataInterface
     */
    public function setMessage($message)
    {
        return $this->setData(self::MESSAGE, $message);
    }

    /**
     * Set telephone
     *
     * @param string $telephone
     * @return \Ulmod\Productinquiry\Api\Data\DataInterface
     */
    public function setTelephone($telephone)
    {
        return $this->setData(self::TELEPHONE, $telephone);
    }

    /**
     * Set subject
     *
     * @param string $subject
     * @return \Ulmod\Productinquiry\Api\Data\DataInterface
     */
    public function setSubject($subject)
    {
        return $this->setData(self::SUBJECT, $subject);
    }

    /**
     * Set image
     *
     * @param string $image
     * @return \Ulmod\Productinquiry\Api\Data\DataInterface
     */
    public function setImage($image)
    {
        return $this->setData(self::IMAGE, $image);
    }

    /**
     * Receive page store ids
     *
     * @return int[]
     */
    public function getStores()
    {
        return $this->hasData('stores') ? $this->getData('stores') : $this->getData('store_id');
    }
}
