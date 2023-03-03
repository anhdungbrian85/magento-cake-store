<?php
/*** Copyright © Ulmod. All rights reserved. **/
 
namespace Ulmod\Productinquiry\Api\Data;

interface DataInterface
{
    public const INQUIRY_ID = 'inquiry_id';
    public const STATUS = 'status';
    public const DATE = 'date';
    public const NAME = 'name';
    public const EMAIL = 'email';
    public const MESSAGE = 'message';
    public const SUBJECT = 'subject';
    public const TELEPHONE = 'telephone';
    public const IMAGE = 'image';

    /**
     * Get inquiry_id
     *
     * @return int
     */
    public function getInquiryId();

    /**
     * Get status
     *
     * @return int
     */
    public function getStatus();

    /**
     * Get date
     *
     * @return string
     */
    public function getDate();

    /**
     * Get name
     *
     * @return string
     */
    public function getName();

    /**
     * Get email
     *
     * @return string
     */
    public function getEmail();

    /**
     * Get message
     *
     * @return string
     */
    public function getMessage();

    /**
     * Get subject
     *
     * @return string
     */
    public function getSubject();

    /**
     * Get telephone
     *
     * @return string
     */
    public function getTelephone();

    /**
     * Get image
     *
     * @return string
     */
    public function getImage();

    /**
     * Set inquiry_id
     *
     * @param int $inquiryId
     * @return \Ulmod\Productinquiry\Api\Data\DataInterface
     */
    public function setInquiryId($inquiryId);

    /**
     * Set status
     *
     * @param int $status
     * @return \Ulmod\Productinquiry\Api\Data\DataInterface
     */
    public function setStatus($status);

    /**
     * Set date
     *
     * @param string $date
     * @return \Ulmod\Productinquiry\Api\Data\DataInterface
     */
    public function setDate($date);

    /**
     * Set name
     *
     * @param string $name
     * @return \Ulmod\Productinquiry\Api\Data\DataInterface
     */
    public function setName($name);

    /**
     * Set email
     *
     * @param string $email
     * @return \Ulmod\Productinquiry\Api\Data\DataInterface
     */
    public function setEmail($email);

    /**
     * Set message
     *
     * @param string $message
     * @return \Ulmod\Productinquiry\Api\Data\DataInterface
     */
    public function setMessage($message);

    /**
     * Set subject
     *
     * @param string $subject
     * @return \Ulmod\Productinquiry\Api\Data\DataInterface
     */
    public function setSubject($subject);

    /**
     * Set telephone
     *
     * @param string $telephone
     * @return \Ulmod\Productinquiry\Api\Data\DataInterface
     */
    public function setTelephone($telephone);

    /**
     * Set image
     *
     * @param string $image
     * @return \Ulmod\Productinquiry\Api\Data\DataInterface
     */
    public function setImage($image);
}
