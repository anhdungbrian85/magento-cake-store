<?php
/*** Copyright © Ulmod. All rights reserved. **/

namespace Ulmod\Productinquiry\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\App\Request\DataPersistorInterface;
use Magento\Framework\App\ObjectManager;
use Magento\Captcha\Observer\CaptchaStringResolver;
use Magento\Captcha\Helper\Data as HelperData;
use Magento\Framework\App\ActionFlag;
use Magento\Framework\Message\ManagerInterface;
use Magento\Framework\App\Response\RedirectInterface;
use Ulmod\Productinquiry\Model\ConfigData;
use Magento\Framework\App\ActionInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Controller\Result\Forward;
use Magento\Framework\Controller\Result\ForwardFactory;
use Magento\Framework\Event\Observer;
use Magento\Framework\Exception\NotFoundException;
use Magento\Framework\App\Action\Action as AppAction;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\HTTP\Client\CurlFactory;
use Magento\Framework\Serialize\Serializer\Json;
use Psr\Log\LoggerInterface;
    
class ValidateSpamBlocker implements ObserverInterface
{
    /**
     * @var HelperData
     */
    protected $_helper;

    /**
     * @var ActionFlag
     */
    protected $_actionFlag;

    /**
     * @var ManagerInterface
     */
    protected $messageManager;

    /**
     * @var RedirectInterface
     */
    protected $redirect;

    /**
     * @var CaptchaStringResolver
     */
    protected $captchaStringResolver;

    /**
     * @var DataPersistorInterface
     */
    private $dataPersistor;

    /**
     * @var ConfigData
     */
    protected $configData;

    /**
     * @var ForwardFactory
     */
    protected $forwardFactory;

    /**
     * @var ResponseInterface
     */
    protected $response;

    /**
     * @var CurlFactory
     */
    private $curlFactory;

    /**
     * @var Json
     */
    private $serializer;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var array
     */
    private $formActions = [
        'productinquiry_index_save/' => 'um_productinquiry_form'
    ];

    /**
     * @param HelperData $helper
     * @param ActionFlag $actionFlag
     * @param ManagerInterface $messageManager
     * @param RedirectInterface $redirect
     * @param CaptchaStringResolver $captchaStringResolver
     * @param ConfigData $configData
     * @param ForwardFactory $forwardFactory
     * @param ResponseInterface $response
     * @param LoggerInterface $logger
     * @param CurlFactory $curlFactory
     * @param Json $serializer
     */
    public function __construct(
        HelperData $helper,
        ActionFlag $actionFlag,
        ManagerInterface $messageManager,
        RedirectInterface $redirect,
        CaptchaStringResolver $captchaStringResolver,
        ConfigData $configData,
        ForwardFactory $forwardFactory,
        ResponseInterface $response,
        LoggerInterface $logger,
        CurlFactory $curlFactory,
        Json $serializer
    ) {
        $this->_helper = $helper;
        $this->_actionFlag = $actionFlag;
        $this->messageManager = $messageManager;
        $this->redirect = $redirect;
        $this->captchaStringResolver = $captchaStringResolver;
        $this->configData = $configData;
        $this->forwardFactory = $forwardFactory;
        $this->response = $response;
        $this->logger = $logger;
        $this->curlFactory = $curlFactory;
        $this->serializer = $serializer;
    }

    /**
     * Validate
     *
     * @param Observer $observer
     * @return void
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        // validate Magento Captcha
        $this->validateMagentoCaptcha($observer);
        
        // validate Google reCAPTCHA V2 checkbox
        $this->validateRecaptchaCheckbox($observer);
    
        // validate Honeypot
        $this->validateHoneypot($observer);
    }

    /**
     * Validate Magento CAPTCHA
     *
     * @param Observer $observer
     * @return void
     */
    public function validateMagentoCaptcha($observer)
    {
        if ($this->configData->isMagentoCaptchaEnabled()) {
            $formId = 'um_productinquiry_form';

            $captcha = $this->_helper->getCaptcha($formId);
            if ($captcha->isRequired() && $this->configData->isMagentoCaptchaEnabled()) {
                /** @var \Magento\Framework\App\Action\Action $controller */
                $controller = $observer->getControllerAction();
                $cRequest = $controller->getRequest();
                $postValue = $controller->getRequest()->getPostValue();
                if (!$captcha->isCorrect($this->captchaStringResolver->resolve($cRequest, $formId))) {
                    $this->messageManager->addError(__('Incorrect CAPTCHA.'));
                    $this->getDataPersistor()->set($formId, $postValue);
                    $this->_actionFlag->set('', AppAction::FLAG_NO_DISPATCH, true);
                    $refererUrl = $this->redirect->getRefererUrl();
                    $this->redirect->redirect($controller->getResponse(), $refererUrl);
                }
            }
        }
    }
    
    /**
     * Get Data Persistor
     *
     * @return DataPersistorInterface
     */
    private function getDataPersistor()
    {
        if ($this->dataPersistor === null) {
            $this->dataPersistor = ObjectManager::getInstance()
                ->get(DataPersistorInterface::class);
        }
        return $this->dataPersistor;
    }

    /**
     * Validate Honeypot
     *
     * @param Observer $observer
     * @return void
     */
    public function validateHoneypot($observer)
    {
        if ($this->configData->isHoneypotEnabled()) {
            /** @var RequestInterface $request */
            $request = $observer->getEvent()->getData('request');
            if (!$this->isHoneypotRequest($request)) {
                $this->messageManager->getMessages(true);
                $this->messageManager->addErrorMessage(
                    $this->configData->getHoneypotNotAllowedMessage()
                );
                $this->_actionFlag->set('', Action::FLAG_NO_DISPATCH, true);
                return $this->response->setRedirect($this->redirect->getRefererUrl());
            }
        }
    }

    /**
     * Check if the honeypot field is present
     *
     * In request and that field is empty.
     *
     * @param RequestInterface $request
     * @return bool
     */
    private function isHoneypotRequest(RequestInterface $request)
    {
        $field = 'ulmod_honeypot';
        $params = $request->getParams();
        $notEmpty = new \Magento\Framework\Validator\NotEmpty();

        if (!isset($params[$field])
            || $notEmpty->isValid(trim($request->getParam($field)))
        ) {
            return false;
        }
        return true;
    }
    
    /**
     * Validate Recaptcha Checkbox
     *
     * @param Observer $observer
     * @return void
     */
    public function validateRecaptchaCheckbox($observer)
    {
        /** @var AppAction $controllerAction */
        $controllerAction = $observer->getEvent()->getControllerAction();
        
        $eventRequest = $observer->getEvent()->getRequest();

        $isExtensionEnabled = $this->configData->isExtensionEnabled();
        $isRecaptchaV2CheckEnabled = $this->configData->isRecaptchaV2CheckboxEnabled();
        if ($isExtensionEnabled && $isRecaptchaV2CheckEnabled) {
            $actionName = strtolower($eventRequest->getFullActionName());
            $formName = isset($this->formActions[$actionName])
                ? $this->formActions[$actionName] : false;
            
            $formsAllowed = [
                'um_productinquiry_form'
            ];
            if (!$formName || !in_array($formName, $formsAllowed)) {
                return;
            }

            $token = $eventRequest->getParam('g-recaptcha-response');
            if (!$this->verifyToken($token)) {
                $this->messageManager->addErrorMessage(
                    __('Incorrect reCAPTCHA. Please verify reCAPTCHA')
                );
                
                $redirectUrl = $this->redirect->getRefererUrl();
                $controllerAction->getResponse()->setRedirect($redirectUrl)->sendResponse();
                return;
            }
        }
    }

    /**
     * Verify reCAPTCHA token
     *
     * @param string $token
     * @return bool
     */
    public function verifyToken($token)
    {
        $result = $this->siteVerify($token);

        return isset($result['success']) ? $result['success'] : false;
    }

    /**
     * Send a reCAPTCHA request to verify the token
     *
     * @param string $token
     * @return bool|mixed|string
     */
    public function siteVerify($token)
    {
        $parameters = [
            'secret' => $this->configData->getRecaptchaSecretKey(),
            'response' => $token
        ];
        $curl = $this->curlFactory->create();

        try {
            $curl->post(ConfigData::GOOGLE_VERIFY_URL, $parameters);
            $response = $this->serializer->unserialize($curl->getBody());

            if ($curl->getStatus() === 200) {
                return $response['success'];
            } else {
                $this->logger->error(
                    'Error when validating reCAPTCHA.',
                    ['response' => var_export($response, true)
                    ]
                );
            }
        } catch (\Exception $e) {
            $this->logger->critical($e->getMessage(), ['exception' => $e]);
        }

        return true;
    }
}
