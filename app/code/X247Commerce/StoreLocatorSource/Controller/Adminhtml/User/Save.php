<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace X247Commerce\StoreLocatorSource\Controller\Adminhtml\User;

use Magento\Framework\App\Action\HttpPostActionInterface as HttpPostActionInterface;
use Magento\Framework\Exception\AuthenticationException;
use Magento\Framework\Exception\State\UserLockedException;
use Magento\Security\Model\SecurityCookie;
use Magento\User\Model\Spi\NotificationExceptionInterface;

/**
 * Save admin user.
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Save extends \Magento\User\Controller\Adminhtml\User implements HttpPostActionInterface
{
    /**
     * @var SecurityCookie
     */
    private $securityCookie;

    /**
     * Get security cookie
     *
     * @return SecurityCookie
     * @deprecated 100.1.0
     */

    protected $adminSourceFactory;

    protected $adminSourceCollection;

    /**
     * Core registry
     *
     * @var \Magento\Framework\Registry
     */
    // protected $_coreRegistry;

    /**
     * User model factory
     *
     * @var \Magento\User\Model\UserFactory
     */
    // protected $_userFactory;

    public function __construct(        
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\Registry $coreRegistry,
        \Magento\User\Model\UserFactory $userFactory,
        \X247Commerce\StoreLocatorSource\Model\AdminSourceFactory $adminSourceFactory,
        \X247Commerce\StoreLocatorSource\Model\ResourceModel\AdminSource\CollectionFactory $adminSourceCollection
    ) {
        $this->adminSourceFactory = $adminSourceFactory;
        $this->adminSourceCollection = $adminSourceCollection;
        
        parent::__construct($context, $coreRegistry, $userFactory);
    }

    private function getSecurityCookie()
    {
        if (!($this->securityCookie instanceof SecurityCookie)) {
            return \Magento\Framework\App\ObjectManager::getInstance()->get(SecurityCookie::class);
        } else {
            return $this->securityCookie;
        }
    }

    /**
     * @inheritDoc
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function execute()
    {
        $userId = (int)$this->getRequest()->getParam('user_id');
        $data = $this->getRequest()->getPostValue();
        if (array_key_exists('form_key', $data)) {
            unset($data['form_key']);
        }
        if (!$data) {
            $this->_redirect('adminhtml/*/');
            return;
        }

        /** @var $model \Magento\User\Model\User */
        $model = $this->_userFactory->create()->load($userId);
        if ($userId && $model->isObjectNew()) {
            $this->messageManager->addError(__('This user no longer exists.'));
            $this->_redirect('adminhtml/*/');
            return;
        }
        $model->setData($this->_getAdminUserData($data));
        $userRoles = $this->getRequest()->getParam('roles', []);
        if (count($userRoles)) {
            $model->setRoleId($userRoles[0]);
        }

        /** @var $currentUser \Magento\User\Model\User */
        $currentUser = $this->_objectManager->get(\Magento\Backend\Model\Auth\Session::class)->getUser();
        if ($userId == $currentUser->getId()
            && $this->_objectManager->get(\Magento\Framework\Validator\Locale::class)
                ->isValid($data['interface_locale'])
        ) {
            $this->_objectManager->get(
                \Magento\Backend\Model\Locale\Manager::class
            )->switchBackendInterfaceLocale(
                $data['interface_locale']
            );
        }

        /** Before updating admin user data, ensure that password of current admin user is entered and is correct */
        $currentUserPasswordField = \Magento\User\Block\User\Edit\Tab\Main::CURRENT_USER_PASSWORD_FIELD;
        $isCurrentUserPasswordValid = isset($data[$currentUserPasswordField])
            && !empty($data[$currentUserPasswordField]) && is_string($data[$currentUserPasswordField]);
        try {
            if (!($isCurrentUserPasswordValid)) {
                throw new AuthenticationException(
                    __('The password entered for the current user is invalid. Verify the password and try again.')
                );
            }
            $currentUser->performIdentityCheck($data[$currentUserPasswordField]);
            $model->save();

            // edit admin_user_source_link table
            $this->saveAdminSourceLink($model, $data);

            $this->messageManager->addSuccess(__('You saved the user.'));
            $this->_getSession()->setUserData(false);
            $this->_redirect('adminhtml/*/');

            $model->sendNotificationEmailsIfRequired();
        } catch (UserLockedException $e) {
            $this->_auth->logout();
            $this->getSecurityCookie()->setLogoutReasonCookie(
                \Magento\Security\Model\AdminSessionsManager::LOGOUT_REASON_USER_LOCKED
            );
            $this->_redirect('*');
        } catch (NotificationExceptionInterface $exception) {
            $this->messageManager->addErrorMessage($exception->getMessage());
        } catch (\Magento\Framework\Exception\AuthenticationException $e) {
            $this->messageManager->addError(
                __('The password entered for the current user is invalid. Verify the password and try again.')
            );
            $this->redirectToEdit($model, $data);
        } catch (\Magento\Framework\Validator\Exception $e) {
            $messages = $e->getMessages();
            $this->messageManager->addMessages($messages);
            $this->redirectToEdit($model, $data);
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            if ($e->getMessage()) {
                $this->messageManager->addError($e->getMessage());
            }
            $this->redirectToEdit($model, $data);
        }
    }

    /**
     * Redirect to Edit form.
     *
     * @param \Magento\User\Model\User $model
     * @param array $data
     * @return void
     */
    protected function redirectToEdit(\Magento\User\Model\User $model, array $data)
    {
        $this->_getSession()->setUserData($data);
        $arguments = $model->getId() ? ['user_id' => $model->getId()] : [];
        $arguments = array_merge($arguments, ['_current' => true, 'active_tab' => '']);
        $this->_redirect('adminhtml/*/edit', $arguments);
    }


   public function getLinkCollection($userId)
   {
       // $collection = $this->adminSourceCollection->create()
       //   ->addAttributeToSelect('*')
       //   ->addFieldToFilter('user_id', ['eq' => $userId]);

        $collection = $this->adminSourceFactory->create()->getCollection()->addFieldToFilter('user_id', array(
            'eq' => $userId
        ));
        $data = [];
        foreach ($collection as $item) {
            $data[] = $item->getEntityId();
        }
     
        return $data;
     
    }

    public function saveAdminSourceLink($model, $data)
    {
        $userId = $model->getUserId();
        $collectionLinkId = $this->getLinkCollection($userId);
        $adminSourceLink = $this->adminSourceFactory->create();

        if ($collectionLinkId) {
            foreach ($collectionLinkId as $linkId) {
                $modelLink = $adminSourceLink->load($linkId);
                $modelLink->delete();
            }
        }

        if (isset($data['source'])) {
            foreach ($data['source'] as $source_code) {
                $dataSave = [];
                $dataSave['user_id'] = $userId;
                $dataSave['source_code'] = $source_code;

                $adminSourceLink->setData($dataSave);
                $adminSourceLink->save();
            }
        }
    }
}
