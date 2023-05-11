<?php

namespace X247Commerce\Products\Plugin\Magento\GiftMessage\Ui\DataProvider\Product\Modifier;

class GiftMessage
{
    protected $request;

    protected $adminSession;

    public function __construct(
        \Magento\Framework\App\Request\Http $request,
        \Magento\Backend\Model\Auth\Session $adminSession
    ) {
        $this->request = $request;
        $this->adminSession = $adminSession;
    }

    /**
     * @param $subject
     * @param $result
     * @return mixed
     */
    public function afterModifyMeta($subject, $result)
    {
        $roleData = $this->adminSession->getUser()->getRole()->getData();
        $roleId = (int) $roleData['role_id'];
        if ($roleId == 1 || $this->request->getParam('id') == NULL) {
            return $result;
        }

        if (isset($result['gift-options']['arguments']['data']['config'])) {
            $result['gift-options']['arguments']['data']['config']['visible'] = false;
        }
        return $result;
    }
}
