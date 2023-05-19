<?php

namespace X247Commerce\Products\Plugin\Magento\Catalog\Ui\DataProvider\Product\Form\Modifier;

class General
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
        if (isset($result['content']['arguments']['data']['config'])) {
            $result['content']['arguments']['data']['config']['visible'] = false;
        }

        return $result;
    }
}
