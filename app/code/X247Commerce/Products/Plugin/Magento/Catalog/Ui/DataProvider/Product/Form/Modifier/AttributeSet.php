<?php

namespace X247Commerce\Products\Plugin\Magento\Catalog\Ui\DataProvider\Product\Form\Modifier;

class AttributeSet
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
        if (isset($result['attribute_set_id']['arguments']['data']['config'])) {
            $result['attribute_set_id']['arguments']['data']['config']['visible'] = false;
        }

        return $result;
    }
}
