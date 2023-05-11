<?php

namespace X247Commerce\Products\Plugin\Magento\CatalogStaging\Ui\DataProvider\Product\Form\Modifier;

class Eav
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

        if (isset($result['product-details']['arguments']['data']['config'])) {
            $result['product-details']['arguments']['data']['config']['visible'] = false;
        }

        return $result;
    }
}
