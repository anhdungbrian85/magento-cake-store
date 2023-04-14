<?php

namespace X247Commerce\Products\Plugin\Klevu\Search\Ui\DataProvider\Product\Form\Modifier\Attributes;

class ReviewCount
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
        if (isset($result['related']['arguments']['data']['config'])) {
            $result['related']['arguments']['data']['config']['visible'] = false;
        }
        if (isset($result['custom_options']['arguments']['data']['config'])) {
            $result['custom_options']['arguments']['data']['config']['visible'] = false;
        }
        if (isset($result['websites']['arguments']['data']['config'])) {
            $result['websites']['arguments']['data']['config']['visible'] = false;
        }
        if (isset($result['design']['arguments']['data']['config'])) {
            $result['design']['arguments']['data']['config']['visible'] = false;
        }
        if (isset($result['search-engine-optimization']['arguments']['data']['config'])) {
            $result['search-engine-optimization']['arguments']['data']['config']['visible'] = false;
        }
        if (isset($result['category_show']['arguments']['data']['config'])) {
            $result['category_show']['arguments']['data']['config']['visible'] = false;
        }
        if (isset($result['attributes']['arguments']['data']['config'])) {
            $result['attributes']['arguments']['data']['config']['visible'] = false;
        }
        if (isset($result['wesupply-options']['arguments']['data']['config'])) {
            $result['wesupply-options']['arguments']['data']['config']['visible'] = false;
        }
        if (isset($result['product-inquiry-by-ulmod']['arguments']['data']['config'])) {
            $result['product-inquiry-by-ulmod']['arguments']['data']['config']['visible'] = false;
        }
        if (isset($result['mollie']['arguments']['data']['config'])) {
            $result['mollie']['arguments']['data']['config']['visible'] = false;
        }
        return $result;
    }
}
