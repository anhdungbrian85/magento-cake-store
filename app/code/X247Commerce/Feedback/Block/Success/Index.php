<?php
namespace X247Commerce\Feedback\Block\Success;

class Index extends \Magento\Framework\View\Element\Template
{
    protected $_postFactory;
    protected $helperData;
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \X247Commerce\Feedback\Helper\Data $helperData,
        array $data = []
    ) {
        $this->helperData = $helperData;
        parent::__construct($context, $data);
    }

    protected function _prepareLayout()
    {
        parent::_prepareLayout();
        return $this;
    }
    public function getText()
    {
        return $this->helperData->getConfigValue('feedback/general/success_text');
    }
}
?>