<?php
/*** Copyright © Ulmod. All rights reserved. **/
 
namespace Ulmod\Productinquiry\Model\Config\Source;

use Magento\Framework\View\Model\PageLayout\Config\BuilderInterface;

class Layouts implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * @var BuilderInterface
     */
    protected $pageLayoutBuilder;
    
    /**
     * @var array|null
     */
    protected $options;

    /**
     * @param BuilderInterface $pageLayoutBuilder
     */
    public function __construct(
        BuilderInterface $pageLayoutBuilder
    ) {
        $this->pageLayoutBuilder = $pageLayoutBuilder;
    }

    /**
     * To option array
     *
     * @return array
     */
    public function toOptionArray()
    {
        if ($this->options === null) {
            $_options = $this->pageLayoutBuilder->getPageLayoutsConfig()->toOptionArray(true);
            foreach ($_options as $option) {
                $this->options[$option['value']] = $option['label'];
            }
        }
        $options = $this->options;
        return $options;
    }
}
