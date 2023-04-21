<?php
/**
 * Mageplaza
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Mageplaza.com license that is
 * available through the world-wide-web at this URL:
 * https://www.mageplaza.com/LICENSE.txt
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 *
 * @category    Mageplaza
 * @package     Mageplaza_ProductFeed
 * @copyright   Copyright (c) Mageplaza (https://www.mageplaza.com/)
 * @license     https://www.mageplaza.com/LICENSE.txt
 */
declare(strict_types=1);

namespace Mageplaza\ProductFeed\Setup\Patch\Data;

use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;

/**
 * Patch is mechanism, that allows to do atomic upgrade data changes
 *
 * Class UpdateTemplate
 * @package Mageplaza\ProductFeed\Setup\Patch\Data
 */
class UpdateTemplate implements DataPatchInterface
{
    /**
     * @var ModuleDataSetupInterface $moduleDataSetup
     */
    private $moduleDataSetup;

    /**
     * @param ModuleDataSetupInterface $moduleDataSetup
     */
    public function __construct(ModuleDataSetupInterface $moduleDataSetup)
    {
        $this->moduleDataSetup = $moduleDataSetup;
    }

    /**
     * Do Upgrade
     *
     * @return void
     */
    public function apply()
    {
        $templateHtml = '<?xml version="1.0"?>
<AmazonEnvelope xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:noNamespaceSchemaLocation="amzn-envelope.xsd">
  <Header>
    <DocumentVersion>1.01</DocumentVersion>
    <MerchantIdentifier>M_SELLER_123456</MerchantIdentifier>
  </Header>
  <MessageType>Product</MessageType>
  <PurgeAndReplace>false</PurgeAndReplace>
  <Message>
    {% for product in products %}
    <MessageID>1</MessageID>
    <OperationType>Update</OperationType>
    <Product>
      <SKU><![CDATA[{{ product.sku }}]]></SKU>
      <ProductTaxCode>A_GEN_TAX</ProductTaxCode>
      <LaunchDate>2014-04-22T04:00:00</LaunchDate>
      <Condition>
        <ConditionType>New</ConditionType>
      </Condition>
      <DescriptionData>
        <Title><![CDATA[{{ product.name}}]]></Title>
        <Brand><![CDATA[{{ product.manufacturer | ifEmpty: \'DefaultBrand\' }}]]></Brand>
        <Description>{{ product.description | strip_html }}</Description>
        <BulletPoint>Clothes</BulletPoint>
        <ItemDimensions>
          <Weight unitOfMeasure="LB">{{ product.weight | ceil }}</Weight>
        </ItemDimensions>
        <MSRP currency="CAD">{{ product.price | price }}</MSRP>
        <Manufacturer><![CDATA[{{ product.manufacturer | ifEmpty: \'DefaultBrand\' }}]]></Manufacturer>
        <SearchTerms><![CDATA[{{ product.meta_keyword }}]]></SearchTerms>
        <ItemType>handmade-rugs</ItemType>
        <OtherItemAttributes>Rectangular</OtherItemAttributes>
        <TargetAudience>Adults</TargetAudience>
        <TargetAudience>Children</TargetAudience>
        <TargetAudience>Men</TargetAudience>
        <TargetAudience>Women</TargetAudience>
      </DescriptionData>
      <ProductData>
      </ProductData>
    </Product>
    {% endfor %}
  </Message>
</AmazonEnvelope>';

        $this->moduleDataSetup->getConnection()->update(
            $this->moduleDataSetup->getTable('mageplaza_productfeed_defaulttemplate'),
            ['template_html' => $templateHtml],
            ['name LIKE ?' => 'amazon_marketplace_xml']
        );
    }

    /**
     * @inheritdoc
     */
    public function getAliases()
    {
        return [];
    }

    /**
     * @inheritdoc
     */
    public static function getDependencies()
    {
        return [];
    }

    /**
     * @return string
     */
    public static function getVersion()
    {
        return '1.0.8';
    }
}
