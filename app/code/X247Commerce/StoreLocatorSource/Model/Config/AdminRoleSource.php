<?php
namespace X247Commerce\StoreLocatorSource\Model\Config;

class AdminRoleSource implements \Magento\Framework\Data\OptionSourceInterface
{  
  protected $roleCollectionFactory;

  public function __construct(
    \Magento\Authorization\Model\ResourceModel\Role\Grid\CollectionFactory $roleCollectionFactory,
  ) {
    $this->roleCollectionFactory = $roleCollectionFactory;
  }

  public function toOptionArray()
  {
    $roles = $this->roleCollectionFactory->create()->getData();
    $data = [];
    foreach ($roles as $role) {
      $data[] = ['value' => $role['role_id'], 'label' => $role['role_name']];
    }

    return $data;
  }
}