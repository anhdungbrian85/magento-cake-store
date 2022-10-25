<?php
namespace X247Commerce\StoreLocatorSource\Model\ResourceModel;

use Magento\Framework\App\ResourceConnection;

class LocatorSourceResolver
{
    protected $resource;
    protected $connection;

    public function __construct(
        ResourceConnection $resource
    )
    {        
        $this->resource = $resource;
        $this->connection = $resource->getConnection();
    }

    public function getAmLocatorBySource($sourceCode)
    {
        $sourceTbl = $this->resource->getTableName('inventory_source');
        $sqlQuery = $this->connection->select()
                ->from($sourceTbl, ['amlocator_store'])
                ->where("source_code = ?", $sourceCode);
        return $this->connection->fetchOne($sqlQuery);
    }
}