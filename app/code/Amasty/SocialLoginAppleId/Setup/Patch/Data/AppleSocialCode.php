<?php

declare(strict_types=1);

namespace Amasty\SocialLoginAppleId\Setup\Patch\Data;

use Amasty\SocialLogin\Model\ResourceModel\SocialList as SocialListResource;
use Amasty\SocialLogin\Model\SocialList;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Setup\Patch\DataPatchInterface;

class AppleSocialCode implements DataPatchInterface
{
    /**
     * @var ResourceConnection
     */
    private $resource;

    public function __construct(ResourceConnection $resource)
    {
        $this->resource = $resource;
    }

    public static function getDependencies(): array
    {
        return [\Amasty\SocialLogin\Setup\Patch\Data\SocialCode::class];
    }

    public function getAliases(): array
    {
        return [];
    }

    public function apply(): void
    {
        $connection = $this->resource->getConnection();
        $tableName = $this->resource->getTableName(SocialListResource::MAIN_TABLE);
        $connection->insertOnDuplicate($tableName, [SocialListResource::KEY_CODE => SocialList::TYPE_APPLE]);
    }
}
