<?php
 
namespace X247Commerce\Yext\Console;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Amasty\Storelocator\Model\ResourceModel\Location\CollectionFactory as LocationCollectionFactory;
use Magento\Framework\App\ResourceConnection;
use Psr\Log\LoggerInterface;
use Magento\Framework\App\State;
use Magento\User\Model\UserFactory;
use Magento\User\Model\ResourceModel\User\CollectionFactory as UserCollectionFactory;
use X247Commerce\StoreLocatorSource\Model\ResourceModel\LocatorSourceResolver;
use X247Commerce\StoreLocatorSource\Helper\User as X247UserHelper;

class ChangeAdminUsername extends Command
{
    protected $searchCriteriaBuilder;
    protected $locationCollectionFactory;
    protected $resource;
    protected $connection;
    protected $logger;
    protected $state;    
    protected $userFactory;
    protected $userCollectionFactory;
    protected $locatorSourceResolver;
    protected $userHelper;

    public function __construct(
        SearchCriteriaBuilder $searchCriteriaBuilder,
        LocationCollectionFactory $locationCollectionFactory,
        ResourceConnection $resource,
        LoggerInterface $logger,
        UserFactory $userFactory,
        UserCollectionFactory $userCollectionFactory,
        State $state,
        LocatorSourceResolver $locatorSourceResolver,
        X247UserHelper $userHelper
    ) {
        parent::__construct();
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->locationCollectionFactory = $locationCollectionFactory;
        $this->resource = $resource;
        $this->logger = $logger;
        $this->connection = $resource->getConnection();
        $this->state = $state;
        $this->userFactory = $userFactory;
        $this->userCollectionFactory = $userCollectionFactory;
        $this->locatorSourceResolver = $locatorSourceResolver;
        $this->userHelper = $userHelper;
    }


    protected function configure()
    {
        $this->setName('yext:changeadminusername')
             ->setDescription("Change admin's username to admin's email");

        parent::configure();
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln('This process might take long time, please wait!');
        $this->process();
    }

    public function process()
    {
        $tableName = $this->connection->getTableName('admin_user');
        $data = ['username' => 'email'];
        $like = "firstname like '%Cake Box%'";

        foreach ($this->_prepareCollection() as $user) {
            
            $where = $like." and user_id = ".$user->getId();
            $select = $this->connection->select()
                ->from(
                    false,
                    $data
                )
                ->where($where);
            $updateSelect = $this->connection->updateFromSelect(
                $select,
                $tableName
            );
            $this->connection->query($updateSelect);            
        }
    }
    /**
     * @return $this
     */
    protected function _prepareCollection()
    {
        $adminUsers = $this->userCollectionFactory->create()->addFieldToFilter("detail_role.role_id", ['eq' => $this->userHelper->getStaffRole()]);

        return $adminUsers;
    }
}