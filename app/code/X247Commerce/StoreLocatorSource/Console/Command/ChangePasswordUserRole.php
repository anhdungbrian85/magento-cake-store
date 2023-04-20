<?php
 
namespace X247Commerce\StoreLocatorSource\Console\Command;

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
use Magento\Framework\App\DeploymentConfig;

class ChangePasswordUserRole extends Command
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
    protected $deploymentConfig;

    public function __construct(
        SearchCriteriaBuilder $searchCriteriaBuilder,
        LocationCollectionFactory $locationCollectionFactory,
        ResourceConnection $resource,
        LoggerInterface $logger,
        UserFactory $userFactory,
        UserCollectionFactory $userCollectionFactory,
        State $state,
        LocatorSourceResolver $locatorSourceResolver,
        X247UserHelper $userHelper,
        DeploymentConfig $deploymentConfig
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
        $this->deploymentConfig = $deploymentConfig;
    }


    protected function configure()
    {
        $this->setName('x247commerce:changepassworduserrole')
             ->setDescription("Change password user by role");

        parent::configure();
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln('This process might take long time, please wait!');
        $this->process($output);
        $output->writeln('Change password success!');
    }

    public function process($output)
    {
        $tableName = $this->connection->getTableName('admin_user');
        $envCrypt = $this->deploymentConfig->get('crypt/key');

        $data = ['password' => "CONCAT(SHA2('".$envCrypt."Cake123', 256), ':".$envCrypt.":1')"];

        foreach ($this->_prepareCollection() as $user) {
            try {

                $where = "user_id = ".$user->getId();
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
                $output->writeln('Change password for '.$user->getEmail()); 
            } catch(\Exception $err)
            {
                
            }
        }
    }
    /**
     * @return $this
     */
    protected function _prepareCollection()
    {
        return $this->userCollectionFactory->create()->addFieldToFilter("detail_role.role_id", ['eq' => $this->userHelper->getStaffRole()]);
    }
}