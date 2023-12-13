<?php
namespace X247Commerce\HolidayOpeningTime\Console;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\File\Csv as CsvReader;
use Magento\Store\Model\StoreManagerInterface;
use X247Commerce\Yext\Model\YextAttribute;

class AutoDisableDeliveryCollection extends Command
{

    private $logger;

    protected $connection;

    protected $resource;

    private $holidayCollection;

    public function __construct(
        \Magento\Framework\App\ResourceConnection $resource,
        \Psr\Log\LoggerInterface $logger,
        \X247Commerce\HolidayOpeningTime\Model\ResourceModel\StoreLocationHoliday\CollectionFactory $holidayCollection
    ) {
        parent::__construct();
        $this->logger = $logger;
        $this->connection = $resource->getConnection();
        $this->resource = $resource;
        $this->holidayCollection = $holidayCollection;
    }

    protected function configure()
    {
        $this->setName('x247commerce:holiday:auto-disable-shipping')
            ->setDescription('Auto disable delivery, collection on holiday');
        $this->addOption(
            'store-id',
            '-s',
            InputOption::VALUE_OPTIONAL,
            'for specific store locations'
        );
        parent::configure();
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $holidayTbl = $this->resource->getTableName('store_location_holiday');

        $holidayCollection = $this->holidayCollection->create();
        $storeId = $input->getOption('store-id');
        if ($storeId) {
            $holidayCollection->addFieldToFilter('store_location_id', $storeId);
        }
        $count = 0;
        if ($holidayCollection->getSize()) {
            foreach ($holidayCollection as $holiday) {
                if ($holiday['open_time'] == '00:00' && $holiday['closed_time'] == '00:00' &&
                    $holiday['disable_delivery'] == 0 &&	$holiday['disable_pickup'] == 0) {
                    try {
                        $this->connection->update($holidayTbl,
                            [
                                'disable_delivery' => 1,
                                'disable_pickup' => 1
                            ],
                            'id = '. $holiday->getId()
                        );
                        $count++;
                    } catch (\Exception $exception) {
                        $output->writeln('Cannot update row '. $holiday->getId());
                        $this->logger->error(__('Cannot update row %1, reason: %2' , $holiday->getId(), $exception->getMessage()));
                    }
                }
            }
        }
        $output->writeln("Updated $count record!");
    }

}
