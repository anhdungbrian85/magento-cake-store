<?php
/**
 * Copyright ©  All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace X247Commerce\CustomerAddressAutocomplete\Console\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class CorrectCustomerPostcode extends Command
{

    const CUSTOMER_ID = "customer-id";
    const CUSTOMER_RANGE = "customer-range-id";

    protected $resource;
    protected $connection;
    protected $postcodeHelper;

    public function __construct(
        \Magento\Framework\App\ResourceConnection $resource,
        \X247Commerce\StoreLocator\Helper\DeliveryArea $postcodeHelper
    ) {
        $this->resource = $resource;
        $this->connection = $resource->getConnection();
        $this->postcodeHelper = $postcodeHelper;
        parent::__construct();
    }
    /**
     * {@inheritdoc}
     */
    protected function execute(
        InputInterface $input,
        OutputInterface $output
    ) {
        try {
            $customerId = $input->getOption(self::CUSTOMER_ID);
            $customerRange = $input->getOption(self::CUSTOMER_RANGE);
            $connection = $this->connection;
            $caeTbl = $connection->getTableName('customer_address_entity');
            $sql = $connection->select()->from($caeTbl, ['entity_id', 'postcode']);
            
            if ($customerId) {
                $sql->where('parent_id = ?', $customerId);
            }

            if ($customerRange) {
                $customerIdMin = explode(',', $customerRange) [0];
                $customerIdMax = explode(',', $customerRange) [1];
                $sql->where("parent_id >= $customerIdMin AND parent_id <= $customerIdMax");
            }

            $customerAddresses = $connection->fetchAll($sql);

            foreach ($customerAddresses as $customerAddress) {
                $addressId = $customerAddress['entity_id'];
                $curPostcode = $customerAddress['postcode'];
                $correctPostcode = $this->postcodeHelper->correctPostcode($curPostcode);

                if ($curPostcode != $correctPostcode) {
                    $connection->insertOnDuplicate($caeTbl, [
                        'entity_id' => $addressId,
                        'postcode' => $correctPostcode
                    ]);
                    $output->writeln("Edit postcode from $curPostcode to $correctPostcode");
                }
                
            }

        } catch (\Exception $e) {
            $output->writeln($e->getMessage());
        }
        
        
    }


    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this->setName("x247commerce:correct-customer-postcode");
        $this->setDescription("Correct customer postcode UK");

        $this->addOption(
                self::CUSTOMER_ID,
                null,
                InputOption::VALUE_REQUIRED,
                'Specific customer id - eg: 1'
            );
        $this->addOption(
                self::CUSTOMER_RANGE,
                null,
                InputOption::VALUE_REQUIRED,
                'Specific a range of customer ids - eg: 1,100'
            );

        parent::configure();

    }
}
