<?php

namespace X247Commerce\RemoveOrderImages\Console;

use Magento\Framework\Session\SessionManagerInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use X247Commerce\RemoveOrderImages\Cron\RemoveOrderImages as RemoveOrderImagesCron;

class RemoveOrderImages extends Command
{
    protected $removeOrderImagesCron;
    protected $coreSession;
    public function __construct(
        RemoveOrderImagesCron $removeOrderImagesCron,
        SessionManagerInterface $coreSession
    ) {
        parent::__construct();
        $this->removeOrderImagesCron = $removeOrderImagesCron;
        $this->coreSession = $coreSession;
    }


    protected function configure()
    {
        $this->setName('x247commerce:remove_order_images')
            ->setDescription("Remove Images of Complete or Cancel Order");
        $this->addArgument('limit', InputArgument::OPTIONAL, __('Default limit is 500 orders/cron run'));

        parent::configure();
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        $limit = $input->getArgument('limit');
        if ($limit) {
            $limit = $this->coreSession->setLimitCompleteOrder($limit);
        }

        $this->removeOrderImagesCron->execute();
    }



}
