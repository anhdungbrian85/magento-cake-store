<?php

namespace X247Commerce\ChangeOrderStatus\Console;

use Magento\Framework\Session\SessionManagerInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use X247Commerce\ChangeOrderStatus\Cron\ChangeOrderStatus as ChangeOrderStatusCron;

class ChangeOrderStatus extends Command
{
    protected ChangeOrderStatusCron $changeOrderStatusCron;
    protected SessionManagerInterface $coreSession;
    public function __construct(
        ChangeOrderStatusCron $changeOrderStatusCron,
        SessionManagerInterface $coreSession
    ) {
        parent::__construct();
        $this->changeOrderStatusCron = $changeOrderStatusCron;
        $this->coreSession = $coreSession;
    }


    protected function configure()
    {
        $this->setName('x247commerce:change-order-status')
            ->setDescription("Change order status to complete");
        $this->addArgument('limit', InputArgument::OPTIONAL, __('Default limit is 50 orders/cron run'));

        parent::configure();
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        $limit = $file = $input->getArgument('limit');
        if ($limit) {
            $limit = $this->coreSession->setLimitCompleteOrder($limit);
        }

        $this->changeOrderStatusCron->execute();
    }



}
