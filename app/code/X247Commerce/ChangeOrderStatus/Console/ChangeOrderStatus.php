<?php

namespace X247Commerce\ChangeOrderStatus\Console;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use X247Commerce\ChangeOrderStatus\Cron\ChangeOrderStatus as ChangeOrderStatusCron;

class ChangeOrderStatus extends Command
{
    protected ChangeOrderStatusCron $changeOrderStatusCron;

    public function __construct(
        ChangeOrderStatusCron $changeOrderStatusCron,
    ) {
        parent::__construct();
        $this->changeOrderStatusCron = $changeOrderStatusCron;

    }


    protected function configure()
    {
        $this->setName('x247commerce:change-order-status')
            ->setDescription("Change order status to complete");

        parent::configure();
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        $this->changeOrderStatusCron->execute();
    }



}
