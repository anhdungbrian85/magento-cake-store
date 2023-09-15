<?php

namespace X247Commerce\Checkout\Cron;

use X247Commerce\Checkout\Console\CleanInactiveQuote as CleanInactiveQuoteCommand;

class CleanInactiveQuote
{
    protected CleanInactiveQuoteCommand $cleanActiveQuoteCommand;

    public function __construct(
    	CleanInactiveQuoteCommand $cleanActiveQuoteCommand
    ) {
		$this->cleanActiveQuoteCommand = $cleanActiveQuoteCommand;
    }

    /**
     * @return void
     * */
    public function execute()
    {
        $this->cleanActiveQuoteCommand->cleanNotExistCustomerQuote();
    }

}