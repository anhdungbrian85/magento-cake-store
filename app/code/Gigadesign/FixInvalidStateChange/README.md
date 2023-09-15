# Fix issue with Magento 2 quote 'Invalid State Change'

Use this module as a temporary solution to fix the invalid state change message in Magento 2, leading to orders not being created although the customer has payed for the order.

## Important note

This module does not actualle fix the issue, but updates the quote according to the state that was requested.
So if a logged in customer want's to place an order based on a quote for a guest customer, this module converts the quote to the logged in customer.

This might involve in security isues, so please concider before installing this plugin

## Installing

To install this module use:
```
composer require gigadesign/module-magento2-fix-invalid-state-change
bin/magento setup:upgrade
```
