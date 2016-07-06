<?php 

require '../src/MineSQL/CoinPayments.php';

// Create an instance of the class
$CP = new \MineSQL\CoinPayments();

// Set the merchant ID and secret key (can be found in account settings on CoinPayments.net)
$CP->setMerchantId('asdasd');
$CP->setSecretKey('asdasd');

// You are required to set the currency, amount and item name for coinpayments. cmd, reset, and merchant are automatically created within the class
// there are many optional settings that you should probably set as well: https://www.coinpayments.net/merchant-tools-buttons

//REQUIRED
$CP->setFormElement('currency', 'USD');
$CP->setFormElement('amountf', 12.50);
$CP->setFormElement('item_name', 'Test Item');
//OPTIONAL
$CP->setFormElement('custom', 'customValue235');
$CP->setFormElement('ipn_url', 'http://minesql.me/ipn/cp');

// After you have finished configuring all your form elements, 
//you can call the CoinPayments::createForm method to invoke 
// the creation of a usable html form.
echo $CP->createForm();

