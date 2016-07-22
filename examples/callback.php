<?php 

require '../src/MineSQL/CoinPayments.php';


$CP = new \MineSQL\coinPayments();


// Set your Merchant ID
$CP->setMerchantId('');
// Set your secret IPN Key (in Account Settings on Coinpayments)
$CP->setSecretKey('');

// you can use $_POST['custom'] or any other field names using the $_POST construct: https://www.coinpayments.net/merchant-tools-ipn#fields


// we pass the $_POST and $_SERVER variables to alleviate any actual dependencies in the class when testing/troubleshooting.
// in the future, methods will be used within CoinPayments:: to grab the $_POST and $_SERVER variables in order to maintain easy of use
// as well as sound pattern design
try {
if($CP->listen($_POST, $_SERVER)) 
{
	// The payment is successful and passed all security measures
	// you can call the DB here if you want
	
} 
else 
{
	// the payment is pending. an exception is thrown for all other payment errors.
}
}
catch(Exception $e) 
{
	// catch the exception and decided what to do with it. (most likely log it)
}
