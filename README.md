# CoinPayments
A PHP implementation of CoinPayments Payment Gateway wrapped up into a simple to use class.



#Introduction#
This is a one file class with simplicity at its core. I wanted to create a simple to use IPN that works with both paypal and bitcoin, because they are the most requested payment systems. Before you continue reading this, you should head over to https://www.coinpayments.net/merchant-tools-ipn#setup and make sure your account is ready to use with thew IPN. You do not need to setup a IPN url on coinpayments, you can do it in the code. 

#How to Use#

This class is very simple to use, you need to simply include the coinPayments.class.php file and initialize it.

```php

require 'src/MineSQL/CoinPayments.php';

$cp = new \MineSQL\CoinPayments();

$cp->setMerchantId('your_merchant_id_on_coinpayments');
$cp->setSecretKey('your_secret_key_you_defined_in_account_settings_on_coinpayments');

```

Now the coinpayments class is ready to do one of two things, either create a payment or recieve a callback notification.

##Creating A New Payment##

there are many optional settings that you should probably set as well: https://www.coinpayments.net/merchant-tools-buttons

```php
...

// You are required to set the currency, amount and item name for coinpayments. cmd, reset, and merchant are automatically created within the class


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
```

Next, You need to know how to complete the callback (or IPN).

```php

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
```

In order for the payment to actually validate in the class, the request has to be verified through either HMAC or httpauth. Both work seemlessly in the application and is totally plug and play, the source does not need to be modified. The method of verification can be changed in the CoinPayments merchant settings panel.


##Error Catcher##
This application has an error catching mechanism so you can easily differentiate errors. 

Any errors will invoke a new Exception to be called. I am still working on this feature to have named Exceptions for better usability, but for now they simply give detail error messages.


##Misc.##
For now the button is not easy to modify, but it does have a class name 'coinpayments' so you can add whichever styles you want to it through this class. In the future I might make the button styling more dynamic.


#Closing#
I love help, if you think you can make this class better make a pull request! :)

