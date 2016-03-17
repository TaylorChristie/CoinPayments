# CoinPayments
A PHP implementation of CoinPayments API wrapped up into a simple to use class.



#Introduction#
This is a one file class with simplicity at its core. I wanted to create a simple to use IPN that works with both paypal and bitcoin, because they are the most requested payment systems. Before you continue reading this, you should head over to https://www.coinpayments.net/merchant-tools-ipn#setup and make sure your account is ready to use with thew IPN. You do not need to setup a IPN url on coinpayments, you can do it in the code. 

#How to Use#

This class is very simple to use, you need to simply include the coinPayments.class.php file and initialize it.

```php

require 'src/coinPayments/coinPayments.class.php';

$cp = new \MineSQL\coinPayments();

$cp->setMerchantId('your_merchant_id_on_coinpayments');
$cp->setSecretKey('your_secret_key_you_defined_in_account_settings_on_coinpayments');

```

Now the coinpayments class is ready to do one of two things, either create a payment or recieve a callback notification.

##Creating A New Payment##

```php
...

$productName = "A Test Product";
$currency    = "usd";
$price       = 15.00;
// This should be a unique identifier to differentiate payments in your database 
// so you can use it in your IPN to verify that price and currency are the same (more on this later)
$passthruVar = 'asd234sdf';
// The callback url that coinpayments will send a request to so you can validate the payment
$callbackUrl = 'http://localhost/coinPaymentsCallback.php';

// You can modify the button very simply using the following code
// The button just needs to create a submit action, it can be an input or button type
// This will override the default button hard coded into the source (works with bootstrap out of the box)
$cp->createButton('<button type="submit" class="custom">Make Payment</button>');


$form = $cp->createPayment($productName, $currency, $price, $passthruVar, $callbackUrl);

echo $form;
```

Next, You need to know how to complete the callback (or IPN).

```php
... initalize the class and set your merchantID and SecretKey like above


$passthruVar = $_POST['custom'];
// Now you can get the payment information from storage to get the price of the product and the currency

if($cp->validatePayment($price, $currency)){
  // the payment was successful
} else {  
  // The payment did not correctly validate, all errors are caught into an error array
  print_r($cp->getErrors());
}
```

In order for the payment to actually validate in the class, the request has to be verified through either HMAC or httpauth. Both work seemlessly in the application and is totally plug and play, the source does not need to be modified. 

Then it needs to validate that the actual currency and currency paid are the same, so that is why you need to log the payment into some sort of database so you can fetch it when verifying the payment.It also validates that the amount paid by the buyer clears, and that the status coinpayments sends is either 100 or 2 (https://www.coinpayments.net/merchant-tools-ipn#statuses). If all of these challenges are passed then the payment was successful. If there are errors in payment verification the errors are descriptive and not number based.

##Error Catcher##
This application has an error catching mechanism so you can easily differentiate errors. 

to get all the errors of a callback simply call ::getErrors()

```
$cp->getErrors();
```

This function will either return a key-based array with the keys as the error code numbers and the value as the error code text or it will return a null value.


Here is a table of the error numbers and what they mean.
| Error Code # 	| Error Code Text 	|

|     400 	    | [b]Missing POST data from callback[/b]   

				   The callback response is incomplete. 
				   This means the request is most likely not 
				   from coinPayments.  

				   $_POST Variables that use this Error marker:  
				   - ipn_mode 
				   - merchant 	
-------------------------------------
|     401 		| [b]Unauthorized Request (HTTP/HMAC)[/b]  

				  The callback security information does not match 
				  the information in your application. The error 
				  text tells you if the requested information 
				  was trying to access via either HTTP or HMAC.

				  $_SERVER variables that use this Error marker: 
				  - PHP_AUTH_USER 
				  - PHP_AUTH_PW 
				  - HTTP_HMAC  
-------------------------------------				     	
|     402 	    | [b]HMAC Request Header Not Found[/b]  

				  The HMAC header could not be found even though
				   HMAC was defined as the authorization method. 
				   This error may be caused if a request is being 
				   forged and only http is being used.  

				   Dependant Variables that use this Error marker: 
				   - $_SERVER['HTTP_HMAC'] 
				   - coinPayments::isHttpAuth(true);  	
-------------------------------------

				   
| 403 	| [b]Could not validate security[/b]  The request did not send enough security information to be able to authenticate. This error is thrown if HMAC is not present in the request and HMAC is specifically chosen using coinPayments::isHttpAuth(false). This error marker is only present for ease of use for the developer implementing the script, as this should not throw an error in a production enviroment.   	|
| 500 	| [b]Payment has been reversed[/b]  This error only applies to coinPayment accounts which have the PayPal passthru enabled and a user has charged back a payment. 	|
| 501 	| [b]Incomplete Payment[/b]  The payment has not yet been marked as complete on coinPayments. The payment could either be pending or cancelled.  	|
| 502 	| [b]Mismatching payment amount[/b]  The payment sent less than the required amount of a specified currency than what is defined in the application.  This depends on the $cost variable that is passed into the coinPayments::ValidatePayment() function. Ensure that the information you are passing to the application is correct, if it is then form tampering occurred. 	|
| 503 	| [b]Mismatching currency type[/b]  The currency type was changed in the transaction (form tampering). See error 502 for more information on how this error is triggered. 	|
| 504 	| [b]Mismatching Merchant ID[/b]  This error should never be thrown, but it checks to ensure the POSTed merchant ID matches the application's merchant ID. 	|
|  	|  	|
|  	|  	|
|  	|  	|
|  	|  	|
|  	|  	|
|  	|  	|
|  	|  	|
|  	|  	|
|  	|  	|
|  	|  	|
|  	|  	|
|  	|  	|
|  	|  	|
|  	|  	|
|  	|  	|                                                                                                                                                                                                  |

##Misc.##
You can modify the payment button very easily by editing the CPHelper.class.php file under the createButton method. In the future I might make it more dynamic, but for now it will need to be edited.


#Closing#
This class is made to be extremely simple to use. If you find any issues with it or want to help develop it further send a pull request and I will most likely allow it. 

