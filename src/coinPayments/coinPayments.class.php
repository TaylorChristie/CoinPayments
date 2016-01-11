<?php
namespace MineSQL;

class coinPayments 
{

	private $secretKey;
	private $merchantId;
	private $isHttpAuth;
	private $button = '<button type="submit" class="btn btn-default">Purchase With CoinPayments</button>';
	public $paymentErrors;
	const ENDPOINT = 'https://www.coinpayments.net/index.php';


	// Can change the style of your payment button
	public function createButton($button)
	{
		$this->$button = $button;
	}


	public function setMerchantId($merchant)
	{
		$this->merchantId = $merchant;
	}

	public function setSecretKey($secretKey)
	{
		$this->secretKey = $secretKey;
	}


	public function createPayment($productName, $currency, $price, $custom, $callbackUrl, $successUrl = '', $cancelUrl = '')
	{
		$fields = array(
				  'merchant' => $this->merchantId,
				  'item_name' => $productName,
				  'currency' => $currency,
				  'amountf' => $price, 
				  'ipn_url' => $callbackUrl,
				  'success_url' => $successUrl,
				  'cancel_url' => $cancelUrl,
				  'custom'  => $custom
				  );

		return $this->createForm($fields);
	}



	public function ValidatePayment($cost, $currency)
	{
		if(!isset($_POST['ipn_mode']))
		{
			$this->paymentError[] = 'ipn mode not set.';

			return false;

		}

		if($this->isHttpAuth || $_POST['ipn_mode'] != 'hmac') {
			
			//Verify that the http authentication checks out with the users supplied information 
			// 
			if($_SERVER['PHP_AUTH_USER']==$this->merchantId && $_SERVER['PHP_AUTH_PW']==$this->secretKey)
			{
				// Failsafe to prevent malformed requests to throw an error
				if(empty($_POST['merchant']))
				{

					$this->paymentError[] = 'POST data does not contain a merchant ID.';

					return false;

					
				}

				if($this->checkFields()) {
					echo 'IPN OK';
					return true;
				}

			}

			$this->paymentError[] = 'Request does not autheticate (wrong merchant ID + secret Key combo)';

			return false;

		}

		return $this->validatePaymentHMAC();

	}


	private function validatePaymentHMAC()
	{
		if(!empty($_SERVER['HTTP_HMAC'])) {

			$hmac = hash_hmac("sha512", file_get_contents('php://input'), $this->secretKey);

			if($hmac == $_SERVER['HTTP_HMAC']) {

				if($this->checkFields()) {

					echo 'IPN OK';
					return true;

				}
			}

			$this->paymentError[] = 'HMAC hashes do not match';

			return false;
		}

		$this->paymentError[] = 'Does not contain a HMAC request';

		return false;
	}


	private function checkFields($currency, $cost)
	{
		// Ensure the paid out merchant is the same as the application
		if($_POST['merchant'] == $this->merchantId) {

			//ensure that the same currency was used (form tampering)
			if(strtoupper($_POST['currency1']) == strtoupper($currency)) {

				// ensure the price was paid
				if(floatval($_POST['amount1']) >= floatval($cost)) {

					// check and make sure coinpayments confirmed the payment
					if(intval($_POST['status']) >= 100 || intval($_POST['status']) == 2) {

						return true;

					}

					if(intval($_POST['status']) == -2) {

						$this->paymentError[100] = 'The payment has been chargedback through paypal.';

						return false;

					}

					$this->paymentError[101] = 'The payment most likely has not been completed yet.';

					return false;

				}

				$this->paymentError[102] = 'The amount paid does not match the original payment.';

			}

			$this->paymentError[103] = 'The currency requested and currency paid differ, suspected form tampering.';

			return false;
		}

		$this->paymentError[104] = 'Merchant ID does not match.';

		return false;
	}

	private function createProperties($fields)
	{
		$field['cmd']         = '_pay_simple';
		$field['item_name']   = 'Payment';
		$field['custom']	  = '';
		$field['want_shipping'] = '0';


		foreach($field as $key=>$item)
		{
			if(!array_key_exists($key, $fields))
			{
				$fields[$key] = $item;
			}
		}


		return $fields;
	}


	private function createForm($fields)
	{
		$data = $this->createProperties($fields);

		$text = '<form action="'.self::ENDPOINT.'" method="post">';

		foreach($data as $name => $value) {
			$text .= '<input type="hidden" name="'.$name.'" value="'.$value.'">';
		}

		return $text.$this->button.'</form>';

	}


	public function getErrors()
	{


		return (empty($this->paymentErrors)) ? $this->paymentErrors : array('None');
	}











}