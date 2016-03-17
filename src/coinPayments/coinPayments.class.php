<?php
namespace MineSQL;

class coinPayments 
{

	private $secretKey, $merchantId, $isHttpAuth;
	private $button = '<button type="submit" class="btn btn-default">Purchase With CoinPayments</button>';

	public $paymentError;

	const ENDPOINT = 'https://www.coinpayments.net/index.php';


	// Can change the style of your payment button
	public function createButton($button)
	{
		$this->$button = $button;
	}

	//
	public function isHttpAuth($setting = true)
	{
		$this->isHttpAuth = $setting;
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

	public function getIpnVars()
	{
		return $_POST;
	}



	public function ValidatePayment()
	{
		if(!isset($_POST['ipn_mode']))
		{
			$this->paymentError[400] = 'Missing Post Data From Callback';

			return false;

		}

		if(empty($_POST['merchant']))
		{

			$this->paymentError[400] = 'Missing Post Data From Callback';

			return false;
			
		}


		if($this->isHttpAuth || $_POST['ipn_mode'] != 'hmac') {
			
			//Verify that the http authentication checks out with the users supplied information 
			if($_SERVER['PHP_AUTH_USER']==$this->merchantId && $_SERVER['PHP_AUTH_PW']==$this->secretKey)
			{				
				if($this->checkFields()) {

					return true;
				}

			}

			$this->paymentError[401] = 'Unauthorized Request (HTTP)';

			return false;

		} elseif(!empty($_SERVER['HTTP_HMAC'])) {

			$hmac = hash_hmac("sha512", file_get_contents('php://input'), $this->secretKey);
	
			if($hmac == $_SERVER['HTTP_HMAC']) {
	
				if($this->checkFields()) {

					return true;
				}
			}

		} else {

			$this->paymentError[403] = 'Could not validate security';

			return false;
		}


	}


	private function checkFields()
	{
		// Ensure the paid out merchant is the same as the application
		if($_POST['merchant'] == $this->merchantId) {


			// check and make sure coinpayments confirmed the payment
			if(intval($_POST['status']) >= 100 || intval($_POST['status']) == 2) {

				return true;

			}

			if(intval($_POST['status']) == -2) {

				$this->paymentError[500] = 'Payment has been reversed';

				return false;

			}

			$this->paymentError[501] = 'Incomplete Payment';

			return false;

		}

		$this->paymentError[504] = 'Mismatching Merchant ID';

		return false;
	}


	private function createProperties($fields)
	{
		$field['cmd']         = '_pay_simple';
		$field['item_name']   = 'Payment';
		$field['custom']	  = '';
		$field['want_shipping'] = '0';
		$field['quantity']    = '1';


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
		return (empty($this->paymentErrors)) ? $this->paymentErrors : null;
	}











}