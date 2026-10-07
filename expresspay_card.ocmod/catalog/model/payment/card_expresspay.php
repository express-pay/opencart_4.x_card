<?php
namespace Opencart\Catalog\Model\Extension\ExpresspayCard\Payment;

class CardExpresspay extends \Opencart\System\Engine\Model {
	const NAME_PAYMENT_METHOD = 'payment_card_expresspay_name_payment_method';
	const SORT_ORDER_PARAM_NAME = 'payment_card_expresspay_sort_order';
	const CURRENCY = 933;
	const RETURN_TYPE = 'redirect';

	public function getMethods(array $address = []): array {
		$this->load->language('extension/expresspay_card/payment/card_expresspay');
		$status = false;

		if ($this->cart->hasProducts()) {
			$status = true;
		}

		$method_data = [];

		// Название метода оплаты
		$textTitle = $this->language->get('heading_title');
		if ($this->config->get(self::NAME_PAYMENT_METHOD)) {
			$textTitle = $this->config->get(self::NAME_PAYMENT_METHOD);
		}

		$sortOrder = $this->config->get(self::SORT_ORDER_PARAM_NAME);

		if ($status) {
			$option_data['card_expresspay'] = [
				'code' => 'card_expresspay.card_expresspay',
				'name' => $textTitle
			];

			$method_data = [
				'code'       => 'card_expresspay',
				'name'       => $textTitle,
				'option'     => $option_data,
				'sort_order' => $sortOrder
			];
		}

		return $method_data;
	}

	public function setParams($data, $config) {
		$orderId = $this->session->data['order_id'] ?? 0;

		if (!$orderId) {
			$this->load->model('extension/expresspay_card/payment/card_expresspay_log');
			$this->model_extension_expresspay_card_payment_card_expresspay_log->log_error("setParams", "Order ID is empty");
			return $data;
		}

		$this->load->model('checkout/order');

		$order_info = $this->model_checkout_order->getOrder($orderId);

		if (empty($order_info) || !isset($order_info['total'])) {
			$this->load->model('extension/expresspay_card/payment/card_expresspay_log');
			$this->model_extension_expresspay_card_payment_card_expresspay_log->log_error("setParams", "Order not found or missing total; Order ID - " . $orderId);
			return $data;
		}

		$amount = str_replace('.', ',', $this->currency->format($order_info['total'], $this->session->data['currency'], 0, false));
		if ($this->session->data['currency'] !== "BYN") {
			$response = $this->getCurrencyRateFromNBRB($this->session->data['currency']);
			$amount = str_replace('.', ',', round($amount * $response->Cur_OfficialRate, 2));
		}

		$token = $config->get('payment_card_expresspay_token');
		$serviceId = $config->get('payment_card_expresspay_service_id');
		$secretWord = $config->get('payment_card_expresspay_secret_word');
		$info = $config->get('payment_card_expresspay_info');
		$apiUrl = $config->get('payment_card_expresspay_api_url');
		$sandboxUrl = $config->get('payment_card_expresspay_sandbox_url');
		$isTestMode = $config->get('payment_card_expresspay_is_test_mode');

		$signatureParams['Token'] = $token;
		$signatureParams['ServiceId'] = $serviceId;
		$signatureParams['AccountNo'] = $orderId;
		$signatureParams['Amount'] = $amount;
		$signatureParams['Currency'] = self::CURRENCY;
		$signatureParams['Info'] = str_replace('##order_id##', $orderId, $info);
		$signatureParams['ReturnType'] = self::RETURN_TYPE;
		$signatureParams['ReturnUrl'] = $this->url->link('extension/expresspay_card/payment/card_expresspay.success');
		$signatureParams['FailUrl'] = $this->url->link('extension/expresspay_card/payment/card_expresspay.fail');
		$signatureParams["ReturnInvoiceUrl"] = "1";

		$data['Signature'] = self::computeSignature($signatureParams, $secretWord, 'add-webcard-invoice');
		unset($signatureParams['Token']);
		$data = array_merge($data, $signatureParams);

		if ($isTestMode) {
			$data['Action'] = rtrim($sandboxUrl, '/') . '/web_cardinvoices';
		} else {
			$data['Action'] = rtrim($apiUrl, '/') . '/web_cardinvoices';
		}

		return $data;
	}

	private function getCurrencyRateFromNBRB($currency) {
		return json_decode(file_get_contents("https://www.nbrb.by/api/exrates/rates/$currency?parammode=2"));
	}

	private static function computeSignature($signatureParams, $secretWord, $method) {
		$normalizedParams = array_change_key_case($signatureParams, CASE_LOWER);
		$mapping = array(
			"get-qr-code" => array(
				"token",
				"invoiceid",
				"viewtype",
				"imagewidth",
				"imageheight"
			),
			"add-web-invoice" => array(
				"token",
				"serviceid",
				"accountno",
				"amount",
				"currency",
				"expiration",
				"info",
				"surname",
				"firstname",
				"patronymic",
				"city",
				"street",
				"house",
				"building",
				"apartment",
				"isnameeditable",
				"isaddresseditable",
				"isamounteditable",
				"emailnotification",
				"smsphone",
				"returntype",
				"returnurl",
				"failurl",
				"returninvoiceurl"
			),
			"add-webcard-invoice" => array(
				"token",
				"serviceid",
				"accountno",
				"expiration",
				"amount",
				"currency",
				"info",
				"returnurl",
				"failurl",
				"language",
				"sessiontimeoutsecs",
				"expirationdate",
				"returntype",
				"returninvoiceurl"
			)
		);
		$apiMethod = $mapping[$method];
		$result = "";
		foreach ($apiMethod as $item) {
			$result .= (isset($normalizedParams[$item])) ? $normalizedParams[$item] : '';
		}
		$hash = strtoupper(hash_hmac('sha1', $result, $secretWord));
		return $hash;
	}
}
