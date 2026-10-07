<?php
namespace Opencart\Admin\Model\Extension\ExpresspayCard\Payment;

class CardExpresspay extends \Opencart\System\Engine\Model {
	const NOTIFICATION_URL = 'index.php?route=extension/expresspay_card/payment/card_expresspay_api.notify';

	public function setParametersFromConfig($config, $request, $data) {
		$data['payment_card_expresspay_name_payment_method'] = isset($request['payment_card_expresspay_name_payment_method'])
			? $request['payment_card_expresspay_name_payment_method']
			: ($config->get('payment_card_expresspay_name_payment_method') ?: $this->language->get('namePaymentMethodDefault'));

		$data['payment_card_expresspay_token'] = isset($request['payment_card_expresspay_token'])
			? $request['payment_card_expresspay_token']
			: $config->get('payment_card_expresspay_token');

		$data['payment_card_expresspay_service_id'] = isset($request['payment_card_expresspay_service_id'])
			? $request['payment_card_expresspay_service_id']
			: $config->get('payment_card_expresspay_service_id');

		$data['payment_card_expresspay_secret_word'] = isset($request['payment_card_expresspay_secret_word'])
			? $request['payment_card_expresspay_secret_word']
			: $config->get('payment_card_expresspay_secret_word');

		$data['payment_card_expresspay_is_use_signature_for_notification'] = isset($request['payment_card_expresspay_is_use_signature_for_notification'])
			? $this->normCheckboxValue($request['payment_card_expresspay_is_use_signature_for_notification'])
			: $this->normCheckboxValue($config->get('payment_card_expresspay_is_use_signature_for_notification'));

		$data['payment_card_expresspay_secret_word_for_notification'] = isset($request['payment_card_expresspay_secret_word_for_notification'])
			? $request['payment_card_expresspay_secret_word_for_notification']
			: $config->get('payment_card_expresspay_secret_word_for_notification');

		$data['payment_card_expresspay_is_test_mode'] = isset($request['payment_card_expresspay_is_test_mode'])
			? $this->normCheckboxValue($request['payment_card_expresspay_is_test_mode'])
			: $this->normCheckboxValue($config->get('payment_card_expresspay_is_test_mode'));

		$data['payment_card_expresspay_api_url'] = isset($request['payment_card_expresspay_api_url'])
			? $request['payment_card_expresspay_api_url']
			: ($config->get('payment_card_expresspay_api_url') ?: 'https://api.express-pay.by/v1/');

		$data['payment_card_expresspay_sandbox_url'] = isset($request['payment_card_expresspay_sandbox_url'])
			? $request['payment_card_expresspay_sandbox_url']
			: ($config->get('payment_card_expresspay_sandbox_url') ?: 'https://sandbox-api.express-pay.by/v1/');

		$data['payment_card_expresspay_info'] = isset($request['payment_card_expresspay_info'])
			? $request['payment_card_expresspay_info']
			: ($config->get('payment_card_expresspay_info') ?: $this->language->get('infoDefault'));

		$data['payment_card_expresspay_message_success'] = isset($request['payment_card_expresspay_message_success'])
			? $request['payment_card_expresspay_message_success']
			: ($config->get('payment_card_expresspay_message_success') ?: $this->language->get('messageSuccessDefault'));

		$data['payment_card_expresspay_status'] = isset($request['payment_card_expresspay_status'])
			? $this->normCheckboxValue($request['payment_card_expresspay_status'])
			: $this->normCheckboxValue($config->get('payment_card_expresspay_status'));

		$data['payment_card_expresspay_sort_order'] = isset($request['payment_card_expresspay_sort_order'])
			? $request['payment_card_expresspay_sort_order']
			: $config->get('payment_card_expresspay_sort_order');

		$data['payment_card_expresspay_processed_status_id'] = isset($request['payment_card_expresspay_processed_status_id'])
			? $request['payment_card_expresspay_processed_status_id']
			: $config->get('payment_card_expresspay_processed_status_id');

		$data['payment_card_expresspay_success_status_id'] = isset($request['payment_card_expresspay_success_status_id'])
			? $request['payment_card_expresspay_success_status_id']
			: $config->get('payment_card_expresspay_success_status_id');

		$data['payment_card_expresspay_fail_status_id'] = isset($request['payment_card_expresspay_fail_status_id'])
			? $request['payment_card_expresspay_fail_status_id']
			: $config->get('payment_card_expresspay_fail_status_id');

		$data['payment_card_expresspay_url_notification'] = $this->config->get('config_url') . self::NOTIFICATION_URL;

		return $data;
	}

	private function normCheckboxValue($checkboxValue) {
		$normValue = 0;

		if ($checkboxValue == null) {
			return $normValue;
		}

		switch ($checkboxValue) {
			case "on":
			case 1:
			case "1":
				$normValue = 1;
		}

		return $normValue;
	}
}
