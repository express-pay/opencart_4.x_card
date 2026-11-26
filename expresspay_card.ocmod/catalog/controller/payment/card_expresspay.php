<?php
namespace Opencart\Catalog\Controller\Extension\ExpresspayCard\Payment;

class CardExpresspay extends \Opencart\System\Engine\Controller {
	const MESSAGE_SUCCESS_PARAM_NAME = 'payment_card_expresspay_message_success';
	const PROCESSED_STATUS_ID_PARAM_NAME = 'payment_card_expresspay_processed_status_id';
	const SUCCESS_STATUS_ID_PARAM_NAME = 'payment_card_expresspay_success_status_id';
	const FAIL_STATUS_ID_PARAM_NAME = 'payment_card_expresspay_fail_status_id';

	public function index() {
		$this->load->language('extension/expresspay_card/payment/card_expresspay');
		$this->load->model('extension/expresspay_card/payment/card_expresspay');
		$this->load->model('extension/expresspay_card/payment/card_expresspay_log');

		$data['button_confirm'] = $this->language->get('button_confirm');
		$data['text_loading'] = $this->language->get('text_loading');

		$data = $this->model_extension_expresspay_card_payment_card_expresspay->setParams($data, $this->config);

		if (empty($data['Action']) || empty($data['AccountNo'])) {
			$this->model_extension_expresspay_card_payment_card_expresspay_log->log_error("index", "Payment form is not ready");
			return '';
		}

		$this->model_extension_expresspay_card_payment_card_expresspay_log->log_info("index", "DATA: " . json_encode($data));

		return $this->load->view('extension/expresspay_card/payment/card_expresspay', $data);
	}

	public function success() {
		$orderId = $this->resolveOrderId();

		$this->load->model('checkout/order');
		$order_info = $orderId ? $this->model_checkout_order->getOrder($orderId) : [];

		if (!$order_info) {
			$this->response->redirect($this->url->link('checkout/checkout'));
			return;
		}

		$this->cart->clear();
		$this->load->model('extension/expresspay_card/payment/card_expresspay_log');
		$this->load->language('extension/expresspay_card/payment/card_expresspay');
		$this->model_extension_expresspay_card_payment_card_expresspay_log->log_info("successStart", "Order Id: " . $orderId);
		$headingTitle = $this->language->get('heading_title_success');
		$this->document->setTitle($headingTitle);
		$data['heading_title'] = $headingTitle;

		if (empty($this->config->get(self::MESSAGE_SUCCESS_PARAM_NAME))) {
			$data['text_message'] = $this->language->get('text_message_success');
		} else {
			$data['text_message'] = $this->config->get(self::MESSAGE_SUCCESS_PARAM_NAME);
		}
		$data['text_message'] = nl2br(str_replace('##order_id##', $orderId, $data['text_message']));

		$data['button_continue'] = $this->language->get('button_continue');
		$data['text_loading'] = $this->language->get('text_loading');

		$processedStatusId = (int)$this->config->get(self::PROCESSED_STATUS_ID_PARAM_NAME);

		if ((int)$order_info['order_status_id'] === 0 && $processedStatusId > 0) {
			$this->model_checkout_order->addHistory($orderId, $processedStatusId);
		}

		unset($this->session->data['order_id']);

		$data['breadcrumbs'] = $this->setBreadcrumbs();
		$data['continue'] = $this->url->link('common/home');
		$data['header'] = $this->load->controller('common/header');
		$data['footer'] = $this->load->controller('common/footer');

		$this->model_extension_expresspay_card_payment_card_expresspay_log->log_info("successFinish", "DATA: " . json_encode($data));
		$this->response->setOutput($this->load->view('extension/expresspay_card/payment/card_expresspay_successful', $data));
	}

	public function fail() {
		$orderId = $this->resolveOrderId();

		$this->load->model('checkout/order');
		$order_info = $orderId ? $this->model_checkout_order->getOrder($orderId) : [];

		if (!$order_info) {
			$this->response->redirect($this->url->link('checkout/checkout'));
			return;
		}

		$this->load->model('extension/expresspay_card/payment/card_expresspay_log');
		$this->load->language('extension/expresspay_card/payment/card_expresspay');
		$this->model_extension_expresspay_card_payment_card_expresspay_log->log_info("failStart", "Order Id: " . $orderId);
		$headingTitle = $this->language->get('heading_title_fail');
		$this->document->setTitle($headingTitle);
		$data['heading_title'] = $headingTitle;

		$data['text_message'] = nl2br(str_replace('##order_id##', $orderId, $this->language->get('text_message_fail')));

		$paidStatusId = (int)$this->config->get(self::SUCCESS_STATUS_ID_PARAM_NAME);
		$failStatusId = (int)$this->config->get(self::FAIL_STATUS_ID_PARAM_NAME);

		if ((int)$order_info['order_status_id'] !== $paidStatusId && $failStatusId > 0) {
			$this->model_checkout_order->addHistory($orderId, $failStatusId);
		}

		unset($this->session->data['order_id']);

		$data['breadcrumbs'] = $this->setBreadcrumbs();
		$data['continue'] = $this->url->link('checkout/checkout');
		$data['header'] = $this->load->controller('common/header');
		$data['footer'] = $this->load->controller('common/footer');

		$this->model_extension_expresspay_card_payment_card_expresspay_log->log_info("failFinish", "DATA: " . json_encode($data));
		$this->response->setOutput($this->load->view('extension/expresspay_card/payment/card_expresspay_failure', $data));
	}

	private function resolveOrderId(): int {
		$orderId = (int)($this->request->get['order_id'] ?? 0);
		$token = (string)($this->request->get['token'] ?? '');
		$secret = (string)$this->config->get('payment_card_expresspay_secret_word');

		if ($orderId > 0 && $token !== '' && hash_equals(hash_hmac('sha1', (string)$orderId, $secret), $token)) {
			return $orderId;
		}

		return (int)($this->session->data['order_id'] ?? 0);
	}

	private function setBreadcrumbs(): array {
		return [
			[
				'href' => $this->url->link('common/home'),
				'text' => $this->language->get('text_home'),
				'separator' => false
			],
			[
				'href' => $this->url->link('checkout/cart'),
				'text' => $this->language->get('text_basket'),
				'separator' => $this->language->get('text_separator')
			],
			[
				'href' => $this->url->link('checkout/checkout'),
				'text' => $this->language->get('text_checkout'),
				'separator' => $this->language->get('text_separator')
			]
		];
	}
}
