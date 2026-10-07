<?php
namespace Opencart\Catalog\Controller\Extension\ExpresspayCard\Payment;

class CardExpresspay extends \Opencart\System\Engine\Controller {
	const MESSAGE_SUCCESS_PARAM_NAME = 'payment_card_expresspay_message_success';
	const PROCESSED_STATUS_ID_PARAM_NAME = 'payment_card_expresspay_processed_status_id';
	const SUCCESS_STATUS_ID_PARAM_NAME = 'payment_card_expresspay_success_status_id';
	const FAIL_STATUS_ID_PARAM_NAME = 'payment_card_expresspay_fail_status_id';

	public function index() {
		$this->load->model('extension/expresspay_card/payment/card_expresspay');
		$this->load->model('extension/expresspay_card/payment/card_expresspay_log');

		$data['button_confirm'] = $this->language->get('button_confirm');
		$data['text_loading'] = $this->language->get('text_loading');

		$data = $this->model_extension_expresspay_card_payment_card_expresspay->setParams($data, $this->config);

		$this->model_extension_expresspay_card_payment_card_expresspay_log->log_info("index", "DATA: " . json_encode($data));

		return $this->load->view('extension/expresspay_card/payment/card_expresspay', $data);
	}

	public function success() {
		if (empty($this->session->data['order_id'])) {
			$this->response->redirect($this->url->link('checkout/checkout'));
			return;
		}

		$this->cart->clear();
		$this->load->model('extension/expresspay_card/payment/card_expresspay');
		$this->load->model('extension/expresspay_card/payment/card_expresspay_log');
		$this->load->language('extension/expresspay_card/payment/card_expresspay');
		$this->model_extension_expresspay_card_payment_card_expresspay_log->log_info("successStart", "Order Id: " . $this->session->data['order_id']);
		$headingTitle = $this->language->get('heading_title_success');
		$this->document->setTitle($headingTitle);
		$data['heading_title'] = $headingTitle;

		if (empty($this->config->get(self::MESSAGE_SUCCESS_PARAM_NAME))) {
			$data['text_message'] = $this->language->get('text_message_success');
		} else {
			$data['text_message'] = $this->config->get(self::MESSAGE_SUCCESS_PARAM_NAME);
		}
		$data['text_message'] = nl2br(str_replace('##order_id##', $this->session->data['order_id'], $data['text_message']));

		$data['button_continue'] = $this->language->get('button_continue');
		$data['text_loading'] = $this->language->get('text_loading');

		$this->load->model('checkout/order');

		if ($this->model_checkout_order->getOrder($this->session->data['order_id'])['order_status_id'] != $this->config->get(self::SUCCESS_STATUS_ID_PARAM_NAME)) {
			$this->model_checkout_order->addHistory($this->session->data['order_id'], $this->config->get(self::PROCESSED_STATUS_ID_PARAM_NAME));
		}

		unset($this->session->data['order_id']);

		$data['breadcrumbs'] = $this->setBreadcrumbs($data);
		$data['continue'] = $this->url->link('common/home');

		$this->model_extension_expresspay_card_payment_card_expresspay_log->log_info("successFinish", "DATA: " . json_encode($data));
		$this->response->setOutput($this->load->view('extension/expresspay_card/payment/card_expresspay_successful', $data));
	}

	public function fail() {
		if (empty($this->session->data['order_id'])) {
			$this->response->redirect($this->url->link('checkout/checkout'));
			return;
		}

		$this->load->model('extension/expresspay_card/payment/card_expresspay_log');
		$this->load->language('extension/expresspay_card/payment/card_expresspay');
		$this->model_extension_expresspay_card_payment_card_expresspay_log->log_info("failStart", "Order Id: " . $this->session->data['order_id']);
		$headingTitle = $this->language->get('heading_title_fail');
		$this->document->setTitle($headingTitle);
		$data['heading_title'] = $headingTitle;

		$data['text_message'] = nl2br(str_replace('##order_id##', $this->session->data['order_id'], $this->language->get('text_message_fail')));

		$this->load->model('checkout/order');
		$this->model_checkout_order->addHistory($this->session->data['order_id'], $this->config->get(self::FAIL_STATUS_ID_PARAM_NAME));

		unset($this->session->data['order_id']);

		$data['breadcrumbs'] = $this->setBreadcrumbs($data);
		$data['continue'] = $this->url->link('checkout/checkout');

		$this->model_extension_expresspay_card_payment_card_expresspay_log->log_info("failFinish", "DATA: " . json_encode($data));
		$this->response->setOutput($this->load->view('extension/expresspay_card/payment/card_expresspay_failure', $data));
	}

	private function setBreadcrumbs($data) {
		$data['breadcrumbs'] = [];

		$data['breadcrumbs'][] = [
			'href' => $this->url->link('common/home'),
			'text' => $this->language->get('text_home'),
			'separator' => false
		];

		$data['breadcrumbs'][] = [
			'href' => $this->url->link('checkout/cart'),
			'text' => $this->language->get('text_basket'),
			'separator' => $this->language->get('text_separator')
		];

		$data['breadcrumbs'][] = [
			'href' => $this->url->link('checkout/checkout'),
			'text' => $this->language->get('text_checkout'),
			'separator' => $this->language->get('text_separator')
		];

		return $data;
	}
}
