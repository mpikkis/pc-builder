<?php
class ControllerExtensionExtensionPcBuilder extends Controller {
	private $error = array();

	public function index() {
		$this->load->language('extension/extension/pc_builder');

		$this->load->model('setting/extension');

		$this->getList();
	}

	protected function getList() {
		if (isset($this->error['warning'])) {
			$data['error_warning'] = $this->error['warning'];
		} else {
			$data['error_warning'] = '';
		}

		if (isset($this->session->data['success'])) {
			$data['success'] = $this->session->data['success'];

			unset($this->session->data['success']);
		} else {
			$data['success'] = '';
		}

		$data['files'] = array();

		$files = glob(DIR_APPLICATION . 'controller/extension/pc_builder/*.php');

		if ($files) {
			foreach ($files as $file) {
				$pc_builder_file = basename($file, '.php');

				$this->load->language('extension/pc_builder/' . $pc_builder_file, 'pc_builder');

				$data['files'][] = array(
					'name'      => $this->language->get('pc_builder')->get('heading_title'),
					'manage'      => $this->url->link('extension/pc_builder/' . $pc_builder_file, 'user_token=' . $this->session->data['user_token'], true)
				);
			}
		}

		$sort_order = array();

		foreach ($data['files'] as $key => $value) {
			$sort_order[$key] = $value['name'];
		}

		array_multisort($sort_order, SORT_ASC, $data['files']);

		$this->response->setOutput($this->load->view('extension/extension/pc_builder', $data));
	}

	protected function validate() {
		if (!$this->user->hasPermission('modify', 'extension/extension/pc_builder')) {
			$this->error['warning'] = $this->language->get('error_permission');
		}

		return !$this->error;
	}
}
