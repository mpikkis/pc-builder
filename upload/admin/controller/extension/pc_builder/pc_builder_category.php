<?php
class ControllerExtensionPcBuilderPcBuilderCategory extends Controller {
	private $error = array();

	public function index() {
		$this->load->language('extension/pc_builder/pc_builder_category');

		$this->document->setTitle($this->language->get('heading_title'));

		$this->load->model('extension/pc_builder/pc_builder_category');

		$this->getList();
	}

	public function add() {
		$this->load->language('extension/pc_builder/pc_builder_category');

		$this->document->setTitle($this->language->get('heading_title'));

		$this->load->model('extension/pc_builder/pc_builder_category');

		if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validateForm()) {
			$this->model_extension_pc_builder_pc_builder_category->addPcBuilderCategory($this->request->post);

			$this->session->data['success'] = $this->language->get('text_success');

			$url = '';

			if (isset($this->request->get['sort'])) {
				$url .= '&sort=' . $this->request->get['sort'];
			}

			if (isset($this->request->get['order'])) {
				$url .= '&order=' . $this->request->get['order'];
			}

			if (isset($this->request->get['page'])) {
				$url .= '&page=' . $this->request->get['page'];
			}

			$this->response->redirect($this->url->link('extension/pc_builder/pc_builder_category', 'user_token=' . $this->session->data['user_token'] . $url, true));
		}

		$this->getForm();
	}

	public function edit() {
		$this->load->language('extension/pc_builder/pc_builder_category');

		$this->document->setTitle($this->language->get('heading_title'));

		$this->load->model('extension/pc_builder/pc_builder_category');

		if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validateForm()) {
			$this->model_extension_pc_builder_pc_builder_category->editPcBuilderCategory($this->request->get['pc_builder_category_id'], $this->request->post);

			$this->session->data['success'] = $this->language->get('text_success');

			$url = '';

			if (isset($this->request->get['sort'])) {
				$url .= '&sort=' . $this->request->get['sort'];
			}

			if (isset($this->request->get['order'])) {
				$url .= '&order=' . $this->request->get['order'];
			}

			if (isset($this->request->get['page'])) {
				$url .= '&page=' . $this->request->get['page'];
			}

			$this->response->redirect($this->url->link('extension/pc_builder/pc_builder_category', 'user_token=' . $this->session->data['user_token'] . $url, true));
		}

		$this->getForm();
	}

	public function delete() {
		$this->load->language('extension/pc_builder/pc_builder_category');

		$this->document->setTitle($this->language->get('heading_title'));

		$this->load->model('extension/pc_builder/pc_builder_category');

		if (isset($this->request->post['selected']) && $this->validateDelete()) {
			foreach ($this->request->post['selected'] as $pc_builder_category_id) {
				$this->model_extension_pc_builder_pc_builder_category->deletePcBuilderCategory($pc_builder_category_id);
			}

			$this->session->data['success'] = $this->language->get('text_success');

			$url = '';

			if (isset($this->request->get['sort'])) {
				$url .= '&sort=' . $this->request->get['sort'];
			}

			if (isset($this->request->get['order'])) {
				$url .= '&order=' . $this->request->get['order'];
			}

			if (isset($this->request->get['page'])) {
				$url .= '&page=' . $this->request->get['page'];
			}

			$this->response->redirect($this->url->link('extension/pc_builder/pc_builder_category', 'user_token=' . $this->session->data['user_token'] . $url, true));
		}

		$this->getList();
	}

	protected function getList() {
		if (isset($this->request->get['sort'])) {
			$sort = $this->request->get['sort'];
		} else {
			$sort = 'name';
		}

		if (isset($this->request->get['order'])) {
			$order = $this->request->get['order'];
		} else {
			$order = 'ASC';
		}

		if (isset($this->request->get['page'])) {
			$page = (int)$this->request->get['page'];
		} else {
			$page = 1;
		}

		$url = '';

		if (isset($this->request->get['sort'])) {
			$url .= '&sort=' . $this->request->get['sort'];
		}

		if (isset($this->request->get['order'])) {
			$url .= '&order=' . $this->request->get['order'];
		}

		if (isset($this->request->get['page'])) {
			$url .= '&page=' . $this->request->get['page'];
		}

		$data['breadcrumbs'] = array();

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_home'),
			'href' => $this->url->link('common/dashboard', 'user_token=' . $this->session->data['user_token'], true)
		);

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('heading_title'),
			'href' => $this->url->link('extension/pc_builder/pc_builder_category', 'user_token=' . $this->session->data['user_token'] . $url, true)
		);

		$data['add'] = $this->url->link('extension/pc_builder/pc_builder_category/add', 'user_token=' . $this->session->data['user_token'] . $url, true);
		$data['delete'] = $this->url->link('extension/pc_builder/pc_builder_category/delete', 'user_token=' . $this->session->data['user_token'] . $url, true);

		$data['pc_builder_categories'] = array();

		$filter_data = array(
			'sort'  => $sort,
			'order' => $order,
			'start' => ($page - 1) * $this->config->get('config_limit_admin'),
			'limit' => $this->config->get('config_limit_admin')
		);

		$pc_builder_category_total = $this->model_extension_pc_builder_pc_builder_category->getTotalPcBuilderCategories();

		$results = $this->model_extension_pc_builder_pc_builder_category->getPcBuilderCategories($filter_data);

		foreach ($results as $result) {
			$data['pc_builder_categories'][] = array(
				'pc_builder_category_id' => $result['pc_builder_category_id'],
				'name'                    => $result['name'],
				'status'                  => $result['status'] ? $this->language->get('text_enabled') : $this->language->get('text_disabled'),
				'sort_order'              => $result['sort_order'],
				'edit'                    => $this->url->link('extension/pc_builder/pc_builder_category/edit', 'user_token=' . $this->session->data['user_token'] . '&pc_builder_category_id=' . $result['pc_builder_category_id'] . $url, true)
			);
		}

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

		if (isset($this->request->post['selected'])) {
			$data['selected'] = (array)$this->request->post['selected'];
		} else {
			$data['selected'] = array();
		}

		$url = '';

		if ($order == 'ASC') {
			$url .= '&order=DESC';
		} else {
			$url .= '&order=ASC';
		}

		if (isset($this->request->get['page'])) {
			$url .= '&page=' . $this->request->get['page'];
		}

		$data['sort_name'] = $this->url->link('extension/pc_builder/pc_builder_category', 'user_token=' . $this->session->data['user_token'] . '&sort=name' . $url, true);

		$url = '';

		if (isset($this->request->get['sort'])) {
			$url .= '&sort=' . $this->request->get['sort'];
		}

		if (isset($this->request->get['order'])) {
			$url .= '&order=' . $this->request->get['order'];
		}

		$pagination = new Pagination();
		$pagination->total = $pc_builder_category_total;
		$pagination->page = $page;
		$pagination->limit = $this->config->get('config_limit_admin');
		$pagination->url = $this->url->link('extension/pc_builder/pc_builder_category', 'user_token=' . $this->session->data['user_token'] . $url . '&page={page}', true);

		$data['pagination'] = $pagination->render();

		$data['results'] = sprintf($this->language->get('text_pagination'), ($pc_builder_category_total) ? (($page - 1) * $this->config->get('config_limit_admin')) + 1 : 0, ((($page - 1) * $this->config->get('config_limit_admin')) > ($pc_builder_category_total - $this->config->get('config_limit_admin'))) ? $pc_builder_category_total : ((($page - 1) * $this->config->get('config_limit_admin')) + $this->config->get('config_limit_admin')), $pc_builder_category_total, ceil($pc_builder_category_total / $this->config->get('config_limit_admin')));

		$data['sort'] = $sort;
		$data['order'] = $order;

		$data['header'] = $this->load->controller('common/header');
		$data['column_left'] = $this->load->controller('common/column_left');
		$data['footer'] = $this->load->controller('common/footer');

		$this->response->setOutput($this->load->view('extension/pc_builder/pc_builder_category_list', $data));
	}

	protected function getForm() {
		$data['text_form'] = !isset($this->request->get['pc_builder_category_id']) ? $this->language->get('text_add') : $this->language->get('text_edit');

		if (isset($this->error['warning'])) {
			$data['error_warning'] = $this->error['warning'];
		} else {
			$data['error_warning'] = '';
		}

		if (isset($this->error['name'])) {
			$data['error_name'] = $this->error['name'];
		} else {
			$data['error_name'] = array();
		}

		$url = '';

		if (isset($this->request->get['sort'])) {
			$url .= '&sort=' . $this->request->get['sort'];
		}

		if (isset($this->request->get['order'])) {
			$url .= '&order=' . $this->request->get['order'];
		}

		if (isset($this->request->get['page'])) {
			$url .= '&page=' . $this->request->get['page'];
		}

		$data['breadcrumbs'] = array();

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_home'),
			'href' => $this->url->link('common/dashboard', 'user_token=' . $this->session->data['user_token'], true)
		);

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('heading_title'),
			'href' => $this->url->link('extension/pc_builder/pc_builder_category', 'user_token=' . $this->session->data['user_token'] . $url, true)
		);

		if (!isset($this->request->get['pc_builder_category_id'])) {
			$data['action'] = $this->url->link('extension/pc_builder/pc_builder_category/add', 'user_token=' . $this->session->data['user_token'] . $url, true);
		} else {
			$data['action'] = $this->url->link('extension/pc_builder/pc_builder_category/edit', 'user_token=' . $this->session->data['user_token'] . '&pc_builder_category_id=' . $this->request->get['pc_builder_category_id'] . $url, true);
		}

		$data['cancel'] = $this->url->link('extension/pc_builder/pc_builder_category', 'user_token=' . $this->session->data['user_token'] . $url, true);

		if (isset($this->request->get['pc_builder_category_id']) && ($this->request->server['REQUEST_METHOD'] != 'POST')) {
			$pc_builder_category_info = $this->model_extension_pc_builder_pc_builder_category->getPcBuilderCategory($this->request->get['pc_builder_category_id']);
		}

		$this->load->model('localisation/language');

		$data['languages'] = $this->model_localisation_language->getLanguages();

		if (isset($this->request->post['pc_builder_category_description'])) {
			$data['pc_builder_category_description'] = $this->request->post['pc_builder_category_description'];
		} elseif (isset($this->request->get['pc_builder_category_id'])) {
			$data['pc_builder_category_description'] = $this->model_extension_pc_builder_pc_builder_category->getPcBuilderCategoryDescriptions($this->request->get['pc_builder_category_id']);
		} else {
			$data['pc_builder_category_description'] = array();
		}

		if (isset($this->request->post['status'])) {
			$data['status'] = $this->request->post['status'];
		} elseif (!empty($pc_builder_category_info)) {
			$data['status'] = $pc_builder_category_info['status'];
		} else {
			$data['status'] = true;
		}

		if (isset($this->request->post['sort_order'])) {
			$data['sort_order'] = $this->request->post['sort_order'];
		} elseif (!empty($pc_builder_category_info)) {
			$data['sort_order'] = $pc_builder_category_info['sort_order'];
		} else {
			$data['sort_order'] = '';
		}

		$data['header'] = $this->load->controller('common/header');
		$data['column_left'] = $this->load->controller('common/column_left');
		$data['footer'] = $this->load->controller('common/footer');

		$this->response->setOutput($this->load->view('extension/pc_builder/pc_builder_category_form', $data));
	}

	protected function validateForm() {
		if (!$this->user->hasPermission('modify', 'extension/pc_builder/pc_builder_category')) {
			$this->error['warning'] = $this->language->get('error_permission');
		}

		foreach ($this->request->post['pc_builder_category_description'] as $language_id => $value) {
			if ((utf8_strlen($value['name']) < 3) || (utf8_strlen($value['name']) > 32)) {
				$this->error['name'][$language_id] = $this->language->get('error_name');
			}
		}

		return !$this->error;
	}

	protected function validateDelete() {
		if (!$this->user->hasPermission('modify', 'extension/pc_builder/pc_builder_category')) {
			$this->error['warning'] = $this->language->get('error_permission');
		}

		return !$this->error;
	}

	public function autocomplete() {
		$json = array();

		if (isset($this->request->get['filter_name'])) {
			$this->load->model('extension/pc_builder/pc_builder_category');

			$filter_data = array(
				'filter_name' => $this->request->get['filter_name'],
				'start'       => 0,
				'limit'       => 5
			);

			$results = $this->model_extension_pc_builder_pc_builder_category->getPcBuilderCategories($filter_data);

			foreach ($results as $result) {
				$json[] = array(
					'pc_builder_category_id' => $result['pc_builder_category_id'],
					'name'                   => strip_tags(html_entity_decode($result['name'], ENT_QUOTES, 'UTF-8'))
				);
			}
		}

		$sort_order = array();

		foreach ($json as $key => $value) {
			$sort_order[$key] = $value['name'];
		}

		array_multisort($sort_order, SORT_ASC, $json);

		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}
}