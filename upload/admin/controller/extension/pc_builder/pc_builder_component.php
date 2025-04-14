<?php
class ControllerExtensionPcBuilderPcBuilderComponent extends Controller {
	private $error = array();

	public function index() {
		$this->load->language('extension/pc_builder/pc_builder_component');

		$this->document->setTitle($this->language->get('heading_title'));

		$this->load->model('extension/pc_builder/pc_builder_component');

		$this->getList();
	}

	public function add() {
		$this->load->language('extension/pc_builder/pc_builder_component');

		$this->document->setTitle($this->language->get('heading_title'));

		$this->load->model('extension/pc_builder/pc_builder_component');

		if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validateForm()) {
			$this->model_extension_pc_builder_pc_builder_component->addPcBuilderComponent($this->request->post);

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

			$this->response->redirect($this->url->link('extension/pc_builder/pc_builder_component', 'user_token=' . $this->session->data['user_token'] . $url, true));
		}

		$this->getForm();
	}

	public function edit() {
		$this->load->language('extension/pc_builder/pc_builder_component');

		$this->document->setTitle($this->language->get('heading_title'));

		$this->load->model('extension/pc_builder/pc_builder_component');

		if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validateForm()) {
			$this->model_extension_pc_builder_pc_builder_component->editPcBuilderComponent($this->request->get['pc_builder_component_id'], $this->request->post);

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

			$this->response->redirect($this->url->link('extension/pc_builder/pc_builder_component', 'user_token=' . $this->session->data['user_token'] . $url, true));
		}

		$this->getForm();
	}

	public function delete() {
		$this->load->language('extension/pc_builder/pc_builder_component');

		$this->document->setTitle($this->language->get('heading_title'));

		$this->load->model('extension/pc_builder/pc_builder_component');

		if (isset($this->request->post['selected']) && $this->validateDelete()) {
			foreach ($this->request->post['selected'] as $pc_builder_component_id) {
				$this->model_extension_pc_builder_pc_builder_component->deletePcBuilderComponent($pc_builder_component_id);
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

			$this->response->redirect($this->url->link('extension/pc_builder/pc_builder_component', 'user_token=' . $this->session->data['user_token'] . $url, true));
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
			'href' => $this->url->link('extension/pc_builder/pc_builder_component', 'user_token=' . $this->session->data['user_token'] . $url, true)
		);

		$data['add'] = $this->url->link('extension/pc_builder/pc_builder_component/add', 'user_token=' . $this->session->data['user_token'] . $url, true);
		$data['delete'] = $this->url->link('extension/pc_builder/pc_builder_component/delete', 'user_token=' . $this->session->data['user_token'] . $url, true);

		$data['pc_builder_components'] = array();

		$filter_data = array(
			'sort'  => $sort,
			'order' => $order,
			'start' => ($page - 1) * $this->config->get('config_limit_admin'),
			'limit' => $this->config->get('config_limit_admin')
		);

		$pc_builder_component_total = $this->model_extension_pc_builder_pc_builder_component->getTotalPcBuilderComponents();

		$results = $this->model_extension_pc_builder_pc_builder_component->getPcBuilderComponents($filter_data);

		foreach ($results as $result) {
			$data['pc_builder_components'][] = array(
				'pc_builder_component_id' => $result['pc_builder_component_id'],
				'name'                    => $result['name'],
				'status'                  => $result['status'] ? $this->language->get('text_enabled') : $this->language->get('text_disabled'),
				'sort_order'              => $result['sort_order'],
				'edit'                    => $this->url->link('extension/pc_builder/pc_builder_component/edit', 'user_token=' . $this->session->data['user_token'] . '&pc_builder_component_id=' . $result['pc_builder_component_id'] . $url, true)
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

		$data['sort_name'] = $this->url->link('extension/pc_builder/pc_builder_component', 'user_token=' . $this->session->data['user_token'] . '&sort=name' . $url, true);

		$url = '';

		if (isset($this->request->get['sort'])) {
			$url .= '&sort=' . $this->request->get['sort'];
		}

		if (isset($this->request->get['order'])) {
			$url .= '&order=' . $this->request->get['order'];
		}

		$pagination = new Pagination();
		$pagination->total = $pc_builder_component_total;
		$pagination->page = $page;
		$pagination->limit = $this->config->get('config_limit_admin');
		$pagination->url = $this->url->link('extension/pc_builder/pc_builder_component', 'user_token=' . $this->session->data['user_token'] . $url . '&page={page}', true);

		$data['pagination'] = $pagination->render();

		$data['results'] = sprintf($this->language->get('text_pagination'), ($pc_builder_component_total) ? (($page - 1) * $this->config->get('config_limit_admin')) + 1 : 0, ((($page - 1) * $this->config->get('config_limit_admin')) > ($pc_builder_component_total - $this->config->get('config_limit_admin'))) ? $pc_builder_component_total : ((($page - 1) * $this->config->get('config_limit_admin')) + $this->config->get('config_limit_admin')), $pc_builder_component_total, ceil($pc_builder_component_total / $this->config->get('config_limit_admin')));

		$data['sort'] = $sort;
		$data['order'] = $order;

		$data['header'] = $this->load->controller('common/header');
		$data['column_left'] = $this->load->controller('common/column_left');
		$data['footer'] = $this->load->controller('common/footer');

		$this->response->setOutput($this->load->view('extension/pc_builder/pc_builder_component_list', $data));
	}

	protected function getForm() {
		$data['text_form'] = !isset($this->request->get['pc_builder_component_id']) ? $this->language->get('text_add') : $this->language->get('text_edit');

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
			'href' => $this->url->link('extension/pc_builder/pc_builder_component', 'user_token=' . $this->session->data['user_token'] . $url, true)
		);

		if (!isset($this->request->get['pc_builder_component_id'])) {
			$data['action'] = $this->url->link('extension/pc_builder/pc_builder_component/add', 'user_token=' . $this->session->data['user_token'] . $url, true);
		} else {
			$data['action'] = $this->url->link('extension/pc_builder/pc_builder_component/edit', 'user_token=' . $this->session->data['user_token'] . '&pc_builder_component_id=' . $this->request->get['pc_builder_component_id'] . $url, true);
		}

		$data['cancel'] = $this->url->link('extension/pc_builder/pc_builder_component', 'user_token=' . $this->session->data['user_token'] . $url, true);

		if (isset($this->request->get['pc_builder_component_id']) && ($this->request->server['REQUEST_METHOD'] != 'POST')) {
			$pc_builder_component_info = $this->model_extension_pc_builder_pc_builder_component->getPcBuilderComponent($this->request->get['pc_builder_component_id']);
		}

		$data['user_token'] = $this->session->data['user_token'];

		$this->load->model('localisation/language');

		$data['languages'] = $this->model_localisation_language->getLanguages();

		if (isset($this->request->post['pc_builder_component_description'])) {
			$data['pc_builder_component_description'] = $this->request->post['pc_builder_component_description'];
		} elseif (isset($this->request->get['pc_builder_component_id'])) {
			$data['pc_builder_component_description'] = $this->model_extension_pc_builder_pc_builder_component->getPcBuilderComponentDescriptions($this->request->get['pc_builder_component_id']);
		} else {
			$data['pc_builder_component_description'] = array();
		}

		$this->load->model('extension/pc_builder/pc_builder_category');

		if (isset($this->request->post['pc_builder_category_id'])) {
			$data['pc_builder_category_id'] = $this->request->post['pc_builder_category_id'];
		} elseif (!empty($pc_builder_component_info)) {
			$data['pc_builder_category_id'] = $pc_builder_component_info['pc_builder_category_id'];
		} else {
			$data['pc_builder_category_id'] = 0;
		}

		if (isset($this->request->post['pc_builder_category'])) {
			$data['pc_builder_category'] = $this->request->post['pc_builder_category'];
		} elseif (!empty($pc_builder_component_info)) {
			$pc_builder_category_info = $this->model_extension_pc_builder_pc_builder_category->getPcBuilderCategory($pc_builder_component_info['pc_builder_category_id']);

			if ($pc_builder_category_info) {
				$data['pc_builder_category'] = $pc_builder_category_info['name'];
			} else {
				$data['pc_builder_category'] = '';
			}
		} else {
			$data['pc_builder_category'] = '';
		}

		// Categories
		$this->load->model('catalog/category');

		if (isset($this->request->post['pc_builder_component_category'])) {
			$categories = $this->request->post['pc_builder_component_category'];
		} elseif (isset($this->request->get['pc_builder_component_id'])) {
			$categories = $this->model_extension_pc_builder_pc_builder_component->getPcBuilderComponentCategories($this->request->get['pc_builder_component_id']);
		} else {
			$categories = array();
		}

		$data['pc_builder_component_categories'] = array();

		foreach ($categories as $category_id) {
			$category_info = $this->model_catalog_category->getCategory($category_id);

			if ($category_info) {
				$data['pc_builder_component_categories'][] = array(
					'category_id' => $category_info['category_id'],
					'name'        => ($category_info['path']) ? $category_info['path'] . ' &gt; ' . $category_info['name'] : $category_info['name']
				);
			}
		}

		// Image
		if (isset($this->request->post['image'])) {
			$data['image'] = $this->request->post['image'];
		} elseif (!empty($pc_builder_component_info)) {
			$data['image'] = $pc_builder_component_info['image'];
		} else {
			$data['image'] = '';
		}

		$this->load->model('tool/image');

		if (isset($this->request->post['image']) && is_file(DIR_IMAGE . $this->request->post['image'])) {
			$data['thumb'] = $this->model_tool_image->resize($this->request->post['image'], 100, 100);
		} elseif (!empty($pc_builder_component_info) && is_file(DIR_IMAGE . $pc_builder_component_info['image'])) {
			$data['thumb'] = $this->model_tool_image->resize($pc_builder_component_info['image'], 100, 100);
		} else {
			$data['thumb'] = $this->model_tool_image->resize('no_image.png', 100, 100);
		}

		$data['placeholder'] = $this->model_tool_image->resize('no_image.png', 100, 100);

		if (isset($this->request->post['required'])) {
			$data['required'] = $this->request->post['required'];
		} elseif (!empty($pc_builder_component_info)) {
			$data['required'] = $pc_builder_component_info['required'];
		} else {
			$data['required'] = true;
		}

		if (isset($this->request->post['status'])) {
			$data['status'] = $this->request->post['status'];
		} elseif (!empty($pc_builder_component_info)) {
			$data['status'] = $pc_builder_component_info['status'];
		} else {
			$data['status'] = true;
		}

		if (isset($this->request->post['sort_order'])) {
			$data['sort_order'] = $this->request->post['sort_order'];
		} elseif (!empty($pc_builder_component_info)) {
			$data['sort_order'] = $pc_builder_component_info['sort_order'];
		} else {
			$data['sort_order'] = '';
		}

		$data['header'] = $this->load->controller('common/header');
		$data['column_left'] = $this->load->controller('common/column_left');
		$data['footer'] = $this->load->controller('common/footer');

		$this->response->setOutput($this->load->view('extension/pc_builder/pc_builder_component_form', $data));
	}

	protected function validateForm() {
		if (!$this->user->hasPermission('modify', 'extension/pc_builder/pc_builder_component')) {
			$this->error['warning'] = $this->language->get('error_permission');
		}

		foreach ($this->request->post['pc_builder_component_description'] as $language_id => $value) {
			if ((utf8_strlen($value['name']) < 3) || (utf8_strlen($value['name']) > 32)) {
				$this->error['name'][$language_id] = $this->language->get('error_name');
			}
		}

		return !$this->error;
	}

	protected function validateDelete() {
		if (!$this->user->hasPermission('modify', 'extension/pc_builder/pc_builder_component')) {
			$this->error['warning'] = $this->language->get('error_permission');
		}

		return !$this->error;
	}
}