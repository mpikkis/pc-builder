<?php
class ControllerExtensionModulePcBuilderFilter extends Controller {
	public function index() {
		$this->load->language('extension/module/pc_builder_filter');

		$this->load->model('extension/pc_builder/pc_builder_filter');
		$this->load->model('extension/pc_builder/pc_builder_product');
		$this->load->model('extension/pc_builder/pc_builder_component');
		$this->load->model('extension/pc_builder/pc_builder_category');

		$this->load->model('catalog/category');

		//$this->document->addStyle('catalog/view/javascript/jquery/jquery.mobile-1.4.5/jquery.mobile-1.4.5.min.css');
		//$this->document->addScript('catalog/view/javascript/jquery/jquery.mobile-1.4.5/jquery.mobile-1.4.5.min.js');
		$this->document->addStyle('catalog/view/javascript/jquery/jquery-ui-1.13.0/jquery-ui.min.css');
		$this->document->addScript('catalog/view/javascript/jquery/jquery-ui-1.13.0/jquery-ui.min.js');

		if (isset($this->request->get['search'])) {
			$search = $this->request->get['search'];
		} else {
			$search = '';
		}

		if (isset($this->request->get['tag'])) {
			$tag = $this->request->get['tag'];
		} elseif (isset($this->request->get['search'])) {
			$tag = $this->request->get['search'];
		} else {
			$tag = '';
		}

		if (isset($this->request->get['pc_builder_component_id'])) {
			$pc_builder_component_id = $this->request->get['pc_builder_component_id'];
		} else {
			$pc_builder_component_id = 0;
		}

		if ($pc_builder_component_id) {
			// Get PC Builder Component Categories
			$pc_builder_component_categories = $this->model_extension_pc_builder_pc_builder_component->getPcBuilderComponentCategories($pc_builder_component_id);

			$category_ids = $pc_builder_component_categories;
		} else {
			$category_ids = array();
		}

		if (isset($this->session->data['pc_builder_selected_products'])) {
			$pc_builder_selected_products = $this->session->data['pc_builder_selected_products'];
		} else {
			$pc_builder_selected_products = array();
		}

		if (isset($this->request->get['min_price'])) {
			$min_price = $this->request->get['min_price'];
		} else {
			$min_price = 1;
		}

		if (isset($this->request->get['max_price'])) {
			$max_price = $this->request->get['max_price'];
		} else {
			$max_price = 10000;
		}

		$url = '';

		if (isset($this->request->get['pc_builder_component_id'])) {
			$url .= '&pc_builder_component_id=' . $this->request->get['pc_builder_component_id'];
		}

		if (isset($this->request->get['sort'])) {
			$url .= '&sort=' . $this->request->get['sort'];
		}

		if (isset($this->request->get['order'])) {
			$url .= '&order=' . $this->request->get['order'];
		}

		if (isset($this->request->get['limit'])) {
			$url .= '&limit=' . $this->request->get['limit'];
		}

		$data['action'] = str_replace('&amp;', '&', $this->url->link('extension/pc_builder/pc_builder_search', $url));

		// Price
		$data['min_price'] = $min_price;
		$data['max_price'] = $max_price;

		// Get Categories
		if (!empty($this->request->get['category'])) {
			$selected_category = explode(',', $this->request->get['category']);
		} else {
			$selected_category = array();
		}

		$data['selected_category'] = $selected_category;

		$data['categories'] = array();

		foreach ($category_ids as $category_id) {
			$category_info = $this->model_catalog_category->getCategory($category_id);

			if ($category_info) {
				$filter_data = array(
					'filter_name'         => $search,
					'filter_tag'          => $tag,
					'filter_category_ids' => array($category_info['category_id']),
					'filter_sub_category' => 1,
					'selected_products'   => $pc_builder_selected_products,
					//'selected_category'   => $selected_category,
					'min_price'           => $min_price,
					'max_price'           => $max_price,
				);

				$data['categories'][] = array(
					'category_id' => $category_info['category_id'],
					'name' => $category_info['name'],
				);
			}
		}

		// PC Builder Filters
		if (isset($this->request->get['filter'])) {
			$data['selected_filter'] = explode(',', $this->request->get['filter']);
		} else {
			$data['selected_filter'] = array();
		}

		$data['filter_groups'] = array();

		$filter_groups = $this->model_extension_pc_builder_pc_builder_filter->getCategoryFilters($category_ids);

		if ($filter_groups) {
			foreach ($filter_groups as $filter_group) {
				$childen_data = array();

				foreach ($filter_group['filter'] as $filter) {
					$filter_data = array(
						'filter_name'         => $search,
						'filter_tag'          => $tag,
						'filter_category_ids' => $category_ids,
						'filter_sub_category' => 1,
						'selected_products'   => $pc_builder_selected_products,
						'selected_category'   => $selected_category,
						'min_price'           => $min_price,
						'max_price'           => $max_price,
						'filter_filter'       => $filter['filter_id']
					);

					$childen_data[] = array(
						'filter_id' => $filter['filter_id'],
						'name'      => $filter['name'] . ($this->config->get('config_product_count') ? ' (' . $this->model_extension_pc_builder_pc_builder_product->getTotalProducts($filter_data) . ')' : '')
					);
				}

				$data['filter_groups'][] = array(
					'filter_group_id' => $filter_group['filter_group_id'],
					'name'            => $filter_group['name'],
					'filter'          => $childen_data
				);
			}

			return $this->load->view('extension/module/pc_builder_filter', $data);
		}
	}
}