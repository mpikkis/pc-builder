<?php
class ControllerExtensionPcBuilderPcBuilderBuild extends Controller {
	public function index() {
		if (!$this->customer->isLogged()) {
			$this->session->data['redirect'] = $this->url->link('extension/pc_builder/pc_builder_build', '', true);

			$this->response->redirect($this->url->link('account/login', '', true));
		}

		$this->load->language('extension/pc_builder/pc_builder_build');

		$this->load->model('extension/pc_builder/pc_builder');
		$this->load->model('extension/pc_builder/pc_builder_category');
		$this->load->model('extension/pc_builder/pc_builder_component');
		$this->load->model('extension/pc_builder/pc_builder_product');

		$this->load->model('tool/image');
		$this->load->model('tool/upload');

		$this->document->addStyle('catalog/view/theme/default/stylesheet/pc_builder.css');
		$this->document->addScript('catalog/view/javascript/pc_builder.js');

		$this->document->setTitle($this->language->get('heading_title'));

		$data['breadcrumbs'] = array();

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_home'),
			'href' => $this->url->link('common/home')
		);

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_account'),
			'href' => $this->url->link('account/account', '', true)
		);

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_saved_pc_build'),
			'href' => $this->url->link('extension/pc_builder/pc_builder_build', '', true)
		);

		if (isset($this->session->data['success'])) {
			$data['success'] = $this->session->data['success'];

			unset($this->session->data['success']);
		} else {
			$data['success'] = '';
		} 

		// Get PC Builder Saved Builds
		$data['saved_builds'] = array();

		$saved_builds = $this->model_extension_pc_builder_pc_builder->getPcBuilderBuilds($this->customer->getId());

		foreach ($saved_builds as $saved_build) {
			$build = json_decode($saved_build['build'], true);

			$url = '';

			(float)$amount = 0;
			$weight = 0;

			$pc_builder_categories_data = array();

			$pc_builder_categories = $this->model_extension_pc_builder_pc_builder_category->getPcBuilderCategories();

			foreach ($pc_builder_categories as $pc_builder_category) {
				// Get PC Builder Components
				$pc_builder_components_data = array();

				$filter_data = array(
					'filter_category_id' => $pc_builder_category['pc_builder_category_id'],
				);

				$pc_builder_components = $this->model_extension_pc_builder_pc_builder_component->getPcBuilderComponents($filter_data);

				foreach ($pc_builder_components as $pc_builder_component) {
					if ($pc_builder_component['image']) {
						$thumb = $this->model_tool_image->resize($pc_builder_component['image'], 32, 32);
					} else {
						$thumb = $this->model_tool_image->resize('placeholder.png', 32, 32);
					}

					if (isset($build[$pc_builder_component['pc_builder_component_id']]['product_id'])) {
						(float)$option_price = 0;
						$option_points = 0;
						$option_weight = 0;

						$product_info = $this->model_extension_pc_builder_pc_builder_product->getProduct($build[$pc_builder_component['pc_builder_component_id']]['product_id']);

						if ($product_info) {
							if ($product_info['image']) {
								$image = $this->model_tool_image->resize($product_info['image'], 32, 32);
							} else {
								$image = '';
							}

							$option_data = array();

							foreach ($build[$pc_builder_component['pc_builder_component_id']]['option'] as $product_option_id => $value) {

								// Get Selected Product Option Data
								$option = $this->model_extension_pc_builder_pc_builder_product->getSelectedProductOptionData($product_option_id, $value, $product_info['product_id'], 1);

								if ($option) {
									if ($option['type'] != 'file') {
										$value = $option['value'];
									} else {
										$upload_info = $this->model_tool_upload->getUploadByCode($option['value']);

										if ($upload_info) {
											$value = $upload_info['name'];
										} else {
											$value = '';
										}
									}

									$option_data[] = array(
										'name'          => $option['name'],
										'value'         => (utf8_strlen($value) > 20 ? utf8_substr($value, 0, 20) . '..' : $value),
										'type'          => $option['type'],
										'quantity'      => $option['quantity'],
										'subtract'      => $option['subtract'],
										'price'         => $option['price'],
										'price_prefix'  => $option['price_prefix'],
										'points'        => $option['points'],
										'points_prefix' => $option['points_prefix'],
										'weight'        => $option['weight'],
										'weight_prefix' => $option['weight_prefix'],
									);

									if ($option['price_prefix'] == '+') {
										$option_price += $option['price'];
									} elseif ($option['price_prefix'] == '-') {
										$option_price -= $option['price'];
									}

									if ($option['points_prefix'] == '+') {
										$option_points += $option['points'];
									} elseif ($option['points_prefix'] == '-') {
										$option_points -= $option['points'];
									}

									if ($option['weight_prefix'] == '+') {
										$option_weight += $option['weight'];
									} elseif ($option['weight_prefix'] == '-') {
										$option_weight -= $option['weight'];
									}
								}
							}

							// Display prices
							if ($this->customer->isLogged() || !$this->config->get('config_customer_price')) {
								if (!is_null($product_info['special']) && (float)$product_info['special'] >= 0) {
									$special = (float)$product_info['special'];
								} else {
									$special = false;
								}

								if ($special) {
									$unit_price = $this->tax->calculate(((float)$special + (float)$option_price), $product_info['tax_class_id'], $this->config->get('config_tax'));
								} else {
									$unit_price = $this->tax->calculate(((float)$product_info['price'] + (float)$option_price), $product_info['tax_class_id'], $this->config->get('config_tax'));
								}

								$price = $this->currency->format($unit_price, $this->session->data['currency']);
								$total = $this->currency->format($unit_price * 1, $this->session->data['currency']);
							} else {
								$price = false;
								$total = false;
							}

							$product_data = array(
								'product_id' => $product_info['product_id'],
								'name' => $product_info['name'],
								'image' => $image,
								'option' => $option_data,
								'price' => $price,
							);

							$amount += $unit_price;
							$weight += $this->weight->convert($product_info['weight'] + $option_weight, $product_info['weight_class_id'], $this->config->get('config_weight_class_id'));
						} else {
							$product_data = array();
						}
					} else {
						$product_data = array();
					}

					$pc_builder_components_data[] = array(
						'pc_builder_component_id' => $pc_builder_component['pc_builder_component_id'],
						'name' => $pc_builder_component['name'],
						'thumb' => $thumb,
						'product' => $product_data,
						'required' => $pc_builder_component['required'],
						'href' => $this->url->link('extension/pc_builder/pc_builder_search', 'pc_builder_component_id=' . $pc_builder_component['pc_builder_component_id'] . $url)
					);
				}

				$pc_builder_categories_data[] = array(
					'pc_builder_category_id' => $pc_builder_category['pc_builder_category_id'],
					'name' => $pc_builder_category['name'],
					'pc_builder_components' => $pc_builder_components_data,
				);
			}

			$data['saved_builds'][] = array(
				'pc_builder_build_id' => $saved_build['pc_builder_build_id'],
				'pc_builder_categories' => $pc_builder_categories_data,
				'code' => $saved_build['code'],
				'amount' => $amount,
				'weight' => $weight,
				'date_added' => date($this->language->get('date_format_short'), strtotime($saved_build['date_added'])),
				'href' => $this->url->link('extension/pc_builder/pc_builder', 'build=' . $saved_build['code']),
				'delete' => $this->url->link('extension/pc_builder/pc_builder_build/delete', 'pc_builder_build_id=' . $saved_build['pc_builder_build_id'], true)
			);
		}
		
		$data['column_left'] = $this->load->controller('common/column_left');
		$data['column_right'] = $this->load->controller('common/column_right');
		$data['content_top'] = $this->load->controller('common/content_top');
		$data['content_bottom'] = $this->load->controller('common/content_bottom');
		$data['footer'] = $this->load->controller('common/footer');
		$data['header'] = $this->load->controller('common/header');
		
		$this->response->setOutput($this->load->view('extension/pc_builder/pc_builder_build', $data));
	}

	public function delete() {
		if (!$this->customer->isLogged()) {
			$this->session->data['redirect'] = $this->url->link('extension/pc_builder/pc_builder_build', '', true);

			$this->response->redirect($this->url->link('account/login', '', true));
		}

		$this->load->language('extension/pc_builder/pc_builder_build');

		$this->document->setTitle($this->language->get('heading_title'));

		$this->load->model('extension/pc_builder/pc_builder');

		if (isset($this->request->get['pc_builder_build_id'])) {
			$this->model_extension_pc_builder_pc_builder->deletePcBuilderBuild($this->request->get['pc_builder_build_id']);

			$this->session->data['success'] = $this->language->get('text_delete');

			$this->response->redirect($this->url->link('extension/pc_builder/pc_builder_build', '', true));
		}

		$this->index();
	}
}
