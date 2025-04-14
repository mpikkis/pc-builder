<?php
class ControllerExtensionPcBuilderPcBuilder extends Controller {
	public function index() {
		//echo 'pc builder come';
		$this->load->language('extension/pc_builder/pc_builder');

		$this->load->model('extension/pc_builder/pc_builder_category');
		$this->load->model('extension/pc_builder/pc_builder_component');
		$this->load->model('extension/pc_builder/pc_builder_product');
		$this->load->model('extension/pc_builder/pc_builder_build');

		$this->load->model('tool/image');
		$this->load->model('tool/upload');

		$this->document->addStyle('catalog/view/theme/default/stylesheet/pc_builder.css');
		$this->document->addScript('catalog/view/javascript/pc_builder.js');
		$this->document->addScript('catalog/view/javascript/jquery/html2canvas/html2canvas.min.js');

		$this->document->setTitle($this->language->get('heading_title'));

		$data['breadcrumbs'] = array();

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_home'),
			'href' => $this->url->link('common/home')
		);

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('heading_title'),
			'href' => $this->url->link('extension/pc_builder/pc_builder')
		);

		if ($this->request->server['HTTPS']) {
			$server = $this->config->get('config_ssl');
		} else {
			$server = $this->config->get('config_url');
		}

		if (isset($this->request->get['build'])) {
			unset($this->session->data['pc_builder_selected_products']);
		}

		if (isset($this->request->get['build'])) {
			$build = $this->request->get['build'];
		} else {
			$build = '';
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

		$url = '';

		if (isset($this->request->get['build'])) {
			$url .= '&build=' . $this->request->get['build'];
		}

		if (is_file(DIR_IMAGE . $this->config->get('config_logo'))) {
			$data['logo'] = $server . 'image/' . $this->config->get('config_logo');
		} else {
			$data['logo'] = '';
		}

		(float)$amount = 0;
		$weight = 0;

		$data['pc_builder_categories'] = array();

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

				if (!empty($this->request->get['build'])) {
					$build_info = $this->model_extension_pc_builder_pc_builder_build->getPcBuilderBuildByCode($this->request->get['build']);

					if ($build_info) {
						$pc_builder_selected_products = json_decode($build_info['build'], true);
						$this->session->data['pc_builder_selected_products'] = $pc_builder_selected_products;
					} else {
						$pc_builder_selected_products = array();
					}
				} elseif (isset($this->session->data['pc_builder_selected_products'])) {
					$pc_builder_selected_products = $this->session->data['pc_builder_selected_products'];
				} else {
					$pc_builder_selected_products = array();
				}

				if (isset($pc_builder_selected_products[$pc_builder_component['pc_builder_component_id']]['product_id'])) {
					(float)$option_price = 0;
					$option_points = 0;
					$option_weight = 0;

					$product_info = $this->model_extension_pc_builder_pc_builder_product->getProduct($pc_builder_selected_products[$pc_builder_component['pc_builder_component_id']]['product_id']);

					if ($product_info) {
						if ($product_info['image']) {
							$image = $this->model_tool_image->resize($product_info['image'], 32, 32);
						} else {
							$image = '';
						}

						$option_data = array();

						foreach ($pc_builder_selected_products[$pc_builder_component['pc_builder_component_id']]['option'] as $product_option_id => $value) {

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

				$url .= '&page_id=' . rand();

				$pc_builder_components_data[] = array(
					'pc_builder_component_id' => $pc_builder_component['pc_builder_component_id'],
					'name' => $pc_builder_component['name'],
					'thumb' => $thumb,
					'product' => $product_data,
					'required' => $pc_builder_component['required'],
					'href' => $this->url->link('extension/pc_builder/pc_builder_search', 'pc_builder_component_id=' . $pc_builder_component['pc_builder_component_id'] . $url)
				);
			}

			$data['pc_builder_categories'][] = array(
				'pc_builder_category_id' => $pc_builder_category['pc_builder_category_id'],
				'name' => $pc_builder_category['name'],
				'pc_builder_components' => $pc_builder_components_data,
			);
		}

		$data['amount'] = $amount;
		$data['weight'] = $weight;
		$data['build'] = $build;

		$data['total_amount'] = $this->currency->format($amount, $this->session->data['currency']);
		$data['total_weight'] = $this->weight->format($weight, $this->config->get('config_weight_class_id'), $this->language->get('decimal_point'), $this->language->get('thousand_point'));

		$data['print'] = $this->url->link('extension/pc_builder/pc_builder/print', $url);

		$data['column_left'] = $this->load->controller('common/column_left');
		$data['column_right'] = $this->load->controller('common/column_right');
		$data['content_top'] = $this->load->controller('common/content_top');
		$data['content_bottom'] = $this->load->controller('common/content_bottom');
		$data['footer'] = $this->load->controller('common/footer');
		$data['header'] = $this->load->controller('common/header');

		$this->response->setOutput($this->load->view('extension/pc_builder/pc_builder', $data));
	}

	public function add() {
		$this->load->language('extension/pc_builder/pc_builder');

		$json = array();

		if (isset($this->request->post['pc_builder_component_id'])) {
			$pc_builder_component_id = (int)$this->request->post['pc_builder_component_id'];
		} else {
			$pc_builder_component_id = 0;
		}

		if (isset($this->request->post['product_id'])) {
			$product_id = (int)$this->request->post['product_id'];
		} else {
			$product_id = 0;
		}

		$url = '';

		if (isset($this->request->post['pc_builder_component_id'])) {
			$url .= '&pc_builder_component_id=' . $this->request->post['pc_builder_component_id'];
		}

		$this->load->model('extension/pc_builder/pc_builder_product');

		$product_info = $this->model_extension_pc_builder_pc_builder_product->getProduct($product_id);

		if ($product_info) {
			if (isset($this->request->post['quantity'])) {
				$quantity = (int)$this->request->post['quantity'];
			} else {
				$quantity = 1;
			}

			if (isset($this->request->post['option'])) {
				$option = array_filter($this->request->post['option']);
			} else {
				$option = array();
			}

			$product_options = $this->model_extension_pc_builder_pc_builder_product->getProductOptions($this->request->post['product_id']);

			foreach ($product_options as $product_option) {
				if ($product_option['required'] && empty($option[$product_option['product_option_id']])) {
					$json['error']['option'][$product_option['product_option_id']] = sprintf($this->language->get('error_required'), $product_option['name']);
				}
			}

			if (!$json) {
				$this->session->data['pc_builder_selected_products'][$pc_builder_component_id] = array(
					'product_id' => $product_id,
					'option' => $option,
				);

				$json['success'] = true;
			} else {
				$json['redirect'] = str_replace('&amp;', '&', $this->url->link('extension/pc_builder/pc_builder_product', 'product_id=' . $this->request->post['product_id']) . $url);
			}
		}

		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}

	public function remove() {
		$this->load->language('extension/pc_builder/pc_builder');

		$json = array();

		if (isset($this->request->post['pc_builder_component_id'])) {
			$pc_builder_component_id = (int)$this->request->post['pc_builder_component_id'];
		} else {
			$pc_builder_component_id = 0;
		}

		if (isset($this->request->post['product_id'])) {
			$product_id = (int)$this->request->post['product_id'];
		} else {
			$product_id = 0;
		}

		unset($this->session->data['pc_builder_selected_products'][$pc_builder_component_id]);

		$json['success'] = true;

		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}

	public function save() {
		$this->load->language('extension/pc_builder/pc_builder');

		$this->load->model('extension/pc_builder/pc_builder');

		$json = array();

		if (isset($this->session->data['pc_builder_selected_products'])) {
			if ($this->customer->isLogged()) {
				// Save build
				$code = $this->model_extension_pc_builder_pc_builder->addPcBuilderBuild($this->customer->getId(), $this->session->data['pc_builder_selected_products']);

				$this->session->data['success'] = $this->language->get('success_save_build');

				$json['success'] = true;
			} else {
				$json['error'] = $this->language->get('error_login');
			}
		} else {
			$json['error'] = $this->language->get('error_save_build');
		}

		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}

	public function clear() {
		unset($this->session->data['pc_builder_selected_products']);

		$json['success'] = true;

		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}

	public function build_delete() {
		$this->load->language('extension/pc_builder/pc_builder');

		$this->load->model('extension/pc_builder/pc_builder');

		$json = array();

		// Delete build
		$query = $this->model_extension_pc_builder_pc_builder->deletePcBuilderBuild($this->session->data['pc_builder_selected_products']);

		$this->session->data['success'] = $this->language->get('success_save_build');

		$json['success'] = true;

		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}

	public function print() {
		$this->load->language('extension/pc_builder/pc_builder');

		$this->load->model('extension/pc_builder/pc_builder_category');
		$this->load->model('extension/pc_builder/pc_builder_component');
		$this->load->model('extension/pc_builder/pc_builder_product');
		$this->load->model('extension/pc_builder/pc_builder_build');

		$this->load->model('tool/image');
		$this->load->model('tool/upload');

		if ($this->request->server['HTTPS']) {
			$server = $this->config->get('config_ssl');
		} else {
			$server = $this->config->get('config_url');
		}

		if (isset($this->request->get['build'])) {
			$build = $this->request->get['build'];
		} else {
			$build = '';
		}

		$url = '';

		if (isset($this->request->get['build'])) {
			$url .= '&build=' . $this->request->get['build'];
		}

		(float)$amount = 0;
		$weight = 0;

		$data['pc_builder_categories'] = array();

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

				if (!empty($this->request->get['build'])) {
					$build_info = $this->model_extension_pc_builder_pc_builder_build->getPcBuilderBuildByCode($this->request->get['build']);

					if ($build_info) {
						$pc_builder_selected_products = json_decode($build_info['build'], true);
					} else {
						$pc_builder_selected_products = array();
					}
				} elseif (isset($this->session->data['pc_builder_selected_products'])) {
					$pc_builder_selected_products = $this->session->data['pc_builder_selected_products'];
				} else {
					$pc_builder_selected_products = array();
				}

				if (isset($pc_builder_selected_products[$pc_builder_component['pc_builder_component_id']]['product_id'])) {
					(float)$option_price = 0;
					$option_points = 0;
					$option_weight = 0;

					$product_info = $this->model_extension_pc_builder_pc_builder_product->getProduct($pc_builder_selected_products[$pc_builder_component['pc_builder_component_id']]['product_id']);

					if ($product_info) {
						if ($product_info['image']) {
							$image = $this->model_tool_image->resize($product_info['image'], 32, 32);
						} else {
							$image = '';
						}

						$option_data = array();

						foreach ($pc_builder_selected_products[$pc_builder_component['pc_builder_component_id']]['option'] as $product_option_id => $value) {

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

			$data['pc_builder_categories'][] = array(
				'pc_builder_category_id' => $pc_builder_category['pc_builder_category_id'],
				'name' => $pc_builder_category['name'],
				'pc_builder_components' => $pc_builder_components_data,
			);
		}

		$data['amount'] = $amount;
		$data['weight'] = $weight;
		$data['build'] = $build;

		$data['total_amount'] = $this->currency->format($amount, $this->session->data['currency']);
		$data['total_weight'] = $this->weight->format($weight, $this->config->get('config_weight_class_id'), $this->language->get('decimal_point'), $this->language->get('thousand_point'));

		$data['name'] = $this->config->get('config_name');

		if (is_file(DIR_IMAGE . $this->config->get('config_logo'))) {
			$data['logo'] = $server . 'image/' . $this->config->get('config_logo');
		} else {
			$data['logo'] = '';
		}

		$data['store'] = $this->config->get('config_name');
		$data['address'] = nl2br($this->config->get('config_address'));
		$data['geocode'] = $this->config->get('config_geocode');
		$data['geocode_hl'] = $this->config->get('config_language');
		$data['telephone'] = $this->config->get('config_telephone');
		$data['fax'] = $this->config->get('config_fax');
		$data['email'] = $this->config->get('config_email');
		$data['website'] = $server;
		$data['open'] = nl2br($this->config->get('config_open'));
		$data['comment'] = $this->config->get('config_comment');

		$this->response->setOutput($this->load->view('extension/pc_builder/pc_builder_print', $data));
	}

	public function add_to_cart() {
		$this->load->language('extension/pc_builder/pc_builder');

		$this->load->model('extension/pc_builder/pc_builder_category');
		$this->load->model('extension/pc_builder/pc_builder_component');
		$this->load->model('extension/pc_builder/pc_builder_product');
		$this->load->model('extension/pc_builder/pc_builder_build');

		$this->load->model('tool/image');
		$this->load->model('tool/upload');

		$json = array();

		if (($this->request->server['REQUEST_METHOD'] == 'POST')) {
			if (!empty($this->request->post['build']) || isset($this->session->data['pc_builder_selected_products'])) {
				if (!empty($this->request->post['build'])) {
					$build_info = $this->model_extension_pc_builder_pc_builder_build->getPcBuilderBuildByCode($this->request->post['build']);

					if ($build_info) {
						$build = json_decode($build_info['build'], true);
					} else {
						$build = false;
					}
				} else {
					$build = $this->session->data['pc_builder_selected_products'];
				}
			} else {
				$build = false;
			}

			if (!$build) {
				$json['error']['build'] = $this->language->get('error_build');
			}

			$pc_builder_components = $this->model_extension_pc_builder_pc_builder_component->getPcBuilderComponents();

			foreach ($pc_builder_components as $pc_builder_component) {
				// Get PC builder component info
				$pc_builder_component_info = $this->model_extension_pc_builder_pc_builder_component->getPcBuilderComponent($pc_builder_component['pc_builder_component_id']);

				if ($pc_builder_component_info) {
					if ($pc_builder_component_info['required'] && !isset($build[$pc_builder_component_info['pc_builder_component_id']])) {
						$json['error']['pc_builder_components'][$pc_builder_component_info['pc_builder_component_id']] = true;
					}
				}
			}

			if (isset($json['error']['pc_builder_components'])) {
				$json['error']['required_component'] = $this->language->get('error_component_required');
			}
//echo $this->request->post['amount'];
			//echo $this->session->data['currency'];
			//echo $this->config->get('config_currency');
			
			if (empty($json['error'])) {
				$this->session->data['pc_builders'][mt_rand()] = array(
					'build'      => $build,
					'amount'     => $this->currency->convert($this->request->post['amount'], $this->session->data['currency'], $this->config->get('config_currency')),
					'weight'     => $this->request->post['weight'],
				);

				unset($this->session->data['pc_builder_selected_products']);
//print_r($this->session->data['pc_builders']);
				$json['success'] = true;
			}
		}

		
		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}

	public function remove_from_cart() {
		$this->load->language('extension/pc_builder/pc_builder');

		$json = array();
		// Remove
		if (isset($this->request->post['key'])) {
			
			unset($this->session->data['pc_builders'][$this->request->post['key']]);

			$json['success'] = $this->language->get('text_remove');
		}

		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}
}