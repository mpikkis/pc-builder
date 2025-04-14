<?php
class ModelExtensionPcBuilderPcBuilderComponent extends Model {
	public function addPcBuilderComponent($data) {
		$this->db->query("INSERT INTO " . DB_PREFIX . "pc_builder_component SET pc_builder_category_id = '" . (int)$data['pc_builder_category_id'] . "', required = '" . (int)$data['required'] . "', sort_order = '" . (int)$data['sort_order'] . "', status = '" . (int)$data['status'] . "'");

		$pc_builder_component_id = $this->db->getLastId();

		if (isset($data['image'])) {
			$this->db->query("UPDATE " . DB_PREFIX . "pc_builder_component SET image = '" . $this->db->escape($data['image']) . "' WHERE pc_builder_component_id = '" . (int)$pc_builder_component_id . "'");
		}

		foreach ($data['pc_builder_component_description'] as $language_id => $value) {
			$this->db->query("INSERT INTO " . DB_PREFIX . "pc_builder_component_description SET pc_builder_component_id = '" . (int)$pc_builder_component_id . "', language_id = '" . (int)$language_id . "', name = '" . $this->db->escape($value['name']) . "'");
		}

		if (isset($data['pc_builder_component_category'])) {
			foreach ($data['pc_builder_component_category'] as $category_id) {
				$this->db->query("INSERT INTO " . DB_PREFIX . "pc_builder_component_category SET pc_builder_component_id = '" . (int)$pc_builder_component_id . "', category_id = '" . (int)$category_id . "'");
			}
		}

		$this->cache->delete('pc_builder_component');

		return $pc_builder_component_id;
	}

	public function editPcBuilderComponent($pc_builder_component_id, $data) {
		$this->db->query("UPDATE " . DB_PREFIX . "pc_builder_component SET pc_builder_category_id = '" . (int)$data['pc_builder_category_id'] . "', required = '" . (int)$data['required'] . "', sort_order = '" . (int)$data['sort_order'] . "', status = '" . (int)$data['status'] . "' WHERE pc_builder_component_id = '" . (int)$pc_builder_component_id . "'");

		if (isset($data['image'])) {
			$this->db->query("UPDATE " . DB_PREFIX . "pc_builder_component SET image = '" . $this->db->escape($data['image']) . "' WHERE pc_builder_component_id = '" . (int)$pc_builder_component_id . "'");
		}

		$this->db->query("DELETE FROM " . DB_PREFIX . "pc_builder_component_description WHERE pc_builder_component_id = '" . (int)$pc_builder_component_id . "'");

		foreach ($data['pc_builder_component_description'] as $language_id => $value) {
			$this->db->query("INSERT INTO " . DB_PREFIX . "pc_builder_component_description SET pc_builder_component_id = '" . (int)$pc_builder_component_id . "', language_id = '" . (int)$language_id . "', name = '" . $this->db->escape($value['name']) . "'");
		}

		$this->db->query("DELETE FROM " . DB_PREFIX . "pc_builder_component_category WHERE pc_builder_component_id = '" . (int)$pc_builder_component_id . "'");

		if (isset($data['pc_builder_component_category'])) {
			foreach ($data['pc_builder_component_category'] as $category_id) {
				$this->db->query("INSERT INTO " . DB_PREFIX . "pc_builder_component_category SET pc_builder_component_id = '" . (int)$pc_builder_component_id . "', category_id = '" . (int)$category_id . "'");
			}
		}

		$this->cache->delete('pc_builder_component');
	}

	public function deletePcBuilderComponent($pc_builder_component_id) {
		$this->db->query("DELETE FROM `" . DB_PREFIX . "pc_builder_component` WHERE pc_builder_component_id = '" . (int)$pc_builder_component_id . "'");
		$this->db->query("DELETE FROM `" . DB_PREFIX . "pc_builder_component_description` WHERE pc_builder_component_id = '" . (int)$pc_builder_component_id . "'");

		$this->cache->delete('pc_builder_component');
	}

	public function getPcBuilderComponent($pc_builder_component_id) {
		$query = $this->db->query("SELECT DISTINCT * FROM " . DB_PREFIX . "pc_builder_component WHERE pc_builder_component_id = '" . (int)$pc_builder_component_id . "'");

		return $query->row;
	}

	public function getPcBuilderComponents($data = array()) {
		if ($data) {
			$sql = "SELECT * FROM " . DB_PREFIX . "pc_builder_component i LEFT JOIN " . DB_PREFIX . "pc_builder_component_description id ON (i.pc_builder_component_id = id.pc_builder_component_id) WHERE id.language_id = '" . (int)$this->config->get('config_language_id') . "'";

			$sort_data = array(
				'id.name',
				'i.sort_order'
			);

			if (isset($data['sort']) && in_array($data['sort'], $sort_data)) {
				$sql .= " ORDER BY " . $data['sort'];
			} else {
				$sql .= " ORDER BY id.name";
			}

			if (isset($data['order']) && ($data['order'] == 'DESC')) {
				$sql .= " DESC";
			} else {
				$sql .= " ASC";
			}

			if (isset($data['start']) || isset($data['limit'])) {
				if ($data['start'] < 0) {
					$data['start'] = 0;
				}

				if ($data['limit'] < 1) {
					$data['limit'] = 20;
				}

				$sql .= " LIMIT " . (int)$data['start'] . "," . (int)$data['limit'];
			}

			$query = $this->db->query($sql);

			return $query->rows;
		} else {
			$pc_builder_component_data = $this->cache->get('pc_builder_component.' . (int)$this->config->get('config_language_id'));

			if (!$pc_builder_component_data) {
				$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "pc_builder_component i LEFT JOIN " . DB_PREFIX . "pc_builder_component_description id ON (i.pc_builder_component_id = id.pc_builder_component_id) WHERE id.language_id = '" . (int)$this->config->get('config_language_id') . "' ORDER BY id.name");

				$pc_builder_component_data = $query->rows;

				$this->cache->set('pc_builder_component.' . (int)$this->config->get('config_language_id'), $pc_builder_component_data);
			}

			return $pc_builder_component_data;
		}
	}

	public function getPcBuilderComponentDescriptions($pc_builder_component_id) {
		$pc_builder_component_description_data = array();

		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "pc_builder_component_description WHERE pc_builder_component_id = '" . (int)$pc_builder_component_id . "'");

		foreach ($query->rows as $result) {
			$pc_builder_component_description_data[$result['language_id']] = array(
				'name' => $result['name'],
			);
		}

		return $pc_builder_component_description_data;
	}

	public function getPcBuilderComponentCategories($pc_builder_component_id) {
		$pc_builder_component_category_data = array();

		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "pc_builder_component_category WHERE pc_builder_component_id = '" . (int)$pc_builder_component_id . "'");

		foreach ($query->rows as $result) {
			$pc_builder_component_category_data[] = $result['category_id'];
		}

		return $pc_builder_component_category_data;
	}

	public function getTotalPcBuilderComponents() {
		$query = $this->db->query("SELECT COUNT(*) AS total FROM " . DB_PREFIX . "pc_builder_component");

		return $query->row['total'];
	}
}