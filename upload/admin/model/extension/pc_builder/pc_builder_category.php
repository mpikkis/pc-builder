<?php
class ModelExtensionPcBuilderPcBuilderCategory extends Model {
	public function addPcBuilderCategory($data) {
		$this->db->query("INSERT INTO " . DB_PREFIX . "pc_builder_category SET sort_order = '" . (int)$data['sort_order'] . "', status = '" . (int)$data['status'] . "'");

		$pc_builder_category_id = $this->db->getLastId();

		foreach ($data['pc_builder_category_description'] as $language_id => $value) {
			$this->db->query("INSERT INTO " . DB_PREFIX . "pc_builder_category_description SET pc_builder_category_id = '" . (int)$pc_builder_category_id . "', language_id = '" . (int)$language_id . "', name = '" . $this->db->escape($value['name']) . "'");
		}

		$this->cache->delete('pc_builder_category');

		return $pc_builder_category_id;
	}

	public function editPcBuilderCategory($pc_builder_category_id, $data) {
		$this->db->query("UPDATE " . DB_PREFIX . "pc_builder_category SET sort_order = '" . (int)$data['sort_order'] . "', status = '" . (int)$data['status'] . "' WHERE pc_builder_category_id = '" . (int)$pc_builder_category_id . "'");

		$this->db->query("DELETE FROM " . DB_PREFIX . "pc_builder_category_description WHERE pc_builder_category_id = '" . (int)$pc_builder_category_id . "'");

		foreach ($data['pc_builder_category_description'] as $language_id => $value) {
			$this->db->query("INSERT INTO " . DB_PREFIX . "pc_builder_category_description SET pc_builder_category_id = '" . (int)$pc_builder_category_id . "', language_id = '" . (int)$language_id . "', name = '" . $this->db->escape($value['name']) . "'");
		}

		$this->cache->delete('pc_builder_category');
	}

	public function deletePcBuilderCategory($pc_builder_category_id) {
		$this->db->query("DELETE FROM `" . DB_PREFIX . "pc_builder_category` WHERE pc_builder_category_id = '" . (int)$pc_builder_category_id . "'");
		$this->db->query("DELETE FROM `" . DB_PREFIX . "pc_builder_category_description` WHERE pc_builder_category_id = '" . (int)$pc_builder_category_id . "'");

		$this->cache->delete('pc_builder_category');
	}

	public function getPcBuilderCategory($pc_builder_category_id) {
		$query = $this->db->query("SELECT DISTINCT * FROM " . DB_PREFIX . "pc_builder_category i LEFT JOIN " . DB_PREFIX . "pc_builder_category_description id ON (i.pc_builder_category_id = id.pc_builder_category_id) WHERE i.pc_builder_category_id = '" . (int)$pc_builder_category_id . "' AND id.language_id = '" . (int)$this->config->get('config_language_id') . "'");

		return $query->row;
	}

	public function getPcBuilderCategories($data = array()) {
		if ($data) {
			$sql = "SELECT * FROM " . DB_PREFIX . "pc_builder_category i LEFT JOIN " . DB_PREFIX . "pc_builder_category_description id ON (i.pc_builder_category_id = id.pc_builder_category_id) WHERE id.language_id = '" . (int)$this->config->get('config_language_id') . "'";

			if (!empty($data['filter_name'])) {
				$sql .= " AND id.name LIKE '" . $this->db->escape($data['filter_name']) . "%'";
			}

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
			$pc_builder_category_data = $this->cache->get('pc_builder_category.' . (int)$this->config->get('config_language_id'));

			if (!$pc_builder_category_data) {
				$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "pc_builder_category i LEFT JOIN " . DB_PREFIX . "pc_builder_category_description id ON (i.pc_builder_category_id = id.pc_builder_category_id) WHERE id.language_id = '" . (int)$this->config->get('config_language_id') . "' ORDER BY id.name");

				$pc_builder_category_data = $query->rows;

				$this->cache->set('pc_builder_category.' . (int)$this->config->get('config_language_id'), $pc_builder_category_data);
			}

			return $pc_builder_category_data;
		}
	}

	public function getPcBuilderCategoryDescriptions($pc_builder_category_id) {
		$pc_builder_category_description_data = array();

		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "pc_builder_category_description WHERE pc_builder_category_id = '" . (int)$pc_builder_category_id . "'");

		foreach ($query->rows as $result) {
			$pc_builder_category_description_data[$result['language_id']] = array(
				'name' => $result['name'],
			);
		}

		return $pc_builder_category_description_data;
	}

	public function getTotalPcBuilderCategories() {
		$query = $this->db->query("SELECT COUNT(*) AS total FROM " . DB_PREFIX . "pc_builder_category");

		return $query->row['total'];
	}
}