<?php
class ModelExtensionPcBuilderPcBuilderComponent extends Model {
	public function getPcBuilderComponent($pc_builder_component_id) {
		$query = $this->db->query("SELECT DISTINCT * FROM " . DB_PREFIX . "pc_builder_component i LEFT JOIN " . DB_PREFIX . "pc_builder_component_description id ON (i.pc_builder_component_id = id.pc_builder_component_id) WHERE i.pc_builder_component_id = '" . (int)$pc_builder_component_id . "' AND id.language_id = '" . (int)$this->config->get('config_language_id') . "' AND i.status = '1'");

		return $query->row;
	}

	public function getPcBuilderComponents($data = array()) {
		$sql = "SELECT * FROM " . DB_PREFIX . "pc_builder_component i LEFT JOIN " . DB_PREFIX . "pc_builder_component_description id ON (i.pc_builder_component_id = id.pc_builder_component_id) WHERE id.language_id = '" . (int)$this->config->get('config_language_id') . "'";

		if (!empty($data['filter_category_id'])) {
			$sql .= " AND i.pc_builder_category_id = '" . (int)$data['filter_category_id'] . "'";
		}

		$sql .= " AND i.status = '1' ORDER BY i.sort_order, LCASE(id.name) ASC";

		$query = $this->db->query($sql);

		return $query->rows;
	}

	public function getPcBuilderComponentCategories($pc_builder_component_id) {
		$pc_builder_component_category_data = array();

		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "pc_builder_component_category WHERE pc_builder_component_id = '" . (int)$pc_builder_component_id . "'");

		foreach ($query->rows as $result) {
			$pc_builder_component_category_data[] = $result['category_id'];
		}

		return $pc_builder_component_category_data;
	}
}