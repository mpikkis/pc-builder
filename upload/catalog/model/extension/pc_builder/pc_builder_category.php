<?php
class ModelExtensionPcBuilderPcBuilderCategory extends Model {
	public function getPcBuilderCategory($pc_builder_category_id) {
		$query = $this->db->query("SELECT DISTINCT * FROM " . DB_PREFIX . "pc_builder_category i LEFT JOIN " . DB_PREFIX . "pc_builder_category_description id ON (i.pc_builder_category_id = id.pc_builder_category_id) WHERE i.pc_builder_category_id = '" . (int)$pc_builder_category_id . "' AND id.language_id = '" . (int)$this->config->get('config_language_id') . "' AND i.status = '1'");

		return $query->row;
	}

	public function getPcBuilderCategories() {
		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "pc_builder_category i LEFT JOIN " . DB_PREFIX . "pc_builder_category_description id ON (i.pc_builder_category_id = id.pc_builder_category_id) WHERE id.language_id = '" . (int)$this->config->get('config_language_id') . "' AND i.status = '1' ORDER BY i.sort_order, LCASE(id.name) ASC");

		return $query->rows;
	}
}