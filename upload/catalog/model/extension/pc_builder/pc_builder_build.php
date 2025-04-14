<?php
class ModelExtensionPcBuilderPcBuilderBuild extends Model {
	public function getPcBuilderBuildByCode($code) {
		$query = $this->db->query("SELECT DISTINCT * FROM " . DB_PREFIX . "pc_builder_build WHERE code = '" . $this->db->escape($code) . "'");

		return $query->row;
	}
}