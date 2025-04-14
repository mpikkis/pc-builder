<?php
class ModelExtensionPcBuilderPcBuilder extends Model {
	public function addPcBuilderBuild($customer_id, $build) {
		if ($customer_id) {
			$this->db->query("DELETE FROM " . DB_PREFIX . "pc_builder_build WHERE customer_id = '" . (int)$customer_id . "' AND build = '" . $this->db->escape(json_encode($build, true)) . "'");
		}

		// Generate code
		$code = bin2hex(random_bytes(8));

		$this->db->query("INSERT INTO " . DB_PREFIX . "pc_builder_build SET customer_id = '" . (int)$customer_id . "', build = '" . $this->db->escape(json_encode($build, true)) . "', code = '" . $this->db->escape($code) . "', date_added = NOW()");

		$pc_builder_build_id = $this->db->getLastId();

		return $code;
	}

	public function deletePcBuilderBuild($pc_builder_build_id) {
		$this->db->query("DELETE FROM " . DB_PREFIX . "pc_builder_build WHERE pc_builder_build_id = '" . (int)$pc_builder_build_id . "' AND customer_id = '" . (int)$this->customer->getId() . "'");
	}

	public function getPcBuilderBuild($customer_id, $pc_builder_build_id) {
		$query = $this->db->query("SELECT DISTINCT * FROM " . DB_PREFIX . "pc_builder_build WHERE pc_builder_build_id = '" . (int)$pc_builder_build_id . "' AND customer_id = '" . (int)$customer_id . "'");

		return $query->row;
	}

	public function getPcBuilderBuilds($customer_id) {
		$sql = "SELECT * FROM " . DB_PREFIX . "pc_builder_build WHERE customer_id = '" . (int)$customer_id . "'";

		$sql .= " ORDER BY date_added DESC";

		$query = $this->db->query($sql);

		return $query->rows;
	}

}