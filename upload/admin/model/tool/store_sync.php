<?php
class ModelToolStoreSync extends Model {
  public function setup() {
    $sql = "CREATE TABLE IF NOT EXISTS `". DB_PREFIX . "lazada_product` (
      `model` varchar(64) NOT NULL,
      `sku` varchar(64) NOT NULL,
      `status` varchar(64) NOT NULL,
      `quantity` int(4) NOT NULL DEFAULT '0',
      `price` decimal(15,4) NOT NULL DEFAULT '0.0000',
      PRIMARY KEY (`model`),
      KEY `sku` (`sku`)
    )";

    $this->db->query($sql);
  }

  public function sync($userid, $apikey) {
    // Drop everything from table
    $this->db->query("DELETE FROM " . DB_PREFIX . "lazada_product");

    // Insert newfound rows!
    $offset = 0;
    $limit = 500;

    $c = $this->query($userid, $apikey, 'GetProducts', $offset, $limit);

    $rows = array();
    foreach ($c['SuccessResponse']['Body']['Products'] as $key => $value) {
      $attr = $value['Attributes'];
      $skus = $value['Skus'][0];

      $row = join(',', array(
        "'" . $this->db->escape($skus['SellerSku']) . "'",
        "'" . $this->db->escape($skus['ShopSku']) . "'",
        "'" . $this->db->escape($skus['Status']) . "'",
        $this->db->escape($skus['quantity']),
        $this->db->escape($skus['price']),
        // $skus['Available'],
      ));

      array_push($rows, '(' . $row . ')');
    }

    $this->db->query("INSERT INTO " . DB_PREFIX . "lazada_product (model, sku, status, quantity, price) VALUES " . join(',', $rows));
  }

  public function getProducts($data = array()) {
		$sql = "  SELECT pd.name as name, p.model as model, p.quantity as quantity, p.price as price, p.status as status, lp.status as lz_status, lp.quantity as lz_quantity, lp.sku as lz_sku";
    $sql .= " FROM " . DB_PREFIX . "product p";
    $sql .= " LEFT JOIN " . DB_PREFIX . "product_description pd";
    $sql .= "   ON (p.product_id = pd.product_id)";
    $sql .= " LEFT JOIN " . DB_PREFIX . "lazada_product lp";
    $sql .= "   ON (p.model = lp.model)";
    $sql .= " WHERE pd.language_id = '" . (int)$this->config->get('config_language_id') . "'";

		if (!empty($data['filter_name'])) {
			$sql .= " AND pd.name LIKE '" . $this->db->escape($data['filter_name']) . "%'";
		}

		if (!empty($data['filter_model'])) {
			$sql .= " AND p.model LIKE '" . $this->db->escape($data['filter_model']) . "%'";
		}

		if (!empty($data['filter_lz_sku'])) {
			$sql .= " AND lp.sku LIKE '" . $this->db->escape($data['filter_lz_sku']) . "%'";
		}

		if (!empty($data['filter_lz_exists'])) {
      if ($data['filter_lz_exists'] == '1') {
        $sql .= " AND lp.status IS NOT NULL";
      } elseif ($data['filter_lz_exists'] == '2') {
        $sql .= " AND lp.status IS NULL";
      }
		}

		if (isset($data['filter_price']) && !is_null($data['filter_price'])) {
			$sql .= " AND p.price LIKE '" . $this->db->escape($data['filter_price']) . "%'";
		}

		if (isset($data['filter_quantity']) && !is_null($data['filter_quantity'])) {
			$sql .= " AND p.quantity = '" . (int)$data['filter_quantity'] . "'";
		}

		if (isset($data['filter_status']) && !is_null($data['filter_status'])) {
			$sql .= " AND p.status = '" . (int)$data['filter_status'] . "'";
		}

    $sql .= " GROUP BY p.product_id";

		$sort_data = array(
			'name',
			'model',
			'price',
			'quantity',
      'status',
			'lz_quantity',
			'lz_sku',
			'lz_status',
		);

		if (isset($data['sort']) && in_array($data['sort'], $sort_data)) {
			$sql .= " ORDER BY " . $data['sort'];
		} else {
			$sql .= " ORDER BY name";
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
  }

  public function getTotalProducts($data = array()) {
		$sql = "  SELECT COUNT(DISTINCT p.product_id) as total";
    $sql .= " FROM " . DB_PREFIX . "product p";
    $sql .= " LEFT JOIN " . DB_PREFIX . "product_description pd";
    $sql .= "   ON (p.product_id = pd.product_id)";
    $sql .= " LEFT JOIN " . DB_PREFIX . "lazada_product lp";
    $sql .= "   ON (p.model = lp.model)";
    $sql .= " WHERE pd.language_id = '" . (int)$this->config->get('config_language_id') . "'";

		if (!empty($data['filter_name'])) {
			$sql .= " AND pd.name LIKE '" . $this->db->escape($data['filter_name']) . "%'";
		}

		if (!empty($data['filter_model'])) {
			$sql .= " AND p.model LIKE '" . $this->db->escape($data['filter_model']) . "%'";
		}

		if (!empty($data['filter_lz_sku'])) {
			$sql .= " AND lp.sku LIKE '" . $this->db->escape($data['filter_lz_sku']) . "%'";
		}

		if (!empty($data['filter_lz_exists'])) {
      if ($data['filter_lz_exists'] == '1') {
        $sql .= " AND lp.status IS NOT NULL";
      } elseif ($data['filter_lz_exists'] == '2') {
        $sql .= " AND lp.status IS NULL";
      }
		}

		if (isset($data['filter_price']) && !is_null($data['filter_price'])) {
			$sql .= " AND p.price LIKE '" . $this->db->escape($data['filter_price']) . "%'";
		}

		if (isset($data['filter_quantity']) && !is_null($data['filter_quantity'])) {
			$sql .= " AND p.quantity = '" . (int)$data['filter_quantity'] . "'";
		}

		if (isset($data['filter_status']) && !is_null($data['filter_status'])) {
			$sql .= " AND p.status = '" . (int)$data['filter_status'] . "'";
		}

    $query = $this->db->query($sql);

    return $query->row['total'];
  }

  public function savequantity($userid, $apikey, $sku, $quantity) {
    // Save changes to local
		$this->db->query("UPDATE  " . DB_PREFIX . "lazada_product SET quantity = '".(int)$quantity."' WHERE model = '".$sku."'");

    // Make request
    $xml = new SimpleXMLElement("<?xml version=\"1.0\" encoding=\"utf-8\" ?><Request></Request>");
    $xmlskus = $xml->addChild('Product')->addChild('Skus');

    $xmlsku = $xmlskus->addChild('Sku');
    $xmlsku->addChild('SellerSku', $sku);
    $xmlsku->addChild('Quantity', $quantity);
    $xmlsku->addChild('Price');
    $xmlsku->addChild('SalePrice');
    $xmlsku->addChild('SaleStartDate');
    $xmlsku->addChild('SaleEndDate');

    $payload = $xml->asXML();

    return $this->query($userid, $apikey, 'UpdatePriceQuantity', 0, 100, $payload);
  }

  public function query($user, $key, $action, $offset=0, $limit=100, $payload='') {
    $now = new DateTime();

    $parameters = array(
      'UserID' => $user,
      'Version' => '1.0',
      'Action' => $action,
      'Limit' => $limit,
      'Offset' => $offset,
      'Format' => 'JSON',
      'Timestamp' => $now->format(DateTime::ISO8601)
    );
    ksort($parameters);

    // URL encode the parameters.
    $encoded = array();
    foreach ($parameters as $name => $value) {
        $encoded[] = rawurlencode($name) . '=' . rawurlencode($value);
    }
    $concatenated = implode('&', $encoded);
    $api_key = $key;
    $parameters['Signature'] = rawurlencode(hash_hmac('sha256', $concatenated, $api_key, false));

    $url = 'https://api.sellercenter.lazada.com.ph';

    $queryString = http_build_query($parameters, '', '&', PHP_QUERY_RFC3986);

    // Open cURL connection
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url."?".$queryString);

    // Save response to the variable $data
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION,1);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);

    if (strlen($payload) > 0) {
      curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
      curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type:application/xml'));
    }

    $ret = curl_exec($ch);

    // Close Curl connection
    curl_close($ch);

    return json_decode($ret, true);
  }
}
?>