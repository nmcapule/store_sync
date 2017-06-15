<?php
class ModelToolStoreSync extends Model {
  public function setup() {
    $sql = "CREATE TABLE IF NOT EXISTS `". DB_PREFIX . "lazada_product` (
      `model` varchar(64) NOT NULL,
      `sku` varchar(64) NOT NULL,
      `status` varchar(64) NOT NULL,
      `quantity` int(4) NOT NULL DEFAULT '0',
      `price` decimal(15,4) NOT NULL DEFAULT '0.0000',
      `url` varchar(256) NOT NULL,
      PRIMARY KEY (`model`),
      KEY `sku` (`sku`)
    )";

    $this->db->query($sql);
  }

  public function getProductModels($data = array()) {
    $products = $this->getProducts($data);

    $ret = array();
    foreach ($products as $p) {
      array_push($ret, array(
          'model' => $p['model'],
          'quantity' => $p['quantity'],
          'lz_quantity' => $p['lz_quantity'],
          'lz_status' => $p['lz_status'],
        ));
    }

    return $ret;
  }

  public function getProducts($data = array()) {
    $sql = "SELECT
              p.product_id as product_id,
              pd.name as name,
              m.name as manufacturer,
              pd.description as description,
              p.model as model,
              p.quantity as quantity,
              p.price as price,
              p.status as status,
              lp.quantity as lz_quantity,
              lp.sku as lz_sku,
              CASE
                WHEN lp.sku IS NULL THEN 'ERR0x: No upload'
                WHEN p.price <= 67 THEN 'ERR0x: Price less than Lazada minimum'
                WHEN LENGTH(pd.description) < 25 THEN 'ERR0x: Product has invalid description'
                ELSE lp.status
              END as lz_status,
              CASE
                WHEN lp.sku IS NULL THEN 1
                WHEN lp.quantity != p.quantity THEN 2
                ELSE 0
              END as lz_sync_status";
    $sql .= " FROM " . DB_PREFIX . "product p";
    $sql .= " LEFT JOIN " . DB_PREFIX . "product_description pd";
    $sql .= "   ON (p.product_id = pd.product_id)";
    $sql .= " LEFT JOIN " . DB_PREFIX . "manufacturer m";
    $sql .= "   ON (p.manufacturer_id = m.manufacturer_id)";
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

    if (!empty($data['filter_lz_desync'])) {
      $sql .= " AND lp.quantity IS NOT NULL AND p.quantity != lp.quantity";
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
      'lz_sync_status',
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

    if (!empty($data['filter_lz_desync'])) {
      $sql .= " AND lp.quantity IS NOT NULL AND p.quantity != lp.quantity";
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

  // savequantity does all of these things to a single sku:
  // 1. Update cached lazada products (table: oc_lazada_product)
  // 2. Update lazada product quantity (savequantity)
  public function savequantity($userid, $apikey, $sku, $quantity) {
    // Make request
    $xml = new SimpleXMLElement("<?xml version=\"1.0\" encoding=\"utf-8\" ?><Request></Request>");
    $xmlskus = $xml->addChild('Product')->addChild('Skus');

    $xmlsku = $xmlskus->addChild('Sku');
    $xmlsku->addChild('SellerSku', $sku);
    $xmlsku->addChild('Quantity', $quantity);

    $payload = $xml->asXML();
    error_log($payload);

    $ret = $this->query($userid, $apikey, 'UpdatePriceQuantity', 0, 100, $payload);
    if (isset($ret['ErrorResponse'])) {
      error_log(print_r($ret['ErrorResponse'], true));
    } else {
      // Save changes to local
      $this->db->query("UPDATE  " . DB_PREFIX . "lazada_product SET quantity = '".(int)$quantity."' WHERE model = '".$sku."'");
    }

    return $ret;
  }

  // calcquantity returns the true quantity of a lazada-opencart product by
  // combining the diff of the cached vs current lazada quantity and
  // diffing to the opencart quantity.
  //
  // Upon returning, the user of the call should always:
  // 1. Update cached lazada products (table: oc_lazada_product)
  // 2. Update opencart product quantity (table: oc_product)
  // 3. Update lazada product quantity (savequantity)
  //
  // See syncquantity() that does all three above.
  public function calcquantity($userid, $apikey, $sku) {
    $ps = $this->getProducts(array('filter_model' => $sku));
    if (count($ps) == 0) {
      return NULL;
    }

    $p = $ps[0];

    // $quantity = get current opencart quantity
    $quantity = $p['quantity'];

    // $lzcached = get cached lazada quantity
    $lzcached = $p['lz_quantity'];

    $lzp = $this->lzProduct($userid, $apikey, $sku);

    if (!$isset(lzp)) {
      return NULL;
    }

    // $lzcurrent = get current lazada quantity
    $lzcurrent = $lzp['quantity'];

    $lzdiff = $lzcached - $lzcurrent;

    return $quantity - $lzdiff;
  }

  // syncquantity does all of these things to a single sku:
  // 1. Update cached lazada products (table: oc_lazada_product)
  // 2. Update opencart product quantity (table: oc_product)
  // 3. Update lazada product quantity (savequantity)
  public function syncquantity($userid, $apikey, $sku, $quantity) {
    // Updates both lazada and cached lazada.
    $this->savequantity($userid, $apikey, $sku, $quantity)

    // Update opencart quantity.
    $this->db->query("UPDATE  " . DB_PREFIX . "product SET quantity = '".(int)$quantity."' WHERE model = '".$sku."'");
  }

  public function sync($userid, $apikey) {
    // Drop everything from cached lazada products table.
    $this->db->query("DELETE FROM " . DB_PREFIX . "lazada_product");

    $products = $this->lzProducts($userid, $apikey);
    $rows = array();

    foreach ($products as $p) {
      $row = join(',', array(
        "'" . $this->db->escape($p['model']) . "'",
        "'" . $this->db->escape($p['sku']) . "'",
        "'" . $this->db->escape($p['status']) . "'",
        $this->db->escape($p['quantity']),
        $this->db->escape($p['price']),
        "'" . $this->db->escape($p['url']) . "'",
      ));

      array_push($rows, '(' . $row . ')');
    }

    // Insert updated lazada products to cached lazada products table.
    $this->db->query("INSERT INTO " . DB_PREFIX . "lazada_product (model, sku, status, quantity, price, url) VALUES " . join(',', $rows));
  }

  public function lzProduct($userid, $apikey, $sku) {
    $rows = array();

    $c = $this->query($userid, $apikey, 'GetProducts', 0, 100, '', $sku);

    foreach ($c['SuccessResponse']['Body']['Products'] as $key => $value) {
      $skus = $value['Skus'][0];

      $shopSku = 'Pending';
      if (isset($skus['ShopSku'])) {
        $shopSku = $skus['ShopSku'];
      }

      $status = 'SUCC: Active';
      if (!isset($skus['Images']) || strlen(implode($skus['Images'])) == 0) {
        $status = 'ERR00: No image';
      } else if ($skus['price'] != round($skus['price'], 0, PHP_ROUND_HALF_UP)) {
        $status = 'ERR01: Price not rounded';
      } else if ($skus['quantity'] == 0) {
        $status = 'ERR02: Zero stock';
      } else if (!isset($skus['Url'])) {
        $status = 'ERR03: Not active';
      }

      $url = '';
      if (isset($skus['Url'])) {
        $url = $skus['Url'];
      }

      $row = array(
        'model' => $skus['SellerSku'],
        'sku' => $shopSku,
        'status' => $status,
        'quantity' => $skus['quantity'],
        'price' => $skus['price'],
        'url' => $url,
      );

      if ($skus['SellerSku'] == $sku) {
        return $row;
      }
    }

    return NULL;
  }

  public function lzProducts($userid, $apikey) {
    $total = $this->getTotalProducts();

    $increment = 500;

    $rows = array();

    for ($offset = 0; $offset < $total; $offset += $increment) {
      $c = $this->query($userid, $apikey, 'GetProducts', $offset, $increment);

      foreach ($c['SuccessResponse']['Body']['Products'] as $key => $value) {
        $skus = $value['Skus'][0];

        $shopSku = 'Pending';
        if (isset($skus['ShopSku'])) {
          $shopSku = $skus['ShopSku'];
        }

        $status = 'SUCC: Active';
        if (!isset($skus['Images']) || strlen(implode($skus['Images'])) == 0) {
          $status = 'ERR00: No image';
        } else if ($skus['price'] != round($skus['price'], 0, PHP_ROUND_HALF_UP)) {
          $status = 'ERR01: Price not rounded';
        } else if ($skus['quantity'] == 0) {
          $status = 'ERR02: Zero stock';
        } else if (!isset($skus['Url'])) {
          $status = 'ERR03: Not active';
        }

        $url = '';
        if (isset($skus['Url'])) {
          $url = $skus['Url'];
        }

        $row = array(
          'model' => $skus['SellerSku'],
          'sku' => $shopSku,
          'status' => $status,
          'quantity' => $skus['quantity'],
          'price' => $skus['price'],
          'url' => $url,
        );

        array_push($rows, $row);
      }
    }

    return $rows;
  }

    // lzSyncProducts syncs product quantities from opencart to lazada.
  public function lzSyncProducts($userid, $apikey) {
    $this->sync($userid, $apikey);

    // Get all opencart products not synced with lazada.
    $products = $this->getProducts(array('filter_lz_desync' => '1'));

    // Update lazada!
    // maximum items per batch is 50!
    $increment = 50;
    $total = count($products);

    for ($offset = 0; $offset < $total; $offset += $increment) {
      // Make quantity update requests!
      $xml = new SimpleXMLElement("<?xml version=\"1.0\" encoding=\"utf-8\" ?><Request></Request>");
      $xmlskus = $xml->addChild('Product')->addChild('Skus');

      for ($i = $offset; $i < $offset + $increment && $i < $total; $i++) {
        $p = $products[$i];

        $xmlsku = $xmlskus->addChild('Sku');
        $xmlsku->addChild('SellerSku', $p['model']);
        $xmlsku->addChild('Quantity', $p['quantity']);
        $xmlsku->addChild('Price');
        $xmlsku->addChild('SalePrice');
        $xmlsku->addChild('SaleStartDate');
        $xmlsku->addChild('SaleEndDate');
      }

      $payload = $xml->asXML();

      $ret = $this->query($userid, $apikey, 'UpdatePriceQuantity', 0, 100, $payload);
      if (isset($ret['ErrorResponse'])) {
        error_log($ret['ErrorResponse']);
      }
    }

    // From lazada to opencart
    $this->sync($userid, $apikey);
  }

  public function lzUploadImage($userid, $apikey, $url) {
    $xml = new SimpleXMLElement("<?xml version=\"1.0\" encoding=\"utf-8\" ?><Request></Request>");
    $xmli = $xml->addChild('Image');
    $xmli->addChild('Url', $url);

    $payload = $xml->asXML();

    $ret = $this->query($userid, $apikey, 'MigrateImage', 0, 100, $payload);

    return $ret;
  }

  public function lzSyncImagePrice($userid, $apikey, $sku) {
    $this->load->model('catalog/product');
    $this->load->model('tool/image');

    $p = $this->getProducts(array('filter_model' => $sku))[0];
    $pi = $this->model_catalog_product->getProductImages($p['product_id']);

    $xml = new SimpleXMLElement("<?xml version=\"1.0\" encoding=\"utf-8\" ?><Request></Request>");
    $xmlproduct = $xml->addChild('Product');

    // Only do these things for inactive products!
    if ($p['lz_status'] != 'SUCC: Active') {
      $xmlattr = $xmlproduct->addChild('Attributes');

      // Remove href links from description.
      $doc = html_entity_decode($p['description']);
      $doc = preg_replace('#<\/?a[^>]*>#', '', $doc);
      $description = htmlentities($doc);

      // Tokenize per paragraph.
      $doc = preg_replace('#<p[^>]*>#', '|', $doc);
      $doc = preg_replace('#&nbsp;#', ' ', $doc);
      $docs = explode('|', $doc);

      // Collect tokens whose length are > 20, presumably a short description.
      $nodes = array();
      foreach ($docs as $item) {
        if (strlen(trim(strip_tags($item))) <= 20) {
          continue;
        }
        array_push($nodes, strip_tags($item));
      }

      // Get first matching token!
      $short_description = $nodes[0];
      $xmlattr->addChild('description', $description);
      $xmlattr->addChild('short_description', $short_description);
    }

    $lzprice = round($p['price'] + (0.0571 * $p['price']), 0, PHP_ROUND_HALF_UP);

    $xmlsku = $xmlproduct->addChild('Skus')->addChild('Sku');
    $xmlsku->addChild('SellerSku', $p['model']);
    $xmlsku->addChild('quantity', $p['quantity']);

    // Only do these things for inactive products!
    if ($p['lz_status'] != 'SUCC: Active') {
      $xmlsku->addChild('price', $lzprice);

      // Only upload images if product does not have image.
      if ($p['lz_status'] == 'ERR00: No image') {
        if (count($pi) > 0) {
          $xmli = $xmlsku->addChild('Images');
          foreach($pi as $im) {
            // Upload image to lazada first.
            $iret = $this->lzUploadImage($userid, $apikey, $this->model_tool_image->resize($im['image'], 500, 500));
            if (isset($iret['ErrorResponse'])) {
              error_log(print_r($iret['ErrorResponse'], true));
              return $iret;
            }

            // Get lazada url.
            $lzim = $iret['SuccessResponse']['Body']['Image']['Url'];

            // Set image to lazada url.
            $xmli->addChild('Image', $lzim);
          }
        }
      }
    }

    $payload = $xml->asXML();

    $ret = $this->query($userid, $apikey, 'UpdateProduct', 0, 100, $payload);
    if (isset($ret['ErrorResponse'])) {
      error_log(print_r($ret['ErrorResponse'], true));
    } else {
      $this->db->query("UPDATE  " . DB_PREFIX . "lazada_product SET quantity = '".$p['quantity']."', status = 'Pending Lazada Sync' WHERE model = '".$sku."'");
    }

    $ret['payload'] = $payload;

    return $ret;
  }

  public function lzCreateProduct($userid, $apikey, $sku) {
    $this->load->model('catalog/product');
    $this->load->model('tool/image');

    $p = $this->getProducts(array('filter_model' => $sku))[0];
    $pi = $this->model_catalog_product->getProductImages($p['product_id']);

    $xml = new SimpleXMLElement("<?xml version=\"1.0\" encoding=\"utf-8\" ?><Request></Request>");
    $xmlproduct = $xml->addChild('Product');
    $xmlproduct->addChild('PrimaryCategory', 5160);
    $xmlproduct->addChild('SPUId');
    $xmlproduct->addChild('AssociatedSku');

    $xmlattr = $xmlproduct->addChild('Attributes');
    $xmlattr->addChild('name', $p['name']);

    // Remove href links from description.
    $doc = html_entity_decode($p['description']);
    $doc = preg_replace('#<\/?a[^>]*>#', '', $doc);
    $description = htmlentities($doc);

    // Tokenize per paragraph.
    $doc = preg_replace('#<p[^>]*>#', '|', $doc);
    $doc = preg_replace('#&nbsp;#', ' ', $doc);
    $docs = explode('|', $doc);

    // Collect tokens whose length are > 20, presumably a short description.
    $nodes = array();
    foreach ($docs as $item) {
      if (strlen(trim(strip_tags($item))) <= 20) {
        continue;
      }
      array_push($nodes, strip_tags($item));
    }

    // Get first matching token!
    $short_description = $nodes[0];
    $xmlattr->addChild('description', $description);
    $xmlattr->addChild('short_description', $short_description);

    // -- NOTE: Default brand is Arduino
    // $xmlattr->addChild('brand', $p['manufacturer']);
    $xmlattr->addChild('brand', 'Arduino');
    $xmlattr->addChild('model', $sku);
    $xmlattr->addChild('warranty', '7 Days');
    $xmlattr->addChild('warranty_type', 'No Warranty');

    $lzprice = round($p['price'] + (0.0571 * $p['price']), 0, PHP_ROUND_HALF_UP);

    $xmlsku = $xmlproduct->addChild('Skus')->addChild('Sku');
    $xmlsku->addChild('SellerSku', $p['model']);
    $xmlsku->addChild('quantity', $p['quantity']);
    $xmlsku->addChild('price', $lzprice);
    $xmlsku->addChild('package_length', 10);
    $xmlsku->addChild('package_height', 10);
    $xmlsku->addChild('package_width', 10);
    $xmlsku->addChild('package_weight', 0.5);
    $xmlsku->addChild('package_content', '1 x ' . $p['name']);
    if (count($pi) > 0) {
      $xmli = $xmlsku->addChild('Images');
      foreach($pi as $im) {
        // Upload image to lazada first.
        $iret = $this->lzUploadImage($userid, $apikey, $this->model_tool_image->resize($im['image'], 500, 500));
        if (isset($iret['ErrorResponse'])) {
          error_log(print_r($iret['ErrorResponse'], true));
          return $iret;
        }

        // Get lazada url.
        $lzim = $iret['SuccessResponse']['Body']['Image']['Url'];

        // Set image to lazada url.
        $xmli->addChild('Image', $lzim);
      }
    }

    $payload = $xml->asXML();

    $ret = $this->query($userid, $apikey, 'CreateProduct', 0, 100, $payload);
    if (isset($ret['ErrorResponse'])) {
      error_log(print_r($ret['ErrorResponse'], true));
    } else {
      $row = join(',', array(
        "'" . $sku . "'",
        "'Pending Lazada Sync'",
        "'Pending Lazada Sync'",
        $p['quantity'],
        $lzprice,
      ));

      $this->db->query("INSERT INTO " . DB_PREFIX . "lazada_product (model, sku, status, quantity, price) VALUES (" . $row . ")");
    }

    $ret['payload'] = $payload;

    return $ret;
  }

  public function query($user, $key, $action, $offset=0, $limit=100, $payload='', $search='') {
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

    if (strlen($search) > 0) {
      $parameters['Search'] = $search;
    }

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
