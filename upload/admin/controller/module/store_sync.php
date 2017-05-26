<?php
class ControllerModuleStoreSync extends Controller {
  private $error = array();

  public function index() {
    $this->load->language('module/store_sync');
    $this->load->model('setting/setting');
    $this->load->model('catalog/product');
    $this->load->model('tool/store_sync');

    $this->model_tool_store_sync->setup();

    $this->handlepost();
    $this->handlesync();

    $data = array();
    $this->setlanguage($data);
    $this->setfilter($data);

    $data['breadcrumbs'] = array();
    $data['breadcrumbs'][] = array(
      'text' => $this->language->get('text_home'),
      'href' => $this->url->link('common/dashboard', 'token=' . $this->session->data['token'], 'SSL')
    );
    $data['breadcrumbs'][] = array(
      'text' => $this->language->get('text_module'),
      'href' => $this->url->link('extension/module', 'token=' . $this->session->data['token'], 'SSL')
    );
    $data['breadcrumbs'][] = array(
      'text' => $this->language->get('heading_title'),
      'href' => $this->url->link('module/store_sync', 'token=' . $this->session->data['token'], 'SSL')
    );

    if (isset($this->error['warning'])) {
      $data['error_warning'] = $this->error['warning'];
    } else {
      $data['error_warning'] = '';
    }

    if (isset($this->request->post['store_sync_status'])) {
      $data['store_sync_status'] = $this->request->post['store_sync_status'];
    } else {
      $data['store_sync_status'] = $this->config->get('store_sync_status');
    }

    $url = '';

    if (isset($this->request->get['sort'])) {
      $data['sort'] = $this->request->get['sort'];
    } else {
      $data['sort'] = 'name';
    }

    if (isset($this->request->get['order'])) {
      $data['order'] = $this->request->get['order'];
    } else {
      $data['order'] = 'ASC';
    }

    if (isset($this->request->get['page'])) {
      $page = $this->request->get['page'];
    } else {
      $page = 1;
    }

    $data['token'] = $this->request->get['token'];

    if (isset($this->request->get['filter_name'])) {
      $url .= "&filter_name=" . $this->request->get['filter_name'];
    }

    if (isset($this->request->get['filter_model'])) {
      $url .= "&filter_model=" . $this->request->get['filter_model'];
    }

    if (isset($this->request->get['filter_lz_exists'])) {
      $url .= "&filter_lz_exists=" . $this->request->get['filter_lz_exists'];
    }

    if (isset($this->request->get['sort'])) {
      $url .= "&sort=" . $this->request->get['sort'];
    }

    if (isset($this->request->get['order'])) {
      $url .= "&order=" . $this->request->get['order'];
    }

    if (isset($this->request->get['order'])) {
      $url .= "&page=" . $this->request->get['order'];
    }

    if ($data['order'] == 'ASC') {
      $url .= '&order=DESC';
    } else {
      $url .= '&order=ASC';
    }

    $data['action'] = $this->url->link('module/store_sync', 'token=' . $this->session->data['token'], 'SSL');
    $data['cancel'] = $this->url->link('extension/module', 'token=' . $this->session->data['token'], 'SSL');
    $data['sync'] = $this->url->link('module/store_sync', 'token=' . $this->session->data['token'] . '&sync=1', 'SSL');

    $data['sort_name'] = $this->url->link('module/store_sync', 'token=' . $this->session->data['token'] . $url . '&sort=name', 'SSL');
    $data['sort_model'] = $this->url->link('module/store_sync', 'token=' . $this->session->data['token'] . $url . '&sort=model', 'SSL');
    $data['sort_quantity'] = $this->url->link('module/store_sync', 'token=' . $this->session->data['token'] . $url . '&sort=quantity', 'SSL');
    $data['sort_lz_quantity'] = $this->url->link('module/store_sync', 'token=' . $this->session->data['token'] . $url . '&sort=lz_quantity', 'SSL');
    $data['sort_lz_sku'] = $this->url->link('module/store_sync', 'token=' . $this->session->data['token'] . $url . '&sort=lz_sku', 'SSL');

    $data['header'] = $this->load->controller('common/header');
    $data['column_left'] = $this->load->controller('common/column_left');
    $data['footer'] = $this->load->controller('common/footer');

    $start = ($page - 1) * $this->config->get('config_limit_admin');
    $limit = $this->config->get('config_limit_admin');

    $this->setpagination($data);

    $setting = $this->model_setting_setting->getSetting('store_sync');
    if (isset($setting['store_sync_lzusername'])) {
      $data['store_sync_lzusername'] = $setting['store_sync_lzusername'];
    } else {
      $data['store_sync_lzusername'] = '';
    }
    if (isset($setting['store_sync_lzapikey'])) {
      $data['store_sync_lzapikey'] = $setting['store_sync_lzapikey'];
    } else {
      $data['store_sync_lzapikey'] = '';
    }
    if (isset($setting['store_sync_lzlast_sync'])) {
      $data['store_sync_lzlast_sync'] = $setting['store_sync_lzlast_sync'];
    } else {
      $data['store_sync_lzlast_sync'] = '';
    }

    $data['start'] = $start;
    $data['limit'] = $limit;

    $data['products'] = $this->model_tool_store_sync->getProducts($data);

    $data['debug'] = '';

    $this->response->setOutput($this->load->view('module/store_sync.tpl', $data));
  }

  protected function setlanguage(&$data) {
    $data['heading_title'] = $this->language->get('heading_title');

    $data['text_edit'] = $this->language->get('text_edit');
    $data['text_enabled'] = $this->language->get('text_enabled');
    $data['text_disabled'] = $this->language->get('text_disabled');

    $data['tab_general'] = $this->language->get('tab_general');
    $data['tab_lazada'] = $this->language->get('tab_lazada');

    $data['entry_status'] = $this->language->get('entry_status');
    $data['entry_username'] = $this->language->get('entry_username');
    $data['entry_apikey'] = $this->language->get('entry_apikey');

    $data['button_save'] = $this->language->get('button_save');
    $data['button_cancel'] = $this->language->get('button_cancel');

    $this->document->setTitle($this->language->get('heading_title'));
  }

  protected function setfilter(&$data) {
    if (isset($this->request->get['filter_name'])) {
      $data['filter_name'] = $this->request->get['filter_name'];
    } else {
      $data['filter_name'] = null;
    }

    if (isset($this->request->get['filter_model'])) {
      $data['filter_model'] = $this->request->get['filter_model'];
    } else {
      $data['filter_model'] = null;
    }

    if (isset($this->request->get['filter_lz_exists'])) {
      $data['filter_lz_exists'] = $this->request->get['filter_lz_exists'];
    } else {
      $data['filter_lz_exists'] = null;
    }
  }

  protected function setpagination(&$data) {
    if (isset($this->request->get['page'])) {
      $page = $this->request->get['page'];
    } else {
      $page = 1;
    }

    $url = '';

    if (isset($this->request->get['filter_name'])) {
      $url .= "&filter_name=" . $this->request->get['filter_name'];
    }

    if (isset($this->request->get['filter_model'])) {
      $url .= "&filter_model=" . $this->request->get['filter_model'];
    }

    if (isset($this->request->get['filter_lz_exists'])) {
      $url .= "&filter_lz_exists=" . $this->request->get['filter_lz_exists'];
    }

    if (isset($this->request->get['sort'])) {
      $url .= "&sort=" . $this->request->get['sort'];
    }

    if (isset($this->request->get['order'])) {
      $url .= "&order=" . $this->request->get['order'];
    }

    $pagination = new Pagination();
    $pagination->total = $this->model_tool_store_sync->getTotalProducts($data);
    $pagination->page = $page;
    $pagination->limit = $this->config->get('config_limit_admin');
    $pagination->url = $this->url->link('module/store_sync', 'token=' . $this->session->data['token'] . $url . '&page={page}', 'SSL');

    $data['pagination'] = $pagination->render();
  }

  protected function handlepost() {
    if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate()) {
      $this->model_setting_setting->editSetting('store_sync', $this->request->post);

      $this->session->data['success'] = $this->language->get('text_success');

      $this->response->redirect($this->url->link('module/store_sync', 'token=' . $this->session->data['token'], 'SSL'));
    }
  }

  protected function handlesync() {
    if (isset($this->request->get['sync'])) {
      $setting = $this->model_setting_setting->getSetting('store_sync');
      $userid = $setting['store_sync_lzusername'];
      $apikey = $setting['store_sync_lzapikey'];

      // $this->model_tool_store_sync->sync($userid, $apikey);
      $this->model_tool_store_sync->lzSyncProducts($userid, $apikey);

      $setting['store_sync_lzlast_sync'] = (new DateTime())->format('Y-m-d H:i:s');
      $this->model_setting_setting->editSetting('store_sync', $setting);

      $this->response->redirect($this->url->link('module/store_sync', 'token=' . $this->session->data['token'], 'SSL'));
    }
  }

  public function saveoquantity() {
    $this->load->model('tool/store_sync');
    $this->load->model('setting/setting');

    $quantity  = $this->request->get['value'];
    $sku = $this->request->get['sku'];

    $setting = $this->model_setting_setting->getSetting('store_sync');
    $userid = $setting['store_sync_lzusername'];
    $apikey = $setting['store_sync_lzapikey'];

    $result = $this->model_tool_store_sync->savequantity($userid, $apikey, $sku, $quantity);

    $this->response->setOutput(json_encode($result));
  }

  protected function validate() {
    if (!$this->user->hasPermission('modify', 'module/store_sync')) {
      $this->error['warning'] = $this->language->get('error_permission');
    }

    return !$this->error;
  }
}
