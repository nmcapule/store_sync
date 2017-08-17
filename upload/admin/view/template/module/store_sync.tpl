<?php echo $header; ?><?php echo $column_left; ?>
<div id="content">
  <div class="page-header">
    <div class="container-fluid">
      <div class="pull-right">
        <button type="submit" form="form-store-sync" data-toggle="tooltip" title="<?php echo $button_save; ?>" class="btn btn-primary"><i class="fa fa-save"></i></button>
        <a href="<?php echo $cancel; ?>" data-toggle="tooltip" title="<?php echo $button_cancel; ?>" class="btn btn-default"><i class="fa fa-reply"></i></a></div>
      <h1><?php echo $heading_title; ?></h1>
      <ul class="breadcrumb">
        <?php foreach ($breadcrumbs as $breadcrumb) { ?>
        <li><a href="<?php echo $breadcrumb['href']; ?>"><?php echo $breadcrumb['text']; ?></a></li>
        <?php } ?>
      </ul>
    </div>
  </div>
  <div class="container-fluid">
    <?php if ($error_warning) { ?>
    <div class="alert alert-danger"><i class="fa fa-exclamation-circle"></i> <?php echo $error_warning; ?>
      <button type="button" class="close" data-dismiss="alert">&times;</button>
    </div>
    <?php } ?>
    <div class="panel panel-default">
      <div class="panel-heading">
        <h3 class="panel-title"><i class="fa fa-pencil"></i> <?php echo $text_edit; ?></h3>
      </div>
      <div class="panel-body">
        <?php if ($debug != '') { ?>
        <pre><?php echo $debug ?></pre>
        <?php } ?>
        <pre>Disabled</pre>
        <!--
        <ul class="nav nav-tabs">
          <li><a href="#tab-1" data-toggle="tab"><?php echo $tab_general; ?></a></li>
          <li class="active"><a href="#tab-2" data-toggle="tab"><?php echo $tab_lazada; ?></a></li>
        </ul>
        <div class="tab-content">
          <div class="tab-pane active" id="tab-1">
            <div class="well">
              <div class="row">
                <div class="col-sm-12">
                  <div class="form-group">
                    <label class="control-label" for="input-name">Name</label>
                    <input type="text" name="filter_name" value="<?php echo $filter_name; ?>" placeholder="Name" id="input-name" class="form-control" />
                  </div>
                  <div class="form-group">
                    <label class="control-label" for="input-model">Model</label>
                    <input type="text" name="filter_model" value="<?php echo $filter_model; ?>" placeholder="Model" id="input-model" class="form-control" />
                  </div>
                  <button type="button" id="button-filter" class="btn btn-primary pull-right"><i class="fa fa-search"></i> Filter</button>
                </div>
              </div>
            </div>
            <div class="table-responsive">
              <table class="table table-bordered table-hover">
                <thead>
                  <tr>
                    <td class="text-center"><?php if ($sort == 'name') { ?>
                      <a href="<?php echo $sort_name; ?>" class="<?php echo strtolower($order); ?>">Name</a>
                      <?php } else { ?>
                      <a href="<?php echo $sort_name; ?>">Name</a>
                      <?php } ?>
                    </td>
                    <td class="text-center"><?php if ($sort == 'model') { ?>
                      <a href="<?php echo $sort_model; ?>" class="<?php echo strtolower($order); ?>">Model</a>
                      <?php } else { ?>
                      <a href="<?php echo $sort_model; ?>">Model</a>
                      <?php } ?>
                    </td>
                    <td class="text-center"><?php if ($sort == 'quantity') { ?>
                      <a href="<?php echo $sort_quantity; ?>" class="<?php echo strtolower($order); ?>">Quantity</a>
                      <?php } else { ?>
                      <a href="<?php echo $sort_quantity; ?>">Quantity</a>
                      <?php } ?>
                    </td>
                    <td class="text-center" style="width:128px"><?php if ($sort == 'lz_sku') { ?>
                      <a href="<?php echo $sort_lz_status; ?>" class="<?php echo strtolower($order); ?>">Lazada Upload Status</a>
                      <?php } else { ?>
                      <a href="<?php echo $sort_lz_status; ?>">Lazada Upload Status</a>
                      <?php } ?>
                    </td>
                    <td class="text-center" style="width:96px"><?php if ($sort == 'lz_sync_status') { ?>
                      <a href="<?php echo $sort_lz_sync_status; ?>" class="<?php echo strtolower($order); ?>">Action</a>
                      <?php } else { ?>
                      <a href="<?php echo $sort_lz_sync_status; ?>">Action</a>
                      <?php } ?>
                    </td>
                  </tr>
                </thead>
                <tbody>
                  <?php if ($products) { ?>
                  <?php foreach ($products as $product) { ?>
                  <tr>
                    <td class="text-left"><?php echo $product['name'] ?></td>
                    <td class="text-center"><?php echo $product['model'] ?></td>
                    <td class="text-center">
                      <?php if ($product['lz_quantity'] != $product['quantity']) {?>
                        <span class="text-warning"><?php echo $product['lz_quantity'] ?></span> &raquo;
                      <?php } ?>
                      <?php echo $product['quantity'] ?>
                    </td>
                    <td class="text-left"><?php echo $product['lz_status'] ?></td>
                    <td class="text-center ostatus">
                      <?php if ($product['lz_quantity'] == '') { ?>
                        <button class="btn btn-default oupload" name="<?php echo $product['model']?>">
                          <i class="fa fa-upload" aria-hidden="true"></i> Upload
                        </button>
                      <?php } else if ($product['lz_status'] == 'ERR00: No image') { ?>
                        <button class="btn btn-default osyncimageprice" name="<?php echo $product['model']?>">
                          <i class="fa fa-upload text-warning" aria-hidden="true"></i> Image
                        </button>
                      <?php } else if ($product['lz_status'] == 'ERR03: Not active') { ?>
                        <button class="btn btn-default osyncimageprice" name="<?php echo $product['model']?>">
                          <i class="fa fa-upload text-warning" aria-hidden="true"></i> Update
                        </button>
                      <?php } else if ($product['lz_quantity'] != $product['quantity']) { ?>
                        <button class="btn btn-default osync" name="<?php echo $product['model']?>">
                          <i class="fa fa-refresh text-warning" aria-hidden="true"></i> Update
                        </button>
                      <?php } else { ?>
                        <i class="fa fa-check-circle text-success" aria-hidden="true"></i> Ok
                      <?php } ?>
                    </td>
                  </tr>
                  <?php } ?>
                  <?php } ?>
                </tbody>
              </table>
            </div>
            <div class="row">
              <div class="col-sm-6 text-left"><?php echo $pagination; ?></div>
            </div>
            <div class="well">
              <div class="row">
                <button class="col-sm-12 btn btn-default oall">
                  <i class="fa fa-upload" aria-hidden="true"></i> Fix Everything
                </button>
                <pre class="debug">Log goes here</pre>
                <pre class="response">Response goes here</pre>
              </div>
            </div>
          </div>
          <div class="tab-pane" id="tab-2">
            <form action="<?php echo $action; ?>" method="post" enctype="multipart/form-data" id="form-featured" class="form-horizontal">
              <div class="form-group">
                <label class="col-sm-2 control-label" for="input-username"><?php echo $entry_username ?></label>
                <div class="col-sm-10">
                  <input type="text" name="store_sync_lzusername" value="<?php echo $store_sync_lzusername?>" placeholder="<?php echo $entry_username ?>" id="input-username" class="form-control"/>
                </div>
              </div>
              <div class="form-group">
                <label class="col-sm-2 control-label" for="input-apikey"><?php echo $entry_apikey ?></label>
                <div class="col-sm-10">
                  <input type="text" name="store_sync_lzapikey" value="<?php echo $store_sync_lzapikey?>" placeholder="<?php echo $entry_apikey ?>" id="input-apikey" class="form-control"/>
                </div>
              </div>
              <div class="form-group row">
                <div class="col-sm-12 text-right">
                  <a class="btn btn-default" href="<?php echo $sync ?>">Sync Now</a>
                  <div>Synced Since: <?php echo $store_sync_lzlast_sync ?></div>
                </div>
              </div>
            </form>
          </div>
        </div>
        -->
      </div>
    </div>
  </div>
</div>
<?php echo $footer; ?>

<script type="text/javascript">
$('table tr td.oquant input').on("keyup",function(){

  var value = $(this).val();
  var sku = $(this).attr("name");

  $.ajax({
      url: 'index.php?route=module/store_sync/saveoquantity&token=<?php echo $token; ?>&value='+value+'&sku='+sku,
      dataType: 'json',
      success: function(t) {
        console.log(t);
        //  $('tr.'+id+' td.oquant').css("background","rgb(164, 255, 66)");
        //   $("tr."+id+" div.currentquantity").html(t.quantity);
        //   $("tr."+id+" div.currentquantity").css("font-size","16px");
      }
  });
});
$('table tr td.ostatus button.osync').on('click', function() {
  $(this).attr('disabled', 'disabled');
  $(this).text('Loading...');

  var sku = $(this).attr('name');
  var btn = $(this);

  $.ajax({
      url: 'index.php?route=module/store_sync/saveosync&token=<?php echo $token; ?>&sku='+sku,
      dataType: 'json',
      success: function(t) {
        console.log(t);
        btn.html('<i class="fa fa-check-circle text-success" aria-hidden="true"></i> Synced');
      },
      error: function() {
        btn.html('<i class="fa fa-check-circle text-warning" aria-hidden="true"></i> Fail');
      }
  });
});
$('table tr td.ostatus button.osyncimageprice').on('click', function() {
  $(this).attr('disabled', 'disabled');
  $(this).text('Loading...');

  var sku = $(this).attr('name');
  var btn = $(this);

  $.ajax({
      url: 'index.php?route=module/store_sync/saveoimageprice&token=<?php echo $token; ?>&sku='+sku,
      dataType: 'json',
      success: function(t) {
        console.log(t);
        btn.html('<i class="fa fa-check-circle text-success" aria-hidden="true"></i> Synced');
      },
      error: function() {
        btn.html('<i class="fa fa-check-circle text-warning" aria-hidden="true"></i> Fail');
      }
  });
});
$('table tr td.ostatus button.oupload').on('click', function() {
  $(this).attr('disabled', 'disabled');
  $(this).text('Loading...');

  var sku = $(this).attr('name');
  var btn = $(this);

  $.ajax({
      url: 'index.php?route=module/store_sync/saveoupload&token=<?php echo $token; ?>&sku='+sku,
      dataType: 'json',
      success: function(t) {
        console.log(t);
        btn.html('<i class="fa fa-check-circle text-success" aria-hidden="true"></i> Synced');
      },
      error: function() {
        btn.html('<i class="fa fa-check-circle text-warning" aria-hidden="true"></i> Fail');
      }
  });
});
$('button.oall').on('click', function() {
  $(this).attr('disabled', 'disabled');
  $(this).text('Loading...');

  var sku = $(this).attr('name');
  var btn = $(this);

  function cbhell(i, p, all) {
    if (i >= all.length) {
      $('pre.debug').text('finished ' + all.length);
      return;
    }

    $('pre.debug').text('on ' + i + ' out of ' + all.length + ':\n' + JSON.stringify(p, undefined, 2));

    // setTimeout(function() {cbhell(i+1, all[i+1], all);}, (p['lz_status'] == 'SUCC: Active')?1000:0.1);

    // If pending update, do not update!
    if (p['lz_status'] == 'Pending Lazada Sync') {
      cbhell(i+1, all[i+1], all);
      return;
    }

    // If already active, do not update!
    if (p['lz_status'] == 'SUCC: Active') {
      cbhell(i+1, all[i+1], all);
      return;
    }

    var url = 'index.php?route=module/store_sync/saveoimageprice&token=<?php echo $token; ?>&sku='+p['model'];
    if (p['lz_status'] == 'SUCC: Active') {
      url = 'index.php?route=module/store_sync/saveoquantity&token=<?php echo $token; ?>&sku='+p['model'];
    }

    // Only update non actives.
    $.ajax({
        url: url,
        dataType: 'json',
        success: function(t) {
          $('pre.response').text(JSON.stringify(t,undefined,2));
          cbhell(i+1, all[i+1], all);
        },
        error: function(e) {
          console.log(e);
          $('pre.response').text(JSON.stringify(e,undefined,2));
          cbhell(i+1, all[i+1], all);
        }
    });
  }

  function cbhell_level1() {
    $.ajax({
        url: 'index.php?route=module/store_sync/getproducts&token=<?php echo $token; ?>',
        dataType: 'json',
        success: function(t) {
          console.log(t);
          $('pre.debug').text(JSON.stringify(t, undefined, 2));
          cbhell(0, t[0], t);
          btn.html('<i class="fa fa-check-circle text-success" aria-hidden="true"></i> Done?');
        },
        error: function(e) {
          $('pre.debug').text(JSON.stringify(e, undefined, 2));
          btn.html('<i class="fa fa-check-circle text-warning" aria-hidden="true"></i> Fail');
        }
    });
  }

  // Sync first
  $.ajax({
      url: 'index.php?route=module/store_sync/saveosyncall&token=<?php echo $token; ?>',
      dataType: 'json',
      success: function(t) {
        console.log(t);
        $('pre.debug').text(JSON.stringify(t, undefined, 2));
        cbhell_level1(0, t[0], t);
        btn.html('<i class="fa fa-check-circle text-success" aria-hidden="true"></i> Downsync Done. Please wait.');
      },
      error: function(e) {
        $('pre.debug').text(JSON.stringify(e, undefined, 2));
        btn.html('<i class="fa fa-check-circle text-warning" aria-hidden="true"></i> Fail');
      }
  });
});
</script>
<script type="text/javascript"><!--
$('#button-filter').on('click', function() {
  var url = 'index.php?route=module/store_sync&token=<?php echo $token; ?>';

  var filter_name = $('input[name=\'filter_name\']').val();

  if (filter_name) {
    url += '&filter_name=' + encodeURIComponent(filter_name);
  }

  var filter_model = $('input[name=\'filter_model\']').val();

  if (filter_model) {
    url += '&filter_model=' + encodeURIComponent(filter_model);
  }

  location = url;
});
//--></script>
