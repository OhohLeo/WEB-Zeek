<?php

/* we check the session */
session_start();

$login        = $_SESSION["login"];
$project_name = $_SESSION["project_name"];
$project_id   = $_SESSION["project_id"];
$start_ts     = $_SESSION["start_ts"];

/* if one field is not define : do not authorize to display the
 * page */
if (!(isset($login)
      and isset($project_name)
      and isset($project_id)
      and isset($start_ts))) {

    include '_partials/scripts.php';
?>
<script>
    $(location).attr('href', 'index.php');
</script>
<?php
    die();
}

/* we regenerate session id each 5 minutes */
if ((time() - $start_ts) >= 300) {

    session_regenerate_id(true);

    $_SESSION["start_ts"] = time();
}

include '_partials/header.php';

?>

<div class="navbar navbar-default navbar-static-top" role="navigation">
  <div class="container">
    <div class="navbar-header">
    <a class="navbar-brand" data-type='Home'><?php echo "$project_name";?></a>
    </div>
    <div class="collapse navbar-collapse">
      <div class="row">
        <ul class="nav navbar-nav">
          <li><a class="clickable" data-type='Home'>Home</a></li>
          <li><a class="clickable" data-type='Configuration'>Configuration</a></li>
        </ul>

	<ul class="nav navbar-nav pull-right">
	  <li><a class="clickable" data-type='Disconnect'>disconnect</a></li>
	</ul>
      </div>
    </div>
  </div>
</div>

<div class="container-fluid">
  <div class="col-sm-3 col-md-2 sidebar">
    <ul class="nav nav-sidebar">
    </ul>
  </div>

  <div class="col-sm-2 col-sm-offset-2 col-md-8 col-md-offset-0 main">
    <div class="alert alert-danger text-center" role="alert"></div>
    <div class="alert alert-success text-center" role="alert"></div>
    <h2 data-type="data-title" style="text-align:center;">Welcome to Zeek,
         <b><?php echo $login; ?></b>!</h1>
    <hr>
    <div class="dynamic"></div>
    <hr>
  </div>
</div>

<div class="modal fade bs-example-modal-lg"
     tabindex="10"
     role="dialog"
     id="myModal"
     aria-labelledby="myModalLabel"
     aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
    <h3 class="modal-body" style="text-align:center;"></h3>
    <div class="modal-body">
    </div>
    <div class="modal-footer">
  	<button type="button" class="btn btn-success btn-modal">Yes</button>
    <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
      </div>
    </div>
  </div>
</div>


<?php include '_partials/scripts.php'; ?>

<script>
  var $danger = $('div.alert-danger');
  $danger.hide();

  var $success = $('div.alert-success');
  $success.hide();

  var $send_request = (function($data, $next_action) {
      $.ajax({
        "type": "POST",
        "url": "input.php",
        "data": $data,
        "dataType":"json",
        "success": function($result) {
            if ($result["success"]) {
                $danger.hide();
                $success.text($result["success"]).show();
            } else if ($result["error"]) {
                $("div.modal").modal("hide");
                $success.hide();
                $danger.text($result["error"]).show();
            } else if ($result['replace']) {
                $('div.dynamic').replaceWith($result['replace']);
            } else if ($result['append']) {
                $('div.dynamic').append($result['append']);
            } else {
                $('div.modal').modal("hide");
                $success.hide();
                $danger.text("unhandled result!").show();
            }

            if ($next_action) {
                $next_action();
            }
        },
        "error": function($request, $status, $error) {
            $danger.text($status + ' : ' + $error);
            $danger.show();
        },
      });
  });

  $(document).ready(function() {
    var $title = $('h2').first();
    var $clickable;

    $.ajax({
      type: 'POST',
      url: "input.php",
      data: {
        'method': 'get_structure'
      },
      dataType: "html",
      success: function($input)
      {
          $('ul.nav-sidebar').replaceWith($input);

          $('a.clickable').on('click', function($e) {
              $e.preventDefault();
              $danger.hide();
              $success.hide();

              var $type = $(this).data('type');

              if ($type == 'Home') {
                  $title.text('Welcome to Zeek!');
              } else if ($type != 'Disconnect') {
                  $title.text($type);
              }

              $send_request({
                    "method": 'clicked',
                    "type": $type });
          });
      },
      error: function($request, $status, $error)
      {
          $danger.text($status + ' : ' + $error);
          $danger.show();
      }
    });
  }());
</script>

<?php include '_partials/footer.php'; ?>
