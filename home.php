<?php include '_partials/header.php'; ?>

<div class="navbar navbar-default navbar-static-top" role="navigation">
  <div class="container">
    <div class="navbar-header">
      <a class="navbar-brand" data-type='Statistics'>Zeek!</a>
    </div>
    <div class="collapse navbar-collapse">
      <div class="row">
        <ul class="nav navbar-nav">
          <li><a data-type='Statistics'>Home</a></li>
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
    <h2 data-type="data-title" style="text-align:center;">Welcome to Zeek!</h1>
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
  (function()
  {
      var $title = $('h2').first();
      var $clickable;

      $.ajax({
        type: 'POST',
              url: "php/zeek.php",
              data: { 'method': 'get_structure' },
              dataType: "html",
              success: function($input)
              {
                  $('ul.nav-sidebar').replaceWith($input);

                  $('a.clickable').on('click', function()
                  {
                      var $this = $(this);
                      var $type = $this.data('type');
                      var $project_id = 1;

                      if ($type != 'Disconnect') {
                          $title.text($type);
                      }

                      $.ajax({
                        type: 'POST',
                              url: "php/zeek.php",
                              data: {
                                'method': 'clicked',
                                'type': $type,
                                'project_id': $project_id
                              },
                              dataType: 'text',
                              success: function($input, $toto)
                              {
                                  console.log(
                                      'length:' + $input.length + 'result:' + $input + $toto);
                                  if ($type == 'Disconnect') {
                                      $('div.dynamic').append($input);
                                  } else {
                                      $('div.dynamic').replaceWith($input);
                                  }
                              },
                              error: function($request, $status, $error)
                              {
                                  $('div.dynamic').replaceWith(
                                      '<div class="dynamic"><h2>'
                                      + $error + '</h2></div>');
                                  console.log($status + ' : ' + $error);
                              }
                      });
                  });
              },
              error: function($request, $status, $error)
              {
                  console.log('error!!' + $status + ' ' + $error);
              }
      });
  })();
</script>

<?php include '_partials/footer.php'; ?>
