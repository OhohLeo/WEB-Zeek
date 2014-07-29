<?php include '_partials/header.php'; ?>

<div class="hero-unit">
  <h1 class="text-center">Welcome to Zeek!</h1>
  <p class="text-center">"Simply handle your artistic website."</p>
  <hr>

  <div class="col-sm-8 col-sm-offset-2">
    <div class="alert alert-danger" role="alert"></div>
    <div class="alert alert-success" role="alert"></div>
  </div>

  <form action="enter" class="form-horizontal" role="form">
    <div class="form-group">
      <label class="col-sm-4 control-label">Project Name</label>
      <div class="col-sm-4">
	<input class="form-control"
	       type="text"
           maxlength="25"
	       name="project_name">
      </div>
    </div>
    <div class="form-group">
      <label class="col-sm-4 control-label">Login</label>
      <div class="col-sm-4">
	<input class="form-control"
	       type="login"
	       maxlength="25"
           name="login">
      </div>
    </div>
    <div class="form-group">
      <label class="col-sm-4 control-label">Password</label>
      <div class="col-sm-4">
	<input class="form-control"
	       type="password"
           maxlength="25"
	       name="password">
      </div>
    </div>

    <div class="col-sm-offset-8 col-sm-8">
      <button type="submit"
	      class="btn btn-default">Log in</button>
    </div>
  </form>
  <hr>
</div>

<div class="modal fade bs-example-modal-lg"
     tabindex="10"
     role="dialog"
     id="myModal"
     aria-labelledby="myModalLabel"
     aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-body">
	<p class="modal-body"></p>
      </div>
      <div class="modal-footer">
	<button type="button" class="btn btn-success">Yes</button>
	<button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
      </div>
    </div>
  </div>
</div>

<?php include '_partials/scripts.php'; ?>

<script>
  $('input.form-control').maxlength({
    alwaysShow: true,
    threshold: 25,
    warningClass: "label label-success",
    limitReachedClass: "label label-danger"
  });

  $danger = $('div.alert-danger');
  $danger.hide();

  $success = $('div.alert-success');
  $success.hide();

  $('form').on('submit', function(e) {

      $danger.hide();
      $success.hide();

      $.ajax({
        type: "POST",
              url: "php/zeek.php",
              dataType: "json",
              data: {
                   "method": "connect",
                   "params": $(this).serialize(),
                  },
              success: function($result) {
              console.log($result);
              if ($result['action'] == 'new_project') {
                  $('p.modal-body').text(
                      'Do you want to create the new project ?');
                  $('div.modal').modal('show');
              }

             if ($result['success']) {
                 $success.text($result['success'])
                         .show();
                 $danger.hide();
             } else if ($result['error']) {
                 $danger.text($result['error'])
                        .show();
                 $success.hide();
             } else {
                 $('div.alert').fadeOut(300);
             }
          }});

    e.preventDefault();
  });
</script>

<?php include '_partials/footer.php'; ?>
