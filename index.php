<?php include '_partials/header.php'; ?>

<div class="hero-unit">
  <h1 class="text-center">Welcome to Zeek!</h1>
  <p class="text-center">"Simply administrate websites."</p>
  <hr>

  <div class="col-sm-8 col-sm-offset-2">
    <div class="alert alert-danger text-center" role="alert"></div>
    <div class="alert alert-success text-center" role="alert"></div>
  </div>

  <form action="enter" class="form-horizontal" role="form">
    <div class="form-group">
      <label class="col-sm-4 control-label">Project Name</label>
      <div class="col-sm-4">
	<input class="form-control"
	       type="text"
           maxlength="25"
	       name="project_name"
           required>
      </div>
    </div>
    <div class="form-group">
      <label class="col-sm-4 control-label">Login</label>
      <div class="col-sm-4">
	<input class="form-control"
	       type="login"
	       maxlength="25"
           name="login"
           required>
      </div>
    </div>
    <div class="form-group">
      <label class="col-sm-4 control-label">Password</label>
      <div class="col-sm-4">
	<input class="form-control"
	       type="password"
           maxlength="25"
	       name="password"
           required>
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

      $handle_rsp = function($result) {
          if ($result['success']) {
              $danger.hide();
              $success.text($result['success'])
                      .show();
          } else if ($result['error']) {
              $('div.modal').modal('hide');
              $success.hide();
              $danger.text($result['error'])
                     .show();
          } else if ($result['redirect']) {
              $(location).attr('href', $result['redirect']);
          } else {
              console.log($result);
              $('div.alert').fadeOut(300);
          }
      };

      console.log("ok user add!!!" + $(this).serialize());


      $.ajax({
        type: "POST",
              url: "input.php",
              dataType: "json",
              data: {
                   "method": "connect",
                   "params": $(this).serialize(),
                  },
              success: function($result) {

              if ($result['action'] == 'project_create') {
                  $('p.modal-body').text(
                      'Do you want to create the new project ?');
                  $('div.modal').modal('show');

                  $('button.btn-success').on('click', function() {
                      $.ajax({
                        type: "POST",
                              url: "input.php",
                              dataType: "json",
                              data: {
                                  "method": "project_create",
                                  "project_name": $("input:text[name=project_name]").val(),
                                  },
                              success: function($result) {
                                return $handle_rsp($result);
                              }
                      });
                  });
              }

              $handle_rsp($result);
          }});

    e.preventDefault();
  });
</script>

<?php include '_partials/footer.php'; ?>
