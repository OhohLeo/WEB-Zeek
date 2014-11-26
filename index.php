<?php include 'default/header.php'; ?>

<div id="index">
  <h1>Welcome to Zeek!</h1>
  <p>"Simply administrate websites."</p>
  <hr>

 <div id="alert-success"></div>
 <div id="alert-error"></div>

  <form action="enter">
    <label>Project Name</label>
	<input class="form-control"
	       type="text"
           maxlength="25"
	       name="project_name"
           required>
    <label>Login</label>
	<input class="form-control"
	       type="login"
	       maxlength="25"
           name="login"
           required>
    <label>Password</label>
	<input class="form-control"
	       type="password"
           maxlength="25"
	       name="password"
           required>
    <button type="submit"
	      class="btn btn-default">Log in</button>
  </form>
  <hr>
</div>

<?php include 'default/scripts.php'; ?>

<script>
  /* $('input.form-control').maxlength({ */
  /*   alwaysShow: true, */
  /*   threshold: 25, */
  /*   warningClass: "label label-success", */
  /*   limitReachedClass: "label label-danger" */
  /* }); */

  $danger = $('div.alert-error');
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

<?php include 'default/footer.php'; ?>
