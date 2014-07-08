<?php include '_partials/header.php'; ?>

<div class="hero-unit">
  <h1 class="text-center">Welcome to Zeek!</h1>
  <p class="text-center">"Simply handle your artistic website."</p>
  <hr>

  <div class="col-sm-8 col-sm-offset-2">
    <div class="alert alert-danger" role="alert"></div>
  </div>

  <form action="enter" class="form-horizontal" role="form">
    <div class="form-group">
      <label class="col-sm-4 control-label">Project Name</label>
      <div class="col-sm-4">
	<input class="form-control"
	       type="text"
	       name="dname">
      </div>
    </div>
    <div class="form-group">
      <label class="col-sm-4 control-label">Login</label>
      <div class="col-sm-4">
	<input class="form-control"
	       type="login"
	       name="login">
      </div>
    </div>
    <div class="form-group">
      <label class="col-sm-4 control-label">Password</label>
      <div class="col-sm-4">
	<input class="form-control"
	       type="password"
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
	<button type="button" class="btn btn-success">Yep</button>
	<button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
      </div>
    </div>
  </div>
</div>

<?php include '_partials/scripts.php'; ?>

<script>
  alert = $('div.alert');
  alert.hide();

  $('form').on('submit', function(e) {
    $.post('php/init.php', $(this).serialize(),
           function(result) {
             console.log(result);
             if (result) {
               alert.text(result).fadeIn(300);
             } else {
               alert.fadeOut(300);
               $('p.modal-body').text(
                 'Do you want to create the new project ?');
               $('div.modal').modal('show');
             }

             $('input').val('');
           });

    e.preventDefault();
  });
</script>

<?php include '_partials/footer.php'; ?>
