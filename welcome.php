<?php include '_partials/header.php'; ?>

<div class="hero-unit">
  <h1 class="text-center">Welcome to Zeek!</h1>
  <p class="text-center">"Simply handle your artistic website."</p>
  <hr>
  <form action="enter" class="form-horizontal" role="form">
    <div class="form-group">
      <label class="col-sm-4 control-label">Name</label>
      <div class="col-sm-4">
	<input class="form-control"
	       type="text"
	       name="band">
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

<?php include '_partials/scripts.php'; ?>

<script>
  $('form').on('submit', function(e) {
  console.log('clicked! ' + $(this).serialize());

  $.post('php/init.php', $(this).serialize(),
  function(data) {
  console.log('completed!' + data);
  });

  e.preventDefault();
  });
</script>

<?php include '_partials/footer.php'; ?>
