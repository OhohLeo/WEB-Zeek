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
          <li><a data-type='Configuration'>Configuration</a></li>
        </ul>

	<ul class="nav navbar-nav pull-right">
	  <li><a data-type='Disconnect'>disconnect</a></li>
	</ul>
      </div>
    </div>
  </div>
</div>

<div class="container-fluid">
  <div class="col-sm-3 col-md-2 sidebar">
    <ul class="nav nav-sidebar">
      <li><a data-type='Main'>Main</a></li>
      <li><a data-type='News'>News</a></li>
      <li><a data-type='Events'>Events</a></li>
      <li><a data-type='Artists'>Artists</a></li>
      <li><a data-type='Images'>Images</a></li>
      <li><a data-type='Albums'>Albums</a></li>
      <li><a data-type='Musics'>Musics</a></li>
      <li><a data-type='Videos'>Videos</a></li>
      <li><a data-type='Statistics'>Statistics</a></li>
    </ul>
  </div>

  <div class="col-sm-2 col-sm-offset-2 col-md-5 col-md-offset-0 main">
    <h1 data-type="data-title" style="text-align:center;">Statistics</h1>
    <hr>
    <div class="dynamic"></div>
    <hr>
  </div>
</div>

<?php include '_partials/scripts.php'; ?>

<script>
  (function()
  {
  var title = $('h1').first();

  $('a').on('click', function()
  {
  var $this = $(this);
  var type = $this.data('type');

  <!-- on fixe la valeur du titre -->
  title.text(type);

  var url = type.toLowerCase()  + '.html';
  console.log(url);

  var res = $.ajax(
  {
  type: 'GET',
  url: url,
  dataType: 'text',
  success: (function(data, status, request)
  {
  $('div.dynamic').replaceWith(data);

  (function() {
  var dd = $('dd');
  console.log(dd);
  dd.hide();

  $('dt').on('mouseenter', function()
  {
  $(this).next()
  .slideDown(200)
  .siblings('dd')
  .slideUp(300);
  });

  })();
  }),
  error:  (function(request, status, error)
  {
  $('div.dynamic').replaceWith(
  '<div class="dynamic"><h2>' + error + '</h2></div>');
  console.log('no : ' + status + ' ' + error);
  }),
  });

  console.log(res);
  });
  })();
</script>

<?php include '_partials/footer.php'; ?>
