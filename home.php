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
    include 'default/scripts.php';
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

include 'default/header.php';
?>
<link rel="stylesheet" href="css/zeek_home.css">
</head>
<body>
    <nav id="nav_bar">
	<h1 id="title"><?php echo "$project_name";?></h1>
	<ul>
	    <li id="home" class="menu">Home</li>
	    <li id="edit" class="menu">Edit</li>
	    <li id="test" class="menu">Test</li>
	    <li id="deploy" class="menu">Deploy</li>
	    <li id="configuration" class="menu">Configuration</li>
	    <li id="help" class="menu">Help</li>
	    <li id="disconnect" class="menu">Disconnect</li>
	</ul>
    </nav>

    <nav id="nav_data">
	<ul class="sidebar">
	</ul>
    </nav>

    <div id="action">
	<div id="alert-success"></div>
	<div id="alert-error"></div>
	<hr>
	<div id="home" class="menu">
	    <label>Project name
		<input type="text"
		       class="form-control"
		       name="project_name"
		       value=<?php echo $project_name; ?>>
	    </label>
	</div>
	<div id="edit" class="menu">
	    <h2>Edit</h2>
	    <nav id="edit">
		<button class="edit html">html</button>
		<button class="edit css">css</button>
		<button class="edit javascript">javascript</button>
	    </nav>
	    <div id="editor">
		function foo(items) {
		var x = "All this is syntax highlighted";
		return x;
		}
	    </div>
	    <button id="import">Import</button>
	    <button id="export">Export</button>
	</div>
	<div id="test" class="menu"><h2>test</h2></div>
	<div id="deploy" class="menu"><h2>deploy</h2></div>
	<div id="configuration" class="menu">
	    <div class="config">
		<h3>Add new user</h3>
		<div class="boxed-group">
		    <form id="user_add" role="form">
			<label>user e-mail address</label>
			<input type="email" class="form-control" name="email"
			       placeholder="enter e-mail"></input>
			<input type="submit" class="btn-block btn-success">
		    </form>
		</div>
	    </div>
	    <hr>
	    <div class="config">
		<h3>Change password</h3>
		<div class="boxed-group">
		    <form id="user_change_password" role="form">
			<label>old password</label>
			<input type="password"
			       class="form-control"
			       name="password_old"
			       placeholder="enter old password"></input>
			<label>new password</label>
			<input type="password"
			       class="form-control"
			       name="password_new"
			       placeholder="enter new password"></input>
			<label>type new password again</label>
			<input type="password"
			       class="form-control"
			       placeholder="retape new password"></input>
			<input type="submit"
			       class="btn-block btn-success">
		    </form>
		</div>
	    </div>
	    <hr>
	    <div class="config">
		<h3>Clean all data</h3>
		<div class="boxed-group danger-zone">
		    <button id="data_clean" class="danger">DELETE all stored data</button>
		</div>
	    </div>
	    <hr>
	    <div class="config">
		<h3>Delete current project</h3>
		<div class="boxed-group danger-zone">
		    <button id="project_delete" class="danger">DELETE this project</button>
		</div>
	    </div>
	</div>
	<div id="help" class="menu"><p>help</p></div>
	<div id="disconnect" class="menu">
	    <h2>Are you sure you want to disconnect from Zeek ?</h2>
	    <button id="disconnect" class="danger">Disconnect</button>
	</div>
	<div id="dynamic" class="menu"></div>
	<hr>
    </div>
    </div>
</body>
<?php include 'default/scripts.php'; ?>
<script src="js/ace-builds/src-min-noconflict/ace.js"></script>
<script src="js/zeek_home.js"></script>
<?php include 'default/footer.php'; ?>
