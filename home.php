<?php

/* we check the session */
session_start();

$login        = $_SESSION["login"];
$project_name = $_SESSION["project_name"];
$project_id   = $_SESSION["project_id"];
$start_ts     = $_SESSION["start_ts"];
$project_path = $_SESSION["project_path"];
$project_dst  =  $_SESSION["project_dst"];

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
<link rel="stylesheet" href="css/jquery-ui.min.css">
<link rel="stylesheet" href="css/jquery-ui.structure.min.css">
<link rel="stylesheet" href="css/jquery-ui.theme.min.css">
<link rel="stylesheet" href="css/jquery.fileupload.css">
<link rel="stylesheet" href="css/jquery.fileupload-ui.css">
<link rel='stylesheet' href='css/spectrum.css' />
<link rel='stylesheet' href='css/dropzone.min.css' />
<link rel="stylesheet" href="css/zeek_home.css">
</head>
<body>
    <nav id="action">
	<h1 id="title"><?php echo "$project_name";?></h1>
	<ul>
	    <li id="home" class="menu">Home</li>
	    <li id="edit" class="menu">Edit</li>
            <li id="contents" class="menu">Contents</li>
	    <li id="test" class="menu">Test</a></li>
	    <li id="deploy" class="menu">Deploy</li>
	    <li id="configuration" class="menu">Configuration</li>
	    <li id="disconnect" class="menu">Disconnect</li>
	</ul>
    </nav>

    <nav id="data">
	<ul id="structure">
	    <li class="data" id="structure_loading">Loading...</li>
	    <!-- {{#structure}} -->
	    <li class="data" style="display:none">{{.}}</li>
	    <!-- {{/structure}} -->
	</ul>
    </nav>

    <div id="action">
	<div class="alert">
    	    <div class="success"></div>
	    <div class="error"></div>
	</div>
	<div id="home" class="menu">
	    <h3>Project name</h3>
	    <input type="text"
		   class="form-control"
		   name="project_name"
		   value=<?php echo $project_name; ?>>
	</div>
	<div id="contents" class="menu">
            <nav id="contents">
                <button id="contents_create" class="validate">Create</button>
            </nav>
            <div id="content_directory">
                <form action="files[]" class="dropzone" id="dropzone"></form>
                <table>
                    <tbody id="contents_list"></tbody>
                </table>
                <button id="content_directory_remove" class="danger">Remove</button>
            </div>
        </div>
	<div id="edit" class="menu">
	    <nav id="edit"></nav>
	    <select id="edit">
		<option>No file found!</option>
	    </select>
	    <div id="edition">
		<div id="editor"></div>
		<button id="file_save">Save</button>
		<button id="file_modify">Modify</button>
                <button id="file_export">
                    <a href="#">Export</a>
                </button>
		<button id="file_delete" class="danger">Delete</button>
	    </div>
	</div>
	<div id="test" class="menu"></div>
	<div id="deploy" class="menu">
            <h3>Project destination</h3>
	    <input id="deploy_dst"
                   type="text"
		   class="form-control"
		   name="deploy_dst"
		   value=<?php echo $project_dst; ?>>
            <h3>Project options</h3>
            <table class="center" id="options_deploy"></table>
            <button id="deploy_validate" class="danger">DEPLOY</button>
        </div>
	<div id="configuration" class="menu">
	    <div class="config">
		<h3 id="user">Add new user</h3>
		<div class="config-group">
                    <div id="users_list"></div>
		    <form id="user_add" role="form">
			<p><input type="email" class="form-control" name="email"
			          placeholder="enter new e-mail adress"></input></p>
			<input type="submit" class="btn-block btn-success validate">
		    </form>
		</div>
	    </div>
	    <hr>
	    <div class="config">
		<h3>Change password</h3>
		<div class="config-group">
		    <form id="user_change_password" role="form">
			<p><input type="password"
			          class="form-control"
			          name="password_old"
			          placeholder="old password"></input></p>
			<p><input type="password"
			          class="form-control"
			          name="password_new"
			          placeholder="new password"></input></p>
			<p><input type="password"
			          class="form-control"
			          placeholder="retape new password"></input></p>
			<input type="submit" class="btn-block btn-success validate">
		    </form>
		</div>
	    </div>
	    <hr>
            <?php if ($project_path == false) { ?>
	    <div class="config">
		<h3>Zeek configuration</h3>
		<div class="config-group danger-zone">
                    <p>
                        <label>Enable</label>
                        <input type="checkbox" id="zeekify_enabled"">
                    </p>
                    <p>
                        <label>Expert mode</label>
                        <input type="checkbox" id="expert_mode">
                    </p>
		    <button id="structure_modify" class="danger">MODIFY</button>
		</div>
	    </div>
	    <hr>
            <?php } ?>
	    <div class="config">
		<h3>Edit configuration</h3>
		<div class="config-group danger-zone">
                    <select id="file_type_proposed"></select>
                    <table class="center" id="file_type_accepted"></table>
		</div>
	    </div>
	    <hr>
	    <div class="config">
		<h3>Contents configuration</h3>
		<div class="config-group danger-zone">
                    <table class="center" id="content_type_accepted"></table>
                    <h3>Add new content type</h3>
                    <form id="content_type_add">
                        <p><input type="text"
			          name="content_name"
			          placeholder="content name"></input></p>
                        <p><input type="text"
			          name="content_directory"
			          placeholder="directory name"></input></p>
                        <p><input type="text"
			          name="content_mime"
			          placeholder="content mime"></input></p>
                        <input type="submit" class="btn-block btn-success validate">
                    </form>
		</div>
	    </div>
	    <hr>
	    <div class="config">
		<h3>Test configuration</h3>
		<div class="config-group danger-zone">
                    <table class="center" id="options_test"></table>
                </div>
	    </div>
	    <hr>
	    <div class="config">
		<h3>Delete project</h3>
		<div class="config-group danger-zone">
		    <button id="project_delete" class="danger">DELETE this project</button>
		</div>
	    </div>
	</div>
	<div id="disconnect" class="menu">
	    <h2>Are you sure you want to disconnect from Zeek ?</h2>
	    <button id="disconnect" class="danger">Disconnect</button>
	</div>
        <?php if ($project_path == false) { ?>
	<div id="structure" class="menu">
            <h3>What is a Zeek structure ?</h3>
            <p>A structure defines the way datas will be stored.</p>
            <p>It could be seen as a table in database representation.</p>
            <p>A structure has a unique name and some attributes linked with.</p>
            <p>The attributes could be seen as each column of the table.</p>
            <h3>How to use a structure ?</h3>
            <p>A structure could be used in all type of files.</p>
            <p>Write as following:</p>
            <textarea id="structure_explain" rows="3" readonly><zeek name="structure_name" limit="5" offset="1">
       some stuff ... {{attribute_name}} ... some stuff
</zeek>
            </textarea>
            <p>In test & deploy mode : "zeekify" argument should be set at true!</p>
            <h3>How to create a new structure ?</h3>
            <p>Push on 'CREATE' button on the left.</p>
            <h3>How to delete a structure ?</h3>
            <p>Select the structure to delete.</p>
            <p>In the new menu displayed, select on 'Remove' button.</p>
            <h3>Validate</h3>
            <p>Validate will replace the old structure with the new one.</p>
            <p>During this process, some data stored can be lost!</p>
            <button id="structure_validate" class="danger">VALIDATE</button>
            <h3>Cancel</h3>
            <p>You can still come back in this configuration mode</p>
            <p>by clicking on Configuration Menu -> Project structure -> Modify</p>
            <button id="structure_cancel" class="danger">Cancel</button>
        </div>
        <?php } ?>
	<div id="dynamic" class="menu">
	    <h2 id="data_set">create new {{name}}</h2>
	    <div id="data_set">
	    </div>
	    <hr>
	    <div id="data_get">
		<table id="data_get" class="table">
		    <thead>
			<tr>
			    <!-- {{#get_head}} -->
			    <th>{{.}}</th>
			    <!-- {{/get_head}} -->
			</tr>
		    </thead>
		    <tbody id="data_get">
		    </tbody>
		</table>
	    </div>
	    <hr>
	</div>
    </div>
    <div id="modal">
    </div>
</body>
<?php include 'default/scripts.php'; ?>
<script src="js/jquery-ui.min.js"></script>
<script src="js/ace/ace.js"></script>
<script src="js/mustache.js"></script>
<script src="js/vendor/jquery.ui.widget.js"></script>
<script src="js/jquery.iframe-transport.js"></script>
<script src="js/jquery.fileupload.js"></script>
<script src='js/spectrum.js'></script>
<script src='js/JSON.safe.js'></script>
<script src='js/dropzone.min.js'></script>
<script src="js/zeek_home.js"></script>
<?php include 'default/footer.php'; ?>
