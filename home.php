<?php

/* we check the session */
session_start();

$start_ts     = $_SESSION["start_ts"];

$login        = $_SESSION["login"];
$global_path  = $_SESSION["global_path"];

$project_name = $_SESSION["project_name"];
$project_id   = $_SESSION["project_id"];
$project_url  = $_SESSION["project_url"];
$project_dst  = $_SESSION["project_dst"];
$has_project_path = $_SESSION["has_project_path"];

$piwik_token = $_SESSION["piwik_token"];

$host = $_SERVER[HTTP_HOST];
$request_uri = $_SERVER[REQUEST_URI];
$uri = substr($request_uri, 0, strrpos($request_uri, "/"));

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
	<a href="<?php echo "$project_url";?>">
            <h1 id="title"><?php echo "$project_name";?></h1>
        </a>
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

            <?php

            $is_piwik_installed = false;
            $piwik_config_path = $global_path . "extends/piwik/config/config.ini.php";

            // Is piwik install ?
            // we check if config/config.ini.php is defined
            if (file_exists($piwik_config_path) == false)
            {
            ?>
                <a href="extends/piwik">
                    <button id="piwik_install" class="validate">
                        Install Piwik
                    </button>
                </a>
            <?php
            }
            // Is Piwik fully installed ?
            // we check the status 'installation_in_progress' in config/config.ini.php
            else if (strpos(file_get_contents($piwik_config_path), "installation_in_progress") > 0)
            {
            ?>
                <a href="extends/piwik">
                    <button id="piwik_complete_install" class="warning">
                        Complete Piwik Installation
                    </button>
                </a>
            <?php
            }
            else if ($piwik_token == "")
            {
                $is_piwik_installed = true;
            ?>
                <h3>Piwik Statistics</h3>
                <label>Enter piwik token</label>
                <input class="piwik_set_token"
                       type="text"
                       placeholder="piwik authentification token">
            <?php
            }
            else
            {
            ?>
                <iframe id="piwik_statistics" src="http://<?php echo "$host$uri"; ?>/extends/piwik/index.php?module=Widgetize&action=iframe&moduleToWidgetize=Dashboard&actionToWidgetize=index&idSite=1&period=week&date=yesterday&token_auth=<?php echo "$piwik_token"; ?>" frameborder="0" marginheight="0" marginwidth="0" width="100%" height="100%"></iframe>
        <?php
            }
        ?>
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
            <h3>Project url</h3>
	    <input id="project_set_url"
                   type="text"
		   value=<?php echo $project_url; ?>>
            <h3>Project destination</h3>
	    <input id="project_set_dst"
                   type="text"
		   value=<?php echo $project_dst; ?>>
            <h3>Project options</h3>
            <table class="center" id="options_deploy"></table>
            <button id="deploy_validate" class="danger">DEPLOY</button>
        </div>
	<div id="configuration" class="menu">
	    <div class="config">
		<h3>Project</h3>
		<div class="config-group danger-zone">
                    <p>
                        <label>Change project name</label>
                        <input id="project_set_name"
                               type="text"
		               value=<?php echo $project_name; ?>>
		    </p>
                    <button id="project_delete" class="danger">DELETE this project</button>
		</div>
	    </div>
             <hr>
	    <div class="config">
		<h3>Password</h3>
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
	    <div class="config">
		<h3 id="user">User</h3>
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
            <?php
            if ($piwik_token != "")
            {
                $is_piwik_installed = true;
            ?>
	    <div class="config">
		<h3>Piwik</h3>
		<div class="config-group danger-zone">
                    <p>
                        <input class="piwik_token" type="text" placeholder="authentification token">
                    </p>
                </div>
	    </div>
	    <hr>
            <?php
            }
            ?>
	    <div class="config">
		<h3>Zeek</h3>
		<div class="config-group danger-zone">
                    <p>
                        <label>Enable</label>
                        <input type="checkbox" id="structure_enabled"">
                    </p>
                    <?php if ($has_project_path == false) { ?>
                    <div id="structure_enabled">
                        <p>
                            <label>Expert mode</label>
                            <input type="checkbox" id="expert_mode">
                        </p>
		        <button id="structure_modify" class="danger">MODIFY</button>
                    </div>
                    <?php } ?>
		</div>
	    </div>
	    <hr>
	    <div class="config">
		<h3>Edit</h3>
		<div class="config-group danger-zone">
                    <select id="file_type_proposed"></select>
                    <table class="center" id="file_type_accepted"></table>
		</div>
	    </div>
	    <hr>
	    <div class="config">
		<h3>Contents</h3>
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
		<h3>Test</h3>
		<div class="config-group danger-zone">
                    <table class="center" id="options_test"></table>
                </div>
	    </div>
	</div>
	<div id="disconnect" class="menu">
	    <h2>Are you sure you want to disconnect from Zeek ?</h2>
	    <button id="disconnect" class="danger">Disconnect</button>
	</div>
        <?php if ($has_project_path == false) { ?>
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
<script type="text/javascript" src="js/jquery-ui.min.js"></script>
<script type="text/javascript" src="js/ace/ace.js"></script>
<script type="text/javascript" src="js/mustache.js"></script>
<script type="text/javascript" src="js/vendor/jquery.ui.widget.js"></script>
<script type="text/javascript" src="js/jquery.iframe-transport.js"></script>
<script type="text/javascript" src="js/jquery.fileupload.js"></script>
<script type="text/javascript" src='js/spectrum.js'></script>
<script type="text/javascript" src='js/JSON.safe.js'></script>
<script type="text/javascript" src='js/dropzone.min.js'></script>
<script type="text/javascript" src="js/ckeditor/ckeditor.js"></script>
<script type="text/javascript" src="js/zeek_home.js"></script>
<?php
if ($is_piwik_installed)
{
?>
<script>
$(document).ready(function() {
    $input_validator(
        ".piwik_set_token",
        function()
        {
            console.log("CLIK!!")
        })();
});
</script>
<?php
}

include 'default/footer.php';
?>
