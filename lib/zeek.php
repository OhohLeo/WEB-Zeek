<?php
/**
 * Copyright (C) 2015  Léo Martin
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
 * Zeek : all the function to handle the website & the backoffice.
 *
 * @package Zeek
 */

class Zeek extends ZeekOutput {

    public $global_path;
    public $piwik_url;

    protected $project_id;
    protected $project_name;
    protected $zlib;

    private $structure_enabled;
    private $plugins_list;
    private $type_list;
    private $content_type_list;
    private $content_directory_list;
    private $mime_validator;

    private $is_demo;

    private $type_simple = array(
        "TITLE"    => array("db_type" => "VARCHAR", "db_size" => 100),
        "TEXT"     => array("db_type" => "LONGTEXT"),
        "INTEGER"  => array("db_type" => "INTEGER"),
        "NUMBER"   => array("db_type" => "REAL"),
        "DATE"     => array("db_type" => "DATE"),
        "TIME"     => array("db_type" => "TIME"),
        "YEAR"     => array("db_type" => "YEAR"),
        "DATETIME" => array("db_type" => "DATETIME"),
    );

    private $type_complex = array(
        "TINYINT" => array(
	    "type" => "number",
	    "min"  => -128,
	    "max"  => 127,
	    "step" => 1),
        "TINYINT_U" => array(
	    "type" => "number",
	    "min"  => 0,
	    "max"  => 255,
	    "step" => 1),
        "SMALLINT" => array(
	    "type" => "number",
	    "min"  => -32768,
	    "max"  => 32767,
	    "step" => 1),
        "SMALLINT_U" => array(
	    "type" => "number",
	    "min"  => 0,
	    "max"  => 65535,
	    "step" => 1),
        "MEDIUMINT" => array(
	    "type" => "number",
	    "min"  => -8388608,
	    "max"  => 8388607,
	    "step" => 1),
        "MEDIUMINT_U" => array(
	    "type" => "number",
	    "min"  => 0,
	    "max"  => 16777215,
	    "step" => 1),
        "INT" => array(
	    "type" => "number",
	    "min"  => -2147483648,
	    "max"  => 2147483647,
	    "step" => 1),
        "INT_U" => array(
	    "type" => "number",
	    "min"  => 0,
	    "max"  => 4294967295,
	    "step" => 1),
        "BIGINT" => array(
	    "type" => "number",
	    "min"  => -9223372036854775808,
	    "max"  => 9223372036854775807,
	    "step" => 1),
        "BIGINT_U" => array(
	    "type" => "number",
	    "min"  => 0,
	    "max"  => 18446744073709551615,
	    "step" => 1),

        "DECIMAL" => array(
	    "type" => "number",
	    "step" => 1),
        "INTEGER" => array(
	    "type" => "number",
	    "step" => 1),
        "FLOAT"   => array("type" => "number"),
        "DOUBLE"  => array("type" => "number"),
        "REAL"    => array("type" => "number"),

        "DATE"      => array("type" => "date"),
        "TIME"      => array("type" => "time"),
        "DATETIME"  => array("type" => "datetime",
                             "placeholder" => "yyyy-mm-dd hh:mm::ss"),
        "YEAR"      => array(
	    "type" => "number",
	    "min"  => 0,
	    "max"  => 9999,
	    "step" => 1),

	"CHAR" => array(
	    "type" => "text",
	    "size" => 1),
        "VARCHAR" => array(
	    "type" => "text",
	    "size" => 1),
        "TINYTEXT" => array(
	    "type" => "text",
	    "size" => 255),
        "TEXT" => array(
	    "type" => "textarea",
	    "size" => 65535),
        "MEDIUMTEXT" => array(
	    "type" => "textarea",
	    "size" => 16777215),
        "LONGTEXT" => array(
	    "type" => "textarea",
	    "size" => 4294967295),
        "TINYBLOB" => array(
	    "type" => "text",
	    "size" => 255),
        "BLOB" => array(
	    "type" => "text",
	    "size" => 65535),
        "MEDIUMBLOB" => array(
	    "type" => "text",
	    "size" => 16777215),
        "LONGBLOB" => array(
	    "type" => "text",
	    "size" => 4294967295));


/**
 * Startup zeek file.
 *
 * @method start
 * @param string configuration file
 */
    public function start($config_file)
    {
        $config = parse_ini_file($config_file);

        if ($config == null)
        {
            $this->error("Impossible to read config file!");
            return false;
        }

        // we get the global_path
        if (isset($config['global_path']))
        {
            $global_path = $config['global_path'];
	}
        else
        {
            $global_path = getcwd();
        }

	$global_path .=  "/";

        // we search for the zeek library
        if (file_exists($global_path . "index.php") == false)
        {
            $this->error("Wrong global_path : '$global_path/index.php' not found!");
            return false;
        }

	$this->global_path = $global_path;

        if (isset($config['piwik_url']))
            $this->piwik_url = $config['piwik_url'];

        if (isset($config['is_demo']))
            $this->is_demo = true;

        // we initialise using $php_errormgs
        ini_set('track_errors', 1);

        $zeek_lib_src = $global_path . "lib/zeek_library.php";

	// we create de zeek_library object
	require_once $zeek_lib_src;

	$zlib = new ZeekLibrary();
	$zlib->global_path = $global_path;
	$zlib->config($config);

	// we establish the connection with the database
	if ($zlib->connect_to_database() == false)
            return false;

	$this->zlib = $zlib;

        // we initialize the plugins
        $this->plugins_init();

	return true;
    }

/**
 * Received all the commands from client side.
 *
 * @method input
 * @param string command => project_id
 */
    public function input($params)
    {
        // we check if the params is defined
        if ($params == NULL)
            return false;

        // we establish the connection with the database
        if ($this->zlib->connect_to_database() == false)
            return false;

        // we get the method name
        $method = $params['method'];

        // we check if the method name does exist
        if ($this->check_string($method) == false) {
            $this->error("method not defined!");
            return false;
        }

        if (isset($params['params']))
            parse_str($params['params']);

	// we handle the connection method 1st
        if ($method == 'connect')
           return $this->connect($project_name, $login, $password);

	// otherwise we check if the connection is ok
        if ($_SESSION["login"] == false)
	{
	    $this->error("unexpected login error!");
            return false;
	}

	// we create the project
	if ($method == 'project_create')
	    return $this->project_create($params['project_name']);

	// all other commands need that the project name & id need to be used
        $project_name = $_SESSION["project_name"];
        $project_id   = $_SESSION["project_id"];
        $user = $_SESSION["login"];

        if (!(isset($project_name) && isset($project_id)))
	{
	    $this->error("unexpected project error!");
            return false;
	}

        // we store project name and id
	$this->project_name = $project_name;
        $this->project_id   = $project_id;

        switch ($method)
	{
            case 'disconnect':
		$this->disconnect();
		$this->success("disconnect now!");
		return true;

            case 'users_get_list':
	       return $this->users_get_list($project_id);

            case 'user_change_password':
		return $this->user_change_password(
		    $project_id, $user, $password_old, $password_new);

            case 'content_add_directory':
		return $this->content_add_directory(
                    $params["directory"], $params["options"]);

            case 'content_remove_directory':
	        return $this->content_remove_directory(
                    $params["directory"]);

            case 'content_add':
		return $this->content_add(
                    $params["directory"], $params["files"]);

           case 'content_modify':
		return $this->content_modify(
                    $params["directory"], $params["file"]);

            case 'content_delete':
		return $this->content_delete(
                    $params["directory"], $params["name"]);

            case 'contents_get_list':
	        return $this->contents_get_list($project_id);

            case 'contents_get_type_list':
	        return $this->contents_get_type_list($project_id);

            case 'contents_set_type':
                return $this->contents_set_type(
                    $params["name"], $params["directory"], $params["mime"],
                    $params["options"]);

            case 'contents_modify_type':
                return $this->contents_modify_type(
                    $params["name"], $params["options"]);

	    case 'file_create':
	        return $this->file_create(
		    strtolower($params["type"]),
		    strtolower($params["name"]),
		    strtolower($params["extension"]),
		    $params["in_main_directory"] === "true",
		    $params["upload"] ? "files/" . $params["upload"] : null);

	    case 'file_modify':
	        return $this->file_create(
		    strtolower($params["type"]),
		    strtolower($params["name"]),
		    strtolower($params["extension"]),
 		    $params["in_main_directory"] === "true",
		    "projects/" . $project_id . "/" . $params["src"]);

	    case 'file_delete':
	        return $this->file_delete(strtolower($params['name']));

	    case 'file_get_list':
	        return $this->file_get_list();

	    case 'file_get_type_list':
	        return $this->file_get_type_list(true);

	    case 'file_get':
	        return $this->file_get($user, $params['name']);

	    case 'file_set':
	        return $this->file_set($user,
		                       strtolower($params['name']),
		                       $params['data']);

	    case 'test':
	        return $this->test($params['options']);

	    case 'deploy':
	        return $this->deploy($params['dst'],
                                     $params['options']);

            case 'option_get_plugins':
                return $this->option_get_plugins();

            case 'option_get_editor':
                return $this->option_get("editor");

            case 'option_get':
                return $this->option_get(
                    strtolower($params['name']));

            case 'option_set':
                return $this->option_set(
                    strtolower($params['name']),
                    $params['options']);

            case 'option_set_user':
		return $this->user_set_tests(
		    $project_id, $user, $params["options"]);

            case 'structure_get':
		return $this->structure_get();

            case 'structure_get_list':
	        return $this->structure_get_list(
                    $params['expert_mode']  === "true");

            case 'data_get':
		return $this->data_get(
		    strtolower($params['name']),
		    strtolower($params['offset']),
		    strtolower($params['size']));

            case 'data_set':
		return $this->data_set(strtolower($params['type']),
				       $params['values']);

            case 'data_update':
		return $this->data_update(strtolower($params['name']),
					  strtolower($params['id']),
					  $params['values']);

            case 'data_delete':
		return $this->data_delete(strtolower($params['name']),
					  strtolower($params['id']));

        }

        if ($this->user_master_only() || $this->demo_stop())
            return false;

        switch ($method)
	{
            case 'project_delete':
		return $this->project_delete($project_name);

            case 'project_set_name':
                return $this->project_set_name($params["value"]);

            case 'project_set_url':
                return $this->project_set_url($params["value"]);

            case 'project_set_dst':
                return $this->project_set_destination($params["value"]);

            case 'piwik_download':
                return $this->project_download_piwik();

            case 'piwik_set_token':
                return $this->project_set_piwik_token($params["value"]);

            case 'user_add':
	        return $this->user_add(
                    $project_id, $project_name, $email, $is_admin_user);

            case 'user_delete':
	        return $this->user_delete($project_id, $params["email"]);

            case 'structure_enable':
	        return $this->structure_enable($params['enable'] === "true");

            case 'structure_set':
	        return $this->structure_set($params['structure']);

            case 'data_clean_all':
		return $this->data_clean_all();

            case 'contents_unset_type':
                return $this->contents_unset_type($params["name"]);
        }

        $this->error(
            "unknown method '$method' with parameters " . var_dump($params));

        return false;
    }


/**
 * Establish connection with database.
 * Create project name if login and password are valid
 *
 * @method connect
 * @param string project name to create
 * @param string login
 * @param string password to check login
 */
    public function connect($project_name, $login, $password)
    {
        $zlib = $this->zlib;

        // we check if the project_name does exist
        $project_id = $zlib->project_get_id($project_name);
        $projects_path = $zlib->projects_path;

        // we check the validity of the login & password
        if ($this->check_string_and_size($project_name, 25)
            and $this->check_string_and_size($login, 25)
	    and $this->check_string_and_size($password, 32)
	    and $zlib->user_check($project_id, $login, $password))
        {
            // if the project path is used, we accept only project name
            // already defined in this project path
            if (isset($projects_path)
                and $zlib->project_check($project_name) == false)
            {
                $this->error("Project not defined in projects path!");
                return false;
            }

            $_SESSION["is_admin_user"] = $zlib->user_get_authorisation(
                $project_id, $login);

            // we store the session user
            $_SESSION["login"] = $login;
            $_SESSION["start_ts"] = time();

            // the project already exist : it is ok!
            if ($project_id)
            {
                $_SESSION["project_name"] = $project_name;
                $_SESSION["project_id"]   = $project_id;
                $_SESSION["has_project_path"] = isset($projects_path);
                $_SESSION["project_url"] = $zlib->project_get_attribute(
                    $project_id, "url");
                $_SESSION["project_dst"] = $zlib->project_get_attribute(
                    $project_id, "destination");
                $_SESSION["piwik_token"] = $zlib->project_get_attribute(
                    $project_id, "piwik_token");
                $_SESSION["global_path"]  = $this->global_path;

                $this->project_name = $project_name;

                // we redirect to the home
                $this->redirect('home.php');
                return true;
            }
            // only master user can create new project
            else if ($_SESSION["is_admin_user"] == false)
            {
                $this->error("unexpected project name!");
                return false;
            }

            // otherwise we create a new project from the beginning
            $this->success(
                'Connection accepted, now create new project!',
                array('action' => 'project_create'));

            return true;
        }

        $this->error("unexpected project name, login & password!");
        return false;
    }

/**
 * Disconnect session and unset all data.
 *
 * @method disconnect
 */
    public function disconnect()
    {
        // we destroy the session here
        session_destroy();

        // we destroy all the data here
        session_unset();
    }

/**
 * Create new project.
 *
 * @method project_create
 * @param string project name to create
 * @param string project destination
 * @param array options associated to the the project
 */
    public function project_create($project_name, $project_dst=null, $options=null)
    {
        $zlib = $this->zlib;

	// we check the session id
        if ($this->check_string_and_size($project_name, 25))
        {
            // we check if the project_name does not exist
            if ($zlib->project_get_id($project_name) == false)
            {
                // we set default options
                if ($options == null)
                {
                    // zeekify plugins is deactivated by default
                    $plugins = array("zeekify" => "disabled");

                    // we activate all plugins by default
                    foreach ($this->plugins_get_list('files') as $plugin_name)
                    {
                        $plugins[$plugin_name] = true;
                    }

                    $options = array(
                        'editor' => array('html' => '#FF0000',
                                          'css'  => '#00FF00',
                                          'js'   => '#0000FF',
                                          'php'  => '#000000'),
                        'content_types' => array(
                            'images'      => array('img',   'image/*', '#FF0000'),
                            'audio'       => array('audio', 'audio/*', '#00FF00'),
                            'video'       => array('video', 'video/*', '#0000FF'),
                            'application' => array('app',   'application/*', '#000000'),
                        ),
                        'plugins' => $plugins);
                }

		// we create the project
		if ($zlib->project_add($project_name, $project_dst, $options) == false)
		    return false;

		// we store it
		$_SESSION["project_name"] = $project_name;

		$project_id = $zlib->project_get_id($project_name);
		if ($project_id == false)
		{
		    $this->error('Impossible to get project id!');
		    return false;
		}

		$_SESSION["project_id"] = $zlib->project_get_id($project_name);
                $_SESSION["has_project_path"] = isset($projects_path);
                $_SESSION["project_url"] = $zlib->project_get_attribute(
                    $project_id, "url");
                $_SESSION["project_dst"] = $zlib->project_get_attribute(
                    $project_id, "destination");
                $_SESSION["global_path"]  = $this->global_path;

		$this->project_name = $project_name;
		$this->project_id   = $project_id;

		// we redirect to the home
		$this->redirect('home.php');
		return true;
            }
            else
            {
		$this->error('Project already existing!');
		return false;
            }
        }

        $this->error("Project name '" . $project_name
		    . "'  too long or unsupported, change the name!");
        return false;
    }

/**
 * Remove current project.
 *
 * @method project_delete
 * @param string project name to delete
 */
    public function project_delete($project_name)
    {
        if ($this->zlib->project_delete($project_name)) {

            // we proceed the disconnection
            $this->disconnect();

            $this->success(
                "Project '$project_name' correctly deleted!");
            return true;
        }

        return false;
    }

/**
 * Set the name of a project.
 *
 * @method project_set_name
 * @param string new name of the projet
 */
    public function project_set_name($value)
    {

        if ($this->project_set_attribute($this->project_id, "name", $value))
        {
            $_SESSION["project_name"] = $value;
            return true;
        }

        return false;
    }

/**
 * Set the url of a project.
 *
 * @method project_set_url
 * @param string new url of the projet
 */
    public function project_set_url($value)
    {
        if (strpos($value, "www.") == 0)
            $value = "http://" . $value;

        if ($this->project_set_attribute($this->project_id, "url", $value))
        {
            $_SESSION["project_url"] = $value;
            return true;
        }

        return false;
    }


/**
 * Set the destination of a project.
 *
 * @method project_set_destination
 * @param string new destination of the projet
 */
    public function project_set_destination($value)
    {

        if ($this->project_set_attribute($this->project_id, "destination", $value))
        {
            $_SESSION["project_dst"] = $value;
            return true;
        }

        return false;
    }

/**
 * Download & unzip the piwik project
 *
 * @method project_download_piwik
 */
    public function project_download_piwik()
    {
        // Set the default piwik url
        if ($this->piwik_url == "")
            $this->piwik_url = "http://builds.piwik.org/piwik.zip";

        $dst = $this->global_path . "extends/piwik.zip";

        if ($this->zlib->file_download($this->piwik_url, $dst) == false)
            return false;

        if ($this->zlib->unzip($dst, $this->global_path . "extends/") == false)
            return false;

        $this->success("Download Piwik OK!");
        return true;
    }

/**
 * Set the piwik_token of a project.
 *
 * @method project_set_piwik_token
 * @param string new piwik token
 */
    public function project_set_piwik_token($value)
    {
        if ($this->project_set_attribute($this->project_id, "piwik_token", $value))
        {
            $_SESSION["piwik_token"] = $value;
            return true;
        }

        return false;
    }

/**
 * Set the value of an attribute from the specified project.
 *
 * @method project_set_attribute
 * @param int project id
 * @param string attribute name
 * @param string value to write
 */
    public function project_set_attribute($project_id, $name, $value)
    {
        if ($this->zlib->project_set_attribute($project_id, $name, $value))
        {
            $this->success("Attribute '$name' correctly set!");
            return true;
        }

        return false;
    }

/**
 * Authorised new user to connect with this project.
 *
 * @method user_add
 * @param integer id of the current project
 * @param email email to send new password to user
 * @param boolean true if master user
 */
    public function user_add($project_id, $project_name, $email, $is_admin_user)
    {
        // we check if the email is set
        if ($this->check_string($email) == false)
        {
            $this->error("Expecting valid user email!");
            return false;
        }

        if ($is_admin_user == "")
            $is_admin_user = false;
        else if ($is_admin_user == "on")
            $is_admin_user = true;
        else
        {
            $this->error("Expecting valid master user boolean, get '$is_admin_user'!");
            return false;
        }

        $zlib = $this->zlib;

        // check if the user doesn't already exist
        $user = $zlib->user_get($project_id, $email);
        if ($user != NULL)
        {
            // check if the user has the same authorisation
            if ($user->is_admin == $is_admin_user)
            {
                $this->error("The user '$email' already exist!");
                return false;
            }

            // otherwise we change de user authorisation
            if ($zlib->user_change_authorisation($project_id, $user, $is_admin_user))
            {
                $this->success("Change user '$email' authorisations");
                return true;
            }

            return false;
        }

        // we check if the email has valid format
        if (filter_var($email, FILTER_VALIDATE_EMAIL) == false)
        {
            $this->error("Expected a valid email adress, received '$email'!");
            return false;
        }

        // otherwise we create the user with the password randomly
        $password = $this->password_generate(8);

        /* we send the password to the user */
        if ($this->send_email(
            $email,
            "Access to Zeek '$project_name'",
            "Welcome to Zeek '$project_name':\n\n"
            . " - login: $email\n"
            . " - password : $password\n"
            . " - project_name : $project_name\n"))
        {

            if ($zlib->user_add($project_id, $email, $password, $is_admin_user))
            {
                $this->success("User '$email' correctly added & informed!");
                return true;
            }

            return false;
        }

        $this->error("Impossible to send email to '$email'!");
        return false;
    }

/**
 * Don't authorized user to connect with this project.
 *
 * @method user_delete
 * @param integer id of the current project
 * @param email email of user to remove
 */
    public function user_delete($project_id, $email)
    {
        /* we check if the email is set */
        if ($this->check_string($email) == false)
        {
            $this->error("Expecting valid user email!");
            return false;
        }

        if ($this->zlib->user_remove($project_id, $email))
        {
            $this->success("User '$email' correctly deleted!");
            return true;
        }

        return false;
    }

/**
 * Return true and display an error if it isn't a master user.
 *
 * @method user_master_only
 */
    public function user_master_only()
    {
        if ($_SESSION["is_admin_user"])
            return false;

        $this->error("Only master user can do this!");
        return true;
    }

/**
 * Return the list of users associated to the current project.
 *
 * @method users_get_list
 * @param integer id of the current project
 */
    public function users_get_list($project_id)
    {
        $this->output_json(
            array('users' => $this->zlib->users_get_list($project_id)));

	return true;
    }

/**
 * Change password of the user.
 *
 * @method user_change_password
 * @param integer id of the current project
 * @param login of user to change password
 * @param string old password
 * @param string new password
 */
    public function user_change_password(
        $project_id, $login, $password_old, $password_new)
    {
        if ($this->demo_stop())
            return false;

        if (!(isset($password_old) or isset($password_new))) {
            $this->error("Expecting valid old and new passwords!");
            return false;
        }

        if ($this->zlib->user_change_password(
            $project_id, $login, $password_old, $password_new)) {
            $this->success("User password correctly changed!");
            return true;
        }

        return false;
    }

/**
 * Activate/Deactivate test options for the user.
 *
 * @method user_set_tests
 * @param integer id of the current project
 * @param login of user to change password
 * @param json list of options
 */
    public function user_set_tests($project_id, $login, $options)
    {
        // we check if the plugins are valid
        $decode_options = $this->plugins_are_valid($options);
        if ($decode_options == false)
            return false;

        // if it is master user : nothing to store
        if ($this->zlib->user_is_master($login))
        {
            $this->error("Only project options will be used!");
            return false;
        }

        if ($this->zlib->user_set_attribute(
            $project_id, $login, 'options', $this->json_encode(
                array("test" => $decode_options))))
        {
            $this->success("User option 'test' successfully written!");
            return true;
        }

        return false;
    }

/**
 * Return output test options choosen by the user.
 *
 * @method user_get_tests
 */
    public function user_get_tests($project_id, $user)
    {
        $tests = $this->user_get_test_options($project_id, $user);

        if ($tests)
        {
            $this->output_json($tests);
            return true;
        }

        $this->error("No user option found with name 'test'!");
        return false;
    }

/**
 * Get test options choosen by the user.
 *
 * @method user_get_test_options
 */
    private function user_get_test_options($project_id=null, $user=null)
    {
        if ($project_id == null)
            $project_id = $this->project_id;

        if ($user == null)
            $user = $_SESSION['login'];

        $options_str = $this->zlib->user_get_attribute(
            $project_id, $user, 'options');

        // we check that the json is well formated
        $options = $this->json_decode($options_str);

        if ($options
            && is_array($options)
            && array_key_exists('test', $options))
        {
            return $options['test'];
        }

        // otherwise we get project options
        return $this->zlib->project_get_plugins($project_id);
    }

/**
 * Generate random password.
 *
 * @method password_generate
 * @param integer size of the password
 */
    public function password_generate($size)
    {
        $alphabet = "abcdefghijklmnopqrstuvwxyz"
            . "ABCDEFGHIJKLMNOPQRSTUVWXYZ"
            . "1234567890";
        $alphabet_size = strlen($alphabet) - 1;

        $result = array();
        for ($i = 0; $i < $size; $i++) {
            $result[$i] = $alphabet[rand(0, $alphabet_size)];
        }

        return implode($result);
    }

/**
 * Send email.
 *
 * @method send_email
 * @param string email adress
 * @param string title of the email
 * @param string body of the email
 */
    public function send_email($destination, $title, $message)
    {
        return mail($destination, $title, $message);
    }

/**
 * Get content type list from database
 *
 * @method refresh_content_type_list
 */
    private function refresh_content_type_list()
    {
        if ($this->content_type_list != null)
            return $this->content_type_list;

        $options = $this->zlib->option_get($this->project_id);

        if (is_array($options) == false
            || array_key_exists('content_types', $options) == false)
        {
            $this->error("Impossible to get list of current content types!");
            return false;
        }

        $this->content_type_list = $options['content_types'];
        return true;
    }

/**
 * Return the list of actual content
 *
 * @method contents_get_type_list
 */
    public function contents_get_type_list()
    {
        if ($this->refresh_content_type_list() == false)
            return false;

        $this->output_json(
            array('content_types' => $this->content_type_list));

        return true;
    }

/**
 * Return true if the directory is present or return false
 *
 * @method content_has_directory
 */
    private function content_has_directory($directory_name)
    {
        foreach ($this->content_type_list as $type_name => $array)
        {
            if ($directory_name === $array[0])
            {
                return true;
            }
        }

        return false;
    }

/**
 * Return true if the content is correctly added, otherwise return false.
 *
 * @method contents_set_type
 * @param string name of the content type
 * @param string name of the main directory
 * @param string mime filter
 * @param string options added (color)
 */
    public function contents_set_type($name, $directory_name, $mime, $options=NULL)
    {
        if ($this->refresh_content_type_list() == false)
            return false;

        // Check that the name doesn't already exist
        if (array_key_exists($name, $this->content_type_list))
        {
            $this->error("Content type name '$name' already exists!");
            return false;
        }

        // Check that the directory doesn't already exist
        if ($this->content_has_directory($directory_name))
        {
            $this->error(
                "Content type directory name '$directory_name' already exists!");
            return false;
        }

        // Valid the mime filter
        if ($this->mime_validator == null)
        {
            // we create de zeek_mime object
	    require_once $this->global_path . "lib/mime.php";

            $this->mime_validator = new ZeekMime();
        }

        if ($this->mime_validator->validate_mime_type($mime) == false)
        {
            $this->error("Invalid mime '$mime'!");
            return false;
        }

        // Valid options parameters as string
        if ($options != null
            && $this->check_string_and_size($options, 100) == false)
        {
            $this->error("Invalid or too big options '$options'!");
            return false;
        }

        // Add the new valid content type
        $this->content_type_list[$name] = array($directory_name, $mime, $options);

        // And write all the values in database
        if ($this->zlib->option_set($this->project_id,
                                    "content_types",
                                    $this->content_type_list) == false)
        {
            $this->error("Error while writing 'content_types' options!");
            return false;
        }

        $this->success("Content type correctly added!");
        return true;
    }

/**
 * Return true if the content is correctly modified, otherwise return false.
 *
 * @method contents_set_type
 * @param string name of the content type
 * @param string options added (color)
 */
    public function contents_modify_type($name, $options)
    {
        if ($this->refresh_content_type_list() == false)
            return false;

        // Check that the name doesn't already exist
        if (array_key_exists($name, $this->content_type_list) == false)
        {
            $this->error("Content type name '$name' doesn't exists!");
            return false;
        }

        // Valid options parameters as string
        if ($options != null
            && $this->check_string_and_size($options, 100) == false)
        {
            $this->error("Invalid or too big options '$options'!");
            return false;
        }

        // modify the content type options
        $this->content_type_list[$name][2] = $options;

        // And write all the values in database
        if ($this->zlib->option_set($this->project_id,
                                    "content_types",
                                    $this->content_type_list) == false)
        {
            $this->error("Error while writing 'content_types' options!");
            return false;
        }

        $this->success("Content type correctly modified!");
        return true;
    }

/**
 * Return true if the content is correctly deleted, otherwise return false.
 *
 * @method contents_unset_type
 * @param string name of the content type
 */
    public function contents_unset_type($name)
    {
        if ($this->refresh_content_type_list() == false)
            return false;

        // Check that the name doesn't already exist
        if (array_key_exists($name, $this->content_type_list) == false)
        {
            $this->error("Content type name '$name' doesn't exist!");
            return false;
        }

        unset($this->content_type_list[$name]);

        // And write all the values in database
        if ($this->zlib->option_set($this->project_id,
                                    "content_types",
                                    $this->content_type_list) == false)
        {
            $this->error("Error while writing 'content_types' options!");
            return false;
        }

        $this->success("Content type correctly deleted!");
        return true;
    }

/**
 * Get content directory list from database
 *
 * @method refresh_content_directory_list
 */
    private function refresh_content_directory_list()
    {
        if ($this->content_directory_list != null)
            return $this->content_directory_list;

        $options = $this->zlib->option_get($this->project_id);

        if (is_array($options) == false)
        {
            $this->error("Impossible to get list of current content types!");
            return false;
        }

        if (array_key_exists('content_directories', $options))
        {
            $this->content_directory_list = $options['content_directories'];
        }
        else
        {
            $this->content_directory_list = array();
        }

        return true;
    }

/**
 * Add new content directory, return true if directory correctly added,
 * false otherwise.
 *
 * @method content_add_directory
 * @param string directory name
 * @param string json options associated to the directory
 */
    public function content_add_directory($directory_name, $options)
    {
        if ($this->refresh_content_type_list() == false
            || $this->refresh_content_directory_list() == false)
            return false;

        // Check if the directory doesn't already exists
        if (array_key_exists($directory_name, $this->content_directory_list))
        {
            $this->error("Already existing directory '$directory_name'!");
            return false;
        }

        // Valid the directory name
        $index = strrpos($directory_name, "/");
        $directory_path =
        $index > 0 ? substr($directory_name, 0, $index) : $directory_name;

        if ($this->content_has_directory($directory_path) == false)
        {
            $this->error("Not valid directory path '$directory_path'!");
            return false;
        }

        if ($options != null
            && $this->check_string_and_size($options, 100) == false)
        {
            $this->error("Too long options for '$directory_name'!");
            return false;
        }

        // Update database content directories
        $this->content_directory_list[$directory_name] =
            $this->json_decode($options);

        if ($this->zlib->option_set($this->project_id, "content_directories",
                                    $this->content_directory_list) == false)
        {
            $this->error("Error while writing 'content_directories' options!");
            return false;
        }

        $this->success("Content directory '$directory_name' correctly added!");
        return true;
    }


/**
 * Remove the content directory
 *
 * @method content_remove_directory
 * @param string directory name
 */
    public function content_remove_directory($directory_name)
    {
        if ($this->content_valid_directory($directory_name) == false)
            return false;

        $zlib = $this->zlib;

        // Remove the destination directory
        if ($zlib->directory_remove(
            $this->global_path . "projects/" . $this->project_id
          . "/". $_SESSION["login"] . "/$directory_name") == false)
              return false;

        unset($this->content_directory_list[$directory_name]);

        // Update the datase content directories
        if ($zlib->option_set($this->project_id,
                              "content_directories",
                              $this->content_directory_list) == false)
        {
            $this->error("Error while writing 'content_directories' options!");
            return false;
        }

        $this->success("Content directory '$directory_name' correctly removed!");
        return true;
    }

/**
 * Valid the content directory
 *
 * @method content_valid_directory
 * @param string directory name
 */
    private function content_valid_directory($directory_name)
    {
        if ($this->refresh_content_directory_list() == false)
            return false;

        // Valid the directory name
        if (array_key_exists($directory_name, $this->content_directory_list)
            == false)
        {
            $this->error("No directory '$directory_name' found!");
            return false;
        }

        return true;
    }

/**
 * Add new image to the current project or rewrite existing one
 *
 * @method content_add
 * @param string directory name
 * @param string file inputs
 */
    public function content_add($directory_name, $inputs)
    {
        if ($this->content_valid_directory($directory_name) == false)
            return false;

        // we check that the json is well formated
        $decode_inputs = $this->json_decode($inputs);
        if ($decode_inputs == NULL)
        {
            $this->error("Invalid input values '$inputs'!");
            return false;
        }

        // if the test directory exist, we will copy the added
        // content into the test directory
        $destination = $this->global_path . $this->test_get_directory();
        $options = null;

        // we get the user test options
        foreach ($decode_inputs as $input)
        {
            // Get the uploaded files
            $uploaded = "files/" . $input;
            $name = $input;

            $zlib = $this->zlib;

            // Get the extension of the content
            $extension = $zlib->file_get_extension($input);

            // Remove the extension of the destination name
            if ($zlib->file_get_extension($name) === $extension)
                $name = substr($name, 0, strpos($name, '.'));

            // Move source file to destination file
	    if ($zlib->file_modify(
	        $this->project_id, $_SESSION["login"], $uploaded,
                $directory_name, $name, $extension) == false)
            {

                $this->zlib->uploaded_files_delete();
                return false;
            }

            // Check that test directory exists
            if (file_exists($destination) == false)
                continue;

            if ($options == null)
                $options = $this->user_get_test_options();

            // Generate the file informations
            $file = $this->zlib->file_get_details(
                "$directory_name/$name.$extension");

            // Deploy the file to the test directory
            if ($options && $this->deploy_one_file(
                $destination, $file, $options) == false)
            {
                $this->zlib->uploaded_files_delete();
                return false;
            }
        }

	$this->success("Content(s) stored in " . "'$directory_name'!");

        $this->zlib->uploaded_files_delete();
        return $result;
    }

/**
 * Modify existing content
 *
 * @method content_modify
 * @param string directory name
 * @param string file to modify
 */
    public function content_modify($directory_name, $name)
    {
        if ($this->content_valid_directory($directory_name) == false)
            return false;

        // we check that the file already exist

        // we check that the file to copy also exist

        // Get the extension of the content
        $extension = $zlib->file_get_extension($name);

        // Remove the extension of the destination name
        if ($zlib->file_get_extension($name) === $extension)
            $name = substr($name, 0, strpos($name, '.'));

        // we remove the existing file

        // we copy the new file
        $result = false;

	if ($zlib->file_modify(
	    $this->project_id, $_SESSION["login"], $file_to_move,
            $directory_name, $name, $extension))
	{
	    $this->success(
		"content '$file_to_move' stored as '$new_directory/$name.$extension'!");

	    $result = true;
	}

        // TODO : if the test directory exist, we copy the modified
        // content into the test directory

        $this->zlib->uploaded_files_delete();
        return $result;
    }

/**
 * Delete existing image
 *
 * @method content_delete
 * @param string directory name
 * @param string file name
 */
    public function content_delete($directory_name, $name)
    {
        if ($this->content_valid_directory($directory_name) == false)
            return false;

        if ($this->zlib->file_delete($this->project_id, $_SESSION["login"],
                                     $directory_name . "/" . $name))
        {
	    $this->success(
		"content '$directory_name/$name' correctly removed!");

            // TODO : if the test directory exist, we delete the
            // content from the test directory

            return true;
        }

        return false;
    }

/**
 * Give the complete list of contents
 *
 * @method contents_get_list
 */
    public function contents_get_list()
    {
        if ($this->refresh_content_directory_list() == false)
            return false;

        $get_list = array();

        foreach ($this->content_directory_list as $name => $options)
        {
            $get_list[$name] = $this->zlib->contents_get_list(
                $this->project_id, $name);

            $get_list[$name]["infos"] = $options;
        }

	if (count($get_list) > 0)
	{
	    $this->output_json(array('get_list' => $get_list));
	    return true;
	}

        $this->error("No contents found!");
	return false;
    }

/**
 * Initialise the files associated to the project.
 *
 * @method file_init
 */
    public function file_init()
    {
	$zlib = $this->zlib;
	$project_id = $this->project_id;
	$user = $_SESSION["login"];

	// the index.html file of the user already exist :
	// we do nothing
	if (file_exists($zlib->file_get_path($project_id, 'html', 'index', true)))
	    return true;

	// otherwise we create a generic index.html & css directory
	if ($zlib->file_create($project_id, $user, 'html', 'index', 'html', true))
 	{
	    return $this->file_get_list();
	}

	return false;
    }


/**
 * Create the file specified
 *
 * @method file_create()
 * @param string type of file
 * @param string filename
 * @param string extension
 * @param boolean is_in_main_directory
 * @param string uploaded (optional)
 */
    public function file_create($type, $name, $extension,
				$in_main_directory, $uploaded = null)
    {
	$zlib = $this->zlib;

	// we check that the file type is valid
	if ($this->type_list == null)
	    $this->file_get_type_list();

	if (in_array($type, $this->type_list) == false)
	{
	    $this->clean_upload_on_error("The file type '$type' is invalid!");
            return false;
	}

	// case 1 : move the uploaded file in the correct destination
	if ($uploaded)
	{
            $result = false;

	    if ($zlib->file_modify(
		$this->project_id, $_SESSION["login"], $uploaded, $type, $name,
		$extension, $in_main_directory))
	    {
		$this->success(
		    "file '$uploaded' stored as "
		  . "'$name.$extension' with type '$type' created!");

	        $result = true;
	    }

            $this->zlib->uploaded_files_delete();
	    return $result;
	}

	// case 2 : create a new empty file
	if ($zlib->file_create(
	    $this->project_id, $_SESSION["login"], $type, $name,
	    $extension, $in_main_directory))
	{
	    $this->success(
		"file '$name.$extension' with type '$type' created!");
	    return true;
	}

	return false;
    }

    private function clean_upload_on_error($msg)
    {
        $this->zlib->uploaded_files_delete();
        $this->error($msg);
    }


/**
 * Delete the file specified
 *
 * @method file_delete()
 * @param string file path
 */
    public function file_delete($name)
    {
	if ($this->zlib->file_delete(
	    $this->project_id, $_SESSION["login"], $name))
	{
	    $this->success("The file '$name' successfully deleted!");
	    return true;
	}

        // TODO : if the test directory exist, we delete the
        // file from the test directory

	return false;
    }

/**
 * Return the list of current files in "Project" directory
 *
 * @method files_get_list()
 */
    public function file_get_list()
    {
        if ($this->refresh_content_type_list() == false)
            return false;

        $filter_directories = array();

        foreach ($this->content_type_list as $type_name => $array)
        {
            array_push($filter_directories, $array[0]);
        }

	$get_list = $this->zlib->file_get_list(
            $this->project_id, $filter_directories);

	if (is_array($get_list))
	{
	    if (count($get_list) > 0)
	    {
		$this->output_json(array('get_list' => $get_list));
		return true;
	    }

	    return $this->file_init();
	}

        $this->error("No files found!");
	return false;
    }

/**
 * Return the list of type of files
 *
 * @method files_get_type_list()
 *
 * @params boolean json_output
 */
    public function file_get_type_list($is_json_output = false)
    {
	$type_list = $this->zlib->file_get_type_list();

	if (is_array($type_list) && count($type_list) > 0)
	{
            $res = array();

	    foreach ($type_list as $file)
	    {
	        $name = $file["name"];

		if (substr($name, 0, 5) == "mode-")
		    array_push($res, substr($name, 5, -3));
	    }

	    // we store the list
	    if ($is_json_output)
		$this->output_json(array('type_list' => $res));

	    $this->type_list = $res;

	    return true;
	}

	return false;
    }



/**
 * Return the content of the specified file.
 *
 * @method file_get
 * @param string user
 * @param string type
 * @param string name
 * @param boolean is_main_directory
 */
    public function file_get($user, $name)
    {
        if ($this->check_string($user) == false
           || $this->check_string($user) == false)
        {
            $this->error("user '$user' or/and name '$name' field are invalid!");
            return false;
        }

	$zlib = $this->zlib;
	$get = $zlib->file_get($this->project_id, $user, $name);

	if ($get)
	{
	    $this->output_json(array('get' => stripslashes($get),
				     'type' => $zlib->file_get_type($name)));
	    return true;
	}

	return false;
    }


/**
 * Set the content of the specified file.
 *
 * @method file_set
 * @param string user
 * @param string name
 * @param string data
 */
    public function file_set($user, $name, $data)
    {
	$zlib = $this->zlib;

        $status = false;

        // we copy the file in the project directory
	if ($zlib->file_set($this->project_id, $user, $name, $data))
            $status = true;

        // if the test directory exist: we copy it in the test directory
        $destination = $this->global_path . $this->test_get_directory();

        if (file_exists($destination))
        {
            $status = false;

            // we generate the file informations
            $file = $this->zlib->file_get_details($name);

            // we get the user test options
            $options = $this->user_get_test_options();

            if ($options && $this->deploy_one_file($destination, $file, $options))
                $status = true;
        }

        if ($status)
	    $this->success(
		$zlib->file_get_path($this->project_id, $name)
	      . " correctly updated");

	return $status;
    }

/**
 * Method that instantiate all the plugins.
 *
 * @method plugins_init
 */
    public function plugins_init()
    {
        $this->plugins_list = array();

        // we get the plugin list
        $plugins_list = $this->zlib->plugins_get_list();

        foreach ($plugins_list as $plugin)
        {
            require_once $plugin["path"] . "/" . $plugin["name"];

            $plugin_type = $plugin["directory"];
            $plugin_name = $plugin["filename"];

            if (isset($this->plugins_list[$plugin_type]) == false)
            {
                $this->plugins_list[$plugin_type] = array();
            }

            // we get the class name
            $offset = 0;

            while ($idx = strpos($plugin_name, '_', $offset))
            {
                $plugin_name = ucfirst(substr($plugin_name, $offset, $idx))
                             . ucfirst(substr($plugin_name, $idx + 1));

                $offset = $idx + 1;
            }

            // we instantiate each plugin
            $this->plugins_list[$plugin_type][$plugin_name] = new $plugin_name();
        }
    }

/**
 * Method that returns the list of plugins.
 *
 * @method plugins_get_list
 * @param string type of the plugin
 */
    public function plugins_get_list($type)
    {
        if (array_key_exists($type, $this->plugins_list))
        {
            $result = array();

            foreach ($this->plugins_list[$type] as $plugin_name => $plugin_obj)
            {
                array_push($result, $plugin_name);
            }

            return $result;
        }

        var_dump($this->plugins_list);

        return array();
    }

/**
 * Method that returns the decodes options if the plugins exist.
 *
 * @method plugins_are_valid
 * @param json name of the plugin
 */
    public function plugins_are_valid($options)
    {
        // we check that the json is well formated
        $decode_options = $this->json_decode($options);
        if ($decode_options == NULL)
        {
            $this->error("Invalid plugin values '$options'!");
            return false;
        }

        // we get the plugins associated to the project
        $project_options = $this->zlib->project_get_plugins(
            $this->project_id);

        if ($project_options == false)
            return false;

        foreach ($decode_options as $name => $status)
        {
            if (array_key_exists($name, $project_options)
                && is_bool($project_options[$name])
                && is_bool($status))
                    continue;

            $this->error(
                "Unknown '$name' plugin or invalid status '$status'!");

            return false;
        }

        return $decode_options;
    }

 /**
  * Method that replace inside a string <zeek></zeek> content with
  * data stored in databases.
  *
  * @method on_input
  * @param string input to zeekify
  *
  * @return string zeekified output
  */
    public function zeekify($input)
    {
        $zlib = $this->zlib;

        $output = "";

        $offset = 0;

        while ($offset + 6 < strlen($input))
        {
            // search for next '<zeek ' match
            $start_idx = strpos($input, '<zeek ', $offset);
            $end_idx = strpos($input, '</zeek>', $offset + 6);

            if ($end_idx == 0)
                break;

            $output .= substr($input, $offset, $start_idx - $offset);

            if ($start_idx >= $offset && $end_idx + 7 > $offset)
            {
                $options = $this->zeekify_get_options(
                    substr($input, $start_idx, $end_idx + 7 - $start_idx));

                $start_idx = strpos($input, '>', $start_idx) + 1;

                $output .= $this->zeekify_one_by_one(
                    substr($input, $start_idx, $end_idx - $start_idx),
                    $options);

                $offset += $end_idx + 7 - $offset;
            }
        }

        $output .= substr($input, $offset, strlen($input) - $offset);

        return $output;
    }

    private function zeekify_get_options($input)
    {
        $options = array();

        // Get attributes string : <zeek ... >
        $end_idx = strpos($input, '>');

        // Get each attribute [ toto="tutu", titi="tata" ]
        $list = preg_split("/ +/", substr($input, 6, $end_idx - 6),
                           NULL, PREG_SPLIT_NO_EMPTY);

        // Error detected : empty options
        if ($list == false)
            return $options;


        $valid = array("name"    => "[a-z]+",
                       "offset"  => "\d+",
                       "size"    => "\d+",
                       "sort_by" => "\w+([+|-]{2})?");

        // Separate each toto = tutu and check the attribute values
        foreach ($list as $attribute)
        {
            $type_idx = strpos($attribute, '=');

            $attr_type = substr($attribute, 0, $type_idx);
            $attr_value = substr($attribute, $type_idx + 1);

            if (array_key_exists($attr_type, $valid)
                && preg_match("/^\"(" . $valid[$attr_type] .  ")\"\z/",
                              $attr_value, $result))
            {
                $attr_value = $result[1];

                // numérifie les chaînes
                if (is_numeric($result[1]))
                    $attr_value = intval($result[1]);

                $options[$attr_type] = $attr_value;
            }
        }

        return $options;
    }

  /**
   * Method to replace input string with databases content depending on the
   * options.
   *
   * Here are the list of options possible :
   *  - table: name of the table used
   *  - offset : offset of the element
   *  - size: nb of elements
   *
   * Inside the input, use  '{{...}}' symbol to get the attributes value
   *
   * @param string input
   * @param array options
   * @return string output
   */
    private function zeekify_one_by_one($input, $options)
    {
        $zlib = $this->zlib;

        // we check mandatory option : table
        if (array_key_exists('name', $options) == false)
            return "Zeek name should be defined!";

        $table_name = $options['name'];

        if (is_string($table_name) == false)
            return "Invalid zeek name '$table_name'!";

        // we check the optional option : offset
        $offset = array_key_exists('offset', $options) ? $options['offset'] : null;

        if ($offset != null && is_numeric($offset) == false)
            return "Invalid offset option '$offset': expect an integer!";

        // we check the optional option : size
        $size = array_key_exists('size', $options) ? $options['size'] : null;

        if ($size != null && is_numeric($size) == false)
            return "Invalid size option '$size': expect an integer!";

        if ($offset != null && $size == null)
            return "If offset is defined : size option must be also defined!";

        // we check the validity of the table
        $structure = $zlib->type_get($this->project_name, $table_name);

        if ($structure == false)
            return "Zeek '$table_name' not found!";

        // we check the optional option : sort_by
        $sort = array('id', 'ASC');

        $sort_by = array_key_exists('sort_by', $options) ? $options['sort_by'] : null;

        if ($sort_by != null)
        {
            $direction_str = substr($sort_by, strlen($sort_by) - 2);

            $direction = null;

            if ($direction_str == '++')
                $direction = 'ASC';
            else if ($direction_str == '--')
                $direction = 'DESC';

            if ($direction == null)
            {
                $direction = 'ASC';
                $name = $sort_by;
            }
            else
            {
                $name = substr($sort_by, 0, strlen($sort_by) - 2);
            }

            // we check that the attribute is valid
            if (array_key_exists($name, $structure) == false && $name != "id")
                return "Attribute '$name' not found in '$table_name'!";

            $sort = array($name, $direction);
        }


        $result = $zlib->value_get(
            $this->project_id, $table_name, $sort, $size, $offset);

	// if no value : we return an empty array
	if ($result == NULL)
            return "";

        $output = "";

	while ($row = $zlib->value_fetch($result))
        {
            $offset = 0;

            // we check that all attributes in the input are valid
            while ($offset + 2 < strlen($input))
            {
                $start_idx = strpos($input, "{{", $offset);
                $end_idx = strpos($input, "}}", $offset + 2);

                if ($end_idx == 0)
                    break;
                $output .= substr($input, $offset, $start_idx - $offset);

                if ($start_idx >= $offset && $end_idx + 2 > $offset)
                {
                    $attribute_name = substr($input, $start_idx + 2,
                                             $end_idx - $start_idx - 2);

                    if (array_key_exists($attribute_name, $structure) == false)
                        $output .= "Attribute '$attribute_name' not found!";
                    else {
                        $attribute = $structure[$attribute_name];

                        $output .= $row[$attribute_name];
                    }

                    $offset += $end_idx + 2 - $offset;
                }
            }

            $output .= substr($input, $offset, strlen($input) - $offset);
        }

        return $output;
    }


 /**
  * Method to zeekify all the code & deploy the test platform
  *
  * @method test
  * @params string options associated
  */
    public function test($options)
    {
        // we get the user test options
        if ($options == null)
        {
            $decode_options = $this->user_get_test_options();
        }
        // we check the specified options
        else
        {
            $decode_options = $this->plugins_are_valid($options);
        }

        if ($decode_options == false)
            return false;

        // we set the final destination
        $dst = $this->test_get_directory();

        // we deploy the files & apply all the data plugins
        if ($this->deploy_files(
            $this->global_path . $dst, $decode_options) == false)
            return false;

	$this->output_json(array('href' => $dst . '/index.html'));
        return true;
    }

 /**
  * Method to get actual user test directory
  *
  * @method test_get_directory
  */
    private function test_get_directory()
    {
        return 'projects/' . $this->project_id
	        . '/TEST/' . $_SESSION['login'];
    }

/**
 * Method to call to deploy the project on his final directory
 *
 * @method deploy
 * @param string destination to deploy files
 * @param string options for the deployment
 */
    public function deploy($dst, $options)
    {
        // we get the project deploy options
        if ($options == null || $options == '{}')
        {
            $decode_options = $this->zlib->project_get_plugins(
                $this->project_id);
        }
        // or we check the specified options
        else
        {
            $decode_options = $this->plugins_are_valid($options);
        }

        if ($decode_options == false)
            return false;

        // we set the final destination
        $dst = $this->zlib->project_get_attribute(
            $project_id, "destination");

        // we deploy the files & apply all the plugins
        if ($this->deploy_files($dst, $decode_options) == false)
            return false;

	$this->success("Successfully deployed!");
        return true;
    }

/**
 * Method to call to deploy the project on the specified directory
 *
 * @method deploy_files
 * @param string destination to copy the files
 * @param hash options to activate for each file
 */
    public function deploy_files($destination, $options)
    {
        $zlib = $this->zlib;
        $project_id = $this->project_id;

        $files = $zlib->file_get_list($project_id) ;

        if ($files == false) {
            $this->error("No files to deploy!");
            return false;
        }

        // we clean all files in the destination
        // but not the zeek directory
        if ($zlib->directory_remove(
                $destination, $this->global_path) == false)
            return false;

        // we start from clean
        if ($zlib->directory_create($destination) == false)
            return false;

        foreach ($files as $file)
        {
            if ($this->deploy_one_file(
                $destination, $file, $options) == false)
                    return false;
        }

        return true;
    }

/**
 * Method to call to deploy one file
 *
 * @method deploy_one_file
 * @param file details
 * @param hash options to activate for each file
 */
    public function deploy_one_file($destination, $file, $options)
    {
        $zlib = $this->zlib;
        $project_id = $this->project_id;

        // check if the file directory exist
        if ($file['in_main_directory'] == false
            && $zlib->directory_create(
            $destination . '/' . $file['type']) == false)
                return false;

        // we get the file content
        $input = $zlib->file_get($project_id,
                                 $_SESSION['login'],
                                 $file['name']);
        if ($input == false)
            return false;

        // we handle the options
        foreach ($options as $option => $is_activated)
        {
            if (is_string($is_activated) && $is_activated === "disabled")
                continue;

            if ($is_activated == false)
                continue;

            // we handle the plugins here
            if (array_key_exists($option, $this->plugins_list['files']))
            {
                $plugin_obj = $this->plugins_list['files'][$option];

                if (in_array($file['type'], $plugin_obj->accept_files()) == false)
                    continue;

                $input = $plugin_obj->on_input($input);
            }
            // otherwise we handle the file handler here as method
            else
                $input = $this->$option($input, $file['type']);

            if ($input == null)
            {
                $input = "Error on '$option' functionnality: null output!";
                break;
            }
        }

        // we write the file
        if ($zlib->file_write(
            $destination . '/' . $file['name'], $input) == false)
                return false;

        return true;
    }

/**
 * Return the option wanted or an error if it is invalid or not already existing.
 *
 * @method option_get_plugins
 */
    public function option_get_plugins()
    {
        $project_options = $this->zlib->project_get_plugins($this->project_id);
        $user_options = $this->user_get_test_options($project_id, $user);

        if ($project_options && $user_options)
        {
            $this->output_json(array(
                "project" => $project_options,
                "user" => $user_options));

            return true;
        }

        $this->error("No option found with name '$name'!");
        return false;
    }

/**
 * Return the option wanted or an error if it is invalid or not already existing.
 *
 * @method option_get
 * @params string option name
 */
    public function option_get($name)
    {
        if ($this->check_string_and_size($name, 25) == false)
        {
            $this->error("Invalid option name '$name'!");
            return false;
        }

        $options = $this->zlib->option_get($this->project_id);
        if (is_array($options)
            && array_key_exists($name, $options))
        {
            $this->output_json($options[$name]);
            return true;
        }

        $this->error("No option found with name '$name'!");
        return false;
    }


/**
 * Store the new option created or modified an option that is already existing.
 *
 * @method option_set
 * @params string option name
 * @params array values associated to the name
 */
    public function option_set($name, $values)
    {
        if ($this->demo_stop())
            return false;

        if ($this->check_string_and_size($name, 25) == false)
        {
            $this->error("Invalid option name '$name'!");
            return false;
        }

        // we check that the json is well formated
        $decode_values = $this->json_decode($values);
        if ($decode_values == NULL)
        {
            $this->error("Invalid option values '$values'!");
            return false;
        }

        if ($this->zlib->option_set($this->project_id, $name, $decode_values))
        {
            $this->success("Option '$name' successfully written!");
            return true;
        }

        $this->error("Error while setting option '$name'!");
        return false;
    }


    private function structure_get_plugins()
    {
        $options = $this->zlib->option_get($this->project_id);

        if (is_array($options) == false
            || array_key_exists('plugins', $options) == false)
        {
            $this->error("Impossible to get list of plugins!");
            return false;
        }

        return $options['plugins'];
    }

/**
 * Check if project structure is enabled or disabled.
 *
 * @method structure_is_enabled
 */
    public function structure_is_enabled()
    {
        $plugins = $this->structure_get_plugins();

        if ($plugins == false || is_bool($plugins['zeekify']) == false)
        {
            $this->error("Structure should be enabled!");
            return false;
        }

        return true;
    }

/**
 * Enable or disable project structure.
 *
 * @param boolean is_activated
 *
 * @method structure_enable
 */
    public function structure_enable($is_enabled)
    {
        if (is_bool($is_enabled) == false)
        {
            $this->error("Expected boolean parameter!");
            return false;
        }

        $plugins = $this->structure_get_plugins();

        if ($plugins == false)
            return false;

        $status = ($is_enabled ? "en" : "dis") . "abl";

        if (is_bool($plugins['zeekify']) === $is_enabled)
        {
            $this->error("Structure already ". $status ."ed!");
            return false;
        }

        $plugins['zeekify'] = $is_enabled ? true : "disabled";

        // update the project deployment options
        if ($this->zlib->option_set($this->project_id, "plugins", $plugins))
        {
            $this->structure_enabled = $is_enabled;
            $this->success("Structure correctly ". $status ."ed!");
            return true;
        }

        $this->error("Error when ". $status ."ing structure!");
        return false;
    }

/**
 * Send the project structure.
 *
 * @param boolean expert mode
 *
 * @method structure_get_list
 */
    public function structure_get_list($expert_mode)
    {
        if ($this->structure_is_enabled() == false)
            return false;

        $result = array_keys(
            $expert_mode ? $this->type_complex : $this->type_simple);

        // we also support contents type list
        if ($this->refresh_content_type_list())
        {
            foreach ($this->content_type_list as $type_name => $detail)
            {
                array_push($result, "contents:" . $type_name);
            }
        }

	$this->output_json(array('list' => $result));
	return true;
    }


/**
 * Send the project structure.
 *
 * @method structure_get
 */
    public function structure_get()
    {
        if ($this->structure_is_enabled() == false)
            return false;

        // we get the structure project
	$structure = $this->zlib->structure_get($this->project_id,
                                                $this->project_name);

        // we go through all domains
	foreach ($structure as $domain => $attribute)
	{
	    // we go through all attribute of each domain
	    foreach ($attribute as $name => $options)
	    {
                $db_type = $options['db_type'];

		// we get the css options
		$css_options = $this->type_complex[$db_type];

		// we set the options with the db type
		$css_options['db_type'] = $db_type;

		if (array_key_exists('sp_type', $options))
                    $css_options['sp_type'] = $options['sp_type'];


		if (array_key_exists('db_size', $options))
                {
                    $css_options['db_size'] = $options['db_size'];

                    if (array_key_exists('size', $css_options))
                    {
                        $css_options['size'] = $options['db_size'];
                    }
                }

		// we set the css options before sending the data
		$structure[$domain][$name] = $css_options;
	    }
	}

	$this->output_json(array('structure' => $structure));

	return true;
    }


/**
 * Set a new project structure.
 *
 * We compare to the previous structure and process the change.
 *
 * @method structure_set
 * @param array structure to set
 *
 * It has following behavior :
 *  {
 *    structure_name: {
 *         attribute_name: {
 *             sp_type: SP_TYPE, # used in simple mode
 *             db_type: DB_TYPE, # used in expert mode
 *             db_size: DB_SIZE,
 *         },
 *         ...
 *    },
 *    ...
 *  }
 */
    public function structure_set($new_structure)
    {
        if ($this->structure_is_enabled() == false)
            return false;

        // if a structure path is defined : structure_set is not allowed
        if ($this->zlib->projects_path)
        {
            $this->error(
                "Deactivate project path to dynamically modify structure!");
            return false;
        }

        // we decode the json structure
        $decode_structure = $this->json_decode($new_structure);
        if ($decode_structure == NULL)
        {
            $this->error("Invalid structure value '$new_structure'!");
            return false;
        }

        // we add supported contents type list
        if ($this->refresh_content_type_list())
        {
            $array = $this->type_simple["TITLE"];

            foreach ($this->content_type_list as $type_name => $detail)
            {
                $type_name = "contents:" . $type_name;

                $array["type"] = $type_name;

                $this->type_simple[$type_name]  = $array;
                $this->type_complex[$type_name] = $array;
            }
        }

        $structure = array();

        // we check if it is valid
        foreach ($decode_structure as $table_name => $domain)
        {
            if ($this->check_string_and_size($table_name, 25) == false)
            {
                $this->error("Invalid table name '$table_name'!");
                return false;
            }

            foreach ($domain as $attribute => $value)
            {
                if ($this->check_string_and_size($attribute, 25) == false)
                {
                    $this->error(
                        "$table_name : invalid attribute name '$attribute'!");
                    return false;
                }

                // we convert the simple to the expert mode
                if (array_key_exists('sp_type', $value)
                    && array_key_exists($value['sp_type'], $this->type_simple))
                {

                    if (array_key_exists($table_name, $structure) == false) {
                        $structure[$table_name] = array();
                    }

                    $type_simple = $this->type_simple[$value['sp_type']];
                    $type_simple['sp_type'] = $value['sp_type'];

                    $structure[$table_name][$attribute] = $type_simple;
                    continue;
                }

                // we check the whole structure
                if (array_key_exists('db_type', $value) == false)
                {
                    $this->error(
                        "$table_name : db_type should be defined in '$attribute'!");
                    return false;
                }

                if (array_key_exists(
                          $value['db_type'], $this->type_complex) == false)
                {
                    $this->error("$table_name : invalid type in '$attribute'!");
                    return false;
                }

                if (array_key_exists('db_size', $value)
                    && is_numeric($value['db_size']) == false)
                {
                    $this->error(
                        "$table_name : size should be numeric in '$attribute'!");
                    return false;
                }

                $structure[$table_name][$attribute] = $value;
            }
        }

        // we set the new structure into the database
        if ($this->zlib->structure_set(
            $this->project_id, $this->project_name, $structure))
        {
            $this->success("Structure correctly set!");
            return true;
        }

        return false;
    }

/**
 * Return all asked data in JSON format to client side.
 *
 * @method data_get
 * @param string name of the data expected
 * @param integer offset
 * @param integer number of elements to get
 */
    public function data_get($name, $offset, $size)
    {
        if (isset($name) == false) {
            $this->error("Expecting valid table name!");
            return false;
	}

        if (isset($offset) && is_numeric($offset) == false) {
            $this->error("Expecting valid offset!");
            return false;
	}

        if (isset($size) && is_numeric($size) == false) {
            $this->error("Expecting valid size!");
            return false;
	}

        $result = $this->zlib->value_get(
            $this->project_id, $name, array('id' => 'DEC'), $size, $offset);

	// if no value : we return an empty array
	if ($result == NULL)
	    return $this->output_json(array());

	// otherwise we store the values in an array
        $response = array();

	while ($row = $this->zlib->value_fetch($result)) {
            unset($row->project_id);
            array_push($response, $row);
        }

        // we return an array of values
        return $this->output_json($response);
    }

/**
 * Return the elements with the format expected.
 *
 * @method data_get_tables
 * @param hash params received
 */
    public function data_get_tables($params)
    {
        /* we get the name of the table */
        $name = $params['name'];
        if (!isset($name)) {
            $this->error("Expecting valid table name");
            return false;
        }

        /* we get the total number of elements */
        $records_total = $this->zlib->table_count($this->project_id, $name);

        /* we get the elements */
        $result = $this->zlib->value_get(
            $this->project_id, $name, array('id' => 'DEC'), $size, $offset);

        $data = array();

        if ($result) {
            while ($row = $this->zlib->value_fetch($result)) {
                array_push($data, $row);
            }
        }

        $records_filtered = 0;

        $this->output_json(array(
            "draw" => intval($params['draw']),
            "recordsTotal" => intval($records_total),
            "recordsFiltered" => intval($records_filtered),
            "data" =>  $data,
        ));

        return true;
    }

/**
 * Return the number of elements.
 *
 * @method data_get_number
 * @param string name of the data expected
 */
    public function data_get_number($name)
    {
        if (!(isset($name))) {
            $this->error("Expecting valid table name!");
            return false;
        }

        return $this->output_json(
            array('count' => $this->zlib->table_count($project_id, $name)));
    }


/**
 * Return success message or error.
 *
 * @method data_set
 * @param string name of the data expected
 * @param hash values of the data
 */
    public function data_set($type, $values_str)
    {
        if (!(isset($type) && isset($values_str))) {
            $this->error("Expecting valid name and values fields!");
            return true;
        }

	$params = array();
	parse_str($values_str, $params);

        if ($this->zlib->value_insert(
                 $this->project_id, $type, $params) == false) {
            return false;
        }

        // we handle "data" plugins functionnalities

        $this->success("Value correctly inserted!");

        return true;
    }

/**
 * Return success message or error.
 *
 * @method data_update
 * @param string name of the data expected
 * @param integer id of the data to update
 * @param hash values of the data to update
 */
    public function data_update($name, $id, $values_str)
    {
        if (!(isset($name) && isset($id) && isset($values_str))) {
            $this->error("Expecting valid name, id and values field!");
            return false;
        }

	$params = array();
	parse_str($values_str, $params);

        if ($this->zlib->value_update($this->project_id, $name, $id, $params)) {
            $this->success("Value correctly updated!");
            return true;
        }

        return false;
    }

/**
 * Return success message or error.
 *
 * @method data_delete
 * @param string name of the data expected
 * @param integer id of the data to delete
 */
    public function data_delete($name, $id)
    {
        if (!(isset($name) and isset($id))) {
            $this->error("Expecting valid name and id field!");
            return false;
        }

        if ($this->zlib->value_delete($this->project_id, $name, $id)) {
            $this->success("Value correctly deleted!");
            return true;
        }

        return false;
    }

/**
 * Return success message or error.
 *
 * @method data_clean_all
 * @param string name of the data expected
 */
    public function data_clean_all()
    {
    }

/**
 * Return all asked data in JSON format to client side.
 *
 * @method get_data
 * @param string
 */
    public function get_data($type)
    {
        $zlib = $this->zlib;

        $action = NULL;
        $result = '';

        /* we check if it is specified type  */
        if ($zlib->type_check($this->project_name, $type)) {
            $result = $this->display_get_and_set($type);
        }
        /* we handle other cases */
        else if (file_exists($this->global_path . "view/$type.html")) {
            $file = $this->global_path . "view/$type.html";

            $handle = fopen($file, 'r');

            if ($handle == NULL) {
                $this->error("impossible to open file '$file'!");
                return false;
            }

            $result = fread($handle, filesize($file));

            fclose($handle);
        }

        if ($result) {
            $this->clean_and_send($action, $result);
            return true;
        }

        $this->error("unexpected type '$type'!");
        return false;
    }

/**
 * Launch jquery redirection.
 *
 * @method redirect
 * @param string where to redirect
 */
    public function redirect($url)
    {
        $this->output_json(array("redirect" => $url));
    }

/**
 * Return false with a message error in case of demo option
 *
 * @method demo_stop
 */
    public function demo_stop()
    {
        if ($this->is_demo)
        {
            $this->error("Demo version: functionnality disabled!");
            return true;
        }

        return false;
    }

/**
 * Return true if the string is not empty otherwise return false.
 *
 * @method check_string
 * @param string string to check
 */
    private function check_string($input) {
        return isset($input) and $input != '';
    }

/**
 * Return true if the string is not empty and with a size below
 * maximum expected otherwise return false.
 *
 * @method check_string_and_size
 * @param string string to check
 * @param integer maximum size
 */
    private function check_string_and_size($input, $size) {
        return isset($input) and $input != ''
           and strlen($input) <= $size;
    }
}
?>
