<?php
/**
 * Zeek : all the function to handle the website & the backoffice.
 *
 * @package Zeek
 */
class Zeek extends ZeekOutput {

    public $global_path;
    protected $project_id;
    protected $project_name;
    protected $zlib;

    private $plugins_list;
    private $type_list;
    private $content_type_list;
    private $content_directory_list;
    private $mime_validator;

    private $type_simple = array(
        "TITLE"    => array("db_type" => "VARCHAR", "db_size" => 100),
        "IMAGE"    => array("db_type" => "LONGBLOB"),
        "TEXT"     => array("db_type" => "LONGTEXT"),
        "INTEGER"  => array("db_type" => "INTEGER"),
        "NUMBER"   => array("db_type" => "BIGINT"),
        "FLOAT"    => array("db_type" => "FLOAT"),
        "DATE"     => array("db_type" => "DATE"),
        "TIME"     => array("db_type" => "TIME"),
        "YEAR"     => array("db_type" => "YEAR"),
        "DATETIME" => array("db_type" => "TIMESTAMP"),
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
        "TIMESTAMP" => array("type" => "datetime"),
        "DATETIME"  => array("type" => "datetime"),
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

        // we get the global_path
        if (isset($config['global_path'])) {
            $global_path = $config['global_path'];
	} else if (isset($_SERVER['DOCUMENT_ROOT'])) {
	    $global_path = $_SERVER['DOCUMENT_ROOT'];
        } else {
            $global_path = getcmd();
        }

	$global_path .=  "/";

	$this->global_path = $global_path;

        // we initialise using $php_errormgs
        ini_set('track_errors', 1);

	// we create de zeek_library object
	require_once $global_path . "lib/zeek_library.php";

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

            case 'project_delete':
		return $this->project_delete($project_name);

            case 'user_add':
		return $this->user_add($project_id, $project_name, $email);

            case 'user_delete':
	        return $this->user_delete($project_id, $params["email"]);

            case 'users_get_list':
	        return $this->users_get_list($project_id);

            case 'user_change_password':
		return $this->user_change_password(
		    $project_id, $email, $password_old, $password_new);

            case 'content_add_directory':
		return $this->content_add_directory(
                    $params["directory"], $params["options"]);

            case 'content_remove_directory':
	        return $this->content_remove_directory(
                    $params["directory"]);

            case 'content_add':
		return $this->content_add(
                    $params["directory"], $params["name"]);

            case 'content_move':
		return $this->content_update(
                    $params["old_directory"], $params["old_name"],
                    $params["new_directory"], $params["new_name"]);

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

            case 'contents_unset_type':
                return $this->contents_unset_type($params["name"]);

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
		    "projects/" . $project_id . "/"
                                . $user . "/". $params["src"]);

	    case 'file_delete':
	        return $this->file_delete(strtolower($params['name']));

	    case 'file_get_list':
	        return $this->file_get_list();

	    case 'file_get_type_list':
	        return $this->file_get_type_list(true);

	    case 'file_get':
	        return $this->file_get(
		    $params['user'], $params['name']);

	    case 'file_set':
	        return $this->file_set(
		    strtolower($params['user']),
		    strtolower($params['name']),
		    $params['data']);

	    case 'test':
	    return $this->test($params['options']);

	    case 'deploy':
	    return $this->deploy($params['dst'],
                                 $params['options']);

            case 'option_get':
                return $this->option_get(
                    strtolower($params['name']));

            case 'option_set':
                return $this->option_set(
                    strtolower($params['name']),
                    $params['options']);

            case 'structure_get':
		return $this->structure_get();

            case 'structure_set':
	        return $this->structure_set($params['structure']);

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

            case 'data_clean_all':
		return $this->data_clean_all();
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

            // we store the session user
            $_SESSION["login"] = $login;
            $_SESSION["start_ts"] = time();

            // the project already exist : it is ok!
            if ($project_id)
            {
                $_SESSION["project_name"] = $project_name;
                $_SESSION["project_id"]   = $project_id;
                $_SESSION["project_path"] = isset($projects_path);
                $_SESSION["project_dst"]  = $this->global_path;

                $this->project_name = $project_name;

                // we redirect to the home
                $this->redirect('home.php');
                return true;
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
 * @param array options associated to the the project
 */
    public function project_create($project_name, $options = null)
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
                    // zeekify plugins is always here
                    $plugin_files = array("zeekify" => true);

                    // we activate all plugins by default
                    foreach ($this->plugins_get_list("files") as $plugin_name)
                    {
                        $plugin_files[$plugin_name] = true;
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
                        'files' => $plugin_files);
                }

		// we create the project
		if ($zlib->project_add($project_name, $options) == false)
		{
		    $this->error('Impossible to add project!');
		    return false;
		}

		// we store it
		$_SESSION["project_name"] = $project_name;

		$project_id = $zlib->project_get_id($project_name);
		if ($project_id == false)
		{
		    $this->error('Impossible to get project id!');
		    return false;
		}

		$_SESSION["project_id"] = $zlib->project_get_id($project_name);

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
 * Authorised new user to connect with this project.
 *
 * @method user_add
 * @param integer id of the current project
 * @param email email to send new password to user
 */
    public function user_add($project_id, $project_name, $email)
    {
        /* we check if the email is set */
        if ($this->check_string($email) == false) {
            $this->error("Expecting valid user email!");
            return false;
        }

        /* TODO!!  we check if the user doesn't already exist */
        if ($this->zlib->user_get($project_id, $email)) {
            $this->error("The user '$email' already exist!");
            return false;
        }

        /* we check if the email has valid format */
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $this->error("Expected a valid email adress, received '$email'!");
            return false;
        }

        /* otherwise we create the user with the password randomly */
        $password = $this->password_generate(8);

        /* we send the password to the user */
        if ($this->send_email(
            $email,
            "Login Access to Zeek '$project_name'",
            "Welcome to Zeek '$project_name':\n"
            . "login: $email\n"
          . "password : $password\n")) {

            if ($this->zlib->user_add($project_id, $email, $password)) {
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
 * Don't authorized user to connect with this project.
 *
 * @method user_change_password
 * @param integer id of the current project
 * @param email email of user to remove
 * @param string old password
 * @param string new password
 */
    public function user_change_password(
        $project_id, $email, $password_old, $password_new)
    {
        if (!(isset($password_old) or isset($password_new))) {
            $this->error("Expecting valid old and new password!");
            return false;
        }

        if ($this->zlib->user_change_password(
            $project_id, $email, $password_old, $password_new)) {
            $this->success("User password correctly changed!");
            return true;
        }

        return false;
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
        $this->content_type_list[$name] = [ $directory_name, $mime, $options ];

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
 * @param string file input
 * @param string file name
 */
    public function content_add($directory_name, $input, $name=null)
    {
        if ($this->content_valid_directory($directory_name) == false)
            return false;

        // Get the uploaded files
        $uploaded = "files/" . $input;

        if ($name == null)
            $name = $input;

        $zlib = $this->zlib;

        // Get the extension of the content
        $extension = $zlib->file_get_extension($input);

        // Remove the extension of the destination name
        if ($zlib->file_get_extension($name) === $extension)
            $name = substr($name, 0, strpos($name, '.'));

        // Move source file to destination file
        $result = false;

	if ($zlib->file_modify(
	    $this->project_id, $_SESSION["login"], $uploaded,
            $directory_name, $name, $extension))
	{
	    $this->success(
		"content '$uploaded' stored as "
	      . "'$directory_name/$name.$extension'!");

	    $result = true;
	}

        $this->zlib->uploaded_files_delete();
        return $result;
    }

/**
 * Move existing image to another directory/name
 *
 * @method content_move
 * @param string old directory name
 * @param string old file name
 * @param string new directory name
 * @param string new file name
 */
    public function content_move($old_directory, $old_name,
                                 $new_directory, $name)
    {
        if ($this->content_valid_directory($old_directory) == false)
            return false;

        if ($this->content_valid_directory($new_directory) == false)
            return false;

        $zlib = $this->zlib;

        $file_to_move =  "projects/" . $this->project_id
                       . "/". $_SESSION["login"]
                       . "/$old_directory/$old_name";

        // Get the extension of the content
        $extension = $zlib->file_get_extension($old_name);

        // Remove the extension of the destination name
        if ($zlib->file_get_extension($name) === $extension)
            $name = substr($name, 0, strpos($name, '.'));

        // Move source file to destination file
        $result = false;

	if ($zlib->file_modify(
	    $this->project_id, $_SESSION["login"], $file_to_move,
            $new_directory, $name, $extension))
	{
	    $this->success(
		"content '$file_to_move' stored as '$new_directory/$name.$extension'!");

	    $result = true;
	}

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
                $this->project_id, $_SESSION["login"], $name);

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
	if (file_exists($zlib->file_get_path($project_id, $user, 'html', 'index', true)))
	    return true;


	// the index.html file of deploy directory exist :
	// we copy the whole deploy directory in the user's
	if (file_exists($zlib->file_get_path($project_id, 'deploy', 'html', 'index', true)))
	{
	    return $zlib->directory_copy("projects/$project_id/deploy",
					 "projects/$project_id/$user");
	}

	// otherwise we create a generic index.html & css directory
	if ($zlib->file_create($project_id, $user, 'html', 'index', 'html', true))
            //&& $zlib->file_create($project_id, $user, 'css', $this->project_name, 'css'))
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

        // TODO : the file chosen
        //if ($user !==  $_SESSION['login'])
        //{
        //
        //}

	if ($zlib->file_set($this->project_id, $user, $name, $data))
	{
	    $this->success(
		$zlib->file_get_path($this->project_id, $user, $name)
	      . " correctly updated");
	    return true;
	}

	return false;
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

        return array();
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


        $valid = array("name"   => "[a-z]+",
                       "offset" => "\d+",
                       "size"   => "\d+");

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
        $offset = array_key_exists('offset', $options) ? $options['offset'] : 0;

        if (is_numeric($offset) == false)
            return "Invalid offset option '$offset': expect an integer!";

        // we check the optional option : size (5 by default)
        $size = array_key_exists('size', $options) ? $options['size'] : 5;

        if (is_numeric($size) == false)
            return "Invalid size option '$size': expect an integer!";

        $structure = $zlib->type_get($this->project_name, $table_name);

        if ($structure == false)
            return "Zeek '$table_name' not found!";

        $result = $zlib->value_get(
            $this->project_id, $table_name, array('id' => 'DEC'), $size, $offset);

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
                    // TODO : handle different attribute types
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
        // we check that the json is well formated
        $decode_options = $this->json_decode($options);
        if ($decode_options == NULL)
        {
            $this->error("Invalid option values '$options'!");
            return false;
        }

        $dst = 'projects/' . $this->project_id
	     . '/TEST_' . $_SESSION['login'];


        // we deploy the files & apply all the data plugins
        if ($this->deploy_files(
            $this->global_path . $dst, $decode_options) == false)
            return false;

	$this->output_json(array('href' => $dst . '/index.html'));
        return true;
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
        // if the deploy command doesn't come from a master user : we get from the database

        // if the destination doesn't begin with '/':
        // we add the global path before the destination
        //$dst

        // if the options are not set : we get them from the database

        // we check that the json is well formated
        $decode_options = $this->json_decode($options);
        if ($decode_options == NULL)
        {
            $this->error("Invalid option values '$options'!");
            return false;
        }

        // we deploy the files & apply all the plugins
        if ($this->deploy_files($dst, $decode_options) == false)
            return false;

        // we apply all the deploy plugins

        // we store default dst & options

	$this->success("Successfully deployed!");
        return true;
    }

/**
 * Method to call to deploy the project on hisnal directory
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
        if ($zlib->directory_remove($destination) == false)
            return false;

        // we start from clean directory
        if ($zlib->directory_create($destination) == false)
            return false;

        foreach ($files as $file)
        {
            if ($file['user'] != $_SESSION['login'])
                continue;

            // check if the file directory exist
            if ($file['in_main_directory'] == false
                && $zlib->directory_create(
                    $destination . '/' . $file['type']) == false)
                        return false;

            // we get the file content
            $input = $zlib->file_get($project_id,
                                     $file['user'],
                                     $file['name']);
            if ($input == false) {
                $this->error("No file found : " + $file['user']
                    + " " + $file['name'] + "!");
                return false;
            }

            // we handle the options
            foreach ($options as $option => $is_activated)
            {
                if ($is_activated == false)
                    continue;

                // we handle 'files' plugin here
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
        }

        return true;
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


/**
 * Send the project structure.
 *
 * @param boolean expert mode
 *
 * @method structure_get_list
 */
    public function structure_get_list($expert_mode)
    {
	$this->output_json(array('list' =>
            array_keys($expert_mode ? $this->type_complex : $this->type_simple)));

	return true;
    }


/**
 * Send the project structure.
 *
 * @method structure_get
 */
    public function structure_get()
    {
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
        if (!(isset($name) && isset($offset) && isset($size))) {
            $this->error("Expecting valid table name, offset and size field!");
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
    public function redirect($url) {
        $this->output_json(array("redirect" => $url));
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
