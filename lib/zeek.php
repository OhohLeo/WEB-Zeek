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

    private $db_to_css = array(
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
	    "type" => "text",
	    "size" => 65535),
        "MEDIUMTEXT" => array(
	    "type" => "text",
	    "size" => 16777215),
        "LONGTEXT" => array(
	    "type" => "text",
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

	// we create de zeek_library object
	require_once $global_path . "lib/zeek_library.php";

	$zlib = new ZeekLibrary();
	$zlib->global_path = $global_path;
	$zlib->config($config);

	// we establish the connection with the database
	if ($zlib->connect_to_database() == false)
            return false;

	$this->zlib = $zlib;
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
		return $this->user_delete($project_id, $email);

            case 'user_change_password':
		return $this->user_change_password(
		    $project_id, $email, $password_old, $password_new);

	    case 'file_get_list':
	        return $this->file_get_list();

	    case 'file_get':
	        return $this->file_get(
		    strtolower($params['user']),
		    strtolower($params['name']));

	    case 'file_set':
	        return $this->file_set(
		    strtolower($params['user']),
		    strtolower($params['name']),
		    $params['data']);

	    case 'test':
	        return $this->test();

            case 'structure_get':
		return $this->structure_get();

            case 'structure_set':
		return $this->structure_set($new_structure);

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

        // we check the validity of the login & password
        if ($this->check_string_and_size($project_name, 25)
            and $this->check_string_and_size($login, 25)
	    and $this->check_string_and_size($password, 32)
	    and $zlib->user_check($project_id, $login, $password))
        {
            // we store the session user
            $_SESSION["login"] = $login;
            $_SESSION["start_ts"] = time();

            // the project already exist : it is ok!
            if ($project_id)
            {
                $_SESSION["project_name"] = $project_name;
                $_SESSION["project_id"]   = $project_id;

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
 */
    public function project_create($project_name)
    {
        $zlib = $this->zlib;

	// we check the session id
        if ($this->check_string_and_size($project_name, 25))
        {
            // we check if the project_name does not exist
            if ($zlib->project_get_id($project_name) == false)
            {
		// we create the project
		if ($zlib->project_add($project_name) == false)
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
        if (!isset($email)) {
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
        if (!isset($email)) {
            $this->error("Expecting valid user email!");
            return false;
        }

        if ($this->zlib->user_remove($project_id, $email)) {
            $this->success("User '$email' correctly deleted!");
            return true;
        }

        return false;
    }

/**
 * Don't authorized user to connect with this project.
 *
 * @method user_delete
 * @param integer id of the current project
 * @param email email of user to remove
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
	if ($zlib->file_create($project_id, $user, 'html', 'index', true)
 	 && $zlib->file_create($project_id, $user, 'css', $this->project_name))
	{
	    return $this->file_get_list();
	}

	return false;
    }

/**
 * Return the list of current files in "Project" directory
 *
 * @method files_get_list()
 * @param
 */
    public function file_get_list()
    {
	$get_list = $this->zlib->file_get_list($this->project_id);

	if (is_array($get_list))
	{
	    if (count($get_list) > 0)
	    {
		$this->output_json(array('get_list' => $get_list));
		return true;
	    }

	    return $this->file_init();
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
	$zlib = $this->zlib;
	$get = $zlib->file_get($this->project_id, $user, $name);

	if ($get)
	{
	    $this->output_json(array('get' => $get,
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
 * @method
 * @param
 */
    public function file_delete($type, $name)
    {
    }



/**
 * @method
 * @param
 */
    public function file_download($type, $name)
    {
    }

/**
 * @method
 * @param
 */
    public function file_upload($type, $name)
    {
    }

/**
 * @method
 * @param
 */
    public function test()
    {
	$this->output_json(
	    array('href' => 'projects/' . $this->project_id
			. '/' . $_SESSION['login'] . '/index.html'));
    }

/**
 * Method to call to deploy the project on his final directory
 *
 * @method
 * @param
 */
    public function deploy()
    {
	// we copy the whole user directory in the deploy directory

	// foreach js & css files : we minimise the size

	// we send back the deploy URL
	$this->output_json(
	    array('href' => 'projects/' . $this->project_id
			. '/deploy/index.html'));
    }

/**
 * Send the project structure.
 *
 * @method structure_get
 */
    public function structure_get()
    {
	// we get the structure project
	$structure = $this->zlib->structure_get($this->project_name);

	// we go through all domains
	foreach ($structure as $domain => $attribute)
	{
	    // we go through all attribute of each domain
	    foreach ($attribute as $name => $options)
	    {
		// we get the css options
		$css_options = $this->db_to_css[$options['type']];

		// we check that it doesn't exist a specific value
		foreach ($options as $type => $value)
		{
		    // we avoid the option type
		    if ($type === "type")
			continue;

		    // we set the options with the specific value
		    $css_options[$type] = $value;
		}

		// we set the css options before sending the data
		$structure[$domain][$name] = $css_options;
	    }
	}

	$this->output_json(array('structure' => $structure));

	return true;
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

        if ($this->zlib->value_insert($this->project_id, $type, $params)) {
            $this->success("Value correctly inserted!");
            return true;
        }

        return false;
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
            $file =$this->global_path . "view/$type.html";

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
        return isset($input) and $input != '' and strlen($input) <= $size;
    }
}
?>
