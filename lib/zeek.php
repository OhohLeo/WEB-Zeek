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

/**
 * Startup zeek file.
 *
 * @method start
 * @param string configuration file
 */

    public function start($config_file)
    {
        $config = parse_ini_file($config_file);

        /* we get the global_path */
        if (isset($config['global_path'])) {
            $global_path = $config['global_path'] . "/";
        } else {
            $global_path = $_SERVER['DOCUMENT_ROOT'] . "/";
        }

       $this->global_path = $global_path;

       /* we create de zeek_library object */
       require_once $global_path . "lib/zeek_library.php";

       $zlib = new ZeekLibrary();
       $zlib->global_path = $global_path;
       $zlib->config($config);

       $this->zlib = $zlib;
    }

/**
 * Received all the commands from client side.
 *
 * @method input
 * @param string command => project_id
 */
    public function input($params)
    {
        /* we check if the params is defined */
        if ($params == NULL)
            return false;

        /* we establish the connection with the database */
        if ($this->zlib->connect_to_database() == false)
            return false;

        if (isset($params['draw'])) {
            return $this->data_get_tables($params);
        }

        $method = $params['method'];

        /* we check if the method name does exist */
        if ($this->check_string($method) == false) {
            $this->error("method not defined!");
            return false;
        }

        if (isset($params['params']))
            parse_str($params['params']);

        /* we handle the connection method 1st */
        if ($method == 'connect')
            return $this->connect($project_name, $login, $password);
        /* everybody can get the number of stored data */
        else if ($method == 'data_get_number')
            return $this->data_get_number($params['name']);
        /* everybody can get stored data */
        else if ($method == 'data_get')
            return $this->data_get(
                $params['name'], $params['offset'], $params['size']);

        /* otherwise we check if the connection is ok */
        if ($_SESSION["login"] == false)
            return false;

        if ($method == 'project_create')
            return $this->project_create($params['project_name']);

        $project_name = $_SESSION["project_name"];
        $project_id   = $_SESSION["project_id"];

        if (!(isset($project_name) && isset($project_id)))
            return false;

        /* we store project name and id */
        $this->project_name = $project_name;
        $this->project_id   = $project_id;

        switch ($method) {

        case 'disconnect':
             $this->disconnect();
             return true;

        case 'project_delete_to_confirm':
            return $this->project_delete_to_confirm($project_name);

        case 'project_delete':
            return $this->project_delete($project_name);

        case 'user_add':
            return $this->user_add($project_id, $project_name, $email);

        case 'user_delete':
            return $this->user_delete($project_id, $email);

        case 'user_change_password':
            return $this->user_change_password(
                $project_id, $email, $password_old, $password_new);

        case 'data_set':
            return $this->data_set($params['name'], $params['values']);

        case 'data_update':
            return $this->data_update($name, $id, $values);

        case 'data_delete':
            return $this->data_delete($name, $id);

        case 'data_clean_all':
            return $this->data_clean_all();

        case 'get_structure':
            return $this->get_structure();

        case 'clicked':
            return $this->clicked(strtolower($params['type']));
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

        /* we check if the project_name does exist */
        $project_id = $zlib->project_get_id($project_name);

        /* we check the validity of the login & password
           we check 1st forcing the project id to 0 (master admin)
           then we check using the project id if defined */
        if ($this->check_string_and_size($project_name, 25)
        and $this->check_string_and_size($login, 25)
        and $this->check_string_and_size($password, 32)
        and ($zlib->user_check(0, $login, $password)
             or ($project_id > 0
                 and $zlib->user_check($project_id, $login, $password)))) {

            if ($zlib->project_check($project_name) == false) {
                $this->error("No existing project '$project_name'!");
                return false;
            }

            /* we store the session user */
            $_SESSION["login"] = $login;
            $_SESSION["start_ts"] = time();

            if ($project_id) {
                $_SESSION["project_name"] = $project_name;
                $_SESSION["project_id"]   = $project_id;

                $this->project_name = $project_name;

                /* we redirect to the home */
                $this->redirect('home.php');
                return true;
            }

            $this->success(
                'Connection accepted, now create new project!',
                array('action' => 'project_create'));

            return true;
        }

       $this->error('unexpected project name, login & password!');
       return false;
    }

/**
 * Disconnect session and unset all data.
 *
 * @method disconnect
 */
    public function disconnect()
    {
        /* we destroy the session here */
        session_destroy();

        /* we destroy all the data here */
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

        /* we check the session id */
        if ($this->check_string_and_size($project_name, 25)) {

            /* we check if the project_name does not exist */
            if ($zlib->project_get_id($project_name) == false) {

                /* we create the project */
                if ($zlib->project_add($project_name) == false)
                    return false;

                /* we store it */
                $_SESSION["project_name"] = $project_name;
                $_SESSION["project_id"]   = $zlib->project_get_id($project_name);

                /* we redirect to the home */
                $this->redirect('home.php');
                return true;
            }
        }

        $this->error('Project name unacceptable, try again!');
        return false;
    }

    public function project_delete_to_confirm($project_name)
    {
        $this->clean_and_send(
            $this->display_disconnect(
                "Are you sure you want to delete '$project_name' ?",
                "project_delete"));
        return true;
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

            /* we proceed the disconnection */
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
 * Display all navigation bar.
 *
 * @method get_structure
 */
    public function get_structure()
    {
        $result = "<ul class=\"nav nav-sidebar\">\n";

        $structure = $this->zlib->structure_get($this->project_name);

        foreach ($structure as $key => $value) {
            $key = ucfirst($key);
            $result .= "<li><a class=\"clickable\" data-type='$key'>"
                . "$key</a></li>\n";
        }

        $this->output("$result</ul>\n");

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
            $this->project_name,
            $name, array('id' => 'DEC'), $size, $offset);

        $response = array();

        while ($row = $this->zlib->value_fetch($result)) {
            unset($row->id);
            unset($row->project_id);
            array_push($response, $row);
        }

        /* we return an array of values */
        return $this->output($this->json_encode($response));
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
        $records_total = $this->zlib->table_count($name);

        /* we get the elements */
        $result = $this->zlib->value_get(
            $this->project_name,
            $name, array('id' => 'DEC'), $size, $offset);

        $data = array();

        while ($row = $this->zlib->value_fetch($result)) {
            array_push($data, $row);
        }


        $records_filtered = 0;

        $this->output($this->json_encode(array(
            "draw" => intval($params['draw']),
            "recordsTotal" => intval($records_total),
            "recordsFiltered" => intval($records_filtered),
            "data" =>  $data,
        )));

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

        return $this->output($this->json_encode(
            array('count' => $this->zlib->table_count($name))));
    }


/**
 * Return success message or error.
 *
 * @method data_set
 * @param string name of the data expected
 * @param hash values of the data
 */
    public function data_set($name, $values)
    {
        if (!(isset($name) && isset($values))) {
            $this->error("Expecting valid name and values field!");
            return false;
        }

        $params = array();
        parse_str($values, $params);

        if ($this->zlib->value_insert($this->project_name, $name, $params)) {
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
    public function data_update($name, $id, $values)
    {
        if (!(isset($name) && isset($id) && isset($values))) {
            $this->error("Expecting valid name, id and values field!");
            return false;
        }

        if ($this->zlib->value_update(
            $this->project_id, $name, $id, $values)) {
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
 * @method clicked
 * @param string
 */
    public function clicked($type)
    {
        $zlib = $this->zlib;

        $action = NULL;
        $result = '';

        /* we check if it is specified type  */
        if ($zlib->type_check($this->project_name, $type)) {
            $result = $this->display_get_and_set($type);
        }
        /* we handle the disconnect case */
        else if ($type == 'disconnect') {
            $action = 'append';
            $result = $this->display_disconnect(
                "Are you sure you want to disconnect from Zeek ?",
                "disconnect");
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


/*
 * Return html set content.
 *
 * @method display_get_and_set
 * @param string type to display
 */
    private function display_get_and_set($type) {
        /* we check the existence of the table name in the static
         * datastructure */
        $structure = $this->zlib->type_get($this->project_name, $type);
        if ($structure == NULL) {
            $this->error("'$type' not found in static database structure!");
            return false;
        }

        $project_id = $this->project_id;

        ob_start();
        include $this->global_path . "view/get_and_set.html";
        return ob_get_clean();
    }

/**
 * Return html post content.
 *
 * @method display_post
 * @param string element that generate the post request
 * @param string method name to call
 * @param string action to do on success
 */
    public function display_post(
        $element, $action, $method, $data, $on_success)
    {
        $project_id = $this->project_id;
        ob_start();
        include $this->global_path . "view/post.html";
        return ob_get_clean();
    }


/**
 * Return button displayed.
 *
 * @method display_button
 * @param string button title
 * @param string params of the button
 */
    public function display_button($title, $params) {
        ob_start();
        include $this->global_path . "view/button.html";
        return ob_get_clean();
    }

/**
 * Return a modal message before disconnection.
 *
 * @method display_disconnect
 * @param string confirmation message
 * @param string method to call after confirmation
 */
    public function display_disconnect($message, $method) {
        return $this->display_modal(
                $message,
                true,
                NULL,
                $this->display_post(
                    "button.btn-modal",
                    'click',
                    $method,
                    NULL,
                    '$(location).attr("href", "index.php");'),
                NULL);
    }

/**
 * Return html modal content.
 *
 * @method display_modal
 * @param string modal title
 * @param string content of the modal body
 * @param string action to do before
 * @param string action to do after
 */
    public function display_modal($text, $display_footer, $body,
                                  $action_before, $action_after) {
        ob_start();
        include $this->global_path . "view/modal.html";
        return ob_get_clean();
    }

/**
 * Return html content around dynamic div.
 *
 * @method display_dynamic
 * @param string html to put inside dynamic div
 */
    public function display_dynamic($input) {
        ob_start();
        include $this->global_path . "view/dynamic.html";
        return ob_get_clean();
    }


/**
 * Output all the html content in the dynamic content.
 *
 * @method clean_and_send
 * @param string input to send
 */
    public function clean_and_send($action, $input) {

        $input = str_replace(
            array("\n", "  "), "", $this->display_dynamic($input));

        if ($action == 'append') {
            $this->append($input);
            return;
        }

        $this->replace($input);
    }

/**
 * Launch jquery redirection.
 *
 * @method redirect
 * @param string where to redirect
 */
    public function redirect($url) {
        $this->output($this->json_encode(array('redirect' => $url)));
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
