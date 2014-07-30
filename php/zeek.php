<?php

$zeek = new ZeekProject();

if (isset($_POST)) {
    $zeek->input($_POST);
}

/**
 * Zeek : all the function to handle the website & the backoffice.
 *
 * @package Zeek
 */
class ZeekProject {

    protected $access;
    private $project_id = 1;
    private $db_name = 'zeek';

    private $data_structure = array(
        'project'    => array(
            'name'        => array('VARCHAR', 25),
            'since'       => 'DATE',
            'subtitle'    => array('VARCHAR', 300),
            'biography'   => array('TEXT', 1000)),
        'artist'      => array(
            'name'       => array('VARCHAR', 100),
            'surname'    => array('VARCHAR', 100),
            'age'        => array('INT', 11),
            'subtitle'   => array('VARCHAR', 300),
            'biography'  => array('TEXT', 1000),
            'skill'      => array('VARCHAR', 100),
            'project_id' => array('INT', 11)),
        'show'        => array(
            'name'       => array('VARCHAR', 100),
            'date'       => 'DATE',
            'hour'       => 'TIME',
            'location'   => array('VARCHAR', 300),
            'project_id' => array('INT', 11)),
        'news'        => array(
            'name'       => array('VARCHAR', 100),
            'date'       => 'DATE',
            'comments'   => array('TEXT', 1000),
            'project_id' => array('INT', 11)),
        'album'       => array(
            'name'       => array('VARCHAR', 100),
            'duration'   => array('INT', 11),
            'comments'   => array('TEXT', 1000),
            'project_id' => array('INT', 11)),
         'music'       => array(
            'name'       => array('VARCHAR', 100),
            'date'       => 'DATE',
            'duration'   => array('INT', 11),
            'comments'   => array('TEXT', 1000),
            'project_id' => array('INT', 11)),
         'video'       => array(
            'name'       => array('VARCHAR', 100),
            'date'       => 'DATE',
            'duration'   => array('INT', 11),
            'comments'   => array('TEXT', 1000),
            'project_id' => array('INT', 11)),
         'media'       => array(
            'name'       => array('VARCHAR', 100),
            'date'       => 'DATE',
            'comments'   => array('TEXT', 1000),
            'project_id' => array('INT', 11)));


/**
 * Establish a connection with MySQL database
 *
 * @method connect_to_database
 * @param array connections parameters
 */
    public function connect_to_database()
    {
        require_once '/home/leo/zeek/php/database_access.php';


        /* we read the configuration file giving access to the
         * database name, login & password */

        $db_name = 'test';
        $login = 'test';
        $password = 'test';

        $access = new DataBaseAccess();

        $access->set_master($this);

        /* we store the database access */
        $this->access = $access;

        /* we try to establish a connection */
        if ($access->connect($db_name, $login, $password) == false) {
            return false;
        }

        /* We check if the database already exists */
        if ($access->database_check($db_name) == false) {
            return $this->environment_setup(
                $db_name, $login, $password);
        }

        /* We will use only this database */
        $access->database_use($db_name);

        return true;
    }

/**
 * Create environment
 *
 * Return false if an environement already exists,
 * otherwise return true.
 *
 * @method environment_add
 * @param string environement name
 */
    public function environment_setup($name, $login, $password)
    {
        /* we store the database access */
        $access = $this->access;

        /* we create the database */
        $access->database_create($name);

        /* we use this database */
        $access->database_use($name);

        /* we create the user table */
        $access->table_create('user', array(
            'name' => array('VARCHAR', 25),
            'password' => array('CHAR', 32)));

        /* we add the actual user */
        $access->row_insert('user', array(
            'name' => $login,
            'password' => $password));

        /* we create the project table */
        $access->table_create(
            'project', $this->data_structure['project']);

        return true;
    }

/**
 * Cleanup all environment
 *
 * @method environment_add
 * @param string environement name
 */
    public function environment_clean($name)
    {
        /* we create the database */
        $this->access->database_delete($name);
    }

    public function user_add($username, $password)
    {
        /* We check if the project already exists */
        if ($this->user_check($username, $password)) {
            $this->error("User $username already exist!");
            return false;
        }

        /* we insert the new project */
        if ($this->value_insert(
            'user', array(
                'name' => $username,
                'password' => $password ))) {
            return true;
        }

        $this->error("Impossible to add new user!");
        return false;
    }

    public function user_change_password(
        $username, $old_password, $new_password)
    {
        /* we get the user */
        $user = $this->user_get($username);
        if ($user == NULL) {
            $this->error(
                "Can't change password : '$username' doesn't exist!");
            return false;
        }

        /* we check the old password */
        if ($old_password !== $user->password) {
            $this->error(
                "Can't change password : unexpected old password!");
            return false;
        }

        if ($this->access->row_update(
            'user', $user->id,
            array('password' => $new_password))) {
            return true;
        }

        return false;
    }

    public function user_remove($username)
    {
        $user = $this->user_get($username);

        /* We check if the project already exists */
        if ($user == NULL) {
            $this->error("Can't remove user, '$username' doesn't exist!");
            return false;
        }

        /* we insert the new project */
        if ($this->access->row_delete(
            'user', array('name' => $username))) {
            return true;
        }

        return false;
    }

    public function user_get($username)
    {
        $result = $this->access->table_view(
            'user', '*', NULL, NULL, NULL,
            array('name' => $username));

        return ($result == NULL) ? NULL : $result->fetch();
    }

    public function user_check($username, $password)
    {
        $result = $this->access->table_view(
            'user', 'name', NULL, NULL, NULL,
            array('name'     => $username,
                  'password' => $password));

        if ($result == NULL) {
            return false;
        }

        if ($row = $result->fetch()) {
            return true;
        }

        return false;
    }

/**
 * Create a new project
 *
 * Return false if a project with the same name already exists,
 * otherwise return true.
 *
 * @method project_add
 * @param string project name
 */
    public function project_add($project_name)
    {
        $access = $this->access;

        /* We check if the project already exists */
        if ($this->project_check($project_name)) {
            $this->error(
                "Another project have the same name $project_name!");
            return false;
        }

        /* we insert the new project */
        if ($this->value_insert(
            'project', array('name' => $project_name)) == false) {
            $this->error("Impossible to create new project!");
            return false;
        }

        /* we store the project id */
        if ($this->project_check($project_name)) {
            return true;
        }

        $this->error("New project not found!");
        return false;
    }


/**
 * Delete the actual project.
 *
 * Return true if the project and all the relashion has been removed,
 * otherwise return false.
 *
 * When we delete the last remaining project : it removes the database.
 *
 * @method project_delete
 * @param string project name
 */
    public function project_delete()
    {
        $access = $this->access;

        /* if it exists only one project : we delete the database */
        if ($access->table_count('project', '*', NULL) < 2) {
            return $access->database_delete($this->db_name);
        }

        $params = array('project_id' => $this->project_id);

        /* otherwise : all links containing the actual project_id */
        foreach ($this->data_structure as $name => $value) {

            /* we check if the table exist, otherwise we continue */
            if ($access->table_check($name) == false) {
                continue;
            }

            /* for the project : we remove the row with the project_id
             * specified */
            if ($name == 'project') {
                $access->row_delete('project', $params);
                continue;
            }

            /* for all other tables : we check if each table contain
             * other reference of project_id */
            if ($access->table_count($name, 'project_id', NULL) > 1) {
                /* we remove all row with actual project_id */
                $access->row_delete($name, $params);
            } else {
                /* otherwise we delete the table */
                $access->table_delete($name);
            }
        }

        return true;
    }


/**
 * Check if a project exists and get his id.
 *
 * Return true if the project exists otherwise return false.
 *
 * @method project_check
 * @param string project name
 */
    public function project_check($project_name)
    {
        $result = $this->access->table_view(
            'project', 'id', NULL, NULL, NULL,
            array('name' => $project_name));

        if ($result == NULL) {
            return false;
        }

        if ($row = $result->fetch()) {
            $this->project_id = $row->id;
            return true;
        }

        return false;
    }


/**
 * Check if a table exists otherwise automatically create if this
 * table is referenced on the static table.
 *
 * Return true if the table exists or is succesfully created otherwise
 * return false.
 *
 * @method table_check_and_create
 * @param string table name
 */
    protected function table_check_and_create($name)
    {
        /* we check if the table exists */
        if ($this->access->table_check($name)) {
            return true;
        }

        /* otherwise we check the existence of the table name in the
         * static datastructure */
        $structure = $this->data_structure[$name];
        if ($structure == NULL) {
            $this->error("'$name' not found in static database structure!");
            return false;
        }

        /* we create the table and all attributes */
        if ($this->access->table_create($name, $structure))
        {
            return true;
        }

        $this->error("Impossible to create $name table!");
        return false;
    }

/**
 * Check if a table exists otherwise automatically create it.
 *
 * Add in all cases the values specified.
 *
 * Return true if the values are correctly added otherwise
 * return false.
 *
 * @method value_insert
 * @param string table name
 * @param hash values to insert
 */
    public function value_insert($name, $values)
    {
        /* we check if the table exists */
        if ($this->table_check_and_create($name)) {

            /* we insert the new value */
            return $this->access->row_insert($name, $values);
        }

        return false;
    }


/**
 * Modified a value already stored.
 *
 * Return true if the values are modified added otherwise
 * return false.
 *
 * @method value_update
 * @param string table name
 * @param integer id of the element to modify
 * @param hash values that will replace old one
 */
    public function value_update($name, $id, $values)
    {
        /* we add the project id */

        return $this->access->row_update($name, $id, $values);
    }

/**
 * Get values stored in database.
 *
 * @method value_get
 * @param string table name
 * @param string wich parameter to use for sorting
 * @param integer number of elements
 * @param integer offset
 */
    public function value_get($name, $sort, $size, $offset)
    {
        /* we check if the table exists */
        if ($this->table_check_and_create($name)) {

            $params = NULL;

            /* we set up the generic filter */
            if ($name = 'project') {
                $param = array('id' => $this->project_id);
            } else {
                $param = array('project_id' => $this->project_id);
            }

            /* we get all the values desired */
            return $this->access->table_view(
                $name, '*', NULL, $size, $offset, $param);
        }

        return NULL;
    }


/**
 * Received all the data from client side.
 *
 * @method input
 * @param string command => project_id
 */
    public function input($params)
    {
        /* we check if the params is defined */
        if ($params == NULL) {
            return false;
        }

        /* we check if the method name does exist */
        if ($this->check_string($params['method']) == false) {
            $this->error("method not found");
            return false;
        }

        /* we get & check the project id */
        $project_id = $params['project_id'];

        /* we check if the project_id is numeric */
        /* and does exist (TODO) */
        if ($project_id != NULL) {
            if (is_numeric($project_id) == false) {
                $this->error("unexpected project_id '$project_id'!");
            }
        }

        switch ($params['method']) {

        case 'connect':
            parse_str($params['params']);

            /* we establish the connection with the database */
            $this->connect_to_database();

            /* we check the validity of the login & password */
            if ($this->check_string_and_size($project_name, 25)
            and $this->check_string_and_size($login, 25)
            and $this->check_string_and_size($password, 32)
            and $this->user_check($login, $password)) {

                /* we start the session */
                session_start();

                /* we store the session user */
                $_SESSION["username"] = $login;

                /* we check if the project_name does exist */
                if ($this->project_check($project_name)) {

                    $_SESSION["project_name"] = $project_name;

                    /* we redirect to the home */
                    $this->redirect('home.php');
                    die();
                }

                $this->success(
                    'Connection accepted, now create new project!',
                    array('action' => 'new_project'));

                return false;
        }

       $this->error('unexpected login & password!');
       return false;

         case 'disconnect':
             /* we start the session */
             session_start();

             /* we destroy all the data here */
             $_SESSION = array();

             /* we destroy the session here */
             session_destroy();

             die();

        case 'create_new_project':

            $project_name = $params['project_name'];

            /* we start the session */
            session_start();

            /* we check the session id */
            if (isset($_SESSION["username"])
                and $this->check_string_and_size($project_name, 25)) {

                /* we establish the connection with the database */
                $this->connect_to_database();

                /* we check if the project_name does not exist */
                if ($this->project_check($project_name) == false) {

                    /* we create the project */
                    $this->project_add($project_name);

                    /* we store it */
                    $_SESSION["project_name"] = $project_name;

                    /* we redirect to the home */
                    $this->redirect('home.php');
                    die();
                }
            }

            $this->error('Project name unacceptable, try again!');
            die();

        case 'get_structure':

            $result = "<ul class=\"nav nav-sidebar\">\n";

            foreach ($this->data_structure as $key => $value) {
                $key = ucfirst($key);
                $result .= "<li><a class=\"clickable\" data-type='$key'>"
                    . "$key</a></li>\n";
            }

            $this->output("$result</ul>\n");

            return true;

        case 'create_type':
            $type = $params['type'];
            /* $this->access->row_insert($type, $params); */
            return true;

        case 'clicked':
            $type = strtolower($params['type']);

            $result = '';

            /* we check if it is specified type  */
            if (array_key_exists($type, $this->data_structure)) {
                $result = $this->display_get_and_set($type);
            }
            /* we handle the disconnect case */
            else if ($type == 'disconnect') {
                $result = $this->display_modal(
                    "Are you sure you want to disconnect from Zeek ?",
                    true,
                    NULL,
                    $this->display_post(
                        "button.btn-modal",
                        'click',
                        "disconnect",
                        NULL,
                        '$(location).attr("href", "welcome.php");'));
            }
            /* we handle other cases */
            else if (file_exists("/home/leo/zeek/view/$type.html")) {
                $file = "/home/leo/zeek/view/$type.html";

                $handle = fopen($file, 'r');

                if ($handle == NULL) {
                    $this->error("impossible to open file '$file'!");
                    return false;
                }

                $result = fread($handle, filesize($file));

                fclose($handle);
            }

            if ($result) {
                $this->output(
                    str_replace(
                        array("\n", "  "), "", $this->display_dynamic($result)));
                return true;
            }

            $this->error("unexpected type '$type'!");
            return false;
        }

        $this->error("unknown method '$method' with parameters "
        . var_dump($params));
        return false;
    }


/*
 * Return html set content.
 *
 * @method display_get_and_set
 * @param string type to display
 */
    private function display_get_and_set($type) {
        /* we check the existence of the table name in th}e static
         * datastructure */
        $structure = $this->data_structure[$type];
        if ($structure == NULL) {
            $this->error("'$type' not found in static database structure!");
            return false;
        }

        $project_id = $this->project_id;

        ob_start();
        include '/home/leo/zeek/view/get_and_set.html';
        return ob_get_clean();
    }

/*
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
        include '/home/leo/zeek/view/post.html';
        return ob_get_clean();
    }


/*
 * Return button displayed.
 *
 * @method display_button
 * @param string button title
 * @param string params of the button
 */
    public function display_button($title, $params) {
        ob_start();
        include '/home/leo/zeek/view/button.html';
        return ob_get_clean();
    }

/*
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
        include '/home/leo/zeek/view/modal.html';
        return ob_get_clean();
    }

/*
 * Return html content around dynamic div.
 *
 * @method display_dynamic
 * @param string html to put inside dynamic div
 */
    public function display_dynamic($input) {
        ob_start();
        include '/home/leo/zeek/view/dynamic.html';
        return ob_get_clean();
    }

/*
 * launch jquery redirection.
 *
 * @method redirect
 * @param string where to redirect
 */
    public function redirect($url) {
        $this->output(json_encode(array('redirect' => $url)));
    }

/**
 * Echo all data to send to the client side.
 *
 * @method output
 * @param string data to display
 */
    protected function output($input) {
        echo $input;
    }

    public function success($input, $params) {
        $result = array('success' => $input);

        if ($params != NULL) {
            foreach ($params as $key => $value) {
                $result[$key] = $value;
            }
        }

        /* we display it */
        $this->output(json_encode($result));
    }

/**
 * Echo errors in JSON format.
 *
 * @method error
 * @param string data to display
 */
    public function error($input) {
        echo json_encode(array('error' => $input));
    }

    private function check_string($input) {
        return isset($input) and $input != '';
    }

    private function check_string_and_size($input, $size) {
        return isset($input) and $input != '' and strlen($input) <= $size;
    }
}
?>
