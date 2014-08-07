<?php

/**
 * Zeek : all the function to handle the website & the backoffice.
 *
 * @package Zeek
 */
class Zeek extends ZeekOutput {

    public $global_path;
    private $project_id;
    private $zlib;

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
            return $this->connect($project_name, $login, $password);

         case 'disconnect':
             $this->disconnect();
             return true;

        case 'project_create':
            $this->create_new_project($params['project_name']);
            return true;

        case 'project_delete':
            return true;

        case 'admin_add':
            return true;

        case 'admin_remove':
            return true;

        case 'password_change':
            return true;

        case 'data_reset':
            return true;

        case 'get_structure':
            $result = "<ul class=\"nav nav-sidebar\">\n";

            $structure = $this->zlib->structure_get();

            foreach ($structure as $key => $value) {
                $key = ucfirst($key);
                $result .= "<li><a class=\"clickable\" data-type='$key'>"
                    . "$key</a></li>\n";
            }

            $this->output("$result</ul>\n");

            return true;

        case 'create_type':
            $type = $params['type'];
            /* $this->db->row_insert($type, $params); */
            return true;

        case 'clicked':
            return $this->clicked(strtolower($params['type']));
        }

        $this->error("unknown method '$method' with parameters "
        . var_dump($params));
        return false;
    }


    public function connect($project_name, $login, $password)
    {
        $zlib = $this->zlib;

        /* we establish the connection with the database */
        if ($zlib->connect_to_database() == false)
            return false;

        /* we check the validity of the login & password */
        if ($this->check_string_and_size($project_name, 25)
        and $this->check_string_and_size($login, 25)
        and $this->check_string_and_size($password, 32)) {
        /* and $zlib->user_check($login, $password)) { */

            /* we store the session user */
            $_SESSION["username"] = $login;
            $_SESSION["start_ts"] = time();

            /* we check if the project_name does exist */
            if ($zlib->project_check($project_name)) {

                $_SESSION["project_name"] = $project_name;

                /* we redirect to the home */
                $this->redirect('home.php');
                return true;
            }

            $this->success(
                'Connection accepted, now create new project!',
                array('action' => 'project_create'));

            return true;
        }

       $this->error('unexpected login & password!');
       return false;
    }

    public function disconnect()
    {
        /* we destroy the session here */
        session_destroy();

        /* we destroy all the data here */
        session_unset();
    }

    public function create_new_project($project_name)
    {
        $zlib = $this->zlib;

        /* we check the session id */
        if (isset($_SESSION["username"])
            and $this->check_string_and_size($project_name, 25)) {

            /* we establish the connection with the database */
            if ($zlib->connect_to_database() == false)
                return false;

            /* we check if the project_name does not exist */
            if ($zlib->project_check($project_name) == false) {

                /* we create the project */
                $zlib->project_add($project_name);

                /* we store it */
                $_SESSION["project_name"] = $project_name;

                /* we redirect to the home */
                $this->redirect('home.php');
                die();
            }
        }

        $this->error('Project name unacceptable, try again!');
    }

    public function clicked($type)
    {
        $zlib = $this->zlib;

        $result = '';

        /* we check if it is specified type  */
        if ($zlib->type_check($type)) {
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
                    '$(location).attr("href", "index.php");'),
                NULL);
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
            $this->output(
                str_replace(
                    array("\n", "  "), "", $this->display_dynamic($result)));
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
        /* we check the existence of the table name in th}e static
         * datastructure */
        $structure = $this->zlib->type_get($type);
        if ($structure == NULL) {
            $this->error("'$type' not found in static database structure!");
            return false;
        }

        $project_id = $this->project_id;

        ob_start();
        include $this->global_path . "view/get_and_set.html";
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
        include $this->global_path . "view/post.html";
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
        include $this->global_path . "view/button.html";
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
        include $this->global_path . "view/modal.html";
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
        include $this->global_path . "view/dynamic.html";
        return ob_get_clean();
    }

/*
 * launch jquery redirection.
 *
 * @method redirect
 * @param string where to redirect
 */
    public function redirect($url) {
        $this->output($this->json_encode(array('redirect' => $url)));
    }

    private function check_string($input) {
        return isset($input) and $input != '';
    }

    private function check_string_and_size($input, $size) {
        return isset($input) and $input != '' and strlen($input) <= $size;
    }


    protected function session_start()
    {
        session_start();
    }

}
?>
