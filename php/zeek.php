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

    private $access;
    private $project_id;
    private $db_name = 'zeek';

    private $data_structure = array(
        'project'    => array(
            'name'        => array('VARCHAR', 100),
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
 * @method connect
 * @param array connections parameters
 */
    public function connect($project_name, $login, $password)
    {

        require_once 'database_access.php';

        $db_name = $this->db_name;

        $access = new DataBaseAccess();

        /* we store the database access */
        $this->access = $access;

        /* we try to establish a connection */
        if ($access->connect($db_name, $login, $password) == false) {
            return false;
        }

        /* We check if the database already exists */
        if ($access->database_check($db_name) == false) {
            /* we ask to create a new project */
            $this->output("CREATE '$project_name'");
            return false;
        }

        /* We will use only this database */
        $access->database_use($db_name);

        /* We check if the project table already exists */
        if ($access->table_check('project') == false) {
            /* error observed : ask to create a new project */
            return false;
        }

        /* We check if the project already exists */
        if ($this->project_check($project_name)) {
            $this->output("GO NEXT STEP '$project_name'");
            return true;
        }

        /* we ask to create a new project */
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
        $db_name = $this->db_name;
        $access = $this->access;

        $access->set_debug(true);

        /* we create a new database if needed */
        if ($access->database_create($db_name) == false) {
            $this->error("Impossible to create database '$db_name'!");
            return false;
        }

        if ($access->database_use($db_name) == false) {
            $this->error("Impossible to select database '$db_name'!");
            return false;
        }

        /* We check if the project already exists */
        if ($this->project_check($project_name)) {
            $this->error(
                "Another project have the same name '$project_name'!");
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

        $this->error("Impossible to create '$name' table!");
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
        if ($this->is_not_empty($params['method']) == false) {
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
            $text = $params['params'];
            /* a comprendre! */
            $this->output("received" . $text);
            $un = unserialize($text);
            $this->output("userialise : " . $un);

            if ($this->is_not_empty($params['project_name'])
                and $this->is_not_empty($params['login'])
                and $this->is_not_empty($params['password'])) {
                $this->output('OK!');
                $this->connect(
                    $params['project_name'],
                    $params['login'],
                    $params['password']);

                return true;
            }

            return false;

        case 'disconnect':
            return false;

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
            $this->access->row_insert($type, $params);
            $this->output(var_dump($params) . "\n");
            return true;

        case 'clicked':
            $type = strtolower($params['type']);

            $result = '';

            /* we check if it is specified type  */
            if (array_key_exists($type, $this->data_structure)) {
                $result = $this->display_create_type($project_id, $type);
            }
            /* we handle the disconnect case */
            else if ($type == 'disconnect') {
                $result = $this->display_modal(
                    "Are you sure you want to disconnect from Zeek ?",
                    true,
                    NULL,
                    $this->display_post(
                        "button.btn-modal",
                        "disconnect",
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


    private function display_get($type) {
        /* we check the existence of the table name in th}e static
         * datastructure */
        $structure = $this->data_structure[$type];
        if ($structure == NULL) {
            $this->error("'$type' not found in static database structure!");
            return false;
        }

        /* we set up the array header */
        foreach ($structure as $key => $format) {

        }

        /* then we display the list of elements */
        $this->value_get();
    }

    private function display_create_type($project_id, $type) {

        return $this->display_button(
            "Create a new $type!",
            'btn-block btn-success btn-create-type')
            . $this->display_modal(
                "Create a new $type!",
                false,
                $this->display_set($type),
                '$("button.btn-create-type").on("click", function() {',
                '});');


            /* . $this->display_modal( */
            /*     "button.btn-modal", */
            /*     "create_type", */
            /*      */
            /*     $set, */
            /*     '$("div.modal-footer").hide(); */
            /*      $("form").show(); */

            /*      $.ajax({ */
            /*        type: "POST", */
            /*        url: "php/zeek.php", */
            /*        data: { */
            /*          "method": "create_type", */
            /*          "project_id": "$project_id", */
            /*          "type": "$type", */
            /*          "params": $(this).serialize() */
            /*        }, */
            /*        dataType: "text", */
            /*        success: function($input) */
            /*        { */
            /*           $("div.modal").modal("hide"); */
            /*           console.log("created new $type"); */
            /*        }}); */
            /*    ', */
            /*      */
            /*      */
    }

    private function display_set($type)
    {
        /* we check the existence of the table name in the static
         * datastructure */
        $structure = $this->data_structure[$type];

        if ($structure == NULL) {
            $this->error("'$type' not found in static database structure!");
            return false;
        }

        ob_start();
        include '/home/leo/zeek/view/set.html';
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
    public function display_post($element_to_click, $method, $on_success) {
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

/**
 * Echo all data to send to the client side.
 *
 * @method output
 * @param string data to display
 */
    protected function output($input) {
        echo "$input";
    }

/**
 * Echo all errors.
 *
 * @method error
 * @param string data to display
 */
    protected function error($input) {
        echo "error: $input\n";
    }

    private function is_not_empty($input) {
        return isset($input) and $input != '';
    }
}
?>
