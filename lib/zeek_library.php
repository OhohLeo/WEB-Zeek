<?php

class ZeekLibrary extends ZeekOutput {

    public $global_path;
    public $projects_path;

    protected $db;
    public $db_name;

    private $db_login;
    private $db_password;
    private $db_host;
    private $db_use_specific;
    private $db_use_uniq;

    private $data_structure = array();

/**
 * Setup configuration for establishing a connection with MySQL
 * database
 *
 * @method config
 * @param array connections parameters
 */
    public function config($config)
    {
        $this->projects_path = $config['projects_path'];

        $this->db_host = $config['db_host'];
        $this->db_name = $config['db_name'];
        $this->db_login = $config['db_login'];
        $this->db_password = $config['db_password'];

        if (isset($config['db_use_specific']))
            $this->db_use_specific = $config['db_use_specific'];
    }

/**
 * Establish a connection with MySQL database
 *
 * @method connect_to_database
 * @param array connections parameters
 */
    public function connect_to_database()
    {
        /* we choose the good database to use */
        if ($this->db_use_specific === 'old_mysql') {
            require_once $this->global_path . "lib/database_mysql.php";
            $db = new DataBaseOldMySQL();
        } else {
            require_once $this->global_path . "lib/database.php";
            $db = new DataBase();
        }

        /* we store the database */
        $this->db = $db;

        /* we try to establish a connection */
        if ($db->connect($this->db_host,
                             $this->db_name,
                             $this->db_login,
                             $this->db_password) == false) {
            return false;
        }


        /* we get all existing projects from the configuration file */
        if (isset($this->projects_path)) {
            $this->data_structure =
                $this->projects_get($this->projects_path);
        }


        $check_environment = ($this->db_use_specific)
            ? !($db->table_check('project') || $db->table_check('user'))
            : $db->database_check($this->db_name) == false;

      /* We check if the database already exists */
        if ($check_environment) {
               return $this->environment_setup(
                $this->db_name, $this->db_login, $this->db_password);
        }

        /* We will use only this database */
        $db->database_use($this->db_name);

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
        /* we get the database */
        $db = $this->db;

        if ($this->db_use_uniq == false) {

            /* we create the database */
            $db->database_create($name);
        }

        /* we use this database */
        $db->database_use($name);

        /* we create the project table */
        $db->table_create('project', array(
            'name'        => array('VARCHAR', 25),
            'since'       => 'DATE',
            'subtitle'    => array('VARCHAR', 300),
            'biography'   => array('TEXT', 1000)));

        /* we create the user table */
        $db->table_create('user', array(
            'name'       => array('VARCHAR', 25),
            'password'   => array('CHAR', 32),
            'project_id' => array('INT', 11)));

        /* we add the actual user */
        $db->row_insert('user', array(
            'name'       => $login,
            'password'   => $password,
            'project_id' => 0));

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
        /* we delete the database */
        $this->db->database_delete($name);
    }

    public function user_add($project_id, $username, $password)
    {
        /* We check if the user already exists */
        if ($this->user_check($project_id, $username, $password)) {
            $this->error("User $username already exist!");
            return false;
        }

        /* we insert the new project */
        if ($this->db->row_insert(
            'user', array(
                'project_id' => $project_id,
                'name'       => $username,
                'password'   => $password ))) {
            return true;
        }

        return false;
    }

    public function user_change_password(
        $project_id, $username, $old_password, $new_password)
    {
        /* we get the user */
        $user = $this->user_get($project_id, $username);
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

        if ($this->db->row_update(
            'user', $user->id,
            array('password' => $new_password))) {
            return true;
        }

        return false;
    }

    public function user_remove($project_id, $username)
    {
        $user = $this->user_get($project_id, $username);

        /* We check if the project already exists */
        if ($user == NULL) {
            $this->error("Can't remove user, '$username' doesn't exist!");
            return false;
        }

        /* we insert the new project */
        if ($this->db->row_delete(
            'user', array('project_id' => $project_id,
                          'name'       => $username))) {
            return true;
        }

        return false;
    }

    public function user_get($project_id, $username)
    {
        $db = $this->db;

        $result = $db->table_view(
            'user', '*', NULL, NULL, NULL,
            array('project_id' => $project_id,
                  'name'       => $username));

        return ($result == NULL) ? NULL : $db->handle_result($result);
    }

    public function user_check($project_id, $username, $password)
    {
        $db = $this->db;

        // if the login & password are the same than the database one :
        // we accept it immediately whatever the project id exist or not
        if ($username === $this->db_login
            and $password === $this->db_password)
            return true;

        // otherwise we look into the table and check the validity of project
        // id, username & password
        $result = $db->table_view(
            'user', 'name', NULL, NULL, NULL,
            array('project_id' => $project_id,
                  'name'       => $username,
                  'password'   => $password));

        if ($result == false) {
            return false;
        }

        if ($row = $db->handle_result($result)) {
            return true;
        }

        return false;
    }


/**
 * Parse projects.ini configuration file
 *
 * @method get_projects
 * @param string file containing all projects
 */
    public function projects_get($file)
    {
        /* we check if the file exists */
        if (file_exists($file) == false) {
            $this->error("Can't find projects configuration file '$file'!");
            return false;
        }

        $handle = fopen($file, "r");
        if ($handle == false) {
            $this->error("file '$file' not handle!");
            return false;
        }

        $structure = array();

        $project_name;
        $table_name;
        $count_line = 0;

        /* we read the file line by line */
        while (($line = fgets($handle)) !== false) {

            /* we get the number of line */
            $count_line++;

            if (preg_match('/^\s*#/', $line))
                continue;

            /* we detect a new project */
            if (preg_match('/^== ([A-Za-z0-9]+) ==$/', $line, $rsp)) {

                /* we get the project name */
                $project_name = $rsp[1];

                /* we set the structure */
                $structure[$project_name] = array();
                continue;
            }

            /* we check if the project name is defined */
            if (!isset($project_name)) {
                $this->error("Can't find project name for this table!");
                fclose($handle);
                return false;
            }

            /* we detect a new table */
            if (preg_match('/^([A-Za-z0-9]+):$/', $line, $rsp)) {

                /* we get the table name */
                $table_name = $rsp[1];

                /* we set the structure */
                $structure[$project_name][$table_name] = array();
                continue;
            }

            /* we check if the table name is defined */
            if (!isset($table_name)) {
                $this->error("Can't find table name for this attribute!");
                fclose($handle);
                return false;
            }

            /* we detect a new attribute */
            if (preg_match('/^\s+([A-Za-z0-9]+):\s+([A-Z_]+)\s+([0-9]+)?/',
                           $line, $rsp)) {
                $attribute = $rsp[1];
                $type = $rsp[2];

                /* we check if the value is ok */
                if ($this->db->check_type($type) == false) {
                    $this->error(
                        "Unknown type '$type' for attribute '$attribute'!");
                    fclose($handle);
                    return false;
                }

                /* we get the attribute name and values */
                $structure[$project_name][$table_name][$attribute] =
                    isset($rsp[3]) ? array($type, $rsp[3]) : $type;
                continue;
            }

            $this->error("Ignore line $count_line in file '$file'!");
            fclose($handle);
            return false;
        }

        fclose($handle);
        return $structure;
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
        $db = $this->db;

        if ($this->project_check($project_name) == false) {
            $this->error(
                "No existing project '$project_name' in configuration file!");
            return false;
        }

        /* we check if the project already exists */
        if ($this->project_get_id($project_name)) {
            $this->error(
                "Another project have the same name $project_name!");
            return false;
        }

        /* we insert the new project */
        if ($this->db->row_insert(
            'project', array('name' => $project_name)) == false) {
               return false;
        }

        /* we store the project id */
        if ($this->project_get_id($project_name)) {
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
    public function project_delete($project_name)
    {
        $db = $this->db;

        /* we get the id of the project in the database */
        $project_id = $this->project_get_id($project_name);
        if ($project_id == false) {
            $this->error("No existing project delete '$project_name' in database!");
            return false;
        }

        /* if it exists only one project : we delete the database */
        if ($db->table_count('project', '*', NULL) <= 1) {
            return $db->database_delete($this->db_name);
        }

        /* we remove the row with this project_id */
        $db->row_delete('project', $project_id);

        /* we remove all users using this project id */
        $db->row_delete('user', array('project_id' => $project_id));

        /* we remove all the tables beginning with the project id */
        foreach ($this->data_structure[$project_name] as $name => $value) {

            $reel_name = "$project_id$name";

            /* we check if the table exist, otherwise we continue */
            if ($db->table_check($reel_name) == false)
                continue;

            /* for all other tables : we delete the table */
            $db->table_delete($reel_name);
        }

        return true;
    }

/**
 * Check if a project exists in the internal structure.
 *
 * Return true if the project exists otherwise return false.
 *
 * @method project_check
 * @param string project name
 */
    public function project_check($project_name)
    {
        return array_key_exists($project_name, $this->data_structure);
    }

/**
 * Check if a project exists and return the id.
 *
 * @method project_get_id
 * @param string project name
 */
    public function project_get_id($project_name)
    {
        $db = $this->db;

        $result = $db->table_view(
            'project', 'id', NULL, NULL, NULL,
            array('name' => $project_name));

        if ($result == NULL)
            return false;

        /* we return the id of the project */
        if ($row = $db->handle_result($result))
            return $row->id;

        return false;
    }


/**
 * Return the name of the project by his id, false otherwise.
 *
 * @method project_name_get_by_id
 * @param integer project id
 */
    public function project_name_get_by_id($project_id)
    {
        $db = $this->db;

        $result = $db->table_view(
            'project', 'name', NULL, NULL, NULL, $project_id);

        if ($result == NULL)
            return false;

        /* we return the name of the project */
        if ($row = $db->handle_result($result))
            return $row->name;

        return false;
    }

/**
 * Return the whole structure if it exists otherwise return
 * NULL.
 *
 * @method struture_get
 * @param string project name
 */
    public function structure_get($project_name)
    {
        return $this->data_structure[$project_name];
    }

/**
 * Return the content of a type if it exists otherwise return
 * NULL.
 *
 * @method type_get
 * @param string project name
 * @param string type
 */
    public function type_get($project_name, $type)
    {
        /* we check if the project exists */
        if ($this->project_check($project_name) == false) {
            return false;
        }

        if ($this->type_check($project_name, $type))
            return $this->data_structure[$project_name][$type];

        return false;
    }

/**
 * Check if a type exist.
 *
 * Return true if the type exist in the table list otherwise return
 * false.
 *
 * @method type_check
 * @param string project name
 * @param string type
 */
    public function type_check($project_name, $type)
    {
        /* we check if the project exists */
        if ($this->project_check($project_name) == false) {
            return false;
        }

        return array_key_exists($type, $this->data_structure[$project_name]);
    }

/**
 * Check if a table exists otherwise automatically create if this
 * table is referenced on the static table.
 *
 * Return the name of the table if the table exists or is succesfully created
 * otherwise return false.
 *
 * @method table_check_and_create
 * @param string table name
 */
    protected function table_check_and_create($project_id, $table_name)
    {
        $reel_name = "$project_id$table_name";

        /* we check if the table exists */
        if ($this->db->table_check($reel_name))
            return true;

        /* we get the name of the project using the project id */
        $project_name = $this->project_name_get_by_id($project_id);
        if ($project_name == false) {
            $this->error("'$project_id' not found in database!");
            return false;
        }

        if ($this->type_check($project_name, $table_name) == false) {
            $this->error(
                "table '$table_name' not found in static database structure!");
            return false;
        }

        /* otherwise we check the existence of the table name in the
         * static datastructure */
        $structure = $this->data_structure[$project_name][$table_name];
        if ($structure == NULL) {
            $this->error(
                "table '$table_name' empty in static database structure!");
            return false;
        }

        /* we create the table and all attributes */
        if ($this->db->table_create($reel_name, $structure))
            return true;

        return false;
    }


/**
 * Count the number of element in a static table.
 *
 * Return the number of elements otherwise return 0.
 *
 * @method table_count
 * @param string table name
 */
    public function table_count($project_id, $table_name)
    {
        $db = $this->db;

        $reel_name = "$project_id$table_name";

        /* we check if the table exists */
        if ($db->table_check($reel_name))
            return $db->table_count($reel_name, 'id', NULL);

        return 0;
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
    public function value_insert($project_id, $table_name, $values)
    {
        /* we insert the new value */
        if ($this->table_check_and_create($project_id, $table_name))
        {
            return $this->db->row_insert("$project_id$table_name", $values);
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
    public function value_update($project_id, $table_name, $id, $values)
    {
        return $this->db->row_update("$project_id$table_name", $id, $values);
    }

/**
 * Delete a value already stored.
 *
 * Return true if the values are deleted otherwise
 * return false.
 *
 * @method value_delete
 * @param string table name
 * @param integer id of the element to delete
 */
    public function value_delete($project_id, $table_name, $id)
    {
        return $this->db->row_delete("$project_id$table_name", $id);
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
    public function value_get($project_id, $table_name,
                              $sort = NULL, $size = NULL, $offset = NULL)
    {
        $reel_name = "$project_id$table_name";

        if ($this->db->table_check($reel_name)) {
            $params = NULL;

            /* we get all the values desired */
            $result = $this->db->table_view(
                $reel_name, '*', $sort, $size, $offset, $params);

            if ($result == NULL)
                return false;

            return $result;
        }

        return NULL;
    }

/**
 * Get value one by one.
 *
 * @method value_fetch
 * @param object result of 'value_get' method
 */
    public function value_fetch($result)
    {
        return $this->db->handle_result($result);
    }
}

?>
