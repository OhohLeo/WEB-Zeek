<?php

class ZeekLibrary extends ZeekOutput {

    public $global_path;

    protected $db;

    private $db_login;
    private $db_password;
    private $db_host;
    private $db_name;
    private $db_use_specific;
    private $db_use_uniq;

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
    public function config($config)
    {
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

        /* we create the user table */
        $db->table_create('user', array(
            'name' => array('VARCHAR', 25),
            'password' => array('CHAR', 32)));

        /* we add the actual user */
        $db->row_insert('user', array(
            'name' => $login,
            'password' => $password));

        /* we create the project table */
        $db->table_create(
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
        $this->db->database_delete($name);
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

        if ($this->db->row_update(
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
        if ($this->db->row_delete(
            'user', array('name' => $username))) {
            return true;
        }

        return false;
    }

    public function user_get($username)
    {
        $db = $this->db;

        $result = $db->table_view(
            'user', '*', NULL, NULL, NULL,
            array('name' => $username));

        return ($result == NULL) ? NULL : $db->handle_result($result);
    }

    public function user_check($username, $password)
    {
        $db = $this->db;

        $result = $db->table_view(
            'user', 'name', NULL, NULL, NULL,
            array('name'     => $username,
                  'password' => $password));

        if ($result == false) {
            return false;
        }

        if ($row = $db->handle_result($result)) {
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
        $db = $this->db;

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
        $db = $this->db;

        /* if it exists only one project : we delete the database */
        if ($db->table_count('project', '*', NULL) < 2) {
            return $db->database_delete($this->db_name);
        }

        $params = array('project_id' => $this->project_id);

        /* otherwise : all links containing the actual project_id */
        foreach ($this->data_structure as $name => $value) {

            /* we check if the table exist, otherwise we continue */
            if ($db->table_check($name) == false) {
                continue;
            }

            /* for the project : we remove the row with the project_id
             * specified */
            if ($name == 'project') {
                $db->row_delete('project', $params);
                continue;
            }

            /* for all other tables : we check if each table contain
             * other reference of project_id */
            if ($db->table_count($name, 'project_id', NULL) > 1) {
                /* we remove all row with actual project_id */
                $db->row_delete($name, $params);
            } else {
                /* otherwise we delete the table */
                $db->table_delete($name);
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
        $db = $this->db;

        $result = $db->table_view(
            'project', 'id', NULL, NULL, NULL,
            array('name' => $project_name));

        if ($result == NULL) {
            return false;
        }

        if ($row = $db->handle_result($result)) {
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
        if ($this->db->table_check($name))
            return true;


	if ($this->type_check($name) == false) {
            $this->error("'$name' not found in static database structure!");
            return false;
        }

        /* otherwise we check the existence of the table name in the
	 * static datastructure */
	$structure = $this->data_structure[$name];

        /* we create the table and all attributes */
	if ($this->db->table_create($name, $structure))
	    return true;

        $this->error("Impossible to create $name table!");
        return false;
    }

/**
 * Return the whole structure if it exists otherwise return
 * NULL.
 *
 * @method struture_get
 */
    public function structure_get()
    {
        return $this->data_structure;
    }

/**
 * Return the content of a type if it exists otherwise return
 * NULL.
 *
 * @method type_get
 * @param string type
 */
    public function type_get($type)
    {
	if ($this->type_check($type))
	    return $this->data_structure[$type];

	return false;
    }

/**
 * Check if a type exist.
 *
 * Return true if the type exist in the table list otherwise return
 * false.
 *
 * @method type_check
 * @param string type
 */
    public function type_check($type)
    {
        return array_key_exists($type, $this->data_structure);
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

	    /* we check that the values are correct */

            /* we insert the new value */
            return $this->db->row_insert($name, $values);
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
	/* we check that the values are correct */

        /* we add the project id */

        return $this->db->row_update($name, $id, $values);
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
            if ($name == 'project') {
                $param = array('id' => $this->project_id);
            } else {
                $param = array('project_id' => $this->project_id);
            }

            /* we get all the values desired */
	    $result = $this->db->table_view(
                $name, '*', NULL, $size, $offset, $param);

	    if ($result == NULL)
		return false;

	    return $result;
        }

        return false;
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
