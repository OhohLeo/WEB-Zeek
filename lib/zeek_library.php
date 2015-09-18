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
 */

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
    private $options;

/**
 * Setup configuration for establishing a connection with MySQL
 * database
 *
 * @method config
 * @param array connections parameters
 */
    public function config($config)
    {
        if (isset($config['projects_path']))
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
        else if (is_array($_SESSION)
                 && array_key_exists("data_structure", $_SESSION))
        {
            $this->data_structure = $_SESSION["data_structure"];
        }

        // Check if the database already exists
        if ($db->database_check($this->db_name) == false
            || $db->table_check('project') == false
            || $db->table_check('user') == false)
        {
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

        /* we create the database */
        $db->database_create($name);

        /* we use this database */
        $db->database_use($name);

        $this->db_name = $name;

        /* we create the project table */
        $db->table_create('project', array(
            'name'              => array('db_type' => 'VARCHAR',
				         'db_size' => 25),
            'created_at'        => array('db_type' => 'DATE'),
            'last_connection'   => array('db_type' => 'DATE'),
            'last_user'         => array('db_type' => 'VARCHAR',
				         'db_size' => 25),
            'destination'       => array('db_type' => 'VARCHAR',
			                 'db_size' => 200),
            'url'               => array('db_type' => 'VARCHAR',
			                 'db_size' => 200),
            'structure'         => array('db_type' => 'VARCHAR',
				         'db_size' => 2000),
	    'options'           => array('db_type' => 'VARCHAR',
				         'db_size' => 2000),
            'piwik_token'       => array('db_type' => 'VARCHAR',
			                 'db_size' => 32)));

        /* we create the user table */
        $db->table_create('user', array(
            'name'       => array('db_type' => 'VARCHAR',
				  'db_size' => 25),
            'password'   => array('db_type' => 'CHAR',
				  'db_size' => 32),
            'project_id' => array('db_type' => 'INT',
				  'db_size' => 11),
            'is_master'  => array('db_type' => 'TINYINT'),
	    'options'    => array('db_type' => 'VARCHAR',
			          'db_size' => 2000)));

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

    public function user_add($project_id, $username, $password, $is_master)
    {
        // we check if the user already exists
        if ($this->user_check($project_id, $username, $password))
        {
            $this->error("user $username already exist!");
            return false;
        }

        // we get the actual project options
        $project_options = $this->project_get_plugins($project_id);

        if ($project_options == false)
            return false;

        $plugins = array();

        // only insert activated plugins
        foreach ($project_options["plugins"] as $name => $status)
        {
            if (is_bool($status))
                $plugins[$name] = $status;
        }

        // we insert the new project
        if ($this->db->row_insert(
            'user', array(
                'project_id' => $project_id,
                'name'       => $username,
                'password'   => md5($password),
                'is_master'  => $is_master,
                'options'    => $this->json_encode(
                    array('test' => $plugins)))))
        {
            return true;
        }

        return false;
    }

    public function user_get_authorisation($project_id, $username)
    {
        // if the login & password are the same than the database one :
        // we accept it immediately whatever the project id exist or not
        if ($username === $this->db_login)
            return true;

        $user = $this->user_get($project_id, $username);
        if ($user == NULL) {
            return false;
        }

        return !!$user->is_master;
    }

    public function user_change_authorisation($project_id, $username, $is_master)
    {
        if (is_string($username))
        {
            $user = $this->user_get($project_id, $username);
            if ($user == NULL) {
                $this->error(
                    "Can't change authorisation : user '$username' doesn't exist!");
                return false;
            }
        }
        else
            $user = $username;

        if ($this->db->row_update('user', $user->id,
                                  array('is_master' => $is_master))) {
            return true;
        }

        return false;
    }

    public function user_change_password($project_id, $username,
                                         $old_password, $new_password)
    {
        // we get the user
        $user = $this->user_get($project_id, $username);
        if ($user == NULL) {
            $this->error(
                "Can't change password : user '$username' doesn't exist!");
            return false;
        }

        // we check the old password
        if (md5($old_password) !== $user->password) {
            $this->error(
                "Can't change password : wrong old password!");
            return false;
        }

        if ($this->db->row_update('user', $user->id,
                                  array('password' => md5($new_password)))) {
            return true;
        }

        return false;
    }

    public function user_remove($project_id, $username)
    {
        $user = $this->user_get($project_id, $username);

        // we check if the project already exists
        if ($user == NULL) {
            $this->error("Can't remove user, '$username' doesn't exist!");
            return false;
        }

        // we insert the new project
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

    public function users_get_list($project_id)
    {
        $result = $this->db->table_view(
            'user', '*', NULL, NULL, NULL,
            array('project_id' => $project_id));

        $users = array();

        if ($result == NULL)
            return $users;

        while ($row = $this->value_fetch($result))
        {
            array_push($users, $row['name']);
        }

        return $users;
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
                  'password'   => md5($password)));

        if ($result == false) {
            return false;
        }

        if ($row = $db->handle_result($result)) {
            return true;
        }

        return false;
    }

/**
 * Return the value of an attribute from the specified user.
 *
 * @method user_get_attribute
 * @param int project id
 * @param string user name
 * @param string attribute name
 */
    public function user_get_attribute($project_id, $username, $name)
    {
        $db = $this->db;

        $result = $this->db->table_view(
            'user', $name, NULL, 1, 0, array('project_id' => $project_id,
                                             'name'       => $username));

        if ($result)
        {
            $array = $this->value_fetch($result);

            return $array[$name];
        }

        return NULL;
    }

/**
 * Set the value of an attribute from the specified user.
 *
 * @method user_set_attribute
 * @param int project id
 * @param string user name
 * @param string attribute name
 * @param string value to write
 */
    public function user_set_attribute($project_id, $username, $name, $value)
    {
        // we get the user
        $user = $this->user_get($project_id, $username);
        if ($user == NULL) {
            $this->error(
                "Can't change attribute : user '$username' doesn't exist!");
            return false;
        }

        if ($this->db->row_update(
            'user', $user->id, array($name => $value)))
        {
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

        $structure = $this->json_decode(fgets($handle));

        /* we check if the structure is defined */
        if (!isset($structure)) {
            $this->error("structure not defined!");
            fclose($handle);
            return false;
        }

        fclose($handle);

        return $this->object_to_array($structure);
    }

/**
 * Create a new project
 *
 * Return false if a project with the same name already exists,
 * otherwise return true.
 *
 * @method project_add
 * @param string project name
 * @param string project destination
 * @param array options associated to the the project
 */
    public function project_add($project_name, $project_dst=null, $options=null)
    {
        $db = $this->db;

        if ($this->projects_path
            && $this->project_check($project_name) == false) {
            $this->error(
                "No existing project '$project_name' in configuration file!");
            return false;
        }

        // we check if the project already exists
        if ($this->project_get_id($project_name)) {
            $this->error(
                "Another project have the same name '$project_name'!");
            return false;
        }

        $params = array('name' => $project_name,
                        'created_at' => time());

        if ($options)
            $params['options'] = $this->json_encode($options);

        // we insert the new project
        if ($this->db->row_insert('project', $params) == false) {
            $this->error(
                "Impossible to insert project '$project_name' in database!");
            return false;
        }

        // we store the project id
        $project_id = $this->project_get_id($project_name);

        if ($project_id)
        {
            if ($project_dst == NULL) {
                $project_url = "projects/$project_id/DEPLOY";
                $project_dst = $this->global_path . $project_url;

                $this->project_set_attribute($project_id, "url", $project_url);
            }

            $this->project_set_attribute($project_id, "destination", $project_dst);
            return true;
        }

        $this->error('Impossible to add project!');
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

        // we get the id of the project in the database
        $project_id = $this->project_get_id($project_name);
        if ($project_id == false) {
            $this->error("no existing project delete '$project_name' in database!");
            return false;
        }

        // if it exists only one project : we delete the database
        if ($db->table_count('project', '*', NULL) <= 1) {
            return $db->database_delete($this->db_name)
                && $this->files_delete($project_id);
        }

        // we remove the row with this project_id
        $db->row_delete('project', $project_id);

        // we remove all users using this project id
        $db->row_delete('user', array('project_id' => $project_id));

        // we remove all the tables beginning with the project id
        foreach ($this->data_structure[$project_name] as $name => $value) {

            $reel_name = "$project_id$name";

            // we check if the table exist, otherwise we continue
            if ($db->table_check($reel_name) == false)
                continue;

            // for all other tables : we delete the table
            $db->table_delete($reel_name);
        }

        // we remove all the files associated to the project
        return $this->files_delete($project_id);
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
 * Return the list of plugins configured from the specified project.
 *
 * @method project_get_plugins
 * @param int project id
 */
    public function project_get_plugins($project_id)
    {
        // we get the plugins associated to the project
        $project_options = $this->option_get($project_id);
        if (is_array($project_options)
            && array_key_exists("plugins", $project_options))
        {
            return $project_options;
        }

        $this->error("Invalid stored plugin list!");
        return false;
    }

/**
 * Return the value of an attribute from the specified project.
 *
 * @method project_get_attribute
 * @param int project id
 * @param string attribute name
 */
    public function project_get_attribute($project_id, $name)
    {
        $db = $this->db;

        $result = $this->db->table_view(
            'project', $name, NULL, 1, 0, $project_id);

        if ($result)
        {
            $array = $this->value_fetch($result);

            return $array[$name];
        }

        return NULL;
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
        if ($this->db->row_update(
            'project', $project_id, array($name => $value)))
        {
            return true;
        }

        return false;
    }

/**
 * Return the whole structure if it exists otherwise return
 * empty array.
 *
 * @method struture_set
 * @param integer project id
 * @param string project name
 */
    public function structure_get($project_id, $project_name)
    {
	if (array_key_exists($project_name, $this->data_structure))
	    return $this->data_structure[$project_name];

        $db = $this->db;

        $result = $this->db->table_view(
            'project', 'structure', NULL, 1, 0, $project_id);

        if ($result)
        {
            $get = $this->value_fetch($result);

            if (array_key_exists("structure", $get))
                $structure = $this->json_decode($get["structure"]);

            if ($structure == NULL)
                return array();

            $this->data_structure[$project_name] = $structure;

            // we store the data structure in the session
            $_SESSION["data_structure"] = $this->data_structure;

            return $structure;
        }

        return array();
    }

/**
 * Return the whole structure if it exists otherwise return NULL.
 *
 * @method struture_get
 * @param integer project id
 * @param string project name
 * @param array structure to write
 */
    public function structure_set($project_id, $project_name, $structure)
    {
        // we check if the structure are similar
        if ($this->structure_compare($project_name, $structure))
            return true;

        // if not similar : we write the new structure
        $json_structure = $this->json_encode($structure);

        if ($this->db->row_update(
            'project', $project_id, array('structure' => $json_structure)))
        {
            $this->data_structure[$project_name] = $structure;

            // we store the data structure in the session
            $_SESSION["data_structure"] = $this->data_structure;

            return $this->structure_update($project_id, $structure);
        }

        // if there is an issue : we remove the structure
        unset($this->data_structure[$project_name]);
	return false;
    }

/**
 * Compare the structure to the existing one. Return true if the structure are
 * similar, false otherwise.
 *
 * @method struture_compare
 * @param array structure to compare
 */
    public function structure_compare($project_name, $new_structure)
    {
        // we check if there are some modifications between each structure
        if (array_key_exists($project_name, $this->data_structure) == false)
            return false;

        $structure_stored = $this->data_structure[$project_name];

        foreach ($new_structure as $table_name => $domain)
        {
            if (array_key_exists($table_name, $structure_stored) == false)
                return false;

            foreach ($domain as $attribute => $value)
            {
                if (array_key_exists(
                    $attribute, $structure_stored[$table_name]) == false)
                    return false;

                if (array_diff($value,
                               $structure_stored[$table_name][$attribute]) == false)
                        return false;
            }
        }

        return true;
    }

/**
 * Apply changes on existing table impacted by the new structure.
 * Return true if the process correctly happens, false otherwise.
 *
 * @method struture_compare
 * @param integer project id
 * @param array structure to apply
 */
    public function structure_update($project_id, $new_structure)
    {
        $db = $this->db;

        // we get the actual existing tables
        $tables = $db->tables_show($this->db_name);
        if ($tables == false) {
	    $this->error("Impossible to see the current tables!");
            return false;
        }

        // we check if existing tables need to be changed
        foreach ($tables as $table_name)
        {
            // we ignore the not concerned tables
            if ($table_name === "project"
                || $table_name === "user"
                || substr($table_name, 0, 1) != $project_id)
                continue;

            $reel_table_name = substr($table_name, 1);

            // we remove the project id at the beginning of the name
            // we check that the table name still exists
            if (array_key_exists($reel_table_name, $new_structure))
            {
                if ($this->attributes_update(
                    $table_name, $new_structure[$reel_table_name]) == false)
                        return false;
            }
            // otherwise we remove the stored table from the database
            else if ($db->table_delete($table_name))
            {
                continue;
            }
            // impossible to delete the table : error
            else
            {
                $this->error("Error while deleting '$table_name'!");
                return false;
            }
        }

        // we updated all the databases
        return true;
    }

 /**
  * Check and alter table if needed. Return false if an error has been
  * detected, otherwise return true
  *
  * @method attributes_update
  * @param string name of the table containing the attributes list
  * @param array new attributes list
  */
    public function attributes_update($table_name, $new_attributes)
    {
        $db = $this->db;

        // we get the attributes of the actual existing table
        $attributes = $db->table_show($table_name);

        foreach ($attributes as $attribute => $value)
        {
            // we ignore "id" tables automatically created
            if ($attribute === "id")
                continue;

            // we check if the attribute is similar
            if (array_key_exists($attribute, $new_attributes)
                && $db->attribute_check($value, $new_attributes[$attribute]))
            {
                unset($new_attributes[$attribute]);
            }
            // otherwise we remove the attribute altered
            else if ($db->attribute_remove($table_name, $attribute) == false)
            {
                $this->error(
                    "Error removing the attribute '$attribute' in '$table_name'!");
                return false;
            }
        }

        // we add remaining new attributes that could exist
        foreach ($new_attributes as $attribute => $value)
        {
            if ($db->attribute_add($table_name, $attribute, $value))
                continue;

            $this->error(
                "Error setting the attribute '$attribute' in '$table_name'!");
            return false;
        }

        return true;
    }

/**
 * Return the options structure. Return false if the option doesn't exist.
 *
 * @method option_get
 * @param int project id
 */
    public function option_get($project_id)
    {
        if ($this->options)
            return $this->options;

        $options = $this->project_get_attribute($project_id, 'options');

        if ($options != NULL)
        {
            $this->options = $this->json_decode($options);

            return $this->options;
        }

        return NULL;
    }


 /**
 * Set change on the options structure. Return false if the option doesn't exist.
 *
 * @method option_get
 * @param int project id
 * @param string option name
 * @param array option values
  */

    public function option_set($project_id, $name, $values)
    {
        if ($this->options == NULL)
            $this->option_get($project_id);

        $this->options[$name] = $values;

        if ($this->project_set_attribute(
            $project_id, 'options', $this->json_encode($this->options)))
        {
            return true;
        }

        // if there is an issue : we remove this option
        unset($this->options[$name]);

        return false;
    }

/**
 * Return the content of a type if it exists otherwise return
 * NULL.
 *
 * @method type_get
 * @param string project name
 * @param string table name
 */
    public function type_get($project_name, $table_name)
    {
        if ($this->type_check($project_name, $table_name))
            return $this->data_structure[$project_name][$table_name];

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
    public function type_check($project_name, $table_name)
    {
        // we check if the project exists
        if ($this->projects_path
            && $this->project_check($project_name) == false) {
            return false;
        }

        return array_key_exists($table_name, $this->data_structure[$project_name]);
    }


/**
* We create a directory
*
* Return true if the directory already exist or is correctly created,
* otherwise return false.
*
* @method directory_create
* @param string path
*/
    public function directory_create($path)
    {
	// we try to create the directory associated to the project
	if (is_dir($path))
            return true;

        global $php_errormsg;

	try
	{
	    if (mkdir($path, 0755, true) == false)
            {
                $this->error(
		    "Error when creating '$path': $php_errormsg!");
                return false;
            }
	}
	catch (Exception $e)
	{
	    $this->error(
		"Impossible to create '$path': $e!");

	    return false;
	}

       return true;
    }

/**
* We delete a directory
*
* Return true if the directory don't exist or is correctly deleted,
* otherwise return false.
*
* @method directory_remove
* @param string path
*/
    public function directory_remove($path, $filter=null)
    {
        // we check if the directory exists
	if (!is_dir($path))
            return true;

        // we check if we have the rights to handle the directory

	try
	{
	    $objects = scandir($path);

	    foreach ($objects as $object)
	    {
		if (substr($object, 0, 1) != "." && $object != "..")
		{
                    $filepath = $path ."/". $object;

                    if ($filter != null && $filter == $filepath)
                        continue;

		    if (filetype($filepath) == "dir")
		    {
			$this->directory_remove($filepath);
		    }
		    else
		    {
			unlink($filepath);
		    }
		}
	    }

	    reset($objects);

	    rmdir($path);
	}
	catch (Exception $e)
	{
	    $this->error(
		"Impossible to delete '$path': $e!");

	    return false;
	}

       return true;
    }

/**
* We scan a directory in 'projects' directory
*
* Return the list of elements in the directory.
*
* @method directory_scan
* @param string path
* @param array ref of list of elements
* @param integer size to remove
*/
    private function directory_scan($path, &$list,
                                    $is_recursive, $len_to_remove=0,
                                    $filter_directories=null, $no_global_path=false)
    {
	try
	{
	    $files = scandir($path);

	    // we remove data before the username
	    $get_type = substr($path, $len_to_remove);

            if ($no_global_path) {
                $smallpath = substr($path, strlen($this->global_path));
            }

	    foreach ($files as $file)
	    {
                // we ignore the files finishing with ~ and 'extends' filename
		if ($file == "."
                          || $file == ".."
                          || $file == "extends"
                          || preg_match("/[~]+\z/", $file))
		    continue;

                $filepath = $path . "/" . $file;

		if (filetype($filepath) == "dir")
		{
                    if (is_array($filter_directories)
                        && in_array($file, $filter_directories, true)){
                        continue;
                    }

                    if ($is_recursive)
		        $this->directory_scan(
			    $filepath, $list, $is_recursive,
                            $len_to_remove, $filter_directories);

                    continue;
		}

                // we get the content type
                $mime = ""; //mime_content_type($filepath);

                if ($len_to_remove > 0)
		{
		    // the path doesn't exist : we get the type of the
		    // file from the extension
		    if ($get_type == "")
		    {
                        $idx = strrpos($file, '.');
			$type = substr($file, $idx + 1);
                        $in_main_directory = true;
		    }
		    else
		    {
			$file = "$get_type/$file";
                        $type = $get_type;
                        $in_main_directory = false;
                    }

		    array_push($list,
			       array('mime' => $mime,
				     'type' => $type,
				     'name' => $file,
                                     'in_main_directory' => $in_main_directory));
                    continue;
		}

                $directory_idx = strrpos($path, '/');
                $extension_idx = strrpos($file, '.');

                $result = array(
                    'mime'      => $mime,
                    'size'      => filesize($filepath),
                    'path'      =>
                    $no_global_path ? $smallpath : $path,
                    'directory' =>
                    $directory_idx ? substr($path, $directory_idx + 1) : $path,
                    'extension' =>
                    $extension_idx ? substr($file, $extension_idx + 1) : '',
                    'filename'  =>
                    $extension_idx ? substr($file, 0, $extension_idx) : $file,
                    'name'      => $file);

                // we detect if it is an image
                if (preg_match("/^image\//", $mime))
                {
                    list($result["width"],
                         $result["height"]) = getimagesize($filepath);
                }

		array_push($list, $result);
	    }
	}
	catch (Exception $e)
	{
	    $this->error(
		"Impossible to scan '$path': $e!");

	    return false;
	}

       return true;
    }


/**
* We copy directory's data into another.
*
* Return true if everything is correctly copied, otherwise return false.
*
* @method directory_copy
* @param string src
* @param string dst
*/
    public function directory_copy($src, $dst)
    {
	$files_list = array();

	if (directory_scan($src, $files_lists, true) == false)
	    return false;

	foreach ($files_list as $file)
	{
	    $path_end = substr($file, strlen($src));

	    // we get the directory of the file
	    $idx = strrpos($path_end, '/');

	    $directory_end = substr($path_end, 0, $idx);

	    // we create the directory if it doesn't exits
	    if ($this->directory_create($dst . $directory_end) == false)
		return false;

	    // we copy the file
	    if ($this->file_copy($src, $dst . $path_end ) == false)
		return false;
	}

	return true;
    }

/**
* We check that the file exists.
*
* Return true if the file exists, otherwise return false.
*
* @method file_exist
* @param string src
*/
    private function file_exists($src)
    {
	// we check that the file doesn't already exist
	if (!file_exists($src))
	{
	    $this->error("'$src' doesn't exist!");

	    return false;
	}

	return true;
    }

/**
* We copy the file from src to dst.
*
* Return true if the file is correctly copied, otherwise return false.
*
* @method file_copy
* @param string src
* @param string dst
*/
    private function file_copy($src, $dst)
    {
	// we check that the file doesn't already exist
	if (file_exists($dst))
	{
	    $this->error("Already existing file '$dst'!");

	    return false;
	}

	try
	{
	    return copy($src, $dst);
	}
	catch (Exception $e)
	{
	    $this->error("Error while copying '$src' to '$dst'");
	}

	return false;
    }

/**
* We move the file  from src to dst.
*
* Return true if the file is correctly move, otherwise return false.
*
* @method file_move
* @param string src
* @param string dst
*/
    private function file_move($src, $dst)
    {
	// we check that the source exists
	if (file_exists($src) == false)
	{
	    $this->error("File '$src' not found!");
	    return false;
	}

	try
	{
	    return rename($src, $dst);
	}
	catch (Exception $e)
	{
	    $this->error("Impossible to move '$src' to '$dst'");
	}

	return false;
    }


/**
* We read the file.
*
* Return the content if the file is correctly read, otherwise return false.
*
* @method file_read
* @param string src
*/
    public function file_read ($src)
    {
	// we check that the file already exist
	if (!$this->file_exists($src))
	    return false;

	try
	{
	    $fp = fopen($src, 'r');
            $size = filesize($src);

            $content = ($size != 0) ? fread($fp, $size) : "Write a good masterpiece!";
	    fclose($fp);

	    return $content;
	}
	catch (Exception $e)
	{
	    $this->error("Impossible to read '$src'");
	}

	return false;
    }


/**
* We write into the file.
*
* Return true if the file is correctly written, otherwise return false.
*
* @method file_write
* @param string src
* @param string new_data to set on the src
*/
    public function file_write($src, $data)
    {
	try
	{
	    $fp = fopen($src, 'w');
	    fwrite($fp, $data);
	    fclose($fp);
	}
	catch (Exception $e)
	{
	    $this->error("Impossible to write '$src'");

	    return false;
	}

	return true;
    }

/**
* We remove the file.
*
* Return true if the file is correctly removed, otherwise return false.
*
* @method file_remove
* @param string src
*/
    private function file_remove($src)
    {
	// we check that the file doesn't already exist
	if (file_exists($src) == false)
	{
	    $this->error("'$src' doesn't exist!");
	    return false;
	}

	try
	{
	    unlink($src);
	}
	catch (Exception $e)
	{
	    $this->error("Impossible to remove '$src'");

	    return false;
	}

	return true;
    }

/**
* We remove the files associated to the project.
*
* Return true if everything is correctly removed, otherwise return false.
*
* @method files_delete
* @param integer project_id
*/
    public function files_delete($project_id)
    {
	// on supprime l'ensemble des fichiers
	if ($this->directory_remove(
	    $this->global_path . 'projects/' . $project_id))
		return true;

	return false;
    }

/**
* We remove the files uploaded.
*
* Return true if everything is correctly removed, otherwise return false.
*
* @method uploaded_files_delete
*/
    public function uploaded_files_delete()
    {
	// on supprime l'ensemble des fichiers
	if ($this->directory_remove($this->global_path . 'files'))
	    return true;

	return false;
    }


/**
* Return file directory.
*
* @method file_get_directory
* @param integer project_id
* @param string username
* @param string type
*/
    public function file_get_directory($project_id, $type)
    {
	return $this->global_path . "projects/$project_id"
             . (($type != '') ? "/$type" : '');
    }

/**
* Return file whole path.
*
* @method file_get_path
* @param integer project_id
* @param string username
* @param string name
*/
    public function file_get_path($project_id, $name)
    {
	if (substr($name, 0, 1) == '/')
	    $name = substr($name, 1);

	return $this->global_path . "projects/$project_id/$name";
    }

/**
* Return file type.
*
* @method file_get_type
* @param string name
*/
    public function file_get_type($name)
    {
	$type = '';

	// 1st : the type should be in the directory name
	if (substr($name, 0, 1) == '/')
	{
	    $type = substr($name, 1, strrpos($name, '/') - 1);
	}
	// 2nd : the name contains '/' the type should be in
	// the directory name
	else if (strrpos($name, '/') > 0)
	{
	    $type = substr($name, 0, strrpos($name, '/'));
	}
	// otherwise we pickup the extension of the file
	else
	{
	    $type = $this->file_get_extension($name);
	}

	return $type;
    }

/**
* We get the file details.
*
* Return true if the file exists, otherwise return false.
*
* @method file_get_details
* @param string name
*/
    public function file_get_details($name, $get_type=null)
    {
        if ($get_type == null)
        {
            $idx = strrpos($name, '/');
	    $get_type = substr($name, 0, $idx);
        }

	if ($get_type == "")
	{
            $idx = strrpos($name, '.');
	    $type = substr($name, $idx + 1);
            $in_main_directory = true;
	}
	else
	{
            $type = $get_type;
            $in_main_directory = false;
        }

        return array('type' => $type,
		     'name' => $name,
                     'in_main_directory' => $in_main_directory);
    }

/**
* Return file extension.
*
* @method file_get_extension
* @param string name
 */
    public function file_get_extension($name)
    {
        return substr($name, strpos($name, '.') + 1);
    }


/**
* We create the file copied from generic type files stored in 'default' directory
* and we insert the new data in the '_edit' table.
*
* Return true if the file is correctly copied otherwise return false.
*
* @method file_create
* @param integer project_id
* @param string username
* @param string type
* @param string name
* @param string extension
* @param string in_main_directory
*/
    public function file_create($project_id, $user, $type, $name,
				$extension, $in_main_directory = false)
    {
	$dst = $this->file_get_directory(
	    $project_id, $in_main_directory ? '' : $type);

	// we create the directory if it doesn't already exist
	if ($this->directory_create($dst) == false)
            return false;

	$src = $this->global_path . "default/generic";
	$dst .= "/$name.$extension";

	// we copy the generic type file in the directory with the
	// specified name
	if (file_exists("$src.$extension")
	    && $this->file_copy("$src.$extension", $dst))
	    return true;

	// otherwise we copy the generic file that should exist
	if ($this->file_copy($src, $dst))
	    return true;

        $errors = error_get_last();

        $this->error("Impossible to copy '$src' to '$dst' "
                    . $errors['message']);

	return false;
    }

/**
* We move the file due to modification of file's type or change on name
*
* Return true if the file is correctly moved otherwise return false.
*
* @method file_modify
* @param integer project_id
* @param string username
* @param string path of the file to move
* @param string type
* @param string name
* @param string extension
* @param string in_main_directory
*/
    public function file_modify($project_id, $user, $file_to_move, $type, $name,
				$extension, $in_main_directory = false)
    {
	$dst = $this->file_get_directory(
	    $project_id, $in_main_directory ? '' : $type);

	// we create the directory if it doesn't already exist
	if ($this->directory_create($dst) == false)
            return false;

	$src = $this->global_path . $file_to_move;
	$dst .= "/$name.$extension";

	// we move the specified file in the directory with the
	// specified name
	if ($this->file_move($src, $dst))
            return true;

	return false;
    }


/**
* We delete the file
*
* Return true if the file is correctly moved otherwise return false.
*
* @method file_delete
* @param integer project_id
* @param string username
* @param string name
*/
    public function file_delete($project_id, $user, $name)
    {
	$src = $this->file_get_path($project_id, $name);

	if ((file_exists($src) == false) || $this->file_remove($src))
	    return true;

	return false;
    }


    public function file_check($project_id, $name)
    {
	return file_exists(
	    $this->file_get_path($project_id, $name));
    }


    public function file_get_list($project_id, $filter_directories=null)
    {
	$rsp = array();

        if ($filter_directories == null)
            $filter_directories = array("DEPLOY", "TEST");
        else
            array_push($filter_directories, "DEPLOY", "TEST");

	// on récupère le path
	$path = $this->global_path . "projects/$project_id";

	if ($this->directory_scan($path, $rsp, true, strlen($path) + 1, $filter_directories))
	    return $rsp;

	return false;
    }

    public function file_get_type_list()
    {
	$rsp = array();

	// on récupère le path
	$path = $this->global_path . "js/ace";

	if ($this->directory_scan($path, $rsp, false, strlen($path) + 1))
	    return $rsp;

	return false;
    }


    public function file_get($project_id, $user, $name)
    {
	$src = $this->file_get_path($project_id, $name);

	return $this->file_read($src);
    }


    public function file_set($project_id, $user, $name, $data)
    {
	$src = $this->file_get_path($project_id, $name);

	if ($this->file_write($src, $data))
	   return true;

	return false;
    }

/**
 * Ask to download file.
 *
 * @method file_download
 * @param string url to download
 * @param string path where to download file
 */
    public function file_download($url, $path)
    {
        $error = null;

        try
	{
            $file = fopen($url, "rb");

            try
	    {
                $f = fopen($path, "wb");

                while(feof($file) == false)
                {
                    fwrite($f, fread($file, 1024 * 8 ), 1024 * 8 );
                }

                fclose($f);
            }
            catch (Exception $e)
            {
                $error = "Impossible to download '$url' to '$path': $e";
            }

            fclose($file);

        }
        catch (Exception $e)
        {
            $error = "Impossible to download '$url'";
        }

        if ($error)
        {
            $this->error($error);
            return false;
        }

        return true;
    }


/**
 * Ask to unzip file.
 *
 * @method file_unzip
 * @param string file to unzip
 * @param string destination to unzip
 */
    public function unzip($src, $dst)
    {
        $zip = zip_open($src);

        if (is_resource($zip))
        {
            $tree = "";

            while (($zip_entry = zip_read($zip)) !== false)
            {
                if (strpos(zip_entry_name($zip_entry),
                           DIRECTORY_SEPARATOR) !== false)
                {
                    $last = strrpos(zip_entry_name($zip_entry),
                                    DIRECTORY_SEPARATOR);
                    $dir = $dst . substr(zip_entry_name($zip_entry), 0, $last);
                    $file = substr(zip_entry_name($zip_entry),
                                   strrpos(zip_entry_name($zip_entry),
                                           DIRECTORY_SEPARATOR) + 1);

                    if (is_dir($dir) == false)
                    {
                        if (@mkdir($dir, 0755, true) == false)
                        {
                            $this->error("Unable to create '$dir'");
                            return false;
                        }
                    }

                    if (strlen(trim($file)) > 0)
                    {
                        $return = @file_put_contents(
                            $dir."/".$file, zip_entry_read(
                                $zip_entry, zip_entry_filesize($zip_entry)));

                        if ($return === false)
                        {
                            $this->error("Unable to write file '$dir/$file'");
                            return false;
                        }
                    }
                }
                else
                {
                    file_put_contents($file,
                                      zip_entry_read($zip_entry,
                                                     zip_entry_filesize($zip_entry)));
                }
            }
        }
        else
        {
            $this->error("Unable to open zip file '$src'");
            return false;
        }

        return $this->file_remove($src);
    }


/**
 * Return true if the directory received is valid, otherwise return false
 * If the boolean "$has_to_exist" is true, we create this directory if it
 * doesn't already exist.
 *
 * @method content_check_directory
 * @param string directory path
 * @param boolean ask to create the directory
 */
    public function content_check_directory($directory, $has_to_exist=false)
    {
        // we get the "content_types" list

        // we get the directory name

        // we create the directory if needed
    }

/**
 * Return the list of contents.
 *
 * @method contents_get_list
 * @param integer project_id
 */
    public function contents_get_list($project_id, $directory_name)
    {
	$rsp = array();

        $src = $this->global_path . "projects/$project_id/$directory_name";

	if (is_dir($src) && $this->directory_scan($src, $rsp, false, -1, null, true))
	    return $rsp;

	return false;
    }

/**
 * Return the list of plugins.
 *
 * @method plugins_get_list
 */
    public function plugins_get_list()
    {
	$rsp = array();

	if ($this->directory_scan($this->global_path . "plugins", $rsp, true, -1))
	    return $rsp;

	return false;
    }

/**
 * Check if a table exists otherwise automatically create if this
 * table is referenced on the static table.
 *
 * Return the name of the table if the table exists or is succesfully created
 * otherwise return false.
 *
 * @method table_check_and_create
 * @param int project id
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
 * @param int project id
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
 * @param int project id
 * @param string table name
 * @param hash values to insert
 */
    public function value_insert($project_id, $table_name, $values)
    {
	/* we check and create the table for the new value */
        if ($this->value_check($project_id, $table_name, $values)
	    && $this->table_check_and_create($project_id, $table_name))
        {
	    /* we insert the new value */
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
 * @param int project id
 * @param string table name
 * @param integer id of the element to modify
 * @param hash values that will replace old one
 */
    public function value_update($project_id, $table_name, $id, $values)
    {
	if ($this->value_check($project_id, $table_name, $values))
            return $this->db->row_update(
		"$project_id$table_name", $id, $values);

	return false;
    }

/**
 * Delete a value already stored.
 *
 * Return true if the values are deleted otherwise
 * return false.
 *
 * @method value_delete
 * @param int project id
 * @param string table name
 * @param integer id of the element to delete
 */
    public function value_delete($project_id, $table_name, $id)
    {
        return $this->db->row_delete("$project_id$table_name", $id);
    }

/**
 * Check if the value received is matching what is expected.
 *
 * @method value_check
 * @param int project id
 * @param string table name
 * @param hash values to check
 */
    private function value_check($project_id, $table_name, $values)
    {
	// we check that the project id exists and we get the name
	$project_name = $this->project_name_get_by_id($project_id);

	if ($project_name == false) {
	    $this->error("unknown project id '$project_id'!");
	    return false;
	}

	// we get the structure associated with the project name
	// and the table name
	$type = $this->type_get($project_name, $table_name);

	if ($type == false) {
	    $this->error(
		"unexpected type '$table_name' in project id '$project_id'!");
	    return false;
	}

	foreach ($values as $name => $value)
	{
	    // we check that all the expected values exists
	    $expected_format = $type[$name];

	    if ($expected_format == NULL)
	    {
		$this->error("found no format for '$name'"
			   . " in type '$table_name'");
		return false;
	    }

	    // we check that the defined values have expected type
	    if ($this->db->check_value($expected_format["db_type"], $value) == false)
	    {
		$this->error("invalid value '$value' for '$name'"
			   . " with type '"
                           . $expected_format["db_type"]
                           . "' in '$table_name'");
		return false;
	    }
	}

	// we check that the mandatory values are defined
	foreach ($type as $name => $format)
	{
	    if (array_key_exists("is_mandatory", $format)
		&& !array_key_exists($name, $values))
	    {
		$this->error("value '$name' is mandatory but not found"
			   . " in type '$table_name'");
		return false;
	    }
	}

	return true;
    }

/**
 * Get values stored in database.
 *
 * @method value_get
 * @param int project id
 * @param string table name
 * @param string wich parameter to use for sorting
 * @param integer number of elements
 * @param integer offset
 */
    public function value_get($project_id, $table_name,
                              $sort = NULL, $size = NULL,
			      $offset = NULL, $params = NULL)
    {
        $reel_name = "$project_id$table_name";

        if ($this->db->table_check($reel_name)) {

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
        return $this->object_to_array($this->db->handle_result($result));
    }

}

?>
