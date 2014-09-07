<?php

/**
 * DataBase : all the function to access to the database.
 *
 * @package DataBase
 */
class DataBase extends ZeekOutput {

    private $db;
    protected $debug = false;
    private $config;

    # "ENUM" & "SET" are not handled
    private $valid_type = array(
        "TINYINT", "SMALLINT", "MEDIUMINT", "INT", "INTEGER", "BIGINT",
        "FLOAT", "DOUBLEPRECISION", "REAL", "DECIMAL", "CHAR", "VARCHAR",
        "TINYTEXT", "TEXT", "LONGTEXT", "TINYBLOB", "BLOB", "LONGBLOB",
        "DATE", "DATETIME", "TIMESTAMP", "TIME", "YEAR");

    private $translation = array("(" => "", ")" => "");

/**
 * Display SQL request sent
 *
 * @method set_debug
 * @param boolean true/false
 */
    public function set_debug($status)
    {
        $this->debug = $status;
    }

/**
 * Set configuration
 *
 * @method set_config
 * @param string PDO/MYSQL
 */
    public function set_config($config)
    {
        $this->config = $config;
    }

/**
 * Establish a connection with MySQL database
 *
 * @method connect
 * @param string database name
 * @param string login to connect to database
 * @param string password to connect to database
 */
    public function connect($host, $name, $login, $password)
    {
        try {
            $this->db = new PDO(
                "mysql:host=$host;dname=$name", $login, $password,
                array(PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION));
         } catch (Exception $e) {
            $this->error(
                "Impossible to connect to '$host' with MySQL "
                . $e->getMessage());
            return false;
        }

        return true;
    }


/**
 * Create a new database
 *
 * @method database_create
 * @param string database name
 */
    public function database_create($name)
    {
        return $this->send_query(
            "CREATE DATABASE IF NOT EXISTS $name", true);
    }

/**
 * Delete the database
 *
 * @method database_delete
 * @param string database name
 */
    public function database_delete($name)
    {
        return $this->send_query(
            "DROP DATABASE IF EXISTS $name", true);
    }

/**
 * Select a database for future actions
 *
 * @method database_use
 * @param string database name
 */
    public function database_use($name)
    {
        return $this->send_query("USE $name", true);
    }

/**
 * Check if a database exists
 *
 * @method database_check
 * @param string database name
 */
     public function database_check($name)
     {
         $result = $this->send_query(
             "SHOW DATABASES LIKE '$name'", false);

        foreach ($result as $row) {
            return true;
        }

        return false;
    }

/**
 * Create a new table
 *
 * It automatically creates the auto-increment 'id' integer attribute.
 *
 * You can add new attribute as you want.
 *
 * All attributes are "NOT NULL" by default.
 *
 * @method table_create
 * @param string table name
 * @param array specifying the list of attributes in the table
 */
    public function table_create($name, $attributes)
    {
        $request = "CREATE TABLE $name(" .
            "id INT(11) NOT NULL AUTO_INCREMENT,";

        if (isset($attributes) && is_array($attributes)) {

            foreach ($attributes as $key => $type) {

                $request .= "$key  ";

                $vartype = NULL;
                $size = NULL;
                $default = NULL;

                /* we validate the type at first */
                if (is_array($type)) {

                    $type_size = count($type);

                    /* 1st element is the vartype */
                    $vartype = $type[0];

                    if ($type_size > 1) {
                        $size = $type[1];
                    }

                    if ($type_size > 2) {
                        $default = $type[2];
                    }

                } else {
                    $vartype = $type;
                }

                /* the vartype should be defined */
                if ($vartype == NULL) {
                    $this->error(
                        "Unknown attribute type for value '$value'"
                        . " when creating table '$name'");
                    return false;
                }

                /* the vartype should be valid */
                if (in_array($vartype, $this->valid_type)) {
                        $request .= $vartype;
                } else {
                    $this->error(
                        "Unexpected attribute type '$vartype'"
                        . " when creating table '$name'");
                }

                /* 2nd element is the size */
                if (isset($size)) {
                    if (is_numeric($size)) {
                        $request .= "($size) ";
                    } else {
                        $this->error(
                            "Unexpected attribute size '$size' on type "
                            . "'$vartype' when creating table '$name'");
                        return false;
                    }
                }

                /* 3rd element is or the default value or the 'NULL' /
                 * 'NOT NULL' */
                if (isset($default)) {
                    if ($default == "NOT NULL" or $default == "NULL") {
                        $request .= "$default ";
                    } else {
                        $request .= "NOT NULL DEFAULT '$default' ";
                    }
                } else {
                    /* by default the value is NOT NULL */
                    $request .= " NOT NULL ";
                }

                $request .= ", ";
            }
        }

        $request .=  "PRIMARY KEY (id))";

        return $this->send_query($request, true);
    }

/**
 * Delete a table
 *
 * @method table_delete
 * @param string table name
 */
        public function table_delete($name)
    {
        return $this->send_query("DROP TABLE IF EXISTS $name", true);
    }

/**
 * Check if a table exists
 *
 * @method table_check
 * @param string table name
 */
    public function table_check($name)
    {
        return $this->send_query("SHOW COLUMNS FROM $name", true);
    }

/**
 * Count number of row in the specified table
 *
 * @method table_count
 * @param string table name
 * @param string specify the field to count
 * @param array specific parameters
 */
    public function table_count($name, $field, $params)
    {
        $request = "SELECT COUNT($field) FROM $name";

        if ($params) {
            $request .= " WHERE " . implode(' AND ', $this->get_values($params));
        }

        $result = $this->send_request($request, $params);
        if ($result == NULL) {
            return 0;
        }

        if ($count = $result->fetch(PDO::FETCH_ASSOC))
            return $count["COUNT($field)"];

        return 0;
    }

/**
 * Get the description of a table with all the Field, Type, Size & Default Value.
 *
 * @method table_describe
 * @param string table name
 */
    public function table_describe($name)
    {
        return $this->send_request("DESCRIBE $name", NULL);
    }

/**
 * Get the values of a table with all the specified field.
 *
 * @method table_view
 * @param string table name
 * @param string fields
 * @param array specify how to sort the data 'field_name => [ASC/DESC]'
 * @param integer number of data
 * @param integer offset (size attribute above is mandatory
 * @param array specific conditions
 */
    public function table_view($name, $fields, $sort, $size, $offset, $params)
    {
        $options = '';

        # we can't have offset without size
        if (isset($offset) && !isset($size)) {
            $this->error("table_view : size parameters should be defined!");
            return false;
        }

        if (is_array($fields)) {
            $fields = implode(',', $fields);
        }

        if (is_array($sort)
            and count($sort) == 2
            and in_array($sort[1], array('ASC', 'DESC'))) {
            $options .= " ORDER BY " . implode(' ', $sort);
        }

        if (is_array($params)){
            $options .= " WHERE " . implode(' AND ', $this->get_values($params));
        }

        if (is_numeric($size)) {
            $options .= " LIMIT $size";
        }

        if (is_numeric($offset)) {
            $options .= " OFFSET $offset";
        }

        return $this->send_request("SELECT $fields FROM $name $options",
                                   $params);
    }

/**
 * Add a new row to a table.
 *
 * @method row_insert
 * @param string table name
 * @param array values to insert
 */
    public function row_insert($name, $values)
    {
        $fields = array_keys($values);
        $protected_values = array();

        for ($i = 0; $i < count($fields); $i++) {
            $protected_values[$i] = ':' . $fields[$i];
        }

        return ($this->send_request(
            "INSERT INTO $name(" . implode(',', $fields)
            . ') VALUES(' . implode(',', $protected_values) . ')', $values))
            ? true : false;
    }

/**
 * Modify a row to a table.
 *
 * @method row_update
 * @param string table name
 * @param integer id of the row
 * @param array new values
 */
    public function row_update($name, $id, $values)
    {
        $result =
            $this->table_view($name, 'id', NULL, NULL, NULL, array('id' => $id));

        if (gettype($result) == 'object' and $result->rowCount()) {
            return $this->send_request(
                "UPDATE $name SET " . implode(',', $this->get_values($values))
                . " WHERE id = $id", $values) ? true : false;
        }

        return false;
    }


/**
 * Remove a row from a table.
 *
 * @method row_delete
 * @param string table name
 * @param integer/array id of the row or list of parameters
 */
    public function row_delete($name, $params)
    {
        if (is_numeric($params)) {
            $params = array('id' => $params);
        }

        if ($this->send_request(
            "DELETE FROM $name "
            . " WHERE " . implode(' AND ', $this->get_values($params)), $params))
            return true;

        return false;
    }


/**
 * Check values before storing in database.
 *
 * @method value_check
 * @param string type expected
 * @param string value to store
 */
    public function value_check($type, $value)
    {
	switch ($type) {
	    case "TINYINT":
		return $this->check_integer(
		    $value, -128, 127, 0, 255);
	    case "SMALLINT":
		return $this->check_integer(
		    $value, -32768, 32767, 0, 65535);
	    case "MEDIUMINT":
		return $this->check_integer(
		    $value, -8388608, 8388607, 0, 16777215);
	    case "INT":
		return $this->check_integer(
		    $value, -2147483648, 2147483647, 0, 4294967295);
	    case "BIGINT":
		return $this->check_integer(
		    $value, -9223372036854775808, 9223372036854775807, 0,
		    18446744073709551615);

	    case "DECIMAL":
	    case "INTEGER":
	    case "FLOAT":
	    case "DOUBLEPRECISION":
	    case "REAL":
	        return is_numeric($value);

	    case "CHAR":
	    case "VARCHAR":
	    case "NYTEXT":
	    case "TEXT":
	    case "LONGTEXT":
	    case "TINYBLOB":
	    case "BLOB":
	    case "LONGBLOB":
 	        return $this->check_text($value);

	    case "DATE": # '0000-00-00'
	        return (date('Y-m-d', strtotime($value)) == $value);
	    case "TIME": # '00:00:00'
	        return (date('H:i:s', strtotime($value)) == $value);
	    case "DATETIME": # '0000-00-00 00:00:00'
	        return (date('Y-m-d H:i:s', strtotime($value)) == $value);

	    case "TIMESTAMP": # 00000000000000
 	        return is_int($value);
	    case "YEAR": # 0000
	    	return is_int($value)
		       && ($value >= 0 && $value <= 9999);
	}

	return false;
    }


/**
 * Check integer values before storing in database.
 *
 * @method check_integer
 * @param string integer to check
 */
    public function check_integer($value, $min_signed, $max_signed,
				  $min_unsigned, $max_unsigned)
    {
	return is_int($value)
	    && (($value >= $min_signed && $value <= $max_signed)
		|| ($value >= $min_unsigned && $value <= $max_unsigned));
    }

/**
 * Check text values before storing in database.
 *
 * @method check_text
 * @param string text to check
 */
    public function check_text($text)
    {
	return (preg_match('/((create|drop) (database|table))|(insert into)/i', $text) == 0);
    }

/**
 * Fetch to get next result
 *
 * @method handle_result
 * @param result containing the value to fetch
 */
    public function handle_result($result)
    {
        return $result->fetch();
    }

/**
 * Send a SQL query to MySQL server
 *
 * Automatically add the ';' at the end.
 *
 * @method send_query
 * @param string SQL request to be sent without the ';'
 * @param boolean  if true = return true if there is an answer, false otherwise
 *          if false = return the result of the request
 */
    protected function send_query($request, $boolean)
    {
        $db = $this->db;

        if ($db) {

            try {

                if ($this->debug) {
                    print("\n query = $request; \n");
                }

                $result = $db->query("$request;");

                return ($boolean)
                    ? (isset($result) ? true : false)
                    : $result;

           } catch (Exception $e) {
                $this->error(
                    "send_query : impossible to send request '$request' " . $e->getMessage());

                return false;
            }

            return true;
        }

        $this->error("send_query: need to establish connection with database 1st!");
        return false;
    }

/**
 * Send a secured SQL request to MySQL server
 *
 * Automatically add the ';' at the end.
 *
 * @method send_request
 * @param string SQL request to be sent without the ';'
 * @param boolean  if true = return true if there is an answer, false otherwise
 *          if false = return the result of the request
 */
    protected function send_request($request, $params)
    {
        $db = $this->db;
        static $last_request;
        static $result;

        if ($db) {

            try {
                if ($this->debug) {
                    print("\n request = $request;\n"
                          . "last_request = $last_request\n"
                          . "params = " . var_dump($params) . "\n");
                    }

                if ($request != $last_request)
                {
                    $result = $db->prepare("$request;");
                    $result->setFetchMode(PDO::FETCH_OBJ);
                    $last_request = $request;
                }

                $result->execute($params);

                return $result;

           } catch (Exception $e) {
                $this->error(
                    "Impossible to send request : " . $e->getMessage()
		    . "\n request = $request\n"
		    . " params = " . var_dump($params) . "\n");

                return false;
            }

            return true;
        }

        $this->error("Need to establish connection with database at first");
        return false;
    }


/**
 * Return an array of string : "field = :field".
 *
 * @method get_values
 * @param array parameters to handle
 */
    protected function get_values($params)
    {
        $fields = array_keys($params);
        $values = array();

        for ($i = 0; $i < count($fields); $i++) {
            $values[$i] = $fields[$i] . " = :" . $fields[$i];
        }

        return $values;
    }
}

?>