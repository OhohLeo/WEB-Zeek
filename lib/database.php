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
        "TINYINT",     # -128       => 127
        "TINYINT_U",   #    0       => 255
        "SMALLINT",    # -32768     => 32767
        "SMALLINT_U",  #    0       => 65535
        "MEDIUMINT",   # -8388608   => 8388607
        "MEDIUMINT_U", #    0       => 16777215
        "INT",
        "INT_U",
        "BIGINT",
        "BIGINT_U",

        "DECIMAL",
        "INTEGER",
        "FLOAT",
	"DOUBLEPRECISION",
	"REAL",

        "DATE",      # YYYY-MM-DD
        "TIME",      # HH:MM:SS
        "TIMESTAMP", # YYYY-MM-DD HH:MM:SS from '1971-01-01 00:00:01'
        "DATETIME",  # YYYY-MM-DD HH:MM:SS
	"YEAR",      # YYYY

        "CHAR",
        "VARCHAR",
        "TINYTEXT",   # 255 chars max
        "TEXT",       # 65535 chars max
        "MEDIUMTEXT", # 16777215 chars max
        "LONGTEXT",   # 4294967295 chars max
        "TINYBLOB",
        "BLOB",
        "MEDIUMBLOB",
        "LONGBLOB");

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
 * Return attribute parameters request
 *
 * @method attribute_add
 * @param string attribute name
 * @param array/string detail parameter
 */
    private function attribute_add($name, $type)
    {
        $request = "$name  ";

        $vartype = NULL;
        $size = NULL;
        $default = NULL;

        /* we validate the type at first */
        if (is_array($type)) {

            /* 1st element is the vartype */
            $vartype = $type["type"];

	    if (array_key_exists("size", $type))
		$size = $type["size"];

	    if (array_key_exists("default", $type))
		$default = $type["default"];

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
        if ($this->check_type($vartype)) {

            /* we add the unsigned flag */
            if (preg_match('/^([A-Z]+)_U$/', $vartype, $result)) {
                $vartype = $result[1] . ' UNSIGNED ';
            }

            $request .= "$vartype";
        } else {
            $this->error(
                "Unexpected attribute type '$vartype'"
              . " when creating table '$name'");

            return false;
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
                $request .= " $default ";
            } else {
                $request .= " NOT NULL DEFAULT '$default' ";
            }
        } else {
            /* by default the value is NOT NULL */
            $request .= " NOT NULL ";
        }

        return $request;
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

            foreach ($attributes as $name => $type) {

                $attribute_request = $this->attribute_add($name, $type);

                if ($attribute_request == false)
                    return false;

                $request .= $attribute_request . ", ";
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
 * Show tables associated to the selected database
 *
 * @method tables_show
 * @param string database name
 */
    public function tables_show($name)
    {
        $result = $this->send_query("SHOW TABLES", false);
        if ($result == NULL) {
            return false;
        }

        $tables = array();

        while ($next_result = $result->fetch(PDO::FETCH_ASSOC))
        {
            array_push($tables, $next_result["Tables_in_$name"]);
        }

        return $tables;
    }


/**
 * List attribute and type from a table
 *
 * @method table_show
 * @param string table name
 */
    public function table_show($name)
    {
        $result = $this->send_query("SHOW COLUMNS FROM $name", false);
        if ($result == NULL) {
            return false;
        }

        $attributes =  array();

        while ($next_result = $result->fetch(PDO::FETCH_ASSOC))
        {
            $attributes[$next_result["Field"]] = $next_result;
        }

        return $attributes;
    }

/**
 * Add the attribute of the table
 *
 * @method table_add_attribute
 * @param string table name
 * @param string attribute name
 * @param type type associated to the attribute
 */
    public function table_add_attribute($table_name, $attribute_name, $type)
    {
        $attribute_request = $this->attribute_add($attribute_name, $type);

        if ($attribute_request == false)
            return false;

        return $this->send_query(
            "ALTER TABLE $table_name ADD COLUMN $attribute_request", true);
    }

/**
 * Remove an attribute of the table
 *
 * @method table_add_column
 * @param string attribute name
 */
    public function table_remove_attribute($table_name, $attribute_name)
    {
        return $this->send_query(
            "ALTER TABLE $table_name DROP COLUMN $attribute_name", true);
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
    public function table_view($name,
      $fields=NULL, $sort=NULL, $size=NULL, $offset=NULL, $params=NULL)
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

        if (is_numeric($params))
            $params = array('id' => $params);

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

	$result = $this->send_request(
	    "DELETE FROM $name "
	  . " WHERE " . implode(' AND ', $this->get_values($params)), $params);

	if ($result)
	    return true;

        return false;
    }


/**
 * Check if the type of a value exist.
 *
 * @method check_type
 * @param string type expected
 */
    public function check_type($type)
    {
        return in_array($type, $this->valid_type);
    }

/**
 * Check values before storing in database.
 *
 * @method check_value
 * @param string type expected
 * @param string value to store
 */
    public function check_value($type, $value)
    {
	switch ($type) {
	    case "TINYINT":
		return $this->check_integer(
		    $value, -128, 127);
	    case "TINYINT_U":
		return $this->check_integer(
		    $value, 0, 255);
            case "SMALLINT":
		return $this->check_integer(
		    $value, -32768, 32767);
            case "SMALLINT_U":
		return $this->check_integer(
		    $value, 0, 65535);
	    case "MEDIUMINT":
		return $this->check_integer(
		    $value, -8388608, 8388607);
            case "MEDIUMINT_U":
		return $this->check_integer(
		    $value, 0, 16777215);
	    case "INT":
		return $this->check_integer(
		    $value, -2147483648, 2147483647);
            case "INT_U":
		return $this->check_integer($value, 0, 4294967295);
	    case "BIGINT":
		return $this->check_integer(
		    $value, -9223372036854775808, 9223372036854775807);
            case "BIGINT_U":
		return $this->check_integer(
		    $value, 0, 18446744073709551615);

	    case "DECIMAL":
	    case "INTEGER":
	    case "FLOAT":
	    case "DOUBLEPRECISION":
	    case "REAL":
	        return is_numeric($value);

	    case "CHAR":
	    case "VARCHAR":
	    case "TEXT":
	    case "MEDIUMTEXT":
	    case "LONGTEXT":
	    case "TINYBLOB":
	    case "BLOB":
	    case "MEDIUMBLOB":
	    case "LONGBLOB":
 	        return $this->check_text($value);

	    case "DATE": # '0000-00-00'
	        return (date('Y-m-d', strtotime($value)) == $value);
	    case "TIME": # '00:00:00'
	        return (date('H:i:s', strtotime($value)) == $value);
            case "TIMESTAMP":
	    case "DATETIME": # '0000-00-00 00:00:00'
	        return (date('Y-m-d H:i:s', strtotime($value)) == $value);
	    case "YEAR": # 0000
	    	return is_int(0+$value)
		    && ($value >= 1901 && $value <= 9999);
	}

	return false;
    }


    /**
     * Check integer values before storing in database.
     *
 * @method check_integer
 * @param string integer to check
 */
    public function check_integer($value, $min, $max)
    {
	return is_int(0+$value) && $value >= $min && $value <= $max;
    }

/**
 * Check text values before storing in database.
 *
 * @method check_text
 * @param string text to check
 */
    public function check_text($text)
    {
	return (preg_match('/((create|drop) (database|table))|(insert into)/i', $text)
        == 0);
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
                $this->debug(
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


        if ($db == NULL) {
            $this->error("Need to establish connection with database at first");
            return false;
	}

	try {

	    if ($params == NULL)
		$params = array();

            if ($this->debug) {
                print("\n ====== \nrequest = $request;\n"
                    . " - params = " . join(' ', $params) . "\n"
                    . " - last_request = $last_request\n");
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
            $this->debug(
                "Impossible to send request : " . $e->getMessage()
	      . "\n request = $request\n"
	      . " params = " . join(' ', $params) . "\n");

            return false;
        }

        return true;
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
