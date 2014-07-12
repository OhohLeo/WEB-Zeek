<?php

/**
 * DataBaseAccess : all the function to access to the database.
 *
 * @package DataBase Access
 */
class DataBaseAccess {

    private $pdo;

    private $valid_type = array(
        "TINYINT", "SMALLINT", "MEDIUMINT", "INT", "INTEGER", "BIGINT",
        "FLOAT", "DOUBLEPRECISION", "REAL", "DECIMAL", "CHAR", "VARCHAR",
        "NYTEXT", "TEXT", "LONGTEXT", "TINYBLOB", "BLOB", "LONGBLOB",
        "ENUM", "SET", "DATE", "DATETIME", "TIMESTAMP", "TIME", "YEAR");

    private $translation = array("(" => "", ")" => "");

/**
 * Establish a connection with MySQL database
 *
 * @method connect
 * @param string database name
 * @param string login to connect to database
 * @param string password to connect to database
 */
    public function connect($name, $login, $password)
    {
        $project_name = $name;

        try {
            $this->pdo = new PDO(
                "mysql:host=localhost", $login, $password,
                array(PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION));
         } catch (Exception $e) {
            $this->output(
                "Impossible to connect with MySQL " . $e->getMessage());
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

            $step = 0;
            $value = NULL;

            $remaining_size = count($attributes);

            while ($remaining_size--) {

                $value = array_shift($attributes);

                if ($step == 2) {

                    if (substr($value, 0, 8) == 'DEFAULT=') {
                        $request .= "NOT NULL DEFAULT " . substr($value, 8) . ", ";
                        $step = 0;
                        continue;
                    }

                    if ($value == "NOT NULL" or $value == "NULL") {
                        $request .= "$value, ";
                        $step = 0;
                        continue;
                    }

                    /* actual value start a new attribute */
                    $request .= "NOT NULL, ";
                    $step = 0;
                }

                if ($step == 1) {
                    $vartype = strstr($value, '(', true);
                    $size = NULL;

                    if ($vartype) {
                        $size = strstr($value, '(', false);
                        $size = strtr($size, $this->translation);
                    } else {
                        $vartype = $value;
                    }

                    if (in_array($vartype, $this->valid_type)) {
                        $request .= $vartype;
                    } else {
                        $this->output(
                            "Unexpected attribute type '$vartype'"
                            . " when creating table '$name'");
                        return false;
                    }

                    if ($size) {
                        if (is_numeric($size)) {
                            $request .= "($size) ";
                        } else {
                            $this->output(
                                "Unexpected attribute size '$size' on type "
                                . "'$vartype' when creating table '$name'");
                            return false;
                        }
                    }

                    /* on gère le cas où c'est le dernier élément */
                    if ($remaining_size == 0) {
                        $request .= "NOT NULL, ";
                    } else {
                        $request .= " ";
                        $step = 2;
                    }

                    continue;
                }

                if ($step == 0) {

                    /* on gènère une erreur au cas où c'est le dernier élément */
                    if ($remaining_size == 0) {
                        $this->output(
                            "Unknown attribute type for value '$value'"
                            . " when creating table '$name'");
                        return false;
                    }

                    $request .= "$value ";
                    $step = 1;
                    continue;
                }
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
 */
    public function table_view($name, $fields, $sort, $size, $offset)
    {
        $options = '';

        if (is_array($fields)) {
            $fields = implode(',', $fields);
        }

        if (is_array($sort)
            and count($sort) == 2
            and in_array($sort[1], array('ASC', 'DESC'))) {
            $options .= " ORDER BY " . implode(' ', $sort);
        }

        if (is_numeric($size)) {
            $options .= " LIMIT $size";
        }

        if (is_numeric($offset)) {
            $options .= " OFFSET $offset";
        }

        return $this->send_request("SELECT $fields FROM $name$options", NULL);
    }

/**
 * Add a new row to a table.
 *
 * @method row_insert
 * @param string table name
 * @param array parameters to insert
 */
    public function row_insert($name, $params)
    {
        $fields = array_keys($params);
        $values = array();

        for ($i = 0; $i < count($fields); $i++) {
            $values[$i] = ':' . $fields[$i];
        }

        return $this->send_request(
            "INSERT INTO $name(" . implode(',', $fields)
            . ') VALUES(' . implode(',', $values) . ')', $params);
    }

/**
 * Modify a row to a table.
 *
 * @method row_update
 * @param string table name
 * @param integer id of the row
 * @param array parameters to alter
 */
    public function row_update($name, $id, $params)
    {
        return $this->send_request(
            "UPDATE $name SET " . implode(',', $this->get_values($params))
            . " WHERE id = $id", $params);
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

        return $this->send_request(
            "DELETE FROM $name "
            . " WHERE " . implode(' AND ', $this->get_values($params)), $params);
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
    private function send_query($request, $boolean)
    {
        $pdo = $this->pdo;

        if ($pdo) {

            try {
                $result = $pdo->query("$request;");

                return ($boolean)
                    ? (isset($result) ? true : false)
                    : $result;

           } catch (Exception $e) {
                $this->output(
                    "Impossible to send request : " . $e->getMessage());

                return false;
            }

            return true;
        }

        $this->output("Need to establish connection with database 1st");
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
    public function send_request($request, $params)
    {
        $pdo = $this->pdo;
        static $last_request;
        static $result;

        if ($pdo) {

            try {
                if ($request != $last_request)
                {
                    print("$request; \n" . var_dump($params));
                    $result = $pdo->prepare("$request;");
                    $result->setFetchMode(PDO::FETCH_OBJ);
                    $last_request = $request;
                }

                $result->execute($params);

                return $result;

           } catch (Exception $e) {
                $this->output(
                    "Impossible to send request : " . $e->getMessage());

                return false;
            }

            return true;
        }

        $this->output("Need to establish connection with database 1st");
        return false;
    }


/**
 * Return an array of string : "field = :field".
 *
 * @method get_values
 * @param array parameters to handle
 */
    private function get_values($params)
    {
        $fields = array_keys($params);
        $values = array();

        for ($i = 0; $i < count($fields); $i++) {
            $values[$i] = $fields[$i] . " = :" . $fields[$i];
        }

        return $values;
    }

/**
 * Echo all input
 *
 * @method output
 * @param string data to display
 */
    protected function output($input) {
        echo $input . "\n";
    }
}

?>
