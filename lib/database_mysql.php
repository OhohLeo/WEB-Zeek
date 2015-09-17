/*
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

<?php

if (isset($this))
    require_once $this->global_path . 'lib/database.php';
else
    require_once 'lib/database.php';

/**
 * DataBaseOldMySQL : all the function to access to the old MySQL database.
 *
 * @package DataBaseOldMySQL
 */
class DataBaseOldMySQL extends DataBase {

/**
 * Establish a connection with MySQL database with old way
 *
 * @method connect
 * @param string database name
 * @param string login to connect to database
 * @param string password to connect to database
 */
    public function connect($host, $name, $login, $password)
    {
        try {
            if (mysql_connect($host, $login, $password) == false)
                return false;

            if (isset($name)) {
                if (mysql_select_db($name) == false)
                    return false;
            }
        } catch (Exception $e) {
            $this->error(
                "Impossible to connect to '$host' with MySQL "
                . $e->getMessage());
            return false;
        }

        return true;
    }


/**
 * Check if a database exists
 *
 * @method database_check
 * @param string database name
 */
     public function database_check($name)
     {
         $result = $this->send_request(
             "SHOW DATABASES LIKE '$name'", false);

         while ($row = mysql_fetch_assoc($result)) {
             mysql_free_result($result);
             return true;
         }

         mysql_free_result($result);
         return false;
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

        if ($params)
            $request .= " WHERE " . implode(' AND ', $this->get_values($params));


        $result = $this->send_request($request, $params);
        if ($result == NULL)
            return 0;

        if ($count = mysql_fetch_assoc($result))
            return $count["COUNT($field)"];

        return 0;
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
        $related_values = array();

        for ($i = 0; $i < count($fields); $i++) {
            $related_values[$i] = "'" . $values[$fields[$i]] . "'";
        }

        return ($this->send_request(
            "INSERT INTO $name(" . implode(',', $fields)
            . ') VALUES(' . implode(',', $related_values) . ')', NULL))
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

        if (is_bool($result)) {
            return $result;
        }

        if (mysql_num_rows($result) == 0) {
            return false;
        }

        return $this->send_request(
            "UPDATE $name SET " . implode(',', $this->get_values($values))
            . " WHERE id = $id", $values) ? true : false;
    }

/**
 * Fetch to get next result
 *
 * @method handle_result
 * @param result containing the value to fetch
 */
    public function handle_result($result)
    {
        if (is_bool($result)) {
            return $result;
        }

        if (mysql_num_rows($result) == 0) {
            return false;
        }

        /* sinon on retourne un résultat */
        $row = mysql_fetch_assoc($result);
        if (is_bool($row))
            return $row;

        $object = new stdClass();

        foreach ($row as $property => $value) {
            $object->{$property} = $value;
        }

        return $object;
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
         $result = $this->send_request($request, $boolean);

         if ($boolean) {
             if (is_bool($result))
                 return $result;
             if (isset($result))
                 return true;

             return false;
         }

         return $result;
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
        if ($this->debug) {
            print("\n request = $request;\n"
            . "params = " . var_dump($params) . "\n");
        }

        $result = mysql_query("$request;");

        if (!is_bool($params) && $result == false) {
            $this->error(
                "Impossible to send request : " . mysql_error());
            return false;
        }

        return $result;
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
            $values[$i] = $fields[$i] . " = '" . $params[$fields[$i]] . "'";
        }

        return $values;
    }
}
