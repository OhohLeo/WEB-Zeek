<?php

class DataBaseAccess {

    private $pdo;

    private $valid_type = array(
        "TINYINT", "SMALLINT", "MEDIUMINT", "INT", "INTEGER", "BIGINT",
        "FLOAT", "DOUBLEPRECISION", "REAL", "DECIMAL", "CHAR", "VARCHAR",
        "NYTEXT", "TEXT", "LONGTEXT", "TINYBLOB", "BLOB", "LONGBLOB",
        "ENUM", "SET", "DATE", "DATETIME", "TIMESTAMP", "TIME", "YEAR");

    public function connect($dname, $login, $password)
    {
        $project_name = $dname;

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

    public function database_create($name)
    {
        return $this->send_request(
            "CREATE DATABASE IF NOT EXISTS $name", 1);
    }

    public function database_delete($name)
    {
        return $this->send_request(
            "DROP DATABASE IF EXISTS $name", 1);
    }

    public function database_use($name)
    {
        return $this->send_request("USE $name", 1);
    }

    public function database_check($name)
    {
        $result = $this->send_request(
            "SHOW DATABASES LIKE '$name'", NULL);

        foreach ($result as $row) {
            return true;
        }

        return false;
    }

    public function table_create($name, $attributes)
    {
        $request = "CREATE TABLE IF NO EXISTS $name";

        if (isset($attributes) && is_array($attributes)) {

            $request .= "(\n";

            foreach ($attributes as $key => $type) {
                $limit = NULL;
                $default_value = NULL;

               if (is_array($type))
                {
                    $array_type = $type;
                    $type = $array_type[0];

                    foreach ($array_type as $param => $value) {
                        switch ($param) {
                          case "limit":
                              $limit = $value;
                              break;
                          case "default_value":
                              $default_value = $value;
                              break;
                        }
                    }
                }

                if (in_array($type, $this->valid_type)) {
                    $request .= "\t$key $type";

                    if ($key == "id" && $type == "INT") {
                        $request .= " NOT NULL AUTO_INCREMENT,"
                            . "\n\tAUTO_INCREMENT(id),\n";
                        continue;
                    }

                    if ($key == "current_time" && $type == "TIMESTAMP") {
                        $request .= " DEFAULT NOW(),\n";
                        continue;
                    }

                    if ($limit) {
                        $request .= "($limit)";
                    }

                    if ($default_value) {
                        $request .= " NOT NULL DEFAULT $default_value";
                    }

                    $request .= ",\n";

                } else {
                    $this->output(
                        "Unexpected attribute type '$type'"
                        . " for attribute '$key'"
                        . " when creating table '$name'");

                    return false;
                }
            }

            $request = substr($request, 0, -2) . ")";
        }

        $res = $this->send_request($request, NULL);
        print_r($res);
    }

    public function table_delete($name)
    {
        return false;
    }

    private function send_request($request, $boolean)
    {
        if ($this->pdo) {

            try {
                print "\n ========= \n $request\n ========= \n";
                $result = $this->pdo->query("$request;");

                if ($boolean) {
                    return isset($result) ? true : false;
                }

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

    protected function output($input) {
        echo $input . "\n";
    }
}

?>
