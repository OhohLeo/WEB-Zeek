<?php

class DataBaseAccess {

    private $pdo;

    public function connect($dname, $login, $password)
    {
        $project_name = $dname;

        try {
            $this->pdo = new PDO("mysql:host=localhost;dname=$dname",
                                 $login, $password);
        } catch (Exception $e) {
            $this->output("Impossible to connect with MySQL " . $e->getMessage());
            return false;
        }

        return true;
    }

    public function check_database($dbname)
    {
        if ($this->check_connection())
        {
            $res = $this->pdo->query("SHOW DATABASES");
            return true;
        }

        return false;
    }


    private function check_connection()
    {
        if ($this->pdo) {
            return true;
        }

        echo "HERE?";
        $this->output("Need to establish connection with database 1st");
        return false;
    }

    protected function output($input) {
        echo $input;
    }
}

?>
