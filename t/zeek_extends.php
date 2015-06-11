<?php

require_once 'lib/output.php';
require_once 'lib/zeek.php';

class ExtendsZeek extends Zeek
{
    private $output;
    public $send_email_output = true;

    public function checkOutput($input)
    {
        if ($this->output == $input) {
            $this->output = NULL;
            return true;
        }

        echo "\n expect : " . $input
	   . "\n received : " . $this->output . "\n";

        return false;
    }

    public function connect_to_database()
    {
        return $this->zlib->connect_to_database();
    }

    public function database()
    {
        return $this->db;
    }

    public function output($input)
    {
        /* echo "expect : " . $input . "\n"; */

        $this->output = $input;
    }

    public function disconnect()
    {
    }

    public function environment_clean()
    {
        $this->zlib->environment_clean($this->zlib->db_name);
    }

    public function send_email($email, $title, $msg)
    {
        return $this->send_email_output;
    }
}
