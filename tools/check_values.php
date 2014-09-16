<?php

require_once '/home/leo/zeek/lib/output.php';
require_once '/home/leo/zeek/lib/zeek_library.php';

$zlib = new ZeekLibrary();
$zlib->config(parse_ini_file('tools/check_values.ini'));

/* we establish the connection with the database */
if ($zlib->connect_to_database() == false)
    exit;
print "\nconnection to database OK\n";

$project_name = 'fullcheck';

/* we get the structure 1st */
$to_check = $zlib->structure_get($project_name);
if ($to_check == false)
    exit;

/* we create the project */
if ($zlib->project_add($project_name) == false) {
    print "\nimpossible to create project\n";
    exit;
}

$id = $zlib->project_get_id($project_name);

print "project created\n";


$table_name = 'test';

foreach ($to_check as $name => $params) {
    if ($name == 'all')
        continue;

    $result = $params[$name];

    print "check '$name' ";

    $var_type;
    $opt_size;

    if (is_array($result)) {
        $var_type = $result[0];
        $opt_size = $result[1];
        print "($var_type, $opt_size)\n";
    } else {
        $var_type = $result;
        print "($var_type)\n";
    }

    $values = get_value_from_format($var_type);

    if (is_array($values)) {
        $size = count($values);
        for ($i=0; $i < $size; $i++) {
            check_value($zlib, $id, $name, $name, $values[$i]);
        }
    }
}

function check_value($zlib, $project_id, $table, $attribute, $value)
{
    print "=> insert $project_id, $table, $attribute, $value\n";

    if ($zlib->value_insert($project_id, $table, array($attribute => $value))
            == false) {
        print "error insert!\n";
        return false;
    }

    $result = $zlib->value_get($project_id, $table, NULL, 1, 0);
    if ($result == false) {
        print "error get!\n";
        return false;
    }

    $row = $zlib->value_fetch($result);
    if ($row == NULL) {
        print "error fetch!\n";
        return false;
    }

    if ($row->$attribute != $value) {
        print "error insert '$value', get '". $row->$attribute . "'!\n";
        return false;
    }

    if ($zlib->value_delete($project_id, $table, $row->id) == false) {
        print "error delete!\n";
        return false;
    }

    return true;
}

function get_value_from_format($format, $opt_size=NULL)
{
  switch ($format) {
  case "TINYINT":
      return array(-128, 127);
  case "SMALLINT":
      return array(-32768, 32767);
  case "MEDIUMINT":
      return array(-8388608, 8388607);
  case "INTEGER":
      return array(-2147483648,  2147483647);
  case "BIGINT":
      return array(-9223372036854775808,  9223372036854775807);
  /* case "TINYINT_U": */
  /*     return array(0, 255); */
  /* case "SMALLINT_U": */
  /*     return array(0,  65535); */
  /* case "MEDIUMINT_U": */
  /*     return array(0,  16777215); */
  /* case "INTEGER_U": */
  /*     return array(0, 4294967295); */
  /* case "BIGINT_U": */
  /*     return array(0, 18446744073709551615); */

    /* case "FLOAT": */
    /* case "DOUBLEPRECISION": */
    /* case "REAL": */

    /* case "DECIMAL": */
    /* case "CHAR": */

    /* case "VARCHAR": */
    /* case "TINYTEXT": */
    /* case "TEXT": */
    /* case "LONGTEXT": */
    /* case "TINYBLOB": */
    /* case "BLOB": */
    /* case "LONGBLOB": */

    /* case "DATE": */
    /* case "DATETIME": */

    /* case "TIMESTAMP": */
    /*     return 1410560558; */
    /* case "TIME": */
    /*     return '12:21:22'; */
    /* case "YEAR": */
    /*     return 2014; */
  }
}
?>