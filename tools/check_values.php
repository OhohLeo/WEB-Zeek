<?php

require_once '/home/leo/perso/WEB-Zeek/lib/output.php';
require_once '/home/leo/perso/WEB-Zeek/lib/zeek_library.php';

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

foreach ($to_check as $table => $params) {

    if ($table == 'all') {

        foreach ($params as $attribute => $type) {
            launch_test($zlib, $id, $table, $attribute, $type);
        }

        continue;
    }

    launch_test($zlib, $id, $table, $table, $params[$table]);
}

function launch_test($zlib, $id, $table, $attribute, $input)
{
    print "check '$table $attribute' ";

    $var_type;
    $opt_size;

    if (is_array($input)) {
        $var_type = $input[0];
        $opt_size = $input[1];
        print "($var_type, $opt_size)\n";
    } else {
        $var_type = $input;
        print "($var_type)\n";
    }

    $values = get_value_from_format($var_type);

    if (is_array($values)) {
        $size = count($values);
        for ($i=0; $i < $size; $i++) {
            check_value($zlib, $id, $table, $attribute, $values[$i]);
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
  case "TINYINT_U":
      return array(0, 255);
  case "SMALLINT":
      return array(-32768, 32767);
  case "SMALLINT_U":
      return array(0,  65535);
  case "MEDIUMINT":
      return array(-8388608, 8388607);
  case "MEDIUMINT_U":
      return array(0,  16777215);
  case "INT":
  case "INTEGER":
      return array(-2147483648,  2147483647);
  case "INT_U":
  case "INTEGER_U":
      return array(0, 4294967295);
  case "BIGINT":
      return array(-9223372036854775808,  9223372036854775807);
  case "BIGINT_U":
      return array(0, 18446744073709551615);
    case "FLOAT":
        return array(-3.40282E+38, -1.5, 0, 1.5,
                     1.17549E-38, 3.40282E+38);
    case "FLOAT_U":
        return array(0, 1.5, 1.175494351E-38, 3.402823466E+38);
    case "DOUBLE":
        return array(
            -1.7976931348623e308,  -1.5, -2.2250738585072E-308,
            0, 2.2250738585072E-308, 1.5, 1.7976931348623e308);
   case "DOUBLE_U":
       return array(0, 2.2250738585072E-308, 1.5, 1.7976931348623e308);
    case "DECIMAL":
        return array(-9999999999, -555, 0, 1231, 9999999999);


    case "DATE":
        return array('1000-01-01', '2014-09-16', '9999-12-31');
    case "DATETIME":
        return array('1000-01-01 00:00:00', '2014-09-16 11:25:15', '9999-12-31 23:59:59');
    case "TIMESTAMP":
        return array('1971-01-01 00:00:01',  '2014-09-16 11:25:15');
    case "TIME":
        return array('-838:59:59', '12:21:22', '838:59:59');
    case "YEAR":
        return array(1901, 2014, 2155);

    case "CHAR":
    case "VARCHAR":
        return array('toto', '', 'titi est malade');
    case "TINYTEXT":
    case "TEXT":
    case "MEDIUMTEXT":
    case "LONGTEXT":
    case "TINYBLOB":
    case "BLOB":
    case "MEDIUMBLOB":
    case "LONGBLOB":
        return array("turlututu \n chapeau poitu!!");
  }
}
?>