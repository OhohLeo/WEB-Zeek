<?php
$projects = array(
    "test" => array(
	"artist" => array(
	    "name"      => array("type" => "VARCHAR", "size" => 100),
	    "surname"   => array("type" => "VARCHAR", "size" => 100),
	    "age"       => array("type" => "INT_U"),
	    "subtitle"  => array("type" => "VARCHAR", "size" => 300),
	    "biography" => array("type" => "TEXT", "size" => 1000),
	    "skill"     => array("type" => "VARCHAR", "size" => 100)),
	"show"   => array(
	    "name"      => array("type" => "VARCHAR", "size" => 100),
	    "date"      => array("type" => "DATE"),
	    "hour"      => array("type" => "TIME"),
	    "location"  => array("type" => "VARCHAR", "size" => 300)),
	"news"   => array(
	    "name"      => array("type" => "VARCHAR", "size" => 100),
	    "date"      => array("type" => "DATE"),
	    "comments"  => array("type" => "VARCHAR", "size" => 100)),
	"album"  => array(
	    "name"      => array("type" => "VARCHAR", "size" => 100),
	    "duration"  => array("type" => "INT_U"),
	    "comments"  => array("type" => "TEXT", "size" => 1000)),
	"music"  => array(
	    "name"      => array("type" => "VARCHAR", "size" => 100),
	    "date"      => array("type" => "DATE"),
	    "duration"  => array("type" => "INT_U"),
	    "comments"  => array("type" => "TEXT", "size" => 1000)),
	"video"  => array(
	    "name"      => array("type" => "VARCHAR", "size" => 100),
	    "date"      => array("type" => "DATE"),
	    "duration"  => array("type" => "INT_U"),
	    "comments"  => array("type" => "TEXT", "size" => 1000)),
	"media"  => array(
	    "name"      => array("type" => "VARCHAR", "size" => 100),
	    "date"      => array("type" => "DATE"),
	    "comments"  => array("type" => "TEXT", "size" => 1000))),

    "test2" => array(
	"test1" => array(
	    "name"      => array("type" => "VARCHAR", "size" => 25),
	    "since"     => array("type" => "DATE"),
	    "subtitle"  => array("type" => "VARCHAR", "size" => 300),
	    "biography" => array("type" => "TEXT", "size" => 1000))));

/* la version php de free est obsolète et ne propose pas le json */
if (!defined('PHP_VERSION_ID')) {
    require_once "/home/leo/zeek/extends/json.php";

    $json = new Services_JSON();
    echo $json->encode($projects);
}
else
{
    echo json_encode($projects);
}
?>
