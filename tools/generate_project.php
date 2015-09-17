#
# Copyright (C) 2015  Léo Martin
#
# This program is free software; you can redistribute it and/or modify
# it under the terms of the GNU General Public License as published by
# the Free Software Foundation; either version 2 of the License, or
# (at your option) any later version.
#
# This program is distributed in the hope that it will be useful,
# but WITHOUT ANY WARRANTY; without even the implied warranty of
# MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
# GNU General Public License for more details.
#
# You should have received a copy of the GNU General Public License
# along with this program; if not, write to the Free Software
# Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
#

<?php
$projects = array(
    "test" => array(
	"artist" => array(
	    "name"      => array("type" => "VARCHAR"
				 "size" => 100),
	    "surname"   => array("type" => "VARCHAR",
				 "size" => 100),
	    "age"       => array("type" => "INT_U"),
	    "subtitle"  => array("type" => "VARCHAR",
				 "size" => 300),
	    "biography" => array("type" => "TEXT",
				 "size" => 1000),
	    "skill"     => array("type" => "VARCHAR",
				 "size" => 100)),
	"show"   => array(
	    "name"      => array("type" => "VARCHAR",
				 "size" => 100),
	    "date"      => array("type" => "DATE"),
	    "hour"      => array("type" => "TIME"),
	    "location"  => array("type" => "VARCHAR",
				 "size" => 300)),
	"news"   => array(
	    "name"      => array("type" => "VARCHAR",
				 "size" => 100),
	    "date"      => array("type" => "DATE"),
	    "comments"  => array("type" => "VARCHAR",
				 "size" => 100)),
	"album"  => array(
	    "name"      => array("type" => "VARCHAR",
				 "size" => 100),
	    "duration"  => array("type" => "INT_U"),
	    "comments"  => array("type" => "TEXT",
				 "size" => 1000)),
	"music"  => array(
	    "name"      => array("type" => "VARCHAR",
				 "size" => 100),
	    "date"      => array("type" => "DATE"),
	    "duration"  => array("type" => "INT_U"),
	    "comments"  => array("type" => "TEXT",
				 "size" => 1000)),
	"video"  => array(
	    "name"      => array("type" => "VARCHAR",
				 "size" => 100),
	    "date"      => array("type" => "DATE"),
	    "duration"  => array("type" => "INT_U"),
	    "comments"  => array("type" => "TEXT",
				 "size" => 1000)),
	"media"  => array(
	    "name"      => array("type" => "VARCHAR",
				 "size" => 100),
	    "date"      => array("type" => "DATE"),
	    "comments"  => array("type" => "TEXT",
				 "size" => 1000))),

    "test2" => array(
	"test1" => array(
	    "name"      => array("type" => "VARCHAR",
				 "size" => 25),
	    "since"     => array("type" => "DATE"),
	    "subtitle"  => array("type" => "VARCHAR",
				 "size" => 300),
	    "biography" => array("type" => "TEXT",
				 "size" => 1000))));

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
