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

/**
 * ZeekOutput : all the function to output data.
 *
 * @package ZeekOutput
 */
class ZeekOutput {

/**
 * Echo all data to send to the client side.
 *
 * @method output
 * @param string data to display
 */
    public function output($input) {
        echo $input;
    }

/**
 * Echo all data to send to the client side.
 *
 * @method output_json
 * @param structured data to display into json
 */
    public function output_json($input) {
       $this->output($this->json_encode($input));
        return true;
    }

/**
 * Echo success in JSON format.
 *
 * @method success
 * @param string data to display
 * @param hash data to add
 */
    public function success($input, $params = NULL) {
        $result = array('success' => $input);

        if ($params != NULL) {
            foreach ($params as $key => $value) {
                $result[$key] = $value;
            }
        }

        /* we display it */
        $this->output_json($result);
    }

/**
 * Echo errors in JSON format.
 *
 * @method error
 * @param string data to display
 */
    public function error($input) {
        $this->output_json(array('error' => $input));
    }

/**
 * Echo errors.
 *
 * @method debug
 * @param string data to display
 */
    public function debug($input) {
        /* print "DEBUG: $input\n"; */
    }


/**
 * Echo replace content in JSON format.
 *
 * @method error
 * @param string data to replace
 */
    public function replace($input) {
        $this->output_json(array('replace' => $input));
    }

/**
 * Echo append content in JSON format.
 *
 * @method error
 * @param string data to append
 */
    public function append($input) {
        $this->output_json(array('append' => $input));
    }

/**
 * Encode data in JSON format.
 *
 * @method json_encode
 * @param hash data to encode
 */
    protected function json_encode($input)
    {
        /* la version php de free est obsolète et ne propose pas le json */
        if (!function_exists('json_encode'))
        {
            require_once $this->global_path . "extends/json.php";
            $json = new Services_JSON;
            return $json->encode($input);
        }


        return json_encode($input, JSON_UNESCAPED_SLASHES);
    }

/**
 * Decode data in JSON format.
 *
 * @method json_decode
 * @param hash data to decode
 */
    protected function json_decode($input)
    {
        /* la version php de free est obsolète et ne propose pas le json */
        if (!function_exists('json_decode'))
        {
            require_once $this->global_path . "extends/json.php";

            $json = new Services_JSON(SERVICES_JSON_LOOSE_TYPE);

            return $json->decode(stripslashes($input));
        }

        return json_decode($input, true);
    }


/**
 * Convert object structure to array structure.
 *
 * @method object_to_array
 * @param array structure
 */
    protected function object_to_array($obj)
    {
	if (is_object($obj))
	    $obj = (array) $obj;

	if (is_array($obj))
	{
            $new = array();
            foreach($obj as $key => $val)
	    {
		$new[$key] = $this->object_to_array($val);
            }
	}
	else
	{
	    $new = $obj;
	}

	return $new;
    }
}

?>
