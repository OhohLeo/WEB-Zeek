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
        $this->output($this->json_encode($result));
    }

/**
 * Echo errors in JSON format.
 *
 * @method error
 * @param string data to display
 */
    public function error($input) {
        $this->output($this->json_encode(array('error' => $input)));
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
        $this->output($this->json_encode(array('replace' => $input)));
    }

/**
 * Echo append content in JSON format.
 *
 * @method error
 * @param string data to append
 */
    public function append($input) {
        $this->output($this->json_encode(array('append' => $input)));
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
        if (!defined('PHP_VERSION_ID')) {
            require_once $this->global_path . "extends/json.php";

            $json = new Services_JSON();
            return $json->encode($input);
        }

        return json_encode($input);
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
        if (!defined('PHP_VERSION_ID')) {
            require_once $this->global_path . "extends/json.php";

            $json = new Services_JSON();
            return $json->decode($input);
        }

        return json_decode($input);
    }
}

?>