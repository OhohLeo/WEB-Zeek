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
    public function success($input, $params) {
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
 * @param hash data to add
 */
    public function error($input) {
        echo $this->json_encode(array('error' => $input));
    }

/**
 * Encode data in JSON format.
 *
 * @method json_encode
 * @param hash data to encode
 */
    protected function json_encode($input)
    {
        /* la version php de free est obsol�te et ne propose pas le json */
        if (!defined('PHP_VERSION_ID')) {
            require_once $this->global_path . "extends/json.php";

            $json = new Services_JSON();
            return $json->encode($input);
        }

        return json_encode($input);
    }
}

?>