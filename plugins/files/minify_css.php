<?php

require_once("plugins/files/extends/cssmin/CssMin.php");

/**
 * MinifyCss : handle.
 *
 * @package Plugin
 */
class MinifyCss {

 /**
 * Accept only css files
 *
 * @method accept_files
 */
    public function accept_files()
    {
        return array('css');
    }

 /**
  * Method that shrink css code.
  *
  * @method on_input
  * @param string input to shrink
  *
  * @return string shrink output
  */
    public function on_input($input)
    {
        return CssMin::minify($input);
    }
}
?>
