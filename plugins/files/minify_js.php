<?php

require_once("plugins/files/extends/JShrink/src/JShrink/Minifier.php");

/**
 * MinifyJs : handle.
 *
 * @package MinifyJs
 */
class MinifyJs {

 /**
 * Accept only javascript files
 *
 * @method accept_files
 */
    public function accept_files()
    {
        return array('js', 'javascript');
    }

 /**
  * Method that shrink javascript code.
  *
  * @method on_input
  * @param string input to shrink
  *
  * @return string shrink output
  */
    public function on_input($input)
    {
        return \JShrink\Minifier::minify($input);
    }
}
?>
