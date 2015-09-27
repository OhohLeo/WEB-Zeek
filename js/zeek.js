$alert = $("div.alert");
$alert.hide();

$danger = $("div.error");
$success = $("div.success");

$clean_alert = function() {
    $success.hide();
    $danger.hide();
    $alert.hide();
}

$generic_rsp = function($result) {

    $success.hide();
    $danger.hide();
    $alert.hide();
    console.log($result);
    if ($result["success"]) {
	$success.text($result["success"]).show();
	$alert.show();
    } else if ($result["error"]) {
	console.log($result["error"]);
	$danger.text($result["error"]).show();
	$alert.show();
    } else if ($result["redirect"]) {
	$(location).attr("href", $result["redirect"]);
    } else {
	console.log($result);
    }
};

$send_request = (function($data, $handle_rsp) {

    console.debug($data);

    $.ajax({
    	"type": "POST",
    	"url": "input.php",
    	"data": $data,
    	"dataType":"json",
    	"success": function($result) {
    	    if ($handle_rsp) {
		var $res = $handle_rsp($result);

		if ($res == -1)
		    return;

		if ($res == true) {
		    $alert.hide();
		    return;
		}
    	    }

	    $generic_rsp($result);
    	},
    	"error": function($request, $status, $error) {
	    $generic_rsp({error: $status + ": " + $error});
    	},
    });
});


$input_validator = function($name_with_type, $validator, $on_success, $send_parameters) {

    var $name = $name_with_type.substring(1);
    var $button_name = "button#" + $name;
    var $start_value = $("input" + $name_with_type).val();

    if ($send_parameters == null)
        $send_parameters = { "method": $name };

    console.log($send_parameters);

    return function() {

        $("input" + $name_with_type).on("input", function () {

            if ($($button_name).length == 0)
            {
                $(this).after(
                    $("<button>").attr("id", $name)
                                 .attr("class", "validate")
                                 .text("OK")
                                 .on("click", function() {

                                     $value = $("input" + $name_with_type).val();

                                     if ($validator
                                         && ($validator($value) == false))
                                         return false;

                                     $send_parameters["value"] = $value;

                                     $send_request(
                                         $send_parameters,
                                         function($result) {
	                                     if ($result == false || $result["error"])
		                                 return false;

                                             $($button_name).remove();
                                             $on_success($value);

                                             $start_value = $value;
                                         });
                                 }));
            }
            else if ($(this).val() == "" || $(this).val() == $start_value)
            {
                $($button_name).remove();
            }
        });
    };
};

$text_validator = function($input, $size_max) {

    $clean_alert();

    if ($input.match(/^[a-z_0-9]+$/i) && $input.length <= $size_max) {
       return true;
    }

    $danger.text("Invalid input '" + $input
                 + "': only a-z, A-Z, 0-9 and _ and "
                 + $size_max + " letter(s) accepted").show();
    $alert.show();

    return false;
};

$no_space_validator = function($input) {

    $clean_alert();

    if ($input.match(/\s+/)) {
        $danger.text("Invalid input '" + $input
                   + "': no space accepted").show();
        $alert.show();
        return false;
    }

    return true;
};
