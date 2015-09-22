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


$input_validator = function($name_with_type, $on_success) {

    var $name = $name_with_type.substring(1);
    var $button_name = "button#" + $name;
    var $start_value = $("input" + $name_with_type).val();

    return function() {

        $("input" + $name_with_type).on("input", function () {

            if ($($button_name).length == 0)
            {
                $(this).after(
                    $("<button>").attr("id", $name)
                                 .attr("class", "validate")
                                 .text("OK")
                                 .on("click", function() {

                                     $value = $("input" + $name_with_type).val()

                                     $send_request(
                                         {
                                             "method": $name,
                                             "value": $value,
                                         },
                                         function($result) {
	                                     if ($result == false || $result["error"])
		                                 return false;

                                             $($button_name).remove();
                                             $on_success($value);

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

    if ($input.match(/^[a-z_0-9]+$/i) && $input.length <= $size_max) {
        $clean_alert();
       return true;
    }

    $danger.text("Invalid input: '" + $input
                 + "', only a-z, A-Z, 0-9 & _ and "
                 + $size_max + " letter(s) accepted").show();
    $alert.show();

    return false;
};

