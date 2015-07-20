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
