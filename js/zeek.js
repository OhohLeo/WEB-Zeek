$danger = $("div.error");
$danger.hide();

$success = $("div.success");
$success.hide();

$generic_rsp = function($result) {
    $success.hide();
    $danger.hide();

    if ($result["success"]) {
	$success.text($result["success"]).show();
    } else if ($result["error"]) {
	console.log($result["error"]);
	$danger.text($result["error"]).show();
    } else if ($result["redirect"]) {
	$(location).attr("href", $result["redirect"]);
    } else {
	console.log($result);
    }
};

$send_request = (function($data, $handle_rsp) {
    $.ajax({
    	"type": "POST",
    	"url": "input.php",
    	"data": $data,
    	"dataType":"json",
    	"success": function($result) {
    	    if ($handle_rsp && $handle_rsp($result)) {
		$success.hide();
		$danger.hide();
		return;
    	    }

	    $generic_rsp($result);
    	},
    	"error": function($request, $status, $error) {
	    $generic_rsp({error: $status + ": " + $error});
    	},
    });
});
