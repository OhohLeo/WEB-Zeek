$danger = $("div.error");
$danger.hide();

$success = $("div.success");
$success.hide();

$alert = $("div.alert");

$generic_rsp = function($result) {
    if ($result["success"]) {
	$danger.hide();
	$success.text($result["success"]).show();
    } else if ($result["error"]) {
	$success.hide();
	$danger.text($result["error"]).show();
    } else if ($result["redirect"]) {
	$(location).attr("href", $result["redirect"]);
    } else {
	console.log($result);
	$alert.fadeOut(300);
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
    		$alert.fadeOut(300);
		return;
    	    }

	    $generic_rsp($result);
    	},
    	"error": function($request, $status, $error) {
	    $generic_rsp({error: $status + ": " + $error});
    	},
    });
});
