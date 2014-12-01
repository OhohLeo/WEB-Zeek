$danger = $("div.error");
$danger.hide();

$success = $("div.success");
$success.hide();

$handle_rsp = function($result) {
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
	$("div.alert").fadeOut(300);
    }
};

$send_request = (function($data, $handle_rsp) {
    $.ajax({
    	"type": "POST",
    	"url": "input.php",
    	"data": $data,
    	"dataType":"json",
    	"success": function($result) {
    	    if ($handle_rsp) {
    		$handle_rsp($result);
    	    }

	    $handle_rsp($result);
    	},
    	"error": function($request, $status, $error) {
	    $handle_rsp({error: $status + ": " + $error});
    	},
    });
});
