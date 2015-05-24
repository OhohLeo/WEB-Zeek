// once the document is ready
$(document).ready(function() {

    var $div_connect = $("div#connect");
    var $div_create = $("div#create");
    $div_create.hide();

    var $project_name_src = $("input#connect");
    var $project_name_dst = $("input#create");

    $("button#connect").on("click", function(e) {
	$danger.hide();
	$success.hide();
	$div_create.hide();

	$send_request(
	    {
		method: "connect",
		params: $div_connect.children("input").serialize(),
	    },
	    function ($result) {
		if ($result["action"] == "project_create") {
		    $div_connect.hide();
		    $project_name_dst.val($project_name_src.val());
		    $div_create.show();
		}
	    });

	e.preventDefault();
    });

    $("button#create").on("click", function(e) {
	$send_request(
	    {
		method: "project_create",
		project_name: $project_name_dst.val(),
	    });
    });

    $("button#cancel").on("click", function(e) {
	$div_create.hide();
	$div_connect.show();
    });
});
