$handle_rsp = function($result) {
    if ($result['success']) {
	$danger.hide();
	$success.text($result['success']).show();
    } else if ($result['error']) {
	$success.hide();
	$danger.text($result['error']).show();
    } else if ($result['redirect']) {
	$(location).attr('href', $result['redirect']);
    } else {
	console.log($result);
	$('div.alert').fadeOut(300);
    }
};

$danger = $('div.error');
$danger.hide();

$success = $('div.success');
$success.hide();

$div_connect = $('div#connect');

$div_create = $('div#create');
$div_create.hide();

$('button#connect').on('click', function(e) {
    $danger.hide();
    $success.hide();
    $div_create.hide();

    $.ajax({
	type: "POST",
	url: "input.php",
	dataType: "json",
	data: {
	    "method": "connect",
	    "params": $div_connect.children('input').serialize(),
	},
	success: function($result) {

	    if ($result['action'] == 'project_create') {
		$div_connect.hide();
		$div_create.show();
	    }

	    $handle_rsp($result);
	},
	error: function($request, $status, $error) {
	    $handle_rsp({error: $status + ": " + $error});
	},
    });

    e.preventDefault();
});

$('button#create').on('click', function(e) {
    $.ajax({
	type: "POST",
	url: "input.php",
	dataType: "json",
	data: {
	    "method": "project_create",
	    "project_name": $("input#create").val(),
	},
	success: function($result) {
	    $handle_rsp($result);
	},
	error: function($request, $status, $error) {
	    $handle_rsp({error: $status + ": " + $error});
	},
    });
});

$('button#cancel').on('click', function(e) {
    $div_create.hide();
    $div_connect.show();
});
