var $danger = $('div.alert-danger');
$danger.hide();

var $success = $('div.alert-success');
$success.hide();

var $div_menus = $('div.menu');
$div_menus.hide();
$('div#home').show();

$('li.menu').on('click', function() {
    $div_menus.hide();
    $('div#dynamic').hide();
    $('div#' + $(this).attr('id')).show();
});

$div = $("div.boxed-group");
$div.hide();

$("h3").on("click", function($e) {
    $e.preventDefault();

    $boxed = $(this).next("div.boxed-group");
    if ($boxed.is(":hidden")) {
	$boxed.slideDown(200);
    } else {
	$boxed.slideUp(200);
    }
});

$(document).ready(function() {
    $("form#user_add").on("submit", function() {
        $send_request({
            "method": "user_add",
            "params": $(this).serialize(),
        });
    });

    $("form#user_change_password").on("submit", function() {
        $send_request({
            "method": "user_change_password",
            "params": $(this).serialize(),
        });
    });

    $("button#data_clean").on("click", function($e) {
        e.preventDefault();
        $send_request({
            "method": "data_clean",
        });
    });

    $("button#project_delete").on("click", function($e) {
        e.preventDefault();
        $send_request({
            "method": "project_delete_to_confirm",
        });
    });
});

var $send_request = (function($data, $next_action) {
    $.ajax({
        "type": "POST",
        "url": "input.php",
        "data": $data,
        "dataType":"json",
        "success": function($result) {
            if ($result["success"]) {
                $danger.hide();
                $success.text($result["success"]).show();
                return true;
            }
            if ($result["error"]) {
                $("div.modal").modal("hide");
                $success.hide();
                $danger.text($result["error"]).show();
                return true;
            }

            if ($result['replace']) {
                $('div#dynamic').replaceWith($result['replace']);
                return true;
            }

            if ($result['append']) {
                $('div#dynamic').append($result['append']);
                return true;
            }

            $('div.modal').modal("hide");
            $success.hide();
            $danger.hide();

            if ($next_action) {
                $next_action($result);
            } else {
                $danger.text("unhandled result!").show();
            }
        },
        "error": function($request, $status, $error) {
            $danger.text($status + ' : ' + $error);
            $danger.show();
        },
    });
});

$(document).ready(function() {
    var $title = $('h2').first();
    var $clickable;

    $.ajax({
	type: 'POST',
	url: "input.php",
	data: {
            'method': 'get_structure'
	},
	dataType: "html",
	success: function($input)
	{
            $('ul.nav-sidebar').replaceWith($input);

            $('a.clickable').on('click', function($e) {
		$e.preventDefault();
		$danger.hide();
		$success.hide();
		$div_menus.hide();
		$('div#dynamic').show();

		var $type = $(this).data('type');

		if ($type == 'Home') {
                    $title.text('Welcome to Zeek!');
		} else if ($type != 'Disconnect') {
                    $title.text($type);
		}

		$send_request({
                    "method": 'clicked',
                    "type": $type });
            });
	},
	error: function($request, $status, $error)
	{
            $danger.text($status + ' : ' + $error);
            $danger.show();
	}
    });
}());
