var $div_menus = $("div.menu");
$div_menus.hide();
$("div#home").show();

var $editor = ace.edit("editor");
var $session = $editor.getSession();
$editor.setTheme("ace/theme/twilight");
$editor.setFontSize("16px");
$editor.resize();
$session.setTabSize(4);
$session.setUseWrapMode(true)
$session.setMode("ace/mode/html");

$("li.menu").on("click", function() {
    $div_menus.hide();
    $("div#dynamic").hide();
    $("div#" + $(this).attr("id")).show();
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


$("button.edit").on("click", function() {
    var $type = $(this).text();
    $session.setMode("ace/mode/" + $type);
    console.log($type);
});

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

var $title = $("h2").first();
var $data;

$.ajax({
    type: "POST",
    url: "input.php",
    data: {
        "method": "get_structure"
    },
    dataType: "html",
    success: function($input)
    {
        $("ul.sidebar").replaceWith($input);

        $("li.data").on("click", function($e) {
    	    $e.preventDefault();
    	    $danger.hide();
    	    $success.hide();
    	    $div_menus.hide();
    	    $("div#dynamic").show();
    	    $send_request({
                "method": "get_data",
                "type": $(this).data("type") });


        });
    },
    error: function($request, $status, $error)
    {
        $danger.text($status + " : " + $error);
        $danger.show();
    }
});

$("button#disconnect").on("click", $send_request(
    {
	"method": "disconnect",
    },
    function($result) {
	$(location).attr("href", "index.php");
    }
));
