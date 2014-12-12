// once the document is ready
$(document).ready(function() {

    var $div_menus = $("div.menu");
    var $div_dynamic = $("div#dynamic");

    // we store the dynamic html
    var $html_dynamic = $("div#dynamic").html();

    // we show only home menu
    $div_menus.hide();
    $("div#home").show();

    // we set clickable action menu
    var $li_data;
    var $li_menu = $("li.menu");
    $li_menu.on("click", function() {
	var $this = $(this);
	$li_data.removeClass("data_clicked");
	$li_menu.removeClass("menu_clicked");
	$this.addClass("menu_clicked");
	$div_menus.hide();
	$div_dynamic.empty();
	$div_dynamic.hide();
	$("div#" + $this.attr("id")).show();
    });

    // we get the position to set the structure
    $ul_structure = $("ul.structure");

    // we store the structure
    var $structure;

    // we handle the data process
    var $handle_data = function ($name)
    {
	$div_menus.hide();
	var $data = $structure[$name];

	var $set = new Array();
	for (var $attribute in $data)
	{
	    var $options = $data[$attribute];

	    // we get all options for the css input
	    var $input = "<input name=" + $attribute;

	    for (var $type in $options) {
		$input = $input.concat(
		    " " + $type + "=" + $options[$type]);
	    }

	    $input = $input.concat("></input>");

	    console.log($input);

	    $set.push({ name: $attribute,
			     input: $input });
	}

	$div_dynamic.html(Mustache.render(
	    $html_dynamic, {
		name: $name,
		set: $set,
		get_head: Object.keys($data)
	    }));

	$div_dynamic.show();

	var $div_data_set = $("div#data_set");
	$div_data_set.hide();

	$("h2#data_set").on("click", function() {
	    $div_data_set.toggle();
	});

	$("button#data_set").on("click", function() {

	    $send_request(
		{
		    method: "data_set",
		    type: $name,
		    values: $div_data_set.children().serialize(),
		},
		function ($result) {
		    console.log($result);
		});
	});
    };

    // we get the database structure
    $send_request(
	{
	    "method": "structure_get"
	},
	function ($result)
	{
	    var $li_loading = $("li#structure_loading");

	    // we store the structure
	    $structure = $result["structure"];

	    if ($structure)
	    {
		var $ul_structure = $("ul.structure");

		$li_loading.remove();

		$ul_structure.html(Mustache.render(
		    $ul_structure.html(),
		    {
			structure: Object.keys($structure)
		    }));

		$li_data = $("li.data");

		$li_data.show();

		// we set clikable element
		$li_data.on("click", function() {
		    var $this = $(this);

		    $li_menu.removeClass("menu_clicked");
		    $li_data.removeClass("data_clicked");
		    $this.addClass("data_clicked");

		    $handle_data($this.text());
		});

		return true;
	    }

	    $li_loading.text('Error!');
	}
    );

    // we configure the disconnect button
    $("button#disconnect").on("click", function() {
	$send_request(
	    {
		"method": "disconnect",
	    },
	    function($result) {
		$(location).attr("href", "index.php");
		return true;
	    });
    });

    // we configure ace editor
    var $editor = ace.edit("editor");
    var $session = $editor.getSession();
    $editor.setTheme("ace/theme/twilight");
    $editor.setFontSize("16px");
    $editor.resize();
    $session.setTabSize(4);
    $session.setUseWrapMode(true);
    $session.setMode("ace/mode/html");

    // we set clickable edit button
    $("button.edit").on("click", function() {
	var $type = $(this).text();
	$session.setMode("ace/mode/" + $type);
	console.log($type);
    });
});



/* $div = $("div.boxed-group");
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
   var $data; */
