// once the document is ready
$(document).ready(function () {

    var $div_menus = $("div.menu");
    var $div_dynamic = $("div#dynamic");

    // we store the dynamic html
    var $html_dynamic = $("div#dynamic").html();

    // we show only home menu
    $div_menus.hide();
    $("div#home").show();

    // we configure edit menu
    var $nav_edit = $("nav#edit");
    var $div_edition = $("div#edition");

    $div_edition.hide();

    // we configure ace editor
    var $editor = ace.edit("editor");

    var $session = $editor.getSession();
    $editor.setTheme("ace/theme/twilight");
    $editor.setFontSize("16px");
    $editor.resize();
    $session.setTabSize(4);
    $session.setUseWrapMode(true);
    $session.setMode("ace/mode/html");

    var $actual_file = null;

    var $file_get = function ($user, $name) {
	$send_request(
	    {
		method: "file_get",
		user: $user,
		name: $name,
	    },
	    function ($result)
	    {
		// we initialise the actual file used
		$actual_file = {
		    "user": $user,
		    "name": $name,
		    "type": $result["type"],
		    "previous": $result["get"],
		};

		$editor.setValue($result["get"]);
		$session.setMode("ace/mode/" + $result["type"]);
		$div_edition.show();
	    });
    };

    var $file_set = function () {

	// the actual file should be defined
	if ($actual_file == null)
	    return;

	var $actual_data = $editor.getValue();

	// we set the file unless something has changed
	if ($actual_data === $actual_file["previous"])
	    return;

	$send_request(
	    {
		method: "file_set",
		user: $actual_file["user"],
		name: $actual_file["name"],
		data: $actual_data,
	    },
	    function()
	    {
		$actual_file["previous"] = $actual_data;
	    });
    }

    // we configure save command
    $editor.commands.addCommand({
	name: 'saveFile',
	bindKey: {
	    win: 'Ctrl-S',
	    mac: 'Command-S',
	    sender: 'editor|cli'
	},
	exec: function(env, args, request) {
	    // we update the content of the current file
	    $file_set();
	}
    });

    var $edit_update = function () {
 	$send_request(
	    {
		"method": "file_get_list",
	    },
	    function ($result) {

		if ($result == false)
		    return false;

		var $get_list = $result["get_list"];

		var $store_by_type = new Array();
		var $select_edit = $("select#edit");

		$get_list.forEach(function ($obj) {
		    var $type = $obj["type"];

		    // we create the type button
		    if ($nav_edit.children("button." + $type).length == 0)
		    {
			// create the button
			var $btn = $("<button>" + $type + "</button>");

			// add the class
			$btn.addClass("edit " + $type);

			// we generate the color associated to the button
			var $color =  "#" + int_to_ARGB(generate_hash_code($type) + 200);

			// add the css
			$btn.css({ "color": "#fff",
				   "background-color": $color,
				   "border-color": $color });

			// we set clickable edit button
			$btn.on("click", function () {
			    var $type = $(this).text();

			    $select_edit.empty();

			    // we display the file list
			    if ($store_by_type[$type].length > 0)
			    {
				$store_by_type[$type].forEach(function ($obj) {
				    var $user = $obj["user"];
				    var $name = $obj["name"];

				    var $option = $("<option>");

				    $option.text($user + " - " + $name);
				    $option.on('click', function () {
					$file_get($user, $name);
				    });

				    $select_edit.append($option);
				});
			    }
			    // otherwise no file has been found
			    else
			    {
				$store_by_type[$type].append(
				    $("<option>No file found!</option>"));
			    }
			});

			// add the button
			$nav_edit.prepend($btn);
		    }

		    // we store the files by type
		    if ($store_by_type[$type] == null)
			$store_by_type[$type] = new Array();

		    $store_by_type[$type].push($obj);
		});
	    });
	};

    // we set clickable action menu
    var $li_data;
    var $li_menu = $("li.menu");

    $li_menu.on("click", function () {
	var $this = $(this);
	var $id = $this.attr("id");

	if ($id == 'test')
	{
	    $send_request(
		{
		    method: "test",
		},
		function ($result) {
		    window.open($result['href'], '_blank');
		    return true;
		});

	    return;
	}

	if ($id == 'edit')
	{
	    $edit_update();
	}

	$li_data.removeClass("data_clicked");
	$li_menu.removeClass("menu_clicked");
	$this.addClass("menu_clicked");
	$div_menus.hide();
	$div_dynamic.empty();
	$div_dynamic.hide();
	$("div#" + $id).show();
    });

    var $div_modal = $("#modal");

    var $modal = $div_modal.dialog({
            autoOpen: false,
	    resizable: false,
	    modal: true,
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
	    var $input = "<input name=\"" + $attribute + "\"";

	    for (var $type in $options) {
		$input = $input.concat(
		    " " + $type + "=\"" + $options[$type]) + "\"";
	    }

	    $input = $input.concat("></input>");

	    $set.push({ name: $attribute, input: $input });
	}

	$div_dynamic.html(Mustache.render(
	    $html_dynamic, {
		name: $name,
		get_head: Object.keys($data)
	    }));

	$div_dynamic.show();

	var $result = Mustache.to_html(
	   "{{#set}}<p>{{name}}</p>{{{input}}}{{/set}}",
	   { set: $set });

	$div_modal.html($result);

	var $tbody_data_get = $("tbody#data_get");

	//we store the get html
	var $html_get = $("tbody").html();

	var $data_update;
	var $data_delete;

	var $update_get = function () {
	    $send_request(
		{
		    method: "data_get",
		    name: $name,
		    offset: 0,
		    size: 10,
		},
		function ($array) {
		    if ($array == false)
			return false;

		    var $get_body = new Array();

		    $array.forEach(function ($obj) {
			$id = $obj["id"];

			delete $obj["id"];

			$td = "";

			for (var $key in $obj) {
			    $td += "<td>" + $obj[$key] + "</td>";
			}


			$td += "<td><img item=\"" + $id + "\""
			     + " src=\"img/update.png\""
			     + " class=\"data_update\"></td>"
			     + "<td><img item=\"" + $id + "\""
			     + " src=\"img/delete.png\""
			     + " class=\"data_delete\"></td>";

			$get_body.push($td);
		    });

		    var $result = Mustache.to_html(
			"{{#get_body}}<tr class=\"modal\">{{{.}}}</tr>{{/get_body}}",
			{ get_body: $get_body });

		    $tbody_data_get.html($result);

		    $("img.data_update").on("click", $data_update);
		    $("img.data_delete").on("click", $data_delete);

		    return -1;
		});
	};

	$data_update = function () {
	    var $id = $(this).attr("item");

	    $modal.dialog({
		open: function () {
		    $(this).dialog("option", "title",
				   "Update " + $name);
		},
		buttons: {
		    "Update": function () {
			$send_request(
			    {
				method: "data_update",
				name: $name,
				id: $id,
				values: $div_modal.children().serialize(),
			    },
			    function ($result) {
				if ($result['success'])
				{
				    $update_get();
				    $modal.dialog("close");
				}
			    });
		    },
		}});

	    $modal.dialog("open");
	};


        $data_delete = function () {
	    $send_request(
		{
		    method: "data_delete",
		    name: $name,
		    id: $(this).attr("item"),
		},
		function ($result) {
		    $update_get();
		});
	};

	$update_get();


	$("h2#data_set").on("click", function () {
	    $modal.dialog({
		open: function () {
		    $(this).dialog("option", "title",
				   "Create new " + $name);
		},
		buttons: {
		    "Create": function () {
			$send_request(
			    {
				method: "data_set",
				type: $name,
				values: $div_modal.children().serialize(),
			    },
			    function ($result) {
				if ($result['success'])
				{
				    $update_get();
				    $modal.dialog("close");
				}
			    });
		    },
		}});

	    $modal.dialog("open");
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

		// we set clickable element
		$li_data.on("click", function () {
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
    $("button#disconnect").on("click", function () {
	$send_request(
	    {
		"method": "disconnect",
	    },
	    function ($result) {
		$(location).attr("href", "index.php");
		return true;
	    });
    });

    function generate_hash_code($str)
    {
	var $hash = 0;

	for (var i = 0; i < $str.length; i++)
	{
	    $hash = $str.charCodeAt(i) + (($hash << 5) - $hash);
	}

	return $hash;
    }

    function int_to_ARGB($i)
    {
	return (($i>>24)&0xFF).toString(16) +
            (($i>>16)&0xFF).toString(16) +
            (($i>>8)&0xFF).toString(16) +
            ($i&0xFF).toString(16);
    }
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
