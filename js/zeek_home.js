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

    var $select_edit = $("select#edit");

    var $last_selected = "";

    var $on_selected = function() {

        var $selection = $("select#edit option:selected");

        if ($selection.val() != $last_selected)
        {
	    $file_get($selection.attr("user"),
                      $selection.attr("name"));

            $last_selected = $selection.val();
        }
    };

    $select_edit.change($on_selected);

    var $last_edit_btn_type = "";

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
			var $letters = '0123456789ABCDEF'.split('');
			var $color = '#';
			for (var i = 0; i < 6; i++ ) {
			    $color += $letters[Math.floor(Math.random() * 16)];
			}

			// add the css
			$btn.css({ "color": "#fff",
				   "background-color": $color,
				   "border-color": $color });

			// we set clickable edit button
			$btn.on("click", function () {
			    var $type = $(this).text();

			    if ($last_edit_btn_type === $type)
				return;

			    $last_edit_btn_type = $type;

			    $select_edit.empty();

			    // we display the file list
			    if ($store_by_type[$type].length > 0)
			    {
				$store_by_type[$type].forEach(function ($obj) {
				    var $user = $obj["user"];
				    var $name = $obj["name"];

				    var $option = $("<option>");
                                    $option.attr("user", $user);
                                    $option.attr("name", $name);
				    $option.text($user + " - " + $name);

                                    $select_edit.append($option);

                                    $on_selected();
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

                // do not display anything
		return -1;
	    });
	};

    var $file_create_html;
    var $file_modify_html;

    var $table_type_accepted = $("table#file_type_accepted");

    $send_request(
	{
	    method: "file_get_type_list",
	},
	function ($result) {

	    if ($result == false)
		return false;

            var $select_type_proposed = $("select#file_type_proposed");

            var $on_type_accepted = function ($name) {

                // check if the type doesn't already exist
                if ($("tr#config_" + $name).length) {
                    return false;
                }

                // othewise add new type
                var $row = $("<tr>").attr("type", "config_" + $name);

                $row.append($("<td>").text($name))
                    .append(
                        $("<td>").append(
                            $("<img>").attr("src", "img/delete.png")
                                      .addClass("file_type_delete")
                                      .on("click", function () {
                                          $row.remove();
                                      })
                        ));

		$table_type_accepted.append($row);
            };

            // display & configure the type of file proposed
	    $result["type_list"].forEach(function ($type) {
		var $option = $("<option>");
		$option.text($type);

		$select_type_proposed.append($option);
	    });

            $select_type_proposed.change(function () {
                $on_type_accepted(
                    $("select#file_type_proposed option:selected").val());
            });

            // display & delete the type of file accepted
	    /* $result["type_accepted"].forEach(function ($type) {
	       }); */

            var $array =  [
		{ name: "Filename",
		  input: 'name="name" type="text"'},
		{ name: "Extension",
		  input: 'name="extension" type="text"'},
		{ name: "Is in main directory",
		  input: 'name="in_main_directory" type="checkbox"'},
	    ];

            var $generate_html = function ($array) {

                var $list = $('<select></select>');
                $list.attr("id", "select_type");
                $list.attr("name", "type");

                $table_type_accepted.children()
                                    .children()
                                    .each(function() {
	            $list.append($("<option>").text($(this).text()));
                });

                return "<table><tr><td><b>Type</b></td><td>"
	             + $("<div></div>").append($list).html() + "</td></tr>"
                     + Mustache.to_html("{{#foreach}}<tr><td><b>{{name}}</b></td>"
                     + "<td><input {{{input}}}/></td></tr>{{/foreach}}</table>",
		                        { foreach: $array })
                    + '<p id="final_create">Press enter to see the result!</p>'
            };


	    $file_modify_html = function() {
                return $generate_html($array);
            };

            $array.push({ name: "File",
		          input: 'id="file_upload" type="file" name="files[]" multiple'});

            $file_create_html = function() {
                return $generate_html($array)
                    + '<div id="progress_bar"></div>';
            };
	});


    var $generate_filename = function () {

	var $filename = {};

	$("#modal :input").each(function() {
	    var $name = $(this).attr("name");

	    var $value;
	    if ($name == "in_main_directory")
		$value = $(this).is(":checked");
	    else
		$value = $(this).val();

	    if ($name === "files[]" && $value.length > 0)
            {
                $filename["upload"] = $value;

                if ($filename["name"] === "")
		    $filename["name"] = $value;
            }

	    $filename[$name] = $value;
	});


        if ($filename["name"].length > 0)
        {
            var $name = $filename["name"];

            var $ext = /(?:\.([^.]+))?$/.exec($name)[1] || "";

            // we remove the extension from the name
            if ($filename["extension"].length == 0 && $ext.length > 0)
            {
	        $name = $name.substring(
                    0, $name.length - ($ext.length + 1));

                // we set the detected extension
                $filename["extension"] = $ext;
            }

            // we clean the file
            $filename["name"] = $name;
        }

        // the extension is not set
	if ($filename["extension"].length == 0)
	    $filename["extension"] = $filename["type"];
        // otherwise we check that the extension exists
        else
        {
            var $select_type_list = $("select#select_type");

            // we get all the options
            var $option_values = $.map($select_type_list.children(),
                                       function($option) {

                                           return $option.value;
                                       });

            var $index = $.inArray($filename["extension"], $option_values);

            // if the extension match
            if ($index > -1)
            {
                $filename["type"] = $filename["extension"];
                $select_type_list.children()
                                 .eq($index)
                                 .attr("selected", "selected");
            }
            else
                $filename["extension"] = $filename["type"];
        }

	var $result;

	if ($filename["in_main_directory"])
	    $result = $filename["name"] + "." + $filename["type"];
	else
	    $result = $filename["type"] + "/" + $filename["name"]
		    + "." +  $filename["extension"];

	$("p#final_create").text($result);

        return $filename;
    }

    $("button#file_create").on("click", function () {

	// we check that the type is here
	if ($file_create_html == null) {
	    $danger.text("Error: type list not found!").show();
	    $alert.show();
	    return;
	}

	$div_modal.html($file_create_html());

        var $filename = {};

        $div_modal.change(function () {
            $filename = $generate_filename();
        });

	var $file_upload = $("input#file_upload");
	var $need_to_upload = false;

	$modal.dialog({
	    minWidth: 400,
	    open: function () {
		$need_to_upload = false;

		$(this).dialog("option", "title", "Create new file");

		var $progress_bar = $("div#progress_bar").hide();

		$file_upload.fileupload({
		    replaceFileInput: false,
		    url: 'upload.php',
		    dataType: 'json',
		    add: function ($e, $data) {
			$need_to_upload = true;

			$("button#file_create_ok").on("click", function () {
			    $data.submit();
			});
		    },
		    progressall: function ($e, $data) {
			$progress_bar.progressbar({
			    value: parseInt($data.loaded / $data.total * 100, 10)
			});
		    },
		    error: function ($e, $data) {
                        $danger.text("Upload Error: " + $e).show();
	                $alert.show();
		    },
		    done: function ($e, $data) {
		        $send_request($filename, function ($result) {
				 $last_edit_btn_type = "";
				 $select_edit.empty();

				 $modal.dialog("close");
				 $edit_update();
			 });
		    },
		   });
	    },
	    buttons: {
		"Create": {
		    text: "Create",
		    id: "file_create_ok",
		    click: function () {

                        $filename["method"] = "file_create";

			if ($need_to_upload)
			    return;

                        $send_request($filename, function ($result) {
			    $last_edit_btn_type = "";
			    $select_edit.empty();

			    $modal.dialog("close");
			    $edit_update();
			});
                    }
		}
	    }});

	$modal.dialog("open");
    });

    $("button#file_modify").on("click", function () {

        // the actual file should be defined
	if ($actual_file == null)
	    return;

	// we check that the type is here
	if ($file_modify_html == null) {
	    $danger.text("Error: type list not found!").show();
	    $alert.show();
	    return;
	}

	$div_modal.html($file_modify_html());

        var $filename = {};

        $div_modal.change(function () {
            $filename = $generate_filename();
        });

        $modal.dialog({
	    minWidth: 400,
	    open: function () {
		$(this).dialog("option", "title", "Modify this file");
            },
            buttons: {
		"Modify": {
		    text: "Modify",
		    click: function () {

                        $filename["method"] = "file_modify";
                        $filename["src"] = $actual_file["name"];

                        $send_request($filename, function ($result) {
			    $last_edit_btn_type = "";
			    $select_edit.empty();

			    $modal.dialog("close");
			    $edit_update();
			});
                    }
		}
	    }});

        $modal.dialog("open");
    });


    $("button#file_delete").on("click", function () {

	// the actual file should be defined
	if ($actual_file == null)
	    return;

	var $filename = $actual_file["name"];

	$div_modal.html("<p>Do you still want to delete <b>" + $filename + "</b> ?</p>");

	$modal.dialog({
	    open: function () {
		$(this).dialog("option", "title", "Confirm");
	    },
	    buttons: {
		"Delete": function () {
		    $send_request(
			{ "method": "file_delete", "name": $filename },
			function ($result) {
			    $div_edition.hide();

			    $last_edit_btn_type = "";
			    $select_edit.empty();

			    $modal.dialog("close");
			    $edit_update();
			});
		}
	    }
	});

	$modal.dialog("open");
    });

    $("button#file_export").on("click", function () {
	console.log("export!");
    });

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
	$div_dynamic.empty().hide();
	$("div#" + $id).show();
    });

    var $div_modal = $("#modal");

    var $modal = $div_modal.dialog({
        minWidth: 500,
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

    var $ul_structure = $("ul#structure");

    // we store the initial structure
    var $html_structure = $("ul#structure").html();

    // we get the database structure
    $structure_get = function () {

        // we initialise the structure
        $ul_structure.html($html_structure);

        $send_request(
	    {
	        "method": "structure_get"
	    },
	    function ($result)
	    {
	        // we store the structure
	        $structure = $result["structure"];

                console.log($structure);

	        if ($structure)
	        {
		    $ul_structure.html(Mustache.render(
		        $html_structure,
		        {
			    structure: Object.keys($structure)
		        }));

                    $("li#structure_loading").remove();

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

                $("li#structure_loading").text('Error!');
	    }
        );
    };

    $structure_get();

    var $div_users = $("div#users_list");

    // we set the user configuration
    var $users_get_list = function() {
        $send_request(
            {
                "method": "users_get_list",
            },
            function ($users) {
                $div_users.html(
                    Mustache.to_html(
                        '<table>{{#users}}<tr><td>{{{.}}}</td><td><img email="{{{.}}}" src="img/delete.png" class="user_delete"></td></tr>{{/users}}</table>', $users));

                $("img.user_delete").on("click", function() {
                    $send_request(
                        {
                            "method": "user_delete",
                            "email": $(this).attr("email"),
                        },
                        function ($result) {
                            $users_get_list();
                        });
                });

                return -1;
            });
    };

    $("form#user_add").on("submit", function($e) {
        $e.preventDefault();

        $send_request(
            {
                "method": "user_add",
                "params": $(this).serialize(),
            },
            function ($result) {
                $users_get_list();
            });
    });

    $("form#user_change_password").on("submit", function($e) {
        $e.preventDefault();

        $send_request({
            "method": "user_change_password",
            "params": $(this).serialize(),
        });
    });

    // we enable/disable the structure configuration
    var $button_structure_set = $("button#structure_set");

    var $reset_structure_set = function() {
        $button_structure_set.text("MODIFY");
        $('body').css('background', ' #FFF');
        $structure_get();
    };


    var $select_type_list;
    var $structure_get_list = function () {
        $send_request(
	    {
	        method: "structure_get_list",
                expert_mode: $("input#expert_mode").prop('checked'),
	    },
	    function ($result) {

	        if ($result == false)
		    return false;

	        $select_type_list = $('<select></select>');
	        $select_type_list.attr("id", "select_type");
	        $select_type_list.attr("name", "type");

                $result["list"].forEach(function ($type) {
		    var $option = $("<option>");
		    $option.text($type);

		    $select_type_list.append($option);
	        });
            });
    }

    $("input#expert_mode").on("click", function() {
        $structure_get_list();
    });

    $button_structure_set.on("click", function() {
        var $this = $(this);

        if ($this.text() == "MODIFY")
        {
            if ($select_type_list == null) {
                $structure_get_list();
            }

            var $new_structure = $structure;

            $this.text("VALIDATE");
            $('body').css('background', ' #DDD');

            $("ul#structure").append(
                '<li class="data">CREATE</li>');

            $("li.data")
                 .off("click")
                 .on("click", function() {
                     var $name = $(this).text();

                     $modal.dialog({
		         open: function () {
		             $(this).dialog(
                                 "option", "title",
                                 (($name === "CREATE") ? "New" :
                                  "Modify '" + $name + "'") + " structure");

	                     var $data = $new_structure[$name];

                             var $setup = "<table id=\"attribute_add\">";

                             // we handle new structure
                             if ($name === "CREATE") {
                                 $setup +=
                                 "<tr><td><b>Structure name:</b></td>"
                               + "<td><input name=\"structure_name\" type=\"text\"/></td></tr>";
                             }

                             var $delete_attribute = $("<td>").append(
                                 $("<img>").attr("src", "img/delete.png")
                                           .attr("class", "attribute_delete")).html();

                             // we display the attributes already existing
                             for (var $attribute in $data)
	                     {
	                         var $options = $data[$attribute];
                                 $setup += "<tr name=\"" + $attribute + "\">"
                                         + "<td><b>" + $attribute + "</b></td>"
                                         + "<td>" + $options['db_type'] + "</td>"
                                         + "<td>" + $delete_attribute
                                         + "</td><td></td></tr>";
	                     }

                             // we add new attributes
                             var $attribute_add =
                             "<tr class=\"attribute_add\"><td><b>Name</b></td>"
                           + "<td><input name=\"name\" type=\"text\"/></input></td>"
                           + "</td>" + $delete_attribute + "</td></tr>"
                           + "<tr><td><b>Type</b></td><td>"
                           + $("<div></div>").append($select_type_list).html()
                           + "<td></td></tr>";

                             // if we are in expert mode : we ask for the type
                             if ($("input#expert_mode").prop('checked'))
                             {
                                 $attribute_add +=
                                   "<tr><td><b>Size</b></td>"
                                 + "<td><input name=\"name\" type=\"text\"/></input></td>"
                                 + "</td></td></tr>";
                             }

                             $setup += $attribute_add + "</table>"
                                     + "<button id=\"attribute_add\"> + </button>";

                             $div_modal.html($setup);

                             var $attribute_delete = function() {
                                 $("img.attribute_delete").on("click", function () {
                                     var $parent_row = $(this).parents("tr");

                                     if ($parent_row.hasClass("attribute_add")) {
                                         $parent_row.next().remove();
                                     } else {
                                         // TODO: � supprimer!!
                                         console.log($parent_row.attr("name"));
                                         $new_structure[$name][$parent_row.attr("name")] = "";
                                     }

                                     $parent_row.remove();
                                 });
                             };

                             $("button#attribute_add").on("click", function () {
                                 $("table#attribute_add").append($attribute_add);
                                 $attribute_delete();
                             });

                             $attribute_delete();
		         },
		         buttons: {
                             "Cancel": function() {
                                 $modal.dialog("close");
                             },
                             "Remove ALL": function() {
                                 // TODO � supprimer
                                 $new_structure[$name] = "";
                                 $modal.dialog("close");
                             },
		             "Validate": function () {
                                 console.log($("tr.attribute_add").children("input"));
                                 console.log($new_structure);
		             },
		         }});

	             $modal.dialog("open");

                 });
            return;
        }
        else
        {
            $reset_structure_set();
        }

        $reset_structure_set();

    });


    // we set the configuration visual effects
    $div = $("div.config-group");
    $div.hide();

    $("div.config h3").on("click", function($e) {
        $e.preventDefault();

        var $this = $(this);

        if ($this.attr('id') === "user") {
            $users_get_list();
        }

        $boxed = $this.next("div.config-group");
        if ($boxed.is(":hidden")) {
	    $boxed.slideDown(200);
        } else {
	    $boxed.slideUp(200);
        }
    });

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
});

/*

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
