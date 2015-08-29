// once the document is ready
$(document).ready(function() {

    var $div_menus = $("div.menu");

    var $div_structure = $("div#structure");
    var $div_dynamic = $("div#dynamic");

    var $structure_enable = $("input#structure_enabled");

    // we store the dynamic html
    var $html_dynamic = $("div#dynamic").html();

    // we show only home menu
    $div_menus.hide();
    $("div#home").show();

    // we configure contents menu
    var $content_get_type_list;
    var $content_types = {};
    var $div_content_directory = $("div#content_directory");

    $div_content_directory.hide();

    var $button_contents_create = $("button#contents_create");
    var $table_content_type = $("table#content_type_accepted");
    var $on_new_content_type;

    // we store the current list of types
    var $contents_set_type = function($name, $directory, $mime, $color) {

        $send_request(
	    {
	        method: "contents_set_type",
                name: $name,
                directory: $directory,
                mime: $mime,
                options: $color,
	    },
	    function($result) {

	        if ($result == false || $result["error"])
		    return false;

                $content_get_type_list();
            });
    };

    var $contents_modify_type = function($name, $color) {

        $send_request(
	    {
	        method: "contents_modify_type",
                name: $name,
                options: $color,
	    },
	    function($result) {

	        if ($result == false || $result["error"])
		    return false;

                $content_get_type_list();

                // we refresh the contents directory color
                $("button.content_directory")
                         .filter("." + $name)
                         .css({ "color": "#fff",
				"background-color": $color });
            });
    };

    // we store the current list of types
    var $contents_unset_type = function($name, $row) {

        $send_request(
	    {
	        method: "contents_unset_type",
                name: $name,
	    },
	    function($result) {

	        if ($result == false || $result["error"])
		    return false;

                $row.remove();
                $content_get_type_list();
            });
    };

    $("form#content_type_add").on("submit", function($e) {
        $e.preventDefault();

        var $options = {};

        $(this).children("p")
               .children("input")
               .each(function() {
                   $options[$(this).attr("name")] = $(this).val()
               });

        if ($options["content_name"] === ""
            || $options["content_directory"] === "")
            return false;

        if ($options["content_mime"] === "")
            $options["content_mime"] = "*/*";

        $contents_set_type(
            $options["content_name"],
            $options["content_directory"],
            $options["content_mime"],
            "#F0F8FF")
    });

    $on_new_content_type = function($name, $directory, $mime, $color) {

        // check if the content type with same name doesn't already exist
        if ($content_types[$name]) {
            return false;
        }

        // otherwise add new content type
        $content_types[$name] = [ $directory, $mime, $color ];

        var $row = $("<tr>").attr("class", "config_contents");
        $row.append($("<td class=\"content_name\">").text($name))
            .append($("<td class=\"content_directory\">").text($directory))
            .append($("<td class=\"content_mime\">").text($mime))
            .append($("<td>").append(
                "<input id=\"content_color_" + $name +"\" type=\"text\">"))
            .append(
                $("<td>").append(
                    $("<img>").attr("src", "img/delete.png")
                              .addClass("file_type_delete")
                              .on("click", function() {
                                  $contents_unset_type($name, $row);
                              })
                ));

	$table_content_type.append($row);

        $("input#content_color_" + $name).change(function() {
            $contents_modify_type($name, $(this).spectrum("get")
                                                .toHexString());
        })                               .spectrum({
                                             color: $color
                                         });

    };

    $content_get_type_list = function() {
        $send_request(
	    {
	        method: "contents_get_type_list",
	    },
	    function($result) {

	        if ($result == false || ("content_types" in $result) == false)
		    return false;

                $result = $result["content_types"];

	        for ($name in $result) {

                    var $array = $result[$name];

                    if ($array.length != 3)
                        continue;

                    $on_new_content_type(
                        $name, $array[0], $array[1], $array[2]);
                }

                return -1;
            });
    };

    var $contents_list = {};

    var $contents_update = function() {

 	$send_request(
	    {
		"method": "contents_get_list",
	    },
	    function($result) {

                if ($result == false || !("get_list" in $result))
		    return false;

                var $get_list = $result["get_list"];

		for (var $directory in $get_list) {
                    $list = $get_list[$directory];

                    $contents_handle_directory($list["infos"]);
                    delete $list["infos"];

                    $contents_list[$directory] = $list;

                    if ($actual_content_directory != null) {
                        $actual_content_directory.click();
                    }
                };

                return -1;
            });
    };

    // Disable auto discover for all elements:
    Dropzone.autoDiscover = false;

    var $dropzone = new Dropzone("form#dropzone", {
        url: "upload.php",
        maxFilesize: 3, // MB
        acceptedFiles: 'image/*',
        ignoreHiddenFiles: true,
        autoProcessQueue: true,
        createImageThumbnails: true,
        maxThumbnailFilesize: 10,
        thumbnailWidth: 100,
        thumbnailHeight: 100,
        clickable: true,
        autoQueue: true,
        addRemoveLinks: false,
        paramName: "files[]",
        parallelUploads: 1,
        dictDefaultMessage: "Drop file here or click to upload.",
        dictFallbackMessage: "Your browser does not support drag'n'drop file uploads.",
        dictFallbackText: "Please use the fallback form below to upload your files like in the olden days.",
        dictFileTooBig: "File is too big ({{filesize}}MiB). Max filesize: {{maxFilesize}}MiB.",
        dictInvalidFileType: "You can't upload files of this type.",
        dictResponseError: "Server responded with {{statusCode}} code.",
        dictCancelUpload: "Cancel upload",
        dictCancelUploadConfirmation: "Are you sure you want to cancel this upload?",
        dictRemoveFile: "Remove file",
        dictRemoveFileConfirmation: null,
        dictMaxFilesExceeded: "You can not upload any more files.",
        init: function () {
            this.on("complete", function ($file) {

                // when all uploads are over
                if (this.getUploadingFiles().length === 0
                    && this.getQueuedFiles().length === 0) {

                        if ($actual_content_directory == null) {
                            return false;
                        }

                        $send_request({
		            "method": "content_add",
                            "directory": $actual_content_directory.text(),
                            "name": $file["name"],
	                }, function ($result) {

                            if ($result == false) {
                                return false;
                            }

                            $contents_update();
                        });
                }
            });
        },
    });

    $dropzone.on("complete", function($file) {
        $dropzone.removeFile($file);
    });

    var $dropzone_empty = function () {
        $dropzone.removeAllFiles()
    };

    var $actual_content_directory;

    var $tbody_contents_list = $("tbody#contents_list");

    var $contents_handle_directory = function($input) {

        console.log($input);

        var $type = $input["type"];
        var $name = $type + "-" + $input["name"];
        var $dst = $input["dst"];

        // we check if the name already exists
        if ($("button#contents_" + $name).length > 0) {
            return false;
        }

        // we create the directory button
        var $btn = $("<button>")
            .attr("id", "contents_" + $name)
            .addClass("content_directory")
            .addClass($type)
            .text($dst)
            .on("click", function () {

                // we display the selected border
                $("button.content_directory").removeClass("select");
                $(this).addClass("select");

                // we empty the dropzone
                $dropzone_empty();

                $actual_content_directory = $(this);

                $tbody_contents_list.empty();

                // we get all images stored in the directory
                var $list = $contents_list[$dst];

                for (var $idx in $list) {

                    var $content = $list[$idx];
                    var $fullpath = $content["path"]
                                  + "/" + $content["filename"]
                                  + "." + $content["extension"];

                    var $row = $("<tr>").attr("class", "content")
                                        .attr("id", "content-" + $idx)
                                        .attr("name", $content["filename"]
                                                    + "." + $content["extension"]);

                    var $value = $("<img>").attr("src", $fullpath)
                                           .on("click", function() {
                                               $div_modal.html(
                                                   $("<img>").attr("src", $fullpath));
                                               $modal.dialog({
		                                   open: function() {
		                                       $(this).dialog("option", "title",
				                                      $fullpath);
                                                   }});
                                               $modal.dialog("open");
                                           });

                    $row.append($("<td>").attr("class", "content_filename")
                                         .append($("<input>").val($content["filename"])))
                        .append($("<td>").attr("class", "content_extension")
                                         .text($content["extension"]))
                        .append($("<td>").attr("class", "content_size")
                                         .text($content["size"]))
                        .append($("<td>").attr("class", "content_value")
                                         .append($value))
                        .append($("<td>").append(
                            $("<img>").attr("src", "img/delete.png")
                                      .addClass("content_delete")
                                      .on("click", function() {

                                          var $row = $(this).parent().parent();

                                          console.log($row);

                                          $send_request({
		                              "method": "content_delete",
                                              "directory": $actual_content_directory.text(),
                                              "name": $row.attr("name"),
	                                  }, function ($result) {
                                              if ($result == false) {
                                                  return false;
                                              }

                                              $row.remove();
                                          });
                                      })));


                    $tbody_contents_list.append($row);
                }

                $div_content_directory.show();
            });

        if ($content_types[$type] != undefined) {

            // we get the color associated to the button
	    var $color = $content_types[$type][2];

            // add the css
	    $btn.css({ "color": "#fff",
		       "background-color": $color });

            /* if ($.isPlainObject($input)) {
               for (var $key in $input) {
               $content_directory.attr($key, $input[$key]);
               };
               } */
        }

        $button_contents_create.before($btn);
        return true;
    }

    $button_contents_create.on("click", function () {

        $dropzone_empty();

        var $selected_content_type;
        var $list = $('<select></select>');
        $list.attr("id", "content_types");
        $list.attr("name", "type");

        var $generate_name = function() {
            var $name = $("input#content_name").val();

            if ($name != "") {
                $name = "/" + $name
            }

            var $dst = $content_types[$selected_content_type][0] + $name;

            $("td#final_content").text($dst);

            return $dst;
        };

        var $on_selected = function() {
            var $selection = $("select#content_types option:selected");

            $selected_content_type = $selection.val();

            var $tr_contents = $("tr.contents");
            $tr_contents.filter(".opt").hide();
            $tr_contents.filter("." + $selected_content_type).show();

            $generate_name();
        };

        for ($name in $content_types) {
            $list.append($("<option>").text($name));
        }

        var $array =  [
	    { name: "Name",
	      input: 'id="content_name" name="name" type="text" placeholder="optional"',
              class: "contents"},
	    { name: "Max Size",
	      input: 'name="max_size" type="integer" placeholder="optional"',
              class: "contents"},
            { name: "Image Type",
	      input: 'name="img_type" type="text" placeholder="optional"',
              class: 'contents images opt'},
            { name: "Image Height",
	      input: 'class="contents opt images" name="img_height" type="number" step="1" placeholder="optional"',
              class: "contents images opt"},
	    { name: "Image Width",
	      input: 'class="contents opt images" name="img_width" type="number" step="1" placeholder="optional"',
              class: 'contents images opt'},
        ];

        $div_modal.html(Mustache.to_html(
            "<table><tr><td><b>Type</b></td><td>"
          + $("<div></div>").append($list).html() + "</td></tr>"
          + "{{#foreach}}<tr class=\"{{class}}\"><td><b>{{name}}</b></td>"
          + "<td><input {{{input}}}/></td></tr>{{/foreach}}",
	    { foreach: $array })
          + "<tr><td><b>Destination</b></td>"
          + '<td id="final_content">Press enter to see the result!</td></tr></table>');

        $("select#content_types").change($on_selected);
        $on_selected();

        $("input#content_name").change($generate_name);

        $modal.dialog({
	    minWidth: 400,
	    open: function() {
		$(this).dialog("option", "title",
                               "Create new directory");
            },
	    buttons: {
		"Create": {
		    text: "Create",
		    click: function() {

                        $on_selected();

                        var $contents_opt = {};

	                $("#modal :input").each(function() {
	                    var $name = $(this).attr("name");
	                    var $value = $(this).val();

	                    if ($value) {
	                        $contents_opt[$name] = $value;
                            }
	                });

                        if ($.isEmptyObject($contents_opt)) {
                            return;
                        }

                        var $directory = $generate_name();

                        $contents_opt["dst"] = $directory;

                        // TODO: validate options
                        if ($contents_opt["img_type"]
                         || $contents_opt["img_height"]
                         || $contents_opt["img_width"]) {
                        }

                        // we send the command to store the new directory
                        $send_request({
		            "method": "content_add_directory",
                            "directory": $directory,
                            "options": JSON.stringify($contents_opt),
	                }, function ($result) {
                            if ($result == false) {
                                return false;
                            }

                            $contents_handle_directory($contents_opt);

                            $modal.dialog("close");
                        });
                    }
		}
	    }});

	$modal.dialog("open");
    });

    $("button#content_directory_remove").on("click", function() {

        if ($actual_content_directory == null) {
            return false;
        }

        $directory = $actual_content_directory.text();

	$div_modal.html("<p>Do you still want to delete <b>"
                      + $directory + "</b> ?</p>");

	$modal.dialog({
	    open: function() {
		$(this).dialog("option", "title", "Confirm");
	    },
	    buttons: {
		"Delete": function() {

                    $send_request({
                        "method": "content_remove_directory",
                        "directory": $directory
                    }, function ($result) {
                        if ($result == false) {
                            return false;
                        }

                        // Hide directory elements
                        $div_content_directory.hide();

                        // Remove all actual buttons
                        $("button.content_directory").remove();

                        // Get actual content list
                        $contents_update();

                        $modal.dialog("close");
                    });
                }
            }
        });

        $modal.dialog("open");
    });

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

    var $file_get = function($name) {
	$send_request(
	    {
		method: "file_get",
		name: $name,
	    },
	    function($result)
	    {
		// we initialise the actual file used
		$actual_file = {
		    "name": $name,
		    "type": $result["type"],
		    "previous": $result["get"],
		};

		$editor.setValue($result["get"]);
		$session.setMode("ace/mode/" + $result["type"]);
		$div_edition.show();

	    });
    };

    var $file_set = function() {

	// the actual file should be defined
	if ($actual_file == null)
	    return;

	var $actual_data = $editor.getValue();

	// we set the file unless something has changed
	if ($actual_data === $actual_file["previous"])
        {
            $clean_alert();
	    return;
        }

	$send_request(
	    {
		method: "file_set",
		name: $actual_file["name"],
		data: $actual_data,
	    },
	    function()
	    {
		$actual_file["previous"] = $actual_data;
	    });
    }

    // we configure save button
    $("button#file_save").on("click", $file_set);

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
	    $file_get($selection.attr("name"));

            $last_selected = $selection.val();
        }
    };

    $select_edit.change($on_selected);

    var $file_create_on_click;
    var $last_edit_btn_type = "";

    var $edit_update = function($type, $name) {
 	$send_request(
	    {
		"method": "file_get_list",
	    },
	    function($result) {
		if ($result == false || !("get_list" in $result))
		    return false;

		var $get_list = $result["get_list"];

		var $store_by_type = new Array();

                $nav_edit.empty();

		$get_list.forEach(function($obj) {

		    var $type = $obj["type"];

		    // we create the type button
		    if ($nav_edit.children("button." + $type).length == 0)
		    {
			// create the button
			var $btn = $("<button>" + $type + "</button>");

			// add the class
			$btn.addClass("edit " + $type);

			// we get the color associated to the button
			var $color = $files_type[$type];

			// add the css
			$btn.css({ "color": "#fff",
				   "background-color": $color });

			// we set clickable edit button
			$btn.on("click", function() {
			    var $type = $(this).text();

			    if ($last_edit_btn_type === $type)
				return;

                            // we handle select border
                            $("button.edit").removeClass("select");
                            $(this).addClass("select");

			    $last_edit_btn_type = $type;

			    $select_edit.empty();

			    // we display the file list
			    if ($store_by_type[$type].length > 0)
			    {
				$store_by_type[$type].forEach(function($obj) {
				    var $name      = $obj["name"];

				    var $option = $("<option>");
                                    $option.attr("name", $name);
				    $option.text($name);

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

                // we click on the type file button
                if ($type != null)
                    $("button." + $type).click();

                // we select the file created or updated
                if ($name != null)
                    $select_edit.click(function() {
                        $(this).children().filter(function() {
                            return $(this).attr("name") === $name
                        }).click();
                    });

                var $button_file_create = $("<button>")
                         .attr("id", "file_create")
                         .attr("class", "validate")
                         .text("Create")
                         .on("click", $file_create_on_click);

                $nav_edit.append($button_file_create);

                // do not display anything
		return -1;
	    });
	};

    var $file_create_html;
    var $file_modify_html;
    var $file_get_type_list;
    var $files_type = {};

    var $table_type_accepted = $("table#file_type_accepted");

    var $option_set_editor;

    var $on_new_editor_type = function($name, $color) {

        // check if the type doesn't already exist
        if ($files_type[$name]) {
            return false;
        }

        // othewise add new type
        $files_type[$name] = $color;

        var $row = $("<tr>").attr("id", "config_" + $name);
        $row.append($("<td class=\"editor_type\">").text($name))
            .append($("<td>").append(
                "<input id=\"color_" + $name +"\" type=\"text\">"))
            .append(
                $("<td>").append(
                    $("<img>").attr("src", "img/delete.png")
                              .addClass("file_type_delete")
                              .on("click", function() {
                                  $row.remove();
                              })
                ));

	$table_type_accepted.append($row);

        $("input#color_" + $name).change($option_set_editor)
                                 .spectrum({
            color: $color
        });
    };

    // we handle editor options
    var $option_get_editor = function () {
        $send_request({
	    method: "option_get_editor",
	},
	function($result) {

	    if ($result == false || $result["error"])
		return false;

            for (var $name in $result)
            {
                $on_new_editor_type($name, $result[$name]);
            }

            // do not display anything
	    return -1;
        });
    }

    $option_set_editor = function () {

        $files_type = {};

        $("td.editor_type").each(function() {
            var $name = $(this).text();

            $files_type[$name] = $("input#color_" + $name).spectrum("get")
                                                          .toHexString();
        });

        $send_request({
	    method: "option_set",
            name: "editor",
            options: JSON.stringify($files_type),
	},
        function($result) {

	    if ($result == false || $result["error"])
		return false;

            $option_get_editor();

            // do not display anything
	    return -1;
        });
    }

    $option_get_editor();

    $file_get_type_list = function ()
    {
        $send_request(
	    {
	        method: "file_get_type_list",
	    },
	    function($result) {

	        if ($result == false
                            || ("type_list" in $result) == false)
		    return false;

                var $select_type_proposed = $("select#file_type_proposed");

                // display & configure the type of file proposed
	        $result["type_list"].forEach(function($type) {
		    var $option = $("<option>");
		    $option.text($type);

		    $select_type_proposed.append($option);
	        });

                $select_type_proposed.change(function() {
                    $on_new_editor_type(
                        $("select#file_type_proposed option:selected").val(),
                        "#f00");

                    $option_set_editor();
                });

                // display & delete the type of file accepted
	        /* $result["type_accepted"].forEach(function($type) {
	           }); */

                var $array =  [
		{ name: "Filename",
		  input: 'name="name" type="text"'},
		{ name: "Extension",
		  input: 'name="extension" type="text"'},
		    { name: "Is in main directory",
		      input: 'name="in_main_directory" type="checkbox"'},
	        ];

                var $generate_html = function($array) {

                    var $list = $('<select></select>');
                    $list.attr("id", "select_type");
                    $list.attr("name", "type");

                    $("td.editor_type").each(function() {
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
    };

    var $generate_filename = function() {

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
	if ($filename["extension"] || $filename["extension"].length == 0)
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

    $file_create_on_click = function() {

	// we check that the type is here
	if ($file_create_html == null) {
	    $danger.text("Error: type list not found!").show();
	    $alert.show();
	    return;
	}

	$div_modal.html($file_create_html());

        var $filename = {};

        $div_modal.change(function() {
            $filename = $generate_filename();
        });

	var $file_upload = $("input#file_upload");
	var $need_to_upload = false;

	$modal.dialog({
	    minWidth: 400,
	    open: function() {
		$need_to_upload = false;

		$(this).dialog("option", "title", "Create new file");

		var $progress_bar = $("div#progress_bar").hide();

		$file_upload.fileupload({
		    replaceFileInput: false,
		    url: 'upload.php',
		    dataType: 'json',
		    add: function($e, $data) {
			$need_to_upload = true;

			$("button#file_create_ok").on("click", function() {
			    $data.submit();
			});
		    },
		    progressall: function($e, $data) {
			$progress_bar.progressbar({
			    value: parseInt($data.loaded / $data.total * 100, 10)
			});
		    },
		    error: function($e, $data) {
                        $danger.text("Upload Error: " + $e).show();
	                $alert.show();
		    },
		    done: function($e, $data) {
		        /* $send_request($filename, function($result) {
			   $last_edit_btn_type = "";

			   $modal.dialog("close");
			   $edit_update();
			   }); */
		    },
		   });
	    },
	    buttons: {
		"Create": {
		    text: "Create",
		    id: "file_create_ok",
		    click: function() {

                        $filename["method"] = "file_create";

			if ($need_to_upload)
			    return;

                        $send_request($filename, function($result) {
			    $last_edit_btn_type = "";

                            console.log();

			    $modal.dialog("close");
			    $edit_update($filename["extension"],
                                         $filename["user"],
                                         $filename["name"]);
			});
                    }
		}
	    }});

	$modal.dialog("open");
    };

    $("button#file_modify").on("click", function() {

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

        $div_modal.change(function() {
            $filename = $generate_filename();
        });

        $modal.dialog({
	    minWidth: 400,
	    open: function() {
		$(this).dialog("option", "title", "Modify this file");
            },
            buttons: {
		"Modify": {
		    text: "Modify",
		    click: function() {

                        $filename["method"] = "file_modify";
                        $filename["src"] = $actual_file["name"];

                        $send_request($filename, function($result) {
			    $last_edit_btn_type = "";
			    $select_edit.empty();

			    $modal.dialog("close");
			    $edit_update($filename["extension"],
                                         $filename["name"]);
			});
                    }
		}
	    }});

        $modal.dialog("open");
    });

    // the file exportation is only from client side
    $("button#file_export").on("click", function() {

        // the actual file should be defined
        if ($actual_file == null)
            return;

        // we get the current state of the editor
        var $actual_data = $editor.getValue();

        $(this).children()
               .attr('href', 'data:text/'
                     + $actual_file["type"]
                     + ';charset=utf-8,'
                     + encodeURIComponent($actual_data))
               .attr('download', $actual_file["name"])
               .click();
       });


    $("button#file_delete").on("click", function() {

	// the actual file should be defined
	if ($actual_file == null)
	    return;

	var $filename = $actual_file["name"];

	$div_modal.html("<p>Do you still want to delete <b>" + $filename + "</b> ?</p>");

	$modal.dialog({
	    open: function() {
		$(this).dialog("option", "title", "Confirm");
	    },
	    buttons: {
		"Delete": function() {
		    $send_request(
			{ "method": "file_delete", "name": $filename },
			function($result) {
			    $div_edition.hide();

			    $last_edit_btn_type = "";
			    $select_edit.empty();

			    $modal.dialog("close");
			    $edit_update($actual_file["type"]);
			});
		}
	    }
	});

	$modal.dialog("open");
    });


    // we set clickable action menu
    var $li_data;
    var $li_menu = $("li.menu");

    $li_menu.on("click", function() {

        // remove displayed alerts
        $clean_alert();

	var $this = $(this);
	var $id = $this.attr("id");

	if ($id == 'test')
	{
            // we get all options choosed from the configuratio
            var $options = {};

            $("input.option_test").each(function() {
                $options[$(this).attr("name")] = $(this).prop("checked")
            });

	    $send_request(
		{
		    method: "test",
                    options: JSON.stringify($options),
		},
		function($result) {
                    if ($result['href'])
                    {
		        window.open($result['href'], '_blank');
		        return true;
                    }
		});

	    return;
	}

	if ($id == 'edit')
	{
            if ($file_create_html == null
                || $file_modify_html == null)
                $file_get_type_list();

	    $edit_update();
	}

        if ($id == 'contents')
        {
            if (Object.keys($content_types) == 0)
                $content_get_type_list();

            $contents_update();
        }

        if ($id == 'configuration')
        {
            if ($file_create_html == null
                || $file_modify_html == null)
                $file_get_type_list();

            if (Object.keys($content_types) == 0)
                $content_get_type_list();
        }

        if ($structure_enable.prop("checked"))
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
    var $structure = {};

    // we handle the data process
    var $handle_data = function($name)
    {
	$div_menus.hide();
	var $data = $structure[$name];

        if ($data == null)
            return false;

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

	var $update_get = function() {
	    $send_request(
		{
		    method: "data_get",
		    name: $name,
		    offset: 0,
		    size: 10,
		},
		function($array) {
		    if ($array == false)
			return false;

		    var $get_body = new Array();

		    $array.forEach(function($obj) {
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

	$data_update = function() {
	    var $id = $(this).attr("item");

            // we set all actual values in <input>
            var $content = $(this).parents("tr").children();

            $div_modal.children("input").each(function() {
                $(this).val($content.eq(0).text());
                $content = $content.next();
            });

	    $modal.dialog({
		open: function() {
		    $(this).dialog("option", "title",
				   "Update " + $name);
		},
		buttons: {
		    "Update": function() {
			$send_request(
			    {
				method: "data_update",
				name: $name,
				id: $id,
				values: $div_modal.children().serialize(),
			    },
			    function($result) {
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


        $data_delete = function() {
	    $send_request(
		{
		    method: "data_delete",
		    name: $name,
		    id: $(this).attr("item"),
		},
		function($result) {
		    $update_get();
		});
	};

	$update_get();


	$("h2#data_set").on("click", function() {
	    $modal.dialog({
		open: function() {
		    $(this).dialog("option", "title",
				   "Create new " + $name);
		},
		buttons: {
		    "Create": function() {
			$send_request(
			    {
				method: "data_set",
				type: $name,
				values: $div_modal.children().serialize(),
			    },
			    function($result) {
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

    // we configure to set project name
    $input_validator(
        "#project_set_name",
        function($new_value) {
            $("h1#title").text($new_value);
        })();

    // we configure to set project url
    $input_validator(
        "#project_set_url",
        function() {
        })();

    // we configure to set project destination
    $input_validator(
        "#project_set_dst",
        function() {
        })();

    // we enable/disable the structure configuration
    var $button_structure_modify = $("button#structure_modify");

    var $on_structure_modif = function() {
        $div_menus.hide();
        $div_structure.show();
    };

    var $append_create_structure = function() {

        $("ul#structure").append(
            '<li id="structure_create" class="data">CREATE</li>');

        $("li.data")
             .off("click")
             .on("click", function() {
                 $structure_config($(this).text());
                 $on_structure_modif();
             });
    };

    $("button#structure_validate").on("click", function() {

        var $json_structure = safeJSONStringify($structure);

        $send_request(
	    {
	        "method": "structure_set",
                "structure": $json_structure,
	    },
	    function($result)
	    {
	        if ($result == false || $result["error"])
		    return false;

                $structure_get();
	    }
        );
    });

    $("button#structure_cancel").on("click", function() {
        $structure_get();
    });

    var $ul_structure = $("ul#structure");

    // we store the initial structure
    var $html_structure = $("ul#structure").html();

    var $structure_config = function($name) {

        var $is_new_structure = ($name === "CREATE");
        var $input_structure_name = $("input#structure_name");

        var $buttons = {
            "Cancel": function() {
                $modal.dialog("close");
            },
	    "Validate": function() {
                var $structure_name = $is_new_structure ?
                    $("input#structure_name").val() : $name;

                if ($structure_name == "") {
                    return;
                }

                // we set the last attribute if it is defined
                $("button#new_attribute").trigger("click");

                $structure[$structure_name] = {};

                $.each($("tr.attribute"), function($idx, $tr) {
                    var $name = $($tr).children("td.name").text();
                    var $sp_type = $($tr).children("td.sp_type").text();
                    var $db_type = $($tr).children("td.db_type").text();
                    var $db_size = $($tr).children("td.db_size").text();

                    $structure[$structure_name][$name] =
                           { "sp_type": $sp_type, "db_type": $db_type, "db_size": $db_size };
                });

                $structure_set($structure);
                $append_create_structure();
                $modal.dialog("close");
	    },
	};

        if ($is_new_structure == false)
        {
            $buttons["Remove"] = function() {
                delete $structure[$name];
                $structure_set($structure);
                $append_create_structure();
                $modal.dialog("close");
            };
        }

        $modal.dialog({
	    open: function() {
		$(this).dialog(
                    "option", "title",
                    ($is_new_structure ? "New" :
                     "Modify '" + $name + "'") + " structure");


                var $is_expert_mode = $("input#expert_mode").prop('checked');

	        var $data = $structure[$name];

                var $setup = "<table id=\"new_attribute\">";

                // we handle new structure
                if ($name === "CREATE") {
                    $setup +=
                    "<tr><td><b>Structure name:</b></td>"
                  + '<td><input id="structure_name" type="text"/></td></tr>';
                }

                var $delete_attribute = $("<td>").append(
                    $("<img>").attr("src", "img/delete.png")
                              .attr("class", "attribute_delete")).html();

                var $handle_delete_attribute = function() {
                    $("img.attribute_delete").on("click", function() {
                        $(this).parents("tr").remove();
                    });
                };

                var $display_attribute = function($attribute, $type, $size)
                {
                    if ($is_expert_mode)
                    {
                        $content = "<td class=\"db_type\">" + $type + "</td>"
                                 + ($size != null && $size.length != 0)
                            ? "<td class=\"db_size\">" + $size + "</td>" : "";
                    }
                    else
                    {
                        $content = "<td class=\"sp_type\">" + $type + "</td>";
                    }

                    return "<tr class=\"attribute\">"
                         + "<td class=\"name\"><b>" + $attribute + "</b></td>"
                         + $content
                         + "<td>" + $delete_attribute + "</td><td></td></tr>";
                };

                if ($data != null)
                {
                    // we display the attributes already existing
                    for (var $attribute in $data)
	            {
                        var $type = "db_type";

                        if ($is_expert_mode == false
                            && $data[$attribute]["sp_type"] != null)
                        {
                            $type = "sp_type";
                        }

                        $setup += $display_attribute(
                            $attribute,
                            $data[$attribute][$type],
                            $data[$attribute]["db_size"]);
	            }
                }

                // we add new attributes
                var $new_attribute =
                "<tr class=\"add_attribute\"><td><b>Name</b></td><td>"
              + "<input id=\"new_attribute\" name=\"name\" type=\"text\"/>"
              + "</input></td></tr>"
              + "<tr class=\"add_attribute\"><td><b>Type</b></td><td>"
              + $("<div></div>").append($select_type_list).html()
                    + "</td></tr>";

                // if we are in expert mode : we ask for the type
                if ($is_expert_mode)
                {
                    $new_attribute +=
                    "<tr class=\"add_attribute\"><td><b>Size</b></td>"
                  + "<td><input id=\"attribute_size\" name=\"name\""
                  + "type=\"number\"/></input></td>"
                  + "</td></td></tr>";
                }

                $setup += $new_attribute + "</table>"
                        + "<button id=\"new_attribute\"> + </button>";

                $div_modal.html($setup);

                $handle_delete_attribute();

                $("button#new_attribute").on("click", function() {
                    var $name = $("input#new_attribute").val();
                    var $type = $("select#select_type option:selected").val();
                    var $size = $("input#attribute_size").val()

                    // we check if the attribute name is empty or already exists
                    if ($name == "" || $name.indexOf(" ") >= 0) {
                        return;
                    }

                    if ($size != null
                        && ($size.length == 0 || $.isNumeric($size) == false)) {
                            return;
                    }

                    $("tr.add_attribute").remove();

                    $("table#new_attribute").append(
                        $display_attribute($name, $type, $size)
                            + $new_attribute);

                    $handle_delete_attribute();
                });
	    },
	    buttons: $buttons
        });

	$modal.dialog("open");
    }

    var $structure_set = function($structure) {
        $ul_structure.html(Mustache.render(
	    $html_structure,
	    {
		structure: Object.keys($structure)
	    }));

        $("li#structure_loading").remove();

        // if the structure is empty : we activate the configuration mode
        if (Object.keys($structure).length == 0)
        {
            $button_structure_modify.trigger("click");
        }

	$li_data = $("li.data");

	$li_data.show();

	// we set clickable element
	$li_data.on("click", function() {
	    var $this = $(this);

            if ($("li#structure_create").length)
            {
                $structure_config($this.text());
                return;
            }

	    $li_menu.removeClass("menu_clicked");
	    $li_data.removeClass("data_clicked");
	    $this.addClass("data_clicked");

	    $handle_data($this.text());
	});
    }

    // we get the database structure
    var $structure_get = function() {

        // we initialise the structure
        $ul_structure.html($html_structure);

        $send_request(
	    {
	        "method": "structure_get"
	    },
	    function($result)
	    {
	        if ($result == false || $result["error"])
		    return false;

                $('body').css('background', ' #FFF');

	        // we store the structure
	        $structure = $result["structure"];

	        if ($structure)
	        {
                    $structure_set($structure);
		    return -1;
	        }

                $("li#structure_loading").text('Error!');
                return -1;
	    }
        );
    };

    var $div_users = $("div#users_list");

    // we set the user configuration
    var $users_get_list = function() {
        $send_request(
            {
                "method": "users_get_list",
            },
            function($users) {
                $div_users.html(
                    Mustache.to_html(
                        '<table>{{#users}}<tr><td>{{{.}}}</td>'
                      + '<td><img email="{{{.}}}" src="img/delete.png"'
                      + 'class="user_delete"></td>'
                      + '</tr>{{/users}}</table>', $users));

                $("img.user_delete").on("click", function() {
                    $send_request(
                        {
                            "method": "user_delete",
                            "email": $(this).attr("email"),
                        },
                        function($result) {
                            $users_get_list();
                        });
                });

                return -1;
            });
    };

    // we handle new user
    $("form#user_add").on("submit", function($e) {
        $e.preventDefault();

        $send_request(
            {
                "method": "user_add",
                "params": $(this).serialize(),
            },
            function($result) {
                $users_get_list();
            });
    });

    // we handle user password
    $("form#user_change_password").on("submit", function($e) {
        $e.preventDefault();

        $send_request({
            "method": "user_change_password",
            "params": $(this).serialize(),
        });
    });

    // we handle structure list
    var $select_type_list;
    var $structure_get_list = function() {
        $send_request(
	    {
	        method: "structure_get_list",
                expert_mode: $("input#expert_mode").prop('checked'),
	    },
	    function($result) {

	        if ($result == false || $result["error"])
		    return false;

	        $select_type_list = $('<select></select>');
	        $select_type_list.attr("id", "select_type");
	        $select_type_list.attr("name", "type");

                $result["list"].forEach(function($type) {
		    var $option = $("<option>");
		    $option.text($type);

		    $select_type_list.append($option);
	        });
            });
    }

    $("input#expert_mode").on("click", function() {
        $structure_get_list();
    });

    $button_structure_modify.on("click", function() {
        var $this = $(this);

        if ($select_type_list == null) {
            $structure_get_list();
        }

        $on_structure_modif();

        if ($("li#structure_create").length)
            return;

        $('body').css('background', '#DDD');

        $append_create_structure();
    });

    var $table_options_deploy = $("table#options_deploy");
    var $table_options_test   = $("table#options_test");

    var $option_get_plugins;

    var $option_set_plugins = function () {

        var $options = {};

        $("input.option_deploy").each(function() {
            $options[$(this).attr("name")] = $(this).prop("checked")
        });

        $send_request({
	    method: "option_set",
            name: "plugins",
            options: JSON.stringify($options),
	},
        function($result) {

	    if ($result == false || $result["error"])
		return false;

            $option_get_plugins();

            // do not display result
            return -1;
        });
    };


    var $option_get_row = function ($name, $type) {

        var $row = $("<tr>").attr("id", "config_" + $name);

        $row.append($("<td>").text($name))
            .append($("<td>").append(
                "<input id=\"" + $type + "_" + $name + "\" name=\"" + $name
              + "\" class=\"option_" + $type + "\" type=\"checkbox\">"));

        return $row;
    }

    // we handle deploy options
    $option_get_plugins = function () {
        $send_request({
	    method: "option_get_plugins",
	},
	function($result) {

	    if ($result == false || $result["error"])
		return false;

            $table_options_deploy.empty();
            $table_options_test.empty();

            for (var $name in $result)
            {
                // special case concerning "zeekify" that can be disabled
                if ($name === "zeekify")
                {
                    var $div_structure_enabled = $("div#structure_enabled");
                    var $nav_data = $("nav#data");

                    if ($result[$name] === "disabled")
                    {
                        $structure_enable.prop('checked', false);
                        $('body').css('background', ' #FFF');
                        $div_structure_enabled.hide();
                        $nav_data.hide();
                        continue;
                    }

                    $structure_get();

                    $structure_enable.prop('checked', true);
                    $div_structure_enabled.show();
                    $nav_data.show();
                }

                $table_options_deploy.append($option_get_row($name, "deploy"));

                $("input#deploy_" + $name).prop('checked', $result[$name])
                                          .change($option_set_plugins);

                $table_options_test.append($option_get_row($name, "test"));

                $("input#test_" + $name).prop('checked', $result[$name]);
            }

            // do not display anything
	    return -1;
        });
    }

    $option_get_plugins();


    $structure_enable.change(function() {
        $send_request(
	    {
	        method: "structure_enable",
                enable: $(this).prop('checked'),
	    },
	    function($result) {

	        if ($result == false || $result["error"])
		    return false;

                $option_get_plugins();
            });
    });

    $("button#deploy_validate").on("click", function() {

        var $dst = $("input#deploy_dst").val();

        if ($dst === "/home/leo/zeek/" || $dst === "~/zeek/") {
            $danger.text("This is really not a good idea!!").show();
	    $alert.show();
	    return;
        }

        // we get all options choosed from the configuratio
        var $options = {};

        $("input.option_deploy").each(function() {
            $options[$(this).attr("name")] = $(this).prop("checked")
        });

	$send_request(
	    {
		method: "deploy",
                dst: $dst,
                options: JSON.stringify($options),
	    });
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
