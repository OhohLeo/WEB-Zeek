/*
 * Copyright (C) 2015  Léo Martin
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
 */

<?php include 'default/header.php'; ?>
<link rel="stylesheet" href="css/zeek_index.css">
</head>
<body>
    <div id="main_title">
	<h1 id="main_title">Zeek</h1>
	<p id="subtitle">"Simply administrate websites."</p>
    </div>
    <div id="access">
	<div class="alert">
    	    <div class="success"></div>
	    <div class="error"></div>
	</div>
	<div id="connect" disabled="disabled">
	    <input name="login"
		   placeholder="Login"
		   type="text" required>
	    <input name="password"
		   placeholder="Password"
		   type="password" required>
            <input id="connect"
	           name="project_name"
		   placeholder="Project name"
		   type="text" required>
	    <button id="connect" class="success">Connect</button>
	</div>
	<div id="create" disabled="disabled">
	    <p class="message">Confirm the project name:
		<input id="create"
		       name="project_name"
		       placeholder="Project name"
		       type="text" required>
	    </p>
	    <button id="cancel" class="warning small">Cancel</button>
	    <button id="create" class="success">Create</button>
	</div>
    </div>
</body>
<?php include 'default/scripts.php'; ?>
<script src="js/zeek_index.js"></script>
<?php include 'default/footer.php'; ?>
