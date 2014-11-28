<?php include 'default/header.php'; ?>
<body>
    <div id="main_title">
	<h1 id="main_title">Zeek</h1>
	<p id="subtitle">"Simply administrate websites."</p>
    </div>
    <div id="access">
	<div class="alert success"></div>
	<div class="alert error"></div>
	<div id="connect" disabled="disabled">
	    <input name="project_name"
		   placeholder="Project name"
		   type="text" required>
	    <input name="login"
		   placeholder="Login"
		   type="text" required>
	    <input name="password"
		   placeholder="Password"
		   type="password" required>
	    <button id="connect">Connect</button>
	</div>
	<div id="create" disabled="disabled">
	    <p>Confirm the name of the project, please!</p>
	    <input id="create"
		   name="project_name"
		   placeholder="Project name"
		   type="text" required>
	    <button id="cancel">Cancel</button>
	    <button id="create">Create</button>
	</div>
    </div>
</body>
<?php include 'default/scripts.php'; ?>
<script src="js/zeek_index.js"></script>
<?php include 'default/footer.php'; ?>
