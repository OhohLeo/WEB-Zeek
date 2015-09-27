<?php include 'default/header.php'; ?>
<link rel="stylesheet" href="css/zeek_index.css">
</head>
<body>
    <div id="main_title">
	<h1 id="main_title">Zeek</h1>
	<p id="subtitle">"Simply websites"</p>
        <p id="version">1.0.0</p>
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
            <br>
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
