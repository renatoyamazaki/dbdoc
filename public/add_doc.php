<?php
	// No browser cache
	header('Cache-Control: no-cache, no-store, must-revalidate');
	header('Pragma: no-cache');
	header('Expires: 0');

	require_once "config.php";
	require_once $APP_ROOT . "/include/class/connection.php";

	/**
	 * Add document on database.
	 *
	 *
	 * @param	$conn		object	Connection
	 * @param	$type		string
	 * @param	$title		string
	 * @param	$content	string	
	 * @return	bool		TRUE if registered with sucess, FALSE otherwise
	 */
	function add_document ($conn, $typedoc, $title, $content) {

		$sql = "insert into documents (typedoc, title, content, views, id) values (:typedoc, :title, :content, 0, documents_id_seq.nextval)";
		$stmt = oci_parse($conn->dbconn, $sql);
		oci_bind_by_name($stmt, ":typedoc", $typedoc);
		oci_bind_by_name($stmt, ":title", $title);
		oci_bind_by_name($stmt, ":content", $content);
		oci_execute($stmt, OCI_DEFAULT);
		// Commit	
		$r = oci_commit($conn->dbconn);
		if (!$r) {    
 			$e = oci_error($stmt);
			oci_rollback($conn->dbconn);  // rollback changes
			trigger_error(htmlentities($e['message']), E_USER_ERROR);
			return false;
		}
		else 
			return true;
	}




	/**
	 *
	 *
	 *
	 *
	 */
	// Parameters (POST)
	if ( isset($_POST['type']) && isset($_POST['title']) && isset($_POST['content']) ) {
		$type = $_POST['type'];
		$title = $_POST['title'];
		$content = $_POST['content'];

		try {
			$catalog = new conn();
			if (add_document($catalog, $type, $title, $content))
				header("Refresh:0; url=search.php");
		} catch (Exception $e) {
			echo $e->getMessage();
			echo "Conexão com o catálogo falhou. <br/>";
		}
	}
	else {
?>
<!doctype html>
<html>

<head>
<title>DB doc</title>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<?php
	 require_once $APP_ROOT . "/include/html/header.php";
?>
</head>


<body>


<div id="layout">

<?php
        require_once $APP_ROOT . "/include/html/menu.php";
?>

        <div id="main">
                <div class="header">
                        <h1>DB doc</h1>
                        <h2>Add document</h2>
                </div>

                <div class="content">
                        <form class="pure-form pure-form-aligned"  name="cadastro" action="add_doc.php" method="post" >
                        <fieldset>

	                         <div class="pure-control-group">
                                        <label for="type">Type</label>
			                <select name="type" class="pure-input-1-8" required>
						<option></option>
						<option>Postgresql</option>
						<option>Oracle</option>
						<option>SQLServer</option>
						<option>MySQL</option>
					</select>
                                </div>

                                <div class="pure-control-group">
                                        <label for="title">Title</label>
                                        <input name="title" class="pure-input-1-2" type="text" required>
                                </div>

                                <div class="pure-control-group">
                                        <label for="content">Content</label>
					<textarea rows="20" cols="90" name="content"></textarea> 
                                </div>

       
                                <div class="pure-controls">
                                        <button type="submit" class="pure-button pure-button-primary">Register</button>
                                </div>

                        </fieldset>
                        </form>
                </div>

        </div>
</div>


</body>

</html>
<?php
}

?>
