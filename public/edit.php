<?php
	// No browser cache
	header('Cache-Control: no-cache, no-store, must-revalidate');
	header('Pragma: no-cache');
	header('Expires: 0');

	require_once "config.php";
	require_once $APP_ROOT . "/include/class/connection.php";

	/**
	 * Update document
	 *
	 * @param	$conn		object	Connection
	 * @param	$typedoc	string	
	 * @param	$title		string	
	 * @param	$content	string	
	 * @param	$id		string	
	 * @return	BOOL		TRUE if updated with sucess, FALSE otherwise
	 */
	function update_document ($conn, $typedoc, $title, $content, $id) {

		$sql = "update documents set typedoc = :typedoc, title = :title, content = :content where id = :id";

		$stmt = oci_parse($conn->dbconn, $sql);
		oci_bind_by_name($stmt, ":typedoc", $typedoc);
		oci_bind_by_name($stmt, ":title", $title);
		oci_bind_by_name($stmt, ":content", $content);
		oci_bind_by_name($stmt, ":id", $id);
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
	 * Get the document, return all data in an array
	 *
	 * @param	$conn	object	Connection
	 * @param	$id	string	Document number
	 * @return	$result	array	Document data with named indexes
	 */
	function get_document ($conn, $id) {
		
		$result = array();
			
		$sql = "select id, title, content, typedoc from documents where id = :id";

		$stmt = oci_parse($conn->dbconn, $sql);
		oci_bind_by_name($stmt, ":id", $id);
		oci_execute($stmt, OCI_DEFAULT);

		while (($row = oci_fetch_array($stmt, OCI_BOTH)) != false) {
			$result = array( id => $row['ID'], title=> $row['TITLE'], typedoc => $row['TYPEDOC'], content=> $row['CONTENT']->load() );
		}
	
		return $result;
	}



	/**
	 * Print the document, html format 
	 *
	 * @param	$document	array	Document data with named indexes
	 *
	 *
	 */
	function print_document ($document) {

		// Get doc type		
		switch ($document[typedoc]) {
			case "Postgresql":
				$op1 = "selected";
				break;
			case "Oracle":
				$op2 = "selected";				
				break;
			case "SQLServer":
				$op3 = "selected";		
				break;
			case "MySQL":
				$op4 = "selected";		
				break;
		}

		echo "<form class=\"pure-form pure-form-aligned\"  name=\"cadastro\" action=\"edit.php\" method=\"post\" >";
		echo "<fieldset>";
		echo "<div class=\"pure-control-group\">";
		echo "<label for=\"typedoc\">Type</label>";
		echo "<select name=\"typedoc\" class=\"pure-input-1-8\" required>";
		echo "<option></option>";
		echo "<option $op1 >Postgresql</option>";
		echo "<option $op2 >Oracle</option>";
		echo "<option $op3 >SQLServer</option>";
		echo "<option $op4 >MySQL</option>";
		echo "</select>";
		echo "</div>";
		echo "<div class=\"pure-control-group\">";
		echo "<label for=\"title\">Title</label>";
		echo "<input name=\"title\" class=\"pure-input-1-2\" type=\"text\" value=\"" . $document[title] . "\" required>";
		echo "</div>";
		echo "<div class=\"pure-control-group\">";
		echo "<label for=\"content\">Content</label>";
		echo "<textarea rows=\"20\" cols=\"90\" name=\"content\">" . htmlentities($document[content]) . "</textarea>";
		echo "</div>";
		
		echo "<input type=\"hidden\" name=\"id\" value=\"" . $document[id] . "\">";
		echo "<div class=\"pure-controls\">";
		echo "<button type=\"submit\" class=\"pure-button pure-button-primary\">Save</button>";
		echo "</div>";
		echo "</fieldset>";
		echo "</form>";
	}




	/**
	 *
	 *
	 *
	 *
	 */
	try {
		$catalog = new conn();

		// Parameters (GET)
		if ( isset($_GET['id']) ) {
			$id = $_GET['id'];
			$result = get_document($catalog, $id);
		}
		else {
			// Verify the required fields
			if ( isset($_POST['typedoc']) && isset($_POST['title']) && isset($_POST['content']) && isset($_POST['id']) ) {
				$typedoc = $_POST['typedoc'];
				$title = $_POST['title'];
				$content = $_POST['content'];
				$id = $_POST['id'];
				
				if (update_document($catalog, $typedoc, $title, $content, $id))
					header("Refresh:0; url=search.php");
			}
		}
	} catch (Exception $e) {
		echo $e->getMessage();
		echo "Conexão com o catálogo falhou. <br/>";
	}

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
			<form class="pure-form pure-form-aligned"  name="busca" action="search.php" method="post" >
                        <fieldset>

                                <div class="pure-control-group">
                                        <input name="search_string" class="pure-input-1-8" type="text" required>
					<button type="submit" class="pure-button pure-button-primary">Search</button>
                                </div>
       
                        </fieldset>
                        </form>

                </div>

                <div class="content">
                     
<?php
			print_document($result);

?>
                </div>

        </div>
</div>


</body>

</html>
