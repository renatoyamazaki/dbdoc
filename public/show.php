<?php
	// No browser cache
	header('Cache-Control: no-cache, no-store, must-revalidate');
	header('Pragma: no-cache');
	header('Expires: 0');

	require_once "config.php";
	require_once $APP_ROOT . "/include/class/connection.php";

	/**
	 * Update the view count on documents table
	 *
	 * @param	$conn	object	Connection
	 * @param	$id	string	Document number
	 * @return	bool	TRUE if updated with sucess, false otherwise
	 */
	function update_views ($conn, $id) {

		$sql = "update documents set views = views+1 where id = :id";
	
		$stmt = oci_parse($conn->dbconn, $sql);
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
			
		$sql = "select id, title, content from documents where id = :id";

		$stmt = oci_parse($conn->dbconn, $sql);
		oci_bind_by_name($stmt, ":id", $id);
		oci_execute($stmt, OCI_DEFAULT);

		while (($row = oci_fetch_array($stmt, OCI_BOTH)) != false) {
			$result = array( id => $row['ID'], title=> $row['TITLE'], content=> $row['CONTENT']->load() );
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

		$button = "<form class=\"pure-form pure-form-aligned\"  name=\"cadastro\" action=\"edit.php?id=" . $document[id] . "\" method=\"post\" >";
		$button .= "<button type=\"submit\" class=\"pure-button pure-button-primary\">Edit</button>";
		$button .= "</form>";

		echo "<table class='pure-table pure-table-horizontal' id='content'>";
		echo "<thead> <tr> <th>" . $button . "</th> <th>" . $document[title] . " </th> </tr> </thead>";
		echo "<tbody>";
		echo "<tr>";
		echo "<td colspan='2' id='content'><pre>" . htmlentities($document[content]) . "</pre></td>";
		echo "</tr>";
		echo "</tbody>";
		echo "</table>";
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
			update_views($catalog, $id);
		}
		else {
			echo "ID missing";
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
