<?php
	// No browser cache
	header('Cache-Control: no-cache, no-store, must-revalidate');
	header('Pragma: no-cache');
	header('Expires: 0');

	require_once "config.php";
	require_once $APP_ROOT . "/include/class/connection.php";

	/**
	 *  List all documents
	 *
	 * @param	$conn	object	Connection
	 * @return	$result	array	All the documents in a array with named indexes
	 */
	function list_document ($conn) {
		
		$result = array();
			
		$sql = "select id, typedoc, title, views from documents order by typedoc, title";

		$stmt = oci_parse($conn->dbconn, $sql);
		oci_execute($stmt, OCI_DEFAULT);

		while (($row = oci_fetch_array($stmt, OCI_BOTH)) != false) {
			$result[] = array( id => $row['ID'], typedoc => $row['TYPEDOC'], title => $row['TITLE'], views => $row['VIEWS'] );
		}
	
		return $result;
	}


	/**
	 * Search for documents that have a keyword
	 *
	 * @param	$conn	object	Connection
	 * @param	$ss	string	Search string
	 * @return	$result	array	All the documents that have met the criteria
	 */
	function search_document ($conn, $ss) {

		$result = array();			
	
		$sql = "select id, typedoc, title, views from documents where dbms_lob.instr(content, :ss)>=1 or title like '%:ss%' order by views desc";
		$stmt = oci_parse($conn->dbconn, $sql);
		oci_bind_by_name($stmt, ":ss", $ss);
		oci_execute($stmt, OCI_DEFAULT);

		while (($row = oci_fetch_array($stmt, OCI_BOTH)) != false) {
			$result[] = array( id => $row['ID'], typedoc => $row['TYPEDOC'], title => $row['TITLE'], views => $row['VIEWS']);
		}
	
		return $result;
	}


	 
	/**
	 * Print the list of documents found 
	 *
	 * @param	$doc_array	array	All the documents in an array with named indexes
	 * @return	void
	 */
	function print_document_list ($doc_array) {

		if (empty($doc_array)) {
			echo "No results found.";
		}
		else {
			echo "<table class='pure-table pure-table-horizontal' id='sortable'>";
			echo "<thead> <tr> <th>TYPE</th> <th>TITLE</th> <th> VIEWS </th></tr> </thead>";
			echo "<tbody>";
			foreach ($doc_array as $item) {
				echo "<tr> ";
				echo "<td>" . $item[typedoc] . "</td>";
				echo "<td><a href=\"show.php?id=" . $item[id] . "\">" . $item[title] . "</a></td>";
				echo "<td>" . $item[views] . "</td>";
				echo "</tr>";
			}
			echo "</tbody>";
			echo "</table>";
		}
	}



	/**
	 * 
	 *
	 *
	 *
	 */
	try {
		$catalog = new conn();

		// Parameters (POST)
		if ( isset($_POST['search_string']) ) {
			$ss = $_POST['search_string'];
			$result = search_document($catalog, $ss);
		}
		else {
			$result = list_document($catalog);
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
                    <!--    <h2>Search</h2> -->
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
			print_document_list($result);

?>
                </div>

        </div>
</div>


</body>

</html>
