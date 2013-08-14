<!DOCTYPE html>
<html>
	<head>
		<meta http-equiv="Content-Type" content="text/html; charset=windows-1252">
		<title></title>
	</head>
	<body>
		<form action="analyzer.php" method="POST">
			<label	for="url" style="display:block">URLs of Sitemaps:</label>
			<textarea id="url" name="url" value="" rows="20" cols="100"></textarea><br/>
			<input type="checkbox" name="csv" value="true" id="csv"/><label for="csv">Export to csv</label><br/>
			<input type="submit" value="Analyze"/>
		</form>
	</body>
</html>