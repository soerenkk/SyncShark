<?
	if (isset($_POST['username'])) {
		setcookie('username', $_POST['username'], time()+60*60*24*365, '/');
		echo "Cookie set";
		exit;
	}
?>