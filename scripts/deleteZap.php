<?php
function deleteZap($idrow){
	$fn = !empty($_POST['loginus']) ? $_POST['loginus'] : '';
	$host = "localhost";
	$user = "root";
	$password = "Aa123!";
	$link = mysqli_connect($host, $user, $password);
	$dbh = mysqli_select_db($link, "OMS");
	$query = "DELETE FROM questions WHERE id=$idrow";
	$result = mysqli_query($link, $query);
	print"<style>
			#inbtn {
			display: none;
		}
		
			outbtn {
			display: block;
		}		
	
		</style>";
}
?>