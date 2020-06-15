<?php
function updateZap($ans, $id, $answer_user){
	$fn = !empty($_POST['loginus']) ? $_POST['loginus'] : '';
	$host = "localhost";
	$user = "root";
	$password = "Aa123!";
	$link = mysqli_connect($host, $user, $password);
	$dbh = mysqli_select_db($link, "OMS");
	$query = "UPDATE questions SET answer='$ans', answer_user='Ответ от: $answer_user', answer_dt=NOW() WHERE id=$id";
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