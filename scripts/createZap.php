<?php
function createZap($qst, $quest_user){
	$fn = !empty($_POST['loginus']) ? $_POST['loginus'] : '';
	
	settype($qst, "string");
	$host = "localhost";
	$user = "root";
	$password = "Aa123!";
	$link = mysqli_connect($host, $user, $password);
	$dbh = mysqli_select_db($link, "OMS");
	$query = "INSERT INTO questions (quest, quest_dt, quest_user) VALUES ('$qst', NOW(), '$quest_user')";
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