<?php function read(){
	
	$host = "localhost";
	$user = "root";
	$password = "Aa123!";
	$link = mysqli_connect($host, $user, $password);
	$dbh = mysqli_select_db($link, "OMS");
	$query = "SELECT * FROM questions";
	$result = mysqli_query($link, $query);
	
	$log = "";
	$pass = "";
	
	if(isset($_POST['loginus'])){
	$log = ($_POST['loginus']);
	$pass = ($_POST['password']);
	}
	
	$querylog = mysqli_query($link,"SELECT role FROM users WHERE login='".mysqli_real_escape_string($link,isset($_POST['loginus']))."'");
    $data = mysqli_fetch_assoc($querylog);
	echo "
	<br>
	<br>

	<h2>Список вопросов</h2>";

	$url = ((!empty($_SERVER['HTTPS'])) ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
	//echo $url;
	//echo '<br>';
	$parts = parse_url($url); 
	parse_str($parts['query'], $queryurl); 
	//echo $queryurl['login']; 	
	
	if($queryurl['login'] == "admin@mail.ru" and !(isset($_POST['logOut']))){		
		while ($row=mysqli_fetch_row($result)) {
			$id = $row[0];
			echo "<div class='divbg'>
			<h5 style='margin: 20px;'>Вопрос №$row[0]</h5>
			<i style='margin: 20px;'>От $row[3]</i>
			<p><i style='margin: 10px;'>Задан $row[2]</i></p>
			<h6 style='margin: 10px;'> $row[1]</h6>
			<p><i style='margin: 10px;'> $row[7] $row[5]</i></p>
			<i style='margin: 10px;'> $row[6]</i>
			<br><div class='btn btn-group'>
				<form method='POST'>
					<input type='hidden' style='margin: 20px;' name='idrow' id='idrowinput$id' value='$id'></input>
					<input type='submit' class='btn btn-outline-danger' name='del' value='Удалить'></input>
					<a type='button' href='#scroll' class='btn btn-outline-dark' name='idrowans' onclick='anShow($id);'>Ответить</a>
				</form>					
			</div>
			</div>
			<br>";
		}
	}

	else{
		while ($row=mysqli_fetch_row($result)) {
			$id = $row[0];
			echo "<div class='divbg'>
			<h5 style='margin: 20px;'>Вопрос №$row[0]</h5>
			<i style='margin: 20px;'>От $row[3]</i>
			<p><i style='margin: 10px;'>Задан $row[2]</i></p>
			<h6 style='margin: 10px;'> $row[1]</h6>
			<p><i style='margin: 10px;'> $row[7] $row[5]</i></p>
			<i style='margin: 10px;'> $row[6]</i>
			<br><div class='btn btn-group'>
				<form method='POST'>
					<input type='hidden' style='margin: 20px;' name='idrow' id='idrowinput$id' value='$id'></input>
				</form>		
			
			</div>
			</div>
			<br>";
		}
	}
}
?>