
<?php
	$host = "localhost";
	$user = "root";
	$password = "Aa123!";
	$link = mysqli_connect($host, $user, $password);
	$dbh = mysqli_select_db($link, "OMS");
	
	if ($_GET['hash']) {
    $hash = $_GET['hash'];
    // Получаем id и подтверждено ли Email
    if ($result = mysqli_query($link, "SELECT login, email_confirmed FROM users WHERE hash='$hash'")) {
        while( $row = mysqli_fetch_row($result) ) { 
            // Проверяет получаем ли id и Email подтверждён ли 
            if ($row[1] == 1) {
                // Если всё верно, то делаем подтверждение	
                mysqli_query($link, "UPDATE users SET email_confirmed=0 WHERE login='$row[0]'" );
                echo "<br>";
                echo "<br>";
                echo "<br>";
                echo "<br>";
                echo "<br>";
				echo "Email подтверждён";
            } else {
				echo "<br>";
				echo "<br>";
                echo "Что то пошло не так";
            }
        } 
    } else {	
	echo "<br>";
	echo "<br>";
        echo "Что то пошло не так";
    }
} else {
	echo "<br>";
	echo "<br>";
    echo "Что то пошло не так";
}
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8" />
    <title>Регистрация</title>
    <style>
        .msg-error {
            color: red;
			display: none;
        }

        button {
            margin-top: 5px;
        }
    </style>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css" integrity="sha384-ggOyR0iXCbMQv3Xipma34MD+dH/1fQ784/j6cY/iJTQUOhcWr7x9JvoRxT2MZw1T" crossorigin="anonymous">
    <link rel="stylesheet" href="css/stylesheets.css">
    <link rel="stylesheet" href="css/bootstrap.css">
</head>
<body>
    <div class="d-flex flex-md-row p-3 px-md-4 mb-3 text-light bg-secondary fixed-top border-bottom box-shadow">

        <div class="col-md-1">
            <img src="css/vladimir.png" width="50" height="50">
        </div>

        <div class="col-md-3">
            <h6 align="center" class="my-0 mr-md-auto font-weight-bold">Органы местного самоуправления</h6>
            <h6 align="center" class="my-0 mr-md-auto font-weight-bold">г. Владимира</h6>
        </div>

        <div class="col-md-4">
            <h3 align="center" class="my-0 mr-md-auto font-weight-bold">Форум администрации</h3>
        </div>
    </div>
<br>
<br>
<br>
<br>

    <a href="http://localhost/?login=">Основная страница</a>
   
</body>
</html>