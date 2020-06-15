
<?php

require_once "SendMailSmtpClass.php"; // подключаем класс
  
$mailSMTP = new SendMailSmtpClass('oms.vladimir@mail.ru', 'junkrat333', 'ssl://smtp.mail.ru', 'ОМС г. Владимир', 465);
// $mailSMTP = new SendMailSmtpClass('логин', 'пароль', 'хост', 'имя отправителя');

if(isset($_POST['crtuser'])){
	$err = [];
	 
	$host = "localhost";
	$user = "root";
	$password = "Aa123!";
	$link = mysqli_connect($host, $user, $password);
	$dbh = mysqli_select_db($link, "OMS");
	$query = mysqli_query($link, "SELECT * FROM users WHERE login='".mysqli_real_escape_string($link, $_POST['login'])."'");
	
	if(mysqli_num_rows($query) > 0)
    {
        $err[] = "Пользователь с таким логином уже существует в базе данных";
    } 
	
	 if(strlen($_POST['login']) < 3 or strlen($_POST['login']) > 30)
    {
        $err[] = "Логин должен быть не меньше 3-х символов и не больше 30";
    }
	
	if(strlen($_POST['password']) < 6)
    {
        $err[] = "Пароль должен быть не меньше 6-х символов";
    }
	
	 if($_POST['password'] != $_POST['passwordConfirm'])
		{
        $err[] = "Пароли не совпадают!";
    }
	
	if(count($err) == 0)
    {
		$login = $_REQUEST['login'];
		//$password = strip_tags(trim($_POST['password']));
		$fio = $_REQUEST['fio'];		
		$pass = $_REQUEST['password'];       
        $hash = md5($login . time());
		
		$query = "INSERT INTO users (name, login, password, role, hash, email_confirmed) VALUES ('$fio','$login','$pass', 'user', '$hash', 1)";
		$result = mysqli_query($link, $query);

		$result =  $mailSMTP->send($login, 'Добро пожаловать!', 'Здравствуйте, рады приветствовать вас на нашем портале! Что бы подтвердить Email, перейдите по ссылке: http://localhost/confirmed.php?hash=' . $hash . '', ''); // отправляем письмо
		
		if($result === true){
		echo "<br><br><br><br>На введенный почтовый адрес отправлено письмо для подтверждения";
		}
		else{
			echo "Письмо не отправлено. Ошибка: " . $result;		
		}		
	}
	else {
		print "<b><br><br><br><br>При регистрации произошли следующие ошибки:</b><br>";
        foreach($err AS $error)
        {
            print $error."<br>";
        }
	}
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
   
	<div class="col-md-6">
    <form action='' method='POST' >

		<label>ФИО: </label>
        <input class='form-control' type="text" id="fio" name="fio" placeholder="Введите ФИО">

        <label>Email: </label>
        <input class='form-control' type="email" id="login" name="login" placeholder="Введите E-mail">

        <label>Пароль: </label>
        <input class='form-control' type="password" id="password" name="password" placeholder="Введите пароль">

        <label>Подтверждение пароля: </label>
        <input class='form-control' type="password" id="passwordConfirm" name="passwordConfirm" placeholder="Подтвердите пароль">
		<br />
		<input name="crtuser" type="submit" value="Зарегистрироваться" class="btn btn-success">
    </form>
	</div>
</body>
</html>