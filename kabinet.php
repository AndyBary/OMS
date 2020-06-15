
<?php
ini_set('display_errors', 'Off'); 

require_once "SendMailSmtpClass.php"; // подключаем класс
 
echo "<!doctype html>
<html lang='ru'>"; 

$mailSMTP = new SendMailSmtpClass('oms.vladimir@mail.ru', 'junkrat333', 'ssl://smtp.mail.ru', 'ОМС г. Владимир', 465);
// $mailSMTP = new SendMailSmtpClass('логин', 'пароль', 'хост', 'имя отправителя');
/*include "read.php";
include "createZap.php";
include "updateZap.php";
include "deleteZap.php";*/

$fn = !empty($_POST['loginus']) ? $_POST['loginus'] : '';

function read(){	
	$fn_theme = !empty($_POST['themesLoad']) ? $_POST['themesLoad'] : '';
	//вход в БД
	$host = "localhost";
	$user = "root";
	$password = "Aa123!";
	$link = mysqli_connect($host, $user, $password);
	$dbh = mysqli_select_db($link, "OMS");
	
	$fn = !empty($_POST['loginus']) ? $_POST['loginus'] : '';
	$theme_id = 1;//начальная тема
	
	if (isset($_POST['themesLoad'])){	//выбор темы	
		$theme_id = $_POST['themesLoad'];		
	}
	// Текущая страница
	if (isset($_GET['page'])){
		$page = $_GET['page'];
	}else $page = 1;
	
	$kol = 5;  //количество записей для вывода	
	$art = ($page * $kol) - $kol;
	//echo $art;
	
	$url = ((!empty($_SERVER['HTTPS'])) ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];//парсинг URL
	$parts = parse_url($url); 
	parse_str($parts['query'], $queryurl);
	$ab = $queryurl['login'];
	
	// Определяем все количество записей в таблице
	$qres = "SELECT COUNT(*) FROM questions WHERE quest_user='$ab'";
	$res = mysqli_query($link, $qres);
	$row_themes = mysqli_fetch_row($res);
	$total = $row_themes[0]; // всего записей	
	//echo $total;
	
	// Количество страниц для пагинации
	$str_pag = ceil($total / $kol);
	//echo $str_pag;
	
	//запрос для тем 
//---------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------
	$theme_query = "SELECT * FROM themes";
	$theme_result = mysqli_query($link, $theme_query);	
	//вывод вопросов и тем
//---------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------
		
	$url = ((!empty($_SERVER['HTTPS'])) ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];//парсинг URL
	$parts = parse_url($url); 
	parse_str($parts['query'], $queryurl);
	$kab = $queryurl['login'];	
	
	$role_query = "SELECT role FROM users WHERE login = '$kab'"; //определение роли
	$role_query_result = mysqli_query($link, $role_query);
	$role_row=mysqli_fetch_row($role_query_result);
	
	if 	($role_row[0]=='user')
	{
//запрос для вопросов пользователя
//---------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------
	$query = "SELECT quest_id, quest, quest_dt, quest_user, status FROM questions WHERE quest_user='$ab' LIMIT $art,$kol";
	$result = mysqli_query($link, $query);
	}
	else
	{
//запрос для вопросов администратора
//---------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------
	$query = "SELECT quest_id, quest, quest_dt, quest_user, status, answer_id, answer_user FROM questions, answers WHERE quest_id=answer_id AND answer_user='$ab ' LIMIT $art,$kol";
	$result = mysqli_query($link, $query);	
	}
	echo "<div style='margin-left: 50px;'>";
	if($ab != '')
	{ 
		$query = mysqli_query($link,"SELECT name, login, email_confirmed FROM users WHERE login='$ab' LIMIT 1");
		$data = mysqli_fetch_assoc($query);
		if ($data['email_confirmed'] == 0){
			echo "<div name='logOk' id='logOk' class='msgClass'><b>Добро пожаловать, ".$data['name']." <i>(".$data['login'].")</i>!</b></div>";
		}
	}
	else {
		echo "<div name='logOk' id='logOk' class='msgClass'><b>Здравствуйте! Войдите в систему, чтобы оставить вопрос</i>!</b></div>";
	}
	if(mysqli_num_rows($result) < 1){
		echo "<h4>У вас нет оставленных вопросов</h4>";
		echo "</div>";
	}
	else {
		if 	($role_row[0]=='user')
		echo "<h2>Список вопросов пользователя</h2>";
		else
		echo "<h2>Список ответов администратора</h2>";
	echo "</div>";
		//вывод 			
			while ($row=mysqli_fetch_row($result)) {
				$answer_query = "SELECT answer, answer_user, answer_dt FROM answers, questions WHERE answer_id = '$row[0]'";//определение ответа, отвтившего пользователя
				$answer_result = mysqli_query($link, $answer_query);
				$answer_row=mysqli_fetch_row($answer_result);
				
				$answer_row[1] = str_replace(" ", "", trim($answer_row[1]));
				
				$quest_user_query = "SELECT name FROM users WHERE login = '$row[3]'";//определение спрашивающего пользователя
				$quest_user_result = mysqli_query($link, $quest_user_query);
				$quest_user_row=mysqli_fetch_row($quest_user_result);
									
				$answer_query_user = "SELECT name FROM users WHERE login='$answer_row[1]'";//выбор администратора
				$answer_result_user = mysqli_query($link, $answer_query_user);
				$answer_row_user=mysqli_fetch_row($answer_result_user);
				
				//вывод вопросов и ответов				
					$id = $row[0];					
					echo "<div class='divbg' style='margin-left: 50px;'>
					<h5 style='margin: 20px;'>Вопрос №$row[0]</h5>
					<i style='margin: 20px;'>От $quest_user_row[0] <b>($row[3])</b></i>
					<p><i style='margin: 10px;'>Задан $row[2]</i></p>
					<h6 style='margin: 10px;'> $row[1]</h6>
					<form method='POST'>
					<input type='hidden' style='margin: 20px;' name='idrow' id='idrowinput$id' value='$id'></input>";
					if ($answer_row[0]!='')
					{
						echo "<p><i style='margin: 10px;'>Ответ от $answer_row_user[0] <b>($answer_row[1])</b></i><br>
						<i style='margin: 10px;'>Получен $answer_row[2]</i></p>	
						<h6 style='margin: 10px;'>$answer_row[0]</h6>";
						echo "<div align='center' style='margin: 20px;'><textarea type='text' align='middle' class='form-control' name='ansArea' cols='200' rows='2'></textarea>
						</br>
							<input type='submit' class='btn btn-outline-success' name='ansChat' value='Отправить'></a>
							</div>";
					}
					echo "</form></br>					
					</div>
					<br>";
			}
	}
	
	// формируем пагинацию
	
	if ($total > 0){
		echo "<div align='center'><label text-align='middle'>Страница: </label>";
	
		for ($i = 1; $i <= $str_pag; $i++){
			echo "<a text-align='middle' href=kabinet.php?login=$ab&page=".$i.">" .$i. " </a>";
		}

		echo "</div>";
	}
}

function themeShow(){
	//вывод тем для задачи вопроса
//---------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------
	$host = "localhost";
	$user = "root";
	$password = "Aa123!";
	$link = mysqli_connect($host, $user, $password);
	$dbh = mysqli_select_db($link, "OMS");
	
	$theme_query = "SELECT * FROM themes";
	$theme_result = mysqli_query($link, $theme_query);
	
	echo "<select class='form-control' name='themesAdd'>";
	while ($theme_row=mysqli_fetch_row($theme_result)) {
	echo "<option value='$theme_row[0]'>$theme_row[1]</option>";
	}
	echo "</select>";
}

function deleteZap($idrow){
//удаление вопроса
//---------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------
	$host = "localhost";
	$user = "root";
	$password = "Aa123!";
	$link = mysqli_connect($host, $user, $password);
	$dbh = mysqli_select_db($link, "OMS");
	
	$fn = !empty($_POST['loginus']) ? $_POST['loginus'] : '';

	$link = mysqli_connect($host, $user, $password);
	$dbh = mysqli_select_db($link, "OMS");
	$query = "DELETE FROM questions WHERE quest_id='$idrow'";
	$result = mysqli_query($link, $query);
	$query = "DELETE FROM answers WHERE answer_id='$idrow'";
	$result = mysqli_query($link, $query);

	print"<style>
			#inbtn {
			display: none;
		}
		
			outbtn {
			display: block;
		}		
	
		</style>";	

	echo "<script type='text/javascript'>
		alert('Вопрос удален!');
		</script>";
			
}

function createZap($qst, $quest_user, $theme){
//добавление вопроса
//---------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------
	$mailSMTP = new SendMailSmtpClass('oms.vladimir@mail.ru', 'junkrat333', 'ssl://smtp.mail.ru', 'ОМС г. Владимир', 465);
	$fn = !empty($_POST['loginus']) ? $_POST['loginus'] : '';
	
	settype($qst, "string");
	$host = "localhost";
	$user = "root";
	$password = "Aa123!";
	$link = mysqli_connect($host, $user, $password);
	$dbh = mysqli_select_db($link, "OMS");
	$query = "INSERT INTO questions (quest, quest_dt, quest_user, theme_id_name, archiv) VALUES ('$qst', NOW(), '$quest_user', '$theme', 0)";
	$result = mysqli_query($link, $query);
	print"<style>
			#inbtn {
			display: none;
		}
		
			outbtn {
			display: block;
		}		
	
		</style>";
		
	$result = mysqli_query($link, "SELECT login, admin_theme_id FROM users WHERE admin_theme_id='$theme'");
	$result_row = mysqli_fetch_row($result);//какому админу отправить
	
	$result_q = mysqli_query($link, "SELECT MAX(quest_id) FROM questions WHERE theme_id_name='$theme'");
	$result_row_q = mysqli_fetch_row($result_q);//поиск id последнего вопроса

	$result_qq = mysqli_query($link, "SELECT quest FROM questions WHERE quest_id='$result_row_q[0]'");
	$result_row_qq = mysqli_fetch_row($result_qq);//содержание последнего вопроса
	
	$result_row[0] = str_replace(" ", "", trim($result_row[0]));
	
	$st = $result_row[0];
	
	$result = $mailSMTP->send($st, 'Новый вопрос!', 'Здраствуйте, в вашей теме новый вопрос: '. $result_row_qq[0].'', ''); // отправляем письмо
	
	echo "<script type='text/javascript'>
		alert('Вопрос добавлен!');
		</script>";
}

function updateZap($ans, $id, $answer_user){
//добавление ответа и оповещения
//---------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------
	$mailSMTP = new SendMailSmtpClass('oms.vladimir@mail.ru', 'junkrat333', 'ssl://smtp.mail.ru', 'ОМС г. Владимир', 465);
	
	$fn = !empty($_POST['loginus']) ? $_POST['loginus'] : '';
	$host = "localhost";
	$user = "root";
	$password = "Aa123!";
	$link = mysqli_connect($host, $user, $password);
	$dbh = mysqli_select_db($link, "OMS");
	$query = "INSERT INTO answers (answer_id, answer, answer_user, answer_dt) VALUES ('$id', '$ans</br>', '$answer_user ', NOW())";
	$result = mysqli_query($link, $query);
	print"<style>
			#inbtn {
			display: none;
		}
		
			outbtn {
			display: block;
		}		
	
		</style>";
	echo "<script type='text/javascript'>
		alert('Ответ добавлен!');
		</script>";
		
	$query = "SELECT * FROM questions WHERE quest_id='$id'";
	$result = mysqli_query($link, $query);
	$row=mysqli_fetch_row($result);

	$result = $mailSMTP->send($row[3], 'Вы получили ответ!', 'Здраствуйте, вы получили ответ на свой вопрос: '. $row[1].'', ''); // отправляем письмо
}

if(isset($_POST['createTheme']))
{
	$host = "localhost";
	$user = "root";
	$password = "Aa123!";
	$link = mysqli_connect($host, $user, $password);
	$dbh = mysqli_select_db($link, "OMS");
	
	$theme = $_POST['newTheme'];
	$theme_id = $_POST['themeId'];
	
	$query = "INSERT INTO themes (theme_id, theme_name) VALUES ('$theme_id', '$theme')";
	$result = mysqli_query($link, $query);	
}

if(isset($_POST['del']))
{
//удаление вопроса (вызов) + мелочи
//---------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------
	$host = "localhost";
	$user = "root";
	$password = "Aa123!";
	$link = mysqli_connect($host, $user, $password);
	$dbh = mysqli_select_db($link, "OMS");
	
	$idrow = strip_tags(trim($_POST['idrow']));	
	
	$url = ((!empty($_SERVER['HTTPS'])) ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
	$parts = parse_url($url); 
	parse_str($parts['query'], $quest_user);
	$ab = $quest_user['login'];

	$quest_query_user = "SELECT role FROM users WHERE login = '$ab'";
	$quest_result_user = mysqli_query($link, $quest_query_user);
	$quest_row_user=mysqli_fetch_row($quest_result_user);

	if ($quest_row_user[0] == 'admin' or $quest_row_user[0] == 'moder'){
		deleteZap($idrow);
	}
	else 
	{
		echo("<script type='text/javascript'>
			window.alert('У вас не хватает прав для удаления вопроса!')
			</script>");
		print"<style>
			#inbtn {
			display: none;
			}
		
			outbtn {
			display: block;
			}		
	
			</style>";
	}
}

if(isset($_POST['create']))
{
//добавление вопроса (вызов) + мелочи
//---------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------
	$host = "localhost";
	$user = "root";
	$password = "Aa123!";
	$link = mysqli_connect($host, $user, $password);
	$dbh = mysqli_select_db($link, "OMS");
	
	$qst = strip_tags(trim($_POST['quest']));
	
	$url = ((!empty($_SERVER['HTTPS'])) ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
	$parts = parse_url($url); 
	parse_str($parts['query'], $quest_user); 
	$ab = $quest_user['login'];
	
	$quest_query_user = "SELECT role FROM users WHERE login = '$ab'";
	$quest_result_user = mysqli_query($link, $quest_query_user);
	$quest_row_user=mysqli_fetch_row($quest_result_user);
	
	if ($quest_row_user[0] == 'admin'){
		echo("<script type='text/javascript'>
			window.alert('Вы не можете оставить вопрос как администратор!')
			</script>");
		print"<style>
			#inbtn {
			display: none;
			}
		
			outbtn {
			display: block;
			}		
	
			</style>";
	}
	else if ($quest_user['login'] == '')
	{
		echo("<script type='text/javascript'>
			window.alert('Войдите в систему, для того, чтобы оставить вопрос')
			</script>");
		print"<style>
			inbtn {
			display: block;
			}
		
			#outbtn {
			display: none;
			}		
	
			</style>";
	}
	else 
	{
		$theme = $_POST['themesAdd'];
		createZap($qst, $quest_user['login'], $theme, $id);
	}
}

if(isset($_POST['update']))
{
//добавление ответа (вызов) + мелочи
//---------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------
	$host = "localhost";
	$user = "root";
	$password = "Aa123!";
	$link = mysqli_connect($host, $user, $password);
	$dbh = mysqli_select_db($link, "OMS");
	
	$id = strip_tags(trim($_POST['inputId']));
	$ans = strip_tags(trim($_POST['getanswer'])); 
	
	$url = ((!empty($_SERVER['HTTPS'])) ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
	$parts = parse_url($url); 
	parse_str($parts['query'], $answer_user);
	$ab_user = $answer_user['login'];
			
	$quest_query_user = "SELECT role FROM users WHERE login = '$ab_user'";
	$quest_result_user = mysqli_query($link, $quest_query_user);
	$quest_row_user=mysqli_fetch_row($quest_result_user);
				
	if ($quest_row_user[0] == 'admin'){
		updateZap($ans, $id, $answer_user['login']);
	}
	else 
	{
		echo("<script type='text/javascript'>
			window.alert('У вас не хватате прав для того, чтобы оставить ответ!')
			</script>");
		print"<style>
			#inbtn {
			display: none;
			}
		
			outbtn {
			display: block;
			}		
	
			</style>";
	}
}

if(isset($_POST['logIn']))
{	
//вход в систему
//---------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------									
	$host = "localhost";
	$user = "root";
	$password = "Aa123!";
	$link = mysqli_connect($host, $user, $password);
	$dbh = mysqli_select_db($link, "OMS");
	$query = mysqli_query($link,"SELECT login, password, name, email_confirmed FROM users WHERE login='".mysqli_real_escape_string($link,$_POST['loginus'])."' LIMIT 1");
    $data = mysqli_fetch_assoc($query);
	
	if($data['password'] == '' or $data['email_confirmed'] == 1){
		print "<br>
		<br>
		<br>
		<br>
		<div name='logOk' id='logOk' class='msgClass'><b>Пользователя с данным именем не существует!</b></div>
		<style>
			inbtn {
			display: block;
		}
		
			#outbtn {
			display: none;
		}		
		<br>
		<br>
		<br>
		</style>";
	}
	else if($data['password'] == $_POST['password'])
    {
		$a = ($_POST['loginus']);
		$b = ($_POST['themesLoad']);
		print "
		<script type='text/javascript'>								
			var newUrl = 'login=$a&page=1';
			window.location.search = newUrl;										
		</script>
		<br>
		<br>
		<br>
		<br>
		<div name='logOk' id='logOk' class='msgClass'><b>Добро пожаловать, ".$data['name']." <i>(".$data['login'].")</i>!</b></div>
		<style>
			#inbtn {
			display: none;
		}
		
			outbtn {
			display: block;
		}		
		<br>
		<br>
		<br>
		</style>";	
	}
	else{
		print "<br>
		<br>
		<br>
		<br>
		<div name='logErr' id='logErr' class='msgClass'><b>Неверный логин или пароль!</b></div>
		<style>
			inbtn {
			display: block;
		}
		
			#outbtn {
			display: none;
		}		
		<br>
		<br>
		<br>
		</style>";
	}
	
	
}

if(isset($_POST['logOut']))
{
//выход из системы
//---------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------
	
	print "<br>
		<br>
		<br>
		<br>
		<div name='logquit' id='logquit' class='msgClass'><b>Вы вышли из системы!</b></div>
		<style>
			inbtn {
			display: block;
		}
		
			#outbtn {
			display: none;
		}		
		<br>
		<br>
		<br>
		</style>";
		echo "<script type='text/javascript'>								
			var newUrl = 'login=&page=1';
			window.location.search = newUrl;										
		</script>";
}

if(isset($_POST['ansChat']))
{
	//чат
	$host = "localhost";
	$user = "root";
	$password = "Aa123!";
	$link = mysqli_connect($host, $user, $password);
	$dbh = mysqli_select_db($link, "OMS");
	$chat = $_POST['ansArea'];
	$id = $_POST['idrow'];
	
	echo $chat;
	$url = ((!empty($_SERVER['HTTPS'])) ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];//парсинг URL
	$parts = parse_url($url); 
	parse_str($parts['query'], $queryurl);
	
	$login_ask = $queryurl['login'];

	$query = "SELECT answer FROM answers WHERE answer_id='$id'";
	$result_query = mysqli_query($link, $query);
	$row = mysqli_fetch_row($result_query);
	
	/*$ask_query = "UPDATE answers SET answer='$row[0]; 
	$queryurl['login']: $chat'";*/
	mysqli_query($link, "UPDATE answers SET answer='$row[0]<br/><i>$login_ask:</i> $chat' WHERE answer_id='$id'");

}

if(isset($_POST['archiv']))
{
	$host = "localhost";
	$user = "root";
	$password = "Aa123!";
	$link = mysqli_connect($host, $user, $password);
	$dbh = mysqli_select_db($link, "OMS");
	
	$id = $_POST['idrow'];
	
	mysqli_query($link, "UPDATE questions SET archiv='1' WHERE quest_id='$id'");
	
}

if (isset($_POST['ThemeAdminSwap']))
{
	$host = "localhost";
	$user = "root";
	$password = "Aa123!";
	$link = mysqli_connect($host, $user, $password);
	$dbh = mysqli_select_db($link, "OMS");
	
	$admin=$_POST['adminSwap'];
	$theme=$_POST['themeSwap'];
	
	mysqli_query($link, "UPDATE users SET admin_theme_id='$theme' WHERE id_admin='$admin'");
	
	echo "<script type='text/javascript'>
	alert('Изменение прошло успешно!');
	</script>";
}

if (isset($_POST['upduserPas']))
{
	$host = "localhost";
	$user = "root";
	$password = "Aa123!";
	$link = mysqli_connect($host, $user, $password);
	$dbh = mysqli_select_db($link, "OMS");
	$password = $_POST['password'];
	
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
		$url = ((!empty($_SERVER['HTTPS'])) ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];//парсинг URL
		$parts = parse_url($url); 
		parse_str($parts['query'], $queryurl);
	
		$kab = $queryurl['login'];
					
		mysqli_query($link, "UPDATE users SET password='$password' WHERE login='$kab'");
		echo "<b><br><br><br><br>Пароль успешно изменен!</b>";
	}
	else 
	{
		print "<b><br><br><br><br>При изменении произошли следующие ошибки:</b><br>";
        foreach($err AS $error)
        {
            print $error."<br>";
        }
	}
}


if (isset($_POST['upduserFIO']))
{
	$host = "localhost";
	$user = "root";
	$password = "Aa123!";
	$link = mysqli_connect($host, $user, $password);
	$dbh = mysqli_select_db($link, "OMS");
	$fio = $_POST['fio'];
	$password = $_POST['passwordFIO'];
	
	$url = ((!empty($_SERVER['HTTPS'])) ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];//парсинг URL
	$parts = parse_url($url); 
	parse_str($parts['query'], $queryurl);
	
	$kab = $queryurl['login'];
	$query=mysqli_query($link, "SELECT password FROM users WHERE login='$kab'");
	$result=mysqli_fetch_row($query);
	
	echo $result[0];
	echo $password;
	
	if($result[0] == $password)
	{
		mysqli_query($link, "UPDATE users SET name='$fio' WHERE login='$kab'");
		echo "<b><br><br><br><br>ФИО успешно изменено!</b>";
	}
	else
	{
		echo "<b><br><br><br><br>Введен неверный пароль!</b>";
	}
	
}
?>

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="description" content="">
    <meta name="author" content="">

    <title>Форум Администрации г. Владимира</title>

    <!-- Bootstrap core CSS -->
    <link rel="stylesheet" href="css/stylesheets.css">
    <link rel="stylesheet" href="css/bootstrap.css">

    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.4.1/css/bootstrap.min.css" integrity="sha384-Vkoo8x4CGsO3+Hhxv8T/Q5PaXtkKtu6ug5TOeNV6gBiFeWPGFN9MuhOf23Q9Ifjh" crossorigin="anonymous">

    <script src="https://code.jquery.com/jquery-3.4.1.slim.min.js" integrity="sha384-J6qa4849blE2+poT4WnyKhv5vZF5SrPo0iEjwBvKU7imGFAV0wwj1yYfoRSJoZ+n" crossorigin="anonymous"></script>
	<script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.0/dist/umd/popper.min.js" integrity="sha384-Q6E9RHvbIyZFJoft+2mJbHaEWldlvI9IOYy5n3zV9zzTtmI3UksdQRVvoxMfooAo" crossorigin="anonymous"></script>
	<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.4.1/js/bootstrap.min.js" integrity="sha384-wfSDF2E50Y2D1uUdj0O3uMBJnjuUD4Ih7YwaYd1iqfktj0Uod8GCExl3Og8ifwB6" crossorigin="anonymous"></script>
</head>

<body>

        <!--Header-->
        <div class="d-flex flex-md-row p-3 px-md-4 mb-3 text-light bg-secondary fixed-top border-bottom box-shadow">

            <div class="col-md-1">
                <a href="http://vladimir-city.ru"> <img src="css/vladimir.png" width="50" height="50"> </a>

            </div>

            <div class="col-md-3">
                <h6 align="center" class="my-0 mr-md-auto font-weight-bold">Органы местного самоуправления</h6>
                <h6 align="center" class="my-0 mr-md-auto font-weight-bold">г. Владимира</h6>
            </div>

            <div class="col-md-4">
                <h3 align="center" class="my-0 mr-md-auto font-weight-bold">Личный кабинет</h3>
            </div>
            <div class="col"></div>

			<script type='text/javascript'>
				var url = window.location.href;
				const searchString = new URLSearchParams(window.location.search);
				const login = searchString.get('login');
			</script>
		</div>
        <!--End Header-->
        <!--Main-->
			<div class='row'>
				<div class='col-md-6'>
					<br>
					<br>
					<br>
					<br>
					<?php
						$url = ((!empty($_SERVER['HTTPS'])) ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];//парсинг URL
						$parts = parse_url($url); 
						parse_str($parts['query'], $queryurl);
	
						$kab = $queryurl['login'];
						echo "<a href='http://localhost/?login=$kab'>Основная страница</a>";
						read();
					?>
				</div>	
				<div class='col-md-6 '>
				</br>
				</br>
				</br>
				</br>
				</br>
				</br>
				<button style='margin-left: 30px;' type='button' class='btn btn-secondary' data-toggle="modal" data-target="#log">Изменить данные</button>
				</div>	
			</div>				
            <hr>
            <br>				
        <!--End Main-->
		 <div class="modal fade" id="log" tabindex="-1" role="dialog" aria-labelledby="exampleModalScrollableTitle" aria-hidden="true">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="exampleModalScrollableTitle">Изменение данных</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <form id="logForm"  method='POST'>
                            <div class="msgClass">
                                <div id="msgAuth"></div>
                                <ul id="formError"></ul>
                            </div>
							<label for="fio">ФИО: </label>
                            <br />
							<input type="text" class="form-control" id="fio" name="fio" placeholder='Введите ФИО'>
							</br>
							<label for="login">Введите пароль для подтверждения </label>
                            <br />
							<input type="password" class="form-control" id="password" name="passwordFIO" placeholder='Пароль'>
                            <br />
							<input type="submit" class="btn btn-secondary" name="upduserFIO" value='Изменить'>
							</br>
							</br>
                            <h5><label for="login">Смена пароля </label></h5>
							
							<label for="login">Введите новый пароль </label>
                            <br />
                            <input type="password" class="form-control" id="pass" name="password" placeholder='Пароль'>
                            <br />
                            <label for="Password">Подтвердите пароль: </label>
                            <br />
                            <input type="password" class="form-control" id="password" name="passwordConfirm" placeholder='Подтверждение'>
                            <br />	
							 <input type="submit" class="btn btn-secondary" name="upduserPas" value='Изменить'>
                        </form>						
                    </div>
                    
                </div>
            </div>
        </div>
        <!--Footer-->
        <br>
        <footer class="pt-4 my-md-5 pt-md-5 border-top">

            <div class="row">
                <div class="col-12 col-md">
                    <small class="d-block mb-3 text-muted">Ярослав Сабуров</small>
                    <small class="d-block mb-3 text-muted">&copy; 2020</small>
                </div>
            </div>
        </footer>
        <!--End Footer-->
       
    <script data-require="jquery@*" data-semver="3.0.0" src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.0.0/jquery.js"></script>
    <!--<script src="/js/script.js"></script>-->
    <script src="https://code.jquery.com/jquery-3.3.1.slim.min.js" integrity="sha384-q8i/X+965DzO0rT7abK41JStQIAqVgRVzpbzo5smXKp4YfRvH+8abtTE1Pi6jizo" crossorigin="anonymous"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.7/umd/popper.min.js" integrity="sha384-UO2eT0CpHqdSJQ6hJty5KVphtPhzWj9WO1clHTMGa3JDZwrnQq4sF86dIHNDz0W1" crossorigin="anonymous"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.min.js" integrity="sha384-JjSmVgyd0p3pXB1rRibZUAYoIIy6OrQ6VrjIEaFf/nJGzIxFDsf4x0xIM+B07jRM" crossorigin="anonymous"></script>
</body>
</html>