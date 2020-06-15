
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
		
$url = ((!empty($_SERVER['HTTPS'])) ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];//парсинг URL
$parts = parse_url($url); 
parse_str($parts['query'], $queryurl);
$query = $queryurl['query'];

search ($query); 
		
function search ($query) 
{ 	
	$host = "localhost";
	$user = "root";
	$password = "Aa123!";
	$link = mysqli_connect($host, $user, $password);
	$dbh = mysqli_select_db($link, "OMS");
	
    $query = trim($query); 
    //$query = mysqli_real_escape_string($query);
    $query = htmlspecialchars($query);

	$url = ((!empty($_SERVER['HTTPS'])) ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];//парсинг URL
	$parts = parse_url($url); 
	parse_str($parts['query'], $queryurl);
	$kab = $queryurl['login'];
	
	echo "<br><br><br><br><button type='button' id='arch' class='btn btn-secondary' onclick=";echo "window.location=";echo"'main.php?login=$kab'>На главную</button>";
    if (!empty($query)) 
    { 
        if (strlen($query) < 3) {
            echo "<p>Слишком короткий поисковый запрос.</p>";
        } else if (strlen($query) > 128) {
             echo "<p>Слишком длинный поисковый запрос.</p>";
        } else { 
            
			$q = "SELECT quest_id, quest FROM questions WHERE quest LIKE '%$query%'";
			$result = mysqli_query($link, $q);

            if (mysqli_num_rows($result) > 0) { 
                $row_w = mysqli_fetch_row($result);                
        
                    // Делаем запрос
					
                    $q1 = "SELECT quest_id, quest, quest_dt, quest_user FROM questions WHERE quest LIKE '%$query%'";
                    $result1 = mysqli_query($link, $q1);					     
					$num = mysqli_num_rows($result1);
					
					echo "<p>По запросу <b>$query</b> найдено совпадений: $num</p>";
					
					echo "<div class='col-md-6'>";
					while ($row=mysqli_fetch_row($result1)) {
						//SELECT
						$answer_query = "SELECT answer, answer_user, answer_dt FROM answers, questions WHERE answer_id = '$row[0]'";//определение ответа, отвтившего пользователя
						$answer_result = mysqli_query($link, $answer_query);
						$answer_row=mysqli_fetch_row($answer_result);
				
						$answer_row[1] = str_replace(" ", "", trim($answer_row[1]));
						
						$quest_user_query = "SELECT name FROM users WHERE login='$row[3]'";//определение спрашивающего пользователя
						$quest_user_result = mysqli_query($link, $quest_user_query);
						$quest_user_row=mysqli_fetch_row($quest_user_result);
				
						$answer_query_user = "SELECT name FROM users WHERE login='$answer_row[1]'";//выбор администратора
						$answer_result_user = mysqli_query($link, $answer_query_user);
						$answer_row_user=mysqli_fetch_row($answer_result_user);
																	
						echo "<div class='divbg'>
						<h5 style='margin: 20px;'>Вопрос №$row[0]</h5>
						<i style='margin: 20px;'>От $quest_user_row[0] ($row[3])</b></i>
						<p><i style='margin: 10px;'>Задан $row[2]</i></p>
						<h6 style='margin: 10px;'>$row[1]</h6>";
						if ($answer_row[0]!=''){
							echo "<p><i style='margin: 10px;'>Ответ от <u>$answer_row_user[0]</u> <b>($answer_row[1])</b></i>
							<br>
							<i style='margin: 10px;'>Получен $answer_row[2]</i></p>
							<h6 style='margin: 10px;'>$answer_row[0]</h6><br>";
						}	
						echo "<br></div>";
						
					}
					echo "</div>";

            } else {
                echo "<p>По вашему запросу ничего не найдено.</p>";
            }
        } 
    } else {
        echo "<p>Задан пустой поисковый запрос.</p>";
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
	$mailSMTP = new SendMailSmtpClass('oms.vladimir@mail.ru', 'junkrat333', 'ssl://smtp.mail.ru', 'ОМС г. Владимир', 465);
	//чат
	$host = "localhost";
	$user = "root";
	$password = "Aa123!";
	$link = mysqli_connect($host, $user, $password);
	$dbh = mysqli_select_db($link, "OMS");
	$chat = $_POST['ansArea'];
	$id = $_POST['idrow'];

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
	
	$query_r = mysqli_query($link, "SELECT role FROM users WHERE login='$login_ask'");
	$result_r=mysqli_fetch_row($query_r);

	if ($result_r[0] == 'user')	
	{
		$query=mysqli_query($link, "SELECT answer_user FROM answers WHERE answer_id='$id'");
		$result=mysqli_fetch_row($query);
		$result[0] = str_replace(" ", "", trim($result[0]));
		$mailSMTP->send($result[0], 'Вопрос дополнен!', 'Здраствуйте, пользователь дополнил вопрос № '. $id.' в Вашей теме', ''); // отправляем письмо
	}
	else
	{
		$query=mysqli_query($link, "SELECT quest_user FROM questions WHERE quest_id='$id'");
		$result=mysqli_fetch_row($query);
		$result[0] = str_replace(" ", "", trim($result[0]));	
		$mailSMTP->send($result[0], 'Ответ дополнен!', 'Здраствуйте, Ваш вопрос № '. $id.' был дополнен новым ответом!', ''); // отправляем письмо
	}
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
    
if (isset($_POST['search'])){
	$url = ((!empty($_SERVER['HTTPS'])) ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];//парсинг URL
	$parts = parse_url($url); 
	parse_str($parts['query'], $queryurl);
	$login = $queryurl['login'];

	$query = $_POST['searchRow'];
	
	print "
		<script type='text/javascript'>								
			var newUrl = 'search.php?login=$login&query=$query';
			window.location = newUrl;										
		</script>";
}
?>

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="description" content="">
    <meta name="author" content="">

    <title>Форум Администрации г. Владимира</title>

    <!-- Bootstrap core CSS -->
    <!--<link rel="stylesheet" href="/lib2/bootstrap.min.css">-->
    <link rel="stylesheet" href="css/stylesheets.css">
    <link rel="stylesheet" href="css/bootstrap.css">

    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.4.1/css/bootstrap.min.css" integrity="sha384-Vkoo8x4CGsO3+Hhxv8T/Q5PaXtkKtu6ug5TOeNV6gBiFeWPGFN9MuhOf23Q9Ifjh" crossorigin="anonymous">

    <script src="https://code.jquery.com/jquery-3.4.1.slim.min.js" integrity="sha384-J6qa4849blE2+poT4WnyKhv5vZF5SrPo0iEjwBvKU7imGFAV0wwj1yYfoRSJoZ+n" crossorigin="anonymous"></script>
	<script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.0/dist/umd/popper.min.js" integrity="sha384-Q6E9RHvbIyZFJoft+2mJbHaEWldlvI9IOYy5n3zV9zzTtmI3UksdQRVvoxMfooAo" crossorigin="anonymous"></script>
	<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.4.1/js/bootstrap.min.js" integrity="sha384-wfSDF2E50Y2D1uUdj0O3uMBJnjuUD4Ih7YwaYd1iqfktj0Uod8GCExl3Og8ifwB6" crossorigin="anonymous"></script>
</head>

<body>

    <div class="container">
        <!--Header-->
        <div class="d-flex flex-md-row p-3 px-md-4 mb-3 text-light bg-secondary fixed-top border-bottom box-shadow">

            <div class="col-md-1">
                <a href="http://vladimir-city.ru"> <img src="css/vladimir.png" width="50" height="50"> </a>

            </div>

            <div class="col-md-2">
                <h6 align="center" class="my-0 mr-md-auto font-weight-bold">Органы местного самоуправления</h6>
                <h6 align="center" class="my-0 mr-md-auto font-weight-bold">г. Владимира</h6>
            </div>

            <div class="col-md-4">
                <h3 align="center" class="my-0 mr-md-auto font-weight-bold">Форум администрации</h3>
            </div>
            <div class="col"></div>
			<form method='POST'>
			
				<button type="button" id="inbtn" class="btn btn-outline-light" data-toggle="modal" data-target="#log" autocomplete="off">Войти</button>
				<input name="logOut" id="outbtn" type="submit" class="btn btn-outline-danger" value="Выйти">
				<?php	
					$host = "localhost";
					$user = "root";
					$password = "Aa123!";
					$link = mysqli_connect($host, $user, $password);
					$dbh = mysqli_select_db($link, "OMS");
					
					$url = ((!empty($_SERVER['HTTPS'])) ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];//парсинг URL
					$parts = parse_url($url); 
					parse_str($parts['query'], $queryurl);	
					$kab = $queryurl['login'];
					
					$result=mysqli_query($link, "SELECT role FROM users WHERE login='$kab'");
					$row=mysqli_fetch_row($result);
					
					if ($row[0] != 'moder')
					{echo "<button type='button' id='kabinet' class='btn btn-outline-light' onclick=";echo "window.location=";echo"'kabinet.php?login=$kab'>Личный кабинет</button>";}			
				?>
				<button type="button" class="btn btn-outline-light" data-toggle="modal" data-target="#exampleModalScrollable" autocomplete="off">О сервисе</button>
				<button type="button" class="btn btn-outline-light" data-toggle="modal" data-target="#contact" autocomplete="off">Контакты</button>
			</form>
			<script type='text/javascript'>
	var url = window.location.href;
	const searchString = new URLSearchParams(window.location.search);
	const login = searchString.get('login');
	//alert(login);
	if(login == '')
	{
		//alert ('Пашет');
		let ib = document.getElementById("#inbtn");
		let ob = document.getElementById("outbtn");
		let kab = document.getElementById("kabinet");
		kab.style.display = "none";
		ob.style.display = "none";
		ib.style.display = "block";				
	}
	else 
	{ 
		//alert ('Не Пашет');
		let ib = document.getElementById("inbtn");
		let ob = document.getElementById("#outbtn");
		let kab = document.getElementById("#kabinet");
		ib.style.display = "none";
		ob.style.display = "block";		
		kab.style.display = "block";		
	}
</script>
			</div>
        <!--End Header-->

<script>	

function anShow(id){
	
	let elm = document.querySelector("#editDiv");
	elm.style.display = "block";

    input = document.getElementById('inputId'); 
	inputId.value = id;
}

</script>
        <!--Main-->
        <div>
            <div class="row">
					<div class='col-md-6'>
						<br>
						<br>
						<br>
						<br>

						<?php read();?>				
					</div>					
            </div>
            <div id="scroll"></div>
            <hr>

            <br>
				
            <div class="row">
			<?php 
			$fn = !empty($_POST['loginus']) ? $_POST['loginus'] : '';
			
				$host = "localhost";
				$user = "root";
				$password = "Aa123!";
				$link = mysqli_connect($host, $user, $password);
				$dbh = mysqli_select_db($link, "OMS");
				
				$url = ((!empty($_SERVER['HTTPS'])) ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];//парсинг URL
				$parts = parse_url($url); 
				parse_str($parts['query'], $queryurl);
	
				$cu = $queryurl['login'];
				
				$query = mysqli_query($link, "SELECT role FROM users WHERE login='$cu'");
				$result = mysqli_fetch_row($query);
				if ($result[0] == 'user') {
                echo "<div class='col-6'>
                    <h2>Задать свой вопрос</h2>
                        <div class='form-group'>
                            
								<form method='POST'>
									<label>Выберите тему для вопроса</label>
									<br>";
										themeShow();									
									echo "<br>
									<label for='quest' >Содержание вопроса</label>
									<input type='hidden' class='saveInput' name='logintocrt' value='$fn'>
									<textarea name='quest' class='form-control' rows='5' value=''> </textarea>
									<br>
									<input name='create' type='submit' value='Отправить' class='btn btn-success'>
								</form>                            
                        </div>                
                </div>";
				}
			?>
                <div class="col-6">
					<div id="editDiv">
						<h2>Ответить на вопрос</h2>
							<div class="form-group">
								<label for="edit-fio">Содержание ответа</label>
									<form method='POST'>
										<input type="hidden" class="saveInput" name="logintoans" value="<?php echo $fn;?>">
										<input type="hidden" name="inputId" id="inputId" value="">
										<textarea name='getanswer' class="form-control" rows="5" value=""></textarea>
										<br>
										<input name="update" type="submit" value="Отправить" class="btn btn-primary">
									</form>                                               
							</div>
					</div>
				</div>
			</div>
		</div>
        <!--End Main-->
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
        <!-- Modal -->
        <div class="modal fade" id="exampleModalScrollable" tabindex="-1" role="dialog" aria-labelledby="exampleModalScrollableTitle" aria-hidden="true">
            <div class="modal-dialog modal-dialog-scrollable" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="exampleModalScrollableTitle">О сервисе</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        Сервис является форумом и предназначен для задания вопросов в администрацию г. Владимира.
                        На Ваш вопрос ответят в течении 24 часов с момента публикации. Задать вопрос может любой желающий.
                        <p></p>
                        <p><b>Замечания</b></p>
                        <p></p>
                        <p>Вопрос может потребовать более длительного времени для ответа.</p>
                        <p>Вопрос должен носить не личностный характер.</p>
                        <p>Вопрос должен относиться к компетенции администрации г. Владимира или её структур.</p>
                        <p><b>Часто задаваемые вопросы</b></p>
                        <p><i>Когда включат горячую воду по пр-ту Строителей д. 53?</i></p>
                        <p>График включения горячей воды в г. Владимире был размещен на официальном сайте ОМС г. Владимира в апреле 2019 года.</p>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Закрыть</button>
                    </div>
                </div>
            </div>
        </div>
        <div class="modal fade" id="contact" tabindex="-1" role="dialog" aria-labelledby="exampleModalScrollableTitle" aria-hidden="true">
            <div class="modal-dialog modal-dialog-scrollable" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="exampleModalScrollableTitle">Контакты</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <p>Администрация города Владимира </p>
                        <p>600000 г. Владимир ул. Горького, 36 </p>
                        <p>Тел. (4922) 53-28-17 </p>
                        <p>Факс (4922) 53-04-54 </p>
                        <p>e-mail: <a href="info@vladimir-city.ru">info@vladimir-city.ru</a>  </p>
                        <p></p>	
                        <p><b>Шутов Сергей Донатович </b></p>
                        <p>Заместитель начальника управления, начальник отдела информационных систем.  </p>
                        <p>e-mail: <a href="shutov@vladimir-city.ru ">shutov@vladimir-city.ru </a></p>
                        <p>Тел.: (4922) 53-01-21</p>
                        <p></p>
                        <p><b>Дементьева Екатерина Александровна </b></p>
                        <p>Заведующий сектором Интернет-систем.  </p>
                        <p>Администратор официального сайта органов местного самоуправления города Владимира. </p>
                        <p>e-mail: <a href="webmaster@vladimir-city.ru ">webmaster@vladimir-city.ru     </a> <a href="dementieva@vladimir-city.ru">dementieva@vladimir-city.ru  </a></p>
                        <p>Тел.: (4922) 53-01-21 </p>
                        <p></p>
						<p><b>Паломнин Андрей Андреевич</b></p>
						<p>Ответственное лицо по вопросам благоустройства города Владимира </p>
						<p>e-mail: <a href="palomnin@mail.ru">palomnin@mail.ru</a></p>
                        <p></p>
						<p><b>Бугаев Александр Иванович</b></p>
						<p>Ответственное лицо по вопросам эпидимологического состояния в городе </p>
						<p>e-mail: <a href="bugaev.oms@@mail.ru">bugaev.oms@@mail.ru</a></p>
                        <p></p>
						<p><b>Жиглова Анна Константиновна</b></p>
						<p>Ответственное лицо по вопросам городского транспорта</p>
						<p>e-mail: <a href="zhiglova.oms@@mail.ru">zhiglova.oms@@mail.ru</a></p>
						<p></p>
						<p><b>Черников Сергей Владимирович</b></p>
						<p>Ответственное лицо по вопросам жилищно-коммунального хозяйства</p>
						<p>e-mail: <a href="chernikov.oms@@mail.ru">chernikov.oms@@mail.ru</a></p>
						<p></p>
						<p><b>Попов Петр Алексеевич</b></p>
						<p>Ответственное лицо по вопросам образования и культуры</p>
						<p>e-mail: <a href="popov.oms@@mail.ru">popov.oms@@mail.ru</a></p>
						<p></p>
						<p><b>Крюков Антон Вячеславович</b></p>
						<p>Ответственное лицо по вопросам образования и культуры</p>
						<p>e-mail: <a href="https://e.mail.ru/inbox/krukov.oms@mail.ru">krukov.oms@mail.ru</a></p>
						<p></p>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Закртыть</button>
                    </div>
                </div>
            </div>
        </div>
        <div class="modal fade" id="log" tabindex="-1" role="dialog" aria-labelledby="exampleModalScrollableTitle" aria-hidden="true">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="exampleModalScrollableTitle">Вход</h5>
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
                            <label for="login">Email: </label>
                            <br />
                            <input type="text" class="saveInput" id="loginus" name="loginus" value="<?php echo $fn;?>" required>
                            <br />
                            <label for="Password">Пароль: </label>
                            <br />
                            <input type="password" id="password" name="password">
                            <br />
								<div class="modal-footer">
										<input name="logIn" type="submit" class="btn btn-success" value="Войти">
								</div>		
                        </form>	
							<label>Еще нет аккаунта? Создай</label>
							<br />
							<button class="btn btn-secondary" onclick="window.location='register.php'">Регистрация</button>						
                    </div>
                    
                </div>
            </div>
        </div>
<!-----------------------------------------------------------------СМЕНА ТЕМЫ----------------------------------------------------------------------------------------->		
        <div class="modal hide fade" id="swap" tabindex="-1" role="dialog" aria-labelledby="exampleModalScrollableTitle" aria-hidden="true">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="exampleModalScrollableTitle">Смена темы</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <form method='POST'>
						<p>Выберите администратора</p>
                            <select class='form-control' name='adminSwap' size='1'>
								<?php 
									$host = "localhost";
									$user = "root";
									$password = "Aa123!";
									$link = mysqli_connect($host, $user, $password);
									$dbh = mysqli_select_db($link, "OMS");

									$query = mysqli_query($link, "SELECT id_admin, name FROM users WHERE role='admin'");
									
									while ($theme_row=mysqli_fetch_row($query)) {//выбор и вывод тем с запоминанием-------------------------------------------------------------------------------------------------------------
										echo "<option value='$theme_row[0]'>$theme_row[1]</option>";
									}
									echo "</select>
									<br>";
								?>
							</select>
							<p>Выберите тему</p>
							<select class='form-control' name='themeSwap' size='1'>
								<?php
									$query = mysqli_query($link, "SELECT * FROM themes");
									while ($theme_row=mysqli_fetch_row($query)) {//выбор и вывод тем с запоминанием-------------------------------------------------------------------------------------------------------------
										echo "<option value='$theme_row[0]'>$theme_row[1]</option>";
									}
									echo "</select>
									<br>
									<input type='submit' class='btn btn-secondary' name='ThemeAdminSwap' value='Выбрать' />
									<br>";
								?>
							</select>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        <div class="modal fade" id="create" tabindex="-1" role="dialog" aria-labelledby="exampleModalScrollableTitle" aria-hidden="true">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="exampleModalScrollableTitle">Сайт</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <form>
                            <div class="msgClass">
                                <div id="msgAuth"></div>
                                <ul id="formError"></ul>
                            </div>
                            <label for="create">Вопрос добавлен </label>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        <div class="modal fade" id="update" tabindex="-1" role="dialog" aria-labelledby="exampleModalScrollableTitle" aria-hidden="true">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="exampleModalScrollableTitle">Сайт</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <form>
                            <div class="msgClass">
                                <div id="msgAuth"></div>
                                <ul id="formError"></ul>
                            </div>
                            <label for="update">Ответ добавлен </label>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        <div class="modal fade" id="isauthcreate" tabindex="-1" role="dialog" aria-labelledby="exampleModalScrollableTitle" aria-hidden="true">
            <div class="modal-dialog" role="document">
                <div class="modal-content">		
                    <div class="modal-header">
                        <h5 class="modal-title" id="exampleModalScrollableTitle">Сайт</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <form>
                            <div class="msgClass">
                                <div id="msgAuth"></div>
                                <ul id="formError"></ul>
                            </div>
                            <label for="isauthcreate">У Вас не хватает прав для того, чтобы оставить вопрос</label>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        <div class="modal fade" id="isauthdelete" tabindex="-1" role="dialog" aria-labelledby="exampleModalScrollableTitle" aria-hidden="true">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="exampleModalScrollableTitle">Сайт</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <form>
                            <div class="msgClass">
                                <div id="msgAuth"></div>
                                <ul id="formError"></ul>
                            </div>
                            <label for="isauthdelete">У Вас не хвататет прав для удаления вопроса </label>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        <div class="modal fade" id="isauthupdate" tabindex="-1" role="dialog" aria-labelledby="exampleModalScrollableTitle" aria-hidden="true">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="exampleModalScrollableTitle">Сайт</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <form>
                            <div class="msgClass">
                                <div id="msgAuth"></div>
                                <ul id="formError"></ul>
                            </div>
                            <label for="isauthupdate">У Вас не хвататет прав для того, чтобы оставить ответ </label>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        <div class="modal fade" id="dp" tabindex="-1" role="dialog" aria-labelledby="exampleModalScrollableTitle" aria-hidden="true">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="exampleModalScrollableTitle"></h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div align="center" class="modal-body">
                        <form>
                            <div class="msgClass">
                                <div id="msgAuth"></div>
                                <ul id="formError"></ul>
                            </div>
                            <label for="dp"><font size="6"><b>Добро пожаловать!</b></font></label>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        <!--End Modal-->
    </div> <!-- /container -->
    <script data-require="jquery@*" data-semver="3.0.0" src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.0.0/jquery.js"></script>
    <!--<script src="/js/script.js"></script>-->
    <script src="https://code.jquery.com/jquery-3.3.1.slim.min.js" integrity="sha384-q8i/X+965DzO0rT7abK41JStQIAqVgRVzpbzo5smXKp4YfRvH+8abtTE1Pi6jizo" crossorigin="anonymous"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.7/umd/popper.min.js" integrity="sha384-UO2eT0CpHqdSJQ6hJty5KVphtPhzWj9WO1clHTMGa3JDZwrnQq4sF86dIHNDz0W1" crossorigin="anonymous"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.min.js" integrity="sha384-JjSmVgyd0p3pXB1rRibZUAYoIIy6OrQ6VrjIEaFf/nJGzIxFDsf4x0xIM+B07jRM" crossorigin="anonymous"></script>
</body>
</html>