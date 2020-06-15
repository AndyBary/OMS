
<?php
ini_set('display_errors', 'Off'); 

 
echo "<!doctype html>
<html lang='ru'>"; 

//include "read.php";

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
	
	// Определяем все количество записей в таблице
	$qres = "SELECT COUNT(*) FROM questions WHERE theme_id_name='$theme_id' and archiv='1'";
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
	//запрос для вопросов
//---------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------
	$query = "SELECT quest_id, quest, quest_dt, quest_user, status FROM questions, themes WHERE theme_id_name = '$theme_id' and theme_id_name = theme_id and archiv='1' LIMIT $art,$kol";
	$result = mysqli_query($link, $query);
	
	$url = ((!empty($_SERVER['HTTPS'])) ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];//парсинг URL
	$parts = parse_url($url); 
	parse_str($parts['query'], $queryurl);
	$ab = $queryurl['login'];

	//вывод вопросов и тем
//---------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------
	$role_query = "SELECT role FROM users WHERE login = '$ab'"; //определение роли
	$role_query_result = mysqli_query($link, $role_query);
	$role_row=mysqli_fetch_row($role_query_result);
		
	$url = ((!empty($_SERVER['HTTPS'])) ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];//парсинг URL
	$parts = parse_url($url); 
	parse_str($parts['query'], $queryurl);
	$kab = $queryurl['login'];
	echo "<button type='button' id='arch' class='btn btn-secondary btn-sm' onclick=";echo "window.location=";echo"'main.php?login=$kab'>На главную</button>";	
	
	if($ab != '')
	{ 
		$query = mysqli_query($link,"SELECT name, login, email_confirmed FROM users WHERE login='$ab' LIMIT 1");
		$data = mysqli_fetch_assoc($query);
		if ($data['email_confirmed'] == 0){
			echo "<div name='logOk' id='logOk' class='msgClass'><b>Добро пожаловать, ".$data['name']." <i>(".$data['login'].")</i>!</b></div>";
		}
	}
	if(mysqli_num_rows($result) < 1){
		echo "<h4>В данной теме еще нет архивных вопросов</h4>";
	}
	else {
		echo "<h2>Список архивных вопросов</h2>";
		//вывод для администратора			
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
				
				$admin_theme = "SELECT admin_theme_id FROM users WHERE login='$ab'";//выбор темы администратора
				$admin_theme_result = mysqli_query($link, $admin_theme);
				$admin_theme_row=mysqli_fetch_row($admin_theme_result);
				

				//вывод вопросов и ответов			
					$id = $row[0];					
					echo "<div class='divbg'>
					<h5 style='margin: 20px;'>Вопрос №$row[0]</h5>
					<i style='margin: 20px;'>От $quest_user_row[0] <b>($row[3])</b></i>
					<p><i style='margin: 10px;'>Задан $row[2]</i></p>
					<h6 style='margin: 10px;'> $row[1]</h6>
					<p><i style='margin: 10px;'>Ответ от $answer_row_user[0] <b>($answer_row[1])</b></i>
					<i style='margin: 10px;'>Получен $answer_row[2]</i></p>
					<h6 style='margin: 10px;'>$answer_row[0]</h6>
					</br>					
					</div>
					<br>";

			}
	}
	
	// формируем пагинацию
	
	if ($total > 0){
		echo "<div align='center'><label text-align='middle'>Страница: </label>";
	
		for ($i = 1; $i <= $str_pag; $i++){
			echo "<a text-align='middle' href=/?login=$ab&page=".$i.">" .$i. " </a>";
		}

		echo "</div>";
	}
	echo "</div>";	
	
	echo "<div class='col-md-6'>";	
	//выбор темы
	echo "<br><br><br><br>
	<h2>Выберите тему для ознакомления со списком вопросов</h2>";//темы---------------------------------------------------------------------------------------------------------------------------
	echo "<form action='' method='post'>
			<input type='hidden' class='saveInput' name='logintheme' value='$fn'>												
			<select class='form-control' name='themesLoad' size='6' multiple>";
	while ($theme_row=mysqli_fetch_row($theme_result)) {//выбор и вывод тем с запоминанием-------------------------------------------------------------------------------------------------------------
		if(isset($_POST['themesLoad']) and $theme_row[0]==$_POST['themesLoad']){
			echo "<option value='$theme_row[0]' selected>$theme_row[1]</option>";
		}
		else {
			echo "<option value='$theme_row[0]'>$theme_row[1]</option>";
		}
	}
	echo "</select>
		<br>
		<input type='submit' class='btn btn-secondary' name='send' value='Выбрать' />
		<br>";
	
//поле добавления новой темы (администратор)	
	$tres = "SELECT COUNT(*) FROM themes";
	$themeres = mysqli_query($link, $tres);
	$trow = mysqli_fetch_row($themeres);
	$total = $trow[0] + 1;
	
	$thuser = $queryurl['login'];
	
	$check_role = "SELECT role FROM users WHERE login='$thuser'";
	$check_role_result = mysqli_query($link, $check_role);
	$check_role_row = mysqli_fetch_row($check_role_result);
	
	echo "</form>";
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


		</div>
<script>
	/*var id = document.getElementById('idrowinput').value

    input = document.getElementById('inputId'); // целевой тег <input>
	inputId.value = id;*/

	/*function getAjax() {
   $.ajax({
       url: 'index.php', // мой файл обработчика формы
       type: 'POST',
       data: {
            value: $('#logForm').val() 
       }
       success: function ( data ) { // данные отправлены, результат пришел
           console.log ( data ) ; // данные которые пришли
           // тут уже можно выводить пользователю инфу
           $('div.info').html(data);
      }
   });
}*/

</script>
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
						<p>e-mail: <a href="popov.oms@mail.ru">popov.oms@@mail.ru</a></p>
						<p></p>
						<p><b>Крюков Антон Вячеславович</b></p>
						<p>Ответственное лицо по вопросам образования и культуры</p>
						<p>e-mail: <a href="krukov.oms@mail.ru">krukov.oms@mail.ru</a></p>
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
									<div class="btn btn-group">
										<input name="logIn" type="submit" class="btn btn-success" value="Войти">
										<input name="logOut" type="submit" class="btn btn-danger" value="Выйти">

									</div>
								</div>		
                        </form>	
							<label>Еще нет аккаунта? Создай</label>
							<br />
							<button class="btn btn-secondary" onclick="window.location='register.php'">Регистрация</button>						
                    </div>
                    
                </div>
            </div>
        </div>
		
        <div class="modal hide fade" id="deletemodal" tabindex="-1" role="dialog" aria-labelledby="exampleModalScrollableTitle" aria-hidden="true">
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
                            <label for="delete">Вопрос удален </label>
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