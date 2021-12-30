<!DOCTYPE html>
<!--
/***************************
4108056020 談維傑 第五次作業11/25
4108056020 TAN WEI CHIEH The Fifth Homework 11/25
***************************/
-->
<html>
<head>
<meta charset = "utf-8">
<title>Blackjack</title>
<link rel="icon" href="Cards/BackCard.png">
<style>

@-webkit-keyframes blink {
	0% {
		opacity:1;
	}
	50% {
		opacity:0;
	}
	100% {
		opacity:1;
	}
}
/* Firefox old*/
@-moz-keyframes blink {
	0% {
		opacity:1;
	}
	50% {
		opacity:0;
	}
	100% {
		opacity:1;
	}
}
/* IE */
@-ms-keyframes blink {
	0% {
		opacity:1;
	}
	50% {
		opacity:0;
	}
	100% {
		opacity:1;
	}
}
/* Opera and prob css3 final iteration */
@keyframes blink {
	0% {
		opacity:1;
	}
	50% {
		opacity:0;
	}
	100% {
		opacity:1;
	}
}
.blink-image {
	/* Firefox */
	-moz-animation: blink normal 2s infinite ease-in-out;
	/* Webkit */
	-webkit-animation: blink normal 2s infinite ease-in-out;
	/* IE */
	-ms-animation: blink normal 2s infinite ease-in-out;
	/* Opera and prob css3 final iteration */
	animation: blink normal 2s infinite ease-in-out;
}
.blink-image:hover {
	animation: transform-origin;
}

.error { color: #FF0000; }

.login { padding: 0px 0px 20px; }

body {
	font-family: 'Open Sans', '微軟正黑體', sans-serif;
	background-color: darkslategray;
	color:white;
}
h1 {
	text-align: center;
	font-size:xx-large;
	color:goldenrod;
}
h2 {
	font-size:x-large;
}


header {
	padding: 7px 10px;
	margin: -8px -8px 0px;
	text-align: right;
	text-decoration: none;
	background-color: DimGray;
	color: white;
}

footer, footer{
	padding: 3px;
	margin: 0px -8px;
	text-align: center;
	text-decoration: none;
	background-color: rgb(50,50,50);
	color: white;
}

a, a:link, a:visited, a:active{
	color:rgb(173,255,47);
	text-decoration: none;
}

a:hover{
	color:#19953b;
	transition: color 300ms;
}

</style></head><body>

<?php
	date_default_timezone_set("Asia/Taipei");
	define("ONE_DAY", 60*60*24);

	// Connect to MySQL and open Blackjack database
	if ( !( $conn = mysqli_connect( "localhost",
		"iw3htp", "password", "blackjack" ) ) )
		die( "Could not connect to database </body></html>" );

	function test_input($data) {
		$data = trim($data);
		$data = stripslashes($data);
		$data = htmlspecialchars($data);
		return $data;
	}

	function loginHTML($uidErr, $pwdErr) { return '
		<h1>Welcome to Blackjack</h1>
		<div class="login" style="text-align: center;">
			<h2>Login or Register</h2>
			<form method="post" action="/blackjack.php">
				Username: <input type="text" name="uid">
				<p class="error">' . $uidErr . '</p><br><br>
				Passward: <input type="password" name="pwd">
				<p class="error">' . $pwdErr . '</p><br><br>
				<input type="submit" value="Login/Register">
			</form>
		</div>
	';}

	function table($uid = ""){
		global $conn;
		// build SELECT query -- Sum of resaults
		$query = 'SELECT SUM(`Resault`) FROM `record_data`';

		// query Blackjack database
		if ( !( $result = mysqli_query( $conn, $query ) ) ) {
			print( "<p>Could not execute query!</p>" );
			die( mysqli_error( $conn ) . "</body></html>" );
		} // end if
		$result = mysqli_fetch_assoc($result);
		$balance = $result['SUM(`Resault`)'];
		echo '<h1 style="text-align: center;">Website earned: $'. ((-1)*$balance) .'</h1>';

		$tableOpenHTML = '
			<div style="width: 100%; max-width: 750px; margin: 0px auto; text-align: center;">
			<a href="/blackjack.php?play" style="font-size:xxx-large;">
			'.(isset($_COOKIE['deck'])?'Continue':'Play').'</a><br><br>
			<input form="check_record" type="submit" name="user" value="All User record"/>
			<h2>Record Board</h2><table style="width: 100%;">
			<thead><tr> <th>Time</th> <th>Username</th> <th>Balance</th> <th>Resault</th>
			<th>Ante</th> <th>Point</th> <th>BankerPoint</th> <th>Note</th> </tr></thead>
			<tbody>
		';
		$tableCloseHTML ='</tbody><tfoot></tfoot></table></div>';

		// build SELECT query -- get record for all or specific user
		$query = 'SELECT * FROM `record_data`';
		if($uid == 'All User record') $uid = '';
		if($uid) $query .= 'WHERE Username="' . $uid . '"';

		// query Blackjack database
		if ( !( $result = mysqli_query( $conn, $query ) ) ) {
			print( "<p>Could not execute query!</p>" );
			die( mysqli_error( $conn ) . "</body></html>" );
		} // end if

		$tableTbodyHTML = '';

		// fetch each record in result set
		while ( $ass =  mysqli_fetch_assoc( $result ) ) {
			// build table to display results
			$tableTbodyHTML .= '<tr>';
			foreach($ass as $key => $value){
				if($key!='Username')
					$tableTbodyHTML .= '<td>'.$value.'</td>';
				else $tableTbodyHTML .= '<td><input form="check_record" type="submit" name="user" value="'.$value.'"/></td>';
			}
			$tableTbodyHTML .= '</tr>';
		} // end while

		$table = $tableOpenHTML;
		$table .= $tableTbodyHTML;
		$table .= $tableCloseHTML;
		return $table;
	}

	function imgCard($card, $blink = FALSE){
		if($blink) return '<a href="/blackjack.php?play&hit">
			<img src="../Cards/'.$card.'.png" alt="'.$card.'" width="110" 
			height="176" class="blink-image"></a>';
		return '<img src="../Cards/'.$card.'.png" alt="'.$card.'" width="110" height="176">';
	}
	
	function ptCheck($deck, $hide = FALSE){
		$pt = 0;
		$len = count($deck);
		for($i = ($hide?1:0); $i < $len; $i++){
			switch($deck[$i][0]){
				case '2': $pt += 2; break;
				case '3': $pt += 3; break;
				case '4': $pt += 4; break;
				case '5': $pt += 5; break;
				case '6': $pt += 6; break;
				case '7': $pt += 7; break;
				case '8': $pt += 8; break;
				case '9': $pt += 9; break;
				case '1': case 'J': case 'Q': case 'K':
					$pt += 10; break;
				case 'A':
					if($pt<=10) $pt+=11;
					else $pt+=1;
			}
		}
		return $pt;
	}
?>
<?php if(isset($_GET["play"])):?>
<?php
	// build SELECT query -- find balance by uid
	$query = 'SELECT balance FROM `account_data` WHERE uid="' . $_COOKIE["uid"] . '"';

	// query Blackjack database
	if ( !( $result = mysqli_query( $conn, $query ) ) ) {
	print( "<p>Could not execute query!</p>" );
	die( mysqli_error( $conn ) . "</body></html>" );
	} // end if

	$result = mysqli_fetch_assoc( $result );
	$balance = $result["balance"];

	setcookie("balance", $balance, time() + ONE_DAY, "/");
	if(empty($_COOKIE['ante'])) setcookie('ante', '0', time() + ONE_DAY);

	if(empty($_COOKIE['deck'])){
		$deck = array();
		$suit = array("C","D","H","S");
		$spcd = array("J","Q","K","A");
		for($i = 2; $i <= 10; $i++)
			for($j = 0; $j < 4; $j++)
				array_push($deck, $i . $suit[$j]);
		for($i = 0; $i < 4; $i++)
			for($j = 0; $j < 4; $j++)
				array_push($deck, $spcd[$i] . $suit[$j]);
		shuffle($deck);
		$dealerDeck = array(array_pop($deck));
		$playerDeck = array(array_pop($deck));
		array_push($dealerDeck, array_pop($deck));
		array_push($playerDeck, array_pop($deck));
		setcookie('deck', serialize($deck), time() + ONE_DAY);
		setcookie('dealerDeck', serialize($dealerDeck), time() + ONE_DAY);
		setcookie('playerDeck', serialize($playerDeck), time() + ONE_DAY);
		// build INSERT query -- record
		$newUserQuery =
		'INSERT INTO `record_data` (`Time`, `Username`, `Balance`, `Resault`, `Ante`, `Point`, `BankerPoint`, `Note`)
		 VALUES (current_timestamp(), "'. $_COOKIE['uid'] .'", "'. $balance .'", "0", "0", "'.ptCheck($playerDeck).'", "'.ptCheck($dealerDeck,TRUE).'", "New Game");';

		// query Blackjack database -- record
		if ( !( $result = mysqli_query( $conn, $newUserQuery ) ) ) {
			print( "<p>Could not execute query!</p>" );
			die( mysqli_error( $conn ) . "</body></html>" );
		} // end if
	}
	else {
		$deck = unserialize($_COOKIE['deck'], ["allowed_classes" => false]);
		$dealerDeck = unserialize($_COOKIE['dealerDeck'], ["allowed_classes" => false]);
		$playerDeck = unserialize($_COOKIE['playerDeck'], ["allowed_classes" => false]);
	}

	$gameOver = "";
	$dealerStand = FALSE;

	if(isset($_GET['hit'])){
		array_push($playerDeck, array_pop($deck));
		if(ptCheck($playerDeck) > 21) $gameOver = 'LOSE';
		if(ptCheck($dealerDeck) < 17) array_push($dealerDeck, array_pop($deck));
		else if(ptCheck($dealerDeck) <= 21) $dealerStand = TRUE;
		if(ptCheck($dealerDeck) > 21) $gameOver = 'WIN';
		$note = "Hit";
		// build INSERT query -- record
		$newUserQuery =
		'INSERT INTO `record_data` (`Time`, `Username`, `Balance`, `Resault`, `Ante`, `Point`, `BankerPoint`, `Note`)
		 VALUES (current_timestamp(), "'. $_COOKIE['uid'] .'", "'. $balance .'", "0", "'. (isset($_COOKIE['ante'])?$_COOKIE['ante']:0) .'", "'.ptCheck($playerDeck).'", "'.ptCheck($dealerDeck,TRUE).'", "'. $note .'");';
		// query Blackjack database -- record
		if ( !( $result = mysqli_query( $conn, $newUserQuery ) ) ) {
			print( "<p>Could not execute query!</p>" );
			die( mysqli_error( $conn ) . "</body></html>" );
		} // end if
		setcookie('deck', serialize($deck), time() + ONE_DAY);
		setcookie('dealerDeck', serialize($dealerDeck), time() + ONE_DAY);
		setcookie('playerDeck', serialize($playerDeck), time() + ONE_DAY);
	}

	if(isset($_GET['stand'])){
		while(ptCheck($dealerDeck) < 21) array_push($dealerDeck, array_pop($deck));
		if(ptCheck($dealerDeck) > 21) $gameOver = 'WIN';
		else if(ptCheck($playerDeck) > ptCheck($dealerDeck)) $gameOver = 'WIN';
		else if(ptCheck($playerDeck) < ptCheck($dealerDeck)) $gameOver = 'LOSE';
		else  $gameOver = 'DRAW';
		// build INSERT query -- record
		$newUserQuery =
		'INSERT INTO `record_data` (`Time`, `Username`, `Balance`, `Resault`, `Ante`, `Point`, `BankerPoint`, `Note`)
		 VALUES (current_timestamp(), "'. $_COOKIE['uid'] .'", "'. $balance .'", "0", "'. $_COOKIE['ante'] .'", "'.ptCheck($playerDeck).'", "'.ptCheck($dealerDeck,TRUE).'", "Stand");';
		// query Blackjack database -- record
		if ( !( $result = mysqli_query( $conn, $newUserQuery ) ) ) {
			print( "<p>Could not execute query!</p>" );
			die( mysqli_error( $conn ) . "</body></html>" );
		} // end if
	}

	if($gameOver == "WIN"){
		if(ptCheck($playerDeck) == 21) $result = $_COOKIE['ante']*2.5;
		else  $result = $_COOKIE['ante']*2;
		$balance += $result;
		// build UPDATE query
		$query = 'UPDATE `account_data` SET `balance`='.$balance.' WHERE uid="'.$_COOKIE["uid"].'"';

		// query Blackjack database
		if ( !( $result = mysqli_query( $conn, $query ) ) ) {
			print( "<p>Could not execute query!</p>" );
			die( mysqli_error( $conn ) . "</body></html>" );
		} // end if	
	}

	$header = '
		<form id="check_record" method="get" action="/blackjack.php">
		</form><header>Balance: '.
		($_COOKIE['balance']-(isset($_GET['ante'])?$_GET['ante']:0)).
		'  User: '.$_COOKIE['uid'] .'
		<a href="/blackjack.php">Main</a>  
		<a href="/blackjack.php?logout">Logout</a>
		</header>
	';
	echo $header;

	echo '<div style="text-align:center;">';
	echo '<h2>Dealer';
	if($dealerStand) echo ' STAND';
	echo ': ';
	if($gameOver){
		if(ptCheck($dealerDeck) == 21) echo 'Blackjack! ';
		else echo ptCheck($dealerDeck);
	}
	else echo ptCheck($dealerDeck, TRUE);
	echo '</h2>';
	$len = count($dealerDeck);
	if($gameOver) echo imgCard($dealerDeck[0]);
	else echo '<a href="/blackjack.php?play&stand">'.imgCard('BackCard').'</a>';
	for ($i=1; $i<$len; $i++) echo imgCard($dealerDeck[$i]);

	echo '<h2>You: ';
	if(ptCheck($playerDeck) == 21) echo 'Blackjack! ';
	else echo ptCheck($playerDeck);
	echo '</h2>';
	foreach ($playerDeck as $card) echo imgCard($card);

	if($gameOver){
		$result = 0;
		// build INSERT query -- record
		$newUserQuery =
		'INSERT INTO `record_data` (`Time`, `Username`, `Balance`, `Resault`, `Ante`, `Point`, `BankerPoint`, `Note`)
		 VALUES (current_timestamp(), "'. $_COOKIE['uid'] .'", "'. $balance .'", "'. $result.'", "'. $_COOKIE['ante'] .'", "'.ptCheck($playerDeck).'", "'.ptCheck($dealerDeck).'", "'. $gameOver .'");';
		// query Blackjack database -- record
		if ( !( $result = mysqli_query( $conn, $newUserQuery ) ) ) {
			print( "<p>Could not execute query!</p>" );
			die( mysqli_error( $conn ) . "</body></html>" );
		} // end if
		echo '<h2>'. $gameOver .'</h2>';
		echo '<a href="/blackjack.php?play" style="font-size:xx-large;">New Game</a><br><br>';
		setcookie('ante', '', time()-3600);
		setcookie('deck', '', time()-3600);
		setcookie('dealerDeck', '', time()-3600);
		setcookie('playerDeck', '', time()-3600);
		die("</body></html>" );
	}

	echo imgCard('BackCard',TRUE);

	if(isset($_GET['ante']) && $balance > $_GET['ante']) {
		$balance -= $_GET['ante'];
		setcookie("balance", $balance, time() + ONE_DAY, "/");

		// build UPDATE query
		$query = 'UPDATE `account_data` SET `balance`='.$balance.' WHERE uid="'.$_COOKIE["uid"].'"';

		// query Blackjack database
		if ( !( $result = mysqli_query( $conn, $query ) ) ) {
			print( "<p>Could not execute query!</p>" );
			die( mysqli_error( $conn ) . "</body></html>" );
		} // end if

		setcookie('ante', $_COOKIE['ante']+$_GET['ante'], time() + ONE_DAY);
		echo '<h2>Anted: '.$_COOKIE['ante']+$_GET['ante'].'</h2>';

		// build INSERT query -- record
		$newUserQuery =
		'INSERT INTO `record_data` (`Time`, `Username`, `Balance`, `Resault`, `Ante`, `Point`, `BankerPoint`, `Note`)
		 VALUES (current_timestamp(), "'. $_COOKIE['uid'] .'", "'. $balance .'", "-'. $_GET['ante'] .'", "'.$_COOKIE['ante']+$_GET['ante'].'", "'.ptCheck($playerDeck).'", "'.ptCheck($dealerDeck,TRUE).'", "Ante");';

		// query Blackjack database -- record
		if ( !( $result = mysqli_query( $conn, $newUserQuery ) ) ) {
			print( "<p>Could not execute query!</p>" );
			die( mysqli_error( $conn ) . "</body></html>" );
		} // end if
	}
	else echo '<h2>Ante Option</h2>';
	echo '<a style="font-size:xx-large;" href="/blackjack.php?play&ante=10">10 </a>';
	echo '<a style="font-size:xx-large;" href="/blackjack.php?play&ante=50">50 </a>';
	echo '<a style="font-size:xx-large;" href="/blackjack.php?play&ante=100">100</a>';
	echo '<br><p class="error">Note: Click the blink-hidden card for HIT, click the hidden card for STAND</p></div>';
?>
<?php else:?>
<?php
	$uid = $pwd = $uidErr = $pwdErr = "";

	if ($_SERVER["REQUEST_METHOD"] == "POST") {
		if (empty($_POST["uid"])) $uidErr = "Please enter username.";
		else {
			$uid = test_input($_POST["uid"]);
			setcookie("uid", $uid, time() + ONE_DAY, "/");
		}
		if (empty($_POST["pwd"])) $pwdErr = "Please enter passward.";
		else {
			$pwd = test_input($_POST["pwd"]);
			setcookie("pwd", $pwd, time() + ONE_DAY, "/");
		}
	}
	else {
		if (isset($_COOKIE["uid"])) $uid = $_COOKIE["uid"];
		if (isset($_COOKIE["pwd"])) $pwd = $_COOKIE["pwd"];
	}

	if(isset($_GET["logout"])){
		setcookie('uid', '', time()-3600);
		setcookie('pwd', '', time()-3600);
		setcookie('balance', '', time()-3600);
		setcookie('ante', '', time()-3600);
		setcookie('deck', '', time()-3600);
		setcookie('dealerDeck', '', time()-3600);
		setcookie('playerDeck', '', time()-3600);
		$pwdErr = "Logged out";
		echo loginHTML($uidErr, $pwdErr);
		die( "</body></html>" );
	}

	if(empty($uid) || empty($pwd)){
		echo loginHTML($uidErr, $pwdErr);
		die( "</body></html>" );
	}

	// build SELECT query -- find pwd by uid
	$query = 'SELECT pwd FROM `account_data`
			  WHERE uid="' . $uid . '"';

	// query Blackjack database
	if ( !( $result = mysqli_query( $conn, $query ) ) ) {
		print( "<p>Could not execute query!</p>" );
		die( mysqli_error( $conn ) . "</body></html>" );
	} // end if

	$result = mysqli_fetch_assoc( $result );
	if(isset($result['pwd'])) {
		if($pwd != $result['pwd']) {
			$pwdErr = "Password error! Please enter again.";
			echo loginHTML($uidErr, $pwdErr);
			die( "</body></html>" );
		}
	}
	else {// if uid does not exist, register and record

		// build INSERT query -- register
		$newUserQuery =
		'INSERT INTO `account_data` (`uid`, `pwd`, `balance`)
			VALUES ("'.$uid.'", "'.$pwd.'", "1000")';

		// query Blackjack database -- register
		if ( !( $result = mysqli_query( $conn, $newUserQuery ) ) ) {
			print( "<p>Could not execute query!</p>" );
			die( mysqli_error( $conn ) . "</body></html>" );
		} // end if

		// build INSERT query -- record
		$newUserQuery =
		'INSERT INTO `record_data` (`Time`, `Username`, `Balance`, `Resault`, `Ante`, `Point`, `BankerPoint`, `Note`)
			VALUES (current_timestamp(), "'.$uid.'", "1000", "1000", "0", "0", "0", "New User");';

		// query Blackjack database -- record
		if ( !( $result = mysqli_query( $conn, $newUserQuery ) ) ) {
			print( "<p>Could not execute query!</p>" );
			die( mysqli_error( $conn ) . "</body></html>" );
		} // end if
	}

	// build SELECT query -- find balance by uid
	$query = 'SELECT balance FROM `account_data`
				WHERE uid="' . $uid . '"';

	// query Blackjack database
	if ( !( $result = mysqli_query( $conn, $query ) ) ) {
		print( "<p>Could not execute query!</p>" );
		die( mysqli_error( $conn ) . "</body></html>" );
	} // end if

	$result = mysqli_fetch_assoc( $result );
	$balance = $result["balance"];
	setcookie("balance", $balance, time() + ONE_DAY, "/");

	$header = '
		<form id="check_record" method="get" action="/blackjack.php">
		</form><header>Balance: '. $balance .'  User: '. $uid .'
		<a href="/blackjack.php">Main</a>  
		<a href="/blackjack.php?logout">Logout</a>
		</header>
	';
	echo $header;

	if(empty($isGame)) $isGame = FALSE;
	if (isset($_GET["user"])) echo table($_GET['user']);
	else echo table($uid);
?>
<?php endif;mysqli_close( $conn );?>
</body>
</html>