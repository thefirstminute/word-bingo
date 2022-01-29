<?php
/* Notes 'N Stuff{{{
Colours:
#38023b
#a288e3
#bbd5ed
#cefdff
#ccffcb

PMS 7126 CP
CMYK 0/98/91/30

177/24/30

"Black"
20/32/44

Dk Grey
70/82/79

Med Grey
182/183/186

"White" 
232/233/236

rgba(177,24,30, 1)
rgba(20,32,44, 1)
rgba(70,82,79, 1)
rgba(182,183,186, 1)
rgba(232,233,236, 1)

}}} */

/* initial error checks that would break the site {{{ */
$err='';
$required_files = array('bingo_players.json', 'bingo_words.txt', 'game_title.txt');
foreach ($required_files as $file) {
  if (!file_exists($file)) {
    if (!touch($file)) $err .= "failed to create $file".PHP_EOL;
  }
}

if ($err!='') {
  echo nl2br($err);
  die('<style> body { background-color: #333; color:#EEE; }</style>');
}
/* }}} */

/* fucntions & vars {{{ */
function posted($var){
	if (isset($_POST[$var])) return $_POST[$var];
	else return '';
}

function randomGen($min, $max, $quantity) {
  $numbers = range($min, $max);
  shuffle($numbers);
  return array_slice($numbers, 0, $quantity);
}
// print_r(randomGen(0,45,25)); //generates 20 unique random numbers
$logged_in=false;
$username=$bingo_words='';

$words = file("bingo_words.txt");
$word_rows = count($words)-1;
$game_title = file_get_contents('game_title.txt');

$data = file_get_contents('bingo_players.json');
$users = json_decode($data, true);

/* }}} */

/* Logout: {{{ */
if (isset ($_GET['logout']) && $_GET['logout']!='') {
  setcookie("bingo_player", "", time()-(60*60*24), "/");
  header("Location: index.php");
  die();
}
/* }}} */

/* Login: {{{ */
if (posted('user_login')!='' && posted('username')!='') {
  $username=preg_replace("#[^0-9a-z_]#i", '', $_POST['username']);
  if (is_array($users) && array_key_exists($username, $users)) {
    // user exists, log in & gather user info (ui)
    setcookie('bingo_player', $username, strtotime('today 23:59'), '/');

  } else {
    // user does not exist, create account & generate board nubmers
    // refresh bingo_players, just in case something chnaged while this one was here
    $data = file_get_contents('bingo_players.json');
    $users = json_decode($data, true);

    $add_arr = array(
      'bingo_words' => randomGen(0,$word_rows,24),
      'gotem'       => array()
    );
    $users[$username] = $add_arr;

    file_put_contents('bingo_players.json', json_encode($users, JSON_PRETTY_PRINT));

    setcookie('bingo_player', $username, strtotime('today 23:59'), '/');
  }

  header('location: index.php');
  die;
}
/* END Login }}} */

/* check if there's a user here: {{{ */
if(isset($_COOKIE['bingo_player']) && $_COOKIE['bingo_player']!='') {
  // check if it's a username
  $username=isset($_COOKIE['bingo_player']) ? preg_replace("#[^0-9a-z_]#i", '', $_COOKIE['bingo_player']) : '';
  if (is_array($users) && array_key_exists($username, $users)) {
    $ui = $users[$username];
    $bingo_words = $ui['bingo_words'];
    $gotem = $ui['gotem'];
    $logged_in=true;

    /* Create Leaderboard: {{{ */
    $winners_table='';
    $winners=array();
    foreach($users as $u => $v) {
      $winners[$u]=count($v['gotem']);
    }

    if (count($winners)>1) {
      arsort($winners);

      foreach($winners as $u => $n) {
        $winners_table.= '<tr> <td>'. $u .'</td> <td>'. $n .'</td> </tr> '.PHP_EOL;
      }
    }
    /* }}} */


  } else {
    // didn't find that username, unset the cookie and reload
    setcookie("bingo_player", "", time()-(60*60*24), "/");
    header("Location: index.php");
    die();

  }

}
/* }}} */

/* update_square {{{ */
if (posted('update_square')!='' && $logged_in==true) {
  $square = preg_replace("#[^0-9]#", '', $_POST['update_square']);
  $square = (int)$square;

  if (($key = array_search($square, $gotem)) !== false) {
    unset($gotem[$key]);
  } else {
    $gotem[]=$square;
  }

  $data = file_get_contents('bingo_players.json');
  $users = json_decode($data, true);
  $add_arr = array(
    'bingo_words' => $bingo_words,
    'gotem'       => $gotem
  );
  $users[$username] = $add_arr;
  file_put_contents('bingo_players.json', json_encode($users, JSON_PRETTY_PRINT));
}
/* }}} */

?>
<!DOCTYPE html>
<html lang="en">
<!-- head {{{ -->
<head>
  <meta charset="UTF-8">
  <title>Bingo</title>
  <link rel="shortcut icon" href="favicon.ico">
  <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Bungee&family=Montserrat&display=swap">
  <link rel="stylesheet" href="style.css">
  <script src="scripts.js"></script>
</head>
<!-- }}} -->
<!-- body {{{ -->
<body>
  <?php if ($logged_in==true): ?>

  <div class="wrap">
    <div class="logged_in">
      <div class="text-left">Logged In As: <?php echo $username; ?> </div>
      <div class="text-right"><a href="?logout=true">logout</a></div>
    </div>
    <div class="title"><h1><?php echo $game_title; ?></h1></div>
    <div class="bingo-card">
      <div id="square_0"  class="bingo_square<?php if (in_array(0, $gotem)) echo ' active'; ?>" onClick="update_square('square_0')" ><?php echo $words[$bingo_words[0]]; ?></div>
      <div id="square_1"  class="bingo_square<?php if (in_array(1, $gotem)) echo ' active'; ?>" onClick="update_square('square_1')" ><?php echo $words[$bingo_words[1]]; ?></div>
      <div id="square_2"  class="bingo_square<?php if (in_array(2, $gotem)) echo ' active'; ?>" onClick="update_square('square_2')" ><?php echo $words[$bingo_words[2]]; ?></div>
      <div id="square_3"  class="bingo_square<?php if (in_array(3, $gotem)) echo ' active'; ?>" onClick="update_square('square_3')" ><?php echo $words[$bingo_words[3]]; ?></div>
      <div id="square_4"  class="bingo_square<?php if (in_array(4, $gotem)) echo ' active'; ?>" onClick="update_square('square_4')" ><?php echo $words[$bingo_words[4]]; ?></div>
      <div id="square_5"  class="bingo_square<?php if (in_array(5, $gotem)) echo ' active'; ?>" onClick="update_square('square_5')" ><?php echo $words[$bingo_words[5]]; ?></div>
      <div id="square_6"  class="bingo_square<?php if (in_array(6, $gotem)) echo ' active'; ?>" onClick="update_square('square_6')" ><?php echo $words[$bingo_words[6]]; ?></div>
      <div id="square_7"  class="bingo_square<?php if (in_array(7, $gotem)) echo ' active'; ?>" onClick="update_square('square_7')" ><?php echo $words[$bingo_words[7]]; ?></div>
      <div id="square_8"  class="bingo_square<?php if (in_array(8, $gotem)) echo ' active'; ?>" onClick="update_square('square_8')" ><?php echo $words[$bingo_words[8]]; ?></div>
      <div id="square_9"  class="bingo_square<?php if (in_array(9, $gotem)) echo ' active'; ?>" onClick="update_square('square_9')" ><?php echo $words[$bingo_words[9]]; ?></div>
      <div id="square_10" class="bingo_square<?php if (in_array(10, $gotem)) echo ' active'; ?>" onClick="update_square('square_10')" ><?php echo $words[$bingo_words[10]]; ?></div>
      <div id="square_11" class="bingo_square<?php if (in_array(11, $gotem)) echo ' active'; ?>" onClick="update_square('square_11')" ><?php echo $words[$bingo_words[11]]; ?></div>
      <div class="bingo_square logo_square"> <img src="logo.png" class="logo" alt="bingo logo free square"> </div>
      <div id="square_12" class="bingo_square<?php if (in_array(12, $gotem)) echo ' active'; ?>" onClick="update_square('square_12')" ><?php echo $words[$bingo_words[12]]; ?></div>
      <div id="square_13" class="bingo_square<?php if (in_array(13, $gotem)) echo ' active'; ?>" onClick="update_square('square_13')" ><?php echo $words[$bingo_words[13]]; ?></div>
      <div id="square_14" class="bingo_square<?php if (in_array(14, $gotem)) echo ' active'; ?>" onClick="update_square('square_14')" ><?php echo $words[$bingo_words[14]]; ?></div>
      <div id="square_15" class="bingo_square<?php if (in_array(15, $gotem)) echo ' active'; ?>" onClick="update_square('square_15')" ><?php echo $words[$bingo_words[15]]; ?></div>
      <div id="square_16" class="bingo_square<?php if (in_array(16, $gotem)) echo ' active'; ?>" onClick="update_square('square_16')" ><?php echo $words[$bingo_words[16]]; ?></div>
      <div id="square_17" class="bingo_square<?php if (in_array(17, $gotem)) echo ' active'; ?>" onClick="update_square('square_17')" ><?php echo $words[$bingo_words[17]]; ?></div>
      <div id="square_18" class="bingo_square<?php if (in_array(18, $gotem)) echo ' active'; ?>" onClick="update_square('square_18')" ><?php echo $words[$bingo_words[18]]; ?></div>
      <div id="square_19" class="bingo_square<?php if (in_array(19, $gotem)) echo ' active'; ?>" onClick="update_square('square_19')" ><?php echo $words[$bingo_words[19]]; ?></div>
      <div id="square_20" class="bingo_square<?php if (in_array(20, $gotem)) echo ' active'; ?>" onClick="update_square('square_20')" ><?php echo $words[$bingo_words[20]]; ?></div>
      <div id="square_21" class="bingo_square<?php if (in_array(21, $gotem)) echo ' active'; ?>" onClick="update_square('square_21')" ><?php echo $words[$bingo_words[21]]; ?></div>
      <div id="square_22" class="bingo_square<?php if (in_array(22, $gotem)) echo ' active'; ?>" onClick="update_square('square_22')" ><?php echo $words[$bingo_words[22]]; ?></div>
      <div id="square_23" class="bingo_square<?php if (in_array(23, $gotem)) echo ' active'; ?>" onClick="update_square('square_23')" ><?php echo $words[$bingo_words[23]]; ?></div>
    </div>


    <div class="winners">
      <h2 class="text-center">WINNERS</h2>
      <table>
        <?php echo $winners_table; ?>
      </table>
    </div>

    
  </div> <!-- END class="wrap" -->

  <?php else: ?>

  <div class="login_box">
    <h2 class="text-center">Play <span class="kit"><?php echo $game_title; ?></span></h2>
    <form class="form" action="<?php echo $_SERVER['PHP_SELF']; ?>" enctype="multipart/form-data" method="post">
      <input class="login_field" type="text" name="username" placeholder="username"/>
      <input class="btn" name="user_login" type="submit" value="submit" id="submit" />
    </form>

  </div>

  <?php endif; ?>

  <a style="position: fixed; bottom:3px; right:3px; opacity: 0.3;" href="admin.php">admin</a>
</body>
<!-- }}} -->
</html>
