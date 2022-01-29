<?php
session_start();
$msg=$bingo_words=$game_title='';

if (isset($_REQUEST['logout'])) {
	$msg.='<p class="msg success">You Have Successfully Logged Out</p>';
	session_destroy();
	header('Refresh: 3;url=admin.php');
}

if (isset($_POST['create_account'])) {
	$username=isset($_POST["username"]) ?  $_POST["username"] : '';
	$password=isset($_POST["password"]) ?  password_hash($_POST["password"], PASSWORD_DEFAULT) : '';

	if ($username=='' || $password=='') $msg.='<p class="msg error">Missing Required Info</p>';
	else {
		$data='<?php
$username=\''.$username.'\';
$password=\''.$password.'\';';

		$handle=fopen("login.php", "w");
		fwrite($handle, $data);
		fclose($handle);
		$_SESSION['admin_session']=$username;
	}
}

if (!file_exists('login.php')) {
  /* {{{ */
	$_SESSION['admin_session']='';
	$game_title='Create Admin';
    $page_body='
<div class="login_box">
  <h2 class="text-center">Create Admin</h2>
  '. $msg .'
  <form action="admin.php" enctype="multipart/form-data" method="post">
    <input class="login_field" type="text" name="username" placeholder="Username" />
    <input class="login_field" type="password" name="password" placeholder="Password" />
    <input type="hidden" name="create_account" />
    <input class="btn" type="submit" value="Submit" />
  </form>
</div>
';
  /* }}} */

} else {
	require_once "login.php";

	if (isset($_POST['login'])) {
    if ($_POST['username']==$username && password_verify($_POST["password"], $password)) {
      $_SESSION['admin_session']=$username;
    } else $msg='<p class="msg error">Username Or Password Incorrect</p>';
	}

	if (!isset($_SESSION['admin_session']) || $_SESSION['admin_session']=='') {
		$game_title='Log In';
		$page_body='
	<div class="login_box">
	  <h2 class="text-center">Login</h2>
	  '. $msg .'
	  <form action="admin.php" enctype="multipart/form-data" method="post">
		<input class="login_field" type="text" name="username" placeholder="Username" />
		<input class="login_field" type="password" name="password" placeholder="Password" />
		<input type="hidden" name="login" />
		<input class="btn" type="submit" value="Submit" />
	  </form>
	  <p style="opacity:.6">If you forget your password log into your hosting account and delete the "login.php" file</p>
	</div>
	';
	} else {
		if (isset($_POST['bingo_words'])) {
			$bingo_words=isset($_POST["bingo_words"]) ?  $_POST["bingo_words"] : '';
			$game_title=isset($_POST["game_title"]) ?  $_POST["game_title"] : '';

      $updates=array('bingo_words', 'game_title');
      foreach ($updates as $u) {
        $handle=fopen($u.'.txt', "w");
        fwrite($handle, stripslashes (${$u}));
        fclose($handle);
      }

      $msg.='<p class="msg success">Successfully Updated!</p>';

		} elseif (isset($_REQUEST['delete_users'])) {
      if (file_exists('bingo_players.json')) {
        unlink('bingo_players.json');
        $msg.='<p class="msg success">successfully deleted</p>';
      } else {
        $msg.='<p class="msg error">failed to delete</p>';
      }
		} elseif (isset($_REQUEST['remove_duplicates'])) {
			if (file_exists('bingo_words.txt')) {
				$lines=file('bingo_words.txt');
				$lines=array_unique($lines);
				file_put_contents('bingo_words.txt', implode($lines));
				$msg.='<p class="msg success">Duplicates successfully deleted</p>';
			}
		} // END POSTS LOGICS
	}  // END LOGGED IN LOGIC
}  // END LOGIN FILE CHECK

$bingo_words=file_get_contents('bingo_words.txt');
$game_title=file_get_contents('game_title.txt');
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title><?php echo $game_title; ?></title>
<link rel="stylesheet" type="text/css" href="style.css">
</head>
<body>
<?php
if (!isset($_SESSION['admin_session']) || $_SESSION['admin_session']=='') {
  echo $page_body;
} else {  echo $msg;
?>

  <div class="wrap">
    <h1 class="title"><?php echo $game_title; ?> Admin</h1>
    <form action="admin.php" enctype="multipart/form-data" method="post">
      <input type="text" name="game_title" style="width: 100%; border-radius: 0;" value="<?php echo $game_title; ?>" placeholder="Game Title (In Red Box)"/>
      <textarea name="bingo_words" style="height:500px; width: 100%; border-radius: 0;"><?php echo $bingo_words; ?></textarea>
      <input class="btn" type="submit" value="Update" />
    </form>

    <hr />

    <div class="logged_in">
      <div class="text-left"><a href="admin.php?remove_duplicates=1">Remove Duplicates</a></div>
      <div class="text-right"><a href="admin.php?delete_users=1">Delete Users</a></div>
    </div>


  </div>

<?php } ?>
</body>
</html>
