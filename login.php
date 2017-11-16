<?php include 'lib.php';
$in = testLogin();
print_header("Login");

?>
<div class="grid_10 suffix_1 prefix_1">
  <div id="gatherling_main" class="box">
    <div class="uppertitle"> Login to Gatherling </div>
<?php if (isset($_POST['mode'])) { print_loginFailed(); } ?>
<form action="login.php" method="post">
<table class="form" align="center" style="border-width: 0px" cellpadding="3">
<tr><th>MTGO Username</th>
<td><input type="text" name="username" value=""></td></tr>
<tr><th>Gatherling Password</th>
<td><input type="password" name="password" value="">
</td></tr>
<tr><td>&nbsp;</td></tr>
<tr><td colspan="2" class="buttons">
<input type="submit" name="mode" value="Log In"><br />
Please <a href="register.php">Click Here</a> if you need to register.
</td> </tr>
</table>
</form>

</div> <!-- gatherling_main -->
</div> <!-- grid 10 pre 1 suff 1 -->

<?php print_footer(); ?>

</div> <!-- container -->

</body>
</html>
<?php
function print_loginFailed() {
  echo "<span class=\"error\"><center>Incorrect username or password. Please try again.\n";
  echo "</center></span><br />";
}

function testLogin() {
  $success = 0;
  if(isset($_POST['username']) && isset($_POST['password'])) {
    $auth = Player::checkPassword($_POST['username'], $_POST['password']);
    session_start();
    // The commented code below allows Jamuraa to su into any user without a password.  
    // This should be refactored to work for any admin, or be removed alltogether.
    if ($auth /* || (array_key_exists('username', $_SESSION) && $_SESSION['username'] == 'jamuraa') */) {
      header("Cache-control: private");
      $_SESSION['username'] = $_POST['username'];
      header("location: player.php");
      $success = 1;
    }
  }
  return $success;
}
?>
