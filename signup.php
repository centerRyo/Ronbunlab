<?php
// 共通変数・関数読み込み
require('function.php');

debug('「「「「「「「「「「「「「「「「「「「「「「');
debug('　ユーザー登録ページ　');
debug('「「「「「「「「「「「「「「「「「「「「「「');
debugLogStart();

if (!empty($_POST)) {
  $email = $_POST['email'];
  $pass = $_POST['pass'];
  $pass_re = $_POST['pass_re'];

  // 未入力チェック
  validRequired($email, 'email');
  validRequired($pass, 'pass');
  validRequired($pass_re, 'pass_re');

  if (empty($err_msg)) {
    // emailチェック
    validEmail($email, 'email');
    validMaxLen($email, 'email');
    validDup($email);

    // パスワードチェック
    validHalf($pass, 'pass');
    validMaxLen($pass, 'pass');
    validMinLen($pass, 'pass');

    // パスワード(再入力)チェック
    validMaxLen($pass_re, 'pass_re');
    validMinLen($pass_re, 'pass_re');

    if (empty($err_msg)) {
      validMatch($pass, $pass_re, 'pass_re');

      if (empty($err_msg)) {
        try {
          $dbh = dbConnect();
          $sql = 'INSERT INTO users (email, password, login_time, created_date) VALUES(:email, :pass, :login_time, :created_date)';
          $data = array(':email' => $email, 'pass' => password_hash($pass, PASSWORD_DEFAULT), ':login_time' => date('Y-m-d H:i:s'), 'created_date' => date('Y-m-d H:i:s'));
          $stmt = queryPost($dbh, $sql, $data);

          if ($stmt) {
            $sesLimit = 60 * 60;
            $_SESSION['login_date'] = time();
            $_SESSION['login_limit'] = $sesLimit;
            $_SESSION['user_id'] = $dbh->lastInsertId();

            debug('セッション変数の中身：'.print_r($_SESSION, true));

            header('Location:mypage.php');
          }

        } catch (Exception $e) {
          error_log('エラー発生:'.$e->getMessage());
          $err_msg['common'] = MSG08;
        }
      }
    }
  }
}

debug('画面表示処理終了 <<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<');
?>
<?php
require('head.php');
require('header.php');
?>

  <main>
    <section class="container">
      <h2 class="title">ユーザー登録</h2>
      <form class="form" action="" method="post">
        <div class="area-msg">
          <?php if(!empty($err_msg['common'])) echo $err_msg['common']; ?>
        </div>
        <label class="<?php if(!empty($err_msg['email']))  echo 'err'; ?>">
          メールアドレス
          <input type="text" name="email" value="<?php if(!empty($_POST['email'])) echo $_POST['email']; ?>">
        </label>
        <div class="area-msg">
          <?php if(!empty($err_msg['email'])) echo $err_msg['email']; ?>
        </div>
        <label class="<?php if(!empty($err_msg['pass']))  echo 'err'; ?>">
          パスワード(8文字以上半角英数字)
          <input type="password" name="pass">
        </label>
        <div class="area-msg">
          <?php if(!empty($err_msg['pass'])) echo $err_msg['pass']; ?>
        </div>
        <label class="<?php if(!empty($err_msg['pass_re']))  echo 'err'; ?>">
          パスワード(再入力)
          <input type="password" name="pass_re">
        </label>
        <div class="area-msg">
          <?php if(!empty($err_msg['pass_re'])) echo $err_msg['pass_re']; ?>
        </div>
        <div class="btn-container">
          <input type="submit" class="btn btn-submit" value="登録する">
        </div>
      </form>
    </section>
  </main>

  <?php
    require('footer.php');
  ?>
