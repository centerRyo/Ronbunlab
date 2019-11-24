<header class="header">
  <div class="site-width">
    <h1 class="title"><a href="index.php">論文ラボ</a></h1>

    <nav class="nav-menu">
      <ul class="menu">
        <?php if (empty($_SESSION['user_id'])) { ?>

          <li class="menu-item"><a href="login.php">ログイン</a></li>
          <li class="menu-item"><a href="signup.php">ユーザー登録</a></li>

        <?php } else { ?>

          <li class="menu-item"><a href="mypage.php">マイページ</a></li>
          <li class="menu-item"><a href="logout.php">ログアウト</a></li>

        <?php } ?>
      </ul>
    </nav>
  </div>
</header>
