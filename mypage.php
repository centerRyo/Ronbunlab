<?php
// 共通変数・関数読み込み
require('function.php');

debug('「「「「「「「「「「「「「「「「「「「「「「');
debug('　マイページ　');
debug('「「「「「「「「「「「「「「「「「「「「「「');
debugLogStart();

// 認証関数読み込み
require('auth.php');

$u_id = $_SESSION['user_id'];

$myUserInfo = getUser($u_id);
$ronbunData = getMyRonbun($u_id);
$favoriteData = getFavorite($u_id);

debug('取得したユーザーデータ：'.print_r($myUserInfo, true));
debug('取得した論文データ：'.print_r($ronbunData, true));
debug('取得したお気に入りデータ：'.print_r($favoriteData, true));

debug('画面表示処理終了 <<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<');
?>
<?php
require('head.php');
require('header.php');
?>

  <p id="js-show-msg" style="display:none" class="msg-slide">
    <?php echo getSessionFlash('msg_success') ?>
  </p>

  <main class="container-wrapper">
    <div class="profile-container">
      <section class="site-width">
        <section class="profile">
          <div class="profile-img">
            <img src="<?php echo sanitize($myUserInfo['pic']) ?>" alt="">
          </div>
          <div class="profile-info">
            <h2><?php echo sanitize($myUserInfo['username']); ?></h2>
            <div class="share-icon">
              <a href="https://ja-jp.facebook.com/"><i class="fab fa-facebook-square"></i></a>
              <a href="https://twitter.com/"><i class="fab fa-twitter-square"></i></a>
              <a href="https://www.instagram.com/?hl=ja"><i class="fab fa-instagram-square"></i></a>
            </div>
          </div>
        </section>
      </section>
    </div>

    <div class="site-width">
      <div class="contents-container">
        <section class="contents">
          <h2 class="title">登録論文一覧</h2>
          <div class="contents-wrapper">
            <?php
              if(!empty($ronbunData)):
                foreach($ronbunData as $key => $ronbun):
            ?>
              <div class="content">
                <a href="show.php?r_id=<?php echo $ronbun['id']; ?>">
                  <img src="<?php echo showImg(sanitize($ronbun['image'])); ?>" alt="<?php echo sanitize($ronbun['title']) ?>" class="content-image">
                  <p><?php echo sanitize($ronbun['title']) ?></p>
                </a>
                <div class="btn-container">
                  <a href="registerRonbun.php?r_id=<?php echo $ronbun['id']; ?>" class="btn-edit">編集する</a>
                  <a href="deleteRonbun.php?r_id=<?php echo $ronbun['id']; ?>" class="btn-delete">削除する</a>
                </div>
              </div>
            <?php
                endforeach;
              endif;
            ?>
          </div>
        </section>

        <section class="contents">
          <h2 class="title">お気に入り論文</h2>
          <div class="contents-wrapper">
            <?php
              if(!empty($favoriteData)):
                foreach($favoriteData as $key => $favorite):
            ?>
            <div class="content">
              <a href="show.php?r_id=<?php echo sanitize($favorite['ronbun_id']); ?>">
                <img src="<?php echo showImg(sanitize($favorite['image'])); ?>" alt="<?php echo sanitize($favorite['title']); ?>" class="content-image">
                <p><?php echo sanitize($favorite['title']); ?></p>
              </a>
            </div>
            <?php
                endforeach;
              endif;
            ?>
          </div>
        </section>
      </div>

      <!-- サイドバー(マイページ) -->
      <?php require('sidebar_mypage.php'); ?>
    </div>

  </main>

<?php
require('footer.php');
?>
