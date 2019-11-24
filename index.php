<?php
// 共通変数・関数読み込み
require('function.php');

debug('「「「「「「「「「「「「「「「「「「「「「「');
debug('　論文投稿ページ　');
debug('「「「「「「「「「「「「「「「「「「「「「「');
debugLogStart();

// 画面表示用データ取得
$currentPageNum = (!empty($_GET['p'])) ? $_GET['p'] : 1;
if (!is_int((int)$currentPageNum)) {
  debug('不正な値が入りました');
  header("Location:index.php");
}

$listSpan = 12;
$currentMinNum = ($currentPageNum -1 ) * $listSpan;
$dbRonbunData = getRonbunList($currentMinNum);
$dbCategoryData = getCategory();
debug('現在のページ：'.$currentPageNum);

debug('画面表示処理終了 <<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<');


require('head.php');
require('header.php');
?>

  <main class="container-wrapper">
    <div class="site-width">

        <section class="slider">
          <i class="fas fa-angle-left slider-nav slider-prev js-slider-prev"></i>
          <i class="fas fa-angle-right slider-nav slider-next js-slider-next"></i>
          <ul class="slider-container">
            <li class="slider-item"><img src="images/slider1.jpg"></li>
            <li class="slider-item"><img src="images/slider2.jpg"></li>
            <li class="slider-item"><img src="images/slider3.jpg"></li>
          </ul>
        </section>

        <section class="contents">
          <h2 class="title">記事一覧</h2>
          <div class="contents-wrapper">
            <?php
              foreach($dbRonbunData['data'] as $key => $val):
            ?>
              <a href="show.php?p_id=<?php echo $val['id'].'&p='.$currentPageNum; ?>">
                <div class="content">
                  <img src="<?php echo sanitize($val['image']); ?>" alt="<?php echo sanitize($val['title']); ?>" class="content-image">
                  <p><?php echo sanitize($val['title']); ?></p>
                </div>
              </a>
            <?php endforeach; ?>
          </div>

          <?php pagination($currentPageNum, $dbRonbunData['total_page']); ?>

          <div class="to_new">
            <a href="#">新着記事一覧</a>
          </div>
        </section>

        <section class="sidebar">
          <section class="pickup">
            <h2 class="pickup-title">注目記事</h2>
            <div class="pickup-content">
              <img src="images/content_example.jpg" alt="">
              <p>sampletext.sampletext.</p>
            </div>
            <div class="pickup-content">
              <img src="images/content_example.jpg" alt="">
              <p>sampletext.sampletext.</p>
            </div>
            <div class="pickup-content">
              <img src="images/content_example.jpg" alt="">
              <p>sampletext.sampletext.</p>
            </div>
            <div class="pickup-content">
              <img src="images/content_example.jpg" alt="">
              <p>sampletext.sampletext.</p>
            </div>
          </section>

          <section class="category">
            <ul class="category-wrapper">
              <li class="category-item"><a href="">科学</a></li>
              <li class="category-item"><a href="">スポーツ</a></li>
              <li class="category-item"><a href="">ビジネス</a></li>
              <li class="category-item"><a href="">心理学</a></li>
              <li class="category-item"><a href="">医学</a></li>
              <li class="category-item"><a href="">法律</a></li>
            </ul>
          </section>
        </section>
    </div>
  </main>

  <?php
    require('footer.php');
  ?>
