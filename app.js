// =======================================
// 画像スライダー
// =======================================
let currentItemNum = 1;
let $slideContainer = $('.slider-container');
let slideItemNum = $('.slider-item').length;
let slideItemWidth = $('.slider-item').innerWidth();
let slideContainerWidth = slideItemNum * slideItemWidth;
const DURATION = 500;

$slideContainer.attr('style', 'width:' + slideContainerWidth + 'px');

$('.js-slider-prev').on('click', function() {
  if (currentItemNum > 1) {
    $slideContainer.animate({left: '+=' + slideItemWidth + 'px'}, DURATION);
    currentItemNum--;
  }
});

$('.js-slider-next').on('click', function() {
  if (currentItemNum < slideItemNum) {
    $slideContainer.animate({left: '-=' + slideItemWidth + 'px'}, DURATION);
    currentItemNum++;
  }
});

// =======================================
// フッター高さ調節
// =======================================
let $ftr = $('.footer');
if (window.innerHeight > $ftr.offset().top + $ftr.outerHeight()) {
  $ftr.attr({'style': 'position:fixed; top:' + (window.innerHeight - $ftr.outerHeight()) + 'px;'});
}

// =======================================
// 文字数カウント
// =======================================
let $countUp = $('#js-count-text');
    $countView = $('.js-count-view');
$countUp.on('keyup', function () {
  $countView.html($(this).val().length);
});

// =======================================
// 画像プレビュー
// =======================================
let $dropArea = $('.js-area-drop');
let $fileInput = $('.js-input-file');
$dropArea.on('dragover', function(e){
  e.stopPropagation();
  e.preventDefault();
  $(this).css('border', '3px #ccc dashed');
});
$dropArea.on('dragleave', function(e){
  e.stopPropagation();
  e.preventDefault();
  $(this).css('border', 'none');
});

$fileInput.on('change', function(e) {
  $dropArea.css('border', 'none');
  let file = this.files[0],
      $img = $(this).siblings('.js-prev-img'),
      fileReader = new FileReader();

  fileReader.onload = function(event) {
    $img.attr('src', event.target.result).show();
  };

  fileReader.readAsDataURL(file);
});
