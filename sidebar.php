<section class="sidebar">
  <section class="pickup">
    <h2 class="pickup-title">注目記事</h2>
    <?php foreach($dbRonbunData['data'] as $key => $val): ?>
      <div class="pickup-content">
        <img src="<?php echo sanitize($val['image']); ?>" alt="<?php echo sanitize($val['title']); ?>">
        <p style="width:200px"><?php echo sanitize($val['title']); ?></p>
      </div>
    <?php endforeach; ?>
  </section>

  <section class="category">
    <ul class="category-wrapper">
      <?php
        foreach ($dbCategoryData as $key => $val):
      ?>
        <li class="category-item"><a href="index.php?c_id=<?php echo $val['id'] ?>"><?php echo $val['name']; ?></a></li>
      <?php endforeach; ?>
    </ul>
  </section>
</section>
