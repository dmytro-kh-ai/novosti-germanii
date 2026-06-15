<?php get_header(); ?>

<main class="site-main">
<div class="container">

  <div class="section-wrap" style="text-align:center;padding:40px 20px;">
    <div style="font-size:72px;line-height:1;margin-bottom:16px;">📰</div>
    <h1 style="font-size:28px;color:#1a1a2e;margin-bottom:10px;">Страница не найдена</h1>
    <p style="color:#666;font-size:15px;margin-bottom:24px;">Возможно, материал был удалён или вы перешли по устаревшей ссылке.</p>
    <a href="<?php echo esc_url(home_url('/')); ?>" style="display:inline-block;background:#cc0000;color:#fff;padding:10px 24px;border-radius:4px;font-weight:700;font-size:14px;">← На главную</a>
  </div>

  <?php
  $exclude = array();
  foreach ( array('reklama','partner','afisha') as $s ) {
    $c = get_category_by_slug($s);
    if ($c) $exclude[] = $c->term_id;
  }
  $latest = get_posts(array('post_type'=>'post','posts_per_page'=>6,'category__not_in'=>$exclude));
  if ($latest) : ?>
  <div class="section-wrap">
    <div class="section-head">
      <span class="section-head__title">Последние новости</span>
      <a class="section-head__link" href="<?php echo esc_url(home_url('/')); ?>">На главную →</a>
    </div>
    <hr class="section-divider">
    <div class="news-grid">
      <?php foreach ($latest as $post) :
        $cats = get_the_category($post->ID);
        $cat  = $cats ? $cats[0] : null;
      ?>
      <article class="news-card">
        <div class="news-card__thumb">
          <a href="<?php echo esc_url(get_permalink($post->ID)); ?>">
            <?php if (has_post_thumbnail($post->ID)) echo get_the_post_thumbnail($post->ID,'news-card');
            else echo '<div style="width:100%;height:100%;background:#e8e8e8;"></div>'; ?>
          </a>
        </div>
        <div class="news-card__body">
          <?php if ($cat) : ?>
            <div class="news-card__cat"><a href="<?php echo esc_url(get_category_link($cat->term_id)); ?>"><?php echo esc_html($cat->name); ?></a></div>
          <?php endif; ?>
          <h2 class="news-card__title"><a href="<?php echo esc_url(get_permalink($post->ID)); ?>"><?php echo esc_html(get_the_title($post->ID)); ?></a></h2>
          <div class="news-card__time"><?php echo novosti_time_ago($post->ID); ?></div>
        </div>
      </article>
      <?php endforeach; ?>
    </div>
  </div>
  <?php endif; ?>

</div>
</main>

<?php get_footer(); ?>
