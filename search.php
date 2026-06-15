<?php get_header(); ?>

<?php novosti_breadcrumbs(); ?>

<main class="site-main">
<div class="container">

<div class="section-wrap">
  <div class="section-head">
    <span class="section-head__title">
      <?php if (have_posts()) echo 'Результаты поиска: «' . get_search_query() . '»';
      else echo 'По запросу «' . get_search_query() . '» ничего не найдено'; ?>
    </span>
  </div>
  <hr class="section-divider">

  <div style="margin-bottom:20px;"><?php get_search_form(); ?></div>

  <?php if ( have_posts() ) : ?>
    <div class="news-grid">
      <?php while ( have_posts() ) : the_post();
        $cats = get_the_category();
        $cat  = $cats ? $cats[0] : null;
      ?>
      <article class="news-card">
        <div class="news-card__thumb">
          <a href="<?php the_permalink(); ?>">
            <?php if (has_post_thumbnail()) the_post_thumbnail('news-card');
            else echo '<div style="width:100%;height:100%;background:#e8e8e8;"></div>'; ?>
          </a>
        </div>
        <div class="news-card__body">
          <?php if ($cat) : ?>
            <div class="news-card__cat"><a href="<?php echo esc_url(get_category_link($cat->term_id)); ?>"><?php echo esc_html($cat->name); ?></a></div>
          <?php endif; ?>
          <h2 class="news-card__title"><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h2>
          <div class="news-card__time"><?php echo novosti_time_ago(); ?></div>
        </div>
      </article>
      <?php endwhile; ?>
    </div>
    <div style="margin-top:20px;"><?php the_posts_pagination(array('mid_size'=>2)); ?></div>
  <?php else : ?>
    <p style="color:#888;padding:20px 0;">Попробуйте изменить запрос или <a href="<?php echo esc_url(home_url('/')); ?>">вернитесь на главную</a>.</p>
  <?php endif; ?>
</div>

</div>
</main>

<?php get_footer(); ?>
