<?php get_header(); ?>

<?php novosti_breadcrumbs(); ?>

<main class="site-main">
<div class="container">

<?php if ( have_posts() ) : while ( have_posts() ) : the_post();

  $cats    = get_the_category();
  $cat     = $cats ? $cats[0] : null;

  $source  = get_post_meta(get_the_ID(), '_source_name', true);
  $src_url = get_post_meta(get_the_ID(), '_source_url', true);

?>

<article class="single-post">

  <?php if ($cat) : ?>
    <div class="single-post__cat">
      <a href="<?php echo esc_url(get_category_link($cat->term_id)); ?>">
        <?php echo esc_html($cat->name); ?>
      </a>
    </div>
  <?php endif; ?>

  <h1 class="single-post__title">
    <?php the_title(); ?>
  </h1>

  <div class="single-post__meta">
    <?php echo get_the_date('d F Y'); ?> · <?php the_author(); ?>

    <?php if ($source) : ?>
      · Источник:

      <?php if ($src_url) : ?>
        <a href="<?php echo esc_url($src_url); ?>" target="_blank" rel="nofollow noopener">
          <?php echo esc_html($source); ?>
        </a>
      <?php else : ?>
        <?php echo esc_html($source); ?>
      <?php endif; ?>

    <?php endif; ?>
  </div>

  <?php if ( has_post_thumbnail() ) : ?>
    <div class="single-post__thumb">
      <?php the_post_thumbnail( 'news-featured', array(
        'onerror' => "this.closest('.single-post__thumb').style.display='none';"
      ) ); ?>
    </div>
  <?php endif; ?>

  <div class="single-post__content">
    <?php the_content(); ?>
  </div>

</article>

<?php
$cat_ids = wp_get_post_categories(get_the_ID());

$related = get_posts(array(
  'post_type'      => 'post',
  'posts_per_page' => 3,
  'category__in'   => $cat_ids,
  'post__not_in'   => array(get_the_ID()),
));
?>

<?php if ($related) : ?>

<div class="section-wrap related-news">

  <div class="section-head">
    <span class="section-head__title">Похожие новости</span>
  </div>

  <hr class="section-divider">

  <div class="news-grid">

    <?php foreach ($related as $post) :

      setup_postdata($post);

      $rcats = get_the_category($post->ID);
      $rcat  = $rcats ? $rcats[0] : null;

    ?>

    <article class="news-card">

      <div class="news-card__thumb">
        <a href="<?php echo esc_url(get_permalink($post->ID)); ?>">

          <?php if (has_post_thumbnail($post->ID)) : ?>

            <?php
            echo get_the_post_thumbnail(
              $post->ID,
              'news-card',
              array(
                'onerror' => "this.style.display='none';"
              )
            );
            ?>

          <?php else : ?>

            <div style="width:100%;height:100%;background:#e8e8e8;"></div>

          <?php endif; ?>

        </a>
      </div>

      <div class="news-card__body">

        <?php if ($rcat) : ?>
          <div class="news-card__cat">
            <a href="<?php echo esc_url(get_category_link($rcat->term_id)); ?>">
              <?php echo esc_html($rcat->name); ?>
            </a>
          </div>
        <?php endif; ?>

        <h2 class="news-card__title">
          <a href="<?php echo esc_url(get_permalink($post->ID)); ?>">
            <?php echo esc_html(get_the_title($post->ID)); ?>
          </a>
        </h2>

        <div class="news-card__time">
          <?php echo novosti_time_ago($post->ID); ?>
        </div>

      </div>

    </article>

    <?php endforeach; ?>

    <?php wp_reset_postdata(); ?>

  </div>

</div>

<?php endif; ?>

<?php endwhile; endif; ?>

</div>
</main>

<?php get_footer(); ?>