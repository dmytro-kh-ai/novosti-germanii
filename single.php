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
$current_post_id = get_the_ID();
$related         = novosti_get_related_posts( $current_post_id, 6 );
$related_ids     = wp_list_pluck( $related, 'ID' );
$category_more   = novosti_get_more_from_primary_category( $current_post_id, 4, $related_ids );
$link_block_ids  = array_merge( array( $current_post_id ), $related_ids, wp_list_pluck( $category_more, 'ID' ) );
$latest_more     = get_posts( array(
  'post_type'           => 'post',
  'post_status'         => 'publish',
  'posts_per_page'      => 4,
  'category__not_in'    => novosti_get_special_category_ids(),
  'post__not_in'        => array_values( array_unique( array_map( 'intval', $link_block_ids ) ) ),
  'ignore_sticky_posts' => true,
  'no_found_rows'       => true,
) );
?>

<?php if ($related) : ?>

<div class="section-wrap related-news">

  <div class="section-head">
    <h2 class="section-head__title">Похожие новости</h2>
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

<?php
novosti_render_link_list( 'Ещё по теме', $category_more, 'internal-links--topic' );
novosti_render_link_list( 'Последние новости Германии', $latest_more, 'internal-links--latest' );
?>

<?php endwhile; endif; ?>

</div>
</main>

<?php get_footer(); ?>
