<?php get_header(); ?>

<?php novosti_breadcrumbs(); ?>

<main class="site-main">
<div class="container">

<?php
$author = get_queried_object();
$author_id = $author instanceof WP_User ? (int) $author->ID : 0;
$author_name = $author_id ? get_the_author_meta( 'display_name', $author_id ) : get_the_archive_title();
$author_bio = $author_id ? get_the_author_meta( 'description', $author_id ) : '';
?>

<article class="single-post author-profile">
  <div class="author-profile__head">
    <?php if ( $author_id ) : ?>
      <?php echo get_avatar( $author_id, 120, '', esc_attr( $author_name ), array( 'class' => 'author-profile__avatar' ) ); ?>
    <?php endif; ?>

    <div>
      <h1 class="single-post__title"><?php echo esc_html( $author_name ); ?></h1>
      <p class="author-profile__role">Автор сайта «<?php bloginfo( 'name' ); ?>»</p>
    </div>
  </div>

  <div class="single-post__content">
    <h2>Биография</h2>
    <p>
      <?php
      echo esc_html(
        $author_bio
          ? $author_bio
          : 'Автор публикует материалы о Германии, важных общественных событиях, изменениях для жителей страны и темах, связанных с повседневной жизнью русскоязычной аудитории.'
      );
      ?>
    </p>

    <h2>Публикации автора</h2>
  </div>
</article>

<?php if ( have_posts() ) : ?>
  <div class="news-grid">
    <?php while ( have_posts() ) : the_post();
      $cats = get_the_category();
      $cat  = $cats ? $cats[0] : null;
    ?>
      <article class="news-card">
        <div class="news-card__thumb">
          <a href="<?php the_permalink(); ?>">
            <?php if ( has_post_thumbnail() ) :
              the_post_thumbnail( 'news-card', array(
                'onerror' => "this.style.display='none';this.closest('.news-card__thumb').classList.add('is-empty');"
              ) );
            else : ?>
              <div style="width:100%;height:100%;background:#e8e8e8;"></div>
            <?php endif; ?>
          </a>
        </div>

        <div class="news-card__body">
          <?php if ( $cat ) : ?>
            <div class="news-card__cat">
              <a href="<?php echo esc_url( get_category_link( $cat->term_id ) ); ?>">
                <?php echo esc_html( $cat->name ); ?>
              </a>
            </div>
          <?php endif; ?>

          <h2 class="news-card__title">
            <a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
          </h2>

          <div class="news-card__time"><?php echo novosti_time_ago(); ?></div>
        </div>
      </article>
    <?php endwhile; ?>
  </div>

  <div style="margin-top:20px;">
    <?php the_posts_pagination( array( 'mid_size' => 2 ) ); ?>
  </div>
<?php else : ?>
  <p style="color:#888;padding:20px 0;">Публикации автора пока не найдены.</p>
<?php endif; ?>

</div>
</main>

<?php get_footer(); ?>
