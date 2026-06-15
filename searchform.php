<form role="search" method="get" class="search-form" action="<?php echo esc_url(home_url('/')); ?>">
  <input type="search" class="search-field" placeholder="Поиск новостей…"
    value="<?php echo get_search_query(); ?>" name="s" aria-label="Поиск">
  <button type="submit">🔍 Найти</button>
</form>
