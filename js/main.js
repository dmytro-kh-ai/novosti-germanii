// Часы и дата
function updateDateTime() {
  var now = new Date();
  var days = ['Вс.','Пн.','Вт.','Ср.','Чт.','Пт.','Сб.'];
  var months = ['Янв','Фев','Мар','Апр','Мая','Июн','Июл','Авг','Сен','Окт','Ноя','Дек'];
  var dateEl = document.getElementById('js-date');
  var timeEl = document.getElementById('js-time');
  if (dateEl) dateEl.textContent = days[now.getDay()] + ' ' + months[now.getMonth()] + ' ' + now.getDate() + ', ' + now.getFullYear();
  if (timeEl) {
    var h = String(now.getHours()).padStart(2,'0');
    var m = String(now.getMinutes()).padStart(2,'0');
    timeEl.textContent = h + ':' + m;
  }
}
updateDateTime();
setInterval(updateDateTime, 60000);

// Бургер-меню
var burger = document.getElementById('js-burger');
var menu   = document.getElementById('js-menu');
if (burger && menu) {
  burger.addEventListener('click', function() {
    menu.classList.toggle('is-open');
  });
  // Закрываем при клике вне меню
  document.addEventListener('click', function(e) {
    if (!burger.contains(e.target) && !menu.contains(e.target)) {
      menu.classList.remove('is-open');
    }
  });
}

// Карусель баннеров
var slides = document.querySelectorAll('.banner-carousel__slide');
var dots   = document.querySelectorAll('.banner-carousel__dot');

if (slides.length > 1) {
  var current = 0;

  function goToSlide(n) {
    slides[current].classList.remove('is-active');
    if (dots[current]) dots[current].classList.remove('is-active');

    current = n % slides.length;

    slides[current].classList.add('is-active');
    if (dots[current]) dots[current].classList.add('is-active');
  }

  dots.forEach(function(dot) {
    dot.addEventListener('click', function() {
      goToSlide(parseInt(this.dataset.index, 10) || 0);
    });
  });

  setInterval(function() {
    goToSlide(current + 1);
  }, 10000);
}
