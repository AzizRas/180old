/* ============================================================
   180 kun — фронтенд. Без сборки, без зависимостей.
   ============================================================ */
'use strict';

/* ---------------- i18n ---------------- */
const T = {
ru: {
  // вход
  tg_login:'Войти через Telegram', tg_wait:'Ждём подтверждения в Telegram…',
  tg_open:'Открыть Telegram', tg_hint:'Одно нажатие «Запустить» в боте — и вы внутри. Ни номера, ни пароля, ни SMS.',
  or_sms:'или по SMS', by_sms:'Войти по SMS',
  city:'Город', city_why:'Нужен, чтобы собрать группу рядом с тобой',
  fmt_offline:'Встречи вживую', fmt_online:'Встречи онлайн',
  fmt_offline_d:'В группе {n} человека из одного города — можно собираться лично',
  fmt_online_d:'Участники в разных городах — созвон',
  map:'Карта', quests:'Квесты', items:'Артефакты', class_:'Класс',
  place_open:'Открыто', place_locked:'Закрыто', go_here:'Прийти сюда', you_here:'Ты здесь',
  new_place:'Открыта новая локация', week_focus:'Фокус недели',
  ai_made:'задания подобраны лично под тебя',
  phone:'Номер телефона', code:'Код из SMS', getcode:'Получить код', enter:'Войти',
  codesent:'Код отправлен на {p}', resend:'Отправить снова', resend_in:'Отправить снова через {n} с',
  yourname:'Как тебя зовут?', cont:'Продолжить', wrongcode:'Неверный код', back:'Назад',
  agree:'Продолжая, ты соглашаешься с правилами сообщества',
  dev_code:'Код (режим разработки): {c}',
  // старт
  st_t:'Одна цель на 180 дней', st_goal:'Что ты хочешь изменить?',
  st_goal_ph:'Например: похудеть на 10 кг и начать бегать',
  st_cat:'К чему это ближе?', st_min:'Сколько минут в день реально можешь?',
  st_min_note:'Не в идеальный день, а в обычный. Это можно поменять в любой момент.',
  st_go:'Начать путь', st_priv:'Твою цель увидишь только ты и коуч. Группе видно лишь направление.',
  // навигация
  today:'Сегодня', world:'Мир', squad:'Группа', people:'Люди', coach:'Коуч', hero:'Герой', photos:'Журнал',
  // сегодня
  day:'День', tasks_today:'Задания на сегодня', all_done:'Всё на сегодня закрыто.',
  yesterday_left:'Вчера осталось — можно догнать', streak:'Серия', week:'Неделя',
  points:'Очки', done:'Сделано', intensity:'Интенсивность плана',
  last30:'Последние 30 дней', all180:'Все 180 дней', roadmap:'Карта пути', good_days:'дней в плюсе',
  crisis_badge:'Кризисный день', my_goal:'Моя цель', share:'Поделиться',
  // коуч
  coach_ph:'Написать коучу…', checkin_t:'Ты остановился на несколько дней',
  checkin_p:'Это не провал и не потеря прогресса. Скажи, что случилось — я перестрою план.',
  ai_off:'Коуч сейчас работает в базовом режиме.', answer:'Ответить', skip:'Пропустить',
  // группа
  squad_none_t:'У тебя ещё нет группы', squad_none_p:'Пять человек, каждый со своей целью. Вместе легче не исчезнуть.',
  squad_create:'Создать группу', squad_join:'Войти по коду', squad_auto:'Подобрать мне группу',
  code_:'Код группы', invite:'Код приглашения', team_week:'Цель группы на неделю',
  team_avg:'Средний прогресс', improved:'Больше всех выросли', nudge:'Позвать',
  chat_ph:'Написать группе…', leave:'Выйти из группы', board:'Таблица групп',
  meetings:'Встречи', rsvp_yes:'Буду', rsvp_no:'Не смогу', rsvp_maybe:'Может быть',
  checkin_btn:'Я на встрече', checked:'Отмечен', online_m:'Онлайн', offline_m:'Вживую',
  agenda:'О чём говорим', will_be:'Будут', edit_meet:'Изменить встречу',
  place:'Место', link:'Ссылка', save:'Сохранить',
  // мир
  energy:'Энергия', build:'Построить', built:'Построено', locked:'Закрыто',
  need_stat:'Нужно {n} по «{s}»', expedition:'Экспедиция группы', camp:'Общий лагерь',
  days_left:'дней осталось', last_day:'последний день', at_camp:'У костра', world_hint:'Энергия приходит только за реальные дела. Купить её нельзя.',
  choose_cell:'Выбери клетку', no_energy:'Не хватает энергии',
  // люди
  ppl_active:'Сейчас в пути', ppl_new:'Только начали', ppl_far:'Дальше всех', ppl_city:'Мой город',
  feed:'Что происходит', cheer:'Поддержать', walking:'идут прямо сейчас',
  // профиль
  profile:'Профиль', level:'Уровень', xp:'Опыт', comebacks:'Возвращений', best:'Лучшая серия',
  ach:'Достижения', no_ach:'Пока пусто. Первое появится на 7-й день.',
  health:'Здоровье', discipline:'Дисциплина', mind:'Разум', business:'Дело', soul:'Душа',
  report:'Пожаловаться', report_t:'На что жалуешься?', report_note:'Что произошло (необязательно)',
  report_send:'Отправить жалобу', report_ok:'Жалоба отправлена. Модератор посмотрит.',
  edit:'Редактировать', bio_ph:'Одна строка о себе', city_ph:'Город', avatar:'Фото профиля',
  since:'В пути с', direction:'Направление',
  // журнал
  ph_t:'Журнал', ph_add:'Добавить фото', ph_cap:'Подпись (необязательно)',
  ph_vis:'Кто видит', ph_priv:'Только я', ph_squad:'Группа', ph_pub:'Все',
  ph_empty:'Здесь будут твои доказательства. Через 90 дней они будут стоить дороже любых слов.',
  ph_review:'Разбор от коуча', ph_del:'Удалить',
  // общее
  logout:'Выйти', err:'Не получилось. Попробуй ещё раз.', saved:'Сохранено',
  ok_:'Готово', cancel:'Отмена', open:'Открыть', close:'Закрыть', more:'Ещё',
  // замки разделов
  soon_t:'Что откроется дальше', opens_d:'Откроется на {n}-й день',
  opens_tomorrow:'Откроется завтра', locked_h:'Раздел пока закрыт',
  why_lock:'Мы открываем по одному разделу в день: первая неделя — самая тяжёлая, и заваливать тебя всем сразу нечестно. Всё это уже готово и ждёт тебя.',
  all_sections:'Все разделы', constitution:'Конституция', day_n:'{n}-й день',
  lang_:'Язык', lang_note:'Коуч и задания тоже перейдут на выбранный язык. Уже написанное останется как есть.',
  access_t:'Доступ по донату', trial_left:'Пробный доступ ещё {n} дн.',
  gate_p:'Проект живёт на поддержке участников. Сделай донат — и путь откроется.',
  gate_after:'После перевода нажми кнопку и напиши последние 4 цифры карты. Подтвердим вручную.',
  gate_claim:'Я сделал донат', promo:'Промокод', apply:'Применить',
  pending:'Заявка отправлена. Обычно подтверждаем в течение дня.',
},
uz: {
  tg_login:'Telegram orqali kirish', tg_wait:'Telegramda tasdiqlashni kutyapmiz…',
  tg_open:'Telegramni ochish', tg_hint:"Botda «Start» ni bir marta bosing — va ichkaridasiz. Raqam ham, parol ham, SMS ham kerak emas.",
  or_sms:'yoki SMS orqali', by_sms:'SMS orqali kirish',
  city:'Shahar', city_why:'Yoningdagi jamoani yig\'ish uchun kerak',
  fmt_offline:'Jonli uchrashuvlar', fmt_online:'Onlayn uchrashuvlar',
  fmt_offline_d:'Jamoada bir shahardan {n} kishi bor — jonli yig\'ilsa bo\'ladi',
  fmt_online_d:'Ishtirokchilar turli shaharlarda — video suhbat',
  map:'Xarita', quests:'Kvestlar', items:'Artefaktlar', class_:'Sinf',
  place_open:'Ochiq', place_locked:'Yopiq', go_here:'Bu yerga borish', you_here:'Sen shu yerdasan',
  new_place:'Yangi joy ochildi', week_focus:'Hafta fokusi',
  ai_made:'vazifalar shaxsan sen uchun tanlangan',
  phone:'Telefon raqami', code:'SMS dagi kod', getcode:'Kod olish', enter:'Kirish',
  codesent:'Kod {p} raqamiga yuborildi', resend:'Qayta yuborish', resend_in:'{n} soniyadan keyin qayta yuborish',
  yourname:'Isming nima?', cont:'Davom etish', wrongcode:"Kod noto'g'ri", back:'Orqaga',
  agree:'Davom etsang, hamjamiyat qoidalariga rozi bo\'lasan',
  dev_code:'Kod (test rejimi): {c}',
  st_t:"180 kunga bitta maqsad", st_goal:"Nimani o'zgartirmoqchisan?",
  st_goal_ph:"Masalan: 10 kg ozish va yugurishni boshlash",
  st_cat:'Bu nimaga yaqinroq?', st_min:'Kuniga necha daqiqa ajrata olasan?',
  st_min_note:'Ideal kunda emas, oddiy kunda. Buni istalgan payt o\'zgartirsa bo\'ladi.',
  st_go:"Yo'lni boshlash", st_priv:'Maqsadingni faqat sen va murabbiy ko\'radi. Jamoaga faqat yo\'nalish ko\'rinadi.',
  today:'Bugun', world:'Dunyo', squad:'Jamoa', people:'Odamlar', coach:'Murabbiy', hero:'Qahramon', photos:'Kundalik',
  day:'Kun', tasks_today:'Bugungi vazifalar', all_done:'Bugungi hammasi bajarildi.',
  yesterday_left:'Kechadan qoldi — hozir yopsa bo\'ladi', streak:'Ketma-ketlik', week:'Hafta',
  points:'Ochko', done:'Bajarildi', intensity:'Reja intensivligi',
  last30:'Oxirgi 30 kun', all180:'Barcha 180 kun', roadmap:"Yo'l xaritasi", good_days:'muvaffaqiyatli kun',
  crisis_badge:'Inqiroz kuni', my_goal:'Mening maqsadim', share:'Ulashish',
  coach_ph:'Murabbiyga yozish…', checkin_t:"Sen bir necha kun to'xtading",
  checkin_p:"Bu mag'lubiyat emas. Nima bo'lganini ayt — rejani qayta quraman.",
  ai_off:'Murabbiy hozir asosiy rejimda ishlamoqda.', answer:'Javob berish', skip:"O'tkazish",
  squad_none_t:"Sizda hali jamoa yo'q", squad_none_p:"Besh kishi, har birining o'z maqsadi bor.",
  squad_create:'Jamoa yaratish', squad_join:'Kod bilan kirish', squad_auto:'Menga jamoa tanlang',
  code_:'Jamoa kodi', invite:'Taklif kodi', team_week:'Haftalik jamoa maqsadi',
  team_avg:"O'rtacha natija", improved:"Eng ko'p o'sganlar", nudge:'Chaqirish',
  chat_ph:'Jamoaga yozish…', leave:'Jamoadan chiqish', board:'Jamoalar jadvali',
  meetings:'Uchrashuvlar', rsvp_yes:'Boraman', rsvp_no:'Ulgurmayman', rsvp_maybe:'Balki',
  checkin_btn:'Men uchrashuvdaman', checked:'Belgilandi', online_m:'Onlayn', offline_m:'Jonli',
  agenda:'Nima haqida gaplashamiz', will_be:'Boradiganlar', edit_meet:"Uchrashuvni o'zgartirish",
  place:'Joy', link:'Havola', save:'Saqlash',
  energy:'Energiya', build:'Qurish', built:'Qurilgan', locked:'Yopiq',
  need_stat:'«{s}» bo\'yicha {n} kerak', expedition:'Jamoa ekspeditsiyasi', camp:'Umumiy lager',
  days_left:'kun qoldi', last_day:'oxirgi kun', at_camp:'Gulxan yonida', world_hint:'Energiya faqat haqiqiy ishlar uchun keladi. Uni sotib bo\'lmaydi.',
  choose_cell:'Katakni tanla', no_energy:'Energiya yetmaydi',
  ppl_active:"Hozir yo'lda", ppl_new:'Endi boshlaganlar', ppl_far:'Eng uzoqqa ketganlar', ppl_city:'Mening shahrim',
  feed:'Nimalar bo\'lyapti', cheer:'Qo\'llab-quvvatlash', walking:'hozir yo\'lda',
  profile:'Profil', level:'Daraja', xp:'Tajriba', comebacks:'Qaytishlar', best:'Eng yaxshi ketma-ketlik',
  ach:'Yutuqlar', no_ach:"Hozircha bo'sh. Birinchisi 7-kunda.",
  health:"Sog'liq", discipline:'Intizom', mind:'Aql', business:'Ish', soul:'Ruh',
  report:'Shikoyat', report_t:'Nimadan shikoyat qilasiz?', report_note:'Nima bo\'ldi (majburiy emas)',
  report_send:'Shikoyat yuborish', report_ok:'Shikoyat yuborildi. Moderator ko\'radi.',
  edit:'Tahrirlash', bio_ph:"O'zing haqingda bir qator", city_ph:'Shahar', avatar:'Profil surati',
  since:"Yo'lda", direction:"Yo'nalish",
  ph_t:'Kundalik', ph_add:"Surat qo'shish", ph_cap:'Izoh (majburiy emas)',
  ph_vis:"Kim ko'radi", ph_priv:'Faqat men', ph_squad:'Jamoa', ph_pub:'Hamma',
  ph_empty:"Bu yerda dalillaring bo'ladi. 90 kundan keyin ular har qanday so'zdan qimmatroq bo'ladi.",
  ph_review:'Murabbiy tahlili', ph_del:"O'chirish",
  logout:'Chiqish', err:"Bo'lmadi. Yana urinib ko'ring.", saved:'Saqlandi',
  ok_:'Tayyor', cancel:'Bekor', open:'Ochish', close:'Yopish', more:'Yana',
  soon_t:'Keyin nima ochiladi', opens_d:'{n}-kuni ochiladi',
  opens_tomorrow:'Ertaga ochiladi', locked_h:'Bo\'lim hozircha yopiq',
  why_lock:"Har kuni bitta bo'lim ochamiz: birinchi hafta eng og'iri, hammasini birdan yuklash halol emas. Bularning bari tayyor va seni kutyapti.",
  all_sections:'Barcha bo\'limlar', constitution:'Konstitutsiya', day_n:'{n}-kun',
  lang_:'Til', lang_note:"Murabbiy va vazifalar ham tanlangan tilga o'tadi. Yozilgani o'z holicha qoladi.",
  access_t:'Donat orqali kirish', trial_left:'Sinov muddati yana {n} kun',
  gate_p:"Loyiha ishtirokchilar ko'magi bilan yashaydi.",
  gate_after:"To'lovdan keyin tugmani bosing va kartaning oxirgi 4 raqamini yozing.",
  gate_claim:'Donat qildim', promo:'Promokod', apply:"Qo'llash",
  pending:'Ariza yuborildi. Odatda kun davomida tasdiqlaymiz.',
}};
let LANG = localStorage.getItem('l180_lang') || 'ru';
const t = (k, v) => { let s = (T[LANG] && T[LANG][k]) || T.ru[k] || k;
  if (v) for (const x in v) s = s.split('{' + x + '}').join(v[x]); return s; };

/* ---------------- api ---------------- */
async function api(action, data) {
  const r = await fetch('api.php?a=' + action, {
    method: 'POST', headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify(data || {}), credentials: 'same-origin'
  });
  let j; try { j = await r.json(); } catch (e) { throw new Error('bad_response'); }
  if (!j.ok) { const e = new Error(j.error || 'error'); e.code = r.status; e.data = j; throw e; }
  return j;
}
async function upload(action, form) {
  const r = await fetch('api.php?a=' + action, { method: 'POST', body: form, credentials: 'same-origin' });
  const j = await r.json();
  if (!j.ok) throw new Error(j.error || 'error');
  return j;
}

/* ---------------- ui ---------------- */
const $ = s => document.querySelector(s);
const app = () => $('#app');
const esc = s => String(s == null ? '' : s).replace(/[&<>"']/g, c =>
  ({ '&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;' }[c]));

function toast(m, k) {
  const d = document.createElement('div');
  d.className = 'toast ' + (k || ''); d.textContent = m;
  document.body.appendChild(d);
  setTimeout(() => { d.style.opacity = '0'; }, 2200);
  setTimeout(() => d.remove(), 2700);
}
function sheet(html, cls) {
  // Одновременно на экране только одно окно — иначе они перекрывают друг друга
  document.querySelectorAll('.sheet').forEach(x => x.remove());
  const w = document.createElement('div');
  w.className = 'sheet';
  w.innerHTML = '<div class="sheet-in ' + (cls || '') + '">' + html + '</div>';
  w.addEventListener('click', e => { if (e.target === w) w.remove(); });
  document.body.appendChild(w);
  return w;
}
const ICON = {
  today:'<path d="M3 6a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2v13a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><path d="M8 2v4M16 2v4M3 10h18"/>',
  world:'<circle cx="12" cy="12" r="9"/><path d="M3 12h18M12 3c2.6 2.7 2.6 15 0 18M12 3c-2.6 2.7-2.6 15 0 18"/>',
  squad:'<circle cx="9" cy="8" r="3.2"/><path d="M2.5 20a6.5 6.5 0 0 1 13 0"/><path d="M17 11a3 3 0 1 0-1.6-5.5M18 20a6 6 0 0 0-2-4.5"/>',
  people:'<path d="M12 21s-7-4.4-7-9a4 4 0 0 1 7-2.6A4 4 0 0 1 19 12c0 4.6-7 9-7 9z"/>',
  coach:'<path d="M21 11.5a8.4 8.4 0 0 1-9 8.4L3 21l1.1-3.6A8.4 8.4 0 1 1 21 11.5z"/>',
  hero:'<path d="M12 3l2.6 5.4 5.9.8-4.3 4.1 1 5.9-5.2-2.8-5.2 2.8 1-5.9L3.5 9.2l5.9-.8z"/>',
  photos:'<rect x="3" y="6" width="18" height="14" rx="2"/><circle cx="12" cy="13" r="3.4"/><path d="M8 6l1.5-2h5L16 6"/>',
  more:'<circle cx="5" cy="12" r="1.6"/><circle cx="12" cy="12" r="1.6"/><circle cx="19" cy="12" r="1.6"/>',
  lock:'<rect x="5" y="10.5" width="14" height="10" rx="2.2"/><path d="M8 10.5V7.6a4 4 0 0 1 8 0v2.9"/>',
};
/* Нижнее меню: 5 основных + «Ещё». Остальные разделы живут в «Ещё»,
   но видны там же с замком, если ещё не открыты. */
const NAV_MAIN = ['today', 'coach', 'squad', 'world', 'hero'];
const NAV_MORE = ['photos', 'people'];
const STATS = ['health', 'discipline', 'mind', 'business', 'soul'];
const LOGO = '<span class="logo"><b>180</b> kun</span>';

/* ---------------- состояние ---------------- */
const S = { me: null, tab: 'today', data: {}, timer: null };

/* ---------------- запуск ---------------- */
(async function boot() {
  // Вход по прямой ссылке из Telegram-бота: /index.php?tg=<код>
  const m = location.search.match(/[?&]tg=([a-f0-9]{16,64})/);
  if (m) {
    try {
      const c = await api('tg_check', { nonce: m[1] });
      history.replaceState(null, '', location.pathname);
      if (c.state !== 'ok') toast(t('err'), 'err');
    } catch (e) { history.replaceState(null, '', location.pathname); }
  }
  try {
    const r = await api('me');
    if (r.auth) { LANG = r.user.lang || LANG; localStorage.setItem('l180_lang', LANG); }
    S.me = r; route();
  } catch (e) { S.me = { auth: false }; renderLanding(); }
})();

async function refreshMe() {
  S.me = await api('me');
  if (S.me.auth && S.me.user.lang) { LANG = S.me.user.lang; localStorage.setItem('l180_lang', LANG); }
}
function route() {
  const m = S.me;
  if (!m || !m.auth) return renderLanding();
  if (!m.has_quest) return renderStart();
  if (!m.access && m.user.role !== 'admin') return renderGate();
  return renderTab(S.tab);
}

/* ============================================================
   ЛЕНДИНГ / КОНСТИТУЦИЯ
   ============================================================ */
async function renderLanding() {
  let d;
  try { d = await api('constitution', { lang: LANG }); } catch (e) { d = null; }
  const c = d ? d.c : null;
  const st = d ? d.stats : { walking: 0, tasks: 0, comebacks: 0 };
  if (!c) { return renderAuth(); }

  app().innerHTML = `
  <div class="land">
    <div class="wrap">
      <div class="land-top">
        <img src="assets/logo.svg" alt="180 kun" class="land-logo">
        <div class="langsel">
          <button data-lang="ru" class="${LANG === 'ru' ? 'on' : ''}">RU</button>
          <button data-lang="uz" class="${LANG === 'uz' ? 'on' : ''}">UZ</button>
        </div>
      </div>

      <h1 class="land-h1">${esc(c.lead)}</h1>
      <p class="land-lead">${esc(c.pitch)}</p>
      <button class="btn big" id="l-go">${esc(c.cta)}</button>
      <p class="tiny center">${esc(c.cta_sub)}</p>

      <div class="livebar">
        <div><b>${st.walking}</b><span>${t('walking')}</span></div>
        <div><b>${st.tasks}</b><span>${LANG === 'uz' ? 'bajarilgan vazifa' : 'заданий сделано'}</span></div>
        <div><b>${st.comebacks}</b><span>${LANG === 'uz' ? 'qaytish' : 'возвращений'}</span></div>
      </div>

      <h2 class="land-h2">${esc(c.problem_t)}</h2>
      <ul class="land-list">${c.problem.map(p => `<li>${esc(p)}</li>`).join('')}</ul>

      <h2 class="land-h2">${LANG === 'uz' ? 'Konstitutsiya' : 'Конституция'}</h2>
      <div class="const">
        ${c.points.map((p, i) => `
          <div class="const-item">
            <div class="const-n">${i + 1}</div>
            <div><b>${esc(p[0])}</b><p>${esc(p[1])}</p></div>
          </div>`).join('')}
      </div>

      <h2 class="land-h2">${esc(c.not_t)}</h2>
      <ul class="land-list no">${c.not.map(p => `<li>${esc(p)}</li>`).join('')}</ul>

      <div class="card acc mt2">
        <h3 style="margin-bottom:6px">${esc(c.deal_t)}</h3>
        <p class="small muted" style="margin:0">${esc(c.deal)}</p>
      </div>

      <button class="btn big mt2" id="l-go2">${esc(c.cta)}</button>
      <p class="tiny center" style="padding-bottom:40px">${esc(c.cta_sub)}</p>
    </div>
  </div>`;

  document.querySelectorAll('[data-lang]').forEach(b => b.onclick = () => {
    LANG = b.dataset.lang; localStorage.setItem('l180_lang', LANG); renderLanding();
  });
  $('#l-go').onclick = $('#l-go2').onclick = () => renderAuth();
}

/* ============================================================
   ВХОД ПО ТЕЛЕФОНУ
   ============================================================ */
function renderAuth() {
  let phone = '', step = (S.me && S.me.tg) ? 'choose' : 'phone', wait = 0, dev = null, masked = '';
  let tgTimer = null;

  function draw() {
    app().innerHTML = `
    <div class="wrap screen narrow">
      <div class="topbar"><img src="assets/logo.svg" class="logo-img" alt="180 kun">
        <button class="btn link" id="a-back">${t('back')}</button></div>
      <div class="card" style="margin-top:8vh">
        ${step === 'choose' ? `
          <h2>${t('enter')}</h2>
          <button class="btn tg" id="a-tg">✈️ ${t('tg_login')}</button>
          <p class="tiny center" style="margin-top:10px">${t('tg_hint')}</p>
          <div class="hr"></div>
          <button class="btn sec" id="a-sms">${t('by_sms')}</button>
        ` : step === 'tgwait' ? `
          <h2>${t('tg_wait')}</h2>
          <div class="tgwait"><i></i><i></i><i></i></div>
          <a class="btn" id="a-tgopen" href="#" target="_blank" rel="noopener">${t('tg_open')}</a>
          <p class="tiny center" style="margin-top:10px">${t('tg_hint')}</p>
        ` : step === 'phone' ? `
          <h2>${t('phone')}</h2>
          <input id="a-phone" type="tel" inputmode="tel" placeholder="+998 90 123 45 67" value="${esc(phone)}" autocomplete="tel">
          <button class="btn mt" id="a-send">${t('getcode')}</button>
          <p class="tiny center mt">${t('agree')}</p>
        ` : step === 'code' ? `
          <h2>${t('code')}</h2>
          <p class="small muted">${t('codesent', { p: masked })}</p>
          ${dev ? `<div class="devcode">${t('dev_code', { c: dev })}</div>` : ''}
          <input id="a-code" type="text" inputmode="numeric" maxlength="6" placeholder="000000" class="codein" autocomplete="one-time-code">
          <button class="btn mt" id="a-ok">${t('enter')}</button>
          <div class="center"><button class="btn link" id="a-re">${wait > 0 ? t('resend_in', { n: wait }) : t('resend')}</button></div>
        ` : `
          <h2>${t('yourname')}</h2>
          <input id="a-name" type="text" maxlength="40" placeholder="${LANG === 'uz' ? 'Ism' : 'Имя'}" autocomplete="given-name">
          <button class="btn mt" id="a-fin">${t('cont')}</button>
        `}
      </div>
    </div>`;

    $('#a-back').onclick = () => {
      clearInterval(tgTimer);
      if (step === 'choose' || (step === 'phone' && !(S.me && S.me.tg))) return renderLanding();
      step = (S.me && S.me.tg) ? 'choose' : 'phone'; draw();
    };

    if (step === 'choose') {
      $('#a-tg').onclick = startTg;
      $('#a-sms').onclick = () => { step = 'phone'; draw(); };
    }

    if (step === 'phone') {
      const inp = $('#a-phone'); inp.focus();
      inp.oninput = () => { phone = inp.value; };
      inp.onkeydown = e => { if (e.key === 'Enter') $('#a-send').click(); };
      $('#a-send').onclick = send;
    }
    if (step === 'code') {
      const inp = $('#a-code'); inp.focus();
      inp.oninput = () => { if (inp.value.replace(/\D/g, '').length === 6) verify(); };
      $('#a-ok').onclick = verify;
      $('#a-re').onclick = () => { if (wait <= 0) send(); };
      tick();
    }
    if (step === 'name') {
      $('#a-name').focus();
      $('#a-name').onkeydown = e => { if (e.key === 'Enter') $('#a-fin').click(); };
      $('#a-fin').onclick = finish;
    }
  }

  async function startTg() {
    const b = $('#a-tg'); if (b) b.disabled = true;
    try {
      const r = await api('tg_start');
      step = 'tgwait'; draw();
      const link = $('#a-tgopen'); if (link) link.href = r.url;
      window.open(r.url, '_blank');
      clearInterval(tgTimer);
      tgTimer = setInterval(async () => {
        try {
          const c = await api('tg_check', { nonce: r.nonce });
          if (c.state === 'ok') { clearInterval(tgTimer); await refreshMe(); route(); }
          if (c.state === 'expired') { clearInterval(tgTimer); step = 'choose'; draw(); toast(t('err'), 'err'); }
        } catch (e) { clearInterval(tgTimer); }
      }, 2500);
    } catch (e) { toast(t('err'), 'err'); if (b) b.disabled = false; }
  }

  function tick() {
    clearInterval(S.timer);
    S.timer = setInterval(() => {
      wait--; const b = $('#a-re');
      if (!b) return clearInterval(S.timer);
      b.textContent = wait > 0 ? t('resend_in', { n: wait }) : t('resend');
      if (wait <= 0) clearInterval(S.timer);
    }, 1000);
  }

  async function send() {
    const b = $('#a-send') || $('#a-re'); if (b) b.disabled = true;
    try {
      const r = await api('otp_start', { phone: phone || ($('#a-phone') || {}).value, lang: LANG });
      masked = r.masked; dev = r.dev_code; wait = r.wait || 60; step = 'code'; draw();
    } catch (e) {
      const m = { bad_phone: LANG === 'uz' ? 'Raqam noto\'g\'ri' : 'Проверь номер',
                  cooldown: LANG === 'uz' ? 'Biroz kuting' : 'Подожди немного',
                  too_many_phone: LANG === 'uz' ? 'Juda ko\'p urinish' : 'Слишком много попыток',
                  too_many_ip: LANG === 'uz' ? 'Juda ko\'p urinish' : 'Слишком много попыток' };
      toast(m[e.message] || t('err'), 'err');
      if (b) b.disabled = false;
    }
  }

  async function verify() {
    const code = ($('#a-code') || {}).value || '';
    if (code.replace(/\D/g, '').length !== 6) return;
    try {
      const r = await api('otp_verify', { phone, code, lang: LANG });
      clearInterval(S.timer);
      if (r.new) { step = 'name'; draw(); }
      else { await refreshMe(); route(); }
    } catch (e) { toast(t('wrongcode'), 'err'); }
  }

  async function finish() {
    const name = ($('#a-name') || {}).value.trim();
    try {
      if (name) await api('profile_save', { name });
      await refreshMe(); route();
    } catch (e) { toast(t('err'), 'err'); }
  }

  draw();
}

/* ============================================================
   СТАРТ ПУТИ — ТРИ ВОПРОСА
   ============================================================ */
async function renderStart() {
  const o = await api('start_options');
  let goal = '', cat = '', min = '30';

  app().innerHTML = `
  <div class="wrap screen narrow">
    <div class="topbar"><img src="assets/logo.svg" class="logo-img" alt=""></div>
    <h1>${t('st_t')}</h1>
    <div class="card">
      <div class="field"><label>${t('st_goal')}</label>
        <textarea id="s-goal" placeholder="${t('st_goal_ph')}" maxlength="200"></textarea></div>
      <div class="field"><label>${t('st_cat')}</label>
        <div class="chips" id="s-cat">${o.categories.map(c =>
          `<button class="chip" data-v="${esc(c.value)}">${esc(c.label)}</button>`).join('')}</div></div>
      <div class="field"><label>${t('city')}</label>
        <input id="s-city" list="citylist" placeholder="${t('city')}" maxlength="40" value="${esc(o.city || '')}">
        <datalist id="citylist">${(o.cities || []).map(c => `<option value="${esc(c)}">`).join('')}</datalist>
        <p class="tiny" style="margin-top:6px">${t('city_why')}</p></div>
      <div class="field"><label>${t('st_min')}</label>
        <div class="chips" id="s-min">${o.minutes.map(c =>
          `<button class="chip ${c.value === '30' ? 'on' : ''}" data-v="${esc(c.value)}">${esc(c.label)}</button>`).join('')}</div>
        <p class="tiny" style="margin-top:8px">${t('st_min_note')}</p></div>
      <button class="btn" id="s-go">${t('st_go')}</button>
    </div>
    <p class="tiny center">🔒 ${t('st_priv')}</p>
  </div>`;

  const pick = (box, set) => document.querySelectorAll('#' + box + ' .chip').forEach(b => b.onclick = () => {
    document.querySelectorAll('#' + box + ' .chip').forEach(x => x.classList.remove('on'));
    b.classList.add('on'); set(b.dataset.v);
  });
  pick('s-cat', v => cat = v);
  pick('s-min', v => min = v);
  $('#s-goal').oninput = e => goal = e.target.value;

  $('#s-go').onclick = async () => {
    goal = $('#s-goal').value.trim();
    if (!goal) return toast(t('st_goal'), 'err');
    if (!cat)  return toast(t('st_cat'), 'err');
    const b = $('#s-go'); b.disabled = true; b.textContent = '…';
    try { await api('start', { goal, category: cat, minutes: min, city: ($('#s-city') || {}).value || '' });
      await refreshMe(); S.tab = 'today'; route(); }
    catch (e) { toast(t('err'), 'err'); b.disabled = false; b.textContent = t('st_go'); }
  };
}

/* ============================================================
   ДОСТУП
   ============================================================ */
function renderGate() {
  const d = S.me.donate || {};
  app().innerHTML = `
  <div class="wrap screen">
    <div class="topbar">${LOGO}<button class="btn link" id="g-out">${t('logout')}</button></div>
    <div class="card acc"><h1>${t('access_t')}</h1><p class="muted small">${t('gate_p')}</p>
      <div class="row" style="margin-top:8px"><div class="daynum" style="font-size:28px">${esc(d.amount_label || '')}</div>
      <div class="muted small">/ ${d.period_days || 30} дн.</div></div></div>
    ${(d.cards || []).map(c => `<div class="card tight" style="margin-bottom:8px">
      <div class="tiny">${esc(c.title)}</div>
      <div class="pin copyable" style="font-size:17px;letter-spacing:.1em;margin:4px 0">${esc(c.number)}</div>
      <div class="small muted">${esc(c.holder || '')}</div></div>`).join('')}
    <p class="small muted">${t('gate_after')}</p>
    <button class="btn" id="g-claim">${t('gate_claim')}</button>
    <div class="hr"></div>
    <div class="row"><input id="g-promo" placeholder="${t('promo')}" style="text-transform:uppercase">
      <button class="btn sm" id="g-apply" style="flex:0 0 auto">${t('apply')}</button></div>
    ${d.contact ? `<p class="small muted center mt2">${esc(d.contact)}</p>` : ''}
  </div>`;
  $('#g-out').onclick = async () => { await api('logout'); location.reload(); };
  $('#g-apply').onclick = async () => {
    try { await api('promo', { code: $('#g-promo').value.trim().toUpperCase() });
      await refreshMe(); route(); toast(t('saved'), 'ok'); }
    catch (e) { toast(t('err'), 'err'); }
  };
  $('#g-claim').onclick = () => {
    const w = sheet(`<h2>${t('gate_claim')}</h2>
      <div class="field"><label>Сумма</label><input id="d-am" value="${esc(d.amount_label || '')}"></div>
      <div class="field"><label>Способ</label><input id="d-me" placeholder="Uzcard / Humo / Payme"></div>
      <div class="field"><label>Последние 4 цифры и время</label><textarea id="d-nt" placeholder="**** 1234, 14:20"></textarea></div>
      <button class="btn" id="d-go">${t('ok_')}</button>`);
    w.querySelector('#d-go').onclick = async () => {
      try { await api('donate_claim', { amount: w.querySelector('#d-am').value,
        method: w.querySelector('#d-me').value, note: w.querySelector('#d-nt').value });
        w.remove(); toast(t('pending'), 'ok'); } catch (e) { toast(t('err'), 'err'); }
    };
  };
}

/* ============================================================
   ОБОЛОЧКА
   ============================================================ */
/* Открыт ли раздел */
const isOpen = k => {
  const tabs = (S.me && S.me.tabs && S.me.tabs.length) ? S.me.tabs : ['today', 'coach'];
  return tabs.includes(k);
};
const lockDay = k => (S.me && S.me.locked && S.me.locked[k]) ? S.me.locked[k] : 0;
const tabWhy  = k => (S.me && S.me.tab_why && S.me.tab_why[k]) ? S.me.tab_why[k] : '';

function navBtn(k) {
  const open = isOpen(k), d = lockDay(k);
  return `<button data-tab="${k}" class="${S.tab === k ? 'on' : ''}${open ? '' : ' lk'}">
    <svg viewBox="0 0 24 24">${ICON[k] || ICON.today}</svg><span>${t(k)}</span>
    ${open ? '' : `<i class="lockb">${d || ''}</i>`}
    ${k === 'coach' && open && S.me.unread ? '<i class="dotred"></i>' : ''}</button>`;
}

function shell(inner) {
  const main = NAV_MAIN.slice();
  // текущий раздел всегда виден в меню, даже если он из «Ещё»
  if (!main.includes(S.tab) && S.tab) main[main.length - 1] = S.tab;
  const moreLock = NAV_MORE.some(k => !isOpen(k));
  return `<div class="wrap screen">${inner}</div>
  <nav class="nav"><div class="nav-in" style="grid-template-columns:repeat(${main.length + 1},1fr)">
    ${main.map(navBtn).join('')}
    <button data-more="1" class="${moreLock ? 'lk' : ''}">
      <svg viewBox="0 0 24 24">${ICON.more}</svg><span>${t('more')}</span></button>
  </div></nav>`;
}

function bindNav() {
  document.querySelectorAll('.nav [data-tab]').forEach(b => b.onclick = () => goTab(b.dataset.tab));
  const m = document.querySelector('.nav [data-more]');
  if (m) m.onclick = moreSheet;
}

/* Переход с проверкой замка */
function goTab(k) {
  if (!isOpen(k)) return lockSheet(k);
  renderTab(k);
}

/* Карточка закрытого раздела: что там будет и когда откроется */
function lockSheet(k) {
  const d = lockDay(k);
  const day = (S.me && S.me.day) || 1;
  const when = d === day + 1 ? t('opens_tomorrow') : t('opens_d', { n: d });
  const w = sheet(`<div class="unlock">
    <div class="unlock-ic locked-ic"><svg viewBox="0 0 24 24" class="lockic">${ICON.lock}</svg></div>
    <h2>${t(k)}</h2>
    <p class="muted">${esc(tabWhy(k))}</p>
    <div class="card tight acc center" style="margin:14px 0 4px">
      <b>${esc(when)}</b>
      <div class="tiny" style="margin-top:4px">${t('locked_h')}</div></div>
    <p class="small muted">${t('why_lock')}</p>
    <button class="btn" id="l-x">${t('close')}</button>
  </div>`, 'center');
  w.querySelector('#l-x').onclick = () => w.remove();
}

/* «Ещё»: остальные разделы + сервисные пункты */
function moreSheet() {
  const items = NAV_MORE.concat(NAV_MAIN.filter(k => !isOpen(k)))
    .filter((v, i, a) => a.indexOf(v) === i)
    .sort((a, b) => (lockDay(a) || 0) - (lockDay(b) || 0));   // открытые первыми
  const row = k => {
    const open = isOpen(k), d = lockDay(k);
    return `<button class="mrow${open ? '' : ' lk'}" data-go="${k}">
      <svg viewBox="0 0 24 24">${open ? (ICON[k] || ICON.today) : ICON.lock}</svg>
      <span class="grow"><b>${t(k)}</b><i>${esc(tabWhy(k))}</i></span>
      ${open ? '' : `<em>${t('day_n', { n: d })}</em>`}</button>`;
  };
  const w = sheet(`<h2>${t('all_sections')}</h2>
    <div class="mlist">${items.map(row).join('')}</div>
    <div class="hr"></div>
    <div class="row between" style="align-items:center;margin-bottom:12px">
      <span class="tiny">${t('lang_')}</span>
      <div class="chips" style="margin:0">
        <button class="chip sm${LANG === 'ru' ? ' on' : ''}" data-sl="ru">RU</button>
        <button class="chip sm${LANG === 'uz' ? ' on' : ''}" data-sl="uz">UZ</button>
      </div></div>
    <button class="mrow" data-doc="1"><svg viewBox="0 0 24 24">${ICON.today}</svg>
      <span class="grow"><b>${t('constitution')}</b></span></button>
    <button class="btn link" id="m-out">${t('logout')}</button>`);

  w.querySelectorAll('[data-sl]').forEach(b => b.onclick = async () => {
    const l = b.dataset.sl;
    if (l === LANG) return;
    try {
      await api('lang_set', { lang: l });
      LANG = l; localStorage.setItem('l180_lang', l);
      w.remove(); await refreshMe(); renderTab(S.tab); toast(t('saved'), 'ok');
    } catch (e) { toast(t('err'), 'err'); }
  });
  w.querySelectorAll('[data-go]').forEach(b => b.onclick = () => {
    const k = b.dataset.go; w.remove(); goTab(k);
  });
  const doc = w.querySelector('[data-doc]');
  if (doc) doc.onclick = () => { w.remove(); showConstitution(); };
  w.querySelector('#m-out').onclick = async () => { await api('logout'); location.reload(); };
}

async function showConstitution() {
  try {
    const c = (await api('constitution', { lang: LANG })).c;
    sheet(`<h2>${t('constitution')}</h2>
      <p class="small muted">${esc(c.pitch)}</p>
      <div class="const">${c.points.map((p, i) => `
        <div class="const-item"><div class="const-n">${i + 1}</div>
        <div><b>${esc(p[0])}</b><p>${esc(p[1])}</p></div></div>`).join('')}</div>`);
  } catch (e) { toast(t('err'), 'err'); }
}
/* Карточка «что откроется дальше» — чтобы человек с первого дня видел,
   что продукт больше, чем один экран, и знал, когда придёт остальное. */
function soonCard() {
  const L = (S.me && S.me.locked) || {};
  const ks = Object.keys(L).sort((a, b) => L[a] - L[b]).slice(0, 3);
  if (!ks.length) return '';
  const day = (S.me && S.me.day) || 1;
  return `<div class="card tight soon">
    <div class="tiny">🔒 ${t('soon_t')}</div>
    <div class="soonlist">${ks.map(k => `<button class="soonrow" data-lock="${k}">
      <svg viewBox="0 0 24 24">${ICON[k] || ICON.today}</svg>
      <span class="grow"><b>${t(k)}</b><i>${esc(tabWhy(k))}</i></span>
      <em>${L[k] === day + 1 ? t('opens_tomorrow') : t('opens_d', { n: L[k] })}</em>
    </button>`).join('')}</div></div>`;
}
function bindSoon() {
  document.querySelectorAll('[data-lock]').forEach(b => b.onclick = () => lockSheet(b.dataset.lock));
}

async function renderTab(tab) {
  if (!isOpen(tab)) tab = 'today';
  S.tab = tab;
  try {
    if (tab === 'today') await scToday();
    else if (tab === 'world') await scWorld();
    else if (tab === 'squad') await scSquad();
    else if (tab === 'people') await scPeople();
    else if (tab === 'coach') await scCoach();
    else if (tab === 'photos') await scPhotos();
    else await scHero();
  } catch (e) {
    if (e.message === 'no_access') { await refreshMe(); return renderGate(); }
    if (e.message === 'no_quest') { await refreshMe(); return renderStart(); }
    if (e.code === 401) return renderLanding();
    toast(t('err'), 'err');
  }
  bindNav();
}

/* Модалка «открытия» дня */
function showUnlock(u) {
  if (!u) return;
  const w = sheet(`<div class="unlock">
    <div class="unlock-ic">${u.icon}</div>
    <h2>${esc(u.title)}</h2>
    <p class="muted">${esc(u.text)}</p>
    <button class="btn" id="u-go">${esc(u.cta)}</button>
    <button class="btn link" id="u-later">${t('close')}</button>
  </div>`, 'center');
  const close = async () => { try { await api('onboard_seen', { unlock: u.unlock }); } catch (e) {} w.remove(); await refreshMe(); };
  w.querySelector('#u-go').onclick = async () => { await close(); renderTab(u.tab); };
  w.querySelector('#u-later').onclick = close;
}

/* Карточка вопроса «по ходу» */
function dripCard(d) {
  if (!d) return '';
  return `<div class="card drip">
    <div class="tiny">🤖 ${LANG === 'uz' ? 'Murabbiy so\'rayapti' : 'Коуч спрашивает'}</div>
    <p style="margin:8px 0 12px">${esc(d.q)}</p>
    ${d.opts && d.opts.length
      ? `<div class="chips">${d.opts.map(o => `<button class="chip drip-o" data-v="${esc(o)}">${esc(o)}</button>`).join('')}</div>`
      : `<textarea id="drip-t" placeholder="…" style="min-height:70px"></textarea>
         <button class="btn sm mt" id="drip-s">${t('answer')}</button>`}
    <button class="btn link" id="drip-x">${t('skip')}</button>
  </div>`;
}
function bindDrip(d, after) {
  if (!d) return;
  const send = async v => { try { await api('drip_answer', { key: d.key, value: v }); } catch (e) {} after(); };
  document.querySelectorAll('.drip-o').forEach(b => b.onclick = () => send(b.dataset.v));
  if ($('#drip-s')) $('#drip-s').onclick = () => send(($('#drip-t') || {}).value || '');
  if ($('#drip-x')) $('#drip-x').onclick = () => send('');
}

/* Рекламный блок */
function adBlock(ad) {
  if (!ad) return '';
  return `<a class="ad" href="${esc(ad.url)}" target="_blank" rel="noopener nofollow">
    <div class="ad-lab">${esc(ad.label)}</div>
    <b>${esc(ad.title)}</b>
    ${ad.text ? `<p>${esc(ad.text)}</p>` : ''}
    <span class="ad-cta">${esc(ad.cta)} →</span></a>`;
}

/* ============================================================
   СЕГОДНЯ
   ============================================================ */
async function scToday() {
  const d = await api('today'); S.data.today = d;
  if (d.tabs) { S.me.tabs = d.tabs; S.me.locked = d.locked || {}; S.me.day = d.stats.day; }
  const st = d.stats, q = d.quest;
  const pct = Math.round(st.day / st.total_days * 100);
  const yLeft = (d.yesterday || []).filter(x => !x.done);

  app().innerHTML = shell(`
    <div class="topbar">${LOGO}
      <div class="top-actions"><span class="tiny">${esc(st.stage_name)}</span>
      <button class="btn link" id="t-share">${t('share')}</button></div></div>

    <div class="dayhead">
      <div class="daynum">${st.day}<small> / ${st.total_days}</small></div>
      <div class="grow" style="padding-bottom:6px">
        <div class="bar thin"><i style="width:${pct}%"></i></div>
        <div class="tiny" style="margin-top:6px">${esc(st.stage_sub)}</div></div></div>

    ${d.coach_last ? `<div class="card coachcard" id="t-coach">
      <div class="row between"><span class="tiny">🤖 ${LANG === 'uz' ? 'Murabbiy' : 'Коуч'}</span>
      <span class="tiny">${esc(d.coach_last.ago)}</span></div>
      <p style="margin:8px 0 0">${esc(d.coach_last.text)}</p></div>` : ''}

    ${d.week && d.week.focus ? `<div class="card tight weekcard">
      <div class="tiny">🗓 ${t('week_focus')} · ${LANG === 'uz' ? d.week.week + '-hafta' : d.week.week + '-я неделя'}</div>
      <div style="margin-top:5px;font-weight:600">${esc(d.week.focus)}</div>
      ${d.week.source === 'ai' ? `<div class="tiny" style="margin-top:5px">🤖 ${t('ai_made')}</div>` : ''}
    </div>` : ''}

    ${dripCard(d.drip)}

    <div class="stats">
      <div class="stat"><b>${st.streak}</b><span>${t('streak')}</span></div>
      <div class="stat"><b>${st.week_pct}%</b><span>${t('week')}</span></div>
      <div class="stat"><b>${st.points}</b><span>${t('points')}</span></div>
      <div class="stat"><b>${st.tasks_done}</b><span>${t('done')}</span></div></div>

    ${d.crisis ? `<div class="card crisis"><div class="tiny" style="color:var(--warn)">${t('crisis_badge')}</div>
      <h2 style="margin:6px 0">${esc(d.crisis.title)}</h2>
      <p class="small muted" style="margin:0">${esc(d.crisis.text)}</p></div>` : ''}

    ${d.checkin ? `<div class="card warn"><h2 style="margin-bottom:4px">${t('checkin_t')}</h2>
      <p class="small muted">${t('checkin_p')}</p>
      <button class="btn" id="t-checkin">${t('coach')}</button></div>` : ''}

    ${yLeft.length ? `<div class="card tight"><div class="tiny" style="color:var(--warn)">${t('yesterday_left')}</div>
      <div style="margin-top:8px">${yLeft.map(taskHTML).join('')}</div></div>` : ''}

    <h3 style="margin-top:18px">${t('tasks_today')}</h3>
    <div id="t-list">${d.tasks.map(taskHTML).join('')}</div>
    ${d.tasks.every(x => x.done) ? `<div class="card center small muted">${t('all_done')}</div>` : ''}

    ${soonCard()}

    ${adBlock(d.ad)}

    <div class="card tight mt">
      <div class="row between"><span class="tiny">${t('intensity')}</span><b>${q.intensity} / 5</b></div>
      <div class="bar thin ok" style="margin-top:8px"><i style="width:${q.intensity / 5 * 100}%"></i></div></div>

    <div class="card tight"><div class="tiny" style="margin-bottom:8px">${t('last30')}</div>
      <div class="spark">${(d.history || []).map(h =>
        `<i class="${h.c >= .8 ? 'g' : h.c >= .4 ? 'y' : h.c > 0 ? 'r' : ''}" style="height:${Math.max(3, h.c * 38)}px"></i>`).join('')}</div></div>
  `);

  bindTasks();
  bindSoon();
  bindDrip(d.drip, () => renderTab('today'));
  if ($('#t-checkin')) $('#t-checkin').onclick = () => renderTab('coach');
  if ($('#t-coach')) $('#t-coach').onclick = () => renderTab('coach');
  $('#t-share').onclick = shareCard;
  if (d.unlock) showUnlock(d.unlock);
  else maybeIntake();
}

async function maybeIntake() {
  if (document.querySelector('.sheet')) return;
  try {
    const r = await api('intake');
    if (!r.form) return;
    const f = r.form;
    const w = sheet(`<h2>${esc(f.title)}</h2>
      ${f.note ? `<p class="small muted">${esc(f.note)}</p>` : ''}
      <div id="ik">${f.fields.map((x, i) => `
        <div class="field"><label>${esc(x.l)}</label>
        ${x.t === 'choice'
          ? `<div class="chips ikc" data-k="${esc(x.k)}">${x.o.map(o =>
              `<button class="chip sm" data-v="${esc(o)}">${esc(o)}</button>`).join('')}</div>`
          : x.t === 'number'
            ? `<input type="number" data-k="${esc(x.k)}" min="${x.min || 0}" max="${x.max || 999}">`
            : `<input type="text" data-k="${esc(x.k)}" maxlength="200">`}
        </div>`).join('')}</div>
      <button class="btn" id="ik-go">${t('save')}</button>
      <button class="btn link" id="ik-x">${t('skip')}</button>`);

    const vals = {};
    w.querySelectorAll('.ikc').forEach(box => box.querySelectorAll('.chip').forEach(b => b.onclick = () => {
      box.querySelectorAll('.chip').forEach(x => x.classList.remove('on'));
      b.classList.add('on'); vals[box.dataset.k] = b.dataset.v;
    }));
    w.querySelector('#ik-go').onclick = async () => {
      w.querySelectorAll('input[data-k]').forEach(i => { if (i.value) vals[i.dataset.k] = i.value; });
      try { await api('intake_save', { values: vals }); w.remove(); toast(t('saved'), 'ok'); renderTab('today'); }
      catch (e) { toast(t('err'), 'err'); }
    };
    w.querySelector('#ik-x').onclick = () => w.remove();
  } catch (e) {}
}

function taskHTML(x) {
  return `<div class="task ${x.done ? 'done' : ''}" data-id="${x.id}" data-verify="${x.verify}" data-done="${x.done ? 1 : 0}">
    <div class="tick"><svg viewBox="0 0 24 24" fill="none" stroke="#0B0E14" stroke-width="3.4" stroke-linecap="round"><path d="M4 12l5 5L20 6"/></svg></div>
    <div class="grow"><div class="task-title">${esc(x.title)}</div>
      <div class="task-meta"><span class="dot s-${esc(x.stat)}"></span><span>+${x.points}</span>
      ${x.verify !== 'self' ? `<span>· ${x.verify === 'number' ? '№' : x.verify === 'photo' ? '📷' : '✎'}</span>` : ''}
      ${x.answer ? `<span>· ${esc(String(x.answer).slice(0, 26))}</span>` : ''}</div></div>
    <button class="tphoto" data-ph="${x.id}" title="фото">📷</button>
  </div>`;
}

function bindTasks() {
  document.querySelectorAll('.task').forEach(el => {
    el.onclick = async e => {
      if (e.target.closest('[data-ph]')) return;
      const id = +el.dataset.id, isDone = el.dataset.done === '1';
      if (!isDone && (el.dataset.verify === 'question' || el.dataset.verify === 'number')) {
        const title = el.querySelector('.task-title').textContent;
        const w = sheet(`<h2 style="font-size:16px">${esc(title)}</h2>
          <textarea id="v-a" placeholder="…" style="min-height:80px"></textarea>
          <div class="row mt"><button class="btn ghost" id="v-c" style="flex:0 0 34%">${t('cancel')}</button>
          <button class="btn" id="v-o">${t('ok_')}</button></div>`);
        w.querySelector('#v-c').onclick = () => w.remove();
        w.querySelector('#v-o').onclick = async () => {
          const a = w.querySelector('#v-a').value.trim(); if (!a) return;
          w.remove(); await doToggle(id, true, a);
        };
        setTimeout(() => w.querySelector('#v-a').focus(), 60);
        return;
      }
      await doToggle(id, !isDone, '');
    };
  });
  document.querySelectorAll('[data-ph]').forEach(b => b.onclick = e => {
    e.stopPropagation(); photoSheet(+b.dataset.ph);
  });
}
async function doToggle(id, done, answer) {
  try { await api('task_toggle', { id, done, answer });
    if (navigator.vibrate) navigator.vibrate(done ? 12 : 6);
    await scToday(); bindNav();
  } catch (e) { toast(t('err'), 'err'); }
}

/* ============================================================
   МИР
   ============================================================ */
async function scWorld() {
  const d = await api('world');
  const w = d.world, ex = d.expedition, camp = d.camp, r = d.rpg;
  const sub = S.data.wsub || 'map';
  let sel = null;

  app().innerHTML = shell(`
    <div class="topbar">${LOGO}<span class="tiny">⚡ ${w.energy} ${t('energy')}</span></div>

    <div class="card tight heroline">
      <span class="cls">${r.class.icon || '🛡'}</span>
      <div class="grow"><b>${esc(r.class.label || '')}</b>
        <div class="tiny">${esc(r.class.desc || '')}</div></div>
      <div class="tiny">${r.progress.open} / ${r.progress.total}</div>
    </div>

    <div class="chips" style="margin:12px 0">
      ${[['map', t('map')], ['quests', t('quests')], ['land', LANG === 'uz' ? 'Hudud' : 'Территория'], ['camp', t('camp')]]
        .map(([k, n]) => `<button class="chip sm wsub ${sub === k ? 'on' : ''}" data-v="${k}">${n}</button>`).join('')}
    </div>

    ${sub === 'map' ? `
      ${r.regions.map(reg => `
        <div class="region ${reg.state}">
          <div class="row between">
            <b>${reg.icon} ${esc(reg.name)}</b>
            <span class="tiny">${reg.open} / ${reg.places.length}</span></div>
          <div class="tiny" style="margin-bottom:9px">${esc(reg.sub)}</div>
          ${reg.places.map(p => `
            <div class="place ${p.open ? 'on' : 'off'} ${p.here ? 'here' : ''}" data-p="${esc(p.key)}">
              <span class="pic">${p.open ? p.icon : '🔒'}</span>
              <div class="grow">
                <b>${esc(p.name)}</b>
                <div class="tiny">${p.open ? esc(p.desc) : esc(p.cond)}</div>
              </div>
              ${p.open && p.item ? `<span class="pit" title="${esc(p.item.label)}">${p.item.icon}</span>` : ''}
              ${p.here ? `<span class="tiny">${t('you_here')}</span>` : ''}
            </div>`).join('')}
        </div>`).join('')}
      ${r.items.length ? `<div class="card"><h3>${t('items')}</h3>
        <div class="row wrap" style="gap:9px;font-size:24px">
          ${r.items.filter(Boolean).map(i => `<span title="${esc(i.label)}">${i.icon}</span>`).join('')}
        </div></div>` : ''}
    ` : ''}

    ${sub === 'quests' ? `
      ${r.quests.map(qq => `
        <div class="card">
          <div class="row between"><b>${qq.icon} ${esc(qq.title)}</b>
            <span class="tiny">${qq.progress} / ${qq.target}</span></div>
          <div class="bar thin" style="margin:9px 0 6px">
            <i style="width:${Math.min(100, Math.round(qq.progress / Math.max(1, qq.target) * 100))}%"></i></div>
          <div class="tiny">${esc(qq.note)}</div>
        </div>`).join('')}
      ${!r.quests.length ? `<div class="card center small muted">—</div>` : ''}
    ` : ''}

    ${sub === 'land' ? `
      <div class="card">
        <div class="row between"><h3 style="margin:0">${LANG === 'uz' ? 'Mening hududim' : 'Моя территория'}</h3>
          <span class="tiny">${t('built')}: ${w.built}</span></div>
        <div class="grid" style="grid-template-columns:repeat(${w.w},1fr)">
          ${w.grid.map(c => `<button class="cell ${c.b ? 'has' : ''}" data-x="${c.x}" data-y="${c.y}">${c.icon || ''}</button>`).join('')}
        </div>
        <p class="tiny" style="margin-top:10px">${t('world_hint')}</p>
      </div>
      <h3>${t('build')}</h3>
      <div class="blist">
        ${w.buildings.map(b => `<button class="bitem ${b.locked ? 'lock' : ''}" data-b="${esc(b.key)}">
          <span class="bic">${b.icon}</span>
          <span class="grow"><b>${esc(b.label)}</b>
            <i>${b.locked ? t('need_stat', { n: b.req, s: t(b.stat) }) : '⚡ ' + b.cost}</i></span>
          ${b.locked ? '<span class="tiny">🔒</span>' : ''}</button>`).join('')}
      </div>
    ` : ''}

    ${sub === 'camp' ? `
      ${ex ? `<div class="card acc">
        <div class="row between"><span class="tiny">${t('expedition')}</span>
          <span class="tiny">${ex.days_left > 0 ? ex.days_left + ' ' + t('days_left') : t('last_day')}</span></div>
        <h2 style="margin:6px 0 8px">${esc(ex.title)}</h2>
        <div class="bar"><i style="width:${ex.pct}%"></i></div>
        <div class="row between" style="margin-top:8px">
          <span class="small muted">${ex.progress} / ${ex.target}</span>
          <span class="small">${esc(ex.reward)}</span></div>
        ${ex.top.length ? `<div class="row wrap" style="margin-top:10px;gap:6px">
          ${ex.top.map(p => `<span class="chip sm">${avaMini(p)} ${esc(p.name)} · ${p.amount}</span>`).join('')}</div>` : ''}
      </div>` : `<div class="card center small muted">${LANG === 'uz' ? 'Avval jamoaga qo\'shil' : 'Сначала вступи в группу'}</div>`}
      ${camp ? `<div class="card">
        <h3>${t('camp')}</h3>
        ${camp.landmarks.length
          ? `<div class="row wrap" style="gap:10px;font-size:26px">${camp.landmarks.map(l => `<span title="${esc(l.title)}">${l.icon}</span>`).join('')}</div>`
          : `<p class="small muted" style="margin:0">${LANG === 'uz' ? "Hozircha bo'sh. Ekspeditsiyani yoping." : 'Пока пусто. Закройте экспедицию.'}</p>`}
        <div class="hr"></div>
        <div class="tiny" style="margin-bottom:8px">${t('at_camp')}</div>
        <div class="row wrap" style="gap:8px">${camp.members.map(m =>
          `<span class="chip sm ${m.here ? 'here' : ''}">${avaMini(m)} ${esc(m.name)}</span>`).join('')}</div>
      </div>` : ''}
    ` : ''}

    ${adBlock(d.ad)}
  `);

  document.querySelectorAll('.wsub').forEach(b => b.onclick = () => { S.data.wsub = b.dataset.v; renderTab('world'); });

  document.querySelectorAll('.place.on').forEach(el => el.onclick = async () => {
    const key = el.dataset.p;
    try { await api('rpg_move', { place: key }); renderTab('world'); } catch (e) {}
  });

  document.querySelectorAll('.cell').forEach(c => c.onclick = () => {
    document.querySelectorAll('.cell').forEach(x => x.classList.remove('sel'));
    c.classList.add('sel'); sel = { x: +c.dataset.x, y: +c.dataset.y };
  });
  document.querySelectorAll('.bitem').forEach(b => b.onclick = async () => {
    if (b.classList.contains('lock')) return toast(t('locked'), 'err');
    if (!sel) return toast(t('choose_cell'), 'err');
    try { await api('world_build', { x: sel.x, y: sel.y, b: b.dataset.b }); sel = null; await scWorld(); bindNav(); }
    catch (e) {
      const m = { no_energy: t('no_energy'), busy: LANG === 'uz' ? 'Katak band' : 'Клетка занята', locked: t('locked') };
      toast(m[e.message] || t('err'), 'err');
    }
  });

  // Новая локация — показываем как событие
  if (r.newly && r.newly.length) {
    const n = r.newly[0];
    sheet(`<div class="unlock">
      <div class="unlock-ic">${n.icon}</div>
      <div class="tiny">${t('new_place')}</div>
      <h2>${esc(n.name)}</h2>
      <p class="muted">${esc(n.text)}</p>
      ${n.item ? `<div class="loot">${n.item.icon} ${esc(n.item.label)}</div>` : ''}
      <button class="btn" onclick="this.closest('.sheet').remove()">${t('ok_')}</button>
    </div>`, 'center');
  }
}

function avaMini(p) {
  return p.avatar ? `<img class="ava-mini" src="${esc(p.avatar)}" alt="">`
                  : `<span class="ava-mini txt">${esc(p.initial || '?')}</span>`;
}
function badgeHTML(b) {
  return b ? `<span class="bdg" style="color:${esc(b.color)}" title="${esc(b.label)}">${b.icon}</span>` : '';
}

/* ============================================================
   ГРУППА
   ============================================================ */
async function scSquad() {
  const d = await api('squad');
  if (!d.squad) {
    app().innerHTML = shell(`
      <div class="topbar">${LOGO}</div>
      <div class="card acc"><h1>${t('squad_none_t')}</h1><p class="muted small">${t('squad_none_p')}</p></div>
      <button class="btn" id="s-auto">${t('squad_auto')}</button>
      <div class="mt"><button class="btn sec" id="s-create">${t('squad_create')}</button></div>
      <div class="row mt"><input id="s-code" placeholder="${t('code_')}" style="text-transform:uppercase">
        <button class="btn sm" id="s-join" style="flex:0 0 auto">${t('squad_join')}</button></div>`);
    $('#s-auto').onclick = async () => { await api('squad_auto'); renderTab('squad'); };
    $('#s-create').onclick = async () => { const n = prompt(t('squad_create')); if (n === null) return;
      await api('squad_create', { name: n }); renderTab('squad'); };
    $('#s-join').onclick = async () => { try { await api('squad_join', { code: $('#s-code').value.trim().toUpperCase() });
      renderTab('squad'); } catch (e) { toast(t('err'), 'err'); } };
    return;
  }

  const s = d.squad, ms = d.meetings || [];
  app().innerHTML = shell(`
    <div class="topbar">${LOGO}<span class="tiny">${s.members.length} / ${s.size}</span></div>
    <h1>${esc(s.name)}</h1>

    ${s.format ? `<div class="card tight fmt">
      <b>${s.format.kind === 'offline' ? '📍 ' + t('fmt_offline') : '💻 ' + t('fmt_online')}</b>
      <div class="tiny" style="margin-top:4px">${s.format.kind === 'offline'
        ? t('fmt_offline_d', { n: (s.cities[0] || {}).n || 0 }) + (s.format.city ? ' · ' + esc(s.format.city) : '')
        : t('fmt_online_d')}</div>
      ${s.cities.length ? `<div class="row wrap" style="gap:6px;margin-top:8px">
        ${s.cities.map(c => `<span class="chip sm">${esc(c.city)} · ${c.n}</span>`).join('')}</div>` : ''}
    </div>` : ''}

    ${ms.length ? `<div class="card acc">
      <div class="tiny">${t('meetings')}</div>
      ${ms.slice(0, 2).map(m => `
        <div class="meet" data-m="${m.id}">
          <div class="row between">
            <b>${esc(m.when)}</b>
            <span class="tiny">${m.kind === 'online' ? t('online_m') : t('offline_m')}</span></div>
          <div class="small" style="margin:4px 0">${esc(m.title)}</div>
          ${m.agenda ? `<div class="tiny" style="margin-bottom:8px">${esc(m.agenda)}</div>` : ''}
          ${m.place ? `<div class="tiny">📍 ${esc(m.place)}</div>` : ''}
          ${m.link ? `<a class="tiny" href="${esc(m.link)}" target="_blank" rel="noopener">🔗 ${esc(m.link)}</a>` : ''}
          <div class="row wrap" style="gap:6px;margin-top:8px">
            ${['yes','maybe','no'].map(a => `<button class="chip sm rsvp ${m.my === a ? 'on' : ''}"
              data-id="${m.id}" data-a="${a}">${t('rsvp_' + a)}</button>`).join('')}
            ${m.live ? `<button class="btn sm" data-ci="${m.id}" style="flex:0 0 auto">${t('checkin_btn')}</button>` : ''}
          </div>
          ${m.yes.length ? `<div class="row wrap" style="gap:5px;margin-top:8px">
            <span class="tiny">${t('will_be')}:</span>
            ${m.yes.map(y => avaMini(y)).join('')}</div>` : ''}
        </div>`).join('')}
      ${s.is_leader ? `<button class="btn link" id="m-edit">${t('edit_meet')}</button>` : ''}
    </div>` : ''}

    <div class="card">
      <div class="row between"><span class="tiny">${t('team_week')}</span><b>${s.weekly.done} / ${s.weekly.target}</b></div>
      <div class="bar" style="margin-top:9px"><i style="width:${s.weekly.pct}%"></i></div>
      <div class="row between" style="margin-top:10px"><span class="small muted">${t('team_avg')}</span>
        <b>${s.team_pct}%</b></div></div>

    <div class="card">
      ${s.members.map(m => `
        <div class="member" data-u="${m.id}">
          ${m.avatar ? `<img class="ava" src="${esc(m.avatar)}" alt="">`
                     : `<div class="ava ${m.active ? '' : 'off'}">${esc(m.initial)}</div>`}
          <div class="grow">
            <div class="row between">
              <b>${esc(m.name)}${badgeHTML(m.badge)}${m.is_leader ? ' ⭐' : ''}${m.online ? '<i class="dotg"></i>' : ''}</b>
              <span class="delta ${m.delta > 0 ? 'up' : m.delta < 0 ? 'down' : 'zero'}">${m.delta > 0 ? '+' : ''}${m.delta}%</span></div>
            <div class="small muted">${m.direction.icon} ${esc(m.direction.label)}</div>
            <div class="bar thin" style="margin-top:6px"><i style="width:${m.pct}%"></i></div>
            <div class="tiny" style="margin-top:5px">${t('day')} ${m.day} · ${m.pct}%${m.comebacks ? ' · ↩ ' + m.comebacks : ''}</div>
          </div>
          ${!m.active && !m.is_me ? `<button class="btn sm sec" data-nudge="${m.id}">${t('nudge')}</button>` : ''}
        </div>`).join('')}
    </div>

    ${s.top_improved.length ? `<div class="card tight"><div class="tiny" style="margin-bottom:8px">${t('improved')}</div>
      ${s.top_improved.map((m, i) => `<div class="row between small" style="padding:4px 0">
        <span>${i + 1}. ${esc(m.name)}</span>
        <b class="delta ${m.delta > 0 ? 'up' : 'zero'}">${m.delta > 0 ? '+' : ''}${m.delta}%</b></div>`).join('')}</div>` : ''}

    <div class="card tight"><div class="row between"><span class="tiny">${t('invite')}</span>
      <span class="pin copyable" id="s-pin">${esc(s.code)}</span></div></div>

    ${adBlock(d.ad)}

    <h3 style="margin-top:18px">${t('squad')}</h3>
    <div class="chat">${d.chat.map(m => chatMsg(m, s)).join('')}</div>
    <div class="composer"><input id="s-msg" placeholder="${t('chat_ph')}">
      <button class="btn sm" id="s-send" style="flex:0 0 auto">→</button></div>
    <div class="center mt2"><button class="btn link" id="s-board">${t('board')}</button>
      <button class="btn link" id="s-leave" style="color:var(--bad)">${t('leave')}</button></div>
  `);

  document.querySelectorAll('.rsvp').forEach(b => b.onclick = async e => {
    e.stopPropagation();
    try { await api('rsvp', { id: +b.dataset.id, answer: b.dataset.a }); renderTab('squad'); }
    catch (er) { toast(t('err'), 'err'); }
  });
  document.querySelectorAll('[data-ci]').forEach(b => b.onclick = async e => {
    e.stopPropagation();
    try { const r = await api('meet_checkin', { id: +b.dataset.ci });
      toast(`${t('checked')} ${r.attended}/${r.total}`, 'ok'); renderTab('squad'); }
    catch (er) { toast(t('err'), 'err'); }
  });
  document.querySelectorAll('[data-nudge]').forEach(b => b.onclick = async e => {
    e.stopPropagation(); await api('squad_nudge', { user_id: +b.dataset.nudge }); renderTab('squad');
  });
  document.querySelectorAll('.member').forEach(el => el.onclick = e => {
    if (e.target.closest('button')) return; openProfile(+el.dataset.u);
  });
  document.querySelectorAll('[data-msg]').forEach(el => el.onclick = () => reportSheet('message', +el.dataset.msg));

  const send = async () => { const v = $('#s-msg').value.trim(); if (!v) return;
    $('#s-msg').value = ''; await api('squad_post', { text: v }); renderTab('squad'); };
  $('#s-send').onclick = send;
  $('#s-msg').onkeydown = e => { if (e.key === 'Enter') send(); };
  $('#s-pin').onclick = () => { navigator.clipboard?.writeText(s.code); toast(t('saved'), 'ok'); };
  $('#s-leave').onclick = async () => { if (confirm(t('leave') + '?')) { await api('squad_leave'); renderTab('squad'); } };
  $('#s-board').onclick = async () => {
    const b = await api('squad_board');
    sheet(`<h2>${t('board')}</h2>${b.board.map((x, i) => `
      <div class="row between" style="padding:9px 0;border-bottom:1px solid var(--line)">
        <span>${i + 1}. ${esc(x.name)} <span class="tiny">${x.members}</span></span><b>${x.pct}%</b></div>`).join('')}`);
  };
  if ($('#m-edit')) $('#m-edit').onclick = () => editMeet(ms[0]);
}

function chatMsg(m, s) {
  const mine = m.uid === S.me.user.id;
  if (m.kind === 'system') return `<div class="msg sys">${esc(m.text)}</div>`;
  return `<div class="msg ${mine ? 'user' : 'coach'}">
    ${!mine ? `<b class="tiny" style="display:block;margin-bottom:3px">${esc(m.name)}${badgeHTML(m.badge)}</b>` : ''}
    ${m.photo ? `<img class="chatimg" src="${esc(m.photo)}" alt="">` : ''}
    <span>${esc(m.text)}</span>
    ${!mine ? `<button class="rep" data-msg="${m.id}" title="${t('report')}">⚑</button>` : ''}
    <i class="tiny">${esc(m.ago)}</i></div>`;
}

function editMeet(m) {
  if (!m) return;
  const w = sheet(`<h2>${t('edit_meet')}</h2>
    <div class="chips" id="mk">${['online','offline'].map(k =>
      `<button class="chip ${m.kind === k ? 'on' : ''}" data-v="${k}">${t(k === 'online' ? 'online_m' : 'offline_m')}</button>`).join('')}</div>
    <div class="field mt"><label>${t('place')}</label><input id="mp" value="${esc(m.place || '')}"></div>
    <div class="field"><label>${t('link')}</label><input id="ml" value="${esc(m.link || '')}" placeholder="https://"></div>
    <button class="btn" id="ms">${t('save')}</button>`);
  let kind = m.kind;
  w.querySelectorAll('#mk .chip').forEach(b => b.onclick = () => {
    w.querySelectorAll('#mk .chip').forEach(x => x.classList.remove('on'));
    b.classList.add('on'); kind = b.dataset.v;
  });
  w.querySelector('#ms').onclick = async () => {
    try { await api('meet_edit', { id: m.id, kind, place: w.querySelector('#mp').value, link: w.querySelector('#ml').value });
      w.remove(); renderTab('squad'); } catch (e) { toast(t('err'), 'err'); }
  };
}

/* ============================================================
   ЛЮДИ
   ============================================================ */
async function scPeople() {
  const [p, f] = await Promise.all([api('people', { filter: S.data.pf || 'active' }), api('feed')]);
  const st = p.stats;
  app().innerHTML = shell(`
    <div class="topbar">${LOGO}<span class="tiny">${st.today} ${LANG === 'uz' ? 'bugun' : 'сегодня'}</span></div>

    <div class="livebar sm">
      <div><b>${st.walking}</b><span>${t('walking')}</span></div>
      <div><b>${st.tasks}</b><span>${LANG === 'uz' ? 'vazifa' : 'заданий'}</span></div>
      <div><b>${st.comebacks}</b><span>${LANG === 'uz' ? 'qaytish' : 'возвращений'}</span></div></div>

    <div class="chips" style="margin:12px 0">
      ${['active','new','far','city'].map(k =>
        `<button class="chip sm pf ${(S.data.pf || 'active') === k ? 'on' : ''}" data-v="${k}">${t('ppl_' + k)}</button>`).join('')}
    </div>

    <div class="pgrid">${p.people.map(u => `
      <div class="pcard" data-u="${u.id}">
        ${u.avatar ? `<img class="ava big" src="${esc(u.avatar)}" alt="">`
                   : `<div class="ava big">${esc(u.initial)}</div>`}
        <b>${esc(u.name)}${badgeHTML(u.badge)}</b>
        <span class="tiny">${u.direction.icon} ${esc(u.direction.label)}</span>
        <span class="tiny">${t('day')} ${u.day} · ${u.week_pct}%</span>
        ${u.online ? '<i class="dotg abs"></i>' : ''}
      </div>`).join('')}</div>
    ${!p.people.length ? `<div class="card center small muted">${LANG === 'uz' ? 'Hozircha bo\'sh' : 'Пока пусто'}</div>` : ''}

    <h3 style="margin-top:20px">${t('feed')}</h3>
    ${f.feed.map(x => `
      <div class="fitem">
        <div class="fav" data-u="${x.user_id}">${x.avatar ? `<img src="${esc(x.avatar)}" alt="">` : esc(x.initial)}</div>
        <div class="grow"><b>${esc(x.name)}${badgeHTML(x.badge)}</b> <span class="muted">${esc(x.text)}</span>
          <div class="tiny">${esc(x.ago)}</div></div>
        <button class="cheer ${x.cheered ? 'on' : ''}" data-f="${x.id}">🔥 ${x.cheers || ''}</button>
      </div>`).join('')}
    ${!f.feed.length ? `<div class="card center small muted">${LANG === 'uz' ? 'Hali voqealar yo\'q' : 'Событий пока нет'}</div>` : ''}
  `);

  document.querySelectorAll('.pf').forEach(b => b.onclick = () => { S.data.pf = b.dataset.v; renderTab('people'); });
  document.querySelectorAll('[data-u]').forEach(el => el.onclick = () => openProfile(+el.dataset.u));
  document.querySelectorAll('.cheer').forEach(b => b.onclick = async e => {
    e.stopPropagation();
    try { const r = await api('cheer', { feed_id: +b.dataset.f });
      b.classList.add('on'); b.textContent = '🔥 ' + r.cheers; } catch (er) {}
  });
}

/* ---------------- профиль ---------------- */
async function openProfile(id) {
  let d; try { d = await api('profile', { id }); } catch (e) { return toast(t('err'), 'err'); }
  const p = d.profile;
  const max = Math.max(60, ...STATS.map(k => p.stats[k]));
  const w = sheet(`
    <div class="phead">
      ${p.avatar ? `<img class="ava huge" src="${esc(p.avatar)}" alt="">` : `<div class="ava huge">${esc(p.initial)}</div>`}
      <div class="grow">
        <h2 style="margin:0">${esc(p.name)}${badgeHTML(p.badge)}</h2>
        <div class="small muted">${p.direction.icon} ${esc(p.direction.label)}${p.city ? ' · ' + esc(p.city) : ''}</div>
        ${p.squad ? `<div class="tiny">👥 ${esc(p.squad.name)}</div>` : ''}
        ${p.badge_note ? `<div class="tiny" style="color:${esc(p.badge.color)}">${esc(p.badge_note)}</div>` : ''}
      </div></div>
    ${p.bio ? `<p class="small" style="margin:10px 0">${esc(p.bio)}</p>` : ''}

    <div class="stats" style="margin:12px 0">
      <div class="stat"><b>${p.day}</b><span>${t('day')}</span></div>
      <div class="stat"><b>${p.level}</b><span>${t('level')}</span></div>
      <div class="stat"><b>${p.best}</b><span>${t('best')}</span></div>
      <div class="stat"><b>${p.comebacks}</b><span>${t('comebacks')}</span></div></div>

    <div class="card tight">${STATS.map(k => `<div class="statline">
      <span class="nm">${t(k)}</span>
      <div class="bar thin"><i style="width:${Math.round(p.stats[k] / max * 100)}%"></i></div>
      <span class="vl">${p.stats[k]}</span></div>`).join('')}</div>

    ${p.achievements.length ? `<div class="row wrap" style="gap:6px;margin:10px 0">
      ${p.achievements.slice(0, 12).map(a => `<span class="chip sm">${esc(a.title)}</span>`).join('')}</div>` : ''}

    ${p.photos.length ? `<div class="pgal">${p.photos.map(x =>
      `<img src="${esc(x.url)}" alt="">`).join('')}</div>` : ''}

    <div class="row mt2">
      ${p.is_me ? `<button class="btn sec" id="p-edit">${t('edit')}</button>`
                : `<button class="btn ghost" id="p-rep">⚑ ${t('report')}</button>`}
      <button class="btn sec" id="p-close">${t('close')}</button></div>`);

  w.querySelector('#p-close').onclick = () => w.remove();
  if (w.querySelector('#p-rep')) w.querySelector('#p-rep').onclick = () => { w.remove(); reportSheet('user', id); };
  if (w.querySelector('#p-edit')) w.querySelector('#p-edit').onclick = () => { w.remove(); editProfile(); };
}

function editProfile() {
  const u = S.me.user;
  const w = sheet(`<h2>${t('edit')}</h2>
    <div class="center" style="margin-bottom:14px">
      ${u.avatar ? `<img class="ava huge" id="e-av" src="${esc(u.avatar)}" alt="">`
                 : `<div class="ava huge" id="e-av">${esc(u.initial)}</div>`}
      <div><button class="btn link" id="e-pick">${t('avatar')}</button></div>
      <input type="file" id="e-file" accept="image/*" hidden></div>
    <div class="field"><label>${LANG === 'uz' ? 'Ism' : 'Имя'}</label><input id="e-name" value="${esc(u.name)}" maxlength="40"></div>
    <div class="field"><label>${t('bio_ph')}</label><input id="e-bio" value="${esc(u.bio || '')}" maxlength="160"></div>
    <div class="field"><label>${t('city_ph')}</label><input id="e-city" value="${esc(u.city || '')}" maxlength="40"></div>
    <div class="field"><label>${t('lang_')}</label>
      <div class="chips" id="e-lang">
        <button class="chip${LANG === 'ru' ? ' on' : ''}" data-l="ru">Русский</button>
        <button class="chip${LANG === 'uz' ? ' on' : ''}" data-l="uz">O'zbekcha</button>
      </div>
      <div class="tiny" style="margin-top:6px">${t('lang_note')}</div></div>
    <button class="btn" id="e-save">${t('save')}</button>`);

  let pickLang = LANG;
  w.querySelectorAll('#e-lang .chip').forEach(b => b.onclick = () => {
    w.querySelectorAll('#e-lang .chip').forEach(x => x.classList.remove('on'));
    b.classList.add('on'); pickLang = b.dataset.l;
  });

  w.querySelector('#e-pick').onclick = () => w.querySelector('#e-file').click();
  w.querySelector('#e-file').onchange = async e => {
    const f = e.target.files[0]; if (!f) return;
    const fd = new FormData(); fd.append('file', f);
    try { const r = await upload('avatar', fd); toast(t('saved'), 'ok'); await refreshMe();
      const el = w.querySelector('#e-av');
      el.outerHTML = `<img class="ava huge" id="e-av" src="${r.url}?t=${Date.now()}" alt="">`;
    } catch (er) { toast(t('err'), 'err'); }
  };
  w.querySelector('#e-save').onclick = async () => {
    try { await api('profile_save', { name: w.querySelector('#e-name').value,
      bio: w.querySelector('#e-bio').value, city: w.querySelector('#e-city').value,
      lang: pickLang });
      w.remove(); await refreshMe(); toast(t('saved'), 'ok'); renderTab(S.tab);
    } catch (e) { toast(t('err'), 'err'); }
  };
}

function reportSheet(type, id) {
  api('profile', { id: type === 'user' ? id : S.me.user.id }).then(d => {
    const reasons = d.reasons;
    const w = sheet(`<h2>${t('report_t')}</h2>
      <div class="chips" id="r-list">${Object.keys(reasons).map(k =>
        `<button class="chip sm" data-v="${esc(k)}">${esc(reasons[k])}</button>`).join('')}</div>
      <div class="field mt"><label>${t('report_note')}</label><textarea id="r-note" maxlength="500"></textarea></div>
      <button class="btn" id="r-go" disabled>${t('report_send')}</button>`);
    let reason = '';
    w.querySelectorAll('#r-list .chip').forEach(b => b.onclick = () => {
      w.querySelectorAll('#r-list .chip').forEach(x => x.classList.remove('on'));
      b.classList.add('on'); reason = b.dataset.v; w.querySelector('#r-go').disabled = false;
    });
    w.querySelector('#r-go').onclick = async () => {
      try { await api('report', { type, id, reason, note: w.querySelector('#r-note').value });
        w.remove(); toast(t('report_ok'), 'ok'); } catch (e) { toast(t('err'), 'err'); }
    };
  }).catch(() => toast(t('err'), 'err'));
}

/* ============================================================
   КОУЧ
   ============================================================ */
async function scCoach() {
  const d = await api('coach');
  S.me.unread = 0;
  app().innerHTML = shell(`
    <div class="topbar">${LOGO}<span class="tiny">AI Coach</span></div>
    ${d.checkin ? `<div class="card warn"><h2 style="margin-bottom:4px">${t('checkin_t')}</h2>
      <p class="small muted">${t('checkin_p')}</p>
      <div class="chips">${d.reasons.map(r => `<button class="chip" data-r="${esc(r.id)}">${esc(r.label)}</button>`).join('')}</div>
    </div>` : ''}
    ${dripCard(d.drip)}
    <div class="chat" id="c-chat">${d.messages.map(m => `
      <div class="msg ${m.role === 'user' ? 'user' : 'coach'} ${m.kind === 'milestone' ? 'milestone' : ''}">${esc(m.text)}</div>`).join('')}</div>
    <div class="composer"><input id="c-in" placeholder="${t('coach_ph')}">
      <button class="btn sm" id="c-go" style="flex:0 0 auto">→</button></div>
    ${!d.ai ? `<p class="tiny center">${t('ai_off')}</p>` : ''}`);

  bindDrip(d.drip, () => renderTab('coach'));
  document.querySelectorAll('[data-r]').forEach(b => b.onclick = async () => {
    b.disabled = true;
    try { await api('coach_reason', { reason: b.dataset.r }); await scCoach(); bindNav(); }
    catch (e) { toast(t('err'), 'err'); }
  });
  const send = async () => {
    const inp = $('#c-in'); const v = inp.value.trim(); if (!v) return;
    inp.value = ''; inp.disabled = true;
    $('#c-chat').insertAdjacentHTML('beforeend', `<div class="msg user">${esc(v)}</div>
      <div class="msg coach typing"><i></i><i></i><i></i></div>`);
    window.scrollTo(0, document.body.scrollHeight);
    try { await api('coach_send', { text: v }); await scCoach(); bindNav(); }
    catch (e) { toast(e.message === 'slow_down' ? '…' : t('err'), 'err'); inp.disabled = false; }
  };
  $('#c-go').onclick = send;
  $('#c-in').onkeydown = e => { if (e.key === 'Enter') send(); };
  window.scrollTo(0, document.body.scrollHeight);
}

/* ============================================================
   ЖУРНАЛ
   ============================================================ */
async function scPhotos() {
  const d = await api('photos');
  app().innerHTML = shell(`
    <div class="topbar">${LOGO}<button class="btn link" id="p-rev">${t('ph_review')}</button></div>
    <h1>${t('ph_t')}</h1>
    <button class="btn" id="p-add">${t('ph_add')}</button>
    ${!d.photos.length ? `<div class="card center small muted mt">${t('ph_empty')}</div>` : ''}
    <div class="pgal big mt">${d.photos.map(p => `
      <figure data-p="${p.id}">
        <img src="${esc(p.url)}" alt="" loading="lazy">
        <figcaption>${t('day')} ${p.day}${p.caption ? ' · ' + esc(p.caption) : ''}</figcaption>
      </figure>`).join('')}</div>`);
  $('#p-add').onclick = () => photoSheet(0);
  $('#p-rev').onclick = async () => {
    toast('…');
    try { const r = await api('photos_review');
      sheet(`<h2>${t('ph_review')}</h2><p>${esc(r.text || (LANG === 'uz' ? 'Tahlil uchun kamida 3 ta surat kerak.' : 'Для разбора нужно хотя бы 3 фото.'))}</p>`); }
    catch (e) { toast(t('err'), 'err'); }
  };
  document.querySelectorAll('[data-p]').forEach(f => f.onclick = () => {
    const id = +f.dataset.p;
    const w = sheet(`<img class="cardimg" src="${f.querySelector('img').src}" alt="">
      <div class="row mt"><button class="btn ghost" id="pd" style="color:var(--bad)">${t('ph_del')}</button>
      <button class="btn sec" id="pc">${t('close')}</button></div>`);
    w.querySelector('#pc').onclick = () => w.remove();
    w.querySelector('#pd').onclick = async () => { await api('photo_delete', { id }); w.remove(); renderTab('photos'); };
  });
}

function photoSheet(taskId) {
  const w = sheet(`<h2>${t('ph_add')}</h2>
    <input type="file" id="pf" accept="image/*" capture="environment">
    <div class="field mt"><label>${t('ph_cap')}</label><input id="pc" maxlength="200"></div>
    <div class="field"><label>${t('ph_vis')}</label>
      <div class="chips" id="pv">
        <button class="chip sm" data-v="private">${t('ph_priv')}</button>
        <button class="chip sm on" data-v="squad">${t('ph_squad')}</button>
        <button class="chip sm" data-v="public">${t('ph_pub')}</button></div></div>
    <button class="btn" id="pg">${t('ok_')}</button>`);
  let vis = 'squad';
  w.querySelectorAll('#pv .chip').forEach(b => b.onclick = () => {
    w.querySelectorAll('#pv .chip').forEach(x => x.classList.remove('on'));
    b.classList.add('on'); vis = b.dataset.v;
  });
  w.querySelector('#pg').onclick = async () => {
    const f = w.querySelector('#pf').files[0];
    if (!f) return toast(t('ph_add'), 'err');
    const fd = new FormData();
    fd.append('file', f); fd.append('caption', w.querySelector('#pc').value);
    fd.append('visibility', vis); fd.append('task_id', String(taskId || 0));
    w.querySelector('#pg').disabled = true;
    try { await upload('photo_add', fd); w.remove(); toast(t('saved'), 'ok');
      renderTab(S.tab === 'photos' ? 'photos' : S.tab); }
    catch (e) { toast(t('err'), 'err'); w.querySelector('#pg').disabled = false; }
  };
}

/* ============================================================
   ГЕРОЙ
   ============================================================ */
async function scHero() {
  const d = await api('hero');
  const h = d.hero, me = d.me;
  const max = Math.max(60, ...STATS.map(k => h[k]));
  app().innerHTML = shell(`
    <div class="topbar">${LOGO}<button class="btn link" id="h-out">${t('logout')}</button></div>

    <div class="card phead-card">
      ${me.avatar ? `<img class="ava huge" src="${esc(me.avatar)}" alt="">` : `<div class="ava huge">${esc(me.initial)}</div>`}
      <div class="grow"><h2 style="margin:0">${esc(me.name)}${badgeHTML(me.badge)}</h2>
        <div class="small muted">${me.direction.icon} ${esc(me.direction.label)}${me.city ? ' · ' + esc(me.city) : ''}</div>
        ${me.bio ? `<div class="tiny">${esc(me.bio)}</div>` : ''}</div>
      <button class="btn sm sec" id="h-edit">${t('edit')}</button></div>

    <div class="card acc center">
      <div class="tiny">${t('level')}</div>
      <div class="daynum" style="font-size:42px">${h.level}</div>
      <div class="bar thin" style="margin:12px 0 6px"><i style="width:${Math.min(100, h.xp / Math.max(1, h.next) * 100)}%"></i></div>
      <div class="tiny">${h.xp} / ${h.next} ${t('xp')}</div></div>

    <div class="card">${radarSVG(h, max)}</div>

    <div class="card">${STATS.map(k => `<div class="statline">
      <span class="nm">${t(k)}</span>
      <div class="bar thin"><i style="width:${Math.round(h[k] / max * 100)}%"></i></div>
      <span class="vl">${h[k]}</span></div>`).join('')}</div>

    <div class="stats">
      <div class="stat"><b>${h.streak}</b><span>${t('streak')}</span></div>
      <div class="stat"><b>${h.best_streak}</b><span>${t('best')}</span></div>
      <div class="stat"><b>${h.comebacks}</b><span>${t('comebacks')}</span></div>
      <div class="stat"><b>${d.achievements.length}</b><span>${t('ach')}</span></div></div>

    <h3 style="margin-top:18px">${t('ach')}</h3>
    ${d.achievements.length
      ? `<div class="badges">${d.achievements.map(a => `<div class="badge"><span>${esc(a.title)}</span></div>`).join('')}</div>`
      : `<div class="card center small muted">${t('no_ach')}</div>`}

    <button class="btn sec mt2" id="h-share">${t('share')}</button>`);

  $('#h-out').onclick = async () => { await api('logout'); location.reload(); };
  $('#h-edit').onclick = editProfile;
  $('#h-share').onclick = shareCard;
}

function radarSVG(h, max) {
  const cx = 150, cy = 130, R = 92;
  const pt = (i, r) => { const a = -Math.PI / 2 + i * 2 * Math.PI / 5;
    return [cx + Math.cos(a) * r, cy + Math.sin(a) * r]; };
  const grid = [.25, .5, .75, 1].map(f =>
    `<polygon points="${STATS.map((_, i) => pt(i, R * f).join(',')).join(' ')}" fill="none" stroke="#243049"/>`).join('');
  const poly = STATS.map((k, i) => pt(i, Math.max(6, h[k] / max * R)).join(',')).join(' ');
  const labels = STATS.map((k, i) => { const [x, y] = pt(i, R + 20);
    return `<text x="${x}" y="${y}" fill="#8A97B4" font-size="10.5" text-anchor="middle" dominant-baseline="middle">${t(k)}</text>`; }).join('');
  return `<svg class="radar" viewBox="0 0 300 260">${grid}
    <polygon points="${poly}" fill="rgba(255,90,31,.28)" stroke="#FF5A1F" stroke-width="2"/>
    ${labels}</svg>`;
}

/* ============================================================
   SHARE
   ============================================================ */
async function shareCard() {
  let c; try { c = (await api('share')).card; } catch (e) { return toast(t('err'), 'err'); }
  const W = 1080, H = 1920, cv = document.createElement('canvas');
  cv.width = W; cv.height = H; const x = cv.getContext('2d');
  const g = x.createLinearGradient(0, 0, W, H);
  g.addColorStop(0, '#0B0E14'); g.addColorStop(.55, '#141A2A'); g.addColorStop(1, '#1E1208');
  x.fillStyle = g; x.fillRect(0, 0, W, H);
  x.strokeStyle = 'rgba(255,90,31,.35)'; x.lineWidth = 3; x.strokeRect(48, 48, W - 96, H - 96);
  x.textAlign = 'center';
  x.fillStyle = '#FF5A1F'; x.font = '700 42px -apple-system,Segoe UI,Roboto,Arial';
  x.fillText('180 kun', W / 2, 190);
  x.fillStyle = '#E9EEF8'; x.font = '800 200px -apple-system,Segoe UI,Roboto,Arial';
  x.fillText('DAY ' + c.day, W / 2, 500);
  x.fillStyle = '#5E6B87'; x.font = '600 44px -apple-system,Segoe UI,Roboto,Arial';
  x.fillText('/ ' + c.total + '   ·   ' + c.direction, W / 2, 570);
  const bw = W - 260; x.fillStyle = '#1B2436'; rr(x, 130, 650, bw, 20, 10); x.fill();
  x.fillStyle = '#FF5A1F'; rr(x, 130, 650, bw * (c.day / c.total), 20, 10); x.fill();
  const lines = [
    [c.tasks_done, LANG === 'uz' ? 'bajarilgan vazifa' : 'выполненных заданий'],
    [c.streak, LANG === 'uz' ? 'kun ketma-ket' : 'дней подряд'],
    [c.comebacks, LANG === 'uz' ? 'qaytish' : 'возвращения после срыва'],
    [c.points, LANG === 'uz' ? 'ochko' : 'очков прогресса']];
  let yy = 830;
  lines.forEach(([v, lab]) => {
    x.fillStyle = '#E9EEF8'; x.font = '800 92px -apple-system,Segoe UI,Roboto,Arial';
    x.fillText(String(v), W / 2, yy);
    x.fillStyle = '#8A97B4'; x.font = '500 36px -apple-system,Segoe UI,Roboto,Arial';
    x.fillText(lab, W / 2, yy + 52); yy += 190;
  });
  x.fillStyle = '#FF8A4C'; x.font = 'italic 600 42px -apple-system,Segoe UI,Roboto,Arial';
  x.fillText(LANG === 'uz' ? "Lekin eng muhimi — men to'xtamadim." : 'Но главное — я не остановился.', W / 2, yy + 40);
  x.fillStyle = '#5E6B87'; x.font = '600 34px -apple-system,Segoe UI,Roboto,Arial';
  x.fillText(c.name + (c.squad ? ' · ' + c.squad : ''), W / 2, H - 150);
  x.fillStyle = '#FF5A1F'; x.font = '800 34px -apple-system,Segoe UI,Roboto,Arial';
  x.fillText('180kun.online', W / 2, H - 96);

  const url = cv.toDataURL('image/png');
  const w = sheet(`<h2>${t('share')}</h2><img class="cardimg" src="${url}" alt="">
    <a class="btn mt" href="${url}" download="180kun-day${c.day}.png">${LANG === 'uz' ? 'Yuklab olish' : 'Скачать'}</a>`);
  if (navigator.share && navigator.canShare) {
    cv.toBlob(b => {
      const f = new File([b], '180kun.png', { type: 'image/png' });
      if (navigator.canShare({ files: [f] })) {
        w.querySelector('a').insertAdjacentHTML('afterend', `<button class="btn sec mt" id="sn">${t('share')}</button>`);
        w.querySelector('#sn').onclick = () => navigator.share({ files: [f] }).catch(() => {});
      }
    });
  }
  function rr(ctx, X, Y, w2, h2, r) {
    ctx.beginPath(); ctx.moveTo(X + r, Y);
    ctx.arcTo(X + w2, Y, X + w2, Y + h2, r); ctx.arcTo(X + w2, Y + h2, X, Y + h2, r);
    ctx.arcTo(X, Y + h2, X, Y, r); ctx.arcTo(X, Y, X + w2, Y, r); ctx.closePath();
  }
}
