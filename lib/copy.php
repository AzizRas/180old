<?php
/* ============================================================
   Тексты продукта: Конституция, арка первых 7 дней,
   вопросы «по ходу», интейк-анкеты треков, метки, жалобы.
   ============================================================ */

/* ---------------- КОНСТИТУЦИЯ ---------------- */
function constitution(string $lang = 'ru'): array {
    $ru = [
        'name'   => '180 kun',
        'lead'   => 'Приложение, которое не даёт тебе исчезнуть.',
        'pitch'  => 'Ты выбираешь одну цель. Мы разбиваем её на 180 дней, ставим рядом четырёх человек и не отпускаем, когда ты сорвёшься. Потому что срываются все — а доходят те, кого вернули.',
        'problem_t' => 'Что здесь не так у всех остальных',
        'problem' => [
            'Ты ставишь цель в январе и забываешь про неё в феврале. Не потому что слабый — потому что один.',
            'Трекеры считают серии. Пропустил три дня — серия сгорела, приложение удалено. Оно наказало тебя ровно в тот момент, когда нужна была помощь.',
            'Курсы и марафоны заканчиваются, а ты остаёшься с теми же привычками.',
            'Тебе продают мотивацию. Мотивация заканчивается на 21-й день, а меняет жизнь то, что происходит на 45-й.',
        ],
        'points' => [
            ['Ты не проигрываешь, когда падаешь',
             'Пропуск — это информация, а не проступок. Мы не сжигаем твой прогресс и не пишем «ты потерял серию». Проигрывает только тот, кто перестал возвращаться. За возвращение здесь дают награду.'],
            ['180 дней, а не «навсегда»',
             'У пути есть конец. Это обещание, которое реально сдержать. Шесть этапов по 30 дней, у каждого свой характер, и ты всегда видишь, сколько осталось.'],
            ['План подстраивается под тебя',
             'Если ты второй день не справляешься — виноват план, а не ты. ИИ спросит, что случилось, и уменьшит нагрузку. Вернёшься в силу — поднимет обратно.'],
            ['Ты видишь только сегодня',
             'Никаких пугающих списков на полгода. Открыл приложение — увидел два-три дела на сегодня. Закрыл. Всё.'],
            ['Тебя сравнивают только с тобой',
             'В рейтинге выигрывает не тот, кто сделал больше всех, а тот, кто вырос сильнее всех относительно себя неделю назад.'],
            ['Твоя цель — твоё дело',
             'Группа видит направление: тело, дело, разум, душа. Не видит ни цифр на весах, ни суммы долга, ни диагноза. Подробности — только у тебя и у коуча.'],
            ['Вас пятеро',
             'Не толпа и не лента. Пять человек, которые встречаются два раза в неделю и замечают, если кто-то пропал. Когда захочешь бросить, это заметит не алгоритм, а человек.'],
        ],
        'not_t'  => 'Чего здесь не будет',
        'not'    => [
            'Стыда. Ни одного сообщения в духе «ты опять всё пропустил».',
            'Бесконечной ленты, в которой можно залипнуть на час.',
            'Обещаний «минус 10 кг за месяц». Мы не продаём чудо.',
            'Медицинских назначений от ИИ. Калораж, дозировки и диагнозы — только к врачу.',
            'Твоих личных целей на виду у посторонних.',
        ],
        'deal_t' => 'Честная сделка',
        'deal'   => 'Проект живёт на донатах участников — без рекламы внутри пути и без продажи твоих данных. Ты поддерживаешь — получаешь доступ на 30 дней. Не понравилось — просто не продлеваешь.',
        'cta'    => 'Начать свои 180 дней',
        'cta_sub'=> 'Регистрация по номеру телефона, 30 секунд',
    ];

    $uz = [
        'name'   => '180 kun',
        'lead'   => "Seni g'oyib bo'lishga qo'ymaydigan ilova.",
        'pitch'  => "Sen bitta maqsad tanlaysan. Biz uni 180 kunga bo'lamiz, yoningga to'rt kishini qo'yamiz va uzilib qolganingda qo'yib yubormaymiz. Chunki hamma uziladi — maqsadga esa qaytarilganlar yetadi.",
        'problem_t' => 'Boshqalarda nima noto\'g\'ri',
        'problem' => [
            "Yanvarda maqsad qo'yasan, fevralda unutasan. Zaif bo'lganing uchun emas — yolg'iz bo'lganing uchun.",
            "Trekerlar ketma-ketlikni sanaydi. Uch kun o'tkazding — ketma-ketlik yondi, ilova o'chirildi. U seni aynan yordam kerak bo'lgan paytda jazoladi.",
            "Kurslar va marafonlar tugaydi, sen esa o'sha odatlaring bilan qolasan.",
            "Senga motivatsiya sotishadi. Motivatsiya 21-kuni tugaydi, hayotni esa 45-kuni bo'ladigan narsa o'zgartiradi.",
        ],
        'points' => [
            ["Yiqilganingda yutqazmaysan",
             "Kun o'tkazib yuborish — bu ma'lumot, ayb emas. Biz progressingni yoqmaymiz va «ketma-ketligingni yo'qotding» deb yozmaymiz. Faqat qaytishni to'xtatgan yutqazadi. Qaytganing uchun bu yerda mukofot beriladi."],
            ["180 kun, «abadiy» emas",
             "Yo'lning oxiri bor. Bu — bajarish mumkin bo'lgan va'da. 30 kunlik olti bosqich, har birining o'z xarakteri bor, va sen doim qancha qolganini ko'rasan."],
            ["Reja senga moslashadi",
             "Ikkinchi kun uddalay olmayotgan bo'lsang — reja aybdor, sen emas. AI nima bo'lganini so'raydi va yuklamani kamaytiradi. Kuchga kirsang — qaytarib ko'taradi."],
            ["Sen faqat bugunni ko'rasan",
             "Yarim yillik qo'rqinchli ro'yxatlar yo'q. Ilovani ochding — bugunga ikki-uch ish ko'rding. Yopding. Tamom."],
            ["Seni faqat o'zing bilan solishtirishadi",
             "Reytingda eng ko'p qilgan emas, bir hafta oldingi o'ziga nisbatan eng ko'p o'sgan yutadi."],
            ["Maqsading — sening ishing",
             "Jamoa yo'nalishni ko'radi: tana, ish, aql, ruh. Tarozidagi raqamni ham, qarz miqdorini ham, tashxisni ham ko'rmaydi. Tafsilotlar — faqat sen va murabbiyda."],
            ["Sizlar beshtasiz",
             "Olomon ham, lenta ham emas. Haftada ikki marta uchrashadigan va kimdir g'oyib bo'lsa sezadigan besh kishi. Tashlab ketmoqchi bo'lganingda buni algoritm emas, odam sezadi."],
        ],
        'not_t'  => "Bu yerda nima bo'lmaydi",
        'not'    => [
            "Uyaltirish. «Yana hammasini o'tkazib yubording» degan bironta xabar yo'q.",
            "Bir soat yopishib qoladigan cheksiz lenta.",
            "«Bir oyda 10 kg» va'dalari. Biz mo'jiza sotmaymiz.",
            "AI'dan tibbiy tayinlovlar. Kaloriya, doza va tashxis — faqat shifokorga.",
            "Shaxsiy maqsadlaring begonalar ko'z o'ngida.",
        ],
        'deal_t' => 'Halol kelishuv',
        'deal'   => "Loyiha ishtirokchilar donati hisobiga yashaydi — yo'l ichida reklama yo'q, ma'lumotlaringni sotish yo'q. Qo'llab-quvvatlaysan — 30 kunlik kirish olasan. Yoqmadi — shunchaki uzaytirmaysan.",
        'cta'    => "180 kunimni boshlash",
        'cta_sub'=> "Telefon raqami orqali, 30 soniya",
    ];

    return $lang === 'uz' ? $uz : $ru;
}

/* ---------------- АРКА ПЕРВЫХ 7 ДНЕЙ ----------------
   Каждый день открывается ровно одна новая часть приложения.
   Цель: чтобы на 2-й день было ради чего вернуться. */
function onboarding_arc(string $lang = 'ru'): array {
    $ru = [
        1 => ['unlock' => 'hero',   'icon' => '🧑‍🚀',
              'title' => 'День 1. У тебя появился герой',
              'text'  => 'Он пустой. Каждое выполненное дело добавляет ему очки в одну из пяти характеристик. Сделай сегодня хотя бы одно — и увидишь, как он оживает.',
              'cta'   => 'Открыть героя', 'tab' => 'hero'],
        2 => ['unlock' => 'squad',  'icon' => '👥',
              'title' => 'День 2. Открылась группа',
              'text'  => 'Пять человек, каждый со своей целью. Они не увидят, ЧТО именно ты делаешь — только направление. Напиши одну строчку о себе: с этого начинается доверие.',
              'cta'   => 'Зайти в группу', 'tab' => 'squad'],
        3 => ['unlock' => 'world',  'icon' => '🗺️',
              'title' => 'День 3. Открылась карта мира',
              'text'  => 'Пустая земля. Каждое реальное действие даёт энергию, а энергия строит твою территорию. Это не игра ради игры: если ты ничего не делаешь в жизни, здесь ничего не растёт.',
              'cta'   => 'Посмотреть карту', 'tab' => 'world'],
        4 => ['unlock' => 'people', 'icon' => '🔥',
              'title' => 'День 4. Открылись другие люди',
              'text'  => 'Теперь ты видишь, кто ещё идёт свои 180 дней прямо сейчас. Можно зайти в профиль и поддержать любого — одно нажатие, ему придёт.',
              'cta'   => 'Посмотреть на других', 'tab' => 'people'],
        5 => ['unlock' => 'photos', 'icon' => '📷',
              'title' => 'День 5. Открылся журнал',
              'text'  => 'Фотографируй то, что сделал: тарелку, тренировку, страницу, рабочий стол. Через 90 дней эти фото станут твоим главным доказательством. И коуч сможет разобрать их и подсказать.',
              'cta'   => 'Добавить первое фото', 'tab' => 'photos'],
        6 => ['unlock' => 'meet',   'icon' => '📅',
              'title' => 'День 6. Первая встреча группы',
              'text'  => 'Два раза в неделю группа собирается на 20 минут. Это главное, что отличает дошедших от бросивших. Отметься, придёшь ли.',
              'cta'   => 'Ответить на встречу', 'tab' => 'squad'],
        7 => ['unlock' => 'week',   'icon' => '🏅',
              'title' => 'День 7. Неделя за спиной',
              'text'  => 'Первая неделя — самый обрывистый участок: здесь отваливается больше половины. Ты не отвалился. Забери карточку и посмотри, что изменилось.',
              'cta'   => 'Забрать карточку', 'tab' => 'today'],
    ];
    $uz = [
        1 => ['unlock' => 'hero',   'icon' => '🧑‍🚀',
              'title' => "1-kun. Senda qahramon paydo bo'ldi",
              'text'  => "U hozircha bo'sh. Har bir bajarilgan ish unga beshta xususiyatdan biriga ochko qo'shadi. Bugun hech bo'lmasa bittasini bajar — jonlanganini ko'rasan.",
              'cta'   => 'Qahramonni ochish', 'tab' => 'hero'],
        2 => ['unlock' => 'squad',  'icon' => '👥',
              'title' => '2-kun. Jamoa ochildi',
              'text'  => "Besh kishi, har birining o'z maqsadi bor. Ular sen NIMA qilayotganingni ko'rmaydi — faqat yo'nalishni. O'zing haqingda bir qator yoz: ishonch shundan boshlanadi.",
              'cta'   => 'Jamoaga kirish', 'tab' => 'squad'],
        3 => ['unlock' => 'world',  'icon' => '🗺️',
              'title' => '3-kun. Dunyo xaritasi ochildi',
              'text'  => "Bo'sh yer. Har bir haqiqiy ish energiya beradi, energiya esa sening hududingni quradi. Bu o'yin uchun o'yin emas: hayotda hech narsa qilmasang, bu yerda ham hech narsa o'smaydi.",
              'cta'   => "Xaritani ko'rish", 'tab' => 'world'],
        4 => ['unlock' => 'people', 'icon' => '🔥',
              'title' => '4-kun. Boshqa odamlar ochildi',
              'text'  => "Endi hozir o'z 180 kunini o'tayotgan boshqalarni ko'rasan. Profiliga kirib, istaganingni qo'llab-quvvatlashing mumkin — bir bosish, unga yetib boradi.",
              'cta'   => "Boshqalarga qarash", 'tab' => 'people'],
        5 => ['unlock' => 'photos', 'icon' => '📷',
              'title' => '5-kun. Kundalik ochildi',
              'text'  => "Qilgan ishingni suratga ol: likopcha, mashg'ulot, kitob beti, ish stoli. 90 kundan keyin bu suratlar sening asosiy dalilingga aylanadi. Murabbiy ham ularni tahlil qilib maslahat bera oladi.",
              'cta'   => "Birinchi suratni qo'shish", 'tab' => 'photos'],
        6 => ['unlock' => 'meet',   'icon' => '📅',
              'title' => '6-kun. Jamoaning birinchi uchrashuvi',
              'text'  => "Haftada ikki marta jamoa 20 daqiqaga yig'iladi. Yetib borganlarni tashlab ketganlardan ajratadigan asosiy narsa — shu. Kelasanmi yoki yo'q, belgilab qo'y.",
              'cta'   => 'Uchrashuvga javob berish', 'tab' => 'squad'],
        7 => ['unlock' => 'week',   'icon' => '🏅',
              'title' => '7-kun. Bir hafta orqada qoldi',
              'text'  => "Birinchi hafta — eng tik joy: bu yerda yarmidan ko'pi tushib qoladi. Sen tushmading. Kartani ol va nima o'zgarganini ko'r.",
              'cta'   => 'Kartani olish', 'tab' => 'today'],
    ];
    return $lang === 'uz' ? $uz : $ru;
}

/* ---------------- ВКЛАДКИ ----------------
   Раздел не «отсутствует», а виден с замком и днём открытия. Человек с
   первого экрана понимает, что продукт больше, чем то, что уже открыто.
   Постепенное открытие можно выключить целиком:
       'onboarding' => ['progressive' => false]
   в config.local.php — тогда всё доступно с первого дня. */
function tab_days(): array {
    return ['today' => 0, 'coach' => 0, 'hero' => 1, 'squad' => 2,
            'world' => 3, 'people' => 4, 'photos' => 5];
}

/* Зачем нужен раздел — текст на карточке замка */
function tab_why(string $tab, string $lang = 'ru'): string {
    $ru = [
        'today'  => 'Задания дня, серия, прогресс.',
        'coach'  => 'Личный ИИ-коуч: пишет первым, перестраивает план.',
        'hero'   => 'Твой персонаж: характеристики, уровень, награды, аватар.',
        'squad'  => 'Группа из 5 человек, чат и встречи 2 раза в неделю.',
        'world'  => 'RPG-карта: локации открываются за реальные дела, экспедиции группы.',
        'people' => 'Другие участники: профили, поддержка, твой город.',
        'photos' => 'Журнал: фото выполненных заданий, коуч их разбирает.',
    ];
    $uz = [
        'today'  => 'Kun vazifalari, seriya, progress.',
        'coach'  => "Shaxsiy AI-murabbiy: o'zi birinchi yozadi, rejani qayta quradi.",
        'hero'   => "Sening qahramoning: xarakteristikalar, daraja, mukofotlar, avatar.",
        'squad'  => "5 kishilik jamoa, chat va haftada 2 marta uchrashuv.",
        'world'  => "RPG-xarita: joylar real ishlar uchun ochiladi, jamoa ekspeditsiyalari.",
        'people' => "Boshqa ishtirokchilar: profillar, qo'llab-quvvatlash, sening shahring.",
        'photos' => "Kundalik: bajarilgan vazifalar surati, murabbiy ularni tahlil qiladi.",
    ];
    $m = $lang === 'uz' ? $uz : $ru;
    return $m[$tab] ?? '';
}

function tab_why_all(string $lang = 'ru'): array {
    $out = [];
    foreach (array_keys(tab_days()) as $tab) $out[$tab] = tab_why($tab, $lang);
    return $out;
}

/* Что открыто на данный день (для нижнего меню).
   $all = true — показать всё (админ/модератор или progressive выключен). */
function unlocked_tabs(int $day, bool $all = false): array {
    $days = tab_days();
    if ($all || !cfg('onboarding.progressive', true)) return array_keys($days);
    $out = [];
    foreach ($days as $tab => $d) if ($day >= $d) $out[] = $tab;
    return $out;
}

/* Что ещё закрыто: вкладка => день открытия */
function locked_tabs(int $day, bool $all = false): array {
    if ($all || !cfg('onboarding.progressive', true)) return [];
    $out = [];
    foreach (tab_days() as $tab => $d) if ($day < $d) $out[$tab] = $d;
    return $out;
}

/* ---------------- ВОПРОСЫ «ПО ХОДУ» ----------------
   На регистрации спрашиваем 3 вещи. Остальное коуч спрашивает по одному
   вопросу в день — это и снижает отвал, и делает диалог живым. */
function drip_questions(string $lang = 'ru'): array {
    $ru = [
        ['key' => 'wake',     'day' => 2, 'q' => 'Во сколько ты обычно просыпаешься? Спрашиваю, чтобы не ставить задания на время, когда ты спишь.',
         'opts' => ['до 6:00', '6:00–8:00', '8:00–10:00', 'позже 10:00']],
        ['key' => 'why',      'day' => 3, 'q' => 'Скажи одной фразой: зачем тебе это на самом деле? Я вернусь к твоим словам, когда станет тяжело.', 'opts' => []],
        ['key' => 'obstacle', 'day' => 4, 'q' => 'Из-за чего ты обычно бросаешь такие затеи?',
         'opts' => ['Нет времени', 'Устаю', 'Пропадает интерес', 'Ставлю слишком много', 'Одному тяжело']],
        ['key' => 'tone',     'day' => 5, 'q' => 'Как с тобой лучше разговаривать, когда ты просядешь?',
         'opts' => ['Прямо и жёстко', 'Спокойно и по делу', 'Мягко и с поддержкой']],
        ['key' => 'city',     'day' => 6, 'q' => 'В каком ты городе? Нужно, чтобы предлагать офлайн-встречи рядом с тобой.', 'opts' => []],
        ['key' => 'noticed',  'day' => 7, 'q' => 'Прошла неделя. Что ты уже заметил в себе — даже совсем мелкое?', 'opts' => []],
        ['key' => 'evening',  'day' => 10,'q' => 'Что чаще всего съедает твой вечер?',
         'opts' => ['Телефон', 'Работа', 'Семья и дом', 'Усталость', 'Друзья']],
        ['key' => 'future',   'day' => 14,'q' => 'Опиши свой обычный день через 180 дней. Как просыпаешься, что делаешь, как себя чувствуешь? Это письмо себе — покажу его на 90-й и 180-й день.', 'opts' => []],
        ['key' => 'support',  'day' => 21,'q' => 'Кто в твоей жизни знает про эту твою цель?',
         'opts' => ['Никто', 'Один человек', 'Семья', 'Многие']],
    ];
    $uz = [
        ['key' => 'wake',     'day' => 2, 'q' => "Odatda soat nechada uyg'onasan? Uxlayotgan paytingga vazifa qo'ymaslik uchun so'rayapman.",
         'opts' => ['6:00 gacha', '6:00–8:00', '8:00–10:00', "10:00 dan keyin"]],
        ['key' => 'why',      'day' => 3, 'q' => "Bir jumlada ayt: bu senga aslida nima uchun kerak? Qiyin bo'lganda shu so'zlaringga qaytaman.", 'opts' => []],
        ['key' => 'obstacle', 'day' => 4, 'q' => "Bunday ishlarni odatda nima sababdan tashlab yuborasan?",
         'opts' => ["Vaqt yo'q", 'Charchayman', "Qiziqish yo'qoladi", "Juda ko'p reja qilaman", "Yolg'iz qiyin"]],
        ['key' => 'tone',     'day' => 5, 'q' => "Pasayib ketganingda sen bilan qanday gaplashgan yaxshi?",
         'opts' => ["To'g'ridan va qat'iy", 'Xotirjam va ishga oid', "Yumshoq va qo'llab-quvvatlab"]],
        ['key' => 'city',     'day' => 6, 'q' => "Qaysi shahardasan? Yoningdagi oflayn uchrashuvlarni taklif qilish uchun kerak.", 'opts' => []],
        ['key' => 'noticed',  'day' => 7, 'q' => "Bir hafta o'tdi. O'zingda nimani sezding — juda mayda bo'lsa ham?", 'opts' => []],
        ['key' => 'evening',  'day' => 10,'q' => "Kechqurunlaringni ko'pincha nima yeb qo'yadi?",
         'opts' => ['Telefon', 'Ish', 'Oila va uy', 'Charchoq', "Do'stlar"]],
        ['key' => 'future',   'day' => 14,'q' => "180 kundan keyingi oddiy kuningni tasvirla. Qanday uyg'onasan, nima qilasan, o'zingni qanday his qilasan? Bu — o'zingga xat, uni 90- va 180-kunda ko'rsataman.", 'opts' => []],
        ['key' => 'support',  'day' => 21,'q' => "Hayotingda bu maqsading haqida kim biladi?",
         'opts' => ['Hech kim', 'Bitta odam', 'Oila', "Ko'pchilik"]],
    ];
    return $lang === 'uz' ? $uz : $ru;
}

/* ---------------- ТРЕКИ И ИНТЕЙК ----------------
   Если человек идёт в «тело» — без роста/веса/режима план будет пустой болтовнёй.
   Анкета показывается один раз, на 2-й день, и только по нужному треку. */
function track_intake(string $track, string $lang = 'ru'): array {
    $ru = [
        'body' => ['title' => 'Пара цифр, чтобы план стал твоим',
            'note' => 'Это нужно только для расчёта нагрузки и ритма. Группа этого не видит. Мы не ставим диагнозов и не назначаем калораж — при заболеваниях сначала врач.',
            'fields' => [
                ['k' => 'sex',     'l' => 'Пол',                    't' => 'choice', 'o' => ['мужской', 'женский']],
                ['k' => 'age',     'l' => 'Возраст',                't' => 'number', 'min' => 14, 'max' => 90],
                ['k' => 'height',  'l' => 'Рост, см',               't' => 'number', 'min' => 120, 'max' => 230],
                ['k' => 'weight',  'l' => 'Вес сейчас, кг',         't' => 'number', 'min' => 30, 'max' => 300],
                ['k' => 'target',  'l' => 'Куда идём',              't' => 'choice', 'o' => ['сбросить', 'набрать', 'удержать и укрепить']],
                ['k' => 'meals',   'l' => 'Сколько раз в день ешь', 't' => 'choice', 'o' => ['1–2', '3', '4 и больше', 'как придётся']],
                ['k' => 'cook',    'l' => 'Готовишь дома?',         't' => 'choice', 'o' => ['почти всегда', 'иногда', 'почти никогда']],
                ['k' => 'sport',   'l' => 'Сейчас двигаешься',      't' => 'choice', 'o' => ['почти нет', 'хожу пешком', '1–2 раза в неделю', '3+ раза в неделю']],
                ['k' => 'sleep',   'l' => 'Сон, часов',             't' => 'choice', 'o' => ['меньше 6', '6–7', '7–8', 'больше 8']],
                ['k' => 'limits',  'l' => 'Ограничения по здоровью, травмы, беременность', 't' => 'text'],
            ]],
        'faith' => ['title' => 'Чтобы путь был по твоему уровню',
            'note' => 'Мы не учим религии и не спорим о толкованиях. Мы помогаем удержать регулярность и найти рядом людей с тем же ритмом.',
            'fields' => [
                ['k' => 'level',   'l' => 'Как сейчас',             't' => 'choice', 'o' => ['начинаю с нуля', 'знаю основы', 'соблюдаю нерегулярно', 'соблюдаю почти всегда']],
                ['k' => 'hard',    'l' => 'Что даётся тяжелее всего','t' => 'choice','o' => ['ранний подъём', 'дневное время на работе', 'вечер и усталость', 'знания и правильность']],
                ['k' => 'learn',   'l' => 'Хочешь ли изучать основы','t' => 'choice','o' => ['да, коротко каждый день', 'да, раз в неделю', 'нет, только регулярность']],
                ['k' => 'together','l' => 'Искать людей с таким же ритмом?', 't' => 'choice', 'o' => ['да', 'нет']],
            ]],
        'work' => ['title' => 'Чтобы задания били в твою цель',
            'note' => 'Это нужно, чтобы не давать общих советов вроде «работай усерднее».',
            'fields' => [
                ['k' => 'stage',   'l' => 'На каком этапе',         't' => 'choice', 'o' => ['есть только идея', 'первые клиенты', 'работает, но мало', 'расту']],
                ['k' => 'hours',   'l' => 'Часов в день на это',    't' => 'choice', 'o' => ['меньше 1', '1–2', '3–5', 'весь день']],
                ['k' => 'metric',  'l' => 'Главная цифра, которую хочешь сдвинуть', 't' => 'text'],
                ['k' => 'block',   'l' => 'Что мешает больше всего','t' => 'choice', 'o' => ['нет клиентов', 'нет времени', 'нет денег', 'нет знаний', 'откладываю']],
            ]],
        'mind' => ['title' => 'Пара уточнений про учёбу',
            'note' => '',
            'fields' => [
                ['k' => 'subject', 'l' => 'Что именно изучаешь',    't' => 'text'],
                ['k' => 'level',   'l' => 'Уровень сейчас',         't' => 'choice', 'o' => ['с нуля', 'базовый', 'средний', 'выше среднего']],
                ['k' => 'deadline','l' => 'Есть ли экзамен или срок','t' => 'text'],
                ['k' => 'minutes', 'l' => 'Минут в день реально',   't' => 'choice', 'o' => ['15', '30', '60', '90+']],
            ]],
    ];
    $uz = $ru; // тексты анкет дублируются; переводятся при первой правке контента
    $set = ($lang === 'uz' ? $uz : $ru);
    return $set[$track] ?? [];
}

/* Какой трек у категории цели */
function track_of(string $category): string {
    return [
        'health' => 'body', 'fitness' => 'body',
        'learning' => 'mind',
        'business' => 'work', 'finance' => 'work',
        'spirit' => 'faith', 'relations' => 'faith',
        'discipline' => '',
    ][$category] ?? '';
}

/* Публичное направление — то, что видит группа вместо конкретной цели */
function direction_of(string $category, string $lang = 'ru'): array {
    $m = [
        'health'     => ['ru' => 'Тело',       'uz' => 'Tana',        'icon' => '❤️'],
        'fitness'    => ['ru' => 'Тело',       'uz' => 'Tana',        'icon' => '💪'],
        'learning'   => ['ru' => 'Разум',      'uz' => 'Aql',         'icon' => '🧠'],
        'business'   => ['ru' => 'Дело',       'uz' => 'Ish',         'icon' => '💼'],
        'finance'    => ['ru' => 'Дело',       'uz' => 'Ish',         'icon' => '💰'],
        'discipline' => ['ru' => 'Дисциплина', 'uz' => 'Intizom',     'icon' => '🔥'],
        'relations'  => ['ru' => 'Люди',       'uz' => 'Odamlar',     'icon' => '🤝'],
        'spirit'     => ['ru' => 'Душа',       'uz' => 'Ruh',         'icon' => '🌱'],
    ];
    $x = $m[$category] ?? $m['discipline'];
    return ['label' => $x[$lang] ?? $x['ru'], 'icon' => $x['icon']];
}

/* ---------------- МЕТКИ ----------------
   Назначаются вручную из админки. Ставятся рядом с именем везде. */
function badge_catalog(): array {
    return [
        'founder'  => ['icon' => '🌟', 'ru' => 'Основатель',        'uz' => 'Asoschi',        'color' => '#8B7BFF'],
        'donor'    => ['icon' => '💎', 'ru' => 'Меценат',           'uz' => 'Homiy',          'color' => '#4C9AFF'],
        'gold'     => ['icon' => '👑', 'ru' => 'Золотой участник',  'uz' => 'Oltin ishtirokchi','color' => '#FFC53D'],
        'moder'    => ['icon' => '🛡', 'ru' => 'Модератор',         'uz' => 'Moderator',      'color' => '#28C486'],
        'leader'   => ['icon' => '⭐', 'ru' => 'Лидер группы',      'uz' => 'Jamoa yetakchisi','color' => '#FF8A4C'],
        'league'   => ['icon' => '🏛', 'ru' => 'Глава лиги',        'uz' => 'Liga rahbari',   'color' => '#FF5A1F'],
        'veteran'  => ['icon' => '🎖', 'ru' => 'Прошёл 180',        'uz' => "180 kunni o'tgan",'color' => '#28C486'],
        'comeback' => ['icon' => '🔁', 'ru' => 'Вернулся',          'uz' => 'Qaytgan',        'color' => '#FFB020'],
    ];
}
function badge_view(string $key, string $lang = 'ru'): ?array {
    $c = badge_catalog();
    if ($key === '' || !isset($c[$key])) return null;
    $b = $c[$key];
    return ['key' => $key, 'icon' => $b['icon'], 'label' => $b[$lang] ?? $b['ru'], 'color' => $b['color']];
}

/* ---------------- ЖАЛОБЫ ---------------- */
function report_reasons(string $lang = 'ru'): array {
    $ru = [
        'spam'     => 'Спам или реклама',
        'money'    => 'Просит деньги / мошенничество',
        'insult'   => 'Оскорбления, грубость',
        'content'  => 'Неприемлемый контент',
        'fake'     => 'Выдаёт себя за другого',
        'cheat'    => 'Обман в заданиях, накрутка',
        'harass'   => 'Навязчивое общение, домогательство',
        'danger'   => 'Опасное поведение, угроза здоровью',
        'other'    => 'Другое',
    ];
    $uz = [
        'spam'     => 'Spam yoki reklama',
        'money'    => "Pul so'rayapti / firibgarlik",
        'insult'   => 'Haqorat, qo\'pollik',
        'content'  => 'Nomaqbul kontent',
        'fake'     => "O'zini boshqa odam qilib ko'rsatyapti",
        'cheat'    => 'Vazifalarda aldov',
        'harass'   => "Bezovta qiluvchi muloqot",
        'danger'   => "Xavfli xatti-harakat",
        'other'    => 'Boshqa',
    ];
    return $lang === 'uz' ? $uz : $ru;
}

/* ---------------- ВСТРЕЧИ ---------------- */
function meeting_agenda(int $weekNo, string $lang = 'ru'): array {
    $ru = [
        ['Знакомство', 'Каждый за 2 минуты: имя, направление, что хочет через 180 дней. Без подробностей — только суть.'],
        ['Первая неделя', 'Что получилось, что нет. Один совет соседу справа.'],
        ['Ритм', 'У кого сбился режим и почему. Договориться о времени, когда каждый делает своё дело.'],
        ['Срывы', 'У кого был пропуск. Как вернулся. Что помогло.'],
        ['Половина этапа', 'Сверить цифры: выполнение за 2 недели. Кто вырос сильнее всех относительно себя.'],
        ['Что мешает', 'Каждый называет одну помеху. Группа предлагает по одному решению.'],
        ['Итог месяца', 'Чекпоинт: измерить результат. Обновить цель, если она изменилась.'],
    ];
    $uz = [
        ['Tanishuv', "Har biri 2 daqiqada: ism, yo'nalish, 180 kundan keyin nima istaydi."],
        ['Birinchi hafta', "Nima chiqdi, nima yo'q. O'ngdagi qo'shniga bitta maslahat."],
        ['Ritm', "Kimning tartibi buzildi va nega. Har kim o'z ishini qiladigan vaqtga kelishish."],
        ['Uzilishlar', "Kimda tanaffus bo'ldi. Qanday qaytdi. Nima yordam berdi."],
        ['Bosqich yarmi', "Raqamlarni solishtirish: 2 haftalik bajarish."],
        ['Nima xalaqit beradi', "Har biri bitta to'siqni aytadi. Jamoa bittadan yechim taklif qiladi."],
        ['Oy yakuni', "Nazorat nuqtasi: natijani o'lchash."],
    ];
    $set = $lang === 'uz' ? $uz : $ru;
    $x = $set[($weekNo - 1) % count($set)];
    return ['title' => $x[0], 'agenda' => $x[1] ?? ''];
}
