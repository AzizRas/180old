<?php
/* ============================================================
   Контент: этапы, категории целей, библиотека заданий,
   вопросы интервью, тексты коуча. RU + UZ.
   Всё это работает без ИИ. Если ключ ИИ задан — он дополняет,
   а не заменяет: при любой ошибке система падает сюда.
   ============================================================ */

function stages_def(): array {
    return [
        1 => ['key' => 'START',          'ru' => 'СТАРТ',        'uz' => 'START',
              'ru_sub' => 'Начало и диагностика', 'uz_sub' => 'Boshlanish va tashxis'],
        2 => ['key' => 'FOUNDATION',     'ru' => 'ФУНДАМЕНТ',    'uz' => 'POYDEVOR',
              'ru_sub' => 'Создание основы', 'uz_sub' => 'Asos yaratish'],
        3 => ['key' => 'MOMENTUM',       'ru' => 'РАЗГОН',       'uz' => 'TEZLANISH',
              'ru_sub' => 'Набор темпа', 'uz_sub' => "Sur'at olish"],
        4 => ['key' => 'TRANSFORMATION', 'ru' => 'ПЕРЕЛОМ',      'uz' => 'BURILISH',
              'ru_sub' => 'Заметные изменения', 'uz_sub' => "Sezilarli o'zgarishlar"],
        5 => ['key' => 'CONSOLIDATION',  'ru' => 'ЗАКРЕПЛЕНИЕ',  'uz' => 'MUSTAHKAMLASH',
              'ru_sub' => 'Закрепление результата', 'uz_sub' => 'Natijani mustahkamlash'],
        6 => ['key' => 'NEW SELF',       'ru' => 'НОВЫЙ Я',      'uz' => 'YANGI MEN',
              'ru_sub' => 'Новая версия себя', 'uz_sub' => "O'zingning yangi versiyang"],
    ];
}

function categories_def(): array {
    return [
        'health'     => ['ru' => 'Здоровье и вес',      'uz' => "Sog'liq va vazn",       'stat' => 'health',     'icon' => '❤️'],
        'fitness'    => ['ru' => 'Спорт и форма',       'uz' => 'Sport va jismoniy shakl','stat' => 'health',     'icon' => '💪'],
        'learning'   => ['ru' => 'Учёба и язык',        'uz' => "O'qish va til",          'stat' => 'mind',       'icon' => '🧠'],
        'business'   => ['ru' => 'Бизнес и карьера',    'uz' => 'Biznes va karyera',      'stat' => 'business',   'icon' => '💼'],
        'discipline' => ['ru' => 'Дисциплина и привычки','uz' => 'Intizom va odatlar',    'stat' => 'discipline', 'icon' => '🔥'],
        'relations'  => ['ru' => 'Отношения и семья',   'uz' => 'Munosabatlar va oila',   'stat' => 'soul',       'icon' => '🤝'],
        'spirit'     => ['ru' => 'Внутреннее состояние','uz' => 'Ichki holat',            'stat' => 'soul',       'icon' => '🌱'],
        'finance'    => ['ru' => 'Финансы',             'uz' => 'Moliya',                 'stat' => 'business',   'icon' => '💰'],
    ];
}

/* Формат задания:
   [ru, uz, stat, points, [этапы], мин_интенсивность, verify]
   verify: self | number | photo | question
*/
function task_library(): array {
    return [

    'health' => [
        ['Выпить 2 литра воды',                       "2 litr suv ichish",                        'health','8',[1,2,3,4,5,6],1,'self'],
        ['Лечь спать до 23:30',                       "23:30 gacha uxlashga yotish",              'health','10',[1,2,3,4,5,6],1,'self'],
        ['Пройти 6 000 шагов',                        "6 000 qadam yurish",                       'health','10',[1,2],1,'number'],
        ['Пройти 8 000 шагов',                        "8 000 qadam yurish",                       'health','12',[3,4],2,'number'],
        ['Пройти 10 000 шагов',                       "10 000 qadam yurish",                      'health','14',[5,6],3,'number'],
        ['Записать всё, что съел за день',            "Kun davomida yegan hamma narsani yozish",  'health','10',[1,2,3],1,'self'],
        ['День без сахара и сладких напитков',        "Shakarsiz va shirin ichimliksiz kun",      'health','14',[2,3,4,5,6],2,'self'],
        ['Завтрак с белком',                          "Oqsilli nonushta",                         'health','8',[2,3,4,5,6],1,'self'],
        ['Взвеситься и записать вес',                 "Vaznni o'lchab yozib qo'yish",             'health','8',[1,3,5,6],1,'number'],
        ['Не есть за 3 часа до сна',                  "Uyqudan 3 soat oldin ovqat yemaslik",      'health','12',[2,3,4,5,6],2,'self'],
        ['Приготовить еду дома вместо доставки',      "Yetkazib berish o'rniga uyda ovqat tayyorlash",'health','12',[2,3,4,5,6],2,'self'],
        ['15 минут на свежем воздухе',                "15 daqiqa toza havoda",                    'health','8',[1,2,3,4,5,6],1,'self'],
        ['Фото тарелки на обед',                      "Tushlik likopchasining surati",            'health','10',[3,4,5],2,'photo'],
        ['День без перекусов между приёмами пищи',    "Ovqatlar orasida gazaksiz kun",            'health','14',[4,5,6],3,'self'],
        ['Замерить объёмы (талия, грудь, бёдра)',     "O'lchamlarni olish (bel, ko'krak, son)",   'health','10',[1,3,6],1,'number'],
    ],

    'fitness' => [
        ['Разминка 10 минут',                          "10 daqiqa isinish",                        'health','8',[1,2],1,'self'],
        ['Тренировка 20 минут',                        "20 daqiqa mashg'ulot",                     'health','14',[1,2,3],1,'self'],
        ['Тренировка 40 минут',                        "40 daqiqa mashg'ulot",                     'health','18',[3,4,5,6],2,'self'],
        ['30 отжиманий (можно частями)',               "30 marta yotib turish (bo'lib bajarsa ham bo'ladi)",'health','12',[1,2,3],1,'number'],
        ['60 отжиманий (можно частями)',               "60 marta yotib turish",                    'health','16',[4,5,6],3,'number'],
        ['Планка: держать личный максимум',            "Planka: shaxsiy maksimumni ushlash",       'health','12',[2,3,4,5,6],2,'number'],
        ['Пробежка / быстрая ходьба 2 км',             "2 km yugurish yoki tez yurish",            'health','16',[2,3],2,'number'],
        ['Пробежка 4 км',                              "4 km yugurish",                            'health','20',[4,5,6],3,'number'],
        ['Растяжка 10 минут перед сном',               "Uyqudan oldin 10 daqiqa cho'zilish",       'health','8',[1,2,3,4,5,6],1,'self'],
        ['Подняться по лестнице вместо лифта',         "Lift o'rniga zinadan chiqish",             'health','6',[1,2,3,4,5,6],1,'self'],
        ['Фото после тренировки',                      "Mashg'ulotdan keyingi surat",              'health','12',[2,3,4,5,6],2,'photo'],
        ['День активного отдыха: прогулка 1 час',      "Faol dam olish: 1 soat sayr",              'health','14',[3,4,5,6],2,'self'],
    ],

    'learning' => [
        ['Выучить 10 новых слов',                      "10 ta yangi so'z o'rganish",               'mind','12',[1,2,3],1,'question'],
        ['Выучить 20 новых слов',                      "20 ta yangi so'z o'rganish",               'mind','16',[4,5,6],3,'question'],
        ['Повторить вчерашние слова',                  "Kechagi so'zlarni takrorlash",             'mind','8',[1,2,3,4,5,6],1,'self'],
        ['20 минут аудирования',                       "20 daqiqa tinglash mashqi",                'mind','12',[1,2,3,4,5,6],1,'self'],
        ['Прочитать 5 страниц на изучаемом языке',     "O'rganayotgan tilda 5 bet o'qish",         'mind','14',[2,3,4,5,6],2,'self'],
        ['Написать 5 предложений о своём дне',         "Kuningiz haqida 5 ta gap yozish",          'mind','12',[1,2,3,4,5,6],1,'question'],
        ['15 минут говорить вслух (можно с собой)',    "15 daqiqa ovoz chiqarib gapirish",         'mind','14',[2,3,4,5,6],2,'self'],
        ['Посмотреть видео 10 минут без субтитров',    "10 daqiqa subtitrsiz video ko'rish",       'mind','14',[3,4,5,6],2,'self'],
        ['Разобрать одну грамматическую тему',         "Bitta grammatika mavzusini o'rganish",     'mind','16',[2,3,4,5,6],2,'self'],
        ['Диалог с носителем или партнёром 10 минут',  "Suhbatdosh bilan 10 daqiqa dialog",        'mind','20',[3,4,5,6],3,'self'],
        ['Прочитать 20 страниц книги',                 "Kitobdan 20 bet o'qish",                   'mind','14',[1,2,3,4,5,6],1,'self'],
        ['Записать 3 главные мысли из прочитанного',   "O'qiganingizdan 3 ta asosiy fikr yozish",  'mind','12',[2,3,4,5,6],2,'question'],
        ['Пройти один урок курса',                     "Kursdan bitta darsni o'tish",              'mind','16',[1,2,3,4,5,6],1,'self'],
    ],

    'business' => [
        ['Определить 1 главную задачу дня и сделать её первой', "Kunning 1 ta asosiy vazifasini aniqlab, birinchi bajarish",'business','16',[1,2,3,4,5,6],1,'self'],
        ['60 минут глубокой работы без телефона',      "Telefonsiz 60 daqiqa chuqur ish",          'business','18',[2,3,4,5,6],2,'self'],
        ['Связаться с 3 потенциальными клиентами',     "3 ta potensial mijoz bilan bog'lanish",    'business','20',[2,3,4,5,6],2,'number'],
        ['Связаться с 10 потенциальными клиентами',    "10 ta potensial mijoz bilan bog'lanish",   'business','26',[4,5,6],4,'number'],
        ['Посчитать доходы и расходы за вчера',        "Kechagi daromad va xarajatni hisoblash",   'business','12',[1,2,3,4,5,6],1,'self'],
        ['Описать своё предложение в 3 предложениях',  "Taklifingizni 3 ta gapda yozish",          'business','16',[1,2],1,'question'],
        ['Собрать обратную связь у 1 клиента',         "1 ta mijozdan fikr olish",                 'business','18',[2,3,4,5,6],2,'self'],
        ['Проанализировать 1 конкурента',              "1 ta raqobatchini tahlil qilish",          'business','16',[2,3],2,'self'],
        ['Сделать 1 публикацию о своём деле',          "Ishingiz haqida 1 ta post qilish",         'business','16',[2,3,4,5,6],2,'self'],
        ['Убрать одну задачу, которая не даёт результата',"Natija bermayotgan bitta vazifani olib tashlash",'business','14',[3,4,5,6],3,'self'],
        ['Спланировать следующую неделю',              "Keyingi haftani rejalashtirish",           'business','16',[1,2,3,4,5,6],1,'self'],
        ['Изучить 1 навык для работы 30 минут',        "Ish uchun 1 ta ko'nikmani 30 daqiqa o'rganish",'business','16',[2,3,4,5,6],2,'self'],
        ['Посчитать: сколько стоит мой час',           "Hisoblang: 1 soatim qancha turadi",        'business','14',[3,5],2,'question'],
    ],

    'discipline' => [
        ['Встать в назначенное время без откладывания',"Belgilangan vaqtda, kechiktirmasdan turish",'discipline','14',[1,2,3,4,5,6],1,'self'],
        ['Заправить постель',                          "To'shakni yig'ish",                        'discipline','6',[1,2,3,4,5,6],1,'self'],
        ['Первый час дня без соцсетей',                "Kunning birinchi soati ijtimoiy tarmoqsiz",'discipline','14',[1,2,3,4,5,6],1,'self'],
        ['Экранное время меньше вчерашнего',           "Ekran vaqti kechagidan kam",               'discipline','14',[2,3,4,5,6],2,'number'],
        ['Сделать самое неприятное дело первым',       "Eng yoqimsiz ishni birinchi qilish",       'discipline','18',[2,3,4,5,6],2,'self'],
        ['Навести порядок 15 минут',                   "15 daqiqa tartib qilish",                  'discipline','8',[1,2,3,4,5,6],1,'self'],
        ['День без прокрастинации: 0 отложенных задач',"Kechiktirishsiz kun: 0 ta qoldirilgan vazifa",'discipline','20',[4,5,6],3,'self'],
        ['Подвести итог дня в 3 строках',              "Kun yakunini 3 qatorda yozish",            'discipline','10',[1,2,3,4,5,6],1,'question'],
        ['Сказать «нет» одной лишней просьбе',         "Bitta ortiqcha iltimosga «yo'q» deyish",   'discipline','14',[3,4,5,6],3,'self'],
        ['Соблюсти режим сна два дня подряд',          "Ketma-ket ikki kun uyqu tartibiga rioya qilish",'discipline','16',[3,4,5,6],2,'self'],
        ['Убрать один отвлекающий фактор с телефона',  "Telefondan bitta chalg'ituvchi narsani olib tashlash",'discipline','12',[1,2,3],1,'self'],
    ],

    'relations' => [
        ['Позвонить родителям',                        "Ota-onaga qo'ng'iroq qilish",              'soul','14',[1,2,3,4,5,6],1,'self'],
        ['30 минут с семьёй без телефона',             "Oila bilan telefonsiz 30 daqiqa",          'soul','16',[1,2,3,4,5,6],1,'self'],
        ['Сказать близкому человеку что-то хорошее',   "Yaqin insonga yaxshi gap aytish",          'soul','10',[1,2,3,4,5,6],1,'self'],
        ['Написать другу, с которым давно не общались',"Uzoq vaqt gaplashmagan do'stga yozish",    'soul','12',[2,3,4,5,6],1,'self'],
        ['Выслушать человека, не перебивая',           "Odamni bo'lmasdan tinglash",               'soul','12',[2,3,4,5,6],2,'self'],
        ['Извиниться там, где был неправ',             "Noto'g'ri bo'lgan joyda uzr so'rash",      'soul','20',[3,4,5,6],3,'self'],
        ['Совместный ужин без гаджетов',               "Gadjetsiz birgalikda kechki ovqat",        'soul','14',[2,3,4,5,6],2,'self'],
        ['Спросить у близкого: «Чем я могу помочь?»',  "Yaqiningizdan so'rang: «Nimada yordam bera olaman?»",'soul','12',[2,3,4,5,6],2,'self'],
        ['Провести 1 час с ребёнком/близким по-настоящему',"Farzand yoki yaqin bilan 1 soat chinakam o'tkazish",'soul','18',[3,4,5,6],2,'self'],
    ],

    'spirit' => [
        ['5 минут тишины без телефона',                "Telefonsiz 5 daqiqa sukunat",              'soul','10',[1,2,3,4,5,6],1,'self'],
        ['Записать 3 вещи, за которые благодарен',     "Minnatdor bo'lgan 3 narsani yozish",       'soul','10',[1,2,3,4,5,6],1,'question'],
        ['10 минут дыхательной практики',              "10 daqiqa nafas mashqi",                   'soul','12',[1,2,3,4,5,6],1,'self'],
        ['Прогулка в одиночестве 20 минут без наушников',"Quloqchinsiz 20 daqiqa yolg'iz sayr",    'soul','12',[2,3,4,5,6],2,'self'],
        ['Написать, что беспокоит, и закрыть тетрадь', "Nima tashvishlantirayotganini yozib, daftarni yopish",'soul','12',[2,3,4,5,6],2,'self'],
        ['День без жалоб',                             "Shikoyatsiz kun",                          'soul','16',[3,4,5,6],3,'self'],
        ['Помочь кому-то без выгоды',                  "Manfaatsiz kimgadir yordam berish",        'soul','16',[2,3,4,5,6],2,'self'],
        ['Вечерняя рефлексия: что я понял сегодня',    "Kechki mulohaza: bugun nimani angladim",   'soul','12',[1,2,3,4,5,6],1,'question'],
        ['Час без информационного шума',               "Bir soat axborot shovqinisiz",             'soul','14',[3,4,5,6],3,'self'],
    ],

    'finance' => [
        ['Записать все траты за день',                 "Kunlik barcha xarajatlarni yozish",        'business','12',[1,2,3,4,5,6],1,'self'],
        ['День без спонтанных покупок',                "Rejasiz xaridlarsiz kun",                  'business','14',[1,2,3,4,5,6],1,'self'],
        ['Отложить фиксированную сумму',               "Belgilangan summani jamg'arish",           'business','16',[2,3,4,5,6],2,'number'],
        ['Посчитать все подписки и отключить лишние',  "Barcha obunalarni hisoblab, keraksizini o'chirish",'business','16',[1,3],1,'self'],
        ['Составить бюджет на неделю',                 "Haftalik byudjet tuzish",                  'business','16',[1,2,3,4,5,6],1,'self'],
        ['Найти один способ дополнительного дохода',   "Qo'shimcha daromadning bir yo'lini topish",'business','20',[3,4,5,6],3,'self'],
        ['Проверить долги и составить план погашения', "Qarzlarni tekshirib, to'lash rejasini tuzish",'business','18',[2,4],2,'self'],
        ['Сравнить траты этой недели с прошлой',       "Bu hafta xarajatlarini o'tgan hafta bilan solishtirish",'business','14',[3,4,5,6],2,'self'],
    ],
    ];
}

/* Базовые привычки — идут всем, независимо от цели.
   Именно они не дают герою «перекоситься» в одну характеристику. */
function base_habits(): array {
    return [
        ['Сон не меньше 7 часов',        "Kamida 7 soat uyqu",                'health','8','self'],
        ['5 минут тишины или дыхания',   "5 daqiqa sukunat yoki nafas mashqi",'soul','6','self'],
        ['Итог дня в 3 строках',         "Kun yakuni 3 qatorda",              'discipline','8','question'],
        ['10 минут чтения',              "10 daqiqa o'qish",                  'mind','8','self'],
        ['Прогулка или разминка',        "Sayr yoki isinish",                 'health','8','self'],
        ['Записать главную задачу на завтра', "Ertangi asosiy vazifani yozish",'business','8','question'],
    ];
}

/* Тексты кризисных дней */
function crisis_text(int $day, string $lang): array {
    $t = [
        21 => [
            'ru' => ['Первый спад', 'День 21 — это тот момент, когда новизна закончилась, а результат ещё не виден. Почти все останавливаются именно здесь. Ты не сломался — ты дошёл до места, где начинается настоящая работа.'],
            'uz' => ['Birinchi pasayish', "21-kun — yangilik tugagan, natija esa hali ko'rinmagan payt. Deyarli hamma shu yerda to'xtaydi. Sen sinmading — sen haqiqiy ish boshlanadigan joyga yetding."],
        ],
        45 => [
            'ru' => ['Проверка реальностью', 'День 45. Сейчас становится ясно, какие задачи ты выбрал под себя, а какие — «как надо». Оставь то, что работает. От остального можно отказаться без чувства вины.'],
            'uz' => ['Haqiqat sinovi', "45-kun. Endi qaysi vazifalar o'zingga mos, qaysilari «kerak edi» ekani ayon bo'ladi. Ishlayotganini qoldir. Qolganidan aybdorlik hissisiz voz kechsa bo'ladi."],
        ],
        90 => [
            'ru' => ['Экватор', 'Половина пути. Оглянись назад: там человек, который ещё ничего не начал. Сравни себя не с идеалом, а с ним.'],
            'uz' => ['Ekvator', "Yo'lning yarmi. Orqaga qara: u yerda hali hech narsa boshlamagan odam turibdi. O'zingni ideal bilan emas, o'sha odam bilan solishtir."],
        ],
        135 => [
            'ru' => ['Второе дыхание', 'День 135. Осталось меньше, чем пройдено. Здесь важно не ускоряться, а не останавливаться.'],
            'uz' => ['Ikkinchi nafas', "135-kun. O'tilganidan kamrog'i qoldi. Bu yerda tezlashish emas, to'xtamaslik muhim."],
        ],
        170 => [
            'ru' => ['Последнее сопротивление', 'День 170. Перед финишем всегда хочется расслабиться. Десять дней — это меньше, чем ты уже выдержал.'],
            'uz' => ['Oxirgi qarshilik', "170-kun. Marradan oldin doim bo'shashgingiz keladi. O'n kun — bu allaqachon chidaganingizdan kam."],
        ],
    ];
    $x = $t[$day][$lang] ?? $t[$day]['ru'] ?? null;
    return $x ? ['title' => $x[0], 'text' => $x[1]] : ['title' => '', 'text' => ''];
}

/* Вопросы интервью. Работают и без ИИ. */
function interview_questions(string $lang = 'ru'): array {
    $Q = [
        ['id' => 'goal',      'type' => 'text',
         'ru' => 'Что ты хочешь изменить за 180 дней? Опиши одним предложением, как будто рассказываешь другу.',
         'uz' => "180 kunda nimani o'zgartirmoqchisan? Do'stingga aytayotgandek bir gapda yoz."],

        ['id' => 'category',  'type' => 'choice',
         'ru' => 'К какой области это ближе всего?',
         'uz' => 'Bu qaysi sohaga yaqinroq?'],

        ['id' => 'why',       'type' => 'text',
         'ru' => 'Почему именно сейчас? Что произойдёт, если через 180 дней ничего не изменится?',
         'uz' => "Nega aynan hozir? Agar 180 kundan keyin hech narsa o'zgarmasa, nima bo'ladi?"],

        ['id' => 'tried',     'type' => 'text',
         'ru' => 'Ты уже пробовал это раньше? Что произошло тогда?',
         'uz' => "Buni ilgari sinab ko'rganmisan? O'shanda nima bo'lgan?"],

        ['id' => 'obstacle',  'type' => 'choice',
         'ru' => 'Из-за чего ты обычно бросаешь?',
         'uz' => 'Odatda nima sababdan tashlab qo\'yasan?'],

        ['id' => 'minutes',   'type' => 'choice',
         'ru' => 'Сколько минут в день ты РЕАЛЬНО можешь уделять? Не в идеальный день, а в обычный.',
         'uz' => "Kuniga HAQIQATDA necha daqiqa ajrata olasan? Ideal kunda emas, oddiy kunda."],

        ['id' => 'strength',  'type' => 'text',
         'ru' => 'В чём ты силён? Что у тебя получается легко, когда получается?',
         'uz' => "Nimada kuchlisan? Qachonki chiqsa, nima senga oson beriladi?"],

        ['id' => 'time',      'type' => 'choice',
         'ru' => 'В какое время суток тебе проще всего что-то делать?',
         'uz' => "Kunning qaysi qismida biror ish qilish osonroq?"],

        ['id' => 'future',    'type' => 'text',
         'ru' => 'Опиши свой обычный день через 180 дней. Как ты просыпаешься, что делаешь, как себя чувствуешь? Это письмо будущему себе — ты увидишь его на 90-й и 180-й день.',
         'uz' => "180 kundan keyingi oddiy kuningni tasvirla. Qanday uyg'onasan, nima qilasan, o'zingni qanday his qilasan? Bu — kelajakdagi o'zingga xat: uni 90- va 180-kunda ko'rasan."],
    ];

    $choices = [
        'category' => array_map(function ($k) use ($lang) {
            $c = categories_def()[$k];
            return ['value' => $k, 'label' => $c['icon'] . ' ' . $c[$lang]];
        }, array_keys(categories_def())),

        'obstacle' => [
            ['value' => 'time',    'label' => $lang === 'uz' ? "Vaqt yetmaydi"            : 'Не хватает времени'],
            ['value' => 'energy',  'label' => $lang === 'uz' ? "Charchayman"               : 'Быстро устаю'],
            ['value' => 'motiv',   'label' => $lang === 'uz' ? "Motivatsiya yo'qoladi"     : 'Пропадает мотивация'],
            ['value' => 'hard',    'label' => $lang === 'uz' ? "Rejani juda og'ir qilaman" : 'Ставлю слишком тяжёлый план'],
            ['value' => 'alone',   'label' => $lang === 'uz' ? "Yolg'iz qiyin"             : 'Одному тяжело'],
            ['value' => 'stress',  'label' => $lang === 'uz' ? "Stress va sharoit"         : 'Стресс и обстоятельства'],
        ],

        'minutes' => [
            ['value' => '15', 'label' => $lang === 'uz' ? '15 daqiqa' : '15 минут'],
            ['value' => '30', 'label' => $lang === 'uz' ? '30 daqiqa' : '30 минут'],
            ['value' => '60', 'label' => $lang === 'uz' ? '1 soat'    : '1 час'],
            ['value' => '120','label' => $lang === 'uz' ? '2 soat va undan ko\'p' : '2 часа и больше'],
        ],

        'time' => [
            ['value' => 'morning', 'label' => $lang === 'uz' ? 'Ertalab'  : 'Утро'],
            ['value' => 'day',     'label' => $lang === 'uz' ? 'Kunduzi'  : 'День'],
            ['value' => 'evening', 'label' => $lang === 'uz' ? 'Kechqurun': 'Вечер'],
            ['value' => 'night',   'label' => $lang === 'uz' ? 'Kechasi'  : 'Ночь'],
        ],
    ];

    $out = [];
    foreach ($Q as $i => $q) {
        $out[] = [
            'id'      => $q['id'],
            'type'    => $q['type'],
            'n'       => $i + 1,
            'total'   => count($Q),
            'text'    => $q[$lang] ?? $q['ru'],
            'choices' => $choices[$q['id']] ?? [],
        ];
    }
    return $out;
}

/* Причины отставания — для диалога адаптации */
function slip_reasons(string $lang = 'ru'): array {
    $r = [
        ['id' => 'time',   'ru' => 'Нет времени',           'uz' => "Vaqt yo'q",              'action' => 'reduce'],
        ['id' => 'hard',   'ru' => 'Задания слишком сложные','uz' => "Vazifalar juda qiyin",  'action' => 'reduce'],
        ['id' => 'tired',  'ru' => 'Устал',                 'uz' => 'Charchadim',             'action' => 'reduce'],
        ['id' => 'stress', 'ru' => 'Стресс',                'uz' => 'Stress',                 'action' => 'pause'],
        ['id' => 'sick',   'ru' => 'Болел',                 'uz' => 'Kasal bo\'ldim',         'action' => 'pause'],
        ['id' => 'motiv',  'ru' => 'Потерял мотивацию',     'uz' => "Motivatsiyani yo'qotdim",'action' => 'refocus'],
        ['id' => 'life',   'ru' => 'Изменились обстоятельства','uz' => "Sharoit o'zgardi",    'action' => 'reduce'],
    ];
    return array_map(fn($x) => ['id' => $x['id'], 'label' => $x[$lang] ?? $x['ru'], 'action' => $x['action']], $r);
}

/* Ответ коуча без ИИ — по правилам */
function coach_rule_reply(string $reasonId, string $lang, int $newIntensity, int $oldIntensity): string {
    $ru = [
        'time'   => "Понял. Сейчас нагрузка выше, чем твой реальный день выдерживает. Я снизил план: с {$oldIntensity} до {$newIntensity} из 5. Задач станет меньше, но они останутся каждый день — это важнее объёма. Через неделю вернёмся к разговору.",
        'hard'   => "Хорошо, что сказал. Слишком сложный план — это не твоя слабость, это моя ошибка в настройке. Снижаю интенсивность с {$oldIntensity} до {$newIntensity}. Сделай завтра одно задание — этого достаточно, чтобы день считался.",
        'tired'  => "Усталость — это сигнал, а не провал. Снижаю план до {$newIntensity} из 5 и на ближайшие дни оставляю только базовые привычки: сон, вода, короткая прогулка. Восстановишься — вернём темп.",
        'stress' => "Стресс съедает ресурс, из которого берётся дисциплина. Ставлю мягкий режим: минимум задач, никаких потерь. Твоя единственная задача сейчас — не исчезать. Одна отметка в день — уже достаточно.",
        'sick'   => "Здоровье выше плана. Я поставил облегчённый режим и не считаю эти дни срывом. Когда почувствуешь силы — скажи, вернём нагрузку постепенно.",
        'motiv'  => "Мотивация всегда заканчивается — на неё нельзя опираться 180 дней. Давай вернёмся к тому, зачем ты начал. Посмотри своё письмо будущему себе в профиле. А план я упростил до {$newIntensity} из 5, чтобы порог входа стал ниже.",
        'life'   => "Обстоятельства меняются — план должен меняться вместе с ними, иначе он не твой. Снижаю до {$newIntensity} из 5. Цель осталась той же, изменился только темп.",
    ];
    $uz = [
        'time'   => "Tushundim. Hozir yuklama sening haqiqiy kuningdan og'irroq. Rejani pasaytirdim: {$oldIntensity} dan {$newIntensity} ga (5 tadan). Vazifa kamayadi, lekin har kuni qoladi — bu hajmdan muhimroq.",
        'hard'   => "Aytganing yaxshi bo'ldi. Juda og'ir reja — bu sening zaifliging emas, mening sozlashdagi xatoyim. Intensivlikni {$oldIntensity} dan {$newIntensity} ga tushiraman. Ertaga bitta vazifa bajarsang ham, kun hisoblanadi.",
        'tired'  => "Charchoq — bu signal, mag'lubiyat emas. Rejani {$newIntensity} ga tushiraman va yaqin kunlarga faqat asosiy odatlarni qoldiraman: uyqu, suv, qisqa sayr.",
        'stress' => "Stress intizom oladigan resursni yeb qo'yadi. Yumshoq rejim qo'ydim: minimal vazifa, hech qanday yo'qotish yo'q. Hozirgi yagona vazifang — g'oyib bo'lmaslik.",
        'sick'   => "Sog'liq rejadan yuqori. Yengillashtirilgan rejim qo'ydim va bu kunlarni uzilish deb hisoblamayman. Kuch yig'ilganda ayt — yuklamani asta qaytaramiz.",
        'motiv'  => "Motivatsiya doim tugaydi — 180 kun unga tayanib bo'lmaydi. Nega boshlaganingga qaytaylik: profildagi kelajakdagi o'zingga yozgan xatingni o'qi. Rejani esa {$newIntensity} ga soddalashtirdim.",
        'life'   => "Sharoit o'zgaradi — reja ham u bilan o'zgarishi kerak, aks holda u sening rejang emas. {$newIntensity} ga tushirdim. Maqsad o'sha, faqat sur'at o'zgardi.",
    ];
    $m = $lang === 'uz' ? $uz : $ru;
    return $m[$reasonId] ?? ($lang === 'uz' ? "Tushundim. Rejani sizga moslashtirdim." : 'Понял. Я подстроил план под тебя.');
}
