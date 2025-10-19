-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Хост: localhost:8889
-- Время создания: Окт 19 2025 г., 12:13
-- Версия сервера: 8.0.40
-- Версия PHP: 8.3.14

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- База данных: `Working`
--

-- --------------------------------------------------------

--
-- Структура таблицы `career_test_options`
--

CREATE TABLE `career_test_options` (
  `id` int NOT NULL,
  `question_id` int NOT NULL,
  `option_text` varchar(255) NOT NULL,
  `direction_hint` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Дамп данных таблицы `career_test_options`
--

INSERT INTO `career_test_options` (`id`, `question_id`, `option_text`, `direction_hint`) VALUES
(1, 1, 'Писать код, настраивать серверы', 'Разработка ПО'),
(2, 1, 'Рисовать макеты, создавать дизайн', 'UI/UX-дизайн'),
(3, 1, 'Общаться с клиентами, вести переговоры', 'Продажи'),
(4, 1, 'Анализировать данные, строить отчёты', 'Анализ данных'),
(5, 2, 'Разработка мобильного приложения', 'Мобильная разработка'),
(6, 2, 'Запуск онлайн-магазина', 'Предпринимательство'),
(7, 2, 'Создание образовательного курса', 'Образование'),
(8, 2, 'Организация благотворительного проекта', 'НКО и социальные проекты'),
(9, 3, 'Работать в одиночку над техническими задачами', 'Разработка ПО'),
(10, 3, 'Участвовать в мозговом штурме с командой', 'Digital-маркетинг'),
(11, 3, 'Проводить встречи и презентации', 'Управление продуктом (PM)'),
(12, 3, 'Исследовать новые методы и технологии', 'Научные исследования'),
(13, 4, 'Создать нейросеть, которая распознаёт изображения', 'Искусственный интеллект'),
(14, 4, 'Разработать API для мобильного приложения', 'Разработка ПО'),
(15, 4, 'Спроектировать интерфейс для нового сервиса', 'UI/UX-дизайн'),
(16, 4, 'Провести A/B-тест и улучшить конверсию', 'Digital-маркетинг'),
(17, 5, 'Когда мой код работает без ошибок', 'Разработка ПО'),
(18, 5, 'Когда пользователь доволен дизайном', 'UI/UX-дизайн'),
(19, 5, 'Когда продажи выросли благодаря моей стратегии', 'Продажи'),
(20, 5, 'Когда удалось выявить скрытую угрозу в системе', 'Кибербезопасность'),
(21, 6, 'Люблю строить прогнозы на основе данных', 'Data Science'),
(22, 6, 'Использую данные для улучшения UX', 'UI/UX-дизайн'),
(23, 6, 'Анализирую метрики эффективности продаж', 'Бизнес-аналитика'),
(24, 6, 'Предпочитаю не работать с сырыми данными', 'Копирайтинг'),
(25, 7, 'Техническая сложность и инновации', 'Искусственный интеллект'),
(26, 7, 'Удобство и эстетика для пользователя', 'UI/UX-дизайн'),
(27, 7, 'Социальная значимость', 'НКО и социальные проекты'),
(28, 7, 'Финансовая выгода', 'Предпринимательство'),
(29, 8, 'Python, TensorFlow, Jupyter Notebook', 'Машинное обучение'),
(30, 8, 'Figma, Adobe XD', 'UI/UX-дизайн'),
(31, 8, 'Git, Docker, Kubernetes', 'DevOps / SRE'),
(32, 8, 'Google Analytics, Meta Ads', 'Digital-маркетинг'),
(33, 9, 'Разбираю на части и пишу алгоритм', 'Разработка ПО'),
(34, 9, 'Провожу интервью с пользователями', 'UI/UX-дизайн'),
(35, 9, 'Ищу уязвимости и устраняю риски', 'Кибербезопасность'),
(36, 9, 'Собираю данные и строю модель', 'Data Science'),
(37, 10, 'Создавать что-то новое с нуля', 'Предпринимательство'),
(38, 10, 'Обучать других и делиться знаниями', 'Образование'),
(39, 10, 'Оптимизировать процессы и системы', 'DevOps / SRE'),
(40, 10, 'Расследовать инциденты и защищать данные', 'Кибербезопасность');

-- --------------------------------------------------------

--
-- Структура таблицы `career_test_questions`
--

CREATE TABLE `career_test_questions` (
  `id` int NOT NULL,
  `question_text` text NOT NULL,
  `order_num` int NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Дамп данных таблицы `career_test_questions`
--

INSERT INTO `career_test_questions` (`id`, `question_text`, `order_num`) VALUES
(1, 'Что вам нравится делать больше всего?', 1),
(2, 'Какой проект вас вдохновляет?', 2),
(3, 'Как вы предпочитаете проводить рабочий день?', 3),
(4, 'Какая задача кажется вам наиболее интересной?', 4),
(5, 'Какой результат вашей работы вы цените больше всего?', 5),
(6, 'Как вы относитесь к работе с данными?', 6),
(7, 'Что важнее для вас в проекте?', 7),
(8, 'Какой инструмент вам ближе?', 8),
(9, 'Как вы решаете сложные проблемы?', 9),
(10, 'Какой тип деятельности вас мотивирует?', 10);

-- --------------------------------------------------------

--
-- Структура таблицы `career_test_results`
--

CREATE TABLE `career_test_results` (
  `id` int NOT NULL,
  `user_id` int DEFAULT NULL,
  `result_direction` varchar(100) NOT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Дамп данных таблицы `career_test_results`
--

INSERT INTO `career_test_results` (`id`, `user_id`, `result_direction`, `created_at`) VALUES
(1, 6, 'Разработка ПО', '2025-10-17 14:19:24'),
(2, 6, 'Разработка ПО', '2025-10-17 14:23:07'),
(3, 6, 'UI/UX-дизайн', '2025-10-17 14:23:36'),
(4, 6, 'Разработка ПО', '2025-10-17 14:25:03');

-- --------------------------------------------------------

--
-- Структура таблицы `requests`
--

CREATE TABLE `requests` (
  `id` int NOT NULL,
  `user_id` int NOT NULL,
  `title` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `description` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci,
  `requirements` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci,
  `price` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `location` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `contacts` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `creator_name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Дамп данных таблицы `requests`
--

INSERT INTO `requests` (`id`, `user_id`, `title`, `description`, `requirements`, `price`, `location`, `contacts`, `creator_name`, `created_at`) VALUES
(1, 3, 'Вахта 300тысяч', NULL, NULL, NULL, NULL, NULL, NULL, '2025-10-03 19:35:19'),
(2, 9, 'ЗАЯВКА', 'ЗАЯВКА', 'ЗАЯВКА', '--', '--', '--', 'йцу йцу', '2025-10-13 20:15:54');

-- --------------------------------------------------------

--
-- Структура таблицы `services`
--

CREATE TABLE `services` (
  `id` int NOT NULL,
  `title` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `description` text COLLATE utf8mb4_general_ci,
  `price` varchar(100) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `location` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `contacts` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `creator_name` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Дамп данных таблицы `services`
--

INSERT INTO `services` (`id`, `title`, `created_at`, `description`, `price`, `location`, `contacts`, `creator_name`) VALUES
(1, 'IT', '2025-10-04 18:38:02', 'IT', '50000', 'удаленно', '9000', 'test_user_name test_user_surname'),
(2, 'Массаж', '2025-10-05 10:03:27', 'Массаж', '5000', 'Москва', '79220001122', 'test_user_name test_user_surname');

-- --------------------------------------------------------

--
-- Структура таблицы `users`
--

CREATE TABLE `users` (
  `id` int NOT NULL,
  `name` varchar(100) COLLATE utf8mb4_general_ci NOT NULL,
  `surname` varchar(100) COLLATE utf8mb4_general_ci NOT NULL,
  `patronymic` varchar(100) COLLATE utf8mb4_general_ci NOT NULL,
  `birth_date` date NOT NULL,
  `country` varchar(100) COLLATE utf8mb4_general_ci NOT NULL,
  `region` varchar(100) COLLATE utf8mb4_general_ci NOT NULL,
  `city` varchar(100) COLLATE utf8mb4_general_ci NOT NULL,
  `phone` varchar(20) COLLATE utf8mb4_general_ci NOT NULL,
  `email` varchar(150) COLLATE utf8mb4_general_ci NOT NULL,
  `password` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `about` text COLLATE utf8mb4_general_ci,
  `skills` text COLLATE utf8mb4_general_ci,
  `direction` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `role` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `experience_years` tinyint UNSIGNED DEFAULT NULL,
  `linkedin` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `telegram` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `github` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Дамп данных таблицы `users`
--

INSERT INTO `users` (`id`, `name`, `surname`, `patronymic`, `birth_date`, `country`, `region`, `city`, `phone`, `email`, `password`, `created_at`, `about`, `skills`, `direction`, `role`, `experience_years`, `linkedin`, `telegram`, `github`) VALUES
(2, 'admin', 'admin', 'admin', '0001-01-01', 'Россия', '--', '--', '+7 (000) 000-00-00', 'workinggroup@mail.ru', '$2y$10$gWouh7aaoJHiPnAUUIGrqO5WXXg6QnfUN0/84YWcZkG0PH3kbzwda', '2025-09-25 15:35:26', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(3, 'test_user_name', 'test_user_surname', 'test_user_patronymic', '1111-01-01', 'Австралия', '--', '--', '+7 (000) 000-00-00', 'test@test.ru', '$2y$10$KnGmxNpir9H8NmYs0LcvVO7iZ3F/z0W9j06jcRDMDiu6Y1S5csAZC', '2025-09-26 14:25:57', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(4, '123', '123', '123', '1111-01-01', 'Австралия', '123', '123', '+7 (123) 231-31-23', '123@123.ru', '$2y$10$b4Ugazb3BZJ8Ls.DsGtEJejt0WoGZHhl4rwj46C4oc6C2CNFiIB96', '2025-09-28 14:58:21', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(5, 'Арсений', 'Постовалов', 'Владимирович', '0001-08-31', 'Канада', 'Оттава', 'Оттава', '+7 (123) 321-11-22', 'arsenii@work.ru', '$2y$10$RAvbLYZN6Q0190fkxr9kdeJKLq6SsiyuUbDaAnPbiGuddnVSIk2F.', '2025-10-03 14:04:02', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(6, 'Степан', 'Кислин', 'Александрович', '2011-03-22', 'Россия', 'Челябинская область', 'Челябинск', '+7 (922) 728-00-01', 's_kislin@mail.ru', '$2y$10$AcRSFCpnbQudpPsKK6ZXPe0pPHlasxMLawEa0ppSk0MY7DfdmBZwK', '2025-10-05 05:02:46', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(7, 'Арсений', 'Постовалов', 'Владимирович', '2010-06-04', 'Азербайджан', 'Баку', 'Баку', '+7 (123) 131-31-35', 'wes@vk.ru', '$2y$10$FvFNZiNGqFthBEFtZ2g7pu70uGw.KvJnLne44cSzOm6qxByQNnTn2', '2025-10-08 14:39:45', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(8, 'qwqqe', 'qweqw', 'qweqe', '1111-01-11', 'Австралия', 'awe', 'qwe', '+7 (121) 301-11-43', 'qwe@qwe.com', '$2y$10$nrU1JRTSc3txYYZ87p019elAAG1U1EkstJ62Vh5ZIyIC.1Xz93U4u', '2025-10-08 15:06:50', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(9, 'йцу', 'йцу', 'йоу', '1111-12-11', 'США', '123', '123', '+7 (123)', 'qwe@mail.ru', '$2y$10$mgRPNSpWLNIZF1T.Yjp80.xbUv4FMAa.4SKEZWu7l68r/7amqNsAm', '2025-10-08 15:10:36', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(10, 'Working', 'Group', 'Team', '2025-10-14', 'Россия', 'Челябинская область', 'Челябинск', '+7 (922) 728-00-01', 'working@working.ru', '$2y$10$fBBvVq/LP.EqTGh6gIf4xuRNOim6gOotCVN.SkOZ/OuOcSRjxObAW', '2025-10-14 15:13:26', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL);

-- --------------------------------------------------------

--
-- Структура таблицы `vacancies`
--

CREATE TABLE `vacancies` (
  `id` int NOT NULL,
  `title` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `requirements` text COLLATE utf8mb4_general_ci,
  `salary` varchar(100) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `creator_name` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `user_id` int DEFAULT NULL,
  `description` text COLLATE utf8mb4_general_ci,
  `contacts` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `location` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Дамп данных таблицы `vacancies`
--

INSERT INTO `vacancies` (`id`, `title`, `created_at`, `requirements`, `salary`, `creator_name`, `user_id`, `description`, `contacts`, `location`) VALUES
(4, 'Go developer', '2025-10-03 19:47:41', 'опыт, уверенное знание GO, Python + фреймворки', '5800$', 'test_user_name test_user_surname', NULL, 'создание бекенда на go', '+7(900)800-90-91', 'Москва'),
(5, 'Fullstack-developer', '2025-10-03 19:55:49', 'знание вышеперечисленных языков + будет бонусом C++ или С#', '450 000 рублей (это стартовая, в дальнейшем может расти)', 'test_user_name test_user_surname', NULL, 'разработка адаптивных интерфейсов на HTML CSS TS jQuery React PHP Python JAVA/Kotlin/Swift (на выбор)', '+7(423) 555-55-05', 'удалённо'),
(6, 'awe', '2025-10-08 19:40:14', 'qrqrtq', '123', 'test_user_name test_user_surname', NULL, 'qrqrqqrq', 'tyne', 'nkngfkjng'),
(7, 'awe', '2025-10-08 20:05:21', 'qwe', 'qwe', 'test_user_name test_user_surname', NULL, 'qwe', 'qwe', 'qwe'),
(8, 'jvbkbv', '2025-10-08 20:05:50', 'skvbksvb', 'skvbksvb', 'test_user_name test_user_surname', NULL, 'sjnvksvb', 'slvjbskjvb', 'svnbskvb'),
(9, 'asd', '2025-10-08 20:06:59', 'asd', 'asd', 'test_user_name test_user_surname', NULL, 'asd', 'asd', 'asd'),
(10, 'awe', '2025-10-08 20:10:45', 'qwe', 'qwe', 'йцу йцу', NULL, 'qwe', 'qwe', 'qwe'),
(11, 'Backend developer', '2025-10-14 14:32:05', 'HTML, CSS (Bootstrap, Tailwind), JS(API), PHP(Laravel, Symphony), Python, Java, опыт 1-3 года', '800 000 рублей', 'йцу йцу', NULL, 'Писать backend для проектов', '--', 'Москва'),
(12, 'Frontend', '2025-10-14 14:51:26', 'HTML, CSS, JS, React, Git', '100 000 р', 'йцу йцу', NULL, 'Писать frontend для проекта', '--', 'удаленно'),
(13, 'Senior Аналитик данных', '2025-10-14 20:15:46', 'Python, R, C++ (будет плюсом), Git', '1 000 000 рублей', 'Working Group', NULL, 'Работа в WorkingGroup', '+7(922) 728 - 00 - 01', 'Москва сити');

--
-- Индексы сохранённых таблиц
--

--
-- Индексы таблицы `requests`
--
ALTER TABLE `requests`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Индексы таблицы `services`
--
ALTER TABLE `services`
  ADD PRIMARY KEY (`id`);

--
-- Индексы таблицы `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Индексы таблицы `vacancies`
--
ALTER TABLE `vacancies`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_vacancies_user` (`user_id`);

--
-- AUTO_INCREMENT для сохранённых таблиц
--

--
-- AUTO_INCREMENT для таблицы `requests`
--
ALTER TABLE `requests`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT для таблицы `services`
--
ALTER TABLE `services`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT для таблицы `users`
--
ALTER TABLE `users`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT для таблицы `vacancies`
--
ALTER TABLE `vacancies`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- Ограничения внешнего ключа сохраненных таблиц
--

--
-- Ограничения внешнего ключа таблицы `requests`
--
ALTER TABLE `requests`
  ADD CONSTRAINT `requests_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

--
-- Ограничения внешнего ключа таблицы `vacancies`
--
ALTER TABLE `vacancies`
  ADD CONSTRAINT `fk_vacancies_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
