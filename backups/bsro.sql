-- phpMyAdmin SQL Dump
-- version 5.2.3
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1:3306
-- Generation Time: Oct 31, 2026 at 06:15 AM
-- Server version: 8.4.7
-- PHP Version: 8.3.28

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `bsro`
--

-- --------------------------------------------------------

--
-- Table structure for table `admins`
--

DROP TABLE IF EXISTS `admins`;
CREATE TABLE IF NOT EXISTS `admins` (
  `id` int NOT NULL AUTO_INCREMENT,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `email` varchar(100) NOT NULL,
  `full_name` varchar(100) NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `role_id` int DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `profile_image` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`)
) ENGINE=InnoDB AUTO_INCREMENT=30 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `admins`
--

INSERT INTO `admins` (`id`, `username`, `password`, `email`, `full_name`, `created_at`, `role_id`, `is_active`, `profile_image`) VALUES
(3, 'newton', '$2y$10$W86cU1S4k9zxupOj4yrBXuqCp0b4ilbMnVLZj6qBKzwsTUYlNeOcy', 'scienceandtechnologylab24@gmail.com', 'SM Atiqur Rahman Limon', '2026-10-21 10:43:34', 1, 1, 'admin_059003b988f3d47f01a45f6844f48e0e.jpg'),
(8, 'shareqa', '$2y$10$szSh//0WNRXK8XeW0cTiPueGT826Ww1T70X4RbCNSPMeqSP/ME0Q.', 'shareqashqomi20@gmail.com', 'Shareqa Shaomi', '2026-10-28 05:22:33', 4, 1, 'admin_68e277beab56a3362664fc10eb0ab8e0.jpg');

-- --------------------------------------------------------

--
-- Table structure for table `admin_roles`
--

DROP TABLE IF EXISTS `admin_roles`;
CREATE TABLE IF NOT EXISTS `admin_roles` (
  `id` int NOT NULL AUTO_INCREMENT,
  `role_key` varchar(50) NOT NULL,
  `role_name` varchar(100) NOT NULL,
  `permissions_json` text NOT NULL,
  `is_super_admin` tinyint(1) NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `role_key` (`role_key`)
) ENGINE=InnoDB AUTO_INCREMENT=13 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `admin_roles`
--

INSERT INTO `admin_roles` (`id`, `role_key`, `role_name`, `permissions_json`, `is_super_admin`, `created_at`) VALUES
(1, 'super_admin', 'Super Admin', '[\"dashboard_view\",\"news_view\",\"news_add\",\"news_edit\",\"news_delete\",\"category_manage\",\"messages_manage\",\"subscribers_view\",\"settings_manage\",\"logs_view\",\"backup_manage\",\"users_manage\"]', 1, '2026-10-28 04:59:05'),
(2, 'administrator', 'Administrator', '[\"dashboard_view\",\"news_view\",\"news_add\",\"news_edit\",\"news_delete\",\"category_manage\",\"messages_manage\",\"subscribers_view\",\"settings_manage\",\"logs_view\",\"backup_manage\"]', 0, '2026-10-28 04:59:05'),
(3, 'news_manager', 'News Manager', '[\"dashboard_view\",\"news_view\",\"news_add\",\"news_edit\",\"news_delete\",\"category_manage\"]', 0, '2026-10-28 04:59:05'),
(4, 'news_editor', 'News Editor', '[\"dashboard_view\",\"news_view\",\"news_add\",\"news_edit\"]', 0, '2026-10-28 04:59:05'),
(5, 'moderator', 'Moderator', '[\"dashboard_view\",\"messages_manage\",\"subscribers_view\"]', 0, '2026-10-28 04:59:05'),
(6, 'log_viewer', 'Log Viewer', '[\"dashboard_view\",\"logs_view\"]', 0, '2026-10-28 04:59:05');

-- --------------------------------------------------------

--
-- Table structure for table `categories`
--

DROP TABLE IF EXISTS `categories`;
CREATE TABLE IF NOT EXISTS `categories` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `name_en` varchar(100) DEFAULT NULL,
  `slug` varchar(191) NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `slug` (`slug`)
) ENGINE=InnoDB AUTO_INCREMENT=16 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `categories`
--

INSERT INTO `categories` (`id`, `name`, `name_en`, `slug`, `created_at`) VALUES
(1, 'দর্শন', 'Philosophy', 'philosophy', '2026-10-17 13:36:55'),
(2, 'আন্তর্জাতিক', NULL, 'international', '2026-10-17 13:36:55'),
(3, 'রাজনীতি', NULL, 'politics', '2026-10-17 13:36:55'),
(4, 'বাণিজ্য', NULL, 'business', '2026-10-17 13:36:55'),
(5, 'প্রেম', 'Love', 'love', '2026-10-17 13:36:55'),
(6, 'বিনোদন', NULL, 'entertainment', '2026-10-17 13:36:55'),
(7, 'স্বাস্থ্য', NULL, 'health', '2026-10-17 13:36:55'),
(8, 'প্রযুক্তি', NULL, 'technology', '2026-10-17 13:36:55'),
(11, 'কবিতা', NULL, 'poem', '2026-10-28 04:49:11'),
(14, 'মহাকাশ', 'space', 'space', '2026-10-29 12:30:00');

-- --------------------------------------------------------

--
-- Table structure for table `contact_messages`
--

DROP TABLE IF EXISTS `contact_messages`;
CREATE TABLE IF NOT EXISTS `contact_messages` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `phone` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `subject` varchar(200) COLLATE utf8mb4_unicode_ci NOT NULL,
  `message` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `is_read` tinyint(1) DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=10 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `contact_messages`
--

INSERT INTO `contact_messages` (`id`, `name`, `email`, `phone`, `subject`, `message`, `created_at`, `is_read`) VALUES
(1, 'SM Atiqur Rahman Limon', 'scienceandtechnologylab24@gmail.com', '01321358921', 'আমার', 'hello how are you', '2026-10-21 11:50:19', 1),
(2, 'MD Atiqur Rahman limon', 'scienceandtechnologylab24@gmail.com', '01321358921', 'পরিচিতি', 'হাই আপনি কেমন আছেন? আশা করি ভালো আছেন। আমি আপনার প্রতিষ্ঠানে যুক্ত হতে উচ্ছক।', '2026-10-21 12:26:31', 1),
(4, 'Newton', 'smatiqurrahmanlimon@gmail.com', '01321358921', 'প্রেম পত্র', 'আশা করি ভালো আছো। তুমি জানো যে আমি তোমাকে আনেক ভালোবাসি এবং সেটি তোমাকে অনেক বার বলছি কিন্ত তুমি আমাকে প্রতিবার ফিরিয়ে দিয়েছো।', '2026-10-21 14:01:02', 1),
(5, 'Tamanna', 'tamannaaktar2005@gmail.com', '01818768777', 'Love letter', 'Dear SM Atiqur Rahman\r\nFrom my love, I wish you a bunch of Rose. Hope you are very well. This is the first time in my life, I am writing my thoughts to you. I don`t know how to start. I fell in love with you from the day I first saw you on the college campus. I don`t know how I love you so much. I never thought I would love someone so much. And I never imagined that person would be you. Always that magical face of yours floats in front of me. I want to keep you with all the love I have in my heart. I promise, my love is pure and pure. Where there is not even the slightest splash of corruption. You can trust me with love. I will try my best to keep you happy. You have a lot to say. Most importantly, I love you more. With much hope, I wrote the words of my heart to you. You don`t let me down I await your positive reply…………….\r\n\r\nyour\r\nTamanna Aktar\r\n\r\n', '2026-10-23 05:20:05', 1),
(6, 'Tamanna Aktar', 'tamannaaktar2005@gmail.com', '01818768777', 'My Leve letter', 'My Dearest SM Atiqur Rahman,\r\nThere are moments when words fail to express the depth of what my heart feels for you, but I’ll try my best to share a piece of it with you through this letter.\r\nFrom the moment you entered my life, everything changed. It’s as if the world became brighter, more vibrant, and full of meaning. The sound of your laughter, the warmth of your presence, the way you look at me—it all fills me with a joy I never thought possible. You have a way of making even the most ordinary moments extraordinary, simply by being there.\r\nI find myself thinking of you in everything I do. Your voice echoes in my mind, your smile lingers in my heart, and your love surrounds me like a comforting embrace. I never knew that love could feel this way—endless, all-encompassing, and beautiful. With you, I am home.\r\nThere is no one else who could ever compare to you. Your kindness, your strength, your warmth—everything about you draws me closer, and I find myself falling more in love with you every day. You are my heart’s desire, my soul’s companion, and I cannot imagine my life without you.\r\nAs I write this, I want you to know that I am forever grateful for you. You are the greatest gift I have ever received, and I promise to cherish and love you with all that I am, for all the days of my life.\r\nI love you more than words could ever say, and I will continue to show you that love in everything I do.\r\nYours ,now and always,\r\nTamanna Aktar\r\n\r\n', '2026-10-23 05:35:39', 1),
(9, 'Atiqur Rahman Limon', 'smatiqurrahmanlimon@gmail.com', '+8801321358921', 'Proposal', 'Hi Newton! I love you.', '2026-10-30 02:07:42', 1);

-- --------------------------------------------------------

--
-- Table structure for table `message_replies`
--

DROP TABLE IF EXISTS `message_replies`;
CREATE TABLE IF NOT EXISTS `message_replies` (
  `id` int NOT NULL AUTO_INCREMENT,
  `message_id` int NOT NULL,
  `admin_id` int NOT NULL,
  `subject` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `message` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `message_id` (`message_id`),
  KEY `admin_id` (`admin_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `news`
--

DROP TABLE IF EXISTS `news`;
CREATE TABLE IF NOT EXISTS `news` (
  `id` int NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL,
  `title_en` varchar(255) DEFAULT NULL,
  `slug` varchar(191) NOT NULL,
  `content` text NOT NULL,
  `content_en` mediumtext,
  `excerpt` varchar(500) DEFAULT NULL,
  `excerpt_en` varchar(500) DEFAULT NULL,
  `category_id` int DEFAULT NULL,
  `featured_image` varchar(255) DEFAULT NULL,
  `author` varchar(100) DEFAULT 'bsro',
  `views` int DEFAULT '0',
  `is_featured` tinyint(1) DEFAULT '0',
  `status` enum('published','draft') DEFAULT 'published',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `slug` (`slug`),
  KEY `category_id` (`category_id`)
) ENGINE=InnoDB AUTO_INCREMENT=16 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `news`
--

INSERT INTO `news` (`id`, `title`, `title_en`, `slug`, `content`, `content_en`, `excerpt`, `excerpt_en`, `category_id`, `featured_image`, `author`, `views`, `is_featured`, `status`, `created_at`, `updated_at`) VALUES
(3, '১০০ কোটির ক্লাবে তামান্নার ‘আজ কি রাত’', 'Tamannaah’s ‘Aaj Ki Raat’ enters the 100-crore club', '------', 'বলিউডের আইটেম সং মানেই যেন এখন তামান্না ভাটিয়া। পরপর বেশ কিছু গানে নিজের যোগ্যতা প্রমাণ করে এই দক্ষিণী নায়িকা এখন বলিউডের সেনসেশন। তামান্নার আইটেম গানের মধ্যে অন্যতম রাজকুমার রাও ও শ্রদ্ধা কাপুর অভিনীত ‘স্ত্রী ২’ ছবির ‘আজ কি রাত’। সম্প্রতি ‘আজ কি রাত’ ইউটিউবে এক বিলিয়ন (১০০ কোটি) ভিউ ছাড়িয়েছে। এ সাফল্যে ভক্তদের প্রতি কৃতজ্ঞতা জানিয়ে আনন্দ প্রকাশ করেছেন তামান্না ভাটিয়া। গানটি ছবিতে তারই নাচের মাধ্যমে আলাদা মাত্রা পেয়েছে। এ উপলক্ষে তামান্না নিজের ইনস্টাগ্রামে শুটিংয়ের কিছু অদেখা ভিডিও শেয়ার করেছেন। একটি ভিডিওতে দেখা যায়, কোরিওগ্রাফার বিজয় গাঙ্গুলী ও টিমের সঙ্গে মনিটরে নিজের নাচ দেখছেন তামান্না। সেখানে বিজয় গাঙ্গুলী শটটির প্রশংসা করলে মজা করে তামান্না বলেন, ‘না!’ আরেকটি ক্লিপে তামান্নার সঙ্গে নাচতে দেখা যায় পঙ্কজ ত্রিপাঠী, রাজকুমার রাও, অভিষেক বন্দ্যোপাধ্যায় ও অপরশক্তি খুরানাকে। পোস্টের ক্যাপশনে তামান্না লেখেন, ‘প্রথম ভিউ থেকে এক বিলিয়ন ভিউ—সব ভালোবাসার জন্য ধন্যবাদ।’ গানটি গেয়েছেন মাধুবন্তী বাগচী ও দিব্য কুমার। কথা লিখেছেন অমিতাভ ভট্টাচার্য, সুর করেছেন জনপ্রিয় জুটি শচীন-জিগর। মুক্তির পরপরই ‘আজ কি রাত’ দর্শকদের মধ্যে তুমুল সাড়া ফেলে এবং তামান্নার নাচ গানটিকে আরও জনপ্রিয় করে তোলে।', 'In Bollywood, the term \"item song\" has become almost synonymous with Tamannaah Bhatia. Having proven her mettle in a string of such songs, this actress from the South has now become a Bollywood sensation. One of her notable item numbers is \"Aaj Ki Raat\" from the film *Stree 2*, which stars Rajkummar Rao and Shraddha Kapoor. Recently, the song \"Aaj Ki Raat\" surpassed one billion views on YouTube. Expressing her joy, Tamannaah Bhatia thanked her fans for this achievement; her dance performance in the film added a special dimension to the track. To mark the occasion, Tamannaah shared some previously unseen behind-the-scenes footage on her Instagram. In one video, she is seen watching her performance on a monitor alongside choreographer Vijay Ganguly and his team. When Vijay Ganguly praises the shot, Tamannaah playfully replies, \"No!\" Another clip shows Pankaj Tripathi, Rajkummar Rao, Abhishek Banerjee, and Aparshakti Khurana dancing alongside her. In the caption, Tamannaah wrote, \"From the very first view to one billion views—thank you for all the love.\" The song is sung by Madhubanti Bagchi and Divya Kumar, with lyrics penned by Amitabh Bhattacharya and music composed by the popular duo Sachin-Jigar. Upon its release, \"Aaj Ki Raat\" received an overwhelming response from the audience, and Tamannaah’s dance further boosted the song\'s popularity.', 'বলিউডের আইটেম সং মানেই যেন এখন তামান্না ভাটিয়া। পরপর বেশ কিছু গানে নিজের যোগ্যতা প্রমাণ করে এই দক্ষিণী নায়িকা এখন বলিউডের সেনসেশন। তামান্নার আইটেম গানের মধ্যে অন্যতম রাজকুমার রাও ও শ্রদ্', '', 5, 'news_e7499e79453c7597.jpg', 'বিজ্ঞান জগৎ', 14, 0, 'published', '2026-10-29 12:45:22', '2026-10-30 09:50:45'),
(4, 'তামান্না ভাটিয়াকে রাখি সাওয়ান্তের কটাক্ষ', 'Rakhi Sawant takes a dig at Tamannaah Bhatia', '----', 'দক্ষিণের জনপ্রিয় অভিনেত্রী তামান্না ভাটিয়ার নাচে আবারও মেতেছে দর্শক। ‘দ্য ব্যাডস অফ বলিউড’ সিনেমার ‘গফুর’ গানটির সঙ্গে তামান্নার নাচ এখন সোশ্যাল মিডিয়ায় ভাইরাল। অনেকেরই দাবি, এবার যেন ‘আইটেম ডান্স কুইন’র মুকুটটা তার মাথায়ই বসতে যাচ্ছে। কিন্তু তামান্নার নাচ নিয়ে কটাক্ষ করলেন একসময়ের ‘আইটেম গার্ল’ রাখি সাওয়ান্ত। টাইমস অব ইন্ডিয়ার প্রতিবেদন অনুযায়ী, বলিউডের বিতর্কিত এই অভিনেত্রীর অভিযোগ, এখনকার নায়িকারাই ‘আইটেম’ গানে নাচছেন, ফলে তাদের কারণে আসল আইটেম ডান্সের আবেদন হারিয়ে যাচ্ছে। রাখির ভাষায়, ‘আমাদের সময়ে আইটেম গানের মধ্যে ছিল এক অন্যরকম মাদকতা। এখনকার নাচে সেই আবেগ, সেই জাদু নেই। এরা (তামান্না ও অন্যান্য নায়িকা) আমাদের দেখে দেখে নাচ শিখেছে। নায়িকা হওয়ার সময় পেরিয়ে গেলে এখন এরা আমাদের পেটে লাথি মেরে আইটেম নাচ করছে। ওদের লজ্জা হওয়া উচিত!’ তিনি আরও বলেন, ‘আসল আইটেম নাচ তো আমিই নেচেছিলাম। এবার আমি নায়িকা হব।’ তামান্না ভাটিয়া এর আগে ‘আজ কি রাত’ এবং ‘কাবালা’ গানে নেচে ব্যাপক প্রশংসা কুড়িয়েছিলেন। তার সর্বশেষ ‘গফুর’ গানটি বলিউডজুড়ে নতুন উন্মাদনা ছড়িয়েছে। তবে এই নাচের মাধ্যমেই বিতর্কের মুখে পড়লেন অভিনেত্রী। রাখির অভিযোগের জবাবে তামান্না এখন পর্যন্ত কোনো মন্তব্য করেননি। তবে ভক্তরা বলছেন, ‘আইটেম ডান্স’-এর সংজ্ঞা বদলে দিয়েছেন তামান্না— তিনি গ্ল্যামারের পাশাপাশি আত্মবিশ্বাস, সাহস আর নিজের প্রতি ভালোবাসার প্রতীক হয়ে উঠেছেন। এক অনুষ্ঠানে তাকে প্রশংসা করে এক নারী বলেছিলেন, ‘তোমার নাচে শক্তি আছে, আত্মবিশ্বাস আছে— এই জন্যই তুমি অনন্য।’ যদিও তামান্না নিজেকে কখনো ‘পৃথুল’ বলে ভাবেননি। বরং তার মতে, ‘আমি যেমন আছি, তাতেই আমি সুন্দর।’ বলিউডে এখন প্রশ্ন একটাই— ‘আইটেম কুইন’ উপাধি রাখির থাকবে, নাকি ছিনিয়ে নেবেন তামান্না?', 'Popular South Indian actress Tamannaah Bhatia has once again captivated audiences with her dance. Her performance to the song \"Gafur\" from the film *The Bads of Bollywood* has gone viral on social media. Many are suggesting that she might well be on the verge of claiming the crown of the \"Item Dance Queen.\" However, Rakhi Sawant—herself a former \"item girl\"—has taken a dig at Tamannaah’s dance. According to a report in The Times of India, this controversial Bollywood actress alleges that contemporary leading ladies are now performing ‘item’ numbers, which is causing the original appeal of such dances to fade away. In Rakhi’s words, “In our time, item numbers possessed a unique, intoxicating allure. Today’s performances lack that same passion and magic. They (Tamannaah and other leading ladies) learned to dance by watching us. Now that their time as leading ladies has passed, they are effectively taking the bread out of our mouths by performing these item numbers. They should be ashamed!” She further added, “I was the one who truly mastered the art of the item dance. Now, it’s my turn to be the leading lady.” Tamannaah Bhatia previously garnered immense praise for her performances in the songs \"Aaj Ki Raat\" and \"Kaavaala.\" Her latest track, \"Gafur,\" has sparked a new wave of excitement across Bollywood. However, this very performance has also landed the actress in the midst of a controversy. Tamannaah has yet to respond to Rakhi’s allegations. Meanwhile, fans argue that Tamannaah has redefined the \"item dance\"—beyond mere glamour, she has become a symbol of self-confidence, courage, and self-love. At one event, a woman praised her, saying, \"There is power and self-confidence in your dancing—that is what makes you unique.\" Tamannaah, for her part, has never viewed herself as \"full-figured\" in a negative sense; rather, she maintains, \"I am beautiful just the way I am.\" Now, the question echoing throughout Bollywood is: will Rakhi retain her title as the \"Item Queen,\" or will Tamannaah snatch it away?', 'দক্ষিণের জনপ্রিয় অভিনেত্রী তামান্না ভাটিয়ার নাচে আবারও মেতেছে দর্শক। ‘দ্য ব্যাডস অফ বলিউড’ সিনেমার ‘গফুর’ গানটির সঙ্গে তামান্নার নাচ এখন সোশ্যাল মিডিয়ায় ভাইরাল। অনেকেরই দাবি, এবার যেন ‘', '', 6, 'news_af6c50024f066e0f.jpg', 'বিজ্ঞান জগৎ', 11, 1, 'published', '2026-10-29 13:27:02', '2026-10-30 05:08:59'),
(6, '‘বয়স বাড়া একটি সুন্দর ব্যাপার’', '‘Growing older is a beautiful thing’', '-----1793365253', 'বলিউড ও দক্ষিণের সিনেমার জনপ্রিয় অভিনেত্রী তামান্না ভাটিয়া। ৩৫ বছর বয়সী এই অগ্নিনেত্রী শুধু অভিনয় নয়, একের পর এক আইটেম গানে কড় তুলেছেন। অসাধারণ পারডরম্যান্সে মুগ্ধ করেছেন দর্শকদের। সম্প্রতি এক সাক্ষাৎকারে তামান্না ৩০ বছর বয়সী নারীদের প্রতি চলচ্চিত্র শিল্পের ইতিবাচক পরিবর্তন নিয়ে আলোচনা করেছেন। চলচ্চিত্রশিল্পে জটিল চরিত্রের জন্য ৩০ই ঘরে নারীদের চাহিদা বাড়ছে। এ প্রসঙ্গে তামান্না ভাটিয়া বলেন, \'বলিউড এখন আরও বেশি করে ৩০\'র ঘরে নারীদের গ্রহণ করছে। বয়স বাড়া কোনো তয় নয়, বরং একটি অসাধারণ ব্যাপার। আগে এই বয়সের পর খুব একটা নারীদের দেখা যেত না পর্দায়। যখন আমি প্রথম অভিনয়ে আসি তখন আমার ১০ বছরের একটা পরিকল্পনা ছিল। ভেবেছিলাম ত্রিশ বছর পর্যন্ত কাজ করব। তারপর বিয়ে করে সংসার ক্যাব। কিন্তু একটা ভালো দিক হলো-বিশ বছরের শেষে এসে জামি নিজেকে নতুনভাবে চিনতে পেরেছি। তখনই সৌভাগ্যবশত ইন্ডাস্ট্রিও নারীদের জন্য আকর্যণীয় চরিত্র লিখতে শুরু করেছিল। এটি আমলে সারাবিশ্বের ইতিবাচক পরিবর্তন। কিছু মানুষ এমনভাবে বলে যেন বয়স বাড়া কোনো রোগ। অথচ বয়স বাড়া একটি সুন্দর ব্যাপার।\' তামান্নাকে পরবর্তী সময়ে দেখা যাবে- শাহিদ কাপুরের সঙ্গে ত্রিশাল চার্মওয়াজের আঅ্যাকশন ছিলার সিনেমা \'ও রোমিও\'-তে। ২০২৫ সালের শেষের দিকে বা ২০২৯ সলের শুরুতে দিনেমাটি মুক্তি পাওয়ার সম্ভাবনা রয়েছে।', 'Tamannaah Bhatia is a popular actress in both Bollywood and South Indian cinema. The 35-year-old star has made her mark not only through her acting but also by appearing in a series of \'item songs,\' captivating audiences with her exceptional performances. In a recent interview, Tamannaah discussed the positive shifts within the film industry regarding women in their thirties. There is a growing demand for women in this age group to portray complex characters on screen. In this context, Tamannaah Bhatia says, \"Bollywood is now increasingly embracing women in their thirties. Aging is not a drawback; rather, it is something wonderful. In the past, women were rarely seen on screen once they reached this age. When I first started acting, I had a ten-year plan—I thought I would work until I was thirty, then get married and settle down. But, fortunately, as I approached the end of my twenties, I discovered a new side of myself. At the same time, the industry also began creating compelling roles for women. This reflects a positive shift happening globally. Some people speak as if aging were a disease, but in reality, it is a beautiful process.\" Tamannaah will next be seen alongside Shahid Kapoor in the action-thriller film \'Oh Romeo\'. The film is expected to be released towards the end of 2025 or the beginning of 2026.', 'বলিউড ও দক্ষিণের সিনেমার জনপ্রিয় অভিনেত্রী তামান্না ভাটিয়া। ৩৫ বছর বয়সী এই অগ্নিনেত্রী শুধু অভিনয় নয়, একের পর এক আইটেম গানে কড় তুলেছেন। অসাধারণ পারডরম্যান্সে মুগ্ধ করেছেন দর্শকদের। সম্প্', '', 6, 'news_39622b2dda9f2928.jpg', 'বিজ্ঞান জগৎ', 2, 0, 'published', '2026-10-29 13:52:57', '2026-10-30 13:00:53'),
(7, 'কৃত্রিম বুদ্ধিমত্তা কি মানুষের চেতনার বিকল্প হতে পারে? দর্শনের আলোয় নতুন বিতর্ক', 'Can artificial intelligence replace human consciousness? A new debate in the light of philosophy', '-----------', 'একবিংশ শতাব্দীর এই প্রযুক্তিনির্ভর যুগে মানুষের জীবনযাত্রার পাশাপাশি বদলে গেছে চিন্তার জগৎও। কৃত্রিম বুদ্ধিমত্তা বা আর্টিফিশিয়াল ইন্টেলিজেন্স (AI) আজ শুধু গাণিতিক হিসাব বা কোডিংয়ের মধ্যেই সীমাবদ্ধ নয়; এটি শিল্প, সাহিত্য থেকে শুরু করে মানুষের বহু জটিল সিদ্ধান্ত নেওয়ার ক্ষমতাও আয়ত্ত করে নিচ্ছে। তবে প্রযুক্তির এই অভাবনীয় অগ্রগতির মুখে বিজ্ঞান ও দর্শনের গবেষকদের মনে নতুন করে উঁকি দিচ্ছে একটি মৌলিক প্রশ্ন—যন্ত্র কি আসলেই মানুষের মতো চিন্তা করতে সক্ষম? নাকি এটি কেবল পূর্বনির্ধারিত কোড আর প্যাটার্নের এক নিখুঁত অনুকরণ মাত্র?\r\n\r\nউপাত্ত বনাম প্রকৃত জ্ঞান: তাত্ত্বিক দ্বন্দ্ব\r\nপ্রাচীন গ্রিক দার্শনিক সক্রেটিস থেকে শুরু করে রেনে দেকার্ত বা জন লক—প্রত্যেকেই জ্ঞান ও চেতনার উৎস অনুসন্ধানে মানব মনকে সর্বাগ্রে স্থান দিয়েছেন। আধুনিক জ্ঞানতত্ত্বের (Epistemology) সংজ্ঞা অনুযায়ী, প্রকৃত জ্ঞান অর্জনের জন্য কেবল তথ্যের সমাহার যথেষ্ট নয়; এর পেছনে থাকতে হয় গভীর বিশ্বাস, উপলব্ধি এবং যৌক্তিক সত্যতা।\r\n\r\nবিশেষজ্ঞদের মতে, কৃত্রিম বুদ্ধিমত্তা কোটি কোটি ডেটা বা উপাত্ত সেকেন্ডের মধ্যে বিশ্লেষণ করতে পারলেও তার নিজস্ব কোনো \'বিশ্বাস\' বা \'চেতনাবোধ\' নেই। প্রখ্যাত দার্শনিক জন সার্লের বিখ্যাত \'চাইনিজ রুম\' (Chinese Room Argument) তত্ত্বর কথা উল্লেখ করে গবেষকরা বলছেন, একটি কম্পিউটার প্রোগ্রাম কেবল নিয়ম মেনে কাজ সম্পন্ন করে, কিন্তু সে যা করছে তার প্রকৃত অর্থ বা তাৎপর্য সে নিজে বুঝতে পারে না।\r\n\r\nচেতনার \'কঠিন সমস্যা\'\r\nমনদর্শনের (Philosophy of Mind) প্রখ্যাত তাত্ত্বিক ডেভিড চালমার্স চেতনার বিষয়টিকে দুটি ভাগে ভাগ করেছেন—একটি হলো তথ্য প্রক্রিয়াকরণ বা সহজ সমস্যা, অন্যটি হলো ব্যক্তিগত অনুভূতি বা অনুভবের (Qualia) কঠিন সমস্যা।\r\n\r\nবিজ্ঞান আজ কৃত্রিম বুদ্ধিমত্তাকে দিয়ে তথ্য প্রক্রিয়াকরণের কাজটি সফলভাবে করিয়ে নিতে পারলেও, কোনো কিছুর ভেতরকার অনুভূতি—যেমন দুঃখ, আনন্দ বা রঙের তীব্রতা উপলব্ধির ক্ষমতা যন্ত্রের মধ্যে তৈরি করতে পারেনি। ফলে যন্ত্র যতই বুদ্ধিমান হোক না কেন, মানুষের অনুভূতির সাথে তার মৌলিক ফারফ থেকেই যাচ্ছে।\r\n\r\nনৈতিকতা এবং ভবিষ্যতের চ্যালেঞ্জ\r\nকৃত্রিম বুদ্ধিমত্তা যখন মানুষের দৈনন্দিন জীবনের সিদ্ধান্তগুলোতে প্রভাব ফেলতে শুরু করেছে, তখন নীতিবিদ্যার (Ethics) প্রশ্নটিও বড় হয়ে দেখা দিয়েছে। দেকার্তের বিখ্যাত উক্তি—“আমি চিন্তা করি, তাই আমি আছি” (Cogito, ergo sum)। আত্ম-সচেতনতাই যদি মানুষের অস্তিত্বের মূল ভিত্তি হয়, তবে আত্ম-সচেতনতাহীন কোনো প্রযুক্তিকে কি নৈতিক সত্তা হিসেবে বিবেচনা করা যায়? যদি কোনো এআই সিস্টেম ভুল সিদ্ধান্ত নেয়, তবে তার দায়ভার কার ওপর বর্তাবে—প্রোগ্রামার, নাকি যন্ত্রের?\r\n\r\nদার্শনিকদের মতে, কৃত্রিম বুদ্ধিমত্তা মানব সভ্যতার প্রগতির এক দুর্দান্ত হাতিয়ার হতে পারে, কিন্তু এটি মানুষের চেতনা এবং চিন্তাশীলতার কোনো চূড়ান্ত বিকল্প নয়। প্রযুক্তির এই দ্রুতগতির যুগে মানবতা, নৈতিকতা এবং অস্তিত্বের ভারসাম্য বজায় রাখাই এখন আধুনিক দর্শনের সামনে সবচেয়ে বড় চ্যালেঞ্জ।', 'In this technology-driven era of the twenty-first century, not only has the human way of life transformed, but the realm of human thought has also undergone a shift. Artificial Intelligence (AI) is no longer confined to mathematical calculations or coding; it is now extending into fields like art and literature, and even demonstrating the capacity to assist in complex human decision-making. Yet, in the face of such extraordinary technological advancement, a fundamental question has resurfaced among researchers in science and philosophy: are machines truly capable of thinking like humans, or are they merely executing a sophisticated imitation based on pre-programmed code and patterns?\r\n\r\nData versus Genuine Knowledge: A Theoretical Dilemma\r\nFrom the ancient Greek philosopher Socrates to thinkers like René Descartes and John Locke, the human mind has always been central to inquiries into the origins of knowledge and consciousness. According to modern epistemology, the mere accumulation of information is insufficient for the attainment of genuine knowledge; it requires deep-seated belief, comprehension, and logical validity.\r\n\r\nExperts argue that while AI can analyze billions of data points in a fraction of a second, it lacks any inherent \'belief\' or \'consciousness.\' Citing the renowned philosopher John Searle’s \'Chinese Room\' argument, researchers point out that a computer program operates strictly by following rules—it performs tasks without grasping the actual meaning or significance of its own actions.\r\n\r\nThe \'Hard Problem\' of Consciousness\r\nDavid Chalmers, a prominent theorist in the philosophy of mind, distinguishes between two aspects of consciousness: the \'easy problem\'—which concerns information processing—and the \'hard problem\'—which relates to subjective experience, or \'qualia.\'\r\n\r\nWhile science has successfully enabled AI to handle information processing, it has yet to endow machines with the capacity for subjective experience—the ability to truly \'feel\' things, such as the depth of sorrow, the intensity of joy, or the vividness of a color. Consequently, no matter how intelligent a machine may become, a fundamental distinction between it and human emotion will always remain.\r\n\r\nEthics and Future Challenges\r\nAs artificial intelligence begins to influence the decisions made in our daily lives, the question of ethics has come to the forefront. Consider Descartes’ famous dictum: \"I think, therefore I am\" (*Cogito, ergo sum*). If self-awareness is the cornerstone of human existence, can a technology devoid of self-awareness be considered a moral entity? If an AI system makes an erroneous decision, with whom does the responsibility lie—the programmer, or the machine itself?\r\n\r\nAccording to philosophers, while artificial intelligence can serve as a powerful tool for the advancement of human civilization, it is by no means a definitive substitute for human consciousness and capacity for thought. In this era of rapid technological evolution, maintaining a balance between humanity, ethics, and existence remains the greatest challenge for modern philosophy.', 'একবিংশ শতাব্দীর এই প্রযুক্তিনির্ভর যুগে মানুষের জীবনযাত্রার পাশাপাশি বদলে গেছে চিন্তার জগৎও। কৃত্রিম বুদ্ধিমত্তা বা আর্টিফিশিয়াল ইন্টেলিজেন্স (AI) আজ শুধু গাণিতিক হিসাব বা কোডিংয়ের মধ্', '', 1, 'news_88e221d7fa980f86.jpg', 'বিজ্ঞান জগৎ', 5, 1, 'published', '2026-10-30 10:07:05', '2026-10-31 02:28:27'),
(8, 'সক্রেটিস এবং তাঁর অনুসন্ধান: প্রজ্ঞা, সত্য ও \'সক্রেটিক পদ্ধতি\'র প্রাসঙ্গিকতা', 'Socrates and His Inquiries: Wisdom, Truth, and the Relevance of the \'Socratic Method\'', '---------', 'পাশ্চাত্য দর্শনের ভিত্তিপ্রস্তর স্থাপনকারীদের মধ্যে সক্রেটিস (Socrates) এক চিরন্তন ও রহস্যময় নাম। প্রাচীন এথেন্সের রাজপথে ঘুরে যিনি মানুষকে সত্য ও জ্ঞানের অন্বেষণে উদ্বুদ্ধ করেছিলেন, তাঁর চিন্তাধারা আজও আধুনিক সভ্যতার তাত্ত্বিক ও বুদ্ধিবৃত্তিক চর্চাকে প্রভাবিত করে চলেছে। কোনো লিখিত গ্রন্থ রেখে না গেলেও প্লেটোর ডায়লগ বা সংলাপের মাধ্যমে বেঁচে থাকা সক্রেটিসের দর্শন আজকের এই কৃত্রিম বুদ্ধিমত্তা ও তথ্যের যুগেও সমানভাবে প্রাসঙ্গিক।\r\n\r\nসক্রেটিসের মূল দর্শন: \"আমি জানি যে আমি কিছুই জানি না\"\r\nসক্রেটিসের দর্শনের মূল ভিত্তি ছিল আত্মোপলব্ধি এবং নিজের অজ্ঞতা সম্পর্কে সচেতন হওয়া। ডেলফির ওরাকল যখন তাঁকে বিশ্বের সবচেয়ে বুদ্ধিমান ব্যক্তি বলে ঘোষণা করে, তখন তিনি নিজেই অবাক হয়ে যান। পরবর্তীতে তিনি বুঝতে পারেন যে, অন্যরা না জেনেই নিজেদের জ্ঞানী মনে করে, আর তিনি অন্তত এতটুকু জানেন যে তিনি কিছুই জানেন না। এই নিরহংকার সত্যের অন্বেষণই তাঁর সমগ্র দর্শনের চালিকাশক্তি ছিল।\r\n\r\n\'সক্রেটিক পদ্ধতি\' বা প্রশ্ন করার শিল্প\r\nসক্রেটিসের সবচেয়ে বড় অবদান হলো তাঁর উদ্ভাবিত \'এলেনকোস\' (Elenchus) বা সক্রেটিক পদ্ধতি। সরাসরি কোনো তথ্য বা উত্তর না দিয়ে তিনি প্রশ্নোত্তরের মাধ্যমে মানুষের ভেতরের লুকানো সত্যকে বের করে আনতেন।\r\n\r\nশিক্ষণপদ্ধতি: এটি এমন এক দ্বান্দ্বিক প্রক্রিয়া যেখানে শিক্ষক বা তাত্ত্বিক সরাসরি বক্তব্য না রেখে অনুবন্ধী বা ধারাবাহিক প্রশ্ন করেন।\r\n\r\nলক্ষ্য: মানুষের মনে আগে থেকে জমে থাকা অন্ধবিশ্বাস, কুসংস্কার এবং ভাসাভাসা ধারণাকে ভেঙে ফেলে যৌক্তিক ও সুনির্দিষ্ট সত্যে পৌঁছানো।\r\n\r\nআধুনিক যুগে সক্রেটিসের প্রাসঙ্গিকতা\r\nআজকের একবিংশ শতাব্দীতে যখন ইন্টারনেট এবং কৃত্রিম বুদ্ধিমত্তার কল্যাণে তথ্যের ছড়াছড়ি, তখন মানুষ খুব সহজেই যেকোনো তথ্য পেয়ে যায়। কিন্তু তথ্য আর প্রকৃত প্রজ্ঞা (Wisdom)-র মধ্যে আকাশ-পাতাল তফাৎ। সক্রেটিসের পদ্ধতি আমাদের শেখায় কীভাবে অন্ধভাবে তথ্য বা অ্যালগরিদমকে বিশ্বাস না করে সেগুলোকে প্রশ্ন করতে হয়। সমালোচনামূলক চিন্তা (Critical Thinking) বিকাশে সক্রেটিসের ডায়লগ পদ্ধতি আজও আধুনিক শিক্ষা ব্যবস্থার মূল ভিত্তি।\r\n\r\nউপসংহার\r\nসক্রেটিস কেবল একজন দার্শনিক ছিলেন না, তিনি ছিলেন সত্যের এক নির্ভীক সাধক। এথেন্সের শাসকরা তাঁর চিন্তাকে ভয় পেয়ে তাঁকে হেমলক বিষ পানে মৃত্যু দণ্ড দিলেও তাঁর দর্শন অমর হয়ে রয়েছে। যুগে যুগে মানুষের ভেতরের মুক্তবুদ্ধি এবং প্রশ্ন করার স্পৃহাকে জাগিয়ে রাখতে সক্রেটিসের অবদান চিরস্মরণীয় হয়ে থাকবে।', 'Socrates stands as an enduring and enigmatic figure among the foundational architects of Western philosophy. By wandering the streets of ancient Athens and inspiring others to pursue truth and knowledge, he established a line of thought that continues to influence the theoretical and intellectual pursuits of modern civilization. Although he left no written works, his philosophy—preserved through the dialogues of Plato—remains profoundly relevant even in today’s era of artificial intelligence and information.\r\n\r\nThe Core of Socratic Philosophy: \"I know that I know nothing\"\r\nThe cornerstone of Socratic philosophy is self-realization and an awareness of one\'s own ignorance. When the Oracle of Delphi declared him the wisest man in the world, Socrates himself was taken aback. He eventually realized that while others believed themselves to be wise despite their lack of true knowledge, he at least possessed the awareness that he knew nothing. This humble pursuit of truth served as the driving force behind his entire philosophy.\r\n\r\nThe \'Socratic Method\' – The Art of Questioning\r\nPerhaps Socrates\' greatest contribution is the \'Elenchus,\' or the Socratic method. Rather than providing direct answers or information, he would draw out the truth hidden within an individual through a process of questioning.\r\n\r\nPedagogical Approach: This is a dialectical process in which the teacher or thinker avoids making direct assertions, opting instead to pose a series of interconnected, sequential questions.\r\n\r\nObjective: To dismantle the blind beliefs, superstitions, and superficial notions held by individuals, thereby arriving at a logical and precise truth.\r\n\r\nThe Relevance of Socrates in the Modern Age\r\nIn the twenty-first century, an era defined by an abundance of information—facilitated by the internet and artificial intelligence—accessing data has become effortless. Yet, there remains a vast, fundamental chasm between mere information and true wisdom. The Socratic method teaches us how to question information or algorithms rather than accepting them blindly. The Socratic dialogue—a method for fostering critical thinking—remains a cornerstone of modern educational systems to this day.\r\n\r\nConclusion\r\nSocrates was more than just a philosopher; he was a fearless seeker of truth. Although the rulers of Athens, fearing his ideas, sentenced him to death by forcing him to drink hemlock, his philosophy has endured. Through the ages, Socrates’ contribution in awakening the free intellect and the spirit of inquiry within human beings will remain forever memorable.', 'পাশ্চাত্য দর্শনের ভিত্তিপ্রস্তর স্থাপনকারীদের মধ্যে সক্রেটিস (Socrates) এক চিরন্তন ও রহস্যময় নাম। প্রাচীন এথেন্সের রাজপথে ঘুরে যিনি মানুষকে সত্য ও জ্ঞানের অন্বেষণে উদ্বুদ্ধ করেছিলেন, তাঁর চি', '', 1, 'news_310bdb8998782c62.jpg', 'বিজ্ঞান জগৎ', 4, 0, 'published', '2026-10-30 10:14:38', '2026-10-31 04:50:32'),
(9, 'কৃত্রিম বুদ্ধিমত্তা কি নতুন যুগের দার্শনিক? উপাত্ত ও চেতনার সীমারেখা', 'Is artificial intelligence the philosopher of a new era? The boundary between data and consciousness', '----------1793355544', 'প্রযুক্তির রথযাত্রায় আজ কৃত্রিম বুদ্ধিমত্তা (AI) কেবল হিসাব-নিকাশ বা কোডিংয়ের গণ্ডি পেরিয়ে মানবীয় মনস্তত্ত্বের চৌকাঠ পর্যন্ত পৌঁছে গেছে। মানুষের সৃষ্টিশীলতা, ভাষা এবং জটিল সিদ্ধান্ত গ্রহণের ক্ষমতা রপ্ত করার সুবাদে প্রশ্ন উঠেছে—প্রযুক্তির এই অভাবনীয় উৎকর্ষ কি শেষ পর্যন্ত দর্শন ও চেতনার প্রচলিত সংজ্ঞাকে পাল্টে দিতে চলেছে? নাকি যন্ত্র কেবল মানুষের চিন্তারই এক অবিকল ছায়া মাত্র? তথ্য প্রক্রিয়াকরণ বনাম অনুধাবন প্রাচীন গ্রিক ও আধুনিক জ্ঞানতত্ত্বের মূল ভিত্তি হলো মানুষের অন্তর্দর্শন এবং অর্জিত জ্ঞান। প্রখ্যাত দার্শনিক রেনে দেকার্ত বা জন লক যেভাবে জ্ঞান অর্জনের সাথে চেতনাকে যুক্ত করেছেন, সেখানে বিশ্বাস ও উপলব্ধির ভূমিকা ছিল অনস্বীকার্য। আধুনিক প্রযুক্তির যুগে কোটি কোটি বাইট ডেটা প্রসেস করে মুহূর্তের মধ্যে নিখুঁত ফলাফল দিতে পারে এআই। কিন্তু দার্শনিক জন সার্লের \'চাইনিজ রুম\' তত্ত্বের আলোকের বিচার করলে দেখা যায়, এই প্রক্রিয়াকরণ নিছকই যান্ত্রিক নিয়ম পালন। যন্ত্র শব্দ বা প্রতীক সাজাতে পারলেও তার ভেতরের গূঢ় অর্থ বা তাৎপর্য নিজে থেকে উপলব্ধি করতে পারে না। চেتনার অমীমাংসিত গোলকধাঁধা মনদর্শনের অন্যতম আলোচিত বিষয় হলো চেতনার \'কঠিন সমস্যা\' (The Hard Problem of Consciousness), যা তুলে ধরেছেন তাত্ত্বিক ডেভিড চালমার্স। কৃত্রিম বুদ্ধিমত্তা প্রোগ্রামিংয়ের মাধ্যমে মানুষের বাহ্যিক আচরণ এবং বুদ্ধিমত্তাকে নিখুঁতভাবে অনুকরণ (Simulation) করতে পারলেও, কোনো কিছু অনুভব করার ক্ষমতা—যেমন আনন্দ, বেদনা কিংবা অনুরাগের ভেতরের অনুভূতি (Qualia)—যন্ত্রের পক্ষে অর্জন করা অসম্ভব। জৈবিক মস্তিষ্কের অনুভূতি এবং কৃত্রিম অ্যালগরিদমের হিসাব কখনো এক বিন্দুতে মিলিত হয় না। ভবিষ্যতের দার্শনিক দিশা বিজ্ঞান ও দর্শনের এই সন্ধিক্ষণে দাঁড়িয়ে সবচেয়ে বড় প্রশ্নটি হলো নৈতিকতা। দেকার্তের বিখ্যাত উক্তি—“আমি চিন্তা করি, তাই আমি আছি”—চেতনার সাথে অস্তিত্বের যোগসূত্র স্থাপন করে। আত্ম-সচেতনতাহীন কোনো প্রযুক্তি নৈতিক সত্তা হতে পারে না। ফলে যন্ত্রের কাজের দায়ভার সবসময় মানবকেই নিতে হয়। কৃত্রিম বুদ্ধিমত্তা মানুষের পরিপূরক হিসেবে প্রগতির পথ প্রশস্ত করতে পারে, কিন্তু এটি মানব চেতনার কোনো বিকল্প বা স্বয়ংসম্পূর্ণ দার্শনিক সত্তা নয়।', 'In the ongoing evolution of technology, artificial intelligence (AI) has transcended the realms of mere calculation and coding, reaching the very threshold of human psychology. With its capacity to master human creativity, language, and complex decision-making, a profound question arises: is this extraordinary technological advancement poised to redefine our traditional understanding of philosophy and consciousness? Or is the machine merely a precise reflection—a shadow—of human thought? Information Processing vs. Comprehension The foundations of both ancient Greek and modern epistemology are rooted in human introspection and acquired knowledge. Philosophers such as René Descartes and John Locke linked the acquisition of knowledge to consciousness, where belief and subjective understanding played an indispensable role. In today’s era of advanced technology, AI can process vast amounts of data—billions of bytes—to deliver precise results in an instant. Yet, viewed through the lens of philosopher John Searle’s \"Chinese Room\" thought experiment, this process is revealed to be nothing more than the execution of mechanical rules. While a machine may arrange words or symbols, it cannot independently grasp the profound meaning or significance underlying them. The Unresolved Labyrinth of Consciousness A central topic in the philosophy of mind is the \"Hard Problem of Consciousness,\" a concept articulated by the theorist David Chalmers. Although AI can be programmed to flawlessly simulate human behavior and intelligence, it remains impossible for a machine to possess the capacity for subjective experience—the internal \"qualia\" of feelings such as joy, pain, or affection. The sensations experienced by a biological brain and the calculations performed by an artificial algorithm can never truly converge. The Philosophical Horizon of the Future Standing at this critical juncture where science and philosophy intersect, the most pressing question concerns morality. Descartes’ famous dictum—“I think, therefore I am”—establishes a link between consciousness and existence. Technology devoid of self-awareness cannot be a moral entity. Consequently, the responsibility for the actions of a machine must always rest with humans. While artificial intelligence can serve as a complement to humanity and pave the way for progress, it is neither a substitute for human consciousness nor a self-sufficient philosophical entity.', 'প্রযুক্তির রথযাত্রায় আজ কৃত্রিম বুদ্ধিমত্তা (AI) কেবল হিসাব-নিকাশ বা কোডিংয়ের গণ্ডি পেরিয়ে মানবীয় মনস্তত্ত্বের চৌকাঠ পর্যন্ত পৌঁছে গেছে। মানুষের সৃষ্টিশীলতা, ভাষা এবং জটিল সিদ্ধান্ত গ্রহ', '', 1, 'news_071703f291618edf.jpg', 'বিজ্ঞান জগৎ', 6, 0, 'published', '2026-10-30 10:17:37', '2026-10-30 10:55:33'),
(10, 'প্লেটোর আদর্শ রাষ্ট্র ও রূপক তত্ত্ব: পশ্চিমা দর্শনের ভিত্তিপ্রস্তর', 'Plato’s Ideal State and Theory of Forms: The Cornerstone of Western Philosophy', '--------', 'পাশ্চাত্য দর্শন, রাষ্ট্রবিজ্ঞান এবং নৈতিকতার ইতিহাসে প্লেটো (Plato) এমন এক নাম, যার চিন্তাধারা আড়াই হাজার বছর ধরে মানবসভ্যতাকে চালিত করে আসছে। এথেন্সের অভিজাত পরিবারে জন্ম নেওয়া প্লেটো তাঁর শিক্ষক সক্রেটিসের মৃত্যুর পর গ্রিক রাজনীতি ও দর্শনের চিরায়ত ধারা বদলে দেন। তিনি প্রতিষ্ঠা করেন ইতিহাসের অন্যতম প্রাচীন উচ্চশিক্ষা প্রতিষ্ঠান—\'একাডেমি\' (Academy)। আধুনিক যুগে গণতন্ত্র, ন্যায়বিচার এবং জ্ঞানতত্ত্বের তাত্ত্বিক আলোচনায় প্লেটোর দর্শন আজও অত্যন্ত প্রাসঙ্গিক।\r\n\r\nরূপক তত্ত্ব (Theory of Forms / Ideas)\r\nপ্লেটোর দর্শনের সবচেয়ে মৌলিক ভিত্তি হলো তাঁর \'তত্ত্ব বা রূপক তত্ত্ব\' (Theory of Forms)। তাঁর মতে, আমরা ইন্দ্রিয় দিয়ে যে দৃশ্যমান পৃথিবী দেখি, তা হলো কেবল একটি পরিবর্তনশীল ও অবাস্তব রূপ (Illusion)। এর পেছনে রয়েছে অপরিবর্তনীয়, শাশ্বত ও প্রকৃত সত্যের এক জগত, যাকে বলা হয় \'Forms\' বা \'Ideas\'।\r\n\r\nউদাহরণস্বরূপ, আমরা পৃথিবীতে নানা আকৃতির সুন্দর বস্তু দেখতে পাই, কিন্তু \'সৌন্দর্য\' নামক নিখুঁত ধারণাটি চিরন্তন ও অপার্থিব জগতে বিদ্যমান। ইন্দ্রিয়লব্ধ জ্ঞান ক্ষণস্থায়ী, আর বুদ্ধিভিত্তিক অনুধাবনই হলো প্রকৃত জ্ঞান।\r\n\r\nগুহার রূপক (Allegory of the Cave)\r\nমানুষের অজ্ঞতা থেকে জ্ঞানে উত্তরণের প্রক্রিয়াটি বোঝাতে প্লেটো তাঁর বিখ্যাত \'গুহার রূপক\' উপস্থাপন করেছেন। কল্পনা করুন, কিছু মানুষ জন্ম থেকে একটি অন্ধকার গুহায় পিছন ফিরে বন্দি রয়েছে এবং গুহার দেয়ালে তারা কেবল বাইরের আলোয় তৈরি হওয়া কিছু ছায়াকেই সত্য বলে মনে করে।\r\nযখন একজন বন্দি মুক্তি পেয়ে গুহার বাইরে আসল সূর্যের আলোয় এসে চারপাশ দেখতে পায়, তখন সে বুঝতে পারে এতদিন সে যা দেখেছে তা কেবল ছায়া মাত্র, আসল সত্য নয়। এই রূপকের মাধ্যমে প্লেটো ফিলোসোফার-কিং বা \'দার্শনিক রাজা\'-র শিক্ষার প্রয়োজনীয়তা তুলে ধরেছেন, যিনি অন্ধকারের অজ্ঞতা থেকে সমাজকে আলোর পথে নিয়ে আসেন।\r\n\r\nআদর্শ রাষ্ট্র ও ন্যায়বিচার (The Republic)\r\nপ্লেটোর অমর গ্রন্থ \'দ্য রিপাবলিক\' (The Republic)-এ তিনি একটি নিখুঁত ও ন্যায়ভিত্তিক আদর্শ রাষ্ট্রের রূপরেখা এঁকেছেন। তাঁর মতে, রাষ্ট্রে শান্তি ও শৃঙ্খলা বজায় রাখতে হলে সমাজকে তিনটি শ্রেণীতে বিভক্ত করা উচিত:\r\n১. শাসক শ্রেণী (Philosopher Kings): যারা জ্ঞান ও প্রজ্ঞার অধিকারী এবং রাজ্য পরিচালনা করবে।\r\n\r\n২. সৈনিক বা রক্ষী শ্রেণী (Guardians): যারা রাষ্ট্রের নিরাপত্তা নিশ্চিত করবে।\r\n\r\n৩. উৎপাদক শ্রেণী (Producers): কৃষক, কারিগর ও ব্যবসায়ীরা যারা অর্থনৈতিক চাহিদা পূরণ করবে।\r\n\r\nপ্লেটোর মতে, যখন সমাজের প্রতিটি শ্রেণী নিজ নিজ দায়িত্ব সততার সাথে পালন করে এবং কেউ অন্যের অধিকারে হস্তক্ষেপ করে না, তখনই কেবল প্রকৃত \'ন্যায়বিচার\' (Justice) প্রতিষ্ঠিত হয়।\r\n\r\nআধুনিক যুগে প্লেটোর প্রাসঙ্গিকতা\r\nআজকের একবিংশ শতাব্দীতে কৃত্রিম বুদ্ধিমত্তা এবং প্রযুক্তির যুগেও প্লেটোর চিন্তা আমাদের ভাবায়। তথাকথিত ভার্চুয়াল বাস্তবতা (VR) বা ডিজিটাল ডেটার দুনিয়া আজকের মানুষকেও প্লেটোর সেই \'গুহার রূপক\'-এর ছায়ার মতো মোহাচ্ছন্ন করে রাখে। বাহ্যিক চাকচিক্য আর প্রকৃত সত্যের পার্থক্য বুঝতে প্লেটোর দর্শন আমাদের সমালোচনামূলক চিন্তার খোরাক জোগায়।\r\n\r\nউপসংহার\r\nপ্লেটো শুধু একজন প্রাচীন দার্শনিকই ছিলেন না, তিনি ছিলেন এক দূরদর্শী সমাজসংস্কারক। তাঁর চিন্তাধারা মানুষের মনস্তত্ত্ব, রাজনীতি এবং নৈতিকতার মানদণ্ডকে চিরকালের জন্য বদলে দিয়েছে। মানবজাতির মুক্তির অন্বেষণে এবং প্রকৃত সত্যের সন্ধানে প্লেটোর দর্শন যুগ যুগ ধরে পথপ্রদর্শক হয়ে থাকবে।', 'In the history of Western philosophy, political science, and ethics, Plato stands as a figure whose ideas have shaped human civilization for two and a half millennia. Born into an aristocratic Athenian family, Plato transformed the course of Greek politics and philosophy following the death of his teacher, Socrates. He founded the \'Academy\'—one of the oldest institutions of higher learning in history. Even today, Plato’s philosophy remains highly relevant to contemporary theoretical discussions on democracy, justice, and epistemology.\r\n\r\nTheory of Forms (or Ideas)\r\nThe most fundamental pillar of Plato’s philosophy is his \'Theory of Forms\' (or \'Theory of Ideas\'). According to him, the visible world we perceive through our senses is merely a transient and unreal manifestation—an illusion. Underlying this is a realm of immutable, eternal, and absolute truth, which he termed \'Forms\' or \'Ideas.\'\r\n\r\nFor instance, while we encounter beautiful objects of various shapes in the physical world, the perfect concept of \'Beauty\' itself exists in an eternal, transcendent realm. Knowledge derived from the senses is fleeting; true knowledge, by contrast, is attained through intellectual apprehension.\r\n\r\nAllegory of the Cave\r\nTo illustrate the transition from human ignorance to knowledge, Plato presented his famous \'Allegory of the Cave.\' Imagine individuals held captive from birth inside a dark cave, facing away from the entrance; they mistake the shadows cast upon the cave wall—created by light from the outside—for reality.\r\nWhen one captive is set free and emerges from the cave into the light of the sun, they realize that what they had previously perceived were merely shadows, not the true reality. Through this allegory, Plato underscores the necessity for a \'Philosopher-King\'—a ruler who guides society out of the darkness of ignorance and toward the light of truth. The Ideal State and Justice (The Republic)\r\nIn his immortal work, \'The Republic\', Plato outlines an ideal, just state. He argues that to maintain peace and order, society should be divided into three classes:\r\n1. The Ruling Class (Philosopher Kings): Those who possess knowledge and wisdom, and are tasked with governing the state.\r\n\r\n2. The Warrior or Guardian Class (Guardians): Those responsible for ensuring the security of the state.\r\n\r\n3. The Producing Class (Producers): Farmers, artisans, and merchants who fulfill the society\'s economic needs.\r\n\r\nAccording to Plato, true \'justice\' is established only when each class in society performs its respective duties with integrity, without encroaching upon the rights of others.\r\n\r\nThe Relevance of Plato in the Modern Era\r\nEven today, in the 21st century—an age defined by artificial intelligence and technology—Plato’s ideas remain deeply thought-provoking. The realm of virtual reality (VR) and digital data can, much like the shadows in Plato’s \'Allegory of the Cave\', leave contemporary individuals under a similar kind of illusion. Plato’s philosophy provides the essential foundation for critical thinking, enabling us to distinguish between superficial appearances and the ultimate truth.\r\n\r\nConclusion\r\nPlato was more than just an ancient philosopher; he was a visionary social reformer. His ideas have fundamentally and permanently transformed our understanding of human psychology, politics, and ethical standards. In the ongoing human quest for liberation and the pursuit of absolute truth, Plato’s philosophy will continue to serve as a guiding light for generations to come.', 'পাশ্চাত্য দর্শন, রাষ্ট্রবিজ্ঞান এবং নৈতিকতার ইতিহাসে প্লেটো (Plato) এমন এক নাম, যার চিন্তাধারা আড়াই হাজার বছর ধরে মানবসভ্যতাকে চালিত করে আসছে। এথেন্সের অভিজাত পরিবারে জন্ম নেওয়া প্লেটো তাঁর শ', '', 1, 'news_6861c89d327c6fbb.jpg', 'বিজ্ঞান জগৎ', 0, 0, 'published', '2026-10-30 10:24:55', '2026-10-30 10:24:55'),
(11, 'ইবনে সিনার দর্শন ও বিজ্ঞান সাধনা: মধ্যযুগের ইসলামী রেনেসাঁ এবং আধুনিক বিজ্ঞানের ভিত্তি', 'Ibn Sina’s Philosophy and Scientific Endeavors: The Islamic Renaissance of the Middle Ages and the Foundations of Modern Science', '------------', 'ইসলামী স্বর্ণযুগের বুদ্ধিবৃত্তিক ইতিহাসে যে কয়েকজন মনীষী বিশ্বসভ্যতাকে চিরতরে বদলে দিয়েছিলেন, আবু আলী ইবনে সিনা (লাতিন নাম \'অ্যাভিসেনা\') তাঁদের মধ্যে অন্যতম। বহুমুখী প্রতিভার অধিকারী এই দার্শনিক ও চিকিৎসাবিজ্ঞানী অ্যারিস্টটলীয় দর্শন এবং ইসলামী একেকত্ববাদের মধ্যে এক চমৎকার মেলবন্ধন তৈরি করেছিলেন। চিকিৎসাবিজ্ঞানের বাইবেলখ্যাত গ্রন্থ \'আল-কানুন ফি আল-তিব্ব\' থেকে শুরু করে দর্শনশাস্ত্রের বিশাল রচনা \'কিতাব আশ-শিফা\' (The Book of Healing)—ইবনে সিনার সৃষ্টি আজও আধুনিক জ্ঞান-বিজ্ঞান ও দর্শনের মূল ভিত্তিগুলোর একটি।\r\n\r\nসত্তাতত্ত্ব ও অপরিহার্য সত্তা (The Necessary Existent)\r\nইবনে সিনার দর্শনের অন্যতম প্রধান ভিত্তি হলো তাঁর সত্তাতত্ত্ব (Ontology)। তিনি অস্তিত্বকে দুটি ভাগে ভাগ করেছেন:\r\n১. সম্ভাব্য সত্তা (Possible Being): যার অস্তিত্ব থাকা বা না থাকা উভয় সম্ভাবনাই রয়েছে (যেমন—এই মহাবিশ্বের সমস্ত সৃষ্টি)।\r\n২. অপরিহার্য সত্তা (Necessary Existent): যাঁর অস্তিত্বের জন্য অন্য কারো ওপর নির্ভর করতে হয় না এবং যিনি নিজেই নিজের অস্তিত্বের কারণ। ইবনে সিনার এই তত্ত্বটি পরবর্তীকালে মধ্যযুগের ইউরোপীয় স্কলাস্টিক দর্শন এবং থমাস অ্যাকুইনাসের চিন্তাধারাকে গভীরভাবে প্রভাবিত করেছিল।\r\n\r\nমনদর্শন ও \'ভাসমান মানব\' রূপক (The Floating Man Thought Experiment)\r\nমানুষের আত্মাসচেতনতা এবং মন ও শরীরের সম্পর্ক বোঝাতে ইবনে সিনা তাঁর বিখ্যাত \'ভাসমান মানব\' (The Floating Man) রূপকটি উপস্থাপন করেছেন।\r\nকল্পনা করুন, একজন মানুষ শূন্যে ভেসে আছে এবং তার চোখ বাঁধা, হাত-পা এমনভাবে ছড়ানো যে সে কোনো স্পর্শ বা ইন্দ্রিয়গত অনুভূতি পাচ্ছে না। ইবনে সিনার মতে, এই অবস্থায় বাহ্যিক কোনো ইন্দ্রিয় বা শরীরের অস্তিত্বের অনুভূতি না থাকলেও সেই মানুষটি নিজ সত্তার বা আত্মாவின் উপস্থিতি স্পষ্টভাবে বুঝতে পারবে। এই পরীক্ষার মাধ্যমে তিনি প্রমাণ করেছেন যে, আত্মা শরীর থেকে স্বাধীন এবং এটি মানুষের চেতনার মূল ভিত্তি।\r\n\r\nবিজ্ঞান ও দর্শনের সমন্বয়\r\nইবনে সিনা বিশ্বাস করতেন যে, জ্ঞান অর্জনের ক্ষেত্রে যুক্তি এবং পর্যবেক্ষণ উভয়েরই সমান ভূমিকা রয়েছে। চিকিৎসাবিজ্ঞানে রোগ নির্ণয় থেকে শুরু করে ওষুধ প্রস্তুতকরণে তিনি যেভাবে পরীক্ষামূলক পদ্ধতির ওপর জোর দিয়েছিলেন, তা আধুনিক বৈজ্ঞানিক পদ্ধতির (Scientific Method) পূর্বসূরি হিসেবে বিবেচিত হয়। তাঁর মতে, দর্শন মানুষকে সত্যের পথ দেখায় এবং বিজ্ঞান তাকে বাস্তবে রূপ দেয়।\r\n\r\nআধুনিক যুগে ইবনে সিনার প্রাসঙ্গিকতা\r\nআজকের একবিংশ শতাব্দীতে কৃত্রিম বুদ্ধিমত্তা, চেতনা এবং মনস্তত্ত্ব নিয়ে যখন নতুন করে বিতর্ক শুরু হয়েছে, তখন ইবনে সিনার মনদর্শন ও \'ভাসমান মানব\' তত্ত্ব চেতনার উৎস অনুসন্ধানে নতুন দিকদর্শন দেয়। ডেটা প্রসেসিং আর মানবিক আত্মোপলব্ধির ফারাক বুঝতে মধ্যযুগের এই ইসলামী দার্শনিকের চিন্তাধারা আজও অত্যন্ত তাৎপর্যপূর্ণ।\r\n\r\nউপসংহার\r\nইবনে সিনা কেবল একজন চিকিৎসক বা দার্শনিক ছিলেন না, তিনি ছিলেন প্রাচ্য ও পাশ্চাত্যের সেতুবন্ধন। মানব সভ্যতার জ্ঞানভাণ্ডারে তাঁর অবদান অমর হয়ে রয়েছে এবং যুগে যুগে মুক্তবুদ্ধি ও বিজ্ঞানমনস্কতার চর্চায় তাঁর দর্শন গবেষকদের অনুপ্রাণিত করে যাবে।', 'Among the great thinkers who transformed world civilization during the intellectual history of the Islamic Golden Age, Abu Ali ibn Sina (known in the West as \'Avicenna\') stands out. A polymath of extraordinary talent, this philosopher and physician forged a brilliant synthesis between Aristotelian philosophy and Islamic monotheism. From his seminal medical text, *Al-Qanun fi al-Tibb* (The Canon of Medicine)—often regarded as the \'bible\' of medical science—to his monumental philosophical work, *Kitab al-Shifa* (The Book of Healing), Ibn Sina’s contributions remain a foundational pillar of modern knowledge, science, and philosophy.\r\n\r\nOntology and the \'Necessary Existent\'\r\nA cornerstone of Ibn Sina’s philosophy is his ontology. He categorized existence into two types:\r\n1. Possible Being: That which may or may not exist (e.g., all creations within this universe).\r\n2. The Necessary Existent: That which does not depend on anything else for its existence and is the cause of its own existence. This theory profoundly influenced later medieval European scholastic philosophy, as well as the thought of Thomas Aquinas.\r\n\r\nPhilosophy of Mind and the \'Floating Man\' Thought Experiment\r\nTo explore human self-awareness and the relationship between the mind and the body, Ibn Sina introduced his famous \'Floating Man\' thought experiment.\r\nImagine a person suspended in a void, blindfolded, with their limbs positioned such that they receive no tactile or sensory input. Ibn Sina argued that, even in the absence of any external sensory data or awareness of one’s own body, the individual would still possess a clear, distinct perception of their own existence—their self or soul. Through this experiment, he demonstrated that the soul is independent of the body and serves as the fundamental basis of human consciousness. The Synthesis of Science and Philosophy\r\nIbn Sina believed that both reason and observation play an equal role in the acquisition of knowledge. His emphasis on experimental methods—ranging from the diagnosis of diseases to the preparation of medicines—is considered a precursor to the modern scientific method. In his view, philosophy guides humanity toward the truth, while science brings that truth into reality.\r\n\r\nThe Relevance of Ibn Sina in the Modern Era\r\nIn the twenty-first century, as new debates emerge regarding artificial intelligence, consciousness, and psychology, Ibn Sina’s philosophy of mind—and his \"floating man\" thought experiment—offer fresh perspectives on the origins of consciousness. The ideas of this medieval Islamic philosopher remain highly significant for understanding the distinction between data processing and human self-awareness.\r\n\r\nConclusion\r\nIbn Sina was more than just a physician or a philosopher; he served as a bridge between the East and the West. His contributions to the collective knowledge of human civilization remain immortal, and his philosophy will continue to inspire researchers in the pursuit of free thought and a scientific mindset for generations to come.', 'ইসলামী স্বর্ণযুগের বুদ্ধিবৃত্তিক ইতিহাসে যে কয়েকজন মনীষী বিশ্বসভ্যতাকে চিরতরে বদলে দিয়েছিলেন, আবু আলী ইবনে সিনা (লাতিন নাম \'অ্যাভিসেনা\') তাঁদের মধ্যে অন্যতম। বহুমুখী প্রতিভার অধিকারী এই ', '', 1, 'news_a6513db0e9e74f50.jpg', 'বিজ্ঞান জগৎ', 0, 0, 'published', '2026-10-30 10:31:09', '2026-10-30 10:31:09'),
(12, 'জনাকীর্ণ কক্ষে নীরব অশ্রু', 'Silent Tears in a Crowded Room', '---', 'লাল শাড়িতে সাজল কনে,\r\n চারপাশটা আলোয় ভাসে,\r\n সবাই আজ মাতোয়ারা খুশিতে আর মৃদু হাসে।\r\n\r\n মণ্ডপজুড়ে চলছে কত প্রীতি আর শুভক্ষণ,\r\n দুজনের সেই মিলন দেখে ভাঙছে আমার মন।\r\n হাতে গোলাপ নিয়ে দাঁড়িয়ে সে,\r\n বর পরেছে স্যুট-টাই,\r\n তাদের চোখের ইশারায় আজ খুশির সীমা নাই।\r\n অথচ আমি দাঁড়িয়ে আছি একটু দূরে কোণায় চেপে,\r\n আমার বুকটা ভেঙে যাচ্ছে অব্যক্ত এক ব্যথায় কেঁপে।\'\r\n কালো হুডির বুকজুড়ে লাল হৃদয়ে নাম যে লেখা,\r\n সব হারিয়ে এই জীবনে রইল শুধু দূর থেকে দেখা।\r\n\r\n চোখের কোণে জমছে লবণ,\r\n গাল বেয়ে নামছে ধারা,\r\n সবাই যখন আনন্দিত,\r\n আমি ভীষণ আত্মহারা।\r\n\r\n মণ্ডপের ওই প্রদীপের আলোয় মুখটা তোমার ভাসে,\r\n অন্য কারো জীবনসঙ্গী হয়ে তুমি হাসছ পাশে।\r\n সিঁথির সিঁদুর বা বিয়ের সাজে তুমি আজ বড় অচেনা,\r\n আমার এই কষ্টগুলো কেউ তো আর বুঝতে পারল না।\r\n ভালো থেকো নতুন ঘরে,\r\n নতুন তোমার ভুবনে,\r\n আমার এই কান্নাগুলো জমা থাক ওই শ্রাবণের মনে।\r\n সব আয়োজন শেষ হলো আজ,\r\n মুছে গেল স্বপ্নগুলো,\r\n অভাগা এই প্রেমিকের মনে শুধু অন্ধকারের ধুলো।', 'In red attire the bride is dressed,\r\n the hall is glowing bright,\r\n Everyone is filled with joy,\r\n rejoicing in the light.\r\n\r\n The wedding canopy is graced with rituals of the day,\r\n Watching them together makes my heart just fade away.\r\n With a rose he stands beside her in a sharp tuxedo suit,\r\n In their quiet,\r\n tender glances,\r\n all my words are mute.\r\n Yet I linger in the corner,\r\n standing far apart,\r\n With an unexpressed agony breaking through my heart.\r\n\r\n On my black hoodie,\r\n a red heart bears a written name,\r\n Having lost it all,\r\n I watch them from a quiet frame.\r\n\r\n Tears are welling in my eyes and rolling down my face,\r\n While the crowd is full of laughter in this happy place.\r\n In the flickering candle\'s glow,\r\n your face appears so clear,\r\n Smiling softly by another,\r\n as someone else\'s dear.\r\n\r\n With the vermilion on your parting and your bridal grace,\r\n You have grown so unfamiliar in this crowded space.\r\n May you live in happiness within your brand-new dome,\r\n Let my tears be locked away inside the storms that roam.\r\n\r\n Every dream is wiped away,\r\n the celebration\'s done,\r\n Only darkness lingers for this lonely,\r\n broken one.', 'লাল শাড়িতে সাজল কনে,\r\n চারপাশটা আলোয় ভাসে,\r\n সবাই আজ মাতোয়ারা খুশিতে আর মৃদু হাসে।\r\n\r\n মণ্ডপজুড়ে চলছে কত প্রীতি আর শুভক্ষণ,\r\n দুজনের সেই মিলন দেখে ভাঙছে আমার মন।\r\n হাতে গোলাপ নিয়ে দাঁড়িয়ে সে,\r\n বর পরে', '', 11, 'news_21b75de68b371ced.jpg', 'বিজ্ঞান জগৎ', 6, 1, 'published', '2026-10-30 10:48:36', '2026-10-31 02:19:07'),
(13, 'বিজয়ী সুখে তুমি, স্মৃতির আঁধারে আমি', 'You, in the joy of victory; I, in the shadows of memory.', '-----', 'আমি হেরে গেছি কোনো যুদ্ধে নয়,\r\nহেরে গেছি তোমার এক চিলতে উপেক্ষায়।\r\nযেখানে বাজি ধরেছিলাম আস্ত একটা হৃদয়,\r\nসেখানে শেষমেশ জুটলো শুধু একাকীত্বের ভয়।\r\nতুমি তো জয়ী হয়ে চলে গেলে আপন আলোয়,\r\nআর আমি আটকে রইলাম ফেলে আসা স্মৃতির আঁধারে।\r\nযে পথ দিয়ে দুজনে হেঁটেছিলাম কত শত রূপকথায়,\r\nআজ সেই পথেই ঝরে পড়ে শুকনো পাতা নীরব ব্যথায়।\r\nভালোবাসা কি তবে শুধুই জয়ের লড়াই ছিল?\r\nনাকি হাত বাড়িয়ে শূন্যতা কুড়ানোর এক খেলা?\r\nআমি না হয় পরাজয়ের কলঙ্কটাই তুলে নিলাম বুকে,\r\nতুমি অন্তত ভালো থেকো তোমার জয়ী হওয়া সুখে।\r\nএই পরাজয়ে নেই কোনো আক্ষেপ, নেই কোনো ক্ষোভ,\r\nহয়তো আমার ভালোবাসাতেই ছিল না তোমার পাওয়ার অধিকার।\r\nতবু যদি কখনও ফিরে তাকাও পেছনের পানে,\r\nদেখবে এক পরাজিত প্রেমিক আজও ভালোবেসে যায় তোমায় নিঃশব্দে।', 'I have not lost in any battle,\r\nI have lost simply to a touch of your neglect.\r\nWhere I had staked an entire heart,\r\nIn the end, there remained only the fear of loneliness.\r\nYou walked away victoriously into your own light,\r\nWhile I remained trapped in the darkness of left-behind memories.\r\nAlong the path where the two of us walked through countless fairy tales,\r\nToday, dry leaves fall in silent sorrow on that very path.\r\nWas love merely a battle to win?\r\nOr a game of reaching out to gather emptiness?\r\nI will gladly take the stigma of defeat to my chest,\r\nAt least you stay well in the happiness of your victory.\r\nIn this defeat, there is no regret, no anger,\r\nPerhaps my love simply did not have the right to claim you.\r\nYet if you ever look back behind you,\r\nYou will see a defeated lover still loving you in silence.', 'আমি হেরে গেছি কোনো যুদ্ধে নয়,\r\nহেরে গেছি তোমার এক চিলতে উপেক্ষায়।\r\nযেখানে বাজি ধরেছিলাম আস্ত একটা হৃদয়,\r\nসেখানে শেষমেশ জুটলো শুধু একাকীত্বের ভয়।\r\nতুমি তো জয়ী হয়ে চলে গেলে আপন আলোয়,\r\nআর আমি আটকে র', '', 11, 'news_e8ed170532303a87.jpg', 'বিজ্ঞান জগৎ', 1, 0, 'published', '2026-10-30 12:31:00', '2026-10-31 02:20:36'),
(14, 'ব্লক লিস্টের ওপারে', 'Beyond the Block List', '--', 'আমি তোমায় ভালোবেসেছি বারবার,\r\nকখনও গোপনে, কখনও বা চিৎকার করে অবিকল।\r\nতুমি প্রতিবারই ফিরিয়ে দিয়েছ আমায়,\r\nহয়তো আমার ভালোবাসায় কোনো অভাব ছিল, না হয় কোনো ভুল।\r\nআজ আমার সব কথার পথ তুমি করেছ বন্ধ,\r\nদেয়াল তুলেছ ডিজিটাল দুনিয়ার প্রতিটি কোণে।\r\nব্লক লিস্টের ওপারে আমি এক নিঃসঙ্গ পথিক,\r\nযে শুধু তোমার দেওয়া নীরবতা গড়ায় মনে মনে।\r\nতুমি ভেবেছ আমায় ব্লক করলেই থেমে যাবে সব?\r\nমুছে যাবে হৃদয়ে খোদাই করা তোমার নাম?\r\nতুমি তো শুধু যোগাযোগ থামিয়েছ প্রিয়,\r\nআমার অনুভূতির গভীরতা বোঝেনি তোমার দাম।\r\nআজ তোমার সঙ্গে কথা বলার কোনো উপায় নেই,\r\nনেই কোনো চিরকুট পাঠাবার নীরব প্রহর।\r\nতবুও অদ্ভুত এক টান রয়ে গেছে ভেতরে,\r\nযার কোনো উত্তর নেই, নেই কোনো শহর।\r\nতুমি সুখে থেকো তোমার নিজের মতো করে,\r\nআমার না-পাওয়ার আক্ষেপ না হয় আমারই থাকুক।\r\nদূর থেকেই তোমায় ভালোবেসে যাব আজীবন,\r\nযেখানে কোনো ব্লকলিস্ট নেই, নেই কোনো সম্পর্কের দায় বা চাবুক।', 'I have loved you over and over again,\r\nSometimes in secret, sometimes out loud with all my heart.\r\nEvery time you turned me away,\r\nPerhaps my love lacked something, or perhaps it was my fault.\r\nToday, you have closed all paths for my words,\r\nAnd built a wall in every corner of the digital world.\r\nBeyond the block list, I am a lonely traveler,\r\nWeaving the silence you gave me deep within my mind.\r\nDid you think blocking me would make everything stop?\r\nOr erase your name carved deep within my heart?\r\nYou only stopped the communication, my love,\r\nYour worth never measured the depth of my feelings.\r\nToday, there is no way to speak to you,\r\nNo quiet hours left to send a little note.\r\nYet an inexplicable pull lingers deep inside,\r\nThat has no answer, and no home.\r\nMay you stay happy in your own way,\r\nAnd let the regret of not having you remain mine alone.\r\nFrom afar, I will go on loving you for a lifetime,\r\nWhere there are no block lists, no ties, and no whips of relation.', 'আমি তোমায় ভালোবেসেছি বারবার,\r\nকখনও গোপনে, কখনও বা চিৎকার করে অবিকল।\r\nতুমি প্রতিবারই ফিরিয়ে দিয়েছ আমায়,\r\nহয়তো আমার ভালোবাসায় কোনো অভাব ছিল, না হয় কোনো ভুল।\r\nআজ আমার সব কথার পথ তুমি করেছ বন্ধ,\r\nদেয়া', '', 11, 'news_48e1144e59439801.jpg', 'বিজ্ঞান জগৎ', 2, 0, 'published', '2026-10-30 12:35:17', '2026-10-31 02:21:53'),
(15, 'একতরফা এই হৃদয়ের গান', 'The song of this one-sided heart', '----1793365174', 'একতরফা এই হৃদয়ের গান,\r\n\r\nতোমার আকাশে ওড়াই অভিমান।\r\n\r\nতুমি জানো না তো আমার এ মন,\r\n\r\nতোমাকেই খোঁজে প্রতিটা ক্ষণ।\r\n\r\nতোমার ঘরের জানালার ধারে,\r\n\r\nবসে থাকি আমি একলা প্রহরে।\r\n\r\nতুমি তাকাও না কভু এ দিকে,\r\n\r\nভেসে যাই আমি অচেনা সুখে।\r\n\r\nচোখে চোখ পড়লে মুখ ফিরিয়ে নাও,\r\n\r\nএক ফোঁটা দয়াও কি আমায় না দাও?\r\n\r\nআমার কবিতা, আমার ডায়েরীর পাতা,\r\n\r\nসব জুড়ে শুধু তোমারই কথা।\r\n\r\nতুমি অন্য কারও ভাবনায় মগ্ন,\r\n\r\nআমার হৃদয় তবুও তোমাকেই লগ্ন।\r\n\r\nহাজার বার তুমি দূরে ঠেলে দাও,\r\n\r\nতবু কেন মন তোমাকেই চায় আও?\r\n\r\nকথা হয় না তো বহুদিন ধরে,\r\n\r\nতবু মেঘ জমে আমার অন্তরে।\r\n\r\nব্লক লিস্টের ওপারে তোমার ঠিকানা,\r\n\r\nআমি খুঁজে ফিরি না পাওয়ার সীমানা।\r\n\r\nসবাই তো আর সব পায় না জীবনে,\r\n\r\nকেউ কেউ ভালোবাসে লুকিয়ে গোপনে।\r\n\r\nআমার ভালোবাসা নাই বা হলো তোমার,\r\n\r\nতুমি ভালো থেকো এই প্রার্থনা আমার।\r\n\r\nতুমি হাসলে আমারও মুখে ফোটে হাসি,\r\n\r\nদূর থেকেই তোমায় নিঃশব্দে ভালোবাসি।\r\n\r\nকোনো দাবি নেই, নেই কোনো অধিকার,\r\n\r\nতবু তুমি আমার হৃদয়ের এক এবং অহংকার।', 'Unrequited is this song of the heart,\r\n\r\nIn your sky, I set my pride apart.\r\n\r\nYou do not know this mind of mine,\r\n\r\nFor you, it searches every time.\r\n\r\nBeside the window of your room,\r\n\r\nI sit alone through hours of gloom.\r\n\r\nYou never glance towards my way,\r\n\r\nYet in strange bliss, I drift away.\r\n\r\nWhen our eyes meet, you turn your face,\r\n\r\nCan you not spare a drop of grace?\r\n\r\nMy poetry, the pages of my diary too,\r\n\r\nAll are filled with thoughts of you.\r\n\r\nYou are lost in someone else\'s mind,\r\n\r\nYet my heart to you is still entwined.\r\n\r\nThough you push me away a thousand times,\r\n\r\nWhy does my heart still seek your rhymes?\r\n\r\nWe haven\'t spoken in days so long,\r\n\r\nYet clouds gather where my feelings belong.\r\n\r\nBeyond the block list is your address,\r\n\r\nI wander the bounds of un-reachability, nothing less.\r\n\r\nNot everyone gets everything in life,\r\n\r\nSome love in secret, through quiet strife.\r\n\r\nEven if my love was never meant to be yours,\r\n\r\nMy only prayer is that peace is always yours.\r\n\r\nWhen you smile, a smile blooms on my face,\r\n\r\nFrom afar, I love you in a silent embrace.\r\n\r\nI have no claims, no rights to call,\r\n\r\nYet you are my heart\'s pride, my all.', 'একতরফা এই হৃদয়ের গান,\r\n\r\nতোমার আকাশে ওড়াই অভিমান।\r\n\r\nতুমি জানো না তো আমার এ মন,\r\n\r\nতোমাকেই খোঁজে প্রতিটা ক্ষণ।\r\n\r\nতোমার ঘরের জানালার ধারে,\r\n\r\nবসে থাকি আমি একলা প্রহরে।\r\n\r\nতুমি তাকাও না কভু এ দিকে,\r\n\r\nভেসে য', '', 11, 'news_425576476b79c8d2.jpg', 'বিজ্ঞান জগৎ', 4, 0, 'published', '2026-10-30 12:59:34', '2026-10-31 02:27:12');

-- --------------------------------------------------------

--
-- Table structure for table `news_comments`
--

DROP TABLE IF EXISTS `news_comments`;
CREATE TABLE IF NOT EXISTS `news_comments` (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `news_id` int NOT NULL,
  `parent_id` bigint UNSIGNED DEFAULT NULL,
  `commenter_name` varchar(80) NOT NULL,
  `comment_body` text NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_news_comments_news_created` (`news_id`,`created_at`,`id`),
  KEY `idx_news_comments_parent_created` (`parent_id`,`created_at`,`id`)
) ENGINE=InnoDB AUTO_INCREMENT=15 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `news_comments`
--

INSERT INTO `news_comments` (`id`, `news_id`, `parent_id`, `commenter_name`, `comment_body`, `created_at`) VALUES
(13, 15, NULL, 'SM Atiqur Rahman Limon', 'কবিতাটি খুব সুন্দর হয়েছে।', '2026-10-31 02:26:48'),
(14, 15, 13, 'Newton', 'ধন্যবাদ', '2026-10-31 02:27:12');

-- --------------------------------------------------------

--
-- Table structure for table `news_likes`
--

DROP TABLE IF EXISTS `news_likes`;
CREATE TABLE IF NOT EXISTS `news_likes` (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `news_id` int NOT NULL,
  `visitor_hash` char(64) NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_news_visitor_like` (`news_id`,`visitor_hash`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `news_likes`
--

INSERT INTO `news_likes` (`id`, `news_id`, `visitor_hash`, `created_at`) VALUES
(5, 6, '48cb8aaf56743d31a5c2cd4abb0741c9aab64f8fd5cea9e9198ab2ecbe5ae7b6', '2026-10-30 09:43:38'),
(6, 7, '4dbf148fecaf2c009d99c7ae27db2c0cf1598a3a8149dba50f195714e560b2af', '2026-10-30 16:07:12'),
(7, 15, '4dbf148fecaf2c009d99c7ae27db2c0cf1598a3a8149dba50f195714e560b2af', '2026-10-31 02:26:01');

-- --------------------------------------------------------

--
-- Table structure for table `settings`
--

DROP TABLE IF EXISTS `settings`;
CREATE TABLE IF NOT EXISTS `settings` (
  `id` int NOT NULL AUTO_INCREMENT,
  `setting_key` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `setting_value` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `setting_key` (`setting_key`)
) ENGINE=InnoDB AUTO_INCREMENT=229 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `settings`
--

INSERT INTO `settings` (`id`, `setting_key`, `setting_value`, `created_at`, `updated_at`) VALUES
(1, 'site_name', 'Science World', '2026-10-17 13:38:02', '2026-10-29 06:55:16'),
(2, 'site_url', 'http://localhost/science-world/', '2026-10-17 13:38:02', '2026-10-29 06:55:16'),
(3, 'site_email', 'scienceandtechnologylab24@gmail.com', '2026-10-17 13:38:02', '2026-10-25 05:44:24'),
(4, 'site_phone', '+8801321358921', '2026-10-17 13:38:02', '2026-10-25 05:44:24'),
(5, 'site_address', 'Alekanda, Barishal Polytechnic Institute College Road, Barishal, Bangladesh', '2026-10-17 13:38:02', '2026-10-30 02:18:54'),
(6, 'site_description', 'বাংলাদেশের শীর্ষ অনলাইন নিউজ পোর্টাল', '2026-10-17 13:38:02', '2026-10-17 13:38:02'),
(7, 'site_keywords', 'Science, Philosophy, Religion, Islam, Space, Love, Sex, Quantum Mechanics', '2026-10-17 13:38:02', '2026-10-30 02:17:33'),
(8, 'facebook_url', 'https://facebook.com/bangladeshprotidin', '2026-10-17 13:38:02', '2026-10-17 13:38:02'),
(9, 'twitter_url', 'https://twitter.com/bdprotidin', '2026-10-17 13:38:02', '2026-10-17 13:38:02'),
(10, 'youtube_url', 'https://youtube.com/bangladeshprotidin', '2026-10-17 13:38:02', '2026-10-17 13:38:02'),
(11, 'instagram_url', 'https://instagram.com/bangladeshprotidin', '2026-10-17 13:38:02', '2026-10-17 13:38:02'),
(12, 'news_per_page', '20', '2026-10-17 13:38:02', '2026-10-17 13:38:02'),
(13, 'breaking_news_enabled', '1', '2026-10-17 13:38:02', '2026-10-17 13:38:02'),
(14, 'comments_enabled', '1', '2026-10-17 13:38:02', '2026-10-17 13:38:02'),
(15, 'maintenance_mode', '0', '2026-10-17 13:38:02', '2026-10-23 05:29:14'),
(16, 'contact_email', 'scienceandtechnologylab24@gmail.com', '2026-10-17 13:38:02', '2026-10-30 02:17:33'),
(17, 'advertisement_email', 'smatiqurrahmanlimon@gmail.com', '2026-10-17 13:38:02', '2026-10-30 02:17:33'),
(18, 'seo_title', 'বিজ্ঞান জগৎ - সবচেয়ে দ্রুত খবর', '2026-10-17 13:38:02', '2026-10-29 12:13:22'),
(19, 'seo_description', 'বাংলাদেশের সবচেয়ে দ্রুততম ও নির্ভরযোগ্য নিউজ পোর্টাল', '2026-10-17 13:38:02', '2026-10-17 13:38:02'),
(20, 'site_theme', 'system', '2026-10-21 11:58:32', '2026-10-30 02:19:20'),
(21, 'ad_mode', 'banner', '2026-10-29 05:08:41', '2026-10-29 14:55:27'),
(22, 'ad_banner_image', '', '2026-10-29 05:08:41', '2026-10-29 05:42:33'),
(23, 'ad_banner_url', 'https://www.arogga.com/product/44663/panther-condom-3-s-pack', '2026-10-29 05:08:41', '2026-10-30 04:22:29'),
(24, 'ad_banner_alt', 'condom', '2026-10-29 05:08:41', '2026-10-29 15:03:50'),
(25, 'adsense_client_id', '', '2026-10-29 05:08:41', '2026-10-29 05:16:06'),
(26, 'adsense_slot_id', '', '2026-10-29 05:08:41', '2026-10-29 05:16:06'),
(43, 'ad_description', 'Safe love, healthy life; let condoms be our lifelong shield.', '2026-10-29 05:23:36', '2026-10-29 15:03:00'),
(74, 'ad_banner_image_1', 'ad_287c7b8903322473972d9c91815ab4cc.jpg', '2026-10-29 05:38:40', '2026-10-30 08:01:43'),
(75, 'ad_banner_image_2', 'ad_5012960a3007739e891259225c978be2.jpg', '2026-10-29 05:38:40', '2026-10-30 08:00:11'),
(76, 'ad_banner_image_3', 'ad_1926618253a767136c255dffcad33905.jpg', '2026-10-29 05:38:40', '2026-10-30 04:07:09');

-- --------------------------------------------------------

--
-- Table structure for table `subscribers`
--

DROP TABLE IF EXISTS `subscribers`;
CREATE TABLE IF NOT EXISTS `subscribers` (
  `id` int NOT NULL AUTO_INCREMENT,
  `email` varchar(100) NOT NULL,
  `name` varchar(100) DEFAULT NULL,
  `subscription_date` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `is_active` tinyint(1) DEFAULT '1',
  `unsubscribe_token` varchar(32) DEFAULT NULL,
  `confirmed` tinyint(1) DEFAULT '0',
  `confirmation_token` varchar(32) DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `subscribers`
--

INSERT INTO `subscribers` (`id`, `email`, `name`, `subscription_date`, `is_active`, `unsubscribe_token`, `confirmed`, `confirmation_token`, `ip_address`) VALUES
(3, 'atiqrurahamnlimon72596247@gmail.com', 'Newton', '2026-10-28 04:05:47', 1, '6296ed19e171d9177e746a97e424321d', 0, '0277399486744ff0aeaab02b86f828df', '::1');

-- --------------------------------------------------------

--
-- Table structure for table `system_logs`
--

DROP TABLE IF EXISTS `system_logs`;
CREATE TABLE IF NOT EXISTS `system_logs` (
  `id` int NOT NULL AUTO_INCREMENT,
  `log_type` enum('info','warning','error','security','login','news','message') DEFAULT 'info',
  `module` varchar(100) DEFAULT NULL,
  `action` varchar(255) DEFAULT NULL,
  `description` text,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text,
  `admin_id` int DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_log_type` (`log_type`),
  KEY `idx_created_at` (`created_at`),
  KEY `idx_module` (`module`),
  KEY `admin_id` (`admin_id`)
) ENGINE=InnoDB AUTO_INCREMENT=126 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `system_logs`
--

INSERT INTO `system_logs` (`id`, `log_type`, `module`, `action`, `description`, `ip_address`, `user_agent`, `admin_id`, `created_at`) VALUES
(1, 'info', 'system', 'লগস পেজ ভিজিট করা হয়েছে', 'লগ ব্যবস্থাপনা', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/155.0.0.0 Safari/537.36', 3, '2026-10-21 14:07:15'),
(2, 'info', 'system', 'লগস পেজ ভিজিট করা হয়েছে', 'লগ ব্যবস্থাপনা', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/155.0.0.0 Safari/537.36', 3, '2026-10-21 14:07:28'),
(3, 'info', 'system', 'লগস পেজ ভিজিট করা হয়েছে', 'লগ ব্যবস্থাপনা', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/155.0.0.0 Safari/537.36', 3, '2026-10-21 14:07:33'),
(4, 'info', 'system', 'লগস পেজ ভিজিট করা হয়েছে', 'লগ ব্যবস্থাপনা', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/155.0.0.0 Safari/537.36', 3, '2026-10-21 14:07:35'),
(5, 'info', 'system', 'লগস ক্লিয়ার করা হয়েছে', 'লগ ব্যবস্থাপনা', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/155.0.0.0 Safari/537.36', 3, '2026-10-21 14:07:58'),
(6, 'info', 'system', 'লগস পেজ ভিজিট করা হয়েছে', 'লগ ব্যবস্থাপনা', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/155.0.0.0 Safari/537.36', 3, '2026-10-21 14:07:58'),
(7, 'info', 'system', 'লগস ক্লিয়ার করা হয়েছে', 'লগ ব্যবস্থাপনা', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/155.0.0.0 Safari/537.36', 3, '2026-10-21 14:08:01'),
(8, 'info', 'system', 'লগস পেজ ভিজিট করা হয়েছে', 'লগ ব্যবস্থাপনা', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/155.0.0.0 Safari/537.36', 3, '2026-10-21 14:08:01'),
(9, 'info', 'system', 'লগস পেজ ভিজিট করা হয়েছে', 'লগ ব্যবস্থাপনা', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/155.0.0.0 Safari/537.36', 3, '2026-10-21 14:08:58'),
(10, 'info', 'system', 'লগস পেজ ভিজিট করা হয়েছে', 'লগ ব্যবস্থাপনা', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/155.0.0.0 Safari/537.36', 3, '2026-10-21 14:09:17'),
(11, 'info', 'system', 'লগস পেজ ভিজিট করা হয়েছে', 'লগ ব্যবস্থাপনা', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/155.0.0.0 Safari/537.36', 3, '2026-10-21 14:11:13'),
(12, 'info', 'system', 'ডকুমেন্টেশন পেজ ভিজিট', 'অ্যাডমিন ডকুমেন্টেশন পেজ ভিজিট করা হয়েছে', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/155.0.0.0 Safari/537.36', 3, '2026-10-21 14:16:29'),
(13, 'info', 'system', 'PHPInfo পেজ ভিজিট', 'PHP ইনফরমেশন পেজ ভিজিট করা হয়েছে', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/155.0.0.0 Safari/537.36', 3, '2026-10-21 14:16:37'),
(14, 'info', 'system', 'লগস পেজ ভিজিট করা হয়েছে', 'লগ ব্যবস্থাপনা', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/155.0.0.0 Safari/537.36', 3, '2026-10-21 14:16:46'),
(15, 'info', 'system', 'লগস পেজ ভিজিট করা হয়েছে', 'লগ ব্যবস্থাপনা', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/155.0.0.0 Safari/537.36', 3, '2026-10-21 14:16:56'),
(16, 'info', 'system', 'লগস পেজ ভিজিট করা হয়েছে', 'লগ ব্যবস্থাপনা', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/155.0.0.0 Safari/537.36', 3, '2026-10-22 04:14:48'),
(17, 'info', 'system', 'লগস পেজ ভিজিট করা হয়েছে', 'লগ ব্যবস্থাপনা', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/155.0.0.0 Safari/537.36', 3, '2026-10-23 05:25:01'),
(18, 'info', 'system', 'লগস পেজ ভিজিট করা হয়েছে', 'লগ ব্যবস্থাপনা', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/155.0.0.0 Safari/537.36', 3, '2026-10-23 05:27:13'),
(19, 'info', 'system', 'PHPInfo পেজ ভিজিট', 'PHP ইনফরমেশন পেজ ভিজিট করা হয়েছে', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/155.0.0.0 Safari/537.36', 3, '2026-10-23 05:28:25'),
(20, 'info', 'system', 'ডকুমেন্টেশন পেজ ভিজিট', 'অ্যাডমিন ডকুমেন্টেশন পেজ ভিজিট করা হয়েছে', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/155.0.0.0 Safari/537.36', 3, '2026-10-23 05:28:30'),
(21, 'info', 'system', 'PHPInfo পেজ ভিজিট', 'PHP ইনফরমেশন পেজ ভিজিট করা হয়েছে', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/155.0.0.0 Safari/537.36', 3, '2026-10-25 05:45:00'),
(22, 'info', 'system', 'লগস পেজ ভিজিট করা হয়েছে', 'লগ ব্যবস্থাপনা', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/155.0.0.0 Safari/537.36', 3, '2026-10-25 05:46:09'),
(23, 'info', 'system', 'লগস পেজ ভিজিট করা হয়েছে', 'লগ ব্যবস্থাপনা', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/156.0.0.0 Safari/537.36', 3, '2026-10-28 04:23:29'),
(24, 'info', 'system', 'লগস পেজ ভিজিট করা হয়েছে', 'লগ ব্যবস্থাপনা', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/156.0.0.0 Safari/537.36', 8, '2026-10-28 05:23:32'),
(25, 'info', 'system', 'লগস পেজ ভিজিট করা হয়েছে', 'লগ ব্যবস্থাপনা', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/156.0.0.0 Safari/537.36', 8, '2026-10-28 05:23:54'),
(26, 'info', 'system', 'ডকুমেন্টেশন পেজ ভিজিট', 'অ্যাডমিন ডকুমেন্টেশন পেজ ভিজিট করা হয়েছে', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/156.0.0.0 Safari/537.36', 8, '2026-10-28 05:23:56'),
(27, 'info', 'system', 'লগস পেজ ভিজিট করা হয়েছে', 'লগ ব্যবস্থাপনা', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/156.0.0.0 Safari/537.36', 8, '2026-10-28 05:24:04'),
(28, 'info', 'system', 'লগস পেজ ভিজিট করা হয়েছে', 'লগ ব্যবস্থাপনা', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/156.0.0.0 Safari/537.36', 8, '2026-10-28 05:24:09'),
(29, 'info', 'system', 'লগস পেজ ভিজিট করা হয়েছে', 'লগ ব্যবস্থাপনা', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/156.0.0.0 Safari/537.36', 8, '2026-10-28 05:24:13'),
(30, 'info', 'system', 'লগস পেজ ভিজিট করা হয়েছে', 'লগ ব্যবস্থাপনা', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/156.0.0.0 Safari/537.36', 8, '2026-10-28 05:24:16'),
(31, 'info', 'system', 'লগস পেজ ভিজিট করা হয়েছে', 'লগ ব্যবস্থাপনা', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/156.0.0.0 Safari/537.36', 3, '2026-10-28 05:29:23'),
(32, 'info', 'system', 'PHPInfo পেজ ভিজিট', 'PHP ইনফরমেশন পেজ ভিজিট করা হয়েছে', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/156.0.0.0 Safari/537.36', 3, '2026-10-28 05:29:41'),
(33, 'info', 'system', 'ডকুমেন্টেশন পেজ ভিজিট', 'অ্যাডমিন ডকুমেন্টেশন পেজ ভিজিট করা হয়েছে', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/156.0.0.0 Safari/537.36', 3, '2026-10-28 05:29:44'),
(34, 'info', 'system', 'লগস পেজ ভিজিট করা হয়েছে', 'লগ ব্যবস্থাপনা', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/156.0.0.0 Safari/537.36', 8, '2026-10-28 05:41:34'),
(35, 'info', 'system', 'লগস পেজ ভিজিট করা হয়েছে', 'লগ ব্যবস্থাপনা', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/156.0.0.0 Safari/537.36', 8, '2026-10-28 05:41:49'),
(36, 'info', 'system', 'লগস পেজ ভিজিট করা হয়েছে', 'লগ ব্যবস্থাপনা', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/156.0.0.0 Safari/537.36', 8, '2026-10-28 05:41:54'),
(37, 'info', 'system', 'লগস পেজ ভিজিট করা হয়েছে', 'লগ ব্যবস্থাপনা', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/156.0.0.0 Safari/537.36', 8, '2026-10-28 05:57:09'),
(38, 'info', 'system', 'লগস পেজ ভিজিট করা হয়েছে', 'লগ ব্যবস্থাপনা', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/156.0.0.0 Safari/537.36', 8, '2026-10-28 05:57:21'),
(39, 'info', 'system', 'লগস পেজ ভিজিট করা হয়েছে', 'লগ ব্যবস্থাপনা', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/156.0.0.0 Safari/537.36', 3, '2026-10-28 05:58:18'),
(40, 'info', 'system', 'PHPInfo পেজ ভিজিট', 'PHP ইনফরমেশন পেজ ভিজিট করা হয়েছে', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/156.0.0.0 Safari/537.36', 3, '2026-10-28 05:58:23'),
(41, 'info', 'system', 'ডকুমেন্টেশন পেজ ভিজিট', 'অ্যাডমিন ডকুমেন্টেশন পেজ ভিজিট করা হয়েছে', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/156.0.0.0 Safari/537.36', 3, '2026-10-28 05:58:30'),
(42, 'info', 'system', 'লগস পেজ ভিজিট করা হয়েছে', 'লগ ব্যবস্থাপনা', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/156.0.0.0 Safari/537.36', 8, '2026-10-28 06:22:36'),
(43, 'info', 'system', 'লগস পেজ ভিজিট করা হয়েছে', 'লগ ব্যবস্থাপনা', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/156.0.0.0 Safari/537.36', 8, '2026-10-28 06:22:44'),
(44, 'info', 'system', 'লগস পেজ ভিজিট করা হয়েছে', 'লগ ব্যবস্থাপনা', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/156.0.0.0 Safari/537.36', 3, '2026-10-28 06:32:33'),
(45, 'info', 'system', 'লগস পেজ ভিজিট করা হয়েছে', 'লগ ব্যবস্থাপনা', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/156.0.0.0 Safari/537.36', 3, '2026-10-28 06:39:04'),
(46, 'info', 'system', 'লগস পেজ ভিজিট করা হয়েছে', 'লগ ব্যবস্থাপনা', '::1', 'curl/8.21.0', NULL, '2026-10-28 12:50:03'),
(47, 'info', 'system', 'ডকুমেন্টেশন পেজ ভিজিট', 'অ্যাডমিন ডকুমেন্টেশন পেজ ভিজিট করা হয়েছে', '::1', 'curl/8.21.0', NULL, '2026-10-28 12:50:03'),
(48, 'info', 'system', 'PHPInfo পেজ ভিজিট', 'PHP ইনফরমেশন পেজ ভিজিট করা হয়েছে', '::1', 'curl/8.21.0', NULL, '2026-10-28 12:50:03'),
(49, 'info', 'system', 'লগস পেজ ভিজিট করা হয়েছে', 'লগ ব্যবস্থাপনা', '::1', 'curl/8.21.0', NULL, '2026-10-28 12:50:42'),
(50, 'info', 'system', 'ডকুমেন্টেশন পেজ ভিজিট', 'অ্যাডমিন ডকুমেন্টেশন পেজ ভিজিট করা হয়েছে', '::1', 'curl/8.21.0', NULL, '2026-10-28 12:50:42'),
(51, 'info', 'system', 'PHPInfo পেজ ভিজিট', 'PHP ইনফরমেশন পেজ ভিজিট করা হয়েছে', '::1', 'curl/8.21.0', NULL, '2026-10-28 12:50:42'),
(52, 'info', 'system', 'লগস পেজ ভিজিট করা হয়েছে', 'লগ ব্যবস্থাপনা', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/156.0.0.0 Safari/537.36', 3, '2026-10-28 12:58:36'),
(53, 'info', 'system', 'লগস পেজ ভিজিট করা হয়েছে', 'লগ ব্যবস্থাপনা', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/156.0.0.0 Safari/537.36', 3, '2026-10-28 14:31:15'),
(54, 'info', 'system', 'PHPInfo পেজ ভিজিট', 'PHP ইনফরমেশন পেজ ভিজিট করা হয়েছে', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/156.0.0.0 Safari/537.36', 3, '2026-10-28 14:31:25'),
(55, 'info', 'system', 'ডকুমেন্টেশন পেজ ভিজিট', 'অ্যাডমিন ডকুমেন্টেশন পেজ ভিজিট করা হয়েছে', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/156.0.0.0 Safari/537.36', 3, '2026-10-28 14:31:42'),
(56, 'info', 'system', 'লগস পেজ ভিজিট করা হয়েছে', 'লগ ব্যবস্থাপনা', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/156.0.0.0 Safari/537.36', 3, '2026-10-28 14:37:05'),
(57, 'info', 'system', 'লগস পেজ ভিজিট করা হয়েছে', 'লগ ব্যবস্থাপনা', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/156.0.0.0 Safari/537.36', 3, '2026-10-28 14:37:51'),
(58, 'info', 'system', 'লগস পেজ ভিজিট করা হয়েছে', 'লগ ব্যবস্থাপনা', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/156.0.0.0 Safari/537.36', 3, '2026-10-28 14:43:55'),
(59, 'info', 'system', 'লগস পেজ ভিজিট করা হয়েছে', 'লগ ব্যবস্থাপনা', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/156.0.0.0 Safari/537.36', 3, '2026-10-28 14:56:51'),
(60, 'info', 'system', 'লগস পেজ ভিজিট করা হয়েছে', 'লগ ব্যবস্থাপনা', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/156.0.0.0 Safari/537.36', 3, '2026-10-28 15:01:17'),
(61, 'info', 'system', 'ডকুমেন্টেশন পেজ ভিজিট', 'অ্যাডমিন ডকুমেন্টেশন পেজ ভিজিট করা হয়েছে', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/156.0.0.0 Safari/537.36', 3, '2026-10-28 15:01:24'),
(62, 'info', 'system', 'লগস পেজ ভিজিট করা হয়েছে', 'লগ ব্যবস্থাপনা', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/156.0.0.0 Safari/537.36', 3, '2026-10-28 15:04:57'),
(63, 'info', 'system', 'লগস পেজ ভিজিট করা হয়েছে', 'লগ ব্যবস্থাপনা', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/156.0.0.0 Safari/537.36', 3, '2026-10-28 15:07:44'),
(64, 'info', 'system', 'PHPInfo পেজ ভিজিট', 'PHP ইনফরমেশন পেজ ভিজিট করা হয়েছে', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/156.0.0.0 Safari/537.36', 3, '2026-10-28 15:08:02'),
(65, 'info', 'system', 'PHPInfo পেজ ভিজিট', 'PHP ইনফরমেশন পেজ ভিজিট করা হয়েছে', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/156.0.0.0 Safari/537.36', 3, '2026-10-28 15:10:35'),
(66, 'info', 'system', 'লগস পেজ ভিজিট করা হয়েছে', 'লগ ব্যবস্থাপনা', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/156.0.0.0 Safari/537.36', 3, '2026-10-28 15:11:08'),
(67, 'info', 'system', 'ডকুমেন্টেশন পেজ ভিজিট', 'অ্যাডমিন ডকুমেন্টেশন পেজ ভিজিট করা হয়েছে', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/156.0.0.0 Safari/537.36', 3, '2026-10-28 15:11:16'),
(68, 'info', 'system', 'ডকুমেন্টেশন পেজ ভিজিট', 'অ্যাডমিন ডকুমেন্টেশন পেজ ভিজিট করা হয়েছে', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/156.0.0.0 Safari/537.36', 3, '2026-10-28 15:14:02'),
(69, 'info', 'system', 'ডকুমেন্টেশন পেজ ভিজিট', 'অ্যাডমিন ডকুমেন্টেশন পেজ ভিজিট করা হয়েছে', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/156.0.0.0 Safari/537.36', 3, '2026-10-28 15:17:13'),
(70, 'info', 'system', 'PHPInfo পেজ ভিজিট', 'PHP ইনফরমেশন পেজ ভিজিট করা হয়েছে', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/156.0.0.0 Safari/537.36', 3, '2026-10-28 15:20:35'),
(71, 'info', 'system', 'ডকুমেন্টেশন পেজ ভিজিট', 'অ্যাডমিন ডকুমেন্টেশন পেজ ভিজিট করা হয়েছে', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/156.0.0.0 Safari/537.36', 3, '2026-10-28 15:20:37'),
(72, 'info', 'system', 'লগস পেজ ভিজিট করা হয়েছে', 'লগ ব্যবস্থাপনা', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/156.0.0.0 Safari/537.36', 3, '2026-10-28 15:20:41'),
(73, 'info', 'system', 'লগস পেজ ভিজিট করা হয়েছে', 'লগ ব্যবস্থাপনা', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/156.0.0.0 Safari/537.36', 3, '2026-10-28 15:25:13'),
(74, 'info', 'system', 'PHPInfo পেজ ভিজিট', 'PHP ইনফরমেশন পেজ ভিজিট করা হয়েছে', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/156.0.0.0 Safari/537.36', 3, '2026-10-28 15:25:15'),
(75, 'info', 'system', 'PHPInfo পেজ ভিজিট', 'PHP ইনফরমেশন পেজ ভিজিট করা হয়েছে', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/156.0.0.0 Safari/537.36', 3, '2026-10-28 15:25:34'),
(76, 'info', 'system', 'ডকুমেন্টেশন পেজ ভিজিট', 'অ্যাডমিন ডকুমেন্টেশন পেজ ভিজিট করা হয়েছে', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/156.0.0.0 Safari/537.36', 3, '2026-10-28 15:25:42'),
(77, 'info', 'system', 'লগস পেজ ভিজিট করা হয়েছে', 'লগ ব্যবস্থাপনা', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/156.0.0.0 Safari/537.36', 3, '2026-10-28 15:25:49'),
(78, 'info', 'system', 'ডকুমেন্টেশন পেজ ভিজিট', 'অ্যাডমিন ডকুমেন্টেশন পেজ ভিজিট করা হয়েছে', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/156.0.0.0 Safari/537.36', 3, '2026-10-28 15:26:03'),
(79, 'info', 'system', 'PHPInfo পেজ ভিজিট', 'PHP ইনফরমেশন পেজ ভিজিট করা হয়েছে', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/156.0.0.0 Safari/537.36', 3, '2026-10-28 15:26:06'),
(80, 'info', 'system', 'ডকুমেন্টেশন পেজ ভিজিট', 'অ্যাডমিন ডকুমেন্টেশন পেজ ভিজিট করা হয়েছে', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/156.0.0.0 Safari/537.36', 3, '2026-10-28 15:26:08'),
(81, 'info', 'system', 'লগস পেজ ভিজিট করা হয়েছে', 'লগ ব্যবস্থাপনা', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/156.0.0.0 Safari/537.36', 3, '2026-10-28 15:26:10'),
(82, 'info', 'system', 'ডকুমেন্টেশন পেজ ভিজিট', 'অ্যাডমিন ডকুমেন্টেশন পেজ ভিজিট করা হয়েছে', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/156.0.0.0 Safari/537.36', 3, '2026-10-28 15:26:12'),
(83, 'info', 'system', 'ডকুমেন্টেশন পেজ ভিজিট', 'অ্যাডমিন ডকুমেন্টেশন পেজ ভিজিট করা হয়েছে', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/156.0.0.0 Safari/537.36', 3, '2026-10-28 15:26:15'),
(84, 'info', 'system', 'ডকুমেন্টেশন পেজ ভিজিট', 'অ্যাডমিন ডকুমেন্টেশন পেজ ভিজিট করা হয়েছে', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/156.0.0.0 Safari/537.36', 3, '2026-10-28 15:26:20'),
(85, 'info', 'system', 'ডকুমেন্টেশন পেজ ভিজিট', 'অ্যাডমিন ডকুমেন্টেশন পেজ ভিজিট করা হয়েছে', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/156.0.0.0 Safari/537.36', 3, '2026-10-28 15:26:25'),
(86, 'info', 'system', 'ডকুমেন্টেশন পেজ ভিজিট', 'অ্যাডমিন ডকুমেন্টেশন পেজ ভিজিট করা হয়েছে', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/156.0.0.0 Safari/537.36', 3, '2026-10-28 15:26:29'),
(87, 'info', 'system', 'ডকুমেন্টেশন পেজ ভিজিট', 'অ্যাডমিন ডকুমেন্টেশন পেজ ভিজিট করা হয়েছে', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/156.0.0.0 Safari/537.36', 3, '2026-10-28 15:26:33'),
(88, 'info', 'system', 'ডকুমেন্টেশন পেজ ভিজিট', 'অ্যাডমিন ডকুমেন্টেশন পেজ ভিজিট করা হয়েছে', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/156.0.0.0 Safari/537.36', 3, '2026-10-28 15:28:50'),
(89, 'info', 'system', 'ডকুমেন্টেশন পেজ ভিজিট', 'অ্যাডমিন ডকুমেন্টেশন পেজ ভিজিট করা হয়েছে', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/156.0.0.0 Safari/537.36', 3, '2026-10-28 15:28:56'),
(90, 'info', 'system', 'ডকুমেন্টেশন পেজ ভিজিট', 'অ্যাডমিন ডকুমেন্টেশন পেজ ভিজিট করা হয়েছে', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/156.0.0.0 Safari/537.36', 3, '2026-10-28 15:29:02'),
(91, 'info', 'system', 'PHPInfo পেজ ভিজিট', 'PHP ইনফরমেশন পেজ ভিজিট করা হয়েছে', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/156.0.0.0 Safari/537.36', 3, '2026-10-28 15:32:05'),
(92, 'info', 'system', 'লগস পেজ ভিজিট করা হয়েছে', 'লগ ব্যবস্থাপনা', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/156.0.0.0 Safari/537.36', 3, '2026-10-28 15:34:14'),
(93, 'info', 'system', 'ডকুমেন্টেশন পেজ ভিজিট', 'অ্যাডমিন ডকুমেন্টেশন পেজ ভিজিট করা হয়েছে', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/156.0.0.0 Safari/537.36', 3, '2026-10-28 15:45:38'),
(94, 'info', 'system', 'PHPInfo পেজ ভিজিট', 'PHP ইনফরমেশন পেজ ভিজিট করা হয়েছে', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/156.0.0.0 Safari/537.36', 3, '2026-10-28 15:45:41'),
(95, 'info', 'system', 'ডকুমেন্টেশন পেজ ভিজিট', 'অ্যাডমিন ডকুমেন্টেশন পেজ ভিজিট করা হয়েছে', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/156.0.0.0 Safari/537.36', 3, '2026-10-28 15:45:43'),
(96, 'info', 'system', 'লগস পেজ ভিজিট করা হয়েছে', 'লগ ব্যবস্থাপনা', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/156.0.0.0 Safari/537.36', 3, '2026-10-28 15:45:46'),
(97, 'info', 'system', 'লগস পেজ ভিজিট করা হয়েছে', 'লগ ব্যবস্থাপনা', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/156.0.0.0 Safari/537.36', 3, '2026-10-29 06:32:29'),
(98, 'info', 'system', 'লগস পেজ ভিজিট করা হয়েছে', 'লগ ব্যবস্থাপনা', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/156.0.0.0 Safari/537.36', 3, '2026-10-29 06:32:34'),
(99, 'info', 'system', 'PHPInfo পেজ ভিজিট', 'PHP ইনফরমেশন পেজ ভিজিট করা হয়েছে', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/156.0.0.0 Safari/537.36', 3, '2026-10-29 06:32:41'),
(100, 'info', 'system', 'লগস পেজ ভিজিট করা হয়েছে', 'লগ ব্যবস্থাপনা', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/156.0.0.0 Safari/537.36', 3, '2026-10-29 06:33:56'),
(101, 'info', 'system', 'লগস পেজ ভিজিট করা হয়েছে', 'লগ ব্যবস্থাপনা', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/156.0.0.0 Safari/537.36', 3, '2026-10-29 06:33:58'),
(102, 'info', 'system', 'লগস পেজ ভিজিট করা হয়েছে', 'লগ ব্যবস্থাপনা', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/156.0.0.0 Safari/537.36', 3, '2026-10-29 06:33:59'),
(103, 'info', 'system', 'লগস পেজ ভিজিট করা হয়েছে', 'লগ ব্যবস্থাপনা', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/156.0.0.0 Safari/537.36', 3, '2026-10-29 06:34:01'),
(104, 'info', 'system', 'লগস পেজ ভিজিট করা হয়েছে', 'লগ ব্যবস্থাপনা', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/156.0.0.0 Safari/537.36', 3, '2026-10-29 06:34:03'),
(105, 'info', 'system', 'ডকুমেন্টেশন পেজ ভিজিট', 'অ্যাডমিন ডকুমেন্টেশন পেজ ভিজিট করা হয়েছে', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/156.0.0.0 Safari/537.36', 3, '2026-10-29 06:34:30'),
(106, 'info', 'system', 'লগস পেজ ভিজিট করা হয়েছে', 'লগ ব্যবস্থাপনা', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Code/1.143.1 Chrome/150.0.7871.250 Electron/43.7.7 Safari/537.36', 3, '2026-10-29 06:37:12'),
(107, 'info', 'system', 'ডকুমেন্টেশন পেজ ভিজিট', 'অ্যাডমিন ডকুমেন্টেশন পেজ ভিজিট করা হয়েছে', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Code/1.143.1 Chrome/150.0.7871.250 Electron/43.7.7 Safari/537.36', 3, '2026-10-29 06:37:13'),
(108, 'info', 'system', 'লগস পেজ ভিজিট করা হয়েছে', 'লগ ব্যবস্থাপনা', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Code/1.143.1 Chrome/150.0.7871.250 Electron/43.7.7 Safari/537.36', 3, '2026-10-29 06:37:58'),
(109, 'info', 'system', 'ডকুমেন্টেশন পেজ ভিজিট', 'অ্যাডমিন ডকুমেন্টেশন পেজ ভিজিট করা হয়েছে', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Code/1.143.1 Chrome/150.0.7871.250 Electron/43.7.7 Safari/537.36', 3, '2026-10-29 06:37:58'),
(110, 'info', 'system', 'ডকুমেন্টেশন পেজ ভিজিট', 'অ্যাডমিন ডকুমেন্টেশন পেজ ভিজিট করা হয়েছে', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Code/1.143.1 Chrome/150.0.7871.250 Electron/43.7.7 Safari/537.36', 3, '2026-10-29 06:38:56'),
(111, 'info', 'system', 'ডকুমেন্টেশন পেজ ভিজিট', 'অ্যাডমিন ডকুমেন্টেশন পেজ ভিজিট করা হয়েছে', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Code/1.143.1 Chrome/150.0.7871.250 Electron/43.7.7 Safari/537.36', 3, '2026-10-29 06:39:51'),
(112, 'info', 'system', 'ডকুমেন্টেশন পেজ ভিজিট', 'অ্যাডমিন ডকুমেন্টেশন পেজ ভিজিট করা হয়েছে', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Code/1.143.1 Chrome/150.0.7871.250 Electron/43.7.7 Safari/537.36', 3, '2026-10-29 06:42:41'),
(113, 'info', 'system', 'লগস পেজ ভিজিট করা হয়েছে', 'লগ ব্যবস্থাপনা', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/156.0.0.0 Safari/537.36', 3, '2026-10-29 06:45:27'),
(114, 'info', 'system', 'PHPInfo পেজ ভিজিট', 'PHP ইনফরমেশন পেজ ভিজিট করা হয়েছে', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/156.0.0.0 Safari/537.36', 3, '2026-10-29 06:45:34'),
(115, 'info', 'system', 'ডকুমেন্টেশন পেজ ভিজিট', 'অ্যাডমিন ডকুমেন্টেশন পেজ ভিজিট করা হয়েছে', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/156.0.0.0 Safari/537.36', 3, '2026-10-29 06:45:46'),
(116, 'info', 'system', 'ডকুমেন্টেশন পেজ ভিজিট', 'অ্যাডমিন ডকুমেন্টেশন পেজ ভিজিট করা হয়েছে', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/156.0.0.0 Safari/537.36', 8, '2026-10-29 06:51:07'),
(117, 'info', 'system', 'PHPInfo পেজ ভিজিট', 'PHP ইনফরমেশন পেজ ভিজিট করা হয়েছে', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/156.0.0.0 Safari/537.36', 3, '2026-10-29 12:18:19'),
(118, 'info', 'system', 'ডকুমেন্টেশন পেজ ভিজিট', 'অ্যাডমিন ডকুমেন্টেশন পেজ ভিজিট করা হয়েছে', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/156.0.0.0 Safari/537.36', 3, '2026-10-29 13:03:03'),
(119, 'info', 'system', 'লগস পেজ ভিজিট করা হয়েছে', 'লগ ব্যবস্থাপনা', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/156.0.0.0 Safari/537.36', 3, '2026-10-29 13:03:10'),
(120, 'info', 'system', 'ডকুমেন্টেশন পেজ ভিজিট', 'অ্যাডমিন ডকুমেন্টেশন পেজ ভিজিট করা হয়েছে', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/156.0.0.0 Safari/537.36', 3, '2026-10-29 13:37:20'),
(121, 'info', 'system', 'লগস পেজ ভিজিট করা হয়েছে', 'লগ ব্যবস্থাপনা', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/156.0.0.0 Safari/537.36', 3, '2026-10-29 13:38:32'),
(122, 'info', 'system', 'লগস পেজ ভিজিট করা হয়েছে', 'লগ ব্যবস্থাপনা', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/156.0.0.0 Safari/537.36', 3, '2026-10-29 13:38:43'),
(123, 'info', 'system', 'লগস পেজ ভিজিট করা হয়েছে', 'লগ ব্যবস্থাপনা', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/156.0.0.0 Safari/537.36', 3, '2026-10-29 13:39:12'),
(124, 'info', 'system', 'লগস পেজ ভিজিট করা হয়েছে', 'লগ ব্যবস্থাপনা', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/156.0.0.0 Safari/537.36', 3, '2026-10-29 13:39:26'),
(125, 'info', 'system', 'ডকুমেন্টেশন পেজ ভিজিট', 'অ্যাডমিন ডকুমেন্টেশন পেজ ভিজিট করা হয়েছে', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/156.0.0.0 Safari/537.36', 3, '2026-10-30 09:51:00');

--
-- Constraints for dumped tables
--

--
-- Constraints for table `news`
--
ALTER TABLE `news`
  ADD CONSTRAINT `news_ibfk_1` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `news_comments`
--
ALTER TABLE `news_comments`
  ADD CONSTRAINT `fk_news_comments_news` FOREIGN KEY (`news_id`) REFERENCES `news` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_news_comments_parent` FOREIGN KEY (`parent_id`) REFERENCES `news_comments` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `news_likes`
--
ALTER TABLE `news_likes`
  ADD CONSTRAINT `fk_news_likes_news` FOREIGN KEY (`news_id`) REFERENCES `news` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `system_logs`
--
ALTER TABLE `system_logs`
  ADD CONSTRAINT `system_logs_ibfk_1` FOREIGN KEY (`admin_id`) REFERENCES `admins` (`id`) ON DELETE SET NULL;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
