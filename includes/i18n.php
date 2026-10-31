<?php

require_once __DIR__ . '/../config.php';

function site_language(): string
{
    static $resolvedLanguage = null;
    if ($resolvedLanguage !== null) {
        return $resolvedLanguage;
    }

    $requestedLanguage = $_GET['lang'] ?? null;
    if (is_string($requestedLanguage) && in_array($requestedLanguage, ['en', 'bn'], true)) {
        $_SESSION['site_language'] = $requestedLanguage;
        $cookiePath = parse_url(SITE_URL, PHP_URL_PATH) ?: '/';
        setcookie('site_language', $requestedLanguage, [
            'expires' => time() + (365 * 24 * 60 * 60),
            'path' => $cookiePath,
            'samesite' => 'Lax'
        ]);
        $_COOKIE['site_language'] = $requestedLanguage;
        $resolvedLanguage = $requestedLanguage;
        return $resolvedLanguage;
    }

    $savedLanguage = $_COOKIE['site_language'] ?? $_SESSION['site_language'] ?? 'en';
    $resolvedLanguage = is_string($savedLanguage) && in_array($savedLanguage, ['en', 'bn'], true)
        ? $savedLanguage
        : 'en';
    return $resolvedLanguage;
}

function translate(string $bengaliText): string
{
    static $translations = null;
    if (site_language() === 'bn') {
        return $bengaliText;
    }

    if ($translations === null) {
        $translations = [];
        foreach (['public', 'admin'] as $catalog) {
            $catalogPath = __DIR__ . '/lang/' . $catalog . '.php';
            if (is_file($catalogPath)) {
                $catalogTranslations = require $catalogPath;
                if (is_array($catalogTranslations)) {
                    $translations = array_merge($translations, $catalogTranslations);
                }
            }
        }
    }

    return $translations[$bengaliText] ?? $bengaliText;
}

function language_switcher(string $className = ''): string
{
    $currentLanguage = site_language();
    $query = $_GET;
    unset($query['lang']);
    $baseUrl = strtok($_SERVER['REQUEST_URI'] ?? SITE_URL, '?');
    $separator = strpos($baseUrl, '?') === false ? '?' : '&';
    $classes = trim('site-language-switcher ' . $className);
    $englishQuery = http_build_query(array_merge($query, ['lang' => 'en']));
    $bengaliQuery = http_build_query(array_merge($query, ['lang' => 'bn']));

    return '<div class="' . htmlspecialchars($classes, ENT_QUOTES, 'UTF-8') . '" role="group" aria-label="Language">'
        . '<a href="' . htmlspecialchars($baseUrl . $separator . $englishQuery, ENT_QUOTES, 'UTF-8') . '"'
        . ($currentLanguage === 'en' ? ' aria-current="true"' : '') . '>English</a>'
        . '<span aria-hidden="true"> | </span>'
        . '<a href="' . htmlspecialchars($baseUrl . $separator . $bengaliQuery, ENT_QUOTES, 'UTF-8') . '"'
        . ($currentLanguage === 'bn' ? ' aria-current="true"' : '') . '>বাংলা</a>'
        . '</div>';
}

function language_toggle(): string
{
    $currentLanguage = site_language();
    $targetLanguage = $currentLanguage === 'en' ? 'bn' : 'en';
    $query = $_GET;
    unset($query['lang']);
    $requestUri = $_SERVER['REQUEST_URI'] ?? SITE_URL;
    $path = parse_url($requestUri, PHP_URL_PATH) ?: SITE_URL;
    $queryString = http_build_query(array_merge($query, ['lang' => $targetLanguage]));
    $href = $path . ($queryString !== '' ? '?' . $queryString : '');
    $label = $targetLanguage === 'bn' ? 'বাংলা ভাষায় পরিবর্তন করুন' : 'ইংরেজিতে পরিবর্তন করুন';
    $visibleLabel = $currentLanguage === 'en' ? 'English' : 'বাংলা';

    return '<a class="language-toggle me-2" href="' . htmlspecialchars($href, ENT_QUOTES, 'UTF-8') . '"'
        . ' aria-label="' . htmlspecialchars(translate($label), ENT_QUOTES, 'UTF-8') . '"'
        . ' title="' . htmlspecialchars(translate($label), ENT_QUOTES, 'UTF-8') . '">'
        . '<i class="fas fa-globe" aria-hidden="true"></i>'
        . '<span>' . htmlspecialchars($visibleLabel, ENT_QUOTES, 'UTF-8') . '</span>'
        . '</a>';
}

function localized_news_value(array $news, string $field): string
{
    $englishField = $field . '_en';
    if (site_language() === 'en'
        && isset($news[$englishField])
        && is_string($news[$englishField])
        && trim($news[$englishField]) !== '') {
        return $news[$englishField];
    }

    return isset($news[$field]) && is_string($news[$field]) ? $news[$field] : '';
}

function localized_news_excerpt(array $news): string
{
    $englishExcerpt = $news['excerpt_en'] ?? null;
    if (site_language() === 'en'
        && is_string($englishExcerpt)
        && trim($englishExcerpt) !== '') {
        return $englishExcerpt;
    }

    $englishContent = $news['content_en'] ?? null;
    if (site_language() === 'en'
        && is_string($englishContent)
        && trim($englishContent) !== '') {
        $contentWithSpacing = preg_replace(
            '/<(?:br|\/p|\/div|\/li|\/h[1-6])\b[^>]*>/i',
            ' ',
            $englishContent
        ) ?? $englishContent;
        $plainText = html_entity_decode(
            strip_tags($contentWithSpacing),
            ENT_QUOTES | ENT_HTML5,
            'UTF-8'
        );
        return trim(preg_replace('/\s+/u', ' ', $plainText) ?? $plainText);
    }

    return localized_news_value($news, 'excerpt');
}

function truncate_text(string $text, int $limit, string $suffix = '...'): string
{
    if ($limit < 0) {
        return $text;
    }

    if (function_exists('mb_strlen') && function_exists('mb_substr')) {
        if (mb_strlen($text, 'UTF-8') <= $limit) {
            return $text;
        }

        return mb_substr($text, 0, $limit, 'UTF-8') . $suffix;
    }

    $characters = preg_split('//u', $text, -1, PREG_SPLIT_NO_EMPTY);
    if ($characters !== false) {
        if (count($characters) <= $limit) {
            return $text;
        }

        return implode('', array_slice($characters, 0, $limit)) . $suffix;
    }

    return strlen($text) > $limit ? substr($text, 0, $limit) . $suffix : $text;
}

function localized_category_name(string $name, ?string $englishName = null): string
{
    if (site_language() === 'en' && is_string($englishName) && trim($englishName) !== '') {
        return $englishName;
    }

    return translate($name);
}
