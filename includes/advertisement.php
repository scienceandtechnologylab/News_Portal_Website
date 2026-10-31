<?php

function render_advertisement(): void
{
    static $settings = null;

    if ($settings === null) {
        $settings = [];
        $result = $GLOBALS['conn']->query(
            "SELECT setting_key, setting_value FROM settings
             WHERE setting_key IN (
                 'ad_mode', 'ad_banner_image', 'ad_banner_url',
                 'ad_banner_alt', 'ad_description',
                 'ad_banner_image_1', 'ad_banner_image_2', 'ad_banner_image_3',
                 'adsense_client_id', 'adsense_slot_id'
             )"
        );

        if (!$result) {
            error_log('Unable to load advertisement settings: ' . $GLOBALS['conn']->error);
            return;
        }

        while ($row = $result->fetch_assoc()) {
            $settings[$row['setting_key']] = $row['setting_value'];
        }
    }

    $description = trim($settings['ad_description'] ?? '');
    $descriptionMarkup = $description === ''
        ? ''
        : '<p class="advertisement-description text-muted small mb-0 mt-2">'
            . nl2br(htmlspecialchars($description, ENT_QUOTES, 'UTF-8'))
            . '</p>';

    if (($settings['ad_mode'] ?? 'off') === 'banner') {
        $bannerUrl = $settings['ad_banner_url'] ?? '';
        $urlParts = is_string($bannerUrl) ? parse_url($bannerUrl) : false;
        $imageFilenames = [];
        for ($index = 1; $index <= 3; $index++) {
            $filename = $settings['ad_banner_image_' . $index]
                ?? ($index === 1 ? ($settings['ad_banner_image'] ?? '') : '');
            $bannerPath = __DIR__ . '/../assets/images/advertisements/' . $filename;
            if (preg_match('/\Aad_[a-f0-9]{32}\.jpg\z/', $filename) && is_file($bannerPath)) {
                $imageFilenames[] = $filename;
            }
        }

        if (count($imageFilenames) > 0
            && filter_var($bannerUrl, FILTER_VALIDATE_URL)
            && is_array($urlParts)
            && in_array(strtolower($urlParts['scheme'] ?? ''), ['http', 'https'], true)) {
            echo '<div class="advertisement-slot text-center my-4">'
                . '<span class="d-block small text-muted mb-1">' . htmlspecialchars(translate('বিজ্ঞাপন'), ENT_QUOTES, 'UTF-8') . '</span>'
                . '<div class="advertisement-carousel" data-ad-interval="2000" role="region" aria-label="' . htmlspecialchars(translate('বিজ্ঞাপন ছবি'), ENT_QUOTES, 'UTF-8') . '">'
                . '<div class="advertisement-carousel-viewport">';

            foreach ($imageFilenames as $index => $filename) {
                $activeClass = $index === 0 ? ' is-active' : '';
                $activeAttribute = $index === 0 ? ' aria-hidden="false"' : ' aria-hidden="true"';
                echo '<a class="advertisement-carousel-slide' . $activeClass . '" href="'
                    . htmlspecialchars($bannerUrl, ENT_QUOTES, 'UTF-8')
                    . '" target="_blank" rel="noopener noreferrer sponsored" tabindex="'
                    . ($index === 0 ? '0' : '-1') . '"' . $activeAttribute . '>'
                    . '<img src="' . htmlspecialchars(SITE_URL . 'assets/images/advertisements/' . rawurlencode($filename), ENT_QUOTES, 'UTF-8') . '"'
                    . ' alt="' . htmlspecialchars($settings['ad_banner_alt'] ?? translate('বিজ্ঞাপন'), ENT_QUOTES, 'UTF-8') . '"'
                    . ' width="300" height="250">'
                    . '</a>';
            }

            echo '</div><div class="advertisement-carousel-indicators" aria-label="' . htmlspecialchars(translate('বিজ্ঞাপনের ছবি নির্বাচন'), ENT_QUOTES, 'UTF-8') . '">';
            foreach ($imageFilenames as $index => $_filename) {
                echo '<button type="button" class="advertisement-carousel-indicator'
                    . ($index === 0 ? ' is-active' : '') . '" data-ad-slide="' . $index
                    . '" aria-label="' . htmlspecialchars(translate('বিজ্ঞাপনের ছবি'), ENT_QUOTES, 'UTF-8') . ' ' . ($index + 1) . '" aria-pressed="'
                    . ($index === 0 ? 'true' : 'false') . '"></button>';
            }
            echo '</div></div>' . $descriptionMarkup . '</div>';
            ?>
            <script>
                document.querySelectorAll('.advertisement-carousel:not([data-initialized])').forEach(function (carousel) {
                    carousel.dataset.initialized = 'true';
                    var slides = Array.from(carousel.querySelectorAll('.advertisement-carousel-slide'));
                    var indicators = Array.from(carousel.querySelectorAll('.advertisement-carousel-indicator'));
                    var currentIndex = 0;
                    var showSlide = function (nextIndex) {
                        currentIndex = (nextIndex + slides.length) % slides.length;
                        slides.forEach(function (slide, index) {
                            var isActive = index === currentIndex;
                            slide.classList.toggle('is-active', isActive);
                            slide.setAttribute('aria-hidden', isActive ? 'false' : 'true');
                            slide.tabIndex = isActive ? 0 : -1;
                        });
                        indicators.forEach(function (indicator, index) {
                            var isActive = index === currentIndex;
                            indicator.classList.toggle('is-active', isActive);
                            indicator.setAttribute('aria-pressed', isActive ? 'true' : 'false');
                        });
                    };

                    indicators.forEach(function (indicator, index) {
                        indicator.addEventListener('click', function () {
                            showSlide(index);
                        });
                    });

                    window.setInterval(function () {
                        showSlide(currentIndex + 1);
                    }, Number(carousel.dataset.adInterval) || 2000);
                });
            </script>
            <?php
            return;
        }

        error_log('Advertisement banner settings are incomplete or invalid.');
        return;
    }

    if (($settings['ad_mode'] ?? '') !== 'adsense') {
        return;
    }

    $clientId = $settings['adsense_client_id'] ?? '';
    $slotId = $settings['adsense_slot_id'] ?? '';
    if (!preg_match('/\Aca-pub-\d{16}\z/', $clientId) || !preg_match('/\A\d+\z/', $slotId)) {
        error_log('AdSense settings are incomplete or invalid.');
        return;
    }

    $escapedClientId = htmlspecialchars($clientId, ENT_QUOTES, 'UTF-8');
    $escapedSlotId = htmlspecialchars($slotId, ENT_QUOTES, 'UTF-8');
    echo '<div class="advertisement-slot text-center my-4">'
        . '<span class="d-block small text-muted mb-1">' . htmlspecialchars(translate('বিজ্ঞাপন'), ENT_QUOTES, 'UTF-8') . '</span>'
        . '<ins class="adsbygoogle" style="display:inline-block;width:300px;height:250px"'
        . ' data-ad-client="' . $escapedClientId . '" data-ad-slot="' . $escapedSlotId . '"></ins>'
        . '<script async src="https://pagead2.googlesyndication.com/pagead/js/adsbygoogle.js?client=' . $escapedClientId . '" crossorigin="anonymous"></script>'
        . '<script>(adsbygoogle = window.adsbygoogle || []).push({});</script>'
        . $descriptionMarkup . '</div>';
}

function save_advertisement_banner(array $file, string $uploadDirectory): ?string
{
    if (($file['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) {
        return null;
    }

    $uploadError = $file['error'] ?? UPLOAD_ERR_NO_FILE;
    if ($uploadError !== UPLOAD_ERR_OK) {
        $uploadErrorMessage = match ($uploadError) {
            UPLOAD_ERR_INI_SIZE, UPLOAD_ERR_FORM_SIZE => 'ব্যানার ছবি আপলোডের নির্ধারিত সাইজের সীমা ছাড়িয়েছে। ছোট ছবি ব্যবহার করুন।',
            UPLOAD_ERR_PARTIAL => 'ব্যানার ছবিটি সম্পূর্ণ আপলোড হয়নি। আবার চেষ্টা করুন।',
            UPLOAD_ERR_NO_TMP_DIR => 'সার্ভারে আপলোডের অস্থায়ী ফোল্ডার নেই। সার্ভার সেটিংস পরীক্ষা করুন।',
            UPLOAD_ERR_CANT_WRITE => 'সার্ভার ছবিটি অস্থায়ীভাবে সংরক্ষণ করতে পারেনি। ফোল্ডারের অনুমতি পরীক্ষা করুন।',
            UPLOAD_ERR_EXTENSION => 'সার্ভারের একটি এক্সটেনশন ছবি আপলোড বন্ধ করেছে।',
            default => 'ব্যানার ছবি আপলোড করা যায়নি। আবার চেষ্টা করুন।'
        };
        throw new RuntimeException($uploadErrorMessage);
    }

    if (($file['size'] ?? 0) > 2 * 1024 * 1024) {
        throw new RuntimeException('ব্যানার ছবির সাইজ সর্বোচ্চ ২ MB হতে পারবে।');
    }

    if (!isset($file['tmp_name']) || !is_uploaded_file($file['tmp_name'])) {
        throw new RuntimeException('আপলোড করা ব্যানার ছবিটি যাচাই করা যায়নি।');
    }

    $imageInfo = @getimagesize($file['tmp_name']);
    $supportedTypes = [IMAGETYPE_JPEG, IMAGETYPE_PNG, IMAGETYPE_GIF];
    if (defined('IMAGETYPE_WEBP')) {
        $supportedTypes[] = IMAGETYPE_WEBP;
    }
    if (!$imageInfo || !in_array($imageInfo[2], $supportedTypes, true)) {
        throw new RuntimeException('শুধুমাত্র JPG, PNG, GIF অথবা WebP ছবি আপলোড করুন।');
    }

    $width = (int)$imageInfo[0];
    $height = (int)$imageInfo[1];
    if ($width < 1 || $height < 1 || $width * $height > 12000000) {
        throw new RuntimeException('ছবির মাত্রা খুব বড়। সর্বোচ্চ ১২ মেগাপিক্সেলের ছবি ব্যবহার করুন।');
    }

    $imageData = file_get_contents($file['tmp_name']);
    $source = $imageData === false ? false : @imagecreatefromstring($imageData);
    if ($source === false) {
        throw new RuntimeException('ব্যানার ছবিটি পড়া যায়নি। অন্য ছবি ব্যবহার করুন।');
    }

    if (!is_dir($uploadDirectory)
        && !mkdir($uploadDirectory, 0755, true)
        && !is_dir($uploadDirectory)) {
        imagedestroy($source);
        throw new RuntimeException('ব্যানার সংরক্ষণের ফোল্ডার তৈরি করা যায়নি।');
    }

    $scale = min(1, 1200 / $width, 1200 / $height);
    $targetWidth = max(1, (int)round($width * $scale));
    $targetHeight = max(1, (int)round($height * $scale));
    $target = imagecreatetruecolor($targetWidth, $targetHeight);
    if ($target === false) {
        imagedestroy($source);
        throw new RuntimeException('ব্যানার ছবিটি প্রসেস করা যায়নি।');
    }

    $background = imagecolorallocate($target, 255, 255, 255);
    imagefill($target, 0, 0, $background);
    imagecopyresampled($target, $source, 0, 0, 0, 0, $targetWidth, $targetHeight, $width, $height);

    $filename = 'ad_' . bin2hex(random_bytes(16)) . '.jpg';
    $destination = rtrim($uploadDirectory, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . $filename;
    $saved = imagejpeg($target, $destination, 88);
    imagedestroy($source);
    imagedestroy($target);

    if (!$saved) {
        throw new RuntimeException('ব্যানার ছবি সংরক্ষণ করা যায়নি।');
    }

    return $filename;
}
