<?php

function process_admin_avatar(array $file, string $uploadDirectory): ?string
{
    if (($file['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) {
        return null;
    }

    if (($file['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
        throw new RuntimeException('প্রোফাইল ছবি আপলোড করা যায়নি। আবার চেষ্টা করুন।');
    }

    if (($file['size'] ?? 0) > 2 * 1024 * 1024) {
        throw new RuntimeException('প্রোফাইল ছবির সাইজ সর্বোচ্চ ২ MB হতে পারবে।');
    }

    if (!isset($file['tmp_name']) || !is_uploaded_file($file['tmp_name'])) {
        throw new RuntimeException('আপলোড করা প্রোফাইল ছবিটি যাচাই করা যায়নি।');
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
    if (!function_exists('imagecreatefromstring') || !function_exists('imagecreatetruecolor')) {
        throw new RuntimeException('সার্ভারে GD image processing extension চালু নেই।');
    }

    $sourceData = file_get_contents($file['tmp_name']);
    $source = $sourceData === false ? false : @imagecreatefromstring($sourceData);
    if ($source === false) {
        throw new RuntimeException('প্রোফাইল ছবিটি পড়া যায়নি। অন্য ছবি ব্যবহার করে চেষ্টা করুন।');
    }

    $maxDimension = 512;
    $scale = min(1, $maxDimension / $width, $maxDimension / $height);
    $targetWidth = max(1, (int)round($width * $scale));
    $targetHeight = max(1, (int)round($height * $scale));
    $target = imagecreatetruecolor($targetWidth, $targetHeight);
    if ($target === false) {
        imagedestroy($source);
        throw new RuntimeException('প্রোফাইল ছবিটি প্রসেস করা যায়নি।');
    }

    $background = imagecolorallocate($target, 255, 255, 255);
    imagefill($target, 0, 0, $background);
    imagecopyresampled(
        $target,
        $source,
        0,
        0,
        0,
        0,
        $targetWidth,
        $targetHeight,
        $width,
        $height
    );

    if (!is_dir($uploadDirectory)
        && !mkdir($uploadDirectory, 0755, true)
        && !is_dir($uploadDirectory)) {
        imagedestroy($source);
        imagedestroy($target);
        throw new RuntimeException('প্রোফাইল ছবি সংরক্ষণের ফোল্ডার তৈরি করা যায়নি।');
    }

    $filename = 'admin_' . bin2hex(random_bytes(16)) . '.jpg';
    $destination = rtrim($uploadDirectory, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . $filename;
    $saved = imagejpeg($target, $destination, 86);

    imagedestroy($source);
    imagedestroy($target);

    if (!$saved) {
        throw new RuntimeException('প্রোফাইল ছবি সংরক্ষণ করা যায়নি।');
    }

    return $filename;
}
