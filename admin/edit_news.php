<?php
require_once '../includes/admin_auth.php';
require_once '../includes/news_image.php';
require_admin_permission('news_edit');

// Get news ID from URL
if(!isset($_GET['id'])) {
    header('Location: manage_news.php');
    exit;
}

$news_id = intval($_GET['id']);

// Fetch news details
$news_query = "SELECT * FROM news WHERE id = $news_id";
$news_result = $conn->query($news_query);

if($news_result->num_rows == 0) {
    header('Location: manage_news.php');
    exit;
}

$news = $news_result->fetch_assoc();

// Fetch categories for dropdown
$categories_query = "SELECT * FROM categories ORDER BY name ASC";
$categories_result = $conn->query($categories_query);

$message = '';
$message_type = '';

// Handle form submission
if($_SERVER['REQUEST_METHOD'] == 'POST') {
    $title = $conn->real_escape_string($_POST['title']);
    $title_en_input = trim($_POST['title_en'] ?? '');
    $title_en = $conn->real_escape_string($title_en_input);
    $slug = $conn->real_escape_string(strtolower(str_replace(' ', '-', $title)));
    $slug = preg_replace('/[^a-z0-9\-]/', '', $slug);

    // Check if slug already exists (excluding current news)
    $slug_check = $conn->query("SELECT id FROM news WHERE slug = '$slug' AND id != $news_id");
    if($slug_check->num_rows > 0) {
        $slug = $slug . '-' . time();
    }

    $content = $conn->real_escape_string($_POST['content']);
    $excerpt = $conn->real_escape_string(substr(strip_tags($_POST['content']), 0, 500));
    $content_en = $conn->real_escape_string($_POST['content_en'] ?? '');
    $excerpt_en = $conn->real_escape_string(trim($_POST['excerpt_en'] ?? ''));
    $category_id = intval($_POST['category_id']);
    $author = $conn->real_escape_string($_POST['author']);
    $is_featured = isset($_POST['is_featured']) ? 1 : 0;
    $status = $_POST['status'];

    // Resize uploaded images without cropping them.
    $featured_image = $news['featured_image']; // Keep existing image by default
    $uploaded_image_path = null;

    if (isset($_FILES['featured_image'])) {
        try {
            $processedImage = process_news_image(
                $_FILES['featured_image'],
                __DIR__ . '/../assets/images/news'
            );
            if ($processedImage !== null) {
                $featured_image = $processedImage;
                $uploaded_image_path = __DIR__ . '/../assets/images/news/' . $featured_image;
            }
        } catch (RuntimeException $exception) {
            $message = $exception->getMessage();
            $message_type = 'danger';
        }
    }

    if(empty($message)) {
        $update_query = "UPDATE news SET
                        title = '$title',
                        title_en = '$title_en',
                        slug = '$slug',
                        content = '$content',
                        content_en = '$content_en',
                        excerpt = '$excerpt',
                        excerpt_en = '$excerpt_en',
                        category_id = $category_id,
                        featured_image = '$featured_image',
                        author = '$author',
                        is_featured = $is_featured,
                        status = '$status',
                        updated_at = NOW()
                        WHERE id = $news_id";

        if($conn->query($update_query)) {
            $message = 'নিউজ সফলভাবে আপডেট করা হয়েছে';
            $message_type = 'success';

            if ($uploaded_image_path !== null && $news['featured_image'] !== 'default.jpg') {
                $old_image_path = __DIR__ . '/../assets/images/news/' . basename($news['featured_image']);
                if (is_file($old_image_path)) {
                    unlink($old_image_path);
                }
            }

            // Refresh news data
            $news_result = $conn->query("SELECT * FROM news WHERE id = $news_id");
            $news = $news_result->fetch_assoc();
        } else {
            if ($uploaded_image_path !== null && is_file($uploaded_image_path)) {
                unlink($uploaded_image_path);
            }
            $message = 'নিউজ আপডেট করতে সমস্যা হয়েছে: ' . $conn->error;
            $message_type = 'danger';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="<?php echo htmlspecialchars(site_language(), ENT_QUOTES, 'UTF-8'); ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>নিউজ সম্পাদনা করুন - বিজ্ঞান জগৎ</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="admin-layout.css?v=6">
    <!-- Summernote WYSIWYG Editor -->
    <link href="https://cdn.jsdelivr.net/npm/summernote@0.8.18/dist/summernote-lite.min.css" rel="stylesheet">
    <!-- Favicon -->
    <link rel="shortcut icon" href="<?php echo SITE_URL; ?>/assets/images/logo1.jpg" type="image/x-icon">
    <style>
        body {
            font-family: 'Noto Sans Bengali', sans-serif;
            background-color: #f5f5f5;
        }
        .sidebar {
            background-color: #343a40;
            min-height: 100vh;
            color: white;
        }
        .sidebar a {
            color: white;
            text-decoration: none;
            display: block;
            padding: 10px 15px;
            border-radius: 5px;
            margin-bottom: 5px;
        }
        .sidebar a:hover, .sidebar a.active {
            background-color: #495057;
        }
        .note-editor {
            direction: ltr !important;
        }
        .note-editor .note-editable {
            direction: rtl !important;
            text-align: right !important;
        }
    </style>
</head>
<body>
    <div class="container-fluid">
        <div class="row admin-layout">
            <!-- Sidebar -->
            <div class="col-md-3 col-lg-2 sidebar admin-sidebar p-0">
                <div class="p-3">
                    <h4 class="text-center mb-4 border-bottom pb-3">
                        <i class="fas fa-newspaper"></i> বিজ্ঞান জগৎ
                    </h4>

                    <div class="text-center mb-4">
                        <?php require __DIR__ . '/../includes/admin_profile_avatar.php'; ?>
                        <h5><?php echo $_SESSION['admin_name']; ?></h5>
                        <small>অ্যাডমিন</small>
                    </div>

                    <nav class="nav flex-column">
                        <a href="dashboard.php">
                            <i class="fas fa-tachometer-alt"></i> ড্যাশবোর্ড
                        </a>
                        <a href="add_news.php">
                            <i class="fas fa-plus-circle"></i> নিউজ যোগ করুন
                        </a>
                        <a href="manage_news.php">
                            <i class="fas fa-newspaper"></i> নিউজ ব্যবস্থাপনা
                        </a>
                        <a href="categories.php" class="active">
                            <i class="fas fa-list"></i> ক্যাটেগরি ব্যবস্থাপনা
                        </a>
                        <a href="messages.php">
                            <i class="fas fa-envelope"></i> বার্তা ব্যবস্থাপনা
                        </a>
                        <a href="settings.php">
                            <i class="fas fa-cog"></i> সেটিংস
                        </a>
                        <a href="logout.php" class="text-warning">
                            <i class="fas fa-sign-out-alt"></i> লগআউট
                        </a>
                    </nav>
                </div>
            </div>

            <!-- Main Content -->
            <div class="col-md-9 col-lg-10 ms-sm-auto px-md-4 py-4 admin-content">
                <!-- Top Bar -->
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                    <h1 class="h2 bangla-font"><i class="fas fa-edit"></i> নিউজ সম্পাদনা করুন</h1>
                    <div class="btn-toolbar mb-2 mb-md-0">
                        <a href="manage_news.php" class="btn btn-sm btn-outline-secondary">
                            <i class="fas fa-list"></i> সব নিউজ দেখুন
                        </a>
                    </div>
                </div>

                <?php if($message): ?>
                <div class="alert alert-<?php echo $message_type; ?> alert-dismissible fade show">
                    <?php echo $message; ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
                <?php endif; ?>

                <div class="card">
                    <div class="card-body">
                        <form method="POST" action="" enctype="multipart/form-data">
                            <div class="row">
                                <div class="col-md-8">
                                    <div class="mb-3">
                                        <label for="title" class="form-label bangla-font"><?php echo translate('নিউজ শিরোনাম'); ?> *</label>
                                        <input type="text" class="form-control" id="title" name="title"
                                               value="<?php echo htmlspecialchars($news['title']); ?>" required>
                                    </div>

                                    <div class="mb-3">
                                        <label for="title_en" class="form-label">English title</label>
                                        <input type="text" class="form-control" id="title_en" name="title_en" maxlength="255"
                                               value="<?php echo htmlspecialchars($news['title_en'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                                    </div>

                                    <div class="mb-3">
                                        <label for="slug" class="form-label bangla-font">ইউআরএল স্লাগ</label>
                                        <input type="text" class="form-control" id="slug" name="slug"
                                               value="<?php echo htmlspecialchars($news['slug']); ?>" readonly>
                                        <div class="form-text bangla-font">এটি স্বয়ংক্রিয়ভাবে তৈরি হয়</div>
                                    </div>

                                    <div class="mb-3">
                                        <label for="content" class="form-label bangla-font">নিউজ কন্টেন্ট *</label>
                                        <textarea class="form-control" id="content" name="content" rows="15" required><?php echo htmlspecialchars($news['content']); ?></textarea>
                                    </div>

                                    <div class="mb-3">
                                        <label for="excerpt_en" class="form-label">English summary</label>
                                        <textarea class="form-control" id="excerpt_en" name="excerpt_en" rows="3" maxlength="500"><?php echo htmlspecialchars($news['excerpt_en'] ?? '', ENT_QUOTES, 'UTF-8'); ?></textarea>
                                    </div>

                                    <div class="mb-3">
                                        <label for="content_en" class="form-label">English content</label>
                                        <textarea class="form-control" id="content_en" name="content_en" rows="15"><?php echo htmlspecialchars($news['content_en'] ?? '', ENT_QUOTES, 'UTF-8'); ?></textarea>
                                    </div>
                                </div>

                                <div class="col-md-4">
                                    <!-- Current Image -->
                                    <div class="card mb-3">
                                        <div class="card-header bg-primary text-white">
                                            <h6 class="mb-0 bangla-font">বর্তমান ছবি</h6>
                                        </div>
                                        <div class="card-body text-center">
                                            <img src="<?php echo '../assets/images/news/' . $news['featured_image']; ?>"
                                                 alt="বর্তমান ছবি"
                                                 class="img-fluid rounded mb-3"
                                                 id="currentImage"
                                                 style="max-width: 100%; max-height: 200px; object-fit: contain;">

                                            <div class="mb-3">
                                                <label for="featured_image" class="form-label bangla-font">নতুন ছবি আপলোড</label>
                                                <input type="file" class="form-control" id="featured_image" name="featured_image"
                                                       accept="image/jpeg,image/png,image/gif,image/webp" onchange="previewNewImage(this)">
                                            </div>

                                            <div id="newImagePreview" style="display: none;">
                                                <h6 class="bangla-font">নতুন ছবি প্রিভিউ</h6>
                                                <img src="" alt="নতুন ছবি প্রিভিউ"
                                                     class="img-fluid rounded mb-3"
                                                     id="newImage"
                                                     style="max-width: 100%; max-height: 150px; object-fit: contain;">
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Category -->
                                    <div class="card mb-3">
                                        <div class="card-header bg-success text-white">
                                            <h6 class="mb-0 bangla-font">ক্যাটেগরি *</h6>
                                        </div>
                                        <div class="card-body">
                                            <select class="form-select" id="category_id" name="category_id" required>
                                                <option value="">ক্যাটেগরি নির্বাচন করুন</option>
                                                <?php if($categories_result && $categories_result->num_rows > 0): ?>
                                                    <?php while($category = $categories_result->fetch_assoc()): ?>
                                                    <option value="<?php echo $category['id']; ?>"
                                                        <?php echo ($news['category_id'] == $category['id']) ? 'selected' : ''; ?>>
                                                        <?php echo htmlspecialchars(localized_category_name($category['name'], $category['name_en'] ?? null), ENT_QUOTES, 'UTF-8'); ?>
                                                    </option>
                                                    <?php endwhile; ?>
                                                <?php endif; ?>
                                            </select>
                                        </div>
                                    </div>

                                    <!-- Additional Information -->
                                    <div class="card mb-3">
                                        <div class="card-header bg-info text-white">
                                            <h6 class="mb-0 bangla-font">অতিরিক্ত তথ্য</h6>
                                        </div>
                                        <div class="card-body">
                                            <div class="mb-3">
                                                <label for="author" class="form-label bangla-font">লেখক</label>
                                                <input type="text" class="form-control" id="author" name="author"
                                                       value="<?php echo htmlspecialchars($news['author']); ?>">
                                            </div>

                                            <div class="mb-3">
                                                <label class="form-label bangla-font">স্ট্যাটাস *</label>
                                                <div>
                                                    <div class="form-check form-check-inline">
                                                        <input class="form-check-input" type="radio" name="status"
                                                               id="status_published" value="published"
                                                               <?php echo ($news['status'] == 'published') ? 'checked' : ''; ?>>
                                                        <label class="form-check-label bangla-font" for="status_published">প্রকাশিত</label>
                                                    </div>
                                                    <div class="form-check form-check-inline">
                                                        <input class="form-check-input" type="radio" name="status"
                                                               id="status_draft" value="draft"
                                                               <?php echo ($news['status'] == 'draft') ? 'checked' : ''; ?>>
                                                        <label class="form-check-label bangla-font" for="status_draft">খসড়া</label>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="form-check mb-3">
                                                <input class="form-check-input" type="checkbox" id="is_featured" name="is_featured"
                                                       <?php echo ($news['is_featured'] == 1) ? 'checked' : ''; ?>>
                                                <label class="form-check-label bangla-font" for="is_featured">
                                                    শীর্ষ নিউজ হিসেবে সেট করুন
                                                </label>
                                            </div>

                                            <div class="mb-3">
                                                <label class="form-label bangla-font">তৈরি করা হয়েছে</label>
                                                <p class="form-control-static bangla-font">
                                                    <?php echo date('d/m/Y h:i A', strtotime($news['created_at'])); ?>
                                                </p>
                                            </div>

                                            <div class="mb-3">
                                                <label class="form-label bangla-font">সর্বশেষ আপডেট</label>
                                                <p class="form-control-static bangla-font">
                                                    <?php echo date('d/m/Y h:i A', strtotime($news['updated_at'])); ?>
                                                </p>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Submit Buttons -->
                                    <div class="d-grid gap-2">
                                        <button type="submit" class="btn btn-success btn-lg bangla-font">
                                            <i class="fas fa-save"></i> আপডেট সংরক্ষণ করুন
                                        </button>
                                        <a href="manage_news.php" class="btn btn-outline-secondary bangla-font">
                                            <i class="fas fa-times"></i> বাতিল করুন
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Quick Actions -->
                <div class="card mt-4">
                    <div class="card-header bg-danger text-white">
                        <h6 class="mb-0 bangla-font">দ্রুত অ্যাকশন</h6>
                    </div>
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <a href="../news.php?slug=<?php echo $news['slug']; ?>"
                               target="_blank" class="btn btn-info bangla-font">
                                <i class="fas fa-eye"></i> ওয়েবসাইটে দেখুন
                            </a>
                            <a href="manage_news.php?delete_id=<?php echo $news['id']; ?>"
                               class="btn btn-danger bangla-font"
                               onclick="return confirm('আপনি কি এই নিউজটি মুছে ফেলতে চান? এটি সরাসরি মুছে যাবে।')">
                                <i class="fas fa-trash"></i> নিউজ মুছে ফেলুন
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
    <!-- Summernote WYSIWYG Editor -->
    <script src="https://cdn.jsdelivr.net/npm/summernote@0.8.18/dist/summernote-lite.min.js"></script>
    <script>
        // Image preview for new image
        function previewNewImage(input) {
            if (input.files && input.files[0]) {
                const reader = new FileReader();

                reader.onload = function(e) {
                    document.getElementById('newImagePreview').style.display = 'block';
                    document.getElementById('newImage').src = e.target.result;
                }

                reader.readAsDataURL(input.files[0]);
            }
        }

        // Initialize Summernote editor
        $(document).ready(function() {
            $('#content').summernote({
                height: 400,
                lang: 'bn-BD',
                toolbar: [
                    ['style', ['style']],
                    ['font', ['bold', 'italic', 'underline', 'clear']],
                    ['fontname', ['fontname']],
                    ['color', ['color']],
                    ['para', ['ul', 'ol', 'paragraph']],
                    ['height', ['height']],
                    ['table', ['table']],
                    ['insert', ['link', 'picture', 'video']],
                    ['view', ['fullscreen', 'codeview', 'help']]
                ],
                fontNames: ['Noto Sans Bengali', 'Arial', 'Helvetica', 'Courier New'],
                fontSizes: ['8', '9', '10', '11', '12', '14', '18', '24', '36'],
                callbacks: {
                    onInit: function() {
                        // Set direction for Bangla
                        $('.note-editable').css('direction', 'rtl');
                        $('.note-editable').css('text-align', 'right');
                    }
                }
            });

            // Auto generate slug from title
            $('#title').on('keyup', function() {
                const title = $(this).val();
                const slug = title.toLowerCase()
                    .replace(/[^\w\u0980-\u09FF]+/g, '-')
                    .replace(/^-+|-+$/g, '');
                $('#slug').val(slug);
            });
        });
    </script>
</body>
</html>