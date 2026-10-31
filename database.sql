CREATE DATABASE IF NOT EXISTS bsro;
USE bsro;

-- Admin Users Table
CREATE TABLE admins (
    id INT PRIMARY KEY AUTO_INCREMENT,
    username VARCHAR(50) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    email VARCHAR(100) NOT NULL,
    full_name VARCHAR(100) NOT NULL,
    profile_image VARCHAR(255) NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- News Categories Table
CREATE TABLE categories (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(100) NOT NULL,
    name_en VARCHAR(100) NULL,
    slug VARCHAR(191) UNIQUE NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- News Table
CREATE TABLE news (
    id INT PRIMARY KEY AUTO_INCREMENT,
    title VARCHAR(255) NOT NULL,
    title_en VARCHAR(255) NULL,
    slug VARCHAR(191) UNIQUE NOT NULL,
    content TEXT NOT NULL,
    content_en MEDIUMTEXT NULL,
    excerpt VARCHAR(500),
    excerpt_en VARCHAR(500) NULL,
    category_id INT,
    featured_image VARCHAR(255),
    author VARCHAR(100) DEFAULT 'bsro',
    views INT DEFAULT 0,
    is_featured BOOLEAN DEFAULT FALSE,
    status ENUM('published', 'draft') DEFAULT 'published',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE SET NULL
)ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS news_likes (
    id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    news_id INT NOT NULL,
    visitor_hash CHAR(64) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY unique_news_visitor_like (news_id, visitor_hash),
    CONSTRAINT fk_news_likes_news
        FOREIGN KEY (news_id) REFERENCES news(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS news_comments (
    id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    news_id INT NOT NULL,
    parent_id BIGINT UNSIGNED NULL,
    commenter_name VARCHAR(80) NOT NULL,
    comment_body TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    KEY idx_news_comments_news_created (news_id, created_at, id),
    KEY idx_news_comments_parent_created (parent_id, created_at, id),
    CONSTRAINT fk_news_comments_news
        FOREIGN KEY (news_id) REFERENCES news(id) ON DELETE CASCADE,
    CONSTRAINT fk_news_comments_parent
        FOREIGN KEY (parent_id) REFERENCES news_comments(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

SET @comment_parent_column_exists = (
    SELECT COUNT(*) FROM information_schema.columns
    WHERE table_schema = DATABASE() AND table_name = 'news_comments' AND column_name = 'parent_id'
);
SET @comment_parent_ddl = IF(@comment_parent_column_exists = 0,
    'ALTER TABLE news_comments
        ADD COLUMN parent_id BIGINT UNSIGNED NULL AFTER news_id,
        ADD KEY idx_news_comments_parent_created (parent_id, created_at, id),
        ADD CONSTRAINT fk_news_comments_parent
            FOREIGN KEY (parent_id) REFERENCES news_comments(id) ON DELETE CASCADE',
    'SELECT 1');
PREPARE comment_parent_stmt FROM @comment_parent_ddl;
EXECUTE comment_parent_stmt;
DEALLOCATE PREPARE comment_parent_stmt;

SET @news_title_en_exists = (
    SELECT COUNT(*) FROM information_schema.columns
    WHERE table_schema = DATABASE() AND table_name = 'news' AND column_name = 'title_en'
);
SET @news_title_en_ddl = IF(@news_title_en_exists = 0,
    'ALTER TABLE news ADD COLUMN title_en VARCHAR(255) NULL AFTER title',
    'SELECT 1');
PREPARE news_title_en_stmt FROM @news_title_en_ddl;
EXECUTE news_title_en_stmt;
DEALLOCATE PREPARE news_title_en_stmt;

SET @news_excerpt_en_exists = (
    SELECT COUNT(*) FROM information_schema.columns
    WHERE table_schema = DATABASE() AND table_name = 'news' AND column_name = 'excerpt_en'
);
SET @news_excerpt_en_ddl = IF(@news_excerpt_en_exists = 0,
    'ALTER TABLE news ADD COLUMN excerpt_en VARCHAR(500) NULL AFTER excerpt',
    'SELECT 1');
PREPARE news_excerpt_en_stmt FROM @news_excerpt_en_ddl;
EXECUTE news_excerpt_en_stmt;
DEALLOCATE PREPARE news_excerpt_en_stmt;

SET @news_content_en_exists = (
    SELECT COUNT(*) FROM information_schema.columns
    WHERE table_schema = DATABASE() AND table_name = 'news' AND column_name = 'content_en'
);
SET @news_content_en_ddl = IF(@news_content_en_exists = 0,
    'ALTER TABLE news ADD COLUMN content_en MEDIUMTEXT NULL AFTER content',
    'SELECT 1');
PREPARE news_content_en_stmt FROM @news_content_en_ddl;
EXECUTE news_content_en_stmt;
DEALLOCATE PREPARE news_content_en_stmt;

-- Contact Messages Table
CREATE TABLE contact_messages (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL,
    phone VARCHAR(20),
    subject VARCHAR(200) NOT NULL,
    message TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    is_read BOOLEAN DEFAULT FALSE
);

-- Insert Default Admin (password: admin123)
INSERT INTO admins (username, password, email, full_name)
VALUES ('admin', '8921', 'admin@protidin.com', 'Administrator');

-- Insert Categories
INSERT INTO categories (name, name_en, slug) VALUES
('জাতীয়', 'National', 'national'),
('আন্তর্জাতিক', 'International', 'international'),
('রাজনীতি', 'Politics', 'politics'),
('বাণিজ্য', 'Business', 'business'),
('খেলা', 'Sports', 'sports'),
('বিনোদন', 'Entertainment', 'entertainment'),
('স্বাস্থ্য', 'Health', 'health'),
('প্রযুক্তি', 'Technology', 'technology');

-- Settings Table
CREATE TABLE IF NOT EXISTS settings (
    id INT PRIMARY KEY AUTO_INCREMENT,
    setting_key VARCHAR(100) UNIQUE NOT NULL,
    setting_value TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Insert default settings
INSERT INTO settings (setting_key, setting_value) VALUES
('site_name', 'বিজ্ঞান জগৎ'),
('site_url', 'http://localhost/bangladesh-protidin/'),
('site_email', 'info@bangladeshprotidin.com'),
('site_phone', '+88 02 9555555'),
('site_address', '১২৩ প্রেস ক্লাব রোড, মতিঝিল, ঢাকা-১০০০'),
('site_description', 'বাংলাদেশের শীর্ষ অনলাইন নিউজ পোর্টাল'),
('site_keywords', 'বাংলাদেশ, খবর, নিউজ, রাজনীতি, বিনোদন, খেলা'),
('facebook_url', 'https://facebook.com/bangladeshprotidin'),
('twitter_url', 'https://twitter.com/bdprotidin'),
('youtube_url', 'https://youtube.com/bangladeshprotidin'),
('instagram_url', 'https://instagram.com/bangladeshprotidin'),
('news_per_page', '20'),
('breaking_news_enabled', '1'),
('comments_enabled', '1'),
('maintenance_mode', '0'),
('contact_email', 'contact@bangladeshprotidin.com'),
('advertisement_email', 'advertisement@bangladeshprotidin.com'),
('seo_title', 'বিজ্ঞান জগৎ - সবচেয়ে দ্রুত খবর'),
('seo_description', 'বাংলাদেশের সবচেয়ে দ্রুততম ও নির্ভরযোগ্য নিউজ পোর্টাল'),
('site_theme', 'light'),
('ad_mode', 'off'),
('ad_banner_image', ''),
('ad_banner_image_1', ''),
('ad_banner_image_2', ''),
('ad_banner_image_3', ''),
('ad_banner_url', ''),
('ad_banner_alt', 'বিজ্ঞাপন'),
('ad_description', ''),
('adsense_client_id', ''),
('adsense_slot_id', '');

-- Create message_replies table if not exists
CREATE TABLE IF NOT EXISTS message_replies (
    id INT PRIMARY KEY AUTO_INCREMENT,
    message_id INT NOT NULL,
    admin_id INT NOT NULL,
    subject VARCHAR(255) NOT NULL,
    message TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (message_id) REFERENCES contact_messages(id) ON DELETE CASCADE,
    FOREIGN KEY (admin_id) REFERENCES admins(id) ON DELETE CASCADE
);

-- Create system_logs table if not exists
CREATE TABLE IF NOT EXISTS system_logs (
    id INT PRIMARY KEY AUTO_INCREMENT,
    log_type ENUM('info', 'warning', 'error', 'security', 'login', 'news', 'message') DEFAULT 'info',
    module VARCHAR(100),
    action VARCHAR(255),
    description TEXT,
    ip_address VARCHAR(45),
    user_agent TEXT,
    admin_id INT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_log_type (log_type),
    INDEX idx_created_at (created_at),
    INDEX idx_module (module),
    FOREIGN KEY (admin_id) REFERENCES admins(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Add admin_role column to admins table if not exists
SET @admin_column_exists = (
    SELECT COUNT(*) FROM information_schema.columns
    WHERE table_schema = DATABASE() AND table_name = 'admins' AND column_name = 'role'
);
SET @admin_ddl = IF(@admin_column_exists = 0,
    'ALTER TABLE admins ADD COLUMN role ENUM(''admin'', ''editor'', ''super_admin'') DEFAULT ''admin''',
    'SELECT 1');
PREPARE admin_stmt FROM @admin_ddl;
EXECUTE admin_stmt;
DEALLOCATE PREPARE admin_stmt;

SET @admin_column_exists = (
    SELECT COUNT(*) FROM information_schema.columns
    WHERE table_schema = DATABASE() AND table_name = 'admins' AND column_name = 'last_login'
);
SET @admin_ddl = IF(@admin_column_exists = 0,
    'ALTER TABLE admins ADD COLUMN last_login TIMESTAMP NULL',
    'SELECT 1');
PREPARE admin_stmt FROM @admin_ddl;
EXECUTE admin_stmt;
DEALLOCATE PREPARE admin_stmt;

SET @admin_column_exists = (
    SELECT COUNT(*) FROM information_schema.columns
    WHERE table_schema = DATABASE() AND table_name = 'admins' AND column_name = 'login_attempts'
);
SET @admin_ddl = IF(@admin_column_exists = 0,
    'ALTER TABLE admins ADD COLUMN login_attempts INT DEFAULT 0',
    'SELECT 1');
PREPARE admin_stmt FROM @admin_ddl;
EXECUTE admin_stmt;
DEALLOCATE PREPARE admin_stmt;

SET @admin_column_exists = (
    SELECT COUNT(*) FROM information_schema.columns
    WHERE table_schema = DATABASE() AND table_name = 'admins' AND column_name = 'is_active'
);
SET @admin_ddl = IF(@admin_column_exists = 0,
    'ALTER TABLE admins ADD COLUMN is_active BOOLEAN NOT NULL DEFAULT TRUE',
    'SELECT 1');
PREPARE admin_stmt FROM @admin_ddl;
EXECUTE admin_stmt;
DEALLOCATE PREPARE admin_stmt;

SET @admin_column_exists = (
    SELECT COUNT(*) FROM information_schema.columns
    WHERE table_schema = DATABASE() AND table_name = 'admins' AND column_name = 'role_id'
);
SET @admin_ddl = IF(@admin_column_exists = 0,
    'ALTER TABLE admins ADD COLUMN role_id INT NULL',
    'SELECT 1');
PREPARE admin_stmt FROM @admin_ddl;
EXECUTE admin_stmt;
DEALLOCATE PREPARE admin_stmt;

SET @admin_column_exists = (
    SELECT COUNT(*) FROM information_schema.columns
    WHERE table_schema = DATABASE() AND table_name = 'admins' AND column_name = 'profile_image'
);
SET @admin_ddl = IF(@admin_column_exists = 0,
    'ALTER TABLE admins ADD COLUMN profile_image VARCHAR(255) NULL',
    'SELECT 1');
PREPARE admin_stmt FROM @admin_ddl;
EXECUTE admin_stmt;
DEALLOCATE PREPARE admin_stmt;

CREATE TABLE IF NOT EXISTS admin_roles (
    id INT PRIMARY KEY AUTO_INCREMENT,
    role_key VARCHAR(50) UNIQUE NOT NULL,
    role_name VARCHAR(100) NOT NULL,
    permissions_json TEXT NOT NULL,
    is_super_admin BOOLEAN NOT NULL DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO admin_roles (role_key, role_name, permissions_json, is_super_admin) VALUES
('super_admin', 'সুপার অ্যাডমিন', '["dashboard_view","news_view","news_add","news_edit","news_delete","category_manage","messages_manage","subscribers_view","settings_manage","logs_view","backup_manage","users_manage"]', TRUE),
('administrator', 'অ্যাডমিনিস্ট্রেটর', '["dashboard_view","news_view","news_add","news_edit","news_delete","category_manage","messages_manage","subscribers_view","settings_manage","logs_view","backup_manage"]', FALSE),
('news_manager', 'নিউজ ম্যানেজার', '["dashboard_view","news_view","news_add","news_edit","news_delete","category_manage"]', FALSE),
('news_editor', 'নিউজ এডিটর', '["dashboard_view","news_view","news_add","news_edit"]', FALSE),
('moderator', 'মডারেটর', '["dashboard_view","messages_manage","subscribers_view"]', FALSE),
('log_viewer', 'লগ পর্যবেক্ষক', '["dashboard_view","logs_view"]', FALSE)
ON DUPLICATE KEY UPDATE
    role_name = VALUES(role_name),
    permissions_json = VALUES(permissions_json),
    is_super_admin = VALUES(is_super_admin);

-- Keep the first existing administrator as the owner during upgrades.
UPDATE admins
SET role_id = (SELECT id FROM admin_roles WHERE role_key = 'super_admin')
WHERE role_id IS NULL
  AND id = (SELECT owner_id FROM (SELECT MIN(id) AS owner_id FROM admins) AS first_admin);

UPDATE admins
SET role_id = (SELECT id FROM admin_roles WHERE role_key = 'administrator')
WHERE role_id IS NULL;







CREATE TABLE IF NOT EXISTS subscribers (
    id INT PRIMARY KEY AUTO_INCREMENT,
    email VARCHAR(100) UNIQUE NOT NULL,
    name VARCHAR(100),
    subscription_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    is_active BOOLEAN DEFAULT TRUE,
    unsubscribe_token VARCHAR(32),
    confirmed BOOLEAN DEFAULT FALSE,
    confirmation_token VARCHAR(32),
    ip_address VARCHAR(45)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- বা যদি টেবিল ইতিমধ্যে থাকে:
-- ALTER TABLE subscribers
-- ADD COLUMN IF NOT EXISTS unsubscribe_token VARCHAR(32),
-- ADD COLUMN IF NOT EXISTS confirmed BOOLEAN DEFAULT FALSE,
-- ADD COLUMN IF NOT EXISTS confirmation_token VARCHAR(32),
-- ADD COLUMN IF NOT EXISTS ip_address VARCHAR(45);