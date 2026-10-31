<?php if (!empty($_SESSION['admin_profile_image'])): ?>
    <img
        src="../assets/images/admins/<?php echo rawurlencode(basename($_SESSION['admin_profile_image'])); ?>"
        alt="<?php echo htmlspecialchars(($_SESSION['admin_name'] ?? translate('অ্যাডমিন')) . translate('এর প্রোফাইল ছবি'), ENT_QUOTES, 'UTF-8'); ?>"
        class="admin-profile-image rounded-circle mb-2"
    >
<?php else: ?>
    <div class="admin-profile-placeholder bg-danger rounded-circle d-inline-flex justify-content-center align-items-center mb-2">
        <i class="fas fa-user-shield"></i>
    </div>
<?php endif; ?>
