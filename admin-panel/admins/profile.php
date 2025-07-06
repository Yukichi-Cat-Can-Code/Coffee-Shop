<?php
require "../../config/config.php";
requireAdminLogin();

// Lấy thông tin admin từ session
$admin_id = $_SESSION['admin_id'];

try {
    $stmt = $conn->prepare("SELECT admin_name, admin_email, created_at FROM admins WHERE ID = :id");
    $stmt->bindParam(':id', $admin_id);
    $stmt->execute();
    $admin = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$admin) {
        session()->setFlash('error_message', "Không tìm thấy thông tin tài khoản.");
        redirect(ADMINAPPURL . "/admins/login-admins.php");
        exit;
    }
} catch (PDOException $e) {
    session()->setFlash('error_message', "Lỗi truy vấn: " . $e->getMessage());
    redirect(ADMINAPPURL . "/admins/login-admins.php");
    exit;
}

require "../layouts/header.php";
?>

<style>
    body {
        background: #f6f3ef;
    }

    .profile-card {
        border-radius: 20px;
        box-shadow: 0 6px 32px rgba(80, 60, 40, 0.12);
        overflow: hidden;
        background: #fff;
        margin-top: 48px;
        font-family: 'Quicksand', 'Segoe UI', Arial, sans-serif;
    }

    .profile-header-cafe {
        background: linear-gradient(120deg, #a9744f 60%, #6f4e37 100%);
        padding: 54px 0 32px 0;
        text-align: center;
        color: #fff;
        position: relative;
    }

    .profile-avatar-cafe {
        width: 110px;
        height: 110px;
        object-fit: cover;
        border-radius: 50%;
        border: 4px solid #fff;
        box-shadow: 0 2px 8px rgba(111, 78, 55, 0.12);
        margin-top: -55px;
        background: #f8f6f2;
        font-size: 2.5rem;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: bold;
        letter-spacing: 2px;
    }

    .coffee-steam {
        position: absolute;
        left: 50%;
        top: 18px;
        transform: translateX(-50%);
        width: 32px;
        height: 32px;
        z-index: 2;
    }

    .coffee-steam span {
        display: block;
        width: 6px;
        height: 18px;
        margin: 0 2px;
        float: left;
        background: linear-gradient(to top, #fff 60%, transparent 100%);
        border-radius: 50px;
        opacity: 0.7;
        animation: steamUp 2s infinite linear;
    }

    .coffee-steam span:nth-child(2) {
        animation-delay: .5s;
    }

    .coffee-steam span:nth-child(3) {
        animation-delay: 1s;
    }

    @keyframes steamUp {
        0% {
            opacity: 0.7;
            transform: translateY(0);
        }

        50% {
            opacity: 0.3;
            transform: translateY(-10px);
        }

        100% {
            opacity: 0.7;
            transform: translateY(0);
        }
    }

    .profile-body {
        padding: 2rem 2.5rem 1.5rem 2.5rem;
        background: #fffdfa;
    }

    .profile-info dt {
        font-weight: 600;
        color: #a9744f;
    }

    .profile-actions {
        padding: 1.5rem 2.5rem 2rem 2.5rem;
        background: #f6f3ef;
        border-top: 1px solid #e3e6f0;
        text-align: right;
    }

    .btn-coffee {
        background: #a9744f;
        color: #fff;
        border: none;
    }

    .btn-coffee:hover,
    .btn-coffee:focus {
        background: #6f4e37;
        color: #fff;
    }

    .btn-outline-coffee {
        border: 1.5px solid #a9744f;
        color: #a9744f;
        background: #fff;
    }

    .btn-outline-coffee:hover,
    .btn-outline-coffee:focus {
        background: #a9744f;
        color: #fff;
    }

    @media (max-width: 576px) {

        .profile-body,
        .profile-actions {
            padding: 1rem;
        }
    }
</style>

<div class="container">
    <div class="row justify-content-center">
        <div class="col-lg-7 col-md-9">
            <div class="profile-card shadow">
                <div class="profile-header-cafe">
                    <!-- <div class="coffee-steam">
                        <span></span><span></span><span></span>
                    </div> -->
                    <!-- Dời avatar xuống dưới một chút -->
                    <div style="height: 30px;"></div>
                    <div class="profile-avatar-cafe mx-auto mb-2" style="background: #f8f6f2; overflow: hidden; padding: 0;">
                        <!-- Ảnh ẩn danh kiểu Facebook (SVG) -->
                        <svg width="90" height="90" viewBox="0 0 90 90" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <circle cx="45" cy="45" r="44" fill="#e0e0e0" stroke="#ccc" stroke-width="2" />
                            <!-- Đầu -->
                            <circle cx="45" cy="36" r="18" fill="#fff" />
                            <!-- Vai/Ngực -->
                            <ellipse cx="45" cy="68" rx="26" ry="16" fill="#fff" />
                            <!-- Đường nét mặt -->
                            <ellipse cx="45" cy="36" rx="12" ry="13" fill="#f5f5f5" />
                        </svg>
                    </div>
                    <h3 class="mt-3 mb-1" style="font-family:'Quicksand',sans-serif;"><?= htmlspecialchars($admin['admin_name']) ?></h3>
                    <div class="small" style="opacity:.92;">☕ Quản trị viên Coffee Shop</div>
                </div>
                <div class="profile-body">
                    <dl class="row profile-info mb-0">
                        <dt class="col-sm-5">Email:</dt>
                        <dd class="col-sm-7"><?= htmlspecialchars($admin['admin_email']) ?></dd>
                        <dt class="col-sm-5">Ngày tạo tài khoản:</dt>
                        <dd class="col-sm-7"><?= date('d/m/Y H:i', strtotime($admin['created_at'])) ?></dd>
                    </dl>
                </div>
                <div class="profile-actions">
                    <a href="edit-admin.php?id=<?= $admin_id ?>" class="btn btn-coffee">
                        <i class="fas fa-edit me-1"></i> Chỉnh sửa thông tin
                    </a>
                    <a href="logout.php" class="btn btn-outline-coffee ms