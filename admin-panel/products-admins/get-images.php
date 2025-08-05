<?php

require "../../config/config.php";
requireAdminLogin();

header('Content-Type: application/json');

$imagesDirectory = $_SERVER['DOCUMENT_ROOT'] . "/Coffee-Shop/images/";
$webPath = APPURL . "/images/";

$images = [];

// Kiểm tra thư mục có tồn tại
if (is_dir($imagesDirectory)) {
    $files = scandir($imagesDirectory);

    foreach ($files as $file) {
        // Bỏ qua thư mục . và ..
        if ($file === '.' || $file === '..') {
            continue;
        }

        $extension = strtolower(pathinfo($file, PATHINFO_EXTENSION));
        $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];

        // Chỉ lấy các file hình ảnh
        if (in_array($extension, $allowedExtensions) && !is_dir($imagesDirectory . $file)) {
            $images[] = [
                'name' => $file,
                'path' => $webPath . $file
            ];
        }
    }
}

// Sắp xếp theo tên file, để ảnh mới nhất lên trên (nếu đặt tên có timestamp)
usort($images, function ($a, $b) {
    return strcmp($b['name'], $a['name']);
});

echo json_encode($images);
