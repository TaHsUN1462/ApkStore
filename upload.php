<?php
// Delete app folder
if (isset($_POST['delete'])) {
  $id = basename($_POST['delete']);
  $path = "uploads/$id";
  if (is_dir($path)) {
    $files = glob("$path/*");
    foreach ($files as $file) {
      if (is_file($file)) unlink($file);
    }
    rmdir($path);
  }
  header("Location: admin.php");
  exit;
}

// Upload or edit app
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['name'])) {
  $name = trim($_POST['name']);
  $desc = trim($_POST['desc'] ?? '');

  if (empty($name)) {
    die('App name is required.');
  }

  $editId = $_POST['edit_id'] ?? null;

  if ($editId) {
    // Edit existing app
    $id = basename($editId);
    $dir = "uploads/$id";
    if (!is_dir($dir)) {
      die('App folder not found.');
    }

    // Replace APK if uploaded
    if (!empty($_FILES['apk']['name'])) {
      if ($_FILES['apk']['error'] !== UPLOAD_ERR_OK || pathinfo($_FILES['apk']['name'], PATHINFO_EXTENSION) !== 'apk') {
        die('Invalid APK file.');
      }
      move_uploaded_file($_FILES['apk']['tmp_name'], "$dir/app.apk");
    }

    // Replace icon if uploaded
    if (!empty($_FILES['icon']['name'])) {
      if ($_FILES['icon']['error'] !== UPLOAD_ERR_OK) {
        die('Invalid icon file.');
      }
      move_uploaded_file($_FILES['icon']['tmp_name'], "$dir/icon.png");
    }

    // Update info.json
    $data = [
      'name' => $name,
      'desc' => $desc,
    ];
    file_put_contents("$dir/info.json", json_encode($data));
    header("Location: admin.php");
    exit;

  } else {
    // New upload
    if (!isset($_FILES['apk'], $_FILES['icon'])) {
      die('APK and icon files are required.');
    }

    if ($_FILES['apk']['error'] !== UPLOAD_ERR_OK || pathinfo($_FILES['apk']['name'], PATHINFO_EXTENSION) !== 'apk') {
      die('Invalid APK file.');
    }
    if ($_FILES['icon']['error'] !== UPLOAD_ERR_OK) {
      die('Invalid icon file.');
    }

    $id = uniqid('app_');
    $dir = "uploads/$id";
    if (!mkdir($dir, 0755, true)) {
      die('Failed to create app folder.');
    }

    if (!move_uploaded_file($_FILES['apk']['tmp_name'], "$dir/app.apk")) {
      die('Failed to save APK.');
    }
    if (!move_uploaded_file($_FILES['icon']['tmp_name'], "$dir/icon.png")) {
      die('Failed to save icon.');
    }

    $data = [
      'name' => $name,
      'desc' => $desc,
    ];
    file_put_contents("$dir/info.json", json_encode($data));

    header("Location: admin.php");
    exit;
  }
}

header("Location: admin.php");
exit;