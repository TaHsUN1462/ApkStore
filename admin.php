<?php
session_start();
$correct_password = '1234';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['password'])) {
  if ($_POST['password'] === $correct_password) {
    $_SESSION['logged_in'] = true;
    header("Location: admin.php");
    exit;
  }
}

if (isset($_POST['logout'])) {
  session_destroy();
  header("Location: admin.php");
  exit;
}

$authed = isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true;
$apps = [];
if ($authed) {
  $apps = array_filter(glob('uploads/*'), 'is_dir');
}

$editingApp = null;
if ($authed && isset($_GET['edit'])) {
  $editId = basename($_GET['edit']);
  $editPath = "uploads/$editId";
  if (is_dir($editPath) && file_exists("$editPath/info.json")) {
    $editingApp = json_decode(file_get_contents("$editPath/info.json"), true);
    $editingApp['id'] = $editId;
  }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1" />
<title>Admin - APK Store</title>
<style>
@import url('https://fonts.googleapis.com/css2?family=Arimo:ital,wght@0,400..700;1,400..700&display=swap');
    * { 
      box-sizing: border-box; 
      margin: 0; 
      padding: 0; 
      transition: 200ms ease all; 
      font-family: "Arimo", Sans-Serif;
    }
  body {
    background: #f5f5f5;
    color: #333;
    padding: 20px;
    height: 100dvh;
  }
  h2 {
    margin-bottom: 15px;
    text-align: center;
  }
  form {
    max-width: 600px;
    margin: 20px auto;
    background: #fff;
    padding: 20px;
    border-radius: 10px;
    box-shadow: 0 4px 10px rgba(0,0,0,0.1);
  }
  input[type="password"], input[type="text"], textarea, input[type="file"] {
    width: 100%;
    padding: 12px;
    font-size: 16px;
    border-radius: 10px;
    border: 1px solid #ccc;
    margin-bottom: 15px;
    resize: vertical;
    display: block;
  }
  label {
    font-weight: 600;
    margin-bottom: 6px;
    display: block;
    font-size: 14px;
  }
  button {
    padding: 12px 20px;
    background: #4CAF50;
    border: none;
    border-radius: 8px;
    color: white;
    font-size: 16px;
    
    transition: background 0.3s;
    display: block;
    width: 100%;
  }
  button:hover {
    background: #3c9f42;
  }
  .logout-form{
    all: unset;
  }
  .logout-btn {
    width: max-content;
    position: absolute;
    top: 5px;
    right: 10px;
    padding: 8px 16px;
    background: #b33;
  }
  .logout-btn:hover {
    background: #900;
  }
  .app-list {
    max-width: 600px;
    margin: 20px auto;
    display: flex;
    justify-content: center;
    align-items: center;
    flex-direction: column;
  }
  .app-row {
    width: 100%;
    background: #fff;
    border-radius: 10px;
    box-shadow: 0 4px 8px rgba(0,0,0,0.05);
    padding: 12px 15px;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-direction: column;
    gap: 15px;
  }
  .app-row img {
    justify-self: center;
    width: 50px;
    height: 50px;
    border-radius: 8px;
    object-fit: cover;
  }
  .app-row b {
    flex-grow: 1;
    font-size: 18px;
    user-select: none;
    min-width: 150px;
  }
  .app-row form {
    margin: 0;
    display: inline-flex;
    background: none;
    padding: 0;
    border: none;
  }
  .app-row button {
    padding: 8px 14px;
    font-size: 14px;
    margin-left: 10px;
    border-radius: 8px;
    white-space: nowrap;
  }
  .app-row button.edit-btn {
    width: 100%;
    height: 50px;
    background: #2196F3;
  }
  .app-row button.edit-btn:hover {
    background: #1976D2;
  }
  .app-row button.delete-btn {
    height: 50px;
    width: 100%;
    background: #e53935;
  }
  .app-row button.delete-btn:hover {
    background: #b71c1c;
  }
input:focus{
  outline: 1px solid #000;
}
  /* Responsive tweaks */
  @media (max-width: 480px) {
    .app-row {
      flex-direction: column;
      align-items: flex-start;
    }
    .app-row form {
      margin-top: 10px;
      gap: 10px;
      width: 100%;
    }
    .app-row button {
      margin-left: 0;
      width: 48%;
    }
    .app-row b {
      font-size: 16px;
      min-width: auto;
      width: 100%;
    }
  }
</style>
</head>
<body>

<?php if (!$authed): ?>
  <form method="post" autocomplete="off" novalidate>
    <h2>Admin Login</h2>
    <label for="password">Enter Password</label>
    <input type="password" id="password" name="password" placeholder="Password" required />
    <button type="submit">Login</button>
  </form>
<?php else: ?>

  <form method="post" class="logout-form" style="text-align:center;">
    <button type="submit" name="logout" class="logout-btn">Logout</button>
  </form>

  <?php if ($editingApp): ?>
    <form action="upload.php" method="post" enctype="multipart/form-data" autocomplete="off" novalidate>
      <h2>Edit App: <?=htmlspecialchars($editingApp['name'])?></h2>

      <input type="hidden" name="edit_id" value="<?=htmlspecialchars($editingApp['id'])?>" />

      <label for="name">App Name</label>
      <input type="text" id="name" name="name" required value="<?=htmlspecialchars($editingApp['name'])?>" />

      <label for="desc">Description (optional)</label>
      <textarea id="desc" name="desc"><?=htmlspecialchars($editingApp['desc'])?></textarea>

      <label for="apk">Replace APK File (leave empty to keep)</label>
      <input type="file" id="apk" name="apk" accept=".apk" />

      <label for="icon">Replace App Icon (leave empty to keep)</label>
      <input type="file" id="icon" name="icon" accept="image/*" />

      <button type="submit">Save Changes</button>
    </form>
    <p style="text-align:center; margin-top: 10px;">
      <a href="admin.php" style="color:#2196F3; text-decoration:none;">Cancel Edit</a>
    </p>

  <?php else: ?>
    <form action="upload.php" method="post" enctype="multipart/form-data" autocomplete="off" novalidate>
      <h2>Upload New App</h2>

      <label for="name">App Name</label>
      <input type="text" id="name" name="name" required placeholder="App Name" />

      <label for="desc">Description (optional)</label>
      <textarea id="desc" name="desc" placeholder="App Description"></textarea>

      <label for="apk">APK File (.apk)</label>
      <input type="file" id="apk" name="apk" accept=".apk" required />

      <label for="icon">App Icon (image)</label>
      <input type="file" id="icon" name="icon" accept="image/*" required />

      <button type="submit">Upload</button>
    </form>

    <div class="app-list">
      <h2>Uploaded Apps</h2>
      <?php if (empty($apps)): ?>
        <p style="text-align:center; color:#666;">No apps uploaded yet.</p>
      <?php else: ?>
        <?php foreach ($apps as $path):
          $info = json_decode(file_get_contents("$path/info.json"), true);
          $icon = "$path/icon.png";
          $id = basename($path);
        ?>
        <div class="app-row">
          <img src="<?= $icon ?>" alt="App Icon" />
          <b><?= htmlspecialchars($info['name']) ?></b>

          <!-- Edit button -->
          <form method="get" action="admin.php" style="display:inline;">
            <input type="hidden" name="edit" value="<?= $id ?>" />
            <button type="submit" class="edit-btn">Edit</button>
          </form>

          <!-- Delete button -->
          <form action="upload.php" method="post" style="display:inline;" onsubmit="return confirm('Delete this app?');">
            <input type="hidden" name="delete" value="<?= $id ?>" />
            <button type="submit" class="delete-btn">Delete</button>
          </form>
        </div>
        <?php endforeach; ?>
      <?php endif; ?>
    </div>
  <?php endif; ?>

<?php endif; ?>

</body>
</html>