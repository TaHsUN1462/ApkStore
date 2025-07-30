<?php
$apps = array_filter(glob('uploads/*'), 'is_dir');
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>APK Store</title>
  <style>
    * { box-sizing: border-box; margin: 0; padding: 0; }
    body {
      font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
      background: #f5f5f5;
      color: #333;
      padding: 20px;
      min-height: 100vh;
    }
    h2 {
      margin-bottom: 15px;
      text-align: center;
    }
    .search {
      width: 100%;
      max-width: 500px;
      margin: 0 auto 25px auto;
      padding: 12px;
      font-size: 16px;
      border-radius: 10px;
      border: 1px solid #ccc;
      display: block;
    }
    #appList {
      max-width: 600px;
      margin: 0 auto;
    }
    .app {
      background: #fff;
      border-radius: 10px;
      padding: 12px 15px;
      display: flex;
      align-items: center;
      gap: 15px;
      box-shadow: 0 4px 8px rgba(0,0,0,0.05);
      margin-bottom: 12px;
      cursor: pointer;
      transition: transform 0.1s ease-in-out;
    }
    .app:hover {
      transform: scale(1.02);
      background: #f0f0f0;
    }
    .app img {
      width: 60px;
      height: 60px;
      object-fit: cover;
      border-radius: 8px;
      flex-shrink: 0;
    }
    .app b {
      font-size: 18px;
      user-select: none;
    }
    #appPage {
      max-width: 600px;
      margin: 0 auto;
      background: #fff;
      padding: 20px;
      border-radius: 10px;
      box-shadow: 0 4px 10px rgba(0,0,0,0.1);
      display: none;
    }
    #appPage img {
      width: 100px;
      height: 100px;
      object-fit: cover;
      border-radius: 10px;
      display: block;
      margin: 15px auto;
    }
    button, a button {
      display: block;
      margin: 15px auto 0 auto;
      padding: 12px 20px;
      background: #4CAF50;
      border: none;
      border-radius: 8px;
      font-size: 16px;
      color: white;
      cursor: pointer;
      text-align: center;
      text-decoration: none;
    }
    button:hover, a button:hover {
      background: #3c9f42;
    }
    button.back {
      background: #555;
      margin-bottom: 20px;
    }
    button.back:hover {
      background: #444;
    }
    @media (max-width: 600px) {
      .app {
        gap: 10px;
        padding: 10px;
      }
      .app img {
        width: 50px;
        height: 50px;
      }
      .app b {
        font-size: 16px;
      }
      #appPage img {
        width: 80px;
        height: 80px;
      }
      button, a button {
        font-size: 14px;
        padding: 10px 16px;
      }
    }
  </style>
</head>
<body>
  <h2>APK Store</h2>
  <input class="search" type="text" placeholder="Search apps..." oninput="filterApps(this.value)" />

  <div id="appList">
    <?php foreach ($apps as $path):
      $info = json_decode(file_get_contents("$path/info.json"), true);
      $icon = "$path/icon.png";
      $id = basename($path);
    ?>
    <div class="app" onclick="openApp('<?= $id ?>')" data-name="<?= strtolower($info['name']) ?>">
      <img src="<?= $icon ?>" alt="Icon" />
      <b><?= htmlspecialchars($info['name']) ?></b>
    </div>
    <?php endforeach; ?>
  </div>

  <div id="appPage"></div>

<script>
  const appList = document.getElementById('appList');
  const appPage = document.getElementById('appPage');

  function filterApps(q) {
    q = q.toLowerCase();
    document.querySelectorAll('.app').forEach(app => {
      const name = app.dataset.name;
      app.style.display = name.startsWith(q) ? 'flex' : 'none';
    });
  }

  function openApp(id) {
    fetch('uploads/' + id + '/info.json')
      .then(res => res.json())
      .then(info => {
        let icon = 'uploads/' + id + '/icon.png';
        let apk = 'uploads/' + id + '/app.apk';
        let desc = info.desc?.trim() || 'No Description';
        appList.style.display = 'none';
        appPage.style.display = 'block';
        appPage.innerHTML = `
          <button class="back" onclick="back()">← Back</button>
          <h2>${info.name}</h2>
          <img src="${icon}" alt="Icon" />
          <p>${desc}</p>
          <a href="${apk}" download="${info.name}.apk"><button>Download APK</button></a>
        `;
      })
      .catch(() => alert('Failed to load app info.'));
  }

  function back() {
    appPage.style.display = 'none';
    appList.style.display = 'block';
  }
</script>
</body>
</html>