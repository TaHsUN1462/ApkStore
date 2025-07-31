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
      display: flex;
      justify-content: flex-start;
      align-items: center;
      flex-direction: column;
      gap: 10px;
      width: 100%;
    }
    h2 {
      text-align: center;
    }
    .search {
      width: 100%;
      max-width: 500px;
      padding: 12px;
      border-radius: 10px;
      border: 1px solid #ccc;
      display: block;
    }
    #appList {
      width: 100%;
      max-width: 600px;
      display: flex;
      justify-content: center;
      align-items: center;
      flex-direction: column;
      gap: 10px;
      margin-top: 20px;
    }
    .app {
      width: 100%;
      background: #fff;
      border-radius: 10px;
      padding: 12px 15px;
      display: flex;
      align-items: center;
      gap: 15px;
      box-shadow: 0 4px 8px rgba(0,0,0,0.05);
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
    #appPage {
      background: #fff;
      padding: 20px;
      border-radius: 10px;
      box-shadow: 0 4px 10px rgba(0,0,0,0.1);
      position: fixed;
      right: 20px;
      left: 20px;
      top: 20px;
      display: flex;
      justify-content: center;
      align-items: center;
      flex-direction: column;
      gap: 10px;
      transform: translateX(110%);
      pointer-events: none;
    }
    #appPage.shown{
      transform: translateX(0);
      pointer-events: auto;
    }
    #appPage img {
      width: 100px;
      height: 100px;
      object-fit: cover;
      border-radius: 10px;
      display: block;
    }
    button, a button {
      display: block;
      padding: 12px 20px;
      background: #4CAF50;
      border: none;
      border-radius: 8px;
      color: white;
      text-align: center;
      text-decoration: none;
    }
    button:hover, a button:hover {
      background: #3c9f42;
    }
    button.back {
      all: unset;
      position: fixed;
      left: 10px;
      top: 10px;
      width: 40px;
      height: 40px;
      background: #007eff;
      display: flex;
      justify-content: center;
      align-items: center;
      font-size: 1rem;
      border-radius: 10px;
      color: white;
    }
    button.back:hover {
      background: #007ebf;
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
      #appPage img {
        width: 80px;
        height: 80px;
      }
      button, a {
        padding: 10px 16px;
        text-decoration: none;
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
        document.querySelector(".search").style.display = "none"
        let icon = 'uploads/' + id + '/icon.png';
        let apk = 'uploads/' + id + '/app.apk';
        let desc = info.desc?.trim() || 'No Description';
        appList.style.display = 'none';
        appPage.classList.add("shown")
        appPage.innerHTML = `
          <button class="back" onclick="back()">
            <svg viewBox="0 0 48 48" xmlns="http://www.w3.org/2000/svg" height="1.5em" width="1.5em"><path fill="currentColor" d="m22.35 38.95-13.9-13.9q-.25-.25-.35-.5Q8 24.3 8 24q0-.3.1-.55.1-.25.35-.5L22.4 9q.4-.4 1-.4t1.05.45q.45.45.45 1.05 0 .6-.45 1.05L13.1 22.5h24.8q.65 0 1.075.425.425.425.425 1.075 0 .65-.425 1.075-.425.425-1.075.425H13.1l11.4 11.4q.4.4.4 1t-.45 1.05q-.45.45-1.05.45-.6 0-1.05-.45Z"/></svg>
          </button>
          <h2>${info.name}</h2>
          <img src="${icon}" alt="Icon" />
          <p>${desc}</p>
          <a href="${apk}" download="${info.name}.apk"><button>Download APK</button></a>
        `;
      })
      .catch(() => alert('Failed to load app info.'));
  }

  function back() {
    appPage.classList.remove("shown")
    appList.style.display = 'block';
    document.querySelector(".search").style.display = "block"
  }
</script>
</body>
</html>