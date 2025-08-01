<!DOCTYPE html>
<html lang="tr">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1" />
<title>CreatiWall - Dijital İçerik Yönetimi</title>
<style>
  @import url('https://fonts.googleapis.com/css2?family=Montserrat:wght@500;700&display=swap');

  body, html {
    margin: 0; padding: 0; height: 100%;
    font-family: 'Montserrat', sans-serif;
    background-color: #ffc000;
    color: #222;
  }

  .container {
    max-width: 960px;
    margin: 0 auto;
    padding: 20px 15px;
    height: 100%;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
  }

  header {
    margin-bottom: 40px;
    background: #ffc000;
    padding: 15px 10px 10px;
    border-radius: 12px;
    box-shadow: 0 6px 15px rgba(0,0,0,0.1);
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    gap: 10px;
  }

  header img {
    width: 320px;
    height: auto;
    display: block;
  }

  header h1 {
    font-weight: 700;
    font-size: 2.5rem;
    margin: 0;
    color: #222;
    text-align: center;
  }

  header p {
    font-weight: 500;
    font-size: 1.125rem;
    margin: 0;
    color: #222;
    text-align: center;
  }

  .features {
    display: flex;
    gap: 24px;
    flex-wrap: wrap;
    justify-content: center;
    width: 100%;
  }

  .feature-card {
    background: white;
    border-radius: 14px;
    box-shadow: 0 8px 20px rgba(0,0,0,0.08);
    padding: 25px 15px;
    flex: 1 1 240px;
    max-width: 280px;
    text-align: center;
    transition: transform 0.2s ease;
    cursor: default;
  }

  .feature-card:hover {
    transform: translateY(-6px);
    box-shadow: 0 10px 25px rgba(0,0,0,0.15);
  }

  .feature-icon {
    font-size: 48px;
    margin-bottom: 16px;
    color: #ffc000;
  }

  .feature-title {
    font-weight: 700;
    font-size: 1.2rem;
    margin-bottom: 8px;
  }

  .feature-desc {
    font-weight: 500;
    font-size: 0.95rem;
    color: #555;
  }

  .login-btn {
    margin-top: 40px;
    background: #222;
    color: white;
    font-weight: 700;
    font-size: 1.1rem;
    text-decoration: none;
    padding: 12px 28px;
    border-radius: 8px;
    display: inline-block;
    transition: background 0.3s ease;
  }

  .login-btn:hover {
    background: #444;
  }

  @media (max-width: 480px) {
    header img {
      width: 240px;
    }
    .features {
      flex-direction: column;
      gap: 16px;
    }
  }
</style>
<!-- FontAwesome CDN for icons -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />
</head>
<body>
  <div class="container">
    <header>
      <img src="uploads/creatiwall dolgulu beyaz.png" alt="CreatiWall Logo" />
      <h1>CreatiWall - Dijital İçerik Yönetimi</h1>
      <p>Ekranlarınız için kolay, güçlü ve özelleştirilebilir içerik yönetim sistemi</p>
    </header>

    <section class="features">
      <div class="feature-card">
        <div class="feature-icon"><i class="fas fa-desktop"></i></div>
        <div class="feature-title">Çoklu Ekran Yönetimi</div>
        <div class="feature-desc">Birden fazla ekranınızı tek yerden kolayca yönetin.</div>
      </div>
      <div class="feature-card">
        <div class="feature-icon"><i class="fas fa-list-ul"></i></div>
        <div class="feature-title">Playlist Desteği</div>
        <div class="feature-desc">Medya dosyalarınızı kolayca playlistlere ekleyin ve yönetin.</div>
      </div>
      <div class="feature-card">
        <div class="feature-icon"><i class="fas fa-upload"></i></div>
        <div class="feature-title">Kolay Medya Yükleme</div>
        <div class="feature-desc">Görsellerinizi ve videolarınızı zahmetsizce yükleyin.</div>
      </div>
      <div class="feature-card">
        <div class="feature-icon"><i class="fas fa-cogs"></i></div>
        <div class="feature-title">Esnek Özelleştirme</div>
        <div class="feature-desc">Temalar ve ayarlarla kendi markanızı yaratın.</div>
      </div>
    </section>

    <a href="login.php" class="login-btn">Giriş Yap</a>
  </div>
</body>
</html>
