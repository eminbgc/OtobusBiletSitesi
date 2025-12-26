<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <title>Bilet Sorgulama Girişi</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

<a href="index.php" class="top-right-btn" style="background-color:#e74c3c; border-color:#e74c3c;">Ana Sayfa</a>

<div class="container" style="max-width: 400px;">
    <h2>🎫 Bilet Sorgula</h2>
    <p>Biletinizi görüntülemek, iptal etmek veya değiştirmek için bilgilerinizi giriniz.</p>
    <hr>
    
    <form action="kod_gonder.php" method="POST">
        <input type="hidden" name="islem" value="sorgula">
        
        <div style="display:flex; flex-direction:column; gap:15px; margin-top:20px;">
            <label style="text-align:left; font-weight:bold;">Adınız:</label>
            <input type="text" name="ad" required style="width:100%;">
            
            <label style="text-align:left; font-weight:bold;">Soyadınız:</label>
            <input type="text" name="soyad" required style="width:100%;">
            
            <label style="text-align:left; font-weight:bold;">E-Posta Adresi:</label>
            <input type="email" name="email" required style="width:100%;">
            
            <br>
            <button type="submit" class="btn" style="width:100%; background-color:#3498db;">Sorgula</button>
        </div>
    </form>
</div>

</body>
</html>