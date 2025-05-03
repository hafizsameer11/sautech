<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <title>Hosting / Logins and Licensing</title>
  <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600&display=swap">

  <style>
    body {
      margin: 0;
      font-family: 'Inter', sans-serif;
      background-color: #f9fbfc;
      color: #333;
    }

    .container {
      max-width: 960px;
      margin: 0 auto;
      padding: 60px 20px;
      display: flex;
      flex-direction: column;
      align-items: center;
    }

    .logo {
      text-align: center;
      margin-bottom: 30px;
    }

    .logo img {
      height: 120px;
      /* reduced from 400px */
    }

    .logo-text {
      font-size: 24px;
      font-weight: 600;
      color: #2c3e50;
      text-align: center;
      margin-top: 20px;
    }


    .button-wrapper {
      display: grid;
      grid-template-columns: 1fr;
      /* ✅ Only one column now */
      gap: 20px;
      margin-top: 40px;
      width: 30%;
      margin: auto;
      max-width: 600px;
    }


    @media (max-width: 600px) {
      .button-wrapper {
        grid-template-columns: 1fr;
      }
    }

    .nav-button {
      background-color: #1abc9c;
      color: white;
      padding: 14px 28px;
      border: none;
      border-radius: 8px;
      font-size: 15px;
      min-width: 180px;
      text-align: center;
      text-decoration: none;
      box-shadow: 0 2px 6px rgba(0, 0, 0, 0.1);
      transition: all 0.3s ease;
    }

    .nav-button:hover {
      background-color: #16a085;
      transform: translateY(-2px);
    }
  </style>
</head>

<body>
  <?php
  // include_once '../../assets/sections/header.php'
  ?>
  <div class="container">
    <div class="logo">
      <!-- <img src="images/ST.png" alt="Sautech Logo"> -->
      <div class="logo-text">Manage Hosting, Logins & Licensing</div>
    </div>
    <div class="button-wrapper">
      <a href="hosting.php" class="nav-button">Hosting</a>
      <a href="login/register.php" class="nav-button">Logins</a>
      <a href="../spla/index.php" class="nav-button">SPLA Licensing</a>
      <a href="../device/index.php" class="nav-button">Devices</a>
    </div>

  </div>
</body>

</html>