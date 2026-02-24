<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Login</title>
  <!-- Bootstrap 5 CSS CDN -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <style>
    /* Background Gradient */
    body {
      background: linear-gradient(135deg, #6f42c1, #fd7e14);
      font-family: 'Arial', sans-serif;
    }
    /* Custom Card Styling */
    .login-card {
      background: #fff;
      border-radius: 10px;
      box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
    }
    /* Button Hover Effects */
    .btn-primary:hover {
      background-color: #0056b3;
    }
    /* Form Field with Icons */
    .input-icon {
      position: absolute;
      top: 10px;
      left: 10px;
      padding: 5px;
      color: #aaa;
    }
    .form-control {
      padding-left: 30px;
    }
    .input-group-text {
      background-color: transparent;
      border: none;
    }

  </style>
</head>
<?php include 'header.php'; ?>
<body class="d-flex justify-content-center align-items-center vh-100">
  <div class="card login-card shadow p-4" style="max-width: 400px; width: 100%;">
    <?php 
    $errors = $_SESSION['error'] ?? [];
    if (!empty($errors)) echo "<p class='text-danger'>$errors</p>"; 
    unset($_SESSION['error']);
    ?>
    <h3 class="text-center mb-3"></h3>
    
    <form action="login/submit" method="POST">
      <div class="mb-3 position-relative">
        <label for="email" class="form-label">Email</label>
        <input type="text" class="form-control" id="email" name="email" required />
        <span class="input-icon"><i class="bi bi-envelope"></i></span>
      </div>
      <div class="mb-3 position-relative">
        <label for="password" class="form-label">Password</label>
        <input type="password" class="form-control" id="password" name="password" required />
        <span class="input-icon"><i class="bi bi-lock"></i></span>
      </div>
      <button type="submit" class="btn btn-primary w-100">Log In</button>
    </form>

    <div class="text-center mt-3">
      <small>Don't have an account? <a href="/register">Sign Up</a></small>
    </div>
  </div>
</body>
<?php include 'footer.php'; ?>
</html>
