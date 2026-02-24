<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Register</title>
  <!-- Bootstrap 5 CSS CDN -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
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
<?php include 'header.php'; ?>
<body class="bg-light d-flex justify-content-center align-items-center vh-100">
  <div class="card shadow p-4" style="max-width: 450px; width: 100%;">
          <?php 
    $errors = $_SESSION['error'] ?? [];
    if (!empty($errors)) echo "<p style='color:red'>$errors</p>"; 
    unset($_SESSION['error']);?>
    
    <form action="register/submit" method="POST">
            <div class="mb-3">
        <label for="fname" class="form-label">First name</label>
        <input type="text" class="form-control" id="fname" name="fname" required />
      </div>
            <div class="mb-3">
        <label for="lname" class="form-label">Last name</label>
        <input type="text" class="form-control" id="lname" name="lname" required />
      </div>
      <div class="mb-3">
        <label for="email" class="form-label">Email</label>
        <input type="text" class="form-control" id="email" name="email" required />
      </div>
      <div class="mb-3">
        <label for="password" class="form-label">Password</label>
        <input type="password" class="form-control" id="password" name="password" required />
      </div>
      <div class="mb-3">
        <label for="confirmPassword" class="form-label">Confirm Password</label>
        <input type="password" class="form-control" id="confirmPassword" name="confirm_password" required />
      </div>
      <button type="submit" class="btn btn-success w-100">Register</button>
    </form>
    <div class="text-center mt-3">
      <small>Already have an account? <a href="/login">Login here</a></small>
    </div>
  </div>

  <!-- Bootstrap JS (Optional) -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
<?php include 'footer.php'; ?>
</html>