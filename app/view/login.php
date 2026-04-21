<!DOCTYPE html>
<html lang="en">
<?php include __DIR__ . '/head.php'; ?>
<?php include 'header.php'; ?>

<body class="d-flex justify-content-center align-items-center vh-100">
  <div class="card login-card shadow p-4" style="max-width: 400px; width: 100%;">
    <?php 
    $errors = $_SESSION['error'] ?? [];
    if (!empty($errors)) echo "<p class='text-danger'>$errors</p>"; 
    unset($_SESSION['error']);
    ?>
        
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
