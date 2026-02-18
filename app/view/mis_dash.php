<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Admin Dashbaord</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

</head>



<!--
    Client-side live search filter.
    Filters visible user rows based on text typed into #search input.
    Performs case-insensitive substring match against row text.
-->
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
<script>
  // Attach keyup event to search box to dynamically filter users
$(document).ready(function () {
    $("#search").on("keyup", function () {
        var searchTerm = $(this).val().toLowerCase();
        $(".all-users").each(function () {
            var text = $(this).text().toLowerCase();
            if (text.indexOf(searchTerm) === -1) {
                $(this).hide();
            } else {
                $(this).show();
            }
        });
    });
});
</script>


<body class="bg-light p-4">

<!--
    Top Navigation Bar
    - Displays logged-in user name
    - Role-based navigation options:
        Admin (3)   -> History + Manager Panel
        Manager (2) -> Manager Panel
    - Back To Dashboard
    - Logout
-->
<nav class="navbar navbar-expand-lg navbar-dark bg-dark mb-4">
  <div class="container-fluid">
    <a class="navbar-brand" href="#">MIS Admin Hub</a>

    <div class="d-flex align-items-center gap-2">
      <span class="navbar-text text-white me-3">
        Welcome <?= htmlspecialchars($_SESSION['fname'] ?? '') ?>!
      </span>

      <?php if (!empty($_SESSION['role']) && $_SESSION['role'] === 3): ?>
        <a class="btn btn-sm btn-outline-warning" href="/history">History</a>
      <?php endif; ?>

      <?php if (!empty($_SESSION['role']) && ($_SESSION['role'] === 2 || $_SESSION['role'] === 3)): ?>
        <a class="btn btn-sm btn-outline-warning" href="/userPanel">Manager Dash</a>
      <?php endif; ?>

      <div class="vr bg-secondary mx-2"></div>

      <a class="btn btn-sm btn-outline-light" href="/dashboard">Dashboard</a>
      <a class="btn btn-sm btn-outline-light" href="/logout">Logout</a>
    </div>
  </div>
</nav>

<!--Show any errors returned from the controller and then clear them -->
<?php
  $errors = $_SESSION['error'] ?? [];
  if (!empty($errors)) {
    echo "<div class='alert alert-danger'>$errors</div>";
  }
  unset($_SESSION['error']);
?>

<!--
    Active Users Table
    Allows inline editing of:
    - Role
    - Manager
    - Department
    Each row submits independently to edituser/submit url on the router.
-->
<div class="card shadow-sm">
  <div class="card-header bg-white">
    <div class="d-flex justify-content-between align-items-center">
      <h5 class="mb-0">Active Users</h5>

      <div class="input-group input-group-sm w-25">
        <span class="input-group-text">🔍</span>
        <input
          type="text"
          class="form-control"
          id="search"
          placeholder="Search users..."
        >
      </div>
    </div>
  </div>

  <div class="card-body p-0">
    <div class="table-responsive">
      <table class="table table-hover table-striped align-middle mb-0">
        <thead class="table-light">
          <tr>
            <th>Name</th>
            <th>Email</th>
            <th>Role</th>
            <th>Manager</th>
            <th>Department</th>
            <th class="text-end">Actions</th>
          </tr>
        </thead>

        <tbody>
        <!--
        * Iterates through all active users and renders editable row.
        * Each row contains its own POST form for updating that user.
        -->
          <?php foreach ($users as $user): ?>
          <tr class="all-users">
            <form method="POST" action="edituser/submit">

              <td>
                <?= htmlspecialchars($user['first_name']) ?>
                <?= htmlspecialchars($user['last_name']) ?>
              </td>

              <td><?= htmlspecialchars($user['email']) ?></td>

              <td>
                <!-- Role selector (0=User, 1=Manager, 3=Admin) -->
                <select name="role" class="form-select form-select-sm" required>
                  <option value="0" <?= $user['role'] === 0 ? 'selected' : '' ?>>User</option>
                  <option value="1" <?= $user['role'] === 1 ? 'selected' : '' ?>>Manager</option>
                  <option value="3" <?= $user['role'] === 3 ? 'selected' : '' ?>>Admin</option>
                </select>
              </td>

              <td>
                <!--
                    Manager assignment dropdown.
                    First option = current manager.
                    Remaining options populated from $managers array.
                -->
                <select name="manager_select" class="form-select form-select-sm">
                  <?php $manager = $user['manager'] ?? ''; ?>
                  <option value="<?= htmlspecialchars($manager) ?>">
                    <?= htmlspecialchars($manager) ?>
                  </option>

                  <?php foreach ($managers as $managerRow): ?>
                    <?php
                      $email = $managerRow['email'];
                      $selected = ($email == ($selectedValue ?? '')) ? 'selected' : '';
                    ?>
                    <option value="<?= htmlspecialchars($email) ?>" <?= $selected ?>>
                      <?= htmlspecialchars($email) ?>
                    </option>
                  <?php endforeach; ?>
                </select>
              </td>

              <td>
                <!-- Department selection list (static organizational values) -->
                
                <select name="dept" class="form-select form-select-sm">
                  <?php $dept = $user['dept'] ?? '' ?>;
                  <option value="<?= htmlspecialchars($dept) ?>">
                    <?= htmlspecialchars($dept) ?>
                  </option>
                  <option value="ADRC">ADRC</option>
                  <option value="CQA">CQA</option>
                  <option value="Fiscal">Fiscal</option>
                  <option value="HR">HR</option>
                  <option value="I&R">I&R</option>
                  <option value="LTC">LTC</option>
                  <option value="OCS">OCS</option>
                  <option value="MIS">MIS</option>
                  <option value="Office">Office</option>
                  <option value="Shine">Shine</option>
                  <option value="VA">VA</option>
                  <option value="MGMT">MGMT</option>
                </select>
              </td>

              <td class="text-end">
                <!--
                    User Actions:
                    - Update  : Submits role/manager/dept changes
                    - Reset   : Opens password reset modal
                    - Delete  : Opens permanent delete confirmation modal
                -->
                <input type="hidden" name="user_id" value="<?= $user['user_id'] ?>">

                <div class="btn-group btn-group-sm" role="group">
                  <button type="submit" class="btn btn-primary">Update</button>


                  <button
                  
                    type="button"
                    onclick="populateEmail('<?= $user['email'] ?>')"
                    class="btn btn-success"
                    data-bs-toggle="modal"
                    data-bs-target="#changePass">
                    Reset
                  </button>

                  <button
                    type="button"
                    onclick="populateDelete(
                      '<?= $user['first_name'] ?>',
                      '<?= $user['last_name'] ?>',
                      '<?= $user['email'] ?>'
                    )"
                    class="btn btn-danger"
                    data-bs-toggle="modal"
                    data-bs-target="#deleteUser">
                    Delete
                  </button>
                </div>
              </td>

            </form>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>


  
<!--
    Change Password Modal
    Populated dynamically with selected user's email.
    Submits POST to /change_password.
-->
<div class="modal fade" id="changePass" tabindex="-1" aria-labelledby="changePassLabel" aria-hidden="true">
  <div class="modal-dialog">
    <form class="modal-content" method="POST" action="/change_password">
      <div class="modal-header">
        <h5 class="modal-title" id="changePassLabel">Change Password</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      
      <div class="modal-body">
          <div class="mb-3">
          <label for="email" class="form-label">User email</label>
          <input type="text" name="email" class="form-control" id="getEmail" required>          
        </div>
        <div class="mb-3">
          <label for="newPassword" class="form-label">New Password</label>
          <input type="password" name="newPassword" class="form-control" id="newPassword" required>
          
        </div>
      </div>

      <div class="modal-footer">
        <button type="submit" class="btn btn-primary">Update Password</button>
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
      </div>
    </form>
  </div>
</div>    
<!--
    Permanent Delete User Modal
    Displays selected user info for confirmation.
    Submits POST to /delete_user_perm.
    WARNING: Permanent removal.
-->
<div class="modal fade" id="deleteUser" tabindex="-1">
  <div class="modal-dialog">
    <form class="modal-content" method="POST" action="/delete_user_perm">
      <div class="modal-header">
        <h5 class="modal-title" id="confirmDelete">Delete User</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      
      <div class="modal-body">
        
        <div class="mb-3">
          <label for="user-delete" class="form-label">User Name</label>
          <input type="text" name="user-delete" class="form-control" id="getDeleteName">          
        </div>
        <div class="mb-3">
          <label for="email-delete" class="form-label">User email</label>
          <input type="text" name="email-delete" class="form-control" id="getDeleteEmail">          
        </div>

      </div>

      <div class="modal-footer">
        <button type="submit" class="btn btn-primary">Delete User</button>
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
      </div>
    </form>
  </div>
</div>  
	  
  

<!--
    Helper functions used to populate modal form fields
    with selected user information before modal opens.
-->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

<script>
// Populate Change Password modal email field
function populateEmail(email) {
    document.querySelector('#getEmail').value = email;
}	

// Populate Delete User modal name + email fields
function populateDelete(fname, lname, email) {
    
    document.querySelector('#getDeleteName').value = fname + " " + lname;
    document.querySelector('#getDeleteEmail').value = email;
}	
</script>
</body>
</html>