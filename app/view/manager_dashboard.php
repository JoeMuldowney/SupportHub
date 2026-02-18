<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Manager Dashboard</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

  <!-- script to allow j query to be used -->
  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

</head>


<body class="bg-light p-4">

<!--
    Top Navigation Bar
    - Displays logged-in user name
    - Role-based navigation options:
        Admin (3)   -> mis_dash
        Manager (1) -> Team History
    - Back to dashboard
    - Logout
-->
<nav class="navbar navbar-expand-lg navbar-dark bg-dark mb-4">
  <div class="container-fluid">
    <a class="navbar-brand" href="#">MIS Support Manger Hub</a>

    <div class="d-flex align-items-center gap-2">
      <span class="navbar-text text-white me-3">
        Welcome <?= htmlspecialchars($_SESSION['fname'] ?? '') ?>!
      </span>

      <?php if (!empty($_SESSION['role']) && $_SESSION['role'] === 3): ?>
        <a class="btn btn-sm btn-outline-warning" href="/admin">MIS</a>
      <?php endif; ?>

      <?php if (!empty($_SESSION['role']) && $_SESSION['role'] === 1): ?>
        <a class="btn btn-sm btn-outline-warning" href="/team_history">Team History</a>
      <?php endif; ?>

      <div class="vr bg-secondary mx-2"></div>

      <a class="btn btn-sm btn-outline-light" href="/dashboard">Dashboard</a>
      <a class="btn btn-sm btn-outline-light" href="/logout">Logout</a>

      <?php if (!empty($_SESSION['role']) && $_SESSION['role'] > 0): ?>
        <a class="btn btn-sm btn-success ms-2" data-bs-toggle="modal" data-bs-target="#addUserModal">
          + Add User
        </a>
      <?php endif; ?>
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

<!-- Card + Table -->
<div class="card shadow-sm">
  <div class="card-header bg-white">
    <div class="d-flex justify-content-between align-items-center">
      <h5 class="mb-0">Active Users</h5>

      <div class="input-group input-group-sm" style="width: 260px;">
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

  
<!--
    Shows all users created and actively in the system
    Each row opens independently in the view user modal.
-->

  <div class="card-body p-0">
    <div class="table-responsive">
      <table class="table table-hover table-striped align-middle mb-0">
        <thead class="table-light">
          <tr>
            <th>Name</th>
            <th>Title</th>
            <th>Department</th>
            <th class="text-end">Actions</th>
          </tr>
        </thead>

        <tbody>
          <?php foreach ($users as $user): ?>
            <tr>
              <td><?= htmlspecialchars($user['fname']) ?> <?= htmlspecialchars($user['lname']) ?></td>
              <td><?= htmlspecialchars($user['title']) ?></td>
              <td><?= htmlspecialchars($user['dept']) ?></td>

              <td class="text-end">
                <div class="btn-group btn-group-sm" role="group">
                  <button
                    type="button"
                    data-user='<?= json_encode($user, JSON_HEX_APOS | JSON_HEX_QUOT) ?>'
                    class="btn btn-primary"
                    data-bs-toggle="modal"
                    data-bs-target="#editUserModal">
                    Update
                  </button>

                  <?php if (!empty($_SESSION['role']) && $_SESSION['role'] > 0): ?>
                    <button
                      type="button"
                      class="btn btn-warning"
                      data-bs-toggle="modal"
                      data-bs-target="#deleteUserModal"
                      onclick="populateDeleteModal(
                        '<?= $user['id'] ?>',
                        '<?= $user['fname'] ?>',
                        '<?= $user['lname'] ?>',
                        '<?= $user['title'] ?>'
                      )">
                      Term
                    </button>
                  <?php endif; ?>

                  <button
                    type="button"
                    data-user='<?= json_encode($user, JSON_HEX_APOS | JSON_HEX_QUOT) ?>'
                    class="btn btn-secondary"
                    data-bs-toggle="modal"
                    data-bs-target="#viewUserModal">
                    View
                  </button>

                  <?php if (!empty($_SESSION['role']) && $_SESSION['role'] == 3): ?>
                    <button
                      type="button"
                      class="btn btn-danger"
                      data-bs-toggle="modal"
                      data-bs-target="#adminDeleteUserModal"
                      onclick="populateAdminDeleteModal(
                        '<?= $user['id'] ?>',
                        '<?= $user['fname'] ?>',
                        '<?= $user['lname'] ?>',
                        '<?= $user['title'] ?>'
                      )">
                      Delete
                    </button>
                  <?php endif; ?>
                </div>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>


<!-- Modal: Create a New Employee -->
<!-- Submits to controller route: add_new_user-->
<div class="modal fade" id="addUserModal" tabindex="-1">
  <div class="modal-dialog">
    <form class="modal-content" id="add_new_user_ticket_and_send_email" action="add_new_user" method="POST">
      <div class="modal-header">
        
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      
      <div class="modal-body">
        <!-- first page of modal -->
        <div id="step1-add" class="step-add">
        <div class="mb-3">
          <label for="fname" class="form-label">First name</label>
          <input type="text" name="fname" class="form-control" id="fname-add" required>          
        </div>
          <div class="mb-3">
          <label for="lanme" class="form-label">Last name</label>
          <input type="text" name="lname" class="form-control" id="lname-add" required>          
        </div>
        <div class="mb-3">
          <label for="pname" class="form-label">Preferred first name</label>
          <input type="text" name="pname" class="form-control" id="pname-add">          
        </div>
          <div class="mb-3">
          <label for="email" class="form-label">Email</label>
          <input type="text" name="email" class="form-control" id="email-add" required>          
        </div>
        <div class="mb-3">
          <label for="dept" class="form-label">Department</label>          
            <select name="dept" id="dept-add" class="form-control">
              <option value="">--Please choose a department--</option>
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
                </select>
            </select>         
        </div>
        <div class="mb-3">
          <label for="title" class="form-label">Title</label>
          <input type="text" name="title" class="form-control" id="title-add" required>          
        </div>
        <div class="mb-3">
          <label>Supervisor's Full Name</label>
          <input type="text" name="supervisor" class="form-control" id="supervisor-add" required>
        </div>
        <div class="mb-3">
          <label for="location">Office Cubicle</label>
          <input type="text" id="location" name="location" class="form-control"> <a href="http://sccintranet/apps/inventory/map/floorplan_view.odb" target="_blank">Click Here to View Floorplan</a>
        </div>
            <div class="mb-3">
          <label>Stare date</label>
          <input type="date" name="sdate" class="form-control" id="sdate-add" placeholder="yyyy-mm-dd" required>
        </div>
          </div>

       <!-- second page of modal -->
       <div id="step2-add" class="step-add" style="display: none;">
       <legend>Position Classification</legend>
          <div class="mb-3">  
            <label>Exempt (salary)</label>
                <input type="radio" id="workTypeSalary-add" name="workType-add" value="salary">
            
          </div>
          <div class="mb-3">
          <label>Full-Time (hourly)</label>
                <input type="radio" id = "workTypeFull-add" name="workType-add" value="Full-time">
          
          </div>
          <div class="mb-3"> 
          <label>Temporary (full-time)</label>
                <input type="radio" id="workTypeTemp-add" name="workType-add" value="temp">
          
          </div>
          <div class="mb-3"> 
          <label>Non-exempt (Hourly) Temporary</label>
                <input type="radio" id="workTypeTempHourly-add" name="workType-add" value="hourly">
          
          </div>
          <div class="mb-3"> 
          <label>Part-Time</label>
                <input type="radio" id = "workTypePart-add" name="workType-add" value="Part-time">
          
          </div>
          <div class="mb-3" id="hoursDiv-add">
                    <label>Please specify how many weekly hours: </label>
                        <input type="text" id="hours-add" placeholder='00.0' name="hours">
          </div>
          </div>

          <!-- third page of modal -->
          <div id="step3-add" class="step-add" style="display: none;">
          <legend>Check all that apply</legend>

          <div class="mb-3"> 
            <label>Avaya Cloud Office</label>
                <input type="checkbox" id="avaya" name="avaya" value="avaya">
            
            </div>
          <div class="mb-3"> 
            <label>eCIRTS</label>
                <input type="checkbox" id="ecirts" name="ecirts" value="ecirts">
            
            </div>
            <div class="mb-3"> 

            <label>DOTS</label>
                <input type="checkbox" id="dots" name="dots" value="dots">
            
            </div>
 
            <div class="mb-3">        
            <label>Shadow Agent</label>
                <input type="checkbox" id="shadowagent" name="shadowagent" value="shadowagent">
             
            </div>
 
          </div>
      </div>
   
    

<div class="modal-footer">
  <button type="button" class="btn btn-secondary" id="backBtn-add" style="display: none;">Back</button>
  <button type="button" class="btn btn-primary" id="nextBtn-add">Next</button>
  <button type="submit" class="btn btn-success" id="submitBtn-add">Submit</button>
  <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
</div>
    </form>
  </div>
</div> 

<!-- Modal: Edit An Employee -->
<!-- Submits to controller route: update_new_user-->
<div class="modal fade" id="editUserModal" tabindex="-1" >
  <div class="modal-dialog">
    <form class="modal-content" id="updateSccUser" action="update_new_user"  method="POST">       
      <div class="modal-body">
       
        <!-- first page of modal -->
        <div id="step1-update" class="step-update">
          <input type="hidden" id="id-update" name="id-update">
          <div class="mb-3">
            <label for="fname" class="form-label">First name</label>
            <input type="text" name="fname-update" class="form-control" id="fname-update">          
          </div>
          <div class="mb-3">
            <label for="lanme" class="form-label">Last name</label>
            <input type="text" name="lname-update" class="form-control" id="lname-update">          
          </div>
          <div class="mb-3">
            <label for="pname" class="form-label">Preferred first name</label>
            <input type="text" name="pname-update" class="form-control" id="pname-update">          
          </div>
          <div class="mb-3">
            <label for="email" class="form-label">Email</label>
            <input type="text" name="email-update" class="form-control" id="email-update">          
          </div>
          <div class="mb-3">
            <label for="dept" class="form-label">Department</label>
            
            <select name="dept-update" class="form-control" id="dept-update">
              <option value="">--Please choose a department--</option>
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
                </select>
            </select>            
          </div>
          <div class="mb-3">
            <label for="title" class="form-label">Title</label>
            <input type="text" name="title-update" class="form-control" id="title-update">          
          </div>
          <div class="mb-3">
            <label>Supervisor's Full Name</label>
            <input type="text" name="supervisor-update" class="form-control" id="supervisor-update">
          </div>
          <div class="mb-3">
            <label for="location">Office Cubicle</label>
            <input type="text" id="location-update" name="location-update" class="form-control"> 
          </div>
          <div class="mb-3">
            <label>Stare date</label>
            <input type="date" name="sdate-update" class="form-control" id="sdate-update" placeholder="yyyy-mm-dd">
          </div>
        </div>
            <!-- second page of edit modal -->
          <div id="step2-update" class="step-update" style="display: none;">
            <div class="mb-3">  
              <label>Exempt (salary)</label>
              <input type="radio" id="workTypeSalary-update" name="workType-update" value="salary">
            </div>
            <div class="mb-3">
              <label>Full-Time (hourly)</label>
              <input type="radio" id = "workTypeFull-update" name="workType-update" value="Full-time">
            </div>
            <div class="mb-3"> 
              <label>Temporary (full-time)</label>
              <input type="radio" id="workTypeTemp-update" name="workType-update" value="temp">
            </div>
            <div class="mb-3"> 
              <label>Non-exempt (Hourly) Temporary</label>
              <input type="radio" id="workTypeTempHourly-update" name="workType-update" value="hourly">
            </div>
            <div class="mb-3"> 
              <label>Part-Time</label>
              <input type="radio" id = "workTypePart-update" name="workType-update" value="Part-time">
            </div>
            <div class="mb-3" id="hoursDiv-update">
              <label>Please specify how many weekly hours: </label>
              <input type="text" id="hours-update" placeholder='00.0' name="hours-update">
            </div>
          </div>

          <!-- Third page of edit modal -->
            <div id="step3-update" class="step-update" style="display: none;">
              <legend>Check all that apply</legend>


              <div class="mb-3"> 
                <label>Avaya Cloud Office</label>
                <input type="checkbox" id="avaya-update" name="avaya-update" value="avaya">
              </div>
              <div class="mb-3"> 
                <label>eCIRTS</label>
                <input type="checkbox" id="ecirts-update" name="ecirts-update" value="ecirts">
              </div>
              <div class="mb-3"> 
                <label>DOTS</label>
                <input type="checkbox" id="dots-update" name="dots-update" value="dots">
              </div>

              <div class="mb-3">        
                <label>Shadow Agent</label>
                <input type="checkbox" id="shadow_agent-update" name="shadowagent-update" value="shadowagent">
              </div>
 
            </div>
        </div>     
    

<div class="modal-footer">
  <button type="button" class="btn btn-secondary" id="backBtn-update" style="display: none;">Back</button>
  <button type="button" class="btn btn-primary" id="nextBtn-update">Next</button>
  <button type="submit" class="btn btn-success" id="submitBtn-update">Submit</button>
  <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
</div>
    </form>
  </div>
</div> 

<!-- Modal: Delete An Employee -->
<!-- Submits to controller route: delete_user -->
<div class="modal fade" id="deleteUserModal" tabindex="-1" >
  <div class="modal-dialog">
    <form class="modal-content" id="deleteSccUser" action="delete_user"  method="POST">       
      <div class="modal-body">
        <input type="hidden" name="deleteid" id="deleteId">
          <div class="mb-3">
            <label for="deletefname" class="form-label">First name</label>
            <input type="text" name="deletefname" class="form-control" id="deleteFname" required>          
          </div>
          <div class="mb-3">
            <label for="deletelname" class="form-label">Last Name</label>
            <input type="text" name="deletelname" class="form-control" id="deleteLname" required>          
          </div>
          <div class="mb-3">
            <label for="deletetitle" class="form-label">Title</label>
            <input type="text" name="deletetitle" class="form-control" id="deleteTitle" required>          
          </div> 
          <div class="mb-3">
            <label for="termdate" class="form-label">Term Date</label>
            <input type="date" id="termdate" class="form-control" name="termdate">
          </div>
          <div class="mb-3">
            <label for="termtime" class="form-label">Term Time</label>
            <input type="time" id="termtime" class="form-control" name="termtime">
          </div> 
      </div>
      <div class="modal-footer">
        <button type="submit" class="btn btn-success" id="submitBtn-delete">Submit</button>
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
      </div>
    </form>
  </div>
</div>   

<!-- Modal: Delete An Employee As admin -->
<!-- Submits to controller route: delete_user -->
 <!-- Removes new user from database (useful for no shows)-->
<div class="modal fade" id="adminDeleteUserModal" tabindex="-1" >
  <div class="modal-dialog">
    <form class="modal-content" id="adminDeleteSccUser" action="admin_delete_user"  method="POST">       
      <div class="modal-body">
        <input type="hidden" name="adminDeleteId" id="adminDeleteId">
          <div class="mb-3">
            <label for="adminDeletefname" class="form-label">First name</label>
            <input type="text" name="adminDeletefname" class="form-control" id="adminDeleteFname" required>          
          </div>
          <div class="mb-3">
            <label for="adminDeleteLname" class="form-label">Last Name</label>
            <input type="text" name="adminDeleteLname" class="form-control" id="adminDeleteLname" required>          
          </div>
          <div class="mb-3">
            <label for="adminDeleteTitle" class="form-label">Title</label>
            <input type="text" name="adminDeleteTitle" class="form-control" id="adminDeleteTitle" required>          
          </div> 

      </div>
      <div class="modal-footer">
        <button type="submit" class="btn btn-success" id="submitBtn-adminDelete">Submit</button>
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
      </div>
    </form>
  </div>
</div>

<!-- Modal: Viewe An Employee -->
<!-- No submits -->
<div class="modal fade" id="viewUserModal" tabindex="-1">
  <div class="modal-dialog">
    <form class="modal-content" id="view_new_user_ticket">
      <div class="modal-header">
        
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      
      <div class="modal-body">
        <!-- first page of view modal -->
        <div id="step1-view" class="step-view">
          <input type="hidden" id="id-view" name="id-view">
          <div class="mb-3">
            <label for="fname" class="form-label">First name</label>
            <input type="text" name="fname" class="form-control" id="fname-view">          
          </div>
          <div class="mb-3">
            <label for="lanme" class="form-label">Last name</label>
            <input type="text" name="lname" class="form-control" id="lname-view">          
          </div>
          <div class="mb-3">
            <label for="pname" class="form-label">Preferred first name</label>
            <input type="text" name="pname" class="form-control" id="pname-view">          
          </div>
          <div class="mb-3">
            <label for="email" class="form-label">Email</label>
            <input type="text" name="email" class="form-control" id="email-view">          
          </div>
          <div class="mb-3">
            <label for="dept" class="form-label">Department</label>
            <input type="text" name="dept" class="form-control" id="dept-view">          
          </div>
          <div class="mb-3">
            <label for="title" class="form-label">Title</label>
            <input type="text" name="title" class="form-control" id="title-view">          
          </div>
          <div class="mb-3">
            <label>Supervisor's Full Name</label>
            <input type="text" name="supervisor" class="form-control" id="supervisor-view">
          </div>
          <div class="mb-3">
            <label for="location">Office Cubicle</label>
            <input type="text" id="location-view" name="location" class="form-control"> 
          </div>
          <div class="mb-3">
            <label>Stare date</label>
            <input type="text" name="sdate" class="form-control" id="sdate-view" placeholder="yyyy-mm-dd">
          </div>
        </div>
       <!-- second page of view modal -->
       <div id="step2-view" class="step-view" style="display: none;">
        <legend>Position Classification</legend>
          <div class="mb-3">  
            <label>Exempt (salary)</label>
            <input type="radio" id="workTypeSalary-view" name="workType-view" value="salary">
          </div>
          <div class="mb-3">
            <label>Full-Time (hourly)</label>
              <input type="radio" id = "workTypeFull-view" name="workType-view" value="Full-time">
          </div>
          <div class="mb-3"> 
            <label>Temporary (full-time)</label>
            <input type="radio" id="workTypeTemp-view" name="workType-view" value="temp">
          </div>
          <div class="mb-3"> 
            <label>Non-exempt (Hourly) Temporary</label>
            <input type="radio" id="workTypeTempHourly-view" name="workType-view" value="hourly">
          </div>
          <div class="mb-3"> 
            <label>Part-Time</label>
            <input type="radio" id = "workTypePart-view" name="workType-view" value="Part-time">
          </div>
          <div class="mb-3" id="hoursDiv-view">
            <label>Please specify how many weekly hours: </label>
            <input type="text" id="hours-view" placeholder='00.0' name="hours">
          </div>
        </div>
          <!-- third page of view modal -->
          <div id="step3-view" class="step-view" style="display: none;">
              <legend>Check all that apply</legend>

  

              <div class="mb-3"> 
                <label>Avaya Cloud Office</label>
                <input type="checkbox" id="avaya-view" name="avaya-view" value="avaya">
              </div>
              <div class="mb-3"> 
                <label>eCIRTS</label>
                <input type="checkbox" id="ecirts-view" name="ecirts-view" value="ecirts">
              </div>
              <div class="mb-3"> 
                <label>DOTS</label>
                <input type="checkbox" id="dots-view" name="dots-view" value="dots">
              </div>
 
              <div class="mb-3">        
                <label>Shadow Agent</label>
                <input type="checkbox" id="shadow_agent-view" name="shadowagent-view" value="shadowagent">
              </div>
 
            </div>
    </div>
 
   
    

<div class="modal-footer">
  <button type="button" class="btn btn-secondary" id="backBtn-view" style="display: none;">Back</button>
  <button type="button" class="btn btn-primary" id="nextBtn-view">Next</button>
  <button type="submit" class="btn btn-success" id="submitBtn-view">Submit</button>  
  <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
</div>
    </form>
  </div>
</div> 
<!-- Bootstrap JS (includes Popper) -->
  
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

<!-- Add event listeners for each modal -->
<!--create logic for multi-step modals
allows to go back and fourth between steps
-addUser
-viewUser
-updateUser
 -->
<script>  

    const addUser = document.getElementById('addUserModal'); 
    const updateUser = document.getElementById('editUserModal');
    const deleteUser = document.getElementById('deleteUserModal');
    const viewUser = document.getElementById('viewUserModal');
    const adminDeleteUser = document.getElementById('adminDeleteUserModal');
    

    function toggleWorkType (data, postfix){
      if (!data) return;

      const salary = data.querySelector(`#workTypeSalary-${postfix}`);
      const fullTime = data.querySelector(`#workTypeFull-${postfix}`);
      const partTime = data.querySelector(`#workTypePart-${postfix}`);
      const tempHourly = data.querySelector(`#workTypeTempHourly-${postfix}`);
      const tempFulltime = data.querySelector(`#workTypeTemp-${postfix}`);
      const hoursDiv = data.querySelector(`#hoursDiv-${postfix}`);
      

      if (!hoursDiv) return;

    const radios = Array.from(data.querySelectorAll(`[name="workType-${postfix}"]`));

      

    function toggleHoursDiv() {
      if (partTime.checked || tempHourly.checked) {
        hoursDiv.style.display = 'block';
      } else {
        hoursDiv.style.display = 'none';
      }
    }

    radios.forEach(radio => radio.addEventListener('change', toggleHoursDiv));

    toggleHoursDiv(); // Set initial state
    }

    toggleWorkType(addUser, 'add');
    toggleWorkType(updateUser, 'update');
    

    
    // === Multi-step Modal Logic ===
    function steps(data, postfix){
        if(!data) return;

        const stepNums = data.querySelectorAll(`.step-${postfix}`);
        if (!stepNums.length) return;

        let currentStep = 0;
        const nextBtn = data.querySelector(`#nextBtn-${postfix}`);
        const backBtn = data.querySelector(`#backBtn-${postfix}`);
        
        const submitBtn = data.querySelector(`#submitBtn-${postfix}`);
        const isViewUser = postfix === 'view';

        
      function showStep(index) {
      stepNums.forEach((step, i) => {
        step.style.display = i === index ? 'block' : 'none';
      });
        backBtn.style.display = index > 0 ? 'inline-block' : 'none';
        nextBtn.style.display = index < stepNums.length - 1 ? 'inline-block' : 'none' 
        submitBtn.style.display = !isViewUser && index === stepNums.length - 1 ? 'inline-block' : 'none';

      }
    
        
    nextBtn?.addEventListener('click', ()  => {
        if (currentStep < stepNums.length - 1) {
          currentStep++;
          showStep(currentStep);
        }

      });
    

   
    backBtn?.addEventListener('click', () => {
        if (currentStep > 0) {
          currentStep--;
          showStep(currentStep);
        }
      }); 
          
    showStep(currentStep); // Initialize    

    // Reset form and step when modal closes
    data.addEventListener('hidden.bs.modal', () => {
          const form = data.querySelector('form');
          form.reset();
          currentStep = 0;
          showStep(currentStep); 
    });
          
  } 
  steps(addUser, 'add');
  steps(updateUser, 'update');
  steps(viewUser, 'view');



// populate currently set values into form for deleteing a current user
function populateDeleteModal(id, fname, lname, title) {
    deleteUser.querySelector('#deleteId').value = id;
    deleteUser.querySelector('#deleteFname').value = fname;
    deleteUser.querySelector('#deleteLname').value = lname;
    deleteUser.querySelector('#deleteTitle').value = title;
}

// populate currently set values into form for deleteing a current user
function populateAdminDeleteModal(id, fname, lname, title) {
    adminDeleteUser.querySelector('#adminDeleteId').value = id;
    adminDeleteUser.querySelector('#adminDeleteFname').value = fname;
    adminDeleteUser.querySelector('#adminDeleteLname').value = lname;
    adminDeleteUser.querySelector('#adminDeleteTitle').value = title;
}
// calls populateModal with data* user , the eventlistener on the modal and the postfix value
viewUser.addEventListener('show.bs.modal', event => {
    const user = JSON.parse(event.relatedTarget.dataset.user);
    populateModal(user, viewUser,  'view');
});

// calls populateModal with data* user , the eventlistener on the modal and the postfix value
updateUser.addEventListener('show.bs.modal', event => {
    const user = JSON.parse(event.relatedTarget.dataset.user);
    populateModal(user, updateUser,  'update');
});

// function that inserts the user data into view and update modals
function populateModal(user, modal, postfix) {
    if (!user) return;

    
    
    modal.querySelector(`#id-${postfix}`).value = user.id ?? '';
    modal.querySelector(`#fname-${postfix}`).value = user.fname ?? '';
    modal.querySelector(`#lname-${postfix}`).value = user.lname ?? '';
    modal.querySelector(`#pname-${postfix}`).value = user.pname ?? '';
    modal.querySelector(`#email-${postfix}`).value = user.email ?? '';
    modal.querySelector(`#dept-${postfix}`).value = user.dept ?? '';
    modal.querySelector(`#title-${postfix}`).value = user.title ?? '';
    modal.querySelector(`#supervisor-${postfix}`).value = user.supervisor ?? '';
    modal.querySelector(`#location-${postfix}`).value = user.location ?? '';
    modal.querySelector(`#sdate-${postfix}`).value = user.sdate ?? '';
    modal.querySelector(`#hours-${postfix}`).value = user.hours ?? '';

        //check mark the correct radio button in the users worktype
   modal.querySelectorAll(`[name="workType-${postfix}"]`).forEach(radio => {
        radio.checked = radio.value === user.position;
    });

    //check mark the correct radio button in the users hours
    const hoursDiv = modal.querySelector(`#hoursDiv-${postfix}`);
    const hoursInput = hoursDiv?.querySelector('input');

    if (user.hours === '37.50') {
        hoursDiv.style.display = 'none';
       
    } else {
        hoursDiv.style.display = 'block';
       
    }

    // select all from the apps array and mark them in the modals
    apps = ['avaya', 'ecirts', 'dots', 'shadow_agent' ]
    
    apps.forEach(field => {
        const checkbox = modal.querySelector(`#${field}-${postfix}`);
        if (checkbox) checkbox.checked = Boolean(user[field]);
    });
}

  
  

</script>
</body>
</html>
