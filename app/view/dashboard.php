<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Dashboard</title>

  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.6/dist/css/bootstrap.min.css" rel="stylesheet">
  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

  <style>
    body { background-color: #f8f9fa; padding: 2rem; }
    .board { display: flex; justify-content: space-between; gap: 1rem; margin-top: 2rem; flex-wrap: wrap; }
    .column { background: white; border-radius: 8px; padding: 1rem; flex: 1; min-width: 300px; box-shadow: 0 4px 10px rgba(0,0,0,0.05); max-height: 80vh; overflow-y: auto; }
    .column h2 { text-align: center; margin-bottom: 1rem; font-size: 1.5rem; color: #343a40; }
    .card { margin-bottom: 1rem; border: none; padding: 0.75rem 1rem; border-radius: 0.5rem; font-weight: 500; box-shadow: 0 2px 6px rgba(0,0,0,0.1); transition: transform 0.2s ease; cursor: pointer; white-space: normal; word-wrap: break-word; min-height: 48px; line-height: 1.4; }
    .card:hover { transform: scale(1.02); }
    .card.todo { background-color: #f8d7da; color: #721c24; }
    .card.inprogress { background-color: #bee5eb; color: #0c5460; }
    .card.done { background-color: #d4edda; color: #155724; }
  </style>
</head>
<body>

<!--
    Top Navigation Bar
    - Displays logged-in user name
    - Role-based navigation options:
        Admin (3)   -> Admin + Manager Portals
        Manager (1) -> Manager Dashboard
    - Add a ticket
    - Logout
-->
<nav class="navbar navbar-expand-lg navbar-dark bg-dark mb-4">
  <div class="container-fluid">
    <a class="navbar-brand" href="#">MIS Support Hub Dashboard</a>
    <div class="d-flex align-items-center">
      <span class="navbar-text text-white me-3">
        Welcome <?= htmlspecialchars($_SESSION['fname'] ?? '') ?>!
      </span>
      <?php if (!empty($_SESSION['role']) && $_SESSION['role'] === 3): ?>
        <a class="btn btn-outline-warning me-2" href="/admin">MIS</a>
      <?php endif; ?>
      <?php if (!empty($_SESSION['role']) && $_SESSION['role'] === 1 || $_SESSION['role'] === 3): ?>
        <a class="btn btn-outline-warning me-2" href="/userPanel">Manager Dash</a>
      <?php endif; ?>
      <button class="btn btn-outline-light me-2" onclick="window.location.href='/logout';">Logout</button>
      <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#addTaskModal">+ Add Task</button>
    </div>
  </div>
</nav>

<!--Show any errors returned from the controller and then clear them -->
<?php if (isset($_SESSION['result'])): ?>
  <div class="alert alert-info"><?= htmlspecialchars($_SESSION['result']) ?></div>
  <?php unset($_SESSION['result']); ?>
<?php endif; ?>

<!-- Kanban Board showing the progress of tickets in 3 columns. New, inprogress and completed.  Each ticket is clickable and opens the view ticket modal -->

<?php if ($noTasks): ?>
  <div class="alert alert-warning text-center">No tickets found.</div>
<?php endif; ?>

<div class="board">
  <div class="column" id="todo" >
    <h2>To Do</h2>
    <?php foreach ($newTasks as $task): ?>
      <?php $statusClass = 'todo'; ?>
      <?php include __DIR__ . '/ticket_card.php'; ?>
    <?php endforeach; ?>
  </div>

  <div class="column" id="inprogress"  >
    <h2>In Progress</h2>
    <?php foreach ($inProgressTasks as $task): ?>
      <?php $statusClass = 'inprogress'; ?>
      <?php include __DIR__ . '/ticket_card.php'; ?>
    <?php endforeach; ?>
  </div>

  <div class="column" id="done" >
    <h2>Completed</h2>
    <?php foreach ($completedTasks as $task): ?>
      <?php $statusClass = 'done'; ?>
      <?php include __DIR__ . '/ticket_card.php'; ?>
    <?php endforeach; ?>
  </div>
</div>

<!-- Modal: Create New Ticket -->
<!-- Submits to controller route: ticket/add -->
<!-- Supports file upload (multipart/form-data) -->
<div class="modal fade" id="addTaskModal" tabindex="-1" aria-labelledby="formModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <form class="modal-content" id="add_ticket_and_send_email" action="ticket/add" method="POST"  enctype="multipart/form-data">
      <div class="modal-header">
        <h5 class="modal-title" id="formModalLabel">Add New Ticket</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <div class="mb-3">
          <label for="taskName" class="form-label">Issue</label>
          <select class="form-select" id="taskName" name="taskName" required>
            <option value="" disabled selected>Select an issue</option> 
            <option value="Account Access / Password Reset">Account Access / Password Reset</option>
            <option value="Email (Gmail)">Email (Gmail)</option>
            <option value="Phone System (Avaya)">Phone System (Avaya)</option>
            <option value="eCIRTS">eCIRTS</option>
            <option value="Hardware Problem">Hardware Problem</option>
            <option value="Software Problem">Software Problem</option>
            <option value="Network / Connectivity">Network / Connectivity</option>
            <option value="Audio / Video Issue">Audio / Video Issue</option>
            <option value="Slow / Performance Issue">Slow / Performance Issue</option>
            <option value="Request / New Setup">Request / New Setup</option>
            <option value="Other">Other</option>
            </select>
        </div>              



        <!-- Location -->
        <div class="mb-3">
          <label class="form-label d-block">Location</label>
          <div class="form-check form-check-inline">
            <input class="form-check-input" type="radio" name="location" id="location-home" value="Home" required>
            <label class="form-check-label" for="location-home">Home</label>
          </div>
          <div class="form-check form-check-inline">
            <input class="form-check-input" type="radio" name="location" id="location-office" value="Office">
            <label class="form-check-label" for="location-office">Office</label>
          </div>
        </div>

        <!-- Priority -->
        <div class="mb-3">
          <label class="form-label d-block">Priority</label>
          <div class="form-check form-check-inline">
            <input class="form-check-input" type="radio" name="priority" id="priority-high" value="High" required>
            <label class="form-check-label" for="priority-high">High</label>
          </div>
          <div class="form-check form-check-inline">
            <input class="form-check-input" type="radio" name="priority" id="priority-medium" value="Medium">
            <label class="form-check-label" for="priority-medium">Medium</label>
          </div>
          <div class="form-check form-check-inline">
            <input class="form-check-input" type="radio" name="priority" id="priority-low" value="Low">
            <label class="form-check-label" for="priority-low">Low</label>
          </div>
        </div>

        <div class="mb-3">
          <label for="desc" class="form-label">Description of the problem</label>
          <textarea id="desc" name="desc" class="form-control" rows="5"></textarea>
        </div>
        <!-- Image -->
        <div class="mb-3">
          <label for="image" class="form-label">Upload Image(s)</label>
          <input class="form-control" type="file" id="image" name="image[]" multiple>
        </div>
        <div id="preview" class="d-flex flex-wrap gap-2 mt-2"></div>
      </div>
    
      <div class="modal-footer">
        <button class="btn btn-primary" type="submit">Request</button>
        <button class="btn btn-secondary" data-bs-dismiss="modal" type="button">Cancel</button>
      </div>
    </form>
  </div>
</div>

<!--
Modal: View Ticket Details
- Populated dynamically using card data attributes
- Allows adding solution
- Allows setting calendar reminder
- Submits solution to: solution/add
-->
<div class="modal fade" id="viewTaskModal" tabindex="-1" aria-labelledby="viewTaskModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <form id="update_ticket_and_send_email" action="solution/add" method="POST" enctype="multipart/form-data">
        <div class="modal-body">
          <p><strong>Ticket:</strong> <span id="ticketNumber" ></span></p>
          <p><strong>Category:</strong> <span id="viewCategory"></span></p>
          <p><strong>Location:</strong> <span id="viewLocation"></span></p>
          <p><strong>Priority:</strong> <span id="viewPriority"></span></p>
          <p><strong>Description:</strong> <span id="viewDesc"></span></p>
          <p><strong>Images:</strong> <span id="viewImagesContainer"></span></p>
          <p><strong>Status:</strong> <span id="viewStatus"></span></p>
          <p><strong>Opened By:</strong> <span id="viewOpenedBy"></span></p>
          <p><strong>Updated By:</strong> <span id="viewUpdatedBy"></span></p>
          <p><strong>Closed By:</strong> <span id="viewClosedBy"></span></p>
          <p><strong>Date Opened:</strong> <span id="viewDateOpened"></span></p>
          <p><strong>Date Updated:</strong> <span id="viewDateUpdated"></span></p>
          <p><strong>Date Closed:</strong> <span id="viewDateClosed"></span></p>
          <p><strong>Solution:</strong> <span id="solutionProgress"></span></p>         
          

          <div class="mt-4">
            <label for="solution" class="form-label">Solution for the problem</label>
            <textarea id="solution" name="solution" class="form-control" rows="5"></textarea>
          </div>
        </div>
        
        <div class="modal-footer">
          <input type="hidden" id="ticketNumberValue" name="ticketNum">
          <input type="hidden" id="ticketStatusValue" name="ticketStatus">
          <button type="button"  class="btn btn-success"  data-bs-toggle="modal" data-bs-target="#addToCalender">Set Reminder</button>
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
          <button type="submit" class="btn btn-primary">Add</button>
        </div>
      </form>
    </div>
  </div>
</div>


<!--
Modal: Add Reminder to Calendar
- Populated from currently viewed ticket
- AJAX POST -> /calendar/add
-->
<div class="modal fade" id="addToCalender" tabindex="-1">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <form id="addCalender" enctype="multipart/form-data">
        <div class="modal-body">
          <div class="mb-3"> 
            
            <label>Ticket #</label>
            <input type="text" class="form-control" id="id-calender" name="id-calender" value="id-calender">
          </div>
          <div class="mb-3"> 
            
            <label>Title</label>
            <input type="text" class="form-control" id="category-calender" name="category-calender" value="category-calender">
          </div>
          <div class="mb-3"> 
            <label>Description</label>
            <input type="text" class="form-control" id="desc-calender" name="desc-calender" value="desc-calender">
          </div>
          <div class="mb-3">
            <label>Date</label>
            <input type="date" name="date-calender" class="form-control" id="date-calender" >
          </div>
          <div class="mb-3">
            <label>Time</label>
            <input type="time" name="time-calender" class="form-control" id="time-calender" value="08:00">
          </div>

          

       
        
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-success" data-bs-dismiss="modal" >Add</button>
          
          
        </div>
      </form>
    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.6/dist/js/bootstrap.bundle.min.js"></script>
<script>
let draggedCard = null;
let isDragging = false;

const calenderModal = document.getElementById('addToCalender');
const viewModel = document.getElementById('viewTaskModal');

/*
  When calendar modal opens, pull ticket info from the currently
  open ticket modal and populate reminder form fields.
*/

 calenderModal.addEventListener('show.bs.modal', event => {
    const calenderId = viewModel.querySelector('#ticketNumber').textContent;
    const calenderCategory = viewModel.querySelector('#viewCategory').textContent;  
     const calenderDesc = viewModel.querySelector('#viewDesc').textContent; 
   
     populateCalenderModal(calenderId, calenderCategory, calenderDesc);
 });


function populateCalenderModal(calenderId, calenderCategory, calenderDesc) {
    

    // Step 1 fields
    calenderModal.querySelector('#id-calender').value = calenderId;
    calenderModal.querySelector('#category-calender').value =calenderCategory;
    calenderModal.querySelector('#desc-calender').value = calenderDesc;

 
}

/*
  Drag & Drop System
  ------------------
  Allows moving ticket cards between columns.
  On drop:
    - DOM is updated
    - AJAX request updates backend ticket status
    - Card styling updated to match new state
*/
document.addEventListener('DOMContentLoaded', () => {
  bindCardEvents();
  bindColumnDnd();
});

function bindCardEvents() {
  document.querySelectorAll('.card').forEach(card => {
    // drag
    card.addEventListener('dragstart', e => {
      draggedCard = e.currentTarget;  // safer than e.target      
      isDragging = true;
      e.dataTransfer.setData('text/plain', draggedCard.dataset.id);
    });
    card.addEventListener('dragend', () => {

      //isDragging = false;
      //draggedCard = null;
    });

    // click -> open modal (ignore if dragging)
    card.addEventListener('click', () => {
      if (isDragging) return;
      openCardModal(card);
    });
  });
}

function bindColumnDnd() {
  
  document.querySelectorAll('.column').forEach(column => {
    column.addEventListener('dragover', e => e.preventDefault());
    column.addEventListener('drop', e => {
      e.preventDefault();
        if (!draggedCard || !draggedCard.dataset) {
          console.error("draggedCard or its dataset is null.");
          return;
        }

      const id = draggedCard.dataset.id;
      let newStatus = '';
      switch (column.id) {
        case 'todo':       newStatus = 'new';        break;
        case 'inprogress': newStatus = 'inProgress'; break;
        case 'done':       newStatus = 'completed';  break;
      }
      if (!newStatus) return;

      // Move card in DOM
      if (draggedCard.parentElement !== column) {
        column.appendChild(draggedCard);
      }

      // Update via AJAX
      fetch('/ticket/updateStatus', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ id: id, status: newStatus })
      })
      .then(res => {
        if (!res.ok) throw new Error('Failed to update');
        // Optionally update dataset + status label instantly
        draggedCard.dataset.status = newStatus;
        // Update visual styling class based on status
        draggedCard.classList.remove('todo', 'inprogress', 'done');

        switch (newStatus) {
          case 'new':
            draggedCard.classList.add('todo');
            break;
          case 'inProgress':
            draggedCard.classList.add('inprogress');
            break;
          case 'completed':
            draggedCard.classList.add('done');
            console.log(draggedCard.dataset.status);
            openCardModal(draggedCard);
            break;
        }        
      })
      .catch(err => {
        alert('Error updating task: ' + err.message);
        // On error, you might want to revert DOM move (optional)
        // location.reload();
      })
      .finally(() => {
        // Now it's safe to reset
        isDragging = false;
        draggedCard = null;
      });
    });
  });
}

/*
  Populates View Ticket modal using data-* attributes
  stored on each ticket card.
  Also dynamically builds image links if ticket has attachments.
*/
function openCardModal(card) {
  document.getElementById('ticketNumberValue').value   = card.dataset.id || '';
  document.getElementById('ticketStatusValue').value   = card.dataset.status || '';
  document.getElementById('ticketNumber').textContent   = card.dataset.id || '';
  document.getElementById('viewCategory').textContent   = card.dataset.category || '';
  document.getElementById('viewLocation').textContent   = card.dataset.location || '';
  document.getElementById('viewPriority').textContent   = card.dataset.priority || '';
  document.getElementById('viewDesc').textContent       = card.dataset.desc || '';  
  document.getElementById('viewStatus').textContent     = card.dataset.status || '';
  document.getElementById('viewOpenedBy').textContent   = card.dataset.openedBy || '';
  document.getElementById('viewUpdatedBy').textContent  = card.dataset.updatedBy || '';
  document.getElementById('viewClosedBy').textContent   = card.dataset.closedBy || '';
  document.getElementById('viewDateOpened').textContent = card.dataset.dateOpened || '';
  document.getElementById('viewDateUpdated').textContent= card.dataset.dateUpdated || '';
  document.getElementById('viewDateClosed').textContent = card.dataset.dateClosed || '';
  document.getElementById('solutionProgress').textContent = card.dataset.solution || '';
  

/* Images stored server-side alias /images/ using custom 000-default.conf from absolute path /var/lib/tickets/data/ */
       const imageNames = card.dataset.images || '';
       const imageNamesArray = imageNames
        ? imageNames.split(", ")
        : [];

        const container = document.getElementById('viewImagesContainer');
        container.innerHTML = ""; // clear old links
     
        imageNamesArray.forEach((name, index) => {
          if (!name) return;

          const link = document.createElement('a');
          link.href = '/images/' + name;
          link.target = "_blank";
          link.textContent = name;

          container.appendChild(link);

          // add space between links (but not after last one)
          if (index < imageNamesArray.length - 1) {
            container.appendChild(document.createTextNode(" "));
          }
        });

  new bootstrap.Modal(document.getElementById('viewTaskModal')).show();
}

$(document).ready(function() {
   
    /*
      Handles async submission of calendar reminder form.
      Sends FormData via AJAX to backend endpoint.
      On success -> reload dashboard.
    */


     $('#addCalender').on('submit', function(e) {
        e.preventDefault();  // Prevent default form submission
        
        var formData = new FormData(this);  // Use FormData to handle file uploads as well

        $.ajax({
            url: '/calendar/add',  // url handled by router
            method: 'POST',
            data: formData,
            processData: false,  // Important for file uploads
            contentType: false,  // Important for file uploads
            success: function(response) {
                // get json_encoded response
                var ticketInfo = JSON.parse(response);

                if (ticketInfo.status === 'Event Created') {                
                                     
                    window.location.href = '/dashboard';  // Redirect to the dashboard
                } else {
                    alert('Error adding ticket: ' + ticketInfo.message);
                }
            },
            error: function() {
                alert('Error adding to calender.');
            }
        });
    });

   
    
});

/*
  Image Upload Preview System
  ---------------------------
  - Limits uploads to 4 images
  - Prevents duplicate files
  - Shows thumbnail previews
  - Allows removing selected files
  - Maintains internal file list using DataTransfer
*/
document.addEventListener('DOMContentLoaded', () => {
    const input = document.getElementById('image');
    const preview = document.getElementById('preview');
    const maxFiles = 4;

    let filesArray = []; // Persistent array of selected files

    input.addEventListener('change', () => {
        Array.from(input.files).forEach(file => {
            if (filesArray.length >= maxFiles) {
                alert(`You can upload a maximum of ${maxFiles} images.`);
                return;
            }

            // Prevent duplicates
            if (filesArray.some(f => f.name === file.name && f.size === file.size)) {
                return;
            }

            filesArray.push(file); // add to persistent array

            // Preview wrapper
            const wrapper = document.createElement('div');
            wrapper.style.position = 'relative';
            wrapper.style.display = 'inline-block';
            wrapper.style.margin = '5px';

            const img = document.createElement('img');
            img.src = URL.createObjectURL(file);
            img.style.width = '100px';
            img.style.height = '100px';
            img.style.objectFit = 'cover';
            img.classList.add('rounded', 'border');
            wrapper.appendChild(img);

            // Remove button
            const btn = document.createElement('button');
            btn.innerHTML = '&times;';
            btn.style.position = 'absolute';
            btn.style.top = '2px';
            btn.style.right = '2px';
            btn.style.background = 'rgba(0,0,0,0.6)';
            btn.style.color = 'white';
            btn.style.border = 'none';
            btn.style.borderRadius = '50%';
            btn.style.width = '20px';
            btn.style.height = '20px';
            btn.style.cursor = 'pointer';
            btn.title = 'Remove image';

            btn.addEventListener('click', () => {
                // Remove file from persistent array
                filesArray = filesArray.filter(f => !(f.name === file.name && f.size === file.size));

                // Remove preview
                wrapper.remove();

                // Rebuild DataTransfer
                const dt = new DataTransfer();
                filesArray.forEach(f => dt.items.add(f));
                input.files = dt.files;
            });

            wrapper.appendChild(btn);
            preview.appendChild(wrapper);

            // Sync DataTransfer
            const dt = new DataTransfer();
            filesArray.forEach(f => dt.items.add(f));
            input.files = dt.files;
        });
    });
});
</script>
</body>
</html>
