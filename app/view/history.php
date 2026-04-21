<!DOCTYPE html>
<html lang="en">

<?php include __DIR__ . '/head.php'; ?>

<body class="bg-light p-4">

<!--
    Top Navigation Bar
    - Displays logged-in user name
    - Role-based navigation options:
        Admin (3)   -> mis_dash
        Manager (2) -> Manager Dash
    - Back to dashboard
    - Logout
-->
<nav class="navbar navbar-expand-lg navbar-dark bg-dark mb-4">
  <div class="container-fluid">
    <a class="navbar-brand">MIS Support History</a>

    <div class="d-flex align-items-center gap-2">
      <span class="navbar-text text-white me-3">
        Welcome <?= htmlspecialchars($_SESSION['fname'] ?? '') ?>!
      </span>

      <?php if (!empty($_SESSION['role']) && $_SESSION['role'] === 3): ?>
        <a class="btn btn-sm btn-outline-warning" href="/admin">MIS</a>
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
<?php if (!empty($_SESSION['error'])): ?>
  <div class="alert alert-danger">
    <?= htmlspecialchars($_SESSION['error']) ?>
  </div>
<?php unset($_SESSION['error']); ?>
<?php endif; ?>

<!--
    All tickets ever created from beginning of time
    Each row opens independently in the view ticket modal.
-->
<div class="card shadow-sm">

  <div class="card-header bg-white">
    <div class="d-flex justify-content-between align-items-center">
      <h5 class="mb-0">Support Requests History</h5>

      <div class="d-flex align-items-center gap-2">
        <div class="input-group input-group-sm" style="width: 260px;">
          <span class="input-group-text">🔍</span>
          <input
            type="text"
            class="form-control"
            id="search"
            placeholder="Search requests..."
          >
        </div>

        <button class="btn btn-sm btn-success" id="toggleButton">
          Toggle
        </button>
      </div>
    </div>
  </div>

  <div class="card-body p-0">
    <?php if ($getAllTasks): ?>
      <div class="table-responsive">
        <table class="table table-hover table-striped align-middle mb-0">
          <thead class="table-light">
            <tr>
              <th style="width: 80px;">Task ID</th>
              <th style="width: 120px;">Status</th>
              <th>Description</th>
              <th>Solution</th>
            </tr>
          </thead>

          <tbody>
            <?php foreach ($getAllTasks as $task): ?>

              <tr
                class="all-task-row"
                data-id="<?= htmlspecialchars($task['id']) ?>"
                data-status="<?= htmlspecialchars($task['status']) ?>"
                data-desc="<?= htmlspecialchars($task['user_desc']) ?? '' ?>"
                data-solution="<?= htmlspecialchars($task['solution'] ?? '') ?>"
                data-location="<?= htmlspecialchars($task['location'] ?? '') ?>"
                data-priority="<?= htmlspecialchars($task['priority'] ?? '') ?>"
                data-opened-by="<?= htmlspecialchars($task['opened_by'] ?? '') ?>"
                data-updated-by="<?= htmlspecialchars($task['updated_by'] ?? '') ?>"
                data-closed-by="<?= htmlspecialchars($task['closed_by'] ?? '') ?>"
                data-date-opened="<?= htmlspecialchars($task['date_opened'] ?? '') ?>"
                data-date-updated="<?= htmlspecialchars($task['date_updated'] ?? '') ?>"
                data-date-closed="<?= htmlspecialchars($task['date_closed'] ?? '') ?>"
                data-images="<?= htmlspecialchars(implode(', ', $task['images'] ?? [])) ?>"

                        
              >
                <td><?= htmlspecialchars($task['id']) ?></td>
                <td><?= htmlspecialchars($task['status']) ?></td>
                <td><?= htmlspecialchars($task['user_desc']) ?></td>
                <td><?= htmlspecialchars($task['solution'] ?? '') ?></td>
                
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
      <?php unset($_SESSION['tasks']); ?>
    <?php else: ?>
      <div class="p-3 text-muted">
        No support requests found.
      </div>
    <?php endif; ?>
  </div>
</div>



<!--
Modal: View Ticket Details
- Populated dynamically using card data attributes
- Allows to view all ticket data
-->
<div class="modal fade" id="viewTaskModal" tabindex="-1" aria-labelledby="viewTaskModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-xl">
    <div class="modal-content">

      <!-- HEADER -->
      <div class="modal-header">
        <h5 class="modal-title" id="viewTaskModalLabel">Details</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>

      
      <div class="modal-body">
         <div id="step1">

        <div class="row g-3">

          <div class="col-6">
            <div class="card h-90">
              <div class="card-body">
                <h6 class="text-muted">Details</h6>

                <strong>Ticket Number:</strong> <span id="ticketNumber"></span>

                <p class="mb-1"><strong>Category:</strong> <span id="viewCategory"></span></p>
                <p class="mb-1"><strong>Location:</strong> <span id="viewLocation"></span></p>
                <p class="mb-1"><strong>Priority:</strong> <span id="viewPriority"></span></p>
                <p class="mb-1"><strong>Status:</strong> <span id="viewStatus"></span></p>
                <p class="mb-1"><strong>Opened By:</strong> <span id="viewOpenedBy"></span></p>

              </div>
            </div>
          </div>

          <div class="col-6">
            <div class="card h-90">
              <div class="card-body">
                <h6 class="text-muted">Tracking</h6>

                <p class="mb-1"><strong>Updated By:</strong> <span id="viewUpdatedBy"></span></p>
                <p class="mb-1"><strong>Closed By:</strong> <span id="viewClosedBy"></span></p>
                <p class="mb-1"><strong>Date Opened:</strong> <span id="viewDateOpened"></span></p>
                <p class="mb-1"><strong>Date Updated:</strong> <span id="viewDateUpdated"></span></p>
                <p class="mb-1"><strong>Date Closed:</strong> <span id="viewDateClosed"></span></p>

              </div>
            </div>
          </div>

        </div>

        <div class="card mt-3">
          <div class="card-body">
            <p><strong>Images:</strong> <span id="viewImagesContainer"></span></p>
            <p><strong>Description:</strong> <span id="viewDesc"></span></p>
            <p><strong>Note:</strong> <span id="solutionProgress"></span></p>
          </div>
        </div>

      </div>
    
          <div id="step2" style="display:none;">

<div class="card">
  <div class="card-header bg-light">
    <strong>History Timeline</strong>
  </div>
  <div id="historyContainer" class="card-body" style="max-height:400px; overflow-y:auto;">
    Loading history...
  </div>
</div>
        </div>
      <!-- FOOTER -->
      <div class="modal-footer d-flex justify-content-between">
        <input type="hidden" id="ticketNumberValue">
        <div>
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
            Close
          </button>
        </div>
          <div>
    <button type="button" id="backButton" onclick="prevStep()" class="btn btn-outline-secondary" style="display:none;">
      Back
    </button>

    <button type="button" id="viewHistoryBtn" onclick="nextStep()" class="btn btn-info">
      View History
    </button>


  </div>
      </div>

    </div>
  </div>
</div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.6/dist/js/bootstrap.bundle.min.js" integrity="sha384-j1CDi7MgGQ12Z7Qab0qlWQ/Qqz24Gc6BM0thvEMVjHnfYGF0rmFCozFSxQBxwHKO" crossorigin="anonymous"></script>
  <script src="https://code.jquery.com/jquery-3.7.1.min.js" integrity="sha256-/JqT3SQfawRcv/BIHPThkBvs0OEvtFFmqPF/lYI/Cxo=" crossorigin="anonymous"></script>
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
<script>
  /*
  When view modal opens, pull ticket info into the currently
  open ticket modal from the row selected and populate all the ticket fields.
*/
  document.addEventListener('DOMContentLoaded', function () {
    const taskRows = document.querySelectorAll('.all-task-row');

    taskRows.forEach(row => {
      row.addEventListener('click', () => {
        
        document.getElementById('ticketNumber').textContent = row.dataset.id || '';
        document.getElementById('viewLocation').textContent = row.dataset.location || '';
        document.getElementById('viewPriority').textContent = row.dataset.priority || '';
        document.getElementById('viewDesc').textContent = row.dataset.desc || '';        
        document.getElementById('viewStatus').textContent = row.dataset.status || '';
        document.getElementById('viewOpenedBy').textContent = row.dataset.openedBy || '';
        document.getElementById('viewUpdatedBy').textContent = row.dataset.updatedBy || '';
        document.getElementById('viewClosedBy').textContent = row.dataset.closedBy || '';
        document.getElementById('viewDateOpened').textContent = row.dataset.dateOpened || '';
        document.getElementById('viewDateUpdated').textContent = row.dataset.dateUpdated || '';
        document.getElementById('viewDateClosed').textContent = row.dataset.dateClosed || '';
        document.getElementById('solutionProgress').textContent = row.dataset.solution || '';


       const imageNames = row.dataset.images || '';
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

        // Show modal
        new bootstrap.Modal(document.getElementById('viewTaskModal')).show();
      });
    });
  });

  $(document).ready(function () {
  /*
    Client-side toggle button search filter on open and closed tickets.
    Filters visible rows based on text in status of ticket (text = 'completed' or text = not 'completed').
    
*/
    var toggleOn = $("#toggleButton").data("toggled") === true;

    // Function to toggle elements
    function toggleElements() {
        if (toggleOn) {
            $("#toggleButton").text("Open Tickets");            
            $(".all-task-row").show();            
        } else {
            $("#toggleButton").text("All Tickets");
            $(".all-task-row:contains('completed')").hide();
        }
    }

    // Call the function initially to set the initial state
    toggleElements();

    // Add a click event handler to the button
    $("#toggleButton").click(function () {
        toggleOn = !toggleOn; // Toggle the state
        toggleElements(); // Call the function to toggle elements
    });
});
/*
    Client-side live search filter.
    Filters visible user rows based on text typed into #search input.
    Performs case-insensitive substring match against row text.
*/
$(document).ready(function () {
    $("#search").on("keyup", function () {
        var searchTerm = $(this).val().toLowerCase();
        $(".all-task-row").each(function () {
            var text = $(this).text().toLowerCase();
            if (text.indexOf(searchTerm) === -1) {
                $(this).hide();
            } else {
                $(this).show();
            }
        });
    });
});
/**
 * Advances modal to step 2 (history view).
 *
 * Behavior:
 * - Hides step 1 content
 * - Shows step 2 history content
 * - Displays back button
 * - Hides action buttons
 * - Fetches and renders ticket history
 */
function nextStep() {

    document.getElementById("step1").style.display = "none";
    document.getElementById("step2").style.display = "block";

    document.getElementById("backButton").style.display = "inline-block";

        // Hide these buttons
    document.getElementById("viewHistoryBtn").style.display = "none";


    let ticketNum = document.getElementById("ticketNumber").innerText;

    fetch("ticket/history", {
        method: "POST",
        headers: { "Content-Type": "application/x-www-form-urlencoded" },
        body: "ticketNum=" + encodeURIComponent(ticketNum)
    })
    .then(response => response.json())
    .then(data => {

        let container = document.getElementById("historyContainer");
        container.innerHTML = "";

        if (!data || data.length === 0) {
            container.innerHTML = "<p>No history found.</p>";
            return;
        }

        data.forEach(row => {

            container.innerHTML += `
                <div class="border-bottom mb-2 pb-2">
                    <strong>${row.commented_by ?? 'Unknown'}</strong>
                    <span class="text-muted"> - ${row.timestamp ?? ''}</span>
                    <div>${row.comment ?? ''}</div>
                </div>
            `;

        });

    });

}
/**
 * Returns modal to step 1 (details view).
 *
 * Behavior:
 * - Hides history step
 * - Shows details step
 * - Hides back button
 * - Restores action buttons
 */
function prevStep(){

    document.getElementById("step2").style.display = "none";
    document.getElementById("step1").style.display = "block";

    document.getElementById("backButton").style.display = "none";

        // Show buttons again
    document.getElementById("viewHistoryBtn").style.display = "inline-block";
}

/**
 * Resets modal to initial state when closed.
 *
 * Behavior:
 * - Returns to step 1
 * - Hides step 2
 * - Hides back button
 * - Restores action buttons
 */
document.getElementById('viewTaskModal').addEventListener('hidden.bs.modal', function () {

    // Reset steps
    document.getElementById("step1").style.display = "block";
    document.getElementById("step2").style.display = "none";

    // Hide back button
    document.getElementById("backButton").style.display = "none";

    // Show action buttons again
    document.getElementById("viewHistoryBtn").style.display = "inline-block";


});
</script>
</script>


</body>
</html>