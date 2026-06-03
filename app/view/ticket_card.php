<?php
    $openedBy   = $task['opened_by'] ?? '';
    $updatedBy  = $task['updated_by'] ?? '';
    $closedBy   = $task['closed_by'] ?? '';
    $dateOpened = $task['date_opened'] ?? '';
    //$dateUpdObj = $task['date_updated'] ?? '';
    //$dateClosed = $task['date_closed'] ?? '';

    $nameOpen = explode('@', $openedBy)[0];   // "firstname.lastname"
    $displayOpenName = ucwords(str_replace('.', ' ', $nameOpen));  // "firstname lastname"

    $nameUpdate = explode('@', $updatedBy)[0];   // "firstname.lastname"
    $displayUpdateName = ucwords(str_replace('.', ' ', $nameUpdate));  // "firstname lastname"

    $nameClose = explode('@', $closedBy )[0];   // "firstname.lastname"
    $displayCloseName = ucwords(str_replace('.', ' ', $nameClose));  // "firstname lastname"

    $dateOpenedObj = !empty($task['date_opened']) ? new DateTime($task['date_opened']) : null;
    $dateOpened = $dateOpenedObj ? $dateOpenedObj->format('M d Y') : '';

    $dateUpdObj = !empty($task['date_updated']) ? new DateTime($task['date_updated']) : null;
    $dateClosedObj = !empty($task['date_closed']) ? new DateTime($task['date_closed']) : null;

    $dateUpdated = $dateUpdObj ? $dateUpdObj->format('M d Y') : '';
    $dateClosedF = $dateClosedObj ? $dateClosedObj->format('M d Y') : '';
    
    $ticket_images = implode(', ', $task['images'] ?? []);  



?>
<div class="card ticket-card <?= $statusClass ?>"
     draggable="true"
     data-id="<?= htmlspecialchars($task['id']) ?>"
     data-category="<?= htmlspecialchars($task['category'] ?? '') ?>"
     data-location="<?= htmlspecialchars($task['location'] ?? '') ?>"
     data-priority="<?= htmlspecialchars($task['priority'] ?? '') ?>"
     data-desc="<?= htmlspecialchars($task['user_desc'] ?? '') ?>"
     data-status="<?= htmlspecialchars($task['status'] ?? $statusClass) ?>"
     data-opened-by="<?= htmlspecialchars($displayOpenName ) ?>"
     data-updated-by="<?= htmlspecialchars($displayUpdateName) ?>"
     data-closed-by="<?= htmlspecialchars($displayCloseName) ?>"
     data-date-opened="<?= htmlspecialchars($dateOpened) ?>"
     data-date-updated="<?= htmlspecialchars($dateUpdated) ?>"
     data-date-closed="<?= htmlspecialchars($dateClosedF) ?>"
     data-images="<?= htmlspecialchars($ticket_images) ?>"
     data-solution="<?= htmlspecialchars($task['solution'] ?? '') ?>"
>
  <div><?= htmlspecialchars($task['id'] ?? '') ?></div>
  <div><?= htmlspecialchars($task['location'] ?? '') ?></div>
  <div><?= htmlspecialchars($task['category'] ?? '') ?></div>
  <div><?= htmlspecialchars($displayOpenName ?? '') ?></div>

</div>

