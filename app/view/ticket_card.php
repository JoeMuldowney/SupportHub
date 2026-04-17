<?php
    $openedBy   = $task['opened_by'] ?? '';
    $updatedBy  = $task['updated_by'] ?? '';
    $closedBy   = $task['closed_by'] ?? '';
    $dateOpened = $task['date_opened'] ?? '';
    $dateUpdObj = $task['date_updated'] ?? '';
    $dateClosed = $task['date_closed'] ?? '';

    $nameOpen = explode('@', $openedBy)[0];   // "firstname.lastname"
    $displayOpenName = str_replace('.', ' ', $nameOpen);  // "firstname lastname"

    $nameUpdate = explode('@', $updatedBy)[0];   // "firstname.lastname"
    $displayUpdateName = str_replace('.', ' ', $nameUpdate);  // "firstname lastname"

    $nameClose = explode('@', $dateClosed)[0];   // "firstname.lastname"
    $displayCloseName = str_replace('.', ' ', $nameClose);  // "firstname lastname"

    $dateUpdated = $dateUpdObj instanceof DateTime ? $dateUpdObj->format('Y-m-d') : '';
    $dateClosedF = $dateClosed instanceof DateTime ? $dateClosed->format('Y-m-d') : '';
    $ticket_images = implode(', ', $task['images'] ?? []);   


?>
<div class="card <?= $statusClass ?>"
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
  
  <div><?= htmlspecialchars($task['priority'] ?? '') ?></div>
  <div><?= htmlspecialchars($task['user_desc'] ?? '') ?></div>
</div>

