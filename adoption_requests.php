<?php
require 'includes/db.php';

// Fetch adoption requests
$stmt = $pdo->query("SELECT id, pet_name, adopter_name, email, reason, submitted_at, status_id, is_new_owner, pets_owned FROM adoption_requests ORDER BY submitted_at DESC");
$requests = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch fees for each adoption status
$feeStmt = $pdo->query("SELECT AdoptionStatusID, FeeAmount FROM adoption_fees");
$fees = $feeStmt->fetchAll(PDO::FETCH_KEY_PAIR);

$statusMap = [
    1 => 'Approved',
    2 => 'N/A',
];
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <title>Adoption Requests - PetConnect</title>
  <link rel="stylesheet" href="styles.css" />
  <link rel="stylesheet" href="adoption_requests.css" />
</head>
<body>
  <h1 class="adoption-requests-title">Adoption Requests</h1>
  
  <button class="back-button" onclick="window.history.back();">← Back</button>
  
  <?php if ($requests): ?>
    <table class="adoption-requests-table" border="1" cellpadding="8" cellspacing="0">
      <thead>
        <tr>
          <th>ID</th>
          <th>Pet Name</th>
          <th>Adopter Name</th>
          <th>Email</th>
          <th>Reason</th>
          <th>New Owner</th>
          <th>Other Pets Owned</th>
          <th>Status</th>
          <th>Fee (₱)</th>
          <th>Submitted At</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($requests as $req): ?>
          <tr>
            <td><?php echo htmlspecialchars($req['id']); ?></td>
            <td><?php echo htmlspecialchars($req['pet_name']); ?></td>
            <td><?php echo htmlspecialchars($req['adopter_name']); ?></td>
            <td><?php echo htmlspecialchars($req['email']); ?></td>
            <td><?php echo nl2br(htmlspecialchars($req['reason'])); ?></td>
            <td><?php echo $req['is_new_owner'] ? 'Yes' : 'No'; ?></td>
            <td><?php echo (int)$req['pets_owned']; ?></td>
            <td><?php echo htmlspecialchars($statusMap[$req['status_id']] ?? 'Unknown'); ?></td>
            <td>
              <?php 
                echo isset($fees[$req['status_id']]) ? number_format($fees[$req['status_id']], 2) : '';
              ?>
            </td>
            <td><?php echo htmlspecialchars($req['submitted_at']); ?></td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  <?php else: ?>
    <p class="adoption-requests-no-data">No adoption requests found.</p>
  <?php endif; ?>
</body>
</html>

