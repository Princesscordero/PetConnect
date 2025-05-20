<?php
require 'includes/db.php';
session_start();

if (!isset($_SESSION['user'])) {
    header('Location: login.php');
    exit;
}

$user = $_SESSION['user'];

// Fetch adoption requests made by the logged-in user
$stmt = $pdo->prepare("SELECT pet_name, status, submitted_at FROM adoption_requests WHERE adopter_name = ?");
$stmt->execute([$user['name']]);
$requests = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>My Adoption Requests - PetConnect</title>
  <link rel="stylesheet" href="styles.css" />
  <style>
    .status-pending { color: orange; font-weight: bold; }
    .status-approved { color: green; font-weight: bold; }
    .status-rejected { color: red; font-weight: bold; }
    table {
      width: 100%;
      border-collapse: collapse;
      margin-top: 20px;
    }
    th, td {
      border: 1px solid #ccc;
      padding: 10px;
      text-align: left;
    }
    th {
      background: #f4f4f4;
    }
  </style>
</head>
<body>
  <header>
    <div class="logo">🐾 PetConnect</div>
    <nav>
      <a href="home.php">🏠 Home</a>
      <a href="adopt.php">❤️ Adopt</a>
      <a href="community.php">👥 Community</a>
      <a href="appointments.php">📅 Appointments</a>
      <a href="shop.php">🛒 Shop</a>
      <a href="notifications.php" title="Notifications">🔔</a>
      <a href="cart.php" title="View Cart">🛍️</a>
      <div class="dropdown">
        <button class="dropbtn">⚙️ Account ▼</button>
        <div class="dropdown-content">
          <a href="profile.php">Profile</a>
          <a href="pet_records.php">Manage Pets</a>
          <a href="reminders.php">Reminders</a>
          <a href="logout.php">Logout</a>
        </div>
      </div>
    </nav>
  </header>

  <main>
    <section class="adoption-status">
      <h1>My Adoption Requests</h1>

      <?php if ($requests): ?>
        <table>
          <thead>
            <tr>
              <th>Pet Name</th>
              <th>Status</th>
              <th>Submitted On</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($requests as $request): ?>
              <tr>
                <td><?php echo htmlspecialchars($request['pet_name']); ?></td>
                <td class="status-<?php echo strtolower($request['status']); ?>">
                  <?php echo htmlspecialchars($request['status']); ?>
                </td>
                <td><?php echo htmlspecialchars(date("F j, Y, g:i a", strtotime($request['submitted_at']))); ?></td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      <?php else: ?>
        <p>You have not submitted any adoption requests yet.</p>
      <?php endif; ?>
    </section>
  </main>

  <footer>
    <p>© 2025 PetConnect. All Rights Reserved.</p>
  </footer>
</body>
</html>
