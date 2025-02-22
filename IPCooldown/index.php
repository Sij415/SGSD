<?php
// Include database connection

$required_role = 'admin';
include('../check_session.php');
include '../dbconnect.php';
 // Start the session
ini_set('display_errors', 1);

// Fetch user details from session
$user_email = $_SESSION['email'];
// Get the user's first name and email from the database
$query = "SELECT First_Name FROM Users WHERE Email = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("s", $user_id); // Bind the email as a string
$stmt->execute();
$stmt->bind_result($user_first_name);
$stmt->fetch();
$stmt->close();

// Fetch IP cooldown entries
$query = "SELECT ID, Email, IP_Address, Attempts, Last_Attempt, Locked_Until FROM IP_Cooldown";
$result = $conn->query($query);

// Handle IP removal
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['remove_ip'])) {
    $ip_id = $_POST['ip_id'];

    // Delete entry from the database
    $deleteQuery = "DELETE FROM IP_Cooldown WHERE ID = ?";
    $stmt = $conn->prepare($deleteQuery);
    $stmt->bind_param("i", $ip_id);

    if ($stmt->execute()) {
        echo json_encode(["success" => true, "message" => "IP removed successfully"]);
    } else {
        echo json_encode(["success" => false, "message" => "Error removing IP"]);
    }
    $stmt->close();
    exit();
}
?>


<body>
    <h2>IP Cooldown List</h2>
    <table border="1">
        <thead>
            <tr>
                <th>ID</th>
                <th>Email</th>
                <th>IP Address</th>
                <th>Attempts</th>
                <th>Last Attempt</th>
                <th>Locked Until</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody id="ipTable">
            <?php while ($row = $result->fetch_assoc()): ?>
                <tr id="row-<?php echo $row['ID']; ?>">
                    <td><?php echo $row['ID']; ?></td>
                    <td><?php echo $row['Email'] ?? 'N/A'; ?></td>
                    <td><?php echo $row['IP_Address']; ?></td>
                    <td><?php echo $row['Attempts']; ?></td>
                    <td><?php echo $row['Last_Attempt']; ?></td>
                    <td><?php echo $row['Locked_Until'] ?? 'N/A'; ?></td>
                    <td>
                        <button class="remove-btn" data-id="<?php echo $row['ID']; ?>">Remove</button>
                    </td>
                </tr>
            <?php endwhile; ?>
        </tbody>
    </table>

    <script>
        $(document).ready(function() {
            $(".remove-btn").click(function() {
                var ipId = $(this).data("id");
                var row = $("#row-" + ipId);

                $.post("", { remove_ip: true, ip_id: ipId }, function(response) {
                    var data = JSON.parse(response);
                    if (data.success) {
                        row.remove();
                    } else {
                        alert("Failed to remove IP.");
                    }
                });
            });
        });
    </script>
</body>
</html>