<?php

require "../vendor/autoload.php";
require "../dbconnect.php"; // Database connection

use Dompdf\Dompdf;
use Dompdf\Options;

// Set Dompdf options
$options = new Options;
$options->setChroot(__DIR__);
$options->setIsRemoteEnabled(true);

// Initialize Dompdf
$dompdf = new Dompdf($options);

// Fetch transaction data from the database
$query = "SELECT t.Transaction_ID, t.Date, t.Time, 
                 c.First_Name AS Customer_First, c.Last_Name AS Customer_Last,
                 p.Product_Name, o.Amount, u.First_Name AS Staff_First, u.Last_Name AS Staff_Last
          FROM Transactions t
          INNER JOIN Orders o ON t.Order_ID = o.Order_ID
          INNER JOIN Customers c ON t.Customer_ID = c.Customer_ID
          INNER JOIN Products p ON o.Product_ID = p.Product_ID
          INNER JOIN Users u ON o.User_ID = u.User_ID
          ORDER BY t.Date DESC, t.Time DESC";

$result = $conn->query($query);

$html = '<h2 style="text-align: center;">Transaction Records</h2>';
$html .= '<table border="1" width="100%" cellpadding="5" cellspacing="0">
            <tr>
                <th>Transaction ID</th>
                <th>Date</th>
                <th>Time</th>
                <th>Customer</th>
                <th>Product</th>
                <th>Amount</th>
                <th>Processed By</th>
            </tr>';

while ($row = $result->fetch_assoc()) {
    $html .= "<tr>
                <td>{$row['Transaction_ID']}</td>
                <td>{$row['Date']}</td>
                <td>{$row['Time']}</td>
                <td>{$row['Customer_First']} {$row['Customer_Last']}</td>
                <td>{$row['Product_Name']}</td>
                <td>{$row['Amount']}</td>
                <td>{$row['Staff_First']} {$row['Staff_Last']}</td>
              </tr>";
}

$html .= '</table>';

// Load the HTML content
$dompdf->loadHtml($html);

// Set paper size and orientation
$dompdf->setPaper("A4", "landscape");

// Render the PDF
$dompdf->render();

// Stream the PDF to browser (inline display)
$dompdf->stream("transactions.pdf", ["Attachment" => false]);

// Save the PDF file locally (optional)
file_put_contents("transactions.pdf", $dompdf->output());

?>
