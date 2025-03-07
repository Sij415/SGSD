<?php


require  "../vendor/autoload.php";

use Dompdf\Dompdf;
use Dompdf\Options;

//$html = '<h1 style="color: green">Example</h1>';
//$html .= "Hello <em>$name</em>";
//$html .= '<img src="example.png">';
//$html .= "Quantity: $quantity";






if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Retrieve data from the POST request
    $managedBy = $_POST['managed_by'] ?? '';
    $customerName = $_POST['customer_name'] ?? '';
    $productName = $_POST['product_name'] ?? '';
    $status = $_POST['status'] ?? '';
    $orderType = $_POST['order_type'] ?? '';
    $quantity = $_POST['quantity'] ?? '';
    $totalPrice = $_POST['total_price'] ?? '';

    // Example of processing the received data (e.g., generating a PDF)

}




/**
 * Set the Dompdf options
 */
$options = new Options;
$options->setChroot(__DIR__);
$options->setIsRemoteEnabled(true);

$dompdf = new Dompdf($options);

/**
 * Set the paper size and orientation
 */
$dompdf->setPaper("A4", "portrait");

/**
 * Load the HTML and replace placeholders with values from the form
 */
$html = file_get_contents("template.html");
$html = str_replace(
    ["{{ name }}","{{ customer_name }}", "{{ product_name }}", "{{ status }}", "{{ order_type }}", "{{ quantity }}", "{{ total_price }}"],
    [$managedBy,$customerName, $productName, $status, $orderType, $quantity, $totalPrice],
    $html
);
$dompdf->loadHtml($html);
//$dompdf->loadHtmlFile("template.html");

/**
 * Create the PDF and set attributes
 */
$dompdf->render();

$dompdf->addInfo("Title", "An Example PDF"); // "add_info" in earlier versions of Dompdf

/**
 * Send the PDF to the browser
 */
$dompdf->stream("invoice.pdf", ["Attachment" => 0]);

/**
 * Save the PDF file locally
 */
$output = $dompdf->output();
file_put_contents("file.pdf", $output);
?>