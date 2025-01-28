<?php
include '../dbconnect.php';  // Include database connection

header('Content-Type: application/json');

$data = json_decode(file_get_contents('php://input'), true);

if (isset($data['product_id'])) {
    $product_id = $data['product_id'];
    $product_name = $conn->real_escape_string($data['product_name']);
    $product_type = $conn->real_escape_string($data['product_type']);
    $price = $conn->real_escape_string($data['price']);
    $new_stock = $conn->real_escape_string($data['new_stock']);
    $old_stock = $conn->real_escape_string($data['old_stock']);
    $threshold = $conn->real_escape_string($data['threshold']);

    // SQL UPDATE query (using Product_ID as the identifier)
    $sql = "
        UPDATE Stocks s
        JOIN Products p ON s.Product_ID = p.Product_ID
        SET 
            p.Product_Name = '$product_name',
            p.Product_Type = '$product_type',
            p.Price = '$price',
            s.New_Stock = '$new_stock',
            s.Old_Stock = '$old_stock',
            s.Threshold = '$threshold'
        WHERE s.Product_ID = '$product_id'
    ";

    if ($conn->query($sql) === TRUE) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'error' => $conn->error]);
    }
} else {
    echo json_encode(['success' => false, 'error' => 'Missing Product ID']);
}

$conn->close();
?>
