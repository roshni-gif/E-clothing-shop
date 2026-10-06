<?php
include 'connect.php';

// Check if columns already exist to prevent errors
$check_qty = mysqli_query($conn, "SHOW COLUMNS FROM `products` LIKE 'quantity'");
if (mysqli_num_rows($check_qty) == 0) {
    $alter1 = mysqli_query($conn, "ALTER TABLE products ADD COLUMN quantity INT(11) NOT NULL DEFAULT 10");
    if ($alter1) {
        echo "Successfully added column 'quantity'\n";
    } else {
        echo "Error adding column 'quantity': " . mysqli_error($conn) . "\n";
    }
} else {
    echo "Column 'quantity' already exists.\n";
}

$check_unit = mysqli_query($conn, "SHOW COLUMNS FROM `products` LIKE 'unit'");
if (mysqli_num_rows($check_unit) == 0) {
    $alter2 = mysqli_query($conn, "ALTER TABLE products ADD COLUMN unit VARCHAR(50) NOT NULL DEFAULT 'pcs'");
    if ($alter2) {
        echo "Successfully added column 'unit'\n";
    } else {
        echo "Error adding column 'unit': " . mysqli_error($conn) . "\n";
    }
} else {
    echo "Column 'unit' already exists.\n";
}
?>
