<?php
require 'config.php';

if (isset($_POST["delete"])) {
    $sql = "DELETE FROM databasemaju";
    if ($conn->query($sql) === TRUE) {
        echo "Seluruh data berhasil dihapus.";
    } else {
        echo "Error deleting record: " . $conn->error;
    }
}

$conn->close();
?>
