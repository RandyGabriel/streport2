<?php
require 'config.php';

$sql = "SELECT * FROM databasemaju";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    echo "
    <style>
        body {
            font-family: 'Arial', sans-serif;
            margin: 20px;
            padding: 20px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
            font-size: 14px; /* Ukuran font lebih kecil */
            text-align: center;
        }
        th, td {
            padding: 8px; /* Padding lebih kecil */
            border: 1px solid #ddd;
        }
        th {
            background-color: #f2f2f2;
            color: #333;
        }
        tr:nth-child(even) {
            background-color: #f9f9f9;
        }
        tr:hover {
            background-color: #f1f1f1;
        }
    </style>
    ";

    echo "<table>
            <tr>
                <th>ID</th>
                <th>Hari</th>
                <th>Bulan</th>
                <th>Tahun</th>
                <th>Customer</th>
                <th>Alias</th>
                <th>Retail Code</th>
                <th>Branch Name</th>
                <th>City</th>
                <th>Marketing</th>
                <th>Nomor Invoice</th>
                <th>Product Name</th>
                <th>Brand</th>
                <th>Qty</th>
                <th>Price</th>
                <th>Total Price</th>
                <th>Additional Info</th>
            </tr>";
    while($row = $result->fetch_assoc()) {
        echo "<tr>
                <td>" . $row["id"]. "</td>
                <td>" . $row["hari"]. "</td>
                <td>" . $row["bulan"]. "</td>
                <td>" . $row["tahun"]. "</td>
                <td>" . $row["customer"]. "</td>
                <td>" . $row["alias"]. "</td>
                <td>" . $row["retail_code"]. "</td>
                <td>" . $row["branch_name"]. "</td>
                <td>" . $row["city"]. "</td>
                <td>" . $row["marketing"]. "</td>
                <td>" . $row["nomor_invoice"]. "</td>
                <td>" . $row["product_name"]. "</td>
                <td>" . $row["brand"]. "</td>
                <td>" . $row["qty"]. "</td>
                <td>" . number_format($row["price"], 0, ',', '.'). "</td>
                <td>" . number_format($row["total_price"], 0, ',', '.'). "</td>
                <td>" . $row["additional_info"]. "</td>
              </tr>";
    }
    echo "</table>";
} else {
    echo "Tidak ada data yang ditemukan.";
}

$conn->close();
?>
