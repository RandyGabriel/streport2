<?php
require 'config.php';
require 'header.php';

// Ambil daftar branch_name untuk dropdown
$branch_query = "SELECT DISTINCT branch_name FROM databasemaju WHERE branch_name IS NOT NULL AND branch_name != '' AND branch_name != 'Branch Name'";
$branch_result = $conn->query($branch_query);
$branches = [];

while ($row = $branch_result->fetch_assoc()) {
    $branches[] = $row['branch_name'];
}

// Periksa apakah branch_name dipilih
$selected_branch = isset($_POST['branch_name']) ? $_POST['branch_name'] : '';

// Bulan dalam urutan terbalik
$bulan_array = array_reverse(['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December']);

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Bestseller Per Branch</title>
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
            padding: 8px;
            border: 1px solid #ddd;
        }
        th {
            background: linear-gradient(to right, #4CAF50, #8BC34A);
            color: white;
        }
        tr:nth-child(even) {
            background: linear-gradient(to right, #f9f9f9, #e0f7fa);
        }
        tr:nth-child(odd) {
            background: linear-gradient(to right, #ffffff, #f1f8e9);
        }
        tr:hover {
            background: #f1f1f1;
        }
        .left-align {
            text-align: left;
        }
        .filter-form {
            display: flex;
            align-items: center;
            margin-bottom: 20px;
        }
        .filter-form label {
            margin-right: 10px;
            font-weight: bold;
        }
        .filter-form select {
            padding: 8px 12px;
            border: 1px solid #ccc;
            border-radius: 4px;
            font-size: 14px;
            margin-right: 10px;
        }
        .filter-form button {
            padding: 8px 16px;
            background-color: #4CAF50;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 14px;
        }
        .filter-form button:hover {
            background-color: #45a049;
        }
    </style>
</head>
<body>
    <h2>TOP 5 Best Product Seller</h2>
    <form class="filter-form" method="post" action="">
        <label for="branch_name">Pilih Depo:</label>
        <select name="branch_name" id="branch_name">
            <option value="">ALL DEPO</option>
            <?php foreach ($branches as $branch): ?>
                <option value="<?php echo $branch; ?>" <?php if($branch == $selected_branch) echo 'selected'; ?>>
                    <?php echo $branch; ?>
                </option>
            <?php endforeach; ?>
        </select>
        <button type="submit">Filter</button>
    </form>

<?php

if ($selected_branch != '') {
    $branches = [$selected_branch];
}

$display_branch_name = $selected_branch ? $selected_branch : 'ALL DEPO';

// Menampilkan data per branch_name
foreach ($branches as $branch_name) {
    echo "<h2>Branch: $display_branch_name</h2>";

    foreach ($bulan_array as $bulan) {
        // Query untuk mendapatkan 5 produk teratas per bulan berdasarkan branch_name atau semua branch_name
        if ($selected_branch == '') {
            $sql = "
            SELECT product_name, 
                   SUM(qty) AS total_qty
            FROM databasemaju
            WHERE bulan = '$bulan'
            GROUP BY product_name
            ORDER BY total_qty DESC
            LIMIT 5";
        } else {
            $sql = "
            SELECT product_name, 
                   SUM(qty) AS total_qty
            FROM databasemaju
            WHERE branch_name = '$branch_name' AND bulan = '$bulan'
            GROUP BY product_name
            ORDER BY total_qty DESC
            LIMIT 5";
        }

        $result = $conn->query($sql);

        if ($result->num_rows > 0) {
            echo "<h3>$bulan</h3>";
            echo "<table>
                    <tr>
                        <th>Product Name</th>
                        <th>Total Qty</th>
                    </tr>";

            while ($row = $result->fetch_assoc()) {
                echo "<tr>
                        <td class='left-align'>" . $row['product_name'] . "</td>
                        <td>" . number_format($row['total_qty'], 0, ',', '.') . "</td>
                      </tr>";
            }

            echo "</table>";
        } else {
            echo "<p>Tidak ada data untuk $bulan.</p>";
        }
    }
}

$conn->close();
?>
<?php include 'footer.php' ?>
</body>
</html>
