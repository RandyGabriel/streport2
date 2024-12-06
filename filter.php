<?php
include 'header.php';
require 'config.php';

// Ambil daftar branch_name untuk dropdown
$branch_query = "SELECT DISTINCT branch_name FROM databasemaju WHERE branch_name IS NOT NULL AND branch_name != '' AND branch_name != 'Branch Name'";
$branch_result = $conn->query($branch_query);
$branches = [];

while($row = $branch_result->fetch_assoc()) {
    $branches[] = $row['branch_name'];
}

// Periksa apakah branch_name dipilih
$selected_branch = isset($_POST['branch_name']) ? $_POST['branch_name'] : '';

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Filter Data</title>
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
            font-size: 16px; /* Ukuran font lebih kecil */
            text-align: center;
        }
        th, td {
            padding: 10px;
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
        a {
            text-decoration: none;
            color: #000;
        }
        a:hover {
            color: #4CAF50;
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
        .left-align {
            text-align: left;
        }
        .capitalize {
            text-transform: uppercase;
        }
    </style>
</head>
<body>
    <h2>REPORT BY MARKETING</h2>
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

// Query untuk mendapatkan jumlah total Qty per bulan berdasarkan Marketing, kecuali yang kosong atau "ADMIN SALES"
$sql = "
SELECT marketing, 
       bulan, 
       SUM(qty) AS total_qty
FROM databasemaju
WHERE marketing IS NOT NULL AND marketing != '' AND marketing != 'ADMIN SALES'";

// Tambahkan filter berdasarkan branch_name jika ada yang dipilih
if ($selected_branch != '') {
    $sql .= " AND branch_name = '$selected_branch'";
}

$sql .= "
GROUP BY marketing, bulan
ORDER BY marketing, FIELD(bulan, 'January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December')";

$result = $conn->query($sql);

// Inisialisasi array untuk menyimpan data
$data = [];

// Bulan dalam urutan yang benar
$bulan_array = ['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'];

if ($result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        $marketing = $row['marketing'];
        $bulan = $row['bulan'];
        $total_qty = $row['total_qty'];

        if (!isset($data[$marketing])) {
            $data[$marketing] = array_fill_keys($bulan_array, 0); // Inisialisasi dengan 0 untuk setiap bulan
        }
        $data[$marketing][$bulan] = $total_qty;
    }

    // Tampilkan tabel
    echo "<h2>Data Marketing</h2>";
    echo "<table>
            <tr>
                <th>Marketing</th>
                <th>January</th>
                <th>February</th>
                <th>March</th>
                <th>April</th>
                <th>May</th>
                <th>June</th>
                <th>July</th>
                <th>August</th>
                <th>September</th>
                <th>October</th>
                <th>November</th>
                <th>December</th>
            </tr>";

    foreach ($data as $marketing => $qty) {
        if ($marketing != 'Marketing' && $marketing != 'ADMIN SALES' && $marketing != '') {
            echo "<tr>
                    <td class='left-align capitalize'><a href='details.php?marketing=$marketing'>" . strtoupper($marketing) . "</a></td>";
            foreach ($bulan_array as $bulan) {
                $class = $qty[$bulan] == 0 ? 'class="zero-value"' : '';
                echo "<td $class>" . number_format($qty[$bulan], 0, ',', '.') . "</td>";
            }
            echo "</tr>";
        }
    }

    echo "</table>";
} else {
    echo "Tidak ada data yang ditemukan.";
}

echo "<p style='font-style: italic;'>Note: Klik pada Nama Marketing untuk Detail</p>";

// Query untuk mendapatkan jumlah total Qty per bulan berdasarkan brand per branch_name
foreach ($branches as $branch_name) {
    echo "<h2>Branch: $branch_name</h2>";

    $sql_brand = "
    SELECT brand, bulan, SUM(qty) AS total_qty
    FROM databasemaju
    WHERE branch_name = '$branch_name' AND brand NOT IN ('HIFUTURE', 'REALME AIOT', 'BRAND', 'OLIKE', 'ITEL IOT')
    GROUP BY brand, bulan
    ORDER BY brand, FIELD(bulan, 'January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December')";

    $result_brand = $conn->query($sql_brand);
    $data_brand = [];

    if ($result_brand->num_rows > 0) {
        while($row = $result_brand->fetch_assoc()) {
            $brand = $row['brand'];
            $bulan = $row['bulan'];
            $total_qty = $row['total_qty'];

            if (!isset($data_brand[$brand])) {
                $data_brand[$brand] = array_fill_keys($bulan_array, 0); // Inisialisasi dengan 0 untuk setiap bulan
            }
            $data_brand[$brand][$bulan] = $total_qty;
        }

        // Tampilkan tabel data brand per branch_name
        echo "<table>
                <tr>
                    <th>Brand</th>
                    <th>January</th>
                    <th>February</th>
                    <th>March</th>
                    <th>April</th>
                    <th>May</th>
                    <th>June</th>
                    <th>July</th>
                    <th>August</th>
                    <th>September</th>
                    <th>October</th>
                    <th>November</th>
                    <th>December</th>
                </tr>";

        foreach ($data_brand as $brand => $qty) {
            echo "<tr>
                    <td>$brand</td>";
            foreach ($bulan_array as $bulan) {
                echo "<td>" . number_format($qty[$bulan], 0, ',', '.') . "</td>";
            }
            echo "</tr>";
        }

        echo "</table>";
    } else {
        echo "<p>Tidak ada data untuk $branch_name.</p>";
    }
}

$conn->close();
?>

<?php include 'footer.php' ?>
</body>
</html>
