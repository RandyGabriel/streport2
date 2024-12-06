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

// Periksa apakah filter dipilih
$filter = isset($_POST['filter']) ? $_POST['filter'] : '';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>ITEL REPORT</title>
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
            font-size: 16px;
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
        .zero-value {
            background: linear-gradient(to right, #ffcccc, #ff9999);
        }
        .filter-form {
            display: flex;
            align-items: center;
            margin-bottom: 20px;
        }
        .filter-form button {
            padding: 8px 16px;
            background-color: #4CAF50;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 14px;
            margin-right: 10px;
        }
        .filter-form button:hover {
            background-color: #45a049;
        }
        .left-align {
            text-align: left;
        }
    </style>
</head>
<body>
    <h2>ITEL REPORT</h2>
    <form class="filter-form" method="post" action="">
        <button type="submit" name="filter" value="tipe">By Tipe</button>
        <button type="submit" name="filter" value="sales">By Sales</button>
        <button type="submit" name="filter" value="store">STORE</button>
    </form>

<?php
foreach ($branches as $branch_name) {
    echo "<h2>Branch: $branch_name</h2>";

    if ($filter == 'tipe') {
        $sql = "
        SELECT product_name, bulan, SUM(qty) AS total_qty
        FROM databasemaju
        WHERE branch_name = '$branch_name' AND brand = 'ITEL'
        GROUP BY product_name, bulan
        ORDER BY product_name";

        $result = $conn->query($sql);
        $data = [];
        $bulan_array = ['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'];
        $latest_month_qty = [];
        $total_qty_footer = array_fill_keys($bulan_array, 0);

        if ($result->num_rows > 0) {
            while($row = $result->fetch_assoc()) {
                $product_name = $row['product_name'];
                $bulan = $row['bulan'];
                $total_qty = $row['total_qty'];

                if (!isset($data[$product_name])) {
                    $data[$product_name] = array_fill_keys($bulan_array, 0); // Inisialisasi dengan 0 untuk setiap bulan
                }
                $data[$product_name][$bulan] += $total_qty;

                // Simpan qty bulan terakhir
                if ($bulan == end($bulan_array)) {
                    $latest_month_qty[$product_name] = $data[$product_name][$bulan];
                }
            }

            // Urutkan data berdasarkan qty terbesar bulan terakhir
            arsort($latest_month_qty);

            echo "<table>
                    <tr>
                        <th>Product Name</th>
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

            foreach ($latest_month_qty as $product_name => $qty_last_month) {
                echo "<tr>
                        <td class='left-align'>$product_name</td>";
                foreach ($bulan_array as $bulan) {
                    $cell_class = $data[$product_name][$bulan] == 0 ? 'class="zero-value"' : '';
                    echo "<td $cell_class>" . number_format($data[$product_name][$bulan], 0, ',', '.') . "</td>";
                    $total_qty_footer[$bulan] += $data[$product_name][$bulan]; // Tambahkan total qty untuk footer hanya dari baris yang muncul
                }
                echo "</tr>";
            }

            // Footer total qty
            echo "<tr>
                    <th>Total Qty</th>";
            foreach ($bulan_array as $bulan) {
                echo "<th>" . number_format($total_qty_footer[$bulan], 0, ',', '.') . "</th>";
            }
            echo "</tr>";

            echo "</table>";
        } else {
            echo "<p>Tidak ada data untuk $branch_name.</p>";
        }
    } elseif ($filter == 'sales') {
        $sql = "
        SELECT marketing, bulan, SUM(qty) AS total_qty
        FROM databasemaju
        WHERE branch_name = '$branch_name' AND brand = 'ITEL'
        GROUP BY marketing, bulan
        ORDER BY marketing, FIELD(bulan, 'January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December')";

        $result = $conn->query($sql);
        $data = [];
        $bulan_array = ['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'];
        $total_qty_footer = array_fill_keys($bulan_array, 0);

        if ($result->num_rows > 0) {
            while($row = $result->fetch_assoc()) {
                $marketing = $row['marketing'];
                $bulan = $row['bulan'];
                $total_qty = $row['total_qty'];

                // Hilangkan baris jika marketing kosong atau berisi "ADMIN SALES"
                if (empty($marketing) || $marketing == 'ADMIN SALES') {
                    continue;
                }

                if (!isset($data[$marketing])) {
                    $data[$marketing] = array_fill_keys($bulan_array, 0); // Inisialisasi dengan 0 untuk setiap bulan
                }
                $data[$marketing][$bulan] += $total_qty;
            }

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
                echo "<tr>
                        <td class='left-align'>$marketing</td>";
                foreach ($bulan_array as $bulan) {
                    $cell_class = $qty[$bulan] == 0 ? 'class="zero-value"' : '';
                    echo "<td $cell_class>" . number_format($qty[$bulan], 0, ',', '.') . "</td>";
                    $total_qty_footer[$bulan] += $qty[$bulan]; // Tambahkan total qty untuk footer hanya dari baris yang muncul
                }
                echo "</tr>";
            }

            // Footer total qty
            echo "<tr>
                    <th>Total Qty</th>";
            foreach ($bulan_array as $bulan) {
                echo "<th>" . number_format($total_qty_footer[$bulan], 0, ',', '.') . "</th>";
            }
            echo "</tr>";

            echo "</table>";
        } else {
            echo "<p>Tidak ada data untuk $branch_name.</p>";
        }
    } elseif ($filter == 'store') {
        $sql = "
        SELECT alias, bulan, SUM(qty) AS total_qty
        FROM databasemaju
        WHERE branch_name = '$branch_name' AND brand = 'ITEL'
        GROUP BY alias, bulan
        ORDER BY alias";

        $result = $conn->query($sql);
        $data = [];
        $bulan_array = ['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'];
        $latest_month_qty = [];
        $total_qty_footer = array_fill_keys($bulan_array, 0);

        if ($result->num_rows > 0) {
            while($row = $result->fetch_assoc()) {
                $alias = $row['alias'];
                $bulan = $row['bulan'];
                $total_qty = $row['total_qty'];

                // Hilangkan baris jika alias kosong atau berisi "ADMIN SALES"
                if (empty($alias) || $alias == 'ADMIN SALES') {
                    continue;
                }

                if (!isset($data[$alias])) {
                    $data[$alias] = array_fill_keys($bulan_array, 0); // Inisialisasi dengan 0 untuk setiap bulan
                }
                $data[$alias][$bulan] += $total_qty;

                // Simpan qty bulan terakhir
                if ($bulan == end($bulan_array)) {
                    $latest_month_qty[$alias] = $data[$alias][$bulan];
                }
            }

            // Urutkan data berdasarkan qty terbesar bulan terakhir
            arsort($latest_month_qty);

            echo "<table>
                    <tr>
                        <th>Alias</th>
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

            foreach ($latest_month_qty as $alias => $qty_last_month) {
                echo "<tr>
                        <td class='left-align'>$alias</td>";
                foreach ($bulan_array as $bulan) {
                    $cell_class = $data[$alias][$bulan] == 0 ? 'class="zero-value"' : '';
                    echo "<td $cell_class>" . number_format($data[$alias][$bulan], 0, ',', '.') . "</td>";
                    $total_qty_footer[$bulan] += $data[$alias][$bulan]; // Tambahkan total qty untuk footer hanya dari baris yang muncul
                }
                echo "</tr>";
            }

            // Footer total qty
            echo "<tr>
                    <th>Total Qty</th>";
            foreach ($bulan_array as $bulan) {
                echo "<th>" . number_format($total_qty_footer[$bulan], 0, ',', '.') . "</th>";
            }
            echo "</tr>";

            echo "</table>";
        } else {
            echo "<p>Tidak ada data untuk $branch_name.</p>";
        }
    }
}

$conn->close();
?>

<?php include 'footer.php' ?>
</body>
</html>
