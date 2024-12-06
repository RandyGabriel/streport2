<?php
require 'config.php';

if (isset($_GET['marketing'])) {
    $marketing = $_GET['marketing'];

    // Query untuk mendapatkan jumlah total Qty per bulan berdasarkan brand untuk marketing yang dipilih
    $sql = "
    SELECT brand, 
           bulan, 
           SUM(qty) AS total_qty
    FROM databasemaju
    WHERE marketing = ? AND brand NOT IN ('REALME AIOT', 'HIFUTURE')
    GROUP BY brand, bulan
    ORDER BY FIELD(bulan, 'January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December')";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $marketing);
    $stmt->execute();
    $result = $stmt->get_result();

    // Inisialisasi array untuk menyimpan data
    $data = [];
    $bulan_array = ['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'];

    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $brand = $row['brand'];
            $bulan = $row['bulan'];
            $total_qty = $row['total_qty'];

            if (!isset($data[$brand])) {
                $data[$brand] = array_fill_keys($bulan_array, 0); // Inisialisasi dengan 0 untuk setiap bulan
            }
            $data[$brand][$bulan] = $total_qty;
        }

        // Tambahkan gaya CSS untuk tampilan modern dengan gradasi warna
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
            .zero-value {
                background: linear-gradient(to right, #ffcccc, #ff9999); /* Gradasi merah untuk nilai nol */
            }
            .back-button {
                display: inline-block;
                padding: 10px 20px;
                font-size: 14px;
                font-weight: bold;
                color: white;
                background-color: #4CAF50;
                border: none;
                border-radius: 4px;
                text-decoration: none;
                margin-bottom: 20px;
            }
            .back-button:hover {
                background-color: #45a049;
            }
        </style>";

        // Tambahkan tombol Back
        echo "<a href='javascript:history.back()' class='back-button'>Back</a>";

        // Tampilkan tabel
        echo "<h2>Detail untuk Marketing: $marketing</h2>";
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

        foreach ($data as $brand => $qty) {
            echo "<tr>
                    <td>$brand</td>";
            foreach ($bulan_array as $bulan) {
                $class = $qty[$bulan] == 0 ? 'class="zero-value"' : '';
                echo "<td $class>" . number_format($qty[$bulan], 0, ',', '.') . "</td>";
            }
            echo "</tr>";
        }

        echo "</table>";

        // Query untuk mendapatkan jumlah total Qty per bulan berdasarkan brand dan alias untuk marketing yang dipilih
        $sql_alias = "
        SELECT brand, 
               alias,
               bulan, 
               SUM(qty) AS total_qty
        FROM databasemaju
        WHERE marketing = ? AND brand NOT IN ('REALME AIOT', 'HIFUTURE')
        GROUP BY brand, alias, bulan
        ORDER BY brand, alias, FIELD(bulan, 'January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December')";

        $stmt_alias = $conn->prepare($sql_alias);
        $stmt_alias->bind_param("s", $marketing);
        $stmt_alias->execute();
        $result_alias = $stmt_alias->get_result();

        // Inisialisasi array untuk menyimpan data
        $data_alias = [];

        if ($result_alias->num_rows > 0) {
            while ($row = $result_alias->fetch_assoc()) {
                $brand = $row['brand'];
                $alias = $row['alias'];
                $bulan = $row['bulan'];
                $total_qty = $row['total_qty'];

                if (!isset($data_alias[$brand])) {
                    $data_alias[$brand] = [];
                }

                if (!isset($data_alias[$brand][$alias])) {
                    $data_alias[$brand][$alias] = array_fill_keys($bulan_array, 0); // Inisialisasi dengan 0 untuk setiap bulan
                }

                $data_alias[$brand][$alias][$bulan] = $total_qty;
            }

            // Tampilkan tabel per brand
            foreach ($data_alias as $brand => $aliases) {
                echo "<h3>Brand: $brand</h3>";
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

                foreach ($aliases as $alias => $qty) {
                    echo "<tr>
                            <td class='left-align'>$alias</td>"; // Alias rata kiri
                    foreach ($bulan_array as $bulan) {
                        $class = $qty[$bulan] == 0 ? 'class="zero-value"' : '';
                        echo "<td $class>" . number_format($qty[$bulan], 0, ',', '.') . "</td>";
                    }
                    echo "</tr>";
                }

                echo "</table>";
            }
        } else {
            echo "Tidak ada data yang ditemukan.";
        }

        $stmt_alias->close();
    } else {
        echo "Tidak ada data yang ditemukan.";
    }

    $stmt->close();
} else {
    echo "Marketing tidak ditemukan.";
}

$conn->close();
?>
