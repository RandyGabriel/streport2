<?php
include 'header.php';
require 'config.php';

$brands = ["REALME SMARTPHONE", "ZTE", "ITEL"]; // Daftar brand yang akan ditampilkan
$bulan_array = ['December', 'November', 'October', 'September', 'August', 'July', 'June', 'May', 'June', 'April', 'March', 'February', 'January'];
$top_limit = 10; // Batas alias teratas yang akan ditampilkan

// Ambil daftar branch_name untuk dropdown
$branch_query = "SELECT DISTINCT branch_name FROM databasemaju WHERE branch_name IS NOT NULL AND branch_name != '' AND branch_name != 'Branch Name'";
$branch_result = $conn->query($branch_query);
$branches = [];

while ($row = $branch_result->fetch_assoc()) {
    $branches[] = $row['branch_name'];
}

// Periksa apakah filter branch_name dipilih
$selected_branch = isset($_POST['branch_name']) ? $_POST['branch_name'] : '';

// Ambil data dari database
$data = [];
$totals = [];

foreach ($brands as $brand) {
    $sql = "
    SELECT alias, bulan, SUM(qty) AS total_qty
    FROM databasemaju
    WHERE brand = '$brand'";
    if ($selected_branch) {
        $sql .= " AND branch_name = '$selected_branch'";
    }
    $sql .= "
    GROUP BY alias, bulan
    ORDER BY alias, FIELD(bulan, 'January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December')";

    $result = $conn->query($sql);
    
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $alias = $row['alias'];
            $bulan = $row['bulan'];
            $total_qty = $row['total_qty'];

            if (!isset($data[$bulan])) {
                $data[$bulan] = [];
            }
            if (!isset($data[$bulan][$alias])) {
                $data[$bulan][$alias] = array_fill_keys($brands, 0);
                $totals[$bulan][$alias] = 0;
            }
            $data[$bulan][$alias][$brand] += $total_qty;
            $totals[$bulan][$alias] += $total_qty;
        }
    }
}

// Urutkan alias berdasarkan total qty dan ambil 10 teratas per bulan
foreach ($totals as $bulan => $total_qty) {
    arsort($totals[$bulan]);
    $totals[$bulan] = array_slice($totals[$bulan], 0, $top_limit, true);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>TOP 10 Dealer ST</title>
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
        .left-align {
            text-align: left;
        }
        .filter-form {
            display: flex;
            align-items: center;
            margin-bottom: 20px;
        }
        .filter-form select {
            padding: 8px;
            margin-right: 10px;
            border-radius: 4px;
            border: 1px solid #ddd;
            font-size: 14px;
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
    <h2>TOP 10 Dealer ST</h2>

    <form class="filter-form" method="post" action="">
        <select name="branch_name">
            <option value="">All Branches</option>
            <?php foreach ($branches as $branch): ?>
                <option value="<?php echo $branch; ?>" <?php echo $selected_branch == $branch ? 'selected' : ''; ?>><?php echo $branch; ?></option>
            <?php endforeach; ?>
        </select>
        <button type="submit">Filter</button>
    </form>

    <?php foreach ($bulan_array as $bulan): ?>
        <?php if (isset($totals[$bulan])): ?>
            <h3><?php echo $bulan; ?></h3>
            <table>
                <tr>
                    <th>Alias</th>
                    <?php foreach ($brands as $brand): ?>
                        <th><?php echo $brand; ?></th>
                    <?php endforeach; ?>
                    <th>Total Qty</th>
                </tr>

                <?php foreach ($totals[$bulan] as $alias => $total_qty): ?>
                    <tr>
                        <td class="left-align"><?php echo $alias; ?></td>
                        <?php foreach ($brands as $brand): ?>
                            <td><?php echo number_format($data[$bulan][$alias][$brand], 0, ',', '.'); ?></td>
                        <?php endforeach; ?>
                        <td><?php echo number_format($total_qty, 0, ',', '.'); ?></td>
                    </tr>
                <?php endforeach; ?>
            </table>
        <?php endif; ?>
    <?php endforeach; ?>

<?php include 'footer.php'; ?>
</body>
</html>

<?php
$conn->close();
?>
