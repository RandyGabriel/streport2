<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require 'vendor/autoload.php';
require 'config.php';

use PhpOffice\PhpSpreadsheet\IOFactory;

if (isset($_POST["update"])) {
    $file = $_FILES["file"]["tmp_name"];
    
    if ($file) {
        $spreadsheet = IOFactory::load($file);
        $sheetData = $spreadsheet->getActiveSheet()->toArray(null, true, true, true);
        
        // Periksa apakah tabel sudah memiliki header yang sama
        $sql = "SHOW COLUMNS FROM databasemaju";
        $result = $conn->query($sql);
        $existingHeaders = [];
        
        while ($row = $result->fetch_assoc()) {
            $existingHeaders[] = $row['Field'];
        }

        // Siapkan prepared statement
        $stmt = $conn->prepare("INSERT INTO databasemaju (hari, bulan, tahun, customer, alias, retail_code, branch_name, city, marketing, nomor_invoice, product_name, brand, qty, price, total_price, additional_info) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

        foreach ($sheetData as $key => $row) {
            // Lewati baris header jika sudah ada
            if ($key == 1 && array_intersect($existingHeaders, array_keys($row)) == $existingHeaders) {
                continue;
            }

            $hari = $row['A'];
            $bulan = $row['B'];
            $tahun = $row['C'];
            $customer = $row['D'];
            $alias = $row['E'];
            $retail_code = $row['F'];
            $branch_name = $row['G'];
            $city = $row['H'];
            $marketing = $row['I'];
            $nomor_invoice = $row['J'];
            $product_name = $row['K'];
            $brand = $row['L'];
            $qty = $row['M'];
            $price = floatval(str_replace(',', '', $row['N'])); // Konversi ke float
            $total_price = floatval(str_replace(',', '', $row['O'])); // Konversi ke float
            $additional_info = $row['P'];

            // Bind parameter dan eksekusi statement
            $stmt->bind_param("ssissssssssssdds", $hari, $bulan, $tahun, $customer, $alias, $retail_code, $branch_name, $city, $marketing, $nomor_invoice, $product_name, $brand, $qty, $price, $total_price, $additional_info);
            if (!$stmt->execute()) {
                echo "Error: " . $stmt->error;
            }
        }
        echo "Data berhasil diperbarui ke database.";
    } else {
        echo "File tidak ditemukan.";
    }
}

$conn->close();
?>
