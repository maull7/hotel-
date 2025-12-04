<?php
require '../db.php';
data($koneksi);
function data($koneksi) {
    $result = $koneksi->query("SELECT * FROM pemesanan ORDER BY id DESC");
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - Pemesanan Hotel</title>
     <link rel="stylesheet" href="styledasboard.css">
  <style>
    body {
      font-family: "Poppins", sans-serif;
      background: #f7f7f7;
    }
    .topbar {
      display: flex;
      justify-content: space-between;
      align-items: center;
      padding: 15px 30px;
      background: #6c5ce7;
      color: white;
    }
    .user-box {
      display: flex;
      align-items: center;
      gap: 10px;
    }
    .user-box img {
      border-radius: 50%;
      width: 40px;
      height: 40px;
    }
    .user-box a {
      color: white;
      text-decoration: none;
      font-weight: bold;
      padding: 5px 10px;
      border: 1px solid white;
      border-radius: 5px;
    }
    .container {
      padding: 20px 30px;
    }

    /* Cards */
    .cards {
      display: flex;
      flex-wrap: wrap;
      gap: 20px;
      margin-bottom: 30px;
    }
    .card {
      flex: 1 1 150px;
      padding: 20px;
      background: #fff;
      border-radius: 15px;
      box-shadow: 0 4px 12px rgba(0,0,0,0.1);
      text-align: center;
    }
    .card .num {
      font-size: 24px;
      font-weight: bold;
      margin-top: 10px;
    }
    .card .green {
      color: green;
    }

    /* Layout rows */
    .row {
      display: flex;
      gap: 20px;
      flex-wrap: wrap;
      margin-bottom: 30px;
    }
    .chart {
      flex: 1 1 400px;
      background: #fff;
      padding: 20px;
      border-radius: 15px;
      box-shadow: 0 4px 12px rgba(0,0,0,0.1);
    }

    /* Table Section */
    .table-section {
      background: #fff;
      padding: 20px;
      border-radius: 15px;
      box-shadow: 0 4px 12px rgba(0,0,0,0.1);
      margin-bottom: 30px;
    }
    table {
      width: 100%;
      border-collapse: collapse;
      margin-top: 15px;
    }
    table th, table td {
      padding: 10px;
      text-align: left;
      border-bottom: 1px solid #ddd;
    }

    .checkin { color: green; font-weight: bold; }
    .checkout { color: red; font-weight: bold; }
    .resv { color: orange; font-weight: bold; }
  </style>
    <style>
        body { font-family: Arial, sans-serif; margin: 0; background: #f4f6f9; }
        .sidebar { width: 240px; background: #1f2937; color: white; position: fixed; top: 0; bottom: 0; padding: 20px; }
        .sidebar h2 { margin-top: 0; text-align: center; }
        .sidebar a { display: block; color: #e5e7eb; padding: 10px; text-decoration: none; border-radius: 6px; }
        .sidebar a:hover { background: #374151; }
        .main { margin-left: 260px; padding: 20px; }
        .card { background: white; padding: 20px; border-radius: 10px; box-shadow: 0 2px 6px rgba(0,0,0,0.1); }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        table th, table td { border: 1px solid #ddd; padding: 10px; }
        table th { background: #e5e7eb; }
        .btn { padding: 6px 12px; border: none; border-radius: 6px; cursor: pointer; }
        .edit { background: #3b82f6; color: white; }
        .hapus { background: #ef4444; color: white; }
        .tambah { background: #10b981; color: white; padding: 8px 14px; }
    </style>
</head>
<body>
    <div class="sidebar">
        <h2>Admin Hotel</h2>
        <a href="dash.php">Dashboard</a>
        <a href="data_pemesanan.php">Data Pemesanan</a>
        <a href="data_kamar.php">Data Kamar</a>
        <a href="data_pengguna.php">Data Pengguna</a>
        <a href="../logout.php">Logout</a>
    </div>

    <div class="main">
       
<div class="container">

  <!-- CARD SECTION -->
  <div class="cards">
    <div class="card">
      <h4>Total Kamar</h4>
      <p class="num">120</p>
    </div>
    <div class="card">
      <h4>Kamar Terisi</h4>
      <p class="num">85</p>
    </div>
    <div class="card">
      <h4>Kamar Tersedia</h4>
      <p class="num green">35</p>
    </div>
    <div class="card">
      <h4>Check-in Hari Ini</h4>
      <p class="num">18</p>
    </div>
    <div class="card">
      <h4>Check-out Hari Ini</h4>
      <p class="num">10</p>
    </div>
    <div class="card">
      <h4>Reservasi Aktif</h4>
      <p class="num">42</p>
    </div>
  </div>

  <!-- CHART ROW -->
  <div class="row">
    <div class="chart">
      <h3>Grafik Okupansi Mingguan</h3>
      <canvas id="chart1"></canvas>
    </div>

    <div class="chart">
      <h3>Pendapatan Bulanan</h3>
      <canvas id="chart2"></canvas>
    </div>
  </div>

  <!-- AKTIVITAS TERBARU -->
  <div class="table-section">
    <h3>Aktivitas Terbaru</h3>
    <table>
      <tr>
        <th>Tamu</th>
        <th>Jenis</th>
        <th>Kamar</th>
        <th>Waktu</th>
      </tr>
      <tr>
        <td>Aulia Putri</td>
        <td class="checkin">Check-in</td>
        <td>Deluxe 203</td>
        <td>10:20</td>
      </tr>
      <tr>
        <td>Rizki Ahmad</td>
        <td class="checkout">Check-out</td>
        <td>Superior 102</td>
        <td>09:40</td>
      </tr>
      <tr>
        <td>Wulan Sari</td>
        <td class="resv">Reservasi</td>
        <td>Suite 501</td>
        <td>08:15</td>
      </tr>
    </table>
  </div>

  <!-- REKAPAN BULANAN -->
  <div class="table-section">
    <h3>Rekapan Per Bulan</h3>
    <table>
      <tr>
        <th>Bulan</th>
        <th>Total Check-in</th>
        <th>Total Check-out</th>
        <th>Total Reservasi</th>
        <th>Okupansi Rata-rata</th>
      </tr>
      <tr>
        <td>Januari</td>
        <td>320</td>
        <td>300</td>
        <td>150</td>
        <td>78%</td>
      </tr>
      <tr>
        <td>Februari</td>
        <td>280</td>
        <td>270</td>
        <td>130</td>
        <td>74%</td>
      </tr>
      <tr>
        <td>Maret</td>
        <td>350</td>
        <td>340</td>
        <td>160</td>
        <td>81%</td>
      </tr>
    </table>
  </div>

  <!-- PENDAPATAN BULANAN -->
  <div class="table-section">
    <h3>Pendapatan Per Bulan</h3>
    <table>
        <tr>
            <th>Bulan</th>
            <th>Pendapatan Kamar</th>
            <th>Pendapatan Lain-lain</th>
            <th>Total Pendapatan</th>
        </tr>
        <tr>
            <td>Januari</td>
            <td>Rp 125.000.000</td>
            <td>Rp 18.500.000</td>
            <td><b>Rp 143.500.000</b></td>
        </tr>
        <tr>
            <td>Februari</td>
            <td>Rp 110.000.000</td>
            <td>Rp 16.200.000</td>
            <td><b>Rp 126.200.000</b></td>
        </tr>
        <tr>
            <td>Maret</td>
            <td>Rp 132.000.000</td>
            <td>Rp 20.100.000</td>
            <td><b>Rp 152.100.000</b></td>
        </tr>
    </table>
  </div>

</div>

    </div>
</body>
</html>

<?php } ?>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="dashboard.js"></script>