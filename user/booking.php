<?php
$hotels = [
    1=>['name'=>'Hotel A','price'=>500000],
    2=>['name'=>'Hotel B','price'=>750000],
    3=>['name'=>'Hotel C','price'=>600000],
];

$id = $_GET['id'];
$hotel = $hotels[$id];
$checkin = $_GET['checkin'];
$checkout = $_GET['checkout'];
$guests = $_GET['guests'];

if($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'];
    $email = $_POST['email'];
    $phone = $_POST['phone'];

    $booking_data = "$name,$email,$phone,$hotel[name],$checkin,$checkout,$guests\n";
    file_put_contents('bookings.txt', $booking_data, FILE_APPEND);

    echo "<h3 style='text-align:center; margin-top:50px;'>Pemesanan Berhasil!</h3>";
    echo "<p style='text-align:center;'>Terima kasih, $name. Hotel $hotel[name] telah dipesan dari $checkin sampai $checkout untuk $guests tamu.</p>";
    exit;
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Pemesanan Hotel</title>
    <style>
        body { font-family: Arial; background:#f5f5f5; margin:0; padding:0;}
        .container { width:90%; max-width:500px; margin:50px auto; background:#fff; padding:30px; border-radius:8px; box-shadow:0 0 10px rgba(0,0,0,0.1);}
        h2 { text-align:center; color:#333; }
        label { display:block; margin-top:15px; color:#555; }
        input { width:100%; padding:10px; margin-top:5px; border-radius:4px; border:1px solid #ccc; }
        button { margin-top:20px; width:100%; padding:12px; background:#28a745; color:white; border:none; border-radius:4px; font-size:16px; cursor:pointer; }
        button:hover { background:#218838; }
    </style>
</head>
<body>
    <div class="container">
        <h2>Pesan Hotel: <?php echo $hotel['name']; ?></h2>
        <form method="POST">
            <label>Nama:</label>
            <input type="text" name="name" required>

            <label>Email:</label>
            <input type="email" name="email" required>

            <label>Telepon:</label>
            <input type="text" name="phone" required>

            <button type="submit">Pesan Sekarang</button>
        </form>
    </div>
</body>
</html>
