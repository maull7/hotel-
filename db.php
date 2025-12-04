<?php

$host = "localhost";
$user = "root";
$password = "";
$db = "hotelmantap";

$koneksi =mysqli_connect($host, $user, $password, $db);

if(!$koneksi){
    die("koneksi gagal: " . mysqli_connect_error());
}
echo "koneksi berhasil!";
?>