<?php
include "../db/koneksi.php";

$username = $_POST['username'];
$password = $_POST['password'];

$login = mysqli_query($conn, "SELECT username FROM user where `username` = '$username'");

if (mysqli_num_rows($login) == 0) {
    $edit = "UPDATE user SET username='$_POST[username]', password='$_POST[password]' where id='$_POST[id]'";
    if (!mysqli_query($conn, $edit))
        die(mysqli_error($conn));
    echo "<script>alert('Selamat, data telah di update');window.location.href='../user.php';</script>";
} else {
    echo "<script>alert('Username yang dimasukan sudah terpakai');window.history.back();</script>";
};

mysqli_close($conn);
// window . location . href = 'ganti.php';