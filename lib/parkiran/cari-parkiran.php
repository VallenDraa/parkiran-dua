<?php
function cariParkiranKosong(mysqli $conn)
{
  $token_parkiran_kosong = [];

  $data = mysqli_query($conn, "SELECT * FROM tempat_parkir WHERE plat_motor IS NULL");

  while ($hasil_fetch = mysqli_fetch_assoc($data)) {
    array_push($token_parkiran_kosong, $hasil_fetch['lokasi_parkir']);
  }

  return $token_parkiran_kosong;
}

function cekParkiranTerisi(mysqli $conn, string $token_parkiran)
{
  $plat_motor = null;

  $stmt = mysqli_prepare(
    $conn,
    "SELECT plat_motor FROM tempat_parkir WHERE lokasi_parkir = ?"
  );

  mysqli_stmt_bind_param($stmt, "s", $token_parkiran);
  mysqli_stmt_execute($stmt);

  mysqli_stmt_bind_result($stmt, $plat_motor);
  mysqli_stmt_close($stmt);

  $terisi = $plat_motor !== null;

  return $terisi;
}
