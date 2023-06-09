<?php
// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();

include "db/koneksi.php";
include "./lib/hak-akses.php";
include "lib/user/cari-user.php";

if (!aksesUser($conn)) {
  header("Location: login.php");
}

include "lib/parkiran/cari-parkiran.php";
include "lib/motor/cari-motor.php";
include "config.php";

$halaman_aktif = isset($_GET['halaman']) ? $_GET['halaman'] : 1;
$keyword = isset($_GET['keyword']) ? $_GET['keyword'] : "";
$parkiran = ambilSemuaParkiran($conn);

[
  'motor_arr' => $motor_arr,
  "total_halaman" => $total_halaman,
  "halaman_sebelumnya" => $halaman_sebelumnya,
  "halaman_berikutnya" => $halaman_berikutnya
] = cariMotorDariUserId(
  $conn,
  $_SESSION["id"],
  $keyword,
  $halaman_aktif,
  JUMLAH_PER_HALAMAN
);
?>

<!DOCTYPE html>
<html lang="en" class="overflow-x-hidden">

<head>
  <meta charset="UTF-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <script defer>
    window.keyword = '<?= $keyword ?>';
    window.tabelMaksHalaman = <?= $total_halaman ?>;
  </script>
  <script src="./public/js/page-js/user/user-index.js" defer type="module"></script>
  <?php include "components/head-tags.php"; ?>
  <title>Halaman User</title>
</head>

<body class="bg-slate-100 dark:bg-slate-950">
  <?php include "components/user-sidebar.php"; ?>

  <div id="content" style="width: calc(100% - 20rem);" class="translate-x-80">
    <header class="sticky top-0 z-[10000] py-2 bg-slate-50/50 dark:bg-slate-950/50 backdrop-blur-lg shadow shadow-slate-300 dark:shadow-slate-800">
      <div class="flex flex-wrap items-center justify-between gap-2 px-6 mx-auto md:gap-0">
        <!-- hamburger menu -->
        <div class="basis-1/3">
          <button id="hamburger-menu-btn" type="button" class="w-10 h-10 text-2xl transition-colors duration-200 rounded-lg hover:bg-slate-200 active:bg-slate-300 dark:hover:bg-slate-700 dark:active:bg-slate-800">
            <i class="text-slate-800 dark:text-slate-300 fa-solid fa-bars"></i>
          </button>
        </div>
      </div>
    </header>

    <main class="px-6 mt-8">
      <h1 class="mb-6 text-4xl font-medium text-center capitalize dark:text-slate-100">Tabel Motor</h1>

      <!-- table list motor -->
      <!-- search bar -->
      <form method="GET" class="relative flex items-center mb-3 border rounded-lg shadow shadow-slate-200 border-slate-400 dark:border-slate-700 dark:shadow-slate-700">
        <input type="hidden" value="<?= $halaman_aktif ?>" name="halaman">

        <input type="search" name="keyword" id="search-data-tabel" placeholder="Cari" value="<?= $keyword ?>" class="w-full px-4 py-2 transition-colors bg-transparent border-l-0 rounded-md rounded-l-none outline-none placeholder:text-transparent peer disabled:cursor-not-allowed disabled:opacity-20 dark:text-slate-200">

        <label class="absolute px-1 text-sm text-blue-500 transition-all scale-90 -translate-x-2 -translate-y-8 dark:text-blue-400 left-4 top-1/2 peer-placeholder-shown:text-slate-500 bg-slate-100 dark:bg-slate-950 peer-focus:-translate-x-2 peer-focus:-translate-y-8 peer-focus:scale-90 peer-placeholder-shown:-translate-y-1/2 peer-placeholder-shown:translate-x-0 peer-placeholder-shown:scale-100" for="search-data-tabel">
          Cari Motor
        </label>

        <button class="w-10 h-10 text-xl text-blue-500 transition-colors duration-200 rounded-r-lg dark:text-blue-400 hover:bg-slate-200 active:bg-slate-300 dark:hover:bg-slate-600 dark:active:bg-slate-700">
          <i class="fa-solid fa-search"></i>
        </button>
      </form>

      <!-- tabel semi-responsive -->
      <div class="mt-2 rounded-lg shadow shadow-slate-300 dark:shadow-slate-700 overflow-clip">
        <div class="w-full overflow-auto">
          <table id="tabel-user-motor" class="w-full table-auto overflow-clip">
            <thead>
              <tr class="[&>th]:p-2 bg-slate-200 dark:bg-slate-800 dark:text-slate-300 text-slate-700">
                <th>No</th>
                <th>Plat</th>
                <th>Lokasi Parkir</th>
                <th>Tanggal Masuk</th>
              </tr>
            </thead>

            <tbody>
              <!-- isi list motor -->
              <?php if (count($motor_arr) > 0) : ?>
                <?php for ($i = 0; $i < count($motor_arr); $i++) : ?>
                  <tr class="[&>td]:p-2 text-center dark:text-slate-400 even:bg-slate-50 dark:even:bg-slate-900">
                    <td><?= $i + (($halaman_aktif - 1) * JUMLAH_PER_HALAMAN) + 1 ?></td>
                    <td><?= $motor_arr[$i]['plat']; ?></td>
                    <td><?= $motor_arr[$i]['lokasi_parkir']; ?></td>
                    <td><?= $motor_arr[$i]['tanggal_masuk']; ?></td>
                  </tr>
                <?php endfor ?>
              <?php else : ?>
                <tr>
                  <td colspan="10" class="p-2 font-medium text-center text-slate-400 dark:bg-slate-900">
                    Tabel Masih Kosong
                  </td>
                </tr>
              <?php endif ?>
            </tbody>
          </table>

        </div>

        <!-- kontrol dari tabel -->
        <div class="flex items-center justify-center w-full gap-2 px-4 py-0.5 bg-slate-200 dark:bg-slate-800 dark:text-slate-300 text-slate-700">
          <?php
          $link_hal_sebelum = $halaman_sebelumnya  !== null ? "?halaman=$halaman_sebelumnya" : "#";
          ?>
          <a href='<?= $link_hal_sebelum ?>' id="halaman-sebelumnya-btn" class="grid w-10 h-10 text-xl text-blue-500 transition-colors duration-200 dark:text-blue-400 rounded-xl place-content-center disabled:text-slate-400 dark:disabled:ext-slate-600 disabled:hover:bg-transparent disabled:active:bg-transparent hover:bg-blue-200 active:bg-blue-300">
            <i class="fa-solid fa-left-long"></i>
          </a>

          <span id="indikator-halaman">
            <input class="w-auto pl-2 shadow rounded-xl dark:bg-slate-700 dark:text-slate-300" type="number" min="1" max="<?= $total_halaman ?>" id="input-halaman" value="<?= $halaman_aktif ?>">
            / <?= $total_halaman ?>
          </span>

          <?php
          $link_hal_berikut = $halaman_berikutnya  !== null ? "?halaman=$halaman_berikutnya" : "#";
          ?>

          <a href="<?= $link_hal_berikut ?>" id="halaman-berikutnya-btn" class="grid w-10 h-10 text-xl text-blue-500 transition-colors duration-200 dark:text-blue-400 rounded-xl place-content-center disabled:text-slate-400 dark:disabled:ext-slate-600 disabled:hover:bg-transparent disabled:active:bg-transparent hover:bg-blue-200 active:bg-blue-300">
            <i class="fa-solid fa-right-long"></i>
          </a>
        </div>
      </div>

      <!-- peta parkiran -->
      <section class="my-16">
        <?php include "./components/peta-parkiran.php" ?>
      </section>
    </main>
  </div>

  <dialog id="dialog">
    <div class="flex items-center justify-between">
      <span id="dialog-title" class="text-2xl font-medium dark:text-slate-100">Detail Motor Terparkir</span>
      <button id="close-dialog-btn" class="w-10 h-10 text-2xl text-red-500 transition-colors duration-200 rounded-xl hover:bg-red-200 active:bg-red-300">
        <i class="fa-solid print:hidden fa-xmark"></i>
      </button>
    </div>

    <?php include "./components/konten-dialog/detail-motor-terparkir.php" ?>
  </dialog>
</body>

</html>