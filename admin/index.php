<?php
// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();

include "../lib/admin/akses-admin.php";

if (!aksesAdmin()) {
  header("Location: ../index.php");
}

include "../db/koneksi.php";
include "../components/button.php";

include "../lib/user/tambah-user.php";
include "../lib/parkiran/cari-parkiran.php";
include "../lib/motor/cari-motor.php";
include "../lib/user/cari-user.php";

$TAB_USER = 'user';
$TAB_MOTOR = 'motor';

$tab_aktif = $TAB_MOTOR;

if (isset($_GET['tab'])) {
  if ($_GET['tab'] === $TAB_USER || $_GET['tab'] === $TAB_MOTOR) {
    $tab_aktif = $_GET['tab'];
  }
}

$semua_username = ambilSemuaUsername($conn);
$parkiran_kosong = cariParkiranKosong($conn);

if ($tab_aktif === $TAB_MOTOR) {
  $semua_motor = ambilSemuaMotor($conn);
} else {
  $semua_user = ambilSemuaDataUser($conn);
}

?>

<!DOCTYPE html>
<html lang="en">

<head>
  <?php include "../components/head-tags.php"; ?>
  <script defer>
    const users = JSON.parse('<?= json_encode(isset($semua_user) ? $semua_user : []) ?>');
    const motor = JSON.parse('<?= json_encode(isset($semua_motor) ? $semua_motor : []) ?>');
  </script>
  <script src="../public/js/page-js/admin/admin-index.js" defer type="module"></script>
  <title>Halaman Utama Admin</title>
</head>

<body class="bg-gray-50 h-[200vh]">
  <div id="content" class="transition-transform duration-200">
    <header class="sticky top-0 z-10 py-2 bg-gray-50/50 backdrop-blur-lg">
      <div class="flex flex-wrap items-center justify-between max-w-screen-xl gap-2 px-6 mx-auto md:gap-0">
        <!-- hamburger menu -->
        <div class="basis-1/3">
          <button id="hamburger-menu-btn" type="button" class="px-3 py-2 text-2xl transition-colors duration-200 rounded-lg hover:bg-gray-200 active:bg-gray-300">
            <i class="fa-solid fa-bars"></i>
          </button>
        </div>

        <!-- tab halaman admin -->
        <nav class="flex justify-end gap-4 text-lg md:justify-center basis-1/3">
          <a href="?tab=user" class="<?= $tab_aktif === $TAB_USER ? "text-blue-500" : "" ?>">User</a>
          <a href="?tab=motor" class="<?= $tab_aktif === $TAB_MOTOR ? "text-blue-500" : "" ?>">Motor</a>
        </nav>

        <!-- tambah motor -->
        <div class="flex justify-end md:basis-1/3 basis-full [&>button]:w-full md:[&>button]:w-fit">
          <?= Button("Tambah Motor", "blue", "primary", "button", "tambah-motor-btn")  ?>
        </div>
      </div>
    </header>

    <main class="max-w-screen-xl px-6 mx-auto mt-4">
      <h1 class="mb-6 text-4xl font-bold capitalize">Tabel <?= $tab_aktif === $TAB_MOTOR ? $TAB_MOTOR : $TAB_USER ?></h1>

      <!-- table list user atau motor -->
      <input type="search" class="w-full px-4 py-1 transition-colors duration-200 bg-gray-200 rounded-lg shadow shadow-gray-200 focus:bg-gray-100">

      <div class="mt-2 rounded-lg shadow shadow-gray-300 overflow-clip">
        <!-- tabel semi-responsive -->
        <div class="w-full overflow-auto">
          <table class="w-full table-auto overflow-clip">
            <thead>
              <tr class="[&>th]:p-2 bg-gray-200 text-gray-700">
                <th>No</th>
                <?php if ($tab_aktif === $TAB_MOTOR) : ?>
                  <th>Plat</th>
                  <th>Pemilik</th>
                  <th>Tanggal Masuk</th>
                  <th>Action</th>
                <?php else : ?>
                  <th>Username</th>
                  <th>Jumlah Motor</th>
                  <th>Action</th>
                <?php endif ?>
              </tr>
            </thead>

            <?php if ($tab_aktif === $TAB_MOTOR) : ?>
              <tbody>
                <!-- isi list motor -->
                <?php for ($i = 0; $i < count($semua_motor); $i++) : ?>
                  <tr class="[&>td]:p-2 text-center even:bg-gray-100">
                    <td><?= $i + 1 ?></td>
                    <td><?= $semua_motor[$i]['plat']; ?></td>
                    <td><?= $semua_motor[$i]['lokasi_parkir']; ?></td>
                    <td><?= $semua_motor[$i]['tanggal_masuk']; ?></td>

                    <td>
                      <form action="../lib/action/hapus-motor.action.php" id="hapus-motor-form" method="POST">
                        <input type="hidden" name="plat-motor" value="<?= $semua_motor[$i]['plat']; ?>" />
                        <input type="hidden" name="token-parkiran" value="<?= $semua_motor[$i]['lokasi_parkir']; ?>" />

                        <div class="flex items-center justify-center gap-2">
                          <button id="info-motor-btn" type="button" class="px-3 py-2 text-2xl text-blue-500 transition-colors duration-200 rounded-lg hover:bg-gray-200 active:bg-gray-300">
                            <i class="drop-shadow fa-solid fa-circle-info"></i>
                          </button>
                          <button id="hapus-motor-btn" class="px-3 py-2 text-2xl text-red-500 transition-colors duration-200 rounded-lg hover:bg-gray-200 active:bg-gray-300">
                            <i class="drop-shadow fa-regular fa-trash-can"></i>
                          </button>
                        </div>
                      </form>
                    </td>

                  </tr>
                <?php endfor ?>
              <?php else : ?>
                <!-- isi list user-->
                <?php for ($i = 0; $i < count($semua_user); $i++) : ?>
                  <tr class="[&>td]:p-2 text-center even:bg-gray-100">
                    <td><?= $i + 1 ?></td>
                    <td><?= $semua_user[$i]['username']; ?></td>
                    <td><?= $semua_user[$i]['jumlah_motor']; ?></td>
                    <td>
                      <form action="../lib/action/hapus-user.action.php" id="hapus-user-form" method="POST">
                        <input type="hidden" name="id-user" value="<?= $semua_user[$i]['id']; ?>" />

                        <!-- tombol user -->
                        <div class="flex items-center justify-center gap-2">
                          <button id="edit-user-btn" type="button" data-id-user="<?= $semua_user[$i]['id']; ?>" class="px-3 py-2 text-2xl text-blue-500 transition-colors duration-200 rounded-lg hover:bg-gray-200 active:bg-gray-300">
                            <i class="drop-shadow fa-regular fa-pen-to-square"></i>
                          </button>

                          <button id="hapus-user-btn" class="px-3 py-2 text-2xl text-red-500 transition-colors duration-200 rounded-lg hover:bg-gray-200 active:bg-gray-300">
                            <i class="drop-shadow fa-regular fa-trash-can"></i>
                          </button>
                        </div>
                      </form>
                    </td>
                  </tr>
                <?php endfor ?>
              <?php endif ?>
              </tbody>
          </table>

        </div>

        <!-- kontrol dari tabel -->
        <div class="flex items-center justify-center w-full gap-2 px-4 py-0.5 bg-gray-200">
          <button id="halaman-sebelumnya-btn" type="button" class="px-3 py-2 text-xl text-blue-500 transition-colors duration-200 rounded-lg disabled:text-gray-400 disabled:hover:bg-transparent disabled:active:bg-transparent hover:bg-gray-300 active:bg-gray-400">
            <i class="fa-solid fa-left-long"></i>
          </button>

          <span>1/2</span>

          <button id="halaman-berikutnya-btn" type="button" class="px-3 py-2 text-xl text-blue-500 transition-colors duration-200 rounded-lg disabled:text-gray-400 disabled:hover:bg-transparent disabled:active:bg-transparent hover:bg-gray-300 active:bg-gray-400">
            <i class="fa-solid fa-right-long"></i>
          </button>
        </div>
      </div>

    </main>
    <footer></footer>
  </div>

  <dialog id="action-dialog">
    <div>
      <span id="dialog-title">Tambah Motor</span>
      <button id="close-action-dialog-btn">
        <i class="drop-shadow fa fa-window-close" aria-hidden='true'></i>
      </button>
    </div>

    <?php include "../components/admin/form-tambah-motor.php" ?>
    <?php include "../components/admin/form-edit-user.php" ?>
  </dialog>
</body>

</html>: