<?php
// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();

include "../db/koneksi.php";
include "../lib/hak-akses.php";
include "../lib/user/cari-user.php";
include "../config.php";

if (!aksesAdmin($conn)) {
  header("Location: ../login.php");
}

include "../lib/user/tambah-user.php";
include "../lib/parkiran/cari-parkiran.php";
include "../lib/motor/cari-motor.php";

$tab_aktif = TAB_MOTOR;
$halaman_aktif = isset($_GET['halaman']) ? $_GET['halaman'] : 1;
$keyword = isset($_GET['keyword']) ? $_GET['keyword'] : "";

if (isset($_GET['tab'])) {
  if (
    $_GET['tab'] === TAB_USER ||
    $_GET['tab'] === TAB_ADMIN ||
    $_GET['tab'] === TAB_MOTOR
  ) {
    $tab_aktif = $_GET['tab'];
  }
}

$semua_username = ambilSemuaUsername($conn);
$parkiran_kosong = cariParkiranKosong($conn);

if ($tab_aktif === TAB_MOTOR) {
  [
    'motor_arr' => $motor_arr,
    "total_halaman" => $total_halaman,
    "halaman_sebelumnya" => $halaman_sebelumnya,
    "halaman_berikutnya" => $halaman_berikutnya
  ] = cariMotor($conn, $keyword, $halaman_aktif, JUMLAH_PER_HALAMAN);
} else {
  [
    "user_arr" => $user_arr,
    "total_halaman" => $total_halaman,
    "halaman_sebelumnya" => $halaman_sebelumnya,
    "halaman_berikutnya" => $halaman_berikutnya
  ] = cariUser($conn, $keyword, $halaman_aktif, JUMLAH_PER_HALAMAN, $tab_aktif === TAB_ADMIN);

  $data_motor_milik_user = new stdClass();

  foreach ($user_arr as $user) {
    // angka data per halaman disetting besar agar seluruh 
    // data terambil dengan sekali query
    $data_motor_milik_user->{$user["id"]} =
      cariMotorDariUserId($conn, $user["id"], "", 1, 999999)["motor_arr"];
  }
}

?>

<!DOCTYPE html>
<html lang="en" class="overflow-x-hidden scroll-smooth">

<head>
  <?php include "../components/head-tags.php"; ?>
  <script defer>
    window.dataMotorMilikUser = JSON.parse('<?= json_encode(isset($data_motor_milik_user) ? $data_motor_milik_user : []) ?>')
    window.users = JSON.parse('<?= json_encode(isset($user_arr) ? $user_arr : []) ?>');
    window.tabAktif = "<?= $tab_aktif ?>";
    window.keyword = '<?= $keyword ?>';
    window.tabelMaksHalaman = <?= $total_halaman ?>;
  </script>
  <script src="../public/js/page-js/admin/index/admin-index.js" defer type="module"></script>
  <title>Halaman Utama Admin</title>
</head>

<body class="bg-slate-100 dark:bg-slate-950">
  <?php include "../components/admin/admin-sidebar.php"; ?>

  <div id="content" style="width: calc(100% - 20rem);" class="translate-x-80">
    <header class="sticky top-0 z-[10000] py-2 bg-slate-50/50 dark:bg-slate-950/50 backdrop-blur-lg shadow shadow-slate-300 dark:shadow-slate-800">
      <div class="flex flex-wrap items-center justify-between gap-2 px-6 mx-auto md:gap-0">
        <!-- hamburger menu -->
        <div class="basis-1/3">
          <button id="hamburger-menu-btn" type="button" class="w-10 h-10 text-2xl transition-colors duration-200 rounded-xl hover:bg-slate-200 active:bg-slate-300 dark:hover:bg-slate-700 dark:active:bg-slate-800">
            <i class="text-slate-800 dark:text-slate-300 fa-solid fa-bars"></i>
          </button>
        </div>

        <!-- tambah motor -->
        <div class="flex justify-end md:basis-1/3 basis-full [&>button]:w-full md:[&>button]:w-fit">
          <button id="tambah-motor-btn" type="button" class="px-5 py-1 text-white transition-opacity duration-200 rounded-md shadow w-fit bg-gradient-to-b disabled:opacity-50 from-blue-400 to-blue-500 shadow-blue-300 hover:opacity-70 active:opacity-95 active:shadow-none">
            Tambah Motor
          </button>
        </div>
      </div>
    </header>

    <main class="px-6 mx-auto mt-8">
      <h1 class="mb-6 text-4xl font-medium text-center capitalize dark:text-slate-100">Tabel <?= $tab_aktif ?></h1>

      <!-- search bar -->
      <form method="GET" class="relative flex items-center mb-3 border shadow rounded-xl shadow-slate-200 border-slate-300 dark:border-slate-700 dark:shadow-slate-700">
        <input type="hidden" value="<?= $halaman_aktif ?>" name="halaman">
        <input type="hidden" value="<?= $tab_aktif ?>" name="tab">

        <input type="search" name="keyword" id="search-data-tabel" placeholder="Cari" value="<?= $keyword ?>" class="w-full px-4 py-2 transition-colors bg-transparent border-l-0 rounded-md rounded-l-none outline-none placeholder:text-transparent peer disabled:cursor-not-allowed disabled:opacity-20 dark:text-slate-200">

        <label class="absolute px-1 text-sm text-blue-500 dark:text-blue-400 transition-all scale-90 -translate-x-2 -translate-y-[30px] left-4 top-1/2 peer-placeholder-shown:text-slate-500 bg-slate-100 dark:bg-slate-950 peer-focus:-translate-x-2 peer-focus:-translate-y-[30px] peer-focus:scale-90 peer-placeholder-shown:-translate-y-1/2 peer-placeholder-shown:translate-x-0 peer-placeholder-shown:scale-100" for="search-data-tabel">
          Cari <?= $tab_aktif ?>
        </label>

        <button class="w-10 h-10 text-xl text-blue-500 transition-colors duration-200 rounded-r-lg dark:text-blue-400 hover:bg-slate-200 active:bg-slate-300 dark:hover:bg-slate-600 dark:active:bg-slate-700">
          <i class="fa-solid fa-search"></i>
        </button>
      </form>

      <!-- table list user atau motor -->
      <section class="mt-2 shadow rounded-xl shadow-slate-200 dark:shadow-slate-700 overflow-clip">
        <!-- tabel semi-responsive -->
        <div class="w-full overflow-auto">
          <table id="tabel-user-motor" class="w-full table-auto overflow-clip">
            <thead>
              <tr class="[&>th]:p-2 bg-slate-200 dark:bg-slate-800 dark:text-slate-300 text-slate-700">
                <th>No</th>
                <?php if ($tab_aktif === TAB_MOTOR) : ?>
                  <th>Plat</th>
                  <th>Pemilik</th>
                  <th>Lokasi Parkir</th>
                  <th>Tanggal Masuk</th>
                  <th>Action</th>
                <?php else : ?>
                  <th>Username</th>
                  <th>Jumlah Motor</th>
                  <th>Action</th>
                <?php endif ?>
              </tr>
            </thead>

            <?php if ($tab_aktif === TAB_MOTOR) : ?>
              <tbody>
                <!-- isi list motor -->
                <?php if (count($motor_arr) > 0) : ?>
                  <?php for ($i = 0; $i < count($motor_arr); $i++) : ?>
                    <tr class="[&>td]:p-2 text-center dark:text-slate-400 even:bg-slate-50 dark:even:bg-slate-900">
                      <td><?= $i + (($halaman_aktif - 1) * JUMLAH_PER_HALAMAN) + 1 ?></td>
                      <td><?= $motor_arr[$i]['plat']; ?></td>
                      <td>
                        <?php
                        $username = '';

                        foreach ($semua_username as $user) {
                          if ($user['id'] == $motor_arr[$i]['id_user_pemilik']) {
                            $username = $user['username'];
                            break;
                          }
                        }

                        echo $username;
                        ?>
                      </td>
                      <td><?= $motor_arr[$i]['lokasi_parkir']; ?></td>
                      <td><?= $motor_arr[$i]['tanggal_masuk']; ?></td>

                      <td>
                        <form action="../lib/action/hapus-motor.action.php" id="hapus-motor-form" method="POST">
                          <input type="hidden" name="plat-motor" value="<?= $motor_arr[$i]['plat']; ?>" />
                          <input type="hidden" name="token-parkiran" value="<?= $motor_arr[$i]['lokasi_parkir']; ?>" />

                          <button id="hapus-motor-btn" class="w-10 h-10 text-2xl text-red-500 transition-colors duration-200 rounded-xl hover:bg-red-200 active:bg-red-300">
                            <i class="drop-shadow fa-regular fa-trash-can"></i>
                          </button>
                        </form>
                      </td>
                    </tr>
                  <?php endfor ?>
                <?php else : ?>
                  <tr>
                    <td colspan="10" class="p-2 font-medium text-center text-slate-400 dark:text-slate-600">
                      Tabel Masih Kosong
                    </td>
                  </tr>
                <?php endif ?>
              <?php else : ?>
                <!-- isi list user-->
                <?php if (count($user_arr) > 0) : ?>
                  <?php for ($i = 0; $i < count($user_arr); $i++) : ?>
                    <tr class="[&>td]:p-2 text-center dark:text-slate-400 even:bg-slate-50 dark:even:bg-slate-900">
                      <td><?= $i + (($halaman_aktif - 1) * JUMLAH_PER_HALAMAN) + 1 ?></td>
                      <td><?= $user_arr[$i]['username']; ?></td>
                      <td><?= $user_arr[$i]['jumlah_motor']; ?></td>
                      <td>
                        <form action="../lib/action/hapus-user.action.php" id="hapus-user-form" method="POST">
                          <input type="hidden" name="id-user" value="<?= $user_arr[$i]['id']; ?>" />

                          <!-- tombol user -->
                          <div class="flex items-center justify-center gap-2">
                            <?php if ($user_arr[$i]['id'] !== $_SESSION['id']) : ?>
                              <button id="edit-user-btn" type="button" data-id-user="<?= $user_arr[$i]['id']; ?>" class="w-10 h-10 text-2xl text-blue-500 transition-colors duration-200 dark:text-blue-400 rounded-xl hover:bg-blue-200 active:bg-blue-300">
                                <i class="drop-shadow fa-regular fa-pen-to-square"></i>
                              </button>

                              <button id="hapus-user-btn" class="w-10 h-10 text-2xl text-red-500 transition-colors duration-200 rounded-xl hover:bg-red-200 active:bg-red-300">
                                <i class="drop-shadow fa-regular fa-trash-can"></i>
                              </button>
                            <?php else : ?>
                              <span class="font-medium text-slate-400 dark:text-slate-600">Anda</span>
                            <?php endif ?>
                          </div>
                        </form>
                      </td>
                    </tr>
                  <?php endfor ?>
                <?php else : ?>
                  <tr>
                    <td colspan="10" class="p-2 font-medium text-center text-slate-500">
                      Tabel Masih Kosong
                    </td>
                  </tr>
                <?php endif ?>
              <?php endif ?>
              </tbody>
          </table>
        </div>

        <!-- kontrol dari tabel -->
        <div class="flex items-center justify-center w-full gap-2 px-4 py-0.5 bg-slate-200 dark:bg-slate-800 dark:text-slate-300 text-slate-700">
          <?php
          $link_hal_sebelum = $halaman_sebelumnya  !== null ? "?tab=$tab_aktif&halaman=$halaman_sebelumnya" : "#";
          ?>
          <a href='<?= $link_hal_sebelum ?>' id="halaman-sebelumnya-btn" class="grid w-10 h-10 text-xl text-blue-500 transition-colors duration-200 dark:text-blue-400 rounded-xl place-content-center disabled:text-slate-400 dark:disabled:text-slate-600 disabled:hover:bg-transparent disabled:active:bg-transparent hover:bg-blue-200 active:bg-blue-300">
            <i class="fa-solid fa-left-long"></i>
          </a>

          <span id="indikator-halaman">
            <input class="w-auto pl-2 shadow rounded-xl dark:bg-slate-700 dark:text-slate-300" type="number" min="1" max="<?= $total_halaman ?>" id="input-halaman" value="<?= $halaman_aktif ?>">
            / <?= $total_halaman ?>
          </span>

          <?php
          $link_hal_berikut = $halaman_berikutnya  !== null ? "?tab=$tab_aktif&halaman=$halaman_berikutnya" : "#";
          ?>

          <a href="<?= $link_hal_berikut ?>" id="halaman-berikutnya-btn" class="grid w-10 h-10 text-xl text-blue-500 transition-colors duration-200 dark:text-blue-400 rounded-xl place-content-center disabled:text-slate-400 dark:disabled:text-slate-600 disabled:hover:bg-transparent disabled:active:bg-transparent hover:bg-blue-200 active:bg-blue-300">
            <i class="fa-solid fa-right-long"></i>
          </a>
        </div>
      </section>
    </main>
  </div>

  <dialog id="action-dialog">
    <div class="flex items-center justify-between">
      <span id="dialog-title" class="text-2xl font-medium dark:text-slate-100">Tambah Motor</span>
      <button id="close-action-dialog-btn" class="w-10 h-10 text-2xl text-red-500 transition-colors duration-200 rounded-xl hover:bg-red-200 active:bg-red-300">
        <i class="fa-solid fa-xmark"></i>
      </button>
    </div>

    <?php include "../components/konten-dialog/form-tambah-motor.php" ?>

    <?php if ($tab_aktif !== TAB_MOTOR) include "../components/konten-dialog/form-edit-user.php" ?>
  </dialog>

</body>

</html>