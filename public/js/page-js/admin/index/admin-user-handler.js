import {
  actionDialog,
  dialogTitle,
  editUserBtns,
  formEditUser,
  formTambahMotor,
  hapusUserforms,
} from "./admin-index.js";
import { qs } from "../../../utils/dom-selector.js";

export function editUserHandler() {
  editUserBtns?.forEach(btn => {
    btn.addEventListener("click", () => {
      actionDialog?.openDialog();
      dialogTitle.textContent = "Edit User";

      const idUser = parseInt(btn.getAttribute("data-id-user"));

      // TODO: error handling ketika idUser tidak ada
      if (!idUser) {
        console.error("Id user tidak ada");
      }

      const { username, is_admin: isAdmin } = window.users.find(
        u => u.id === idUser,
      );

      // ambil data motor milik user
      const motorArr = window.dataMotorMilikUser[idUser];

      // isi list motor di dalam dialog
      let htmlListMotor = "";
      motorArr.forEach(m => {
        htmlListMotor += `
          <li class="flex gap-5">
            <span>${m.plat}</span>
            <span>${m.lokasi_parkir}</span>
            <span>${new Date(m.tanggal_masuk).toLocaleString()}</span>
          </li>
          `;
      });

      // set isi list motor milik user
      qs("#list-motor-user").innerHTML = htmlListMotor;

      // isi data di dalam form
      qs("#id-user-edit").value = idUser;
      qs("[name='username']").value = username;
      qs("[name='is-admin']").checked = isAdmin === 1;

      formEditUser?.classList.remove("hidden");
      formTambahMotor?.classList.add("hidden");
    });
  });

  // konfirmasi ketika menghapus motor
  hapusUserforms?.forEach(form => {
    form.addEventListener("submit", e => {
      e.preventDefault();

      const konfirmasiHapus = confirm(
        "Apakah anda yakin ingin menghapus user ini ?",
      );

      if (konfirmasiHapus) form.submit();
    });
  });
}
