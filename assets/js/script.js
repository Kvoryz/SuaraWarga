function initDarkMode() {
  const savedTheme = localStorage.getItem("theme");
  const toggle = document.getElementById("darkModeToggle");
  const knob = document.getElementById("darkModeKnob");

  console.log("initDarkMode called, savedTheme:", savedTheme);

  if (savedTheme === "dark") {
    document.documentElement.setAttribute("data-theme", "dark");
    if (toggle) toggle.style.backgroundColor = "#3b82f6";
    if (knob) knob.style.transform = "translateX(20px)";
  }
}

function toggleDarkMode() {
  const html = document.documentElement;
  const toggle = document.getElementById("darkModeToggle");
  const knob = document.getElementById("darkModeKnob");
  const isDark = html.getAttribute("data-theme") === "dark";

  console.log("toggleDarkMode called, isDark:", isDark);

  if (isDark) {
    html.removeAttribute("data-theme");
    localStorage.setItem("theme", "light");
    if (toggle) toggle.style.backgroundColor = "#d1d5db";
    if (knob) knob.style.transform = "translateX(0)";
  } else {
    html.setAttribute("data-theme", "dark");
    localStorage.setItem("theme", "dark");
    if (toggle) toggle.style.backgroundColor = "#3b82f6";
    if (knob) knob.style.transform = "translateX(20px)";
  }

  console.log("data-theme is now:", html.getAttribute("data-theme"));
}

document.addEventListener("DOMContentLoaded", function () {
  initDarkMode();
});

function openOffcanvas() {
  const offcanvas = document.getElementById("offcanvasMenu");
  const overlay = document.getElementById("offcanvasOverlay");
  if (offcanvas && overlay) {
    offcanvas.classList.add("active");
    overlay.classList.add("active");
    document.body.classList.add("offcanvas-open");
  }
}

function closeOffcanvas() {
  const offcanvas = document.getElementById("offcanvasMenu");
  const overlay = document.getElementById("offcanvasOverlay");
  if (offcanvas && overlay) {
    offcanvas.classList.remove("active");
    overlay.classList.remove("active");
    document.body.classList.remove("offcanvas-open");
  }
}

function openEditModal() {
  const modal = document.getElementById("editModal");
  if (modal) {
    modal.classList.add("active");
    document.body.classList.add("modal-open");
  }
}

function closeEditModal() {
  const modal = document.getElementById("editModal");
  if (modal) {
    modal.classList.remove("active");
    document.body.classList.remove("modal-open");
  }
}

function openAddModal() {
  const modal = document.getElementById("addModal");
  if (modal) {
    modal.classList.add("active");
    document.body.classList.add("modal-open");
  }
}

function closeAddModal() {
  const modal = document.getElementById("addModal");
  if (modal) {
    modal.classList.remove("active");
    document.body.classList.remove("modal-open");
  }
}

function openEditUserModal(id, nama, email, username, telp, level) {
  const modal = document.getElementById("editModal");
  if (modal) {
    document.getElementById("edit_id").value = id;
    document.getElementById("edit_nama").value = nama;
    document.getElementById("edit_email").value = email;
    document.getElementById("edit_username").value = username;
    document.getElementById("edit_telp").value = telp || "";
    document.getElementById("edit_level").value = level;

    modal.classList.add("active");
    document.body.classList.add("modal-open");
  }
}

function closeEditUserModal() {
  const modal = document.getElementById("editModal");
  if (modal) {
    modal.classList.remove("active");
    document.body.classList.remove("modal-open");
  }
}

function openDeleteModal(id) {
  const modal = document.getElementById("deleteModal");
  if (modal) {
    const pengaduanIdInput = document.getElementById("deletePengaduanId");
    const userIdInput = document.getElementById("delete_id");

    if (pengaduanIdInput) pengaduanIdInput.value = id;
    if (userIdInput) userIdInput.value = id;

    modal.classList.add("active");
    document.body.classList.add("modal-open");
  }
}

function hideDeleteModal() {
  const modal = document.getElementById("deleteModal");
  if (modal) {
    modal.classList.remove("active");
    modal.classList.add("hidden");
    document.body.classList.remove("modal-open");
  }
}

function confirmDeletePengaduan(id, pelapor, tanggal) {
  const modal = document.getElementById("confirmationModal");
  if (!modal) return;

  document.getElementById("delete_id").textContent = "#" + id;
  document.getElementById("delete_pelapor").textContent = pelapor;
  document.getElementById("delete_tanggal").textContent = tanggal;

  const params = new URLSearchParams(window.location.search);
  let deleteUrl = `laporan.php?hapus=${id}`;

  if (params.get("status")) {
    deleteUrl += `&status=${params.get("status")}`;
  }
  if (params.get("date")) {
    deleteUrl += `&date=${params.get("date")}`;
  }
  if (params.get("search")) {
    deleteUrl += `&search=${encodeURIComponent(params.get("search"))}`;
  }

  const confirmDeleteBtn = document.getElementById("confirmDeleteBtn");
  if (confirmDeleteBtn) {
    confirmDeleteBtn.href = deleteUrl;
  }

  modal.classList.add("active");
  document.body.classList.add("modal-open");
}

function closeDeleteModal() {
  const modal = document.getElementById("deleteModal");
  if (modal) {
    modal.classList.remove("active");
    document.body.classList.remove("modal-open");
  }
}

function openDetailModal(
  id,
  tanggal,
  pelapor,
  email,
  laporan,
  status,
  foto,
  lokasi,
  tanggapanData,
  instansiData
) {
  const modal = document.getElementById("detailModal");
  if (!modal) return;

  const idHeader = document.getElementById("detail_id_header");
  if (idHeader) idHeader.textContent = "#" + id;

  document.getElementById("detail_tanggal").textContent = tanggal;
  document.getElementById("detail_pelapor").textContent = pelapor;
  document.getElementById("detail_email").textContent = email;
  document.getElementById("detail_laporan").textContent = laporan;

  let statusText = "";
  let statusClass = "";
  switch (status) {
    case "0":
      statusText = "Belum Diproses";
      statusClass = "bg-yellow-100 text-yellow-800";
      break;
    case "proses":
      statusText = "Dalam Proses";
      statusClass = "bg-orange-100 text-orange-800";
      break;
    case "selesai":
      statusText = "Selesai";
      statusClass = "bg-green-100 text-green-800";
      break;
  }
  document.getElementById("detail_status_badge").textContent = statusText;
  document.getElementById("detail_status_badge").className =
    "status-badge px-2 py-1 text-xs rounded-full " + statusClass;

  const lokasiContainer = document.getElementById("detail_lokasi_container");
  const lokasiText = document.getElementById("detail_lokasi_text");
  if (lokasi && lokasi.trim() !== "") {
    if (lokasiText) lokasiText.textContent = lokasi;
    if (lokasiContainer) lokasiContainer.style.display = "block";
  } else {
    if (lokasiContainer) lokasiContainer.style.display = "none";
  }

  const fotoContainer = document.getElementById("detail_foto_container");
  const fotoElement = document.getElementById("detail_foto");
  if (foto && foto.trim() !== "") {
    fotoElement.src = "uploads/" + foto;
    if (fotoContainer) fotoContainer.style.display = "block";
  } else {
    if (fotoContainer) fotoContainer.style.display = "none";
  }

  const instansiContainer = document.getElementById(
    "detail_instansi_container"
  );
  const instansiList = document.getElementById("detail_instansi_list");

  if (instansiList) instansiList.innerHTML = "";

  if (instansiData && instansiData.length > 0) {
    if (instansiContainer) instansiContainer.style.display = "block";

    instansiData.forEach((inst) => {
      const badge = document.createElement("span");
      badge.className =
        "inline-flex items-center px-3 py-1.5 bg-blue-50 text-blue-700 rounded-lg text-sm font-medium border border-blue-200";
      badge.innerHTML = `<span class="mr-1">${inst.ikon || ""}</span>${
        inst.nama
      }`;
      instansiList.appendChild(badge);
    });
  } else {
    if (instansiContainer) instansiContainer.style.display = "none";
  }

  const tanggapanList = document.getElementById("detail_tanggapan_list");
  const noTanggapan = document.getElementById("detail_no_tanggapan");

  if (tanggapanList) tanggapanList.innerHTML = "";

  if (tanggapanData && tanggapanData.length > 0) {
    if (noTanggapan) noTanggapan.style.display = "none";

    tanggapanData.forEach((item) => {
      const levelClass =
        item.level === "admin"
          ? "bg-purple-100 text-purple-800"
          : "bg-blue-100 text-blue-800";

      const tanggapanItem = document.createElement("div");
      tanggapanItem.className =
        "p-4 bg-gray-50 rounded-lg border-l-4 border-blue-500";
      tanggapanItem.innerHTML = `
        <div class="flex items-center justify-between mb-2">
          <div class="flex items-center">
            <div class="bg-gray-200 p-1.5 rounded-full mr-2">
              <svg class="w-3 h-3 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
              </svg>
            </div>
            <span class="text-sm font-medium text-gray-900">${
              item.petugas
            }</span>
            <span class="ml-2 px-2 py-0.5 text-xs rounded-full ${levelClass}">${
        item.level.charAt(0).toUpperCase() + item.level.slice(1)
      }</span>
          </div>
          <span class="text-xs text-gray-500">${item.tanggal}</span>
        </div>
        <p class="text-sm text-gray-700">${item.isi}</p>
      `;
      tanggapanList.appendChild(tanggapanItem);
    });
  } else {
    if (noTanggapan) noTanggapan.style.display = "block";
  }

  modal.classList.add("active");
  document.body.classList.add("modal-open");
}

function formatTanggal(dateStr) {
  const date = new Date(dateStr);
  const options = {
    day: "numeric",
    month: "short",
    year: "numeric",
    hour: "2-digit",
    minute: "2-digit",
  };
  return date.toLocaleDateString("id-ID", options);
}

function closeDetailModal() {
  const modal = document.getElementById("detailModal");
  if (modal) {
    modal.classList.remove("active");
    document.body.classList.remove("modal-open");
  }
}

function openTanggapanModal(id_pengaduan, currentStatus) {
  const modal = document.getElementById("tanggapanModal");
  if (modal) {
    document.getElementById("tanggapan_id_pengaduan").value = id_pengaduan;
    document.getElementById("tanggapan_status").value = currentStatus;
    document.getElementById("tanggapan_text").value = "";
    modal.classList.add("active");
    document.body.classList.add("modal-open");
  }
}

function closeTanggapanModal() {
  const modal = document.getElementById("tanggapanModal");
  if (modal) {
    modal.classList.remove("active");
    document.body.classList.remove("modal-open");
  }
}

function openDeleteModalLaporan(id, pelapor, tanggal) {
  const modal = document.getElementById("confirmationModal");
  if (!modal) return;

  document.getElementById("delete_id").textContent = "#" + id;
  document.getElementById("delete_pelapor").textContent = pelapor;
  document.getElementById("delete_tanggal").textContent = tanggal;

  const params = new URLSearchParams(window.location.search);
  let deleteUrl = `laporan.php?hapus=${id}`;

  if (params.get("status")) {
    deleteUrl += `&status=${params.get("status")}`;
  }
  if (params.get("date")) {
    deleteUrl += `&date=${params.get("date")}`;
  }
  if (params.get("search")) {
    deleteUrl += `&search=${encodeURIComponent(params.get("search"))}`;
  }

  const confirmDeleteBtn = document.getElementById("confirmDeleteBtn");
  if (confirmDeleteBtn) {
    confirmDeleteBtn.href = deleteUrl;
  }

  modal.classList.add("active");
  document.body.classList.add("modal-open");
}

function closeConfirmationModal() {
  const modal = document.getElementById("confirmationModal");
  if (modal) {
    modal.classList.remove("active");
    document.body.classList.remove("modal-open");
  }
}

function setupFileUpload() {
  const dropZone = document.getElementById("dropZone");
  const fileInput = document.getElementById("foto");
  const previewContainer = document.getElementById("previewContainer");
  const previewImage = document.getElementById("previewImage");
  const uploadIcon = document.getElementById("uploadIcon");
  const uploadText = document.getElementById("uploadText");

  if (!dropZone || !fileInput) return;

  dropZone.addEventListener("click", function () {
    fileInput.click();
  });

  fileInput.addEventListener("change", function (e) {
    handleFileSelect(e, previewContainer, previewImage, uploadIcon, uploadText);
  });

  dropZone.addEventListener("dragover", function (e) {
    e.preventDefault();
    dropZone.classList.add("drag-over");
  });

  dropZone.addEventListener("dragleave", function (e) {
    e.preventDefault();
    dropZone.classList.remove("drag-over");
  });

  dropZone.addEventListener("drop", function (e) {
    e.preventDefault();
    dropZone.classList.remove("drag-over");

    const files = e.dataTransfer.files;
    if (files.length > 0) {
      fileInput.files = files;
      handleFileSelect(
        { target: { files: files } },
        previewContainer,
        previewImage,
        uploadIcon,
        uploadText
      );
    }
  });
}

function handleFileSelect(
  event,
  previewContainer,
  previewImage,
  uploadIcon,
  uploadText
) {
  const file = event.target.files[0];

  if (!file) return;

  const validTypes = ["image/jpeg", "image/jpg", "image/png", "image/gif"];
  if (!validTypes.includes(file.type)) {
    alert("Hanya file gambar (JPEG, PNG, GIF) yang diizinkan.");
    return;
  }

  if (file.size > 5 * 1024 * 1024) {
    alert("Ukuran file terlalu besar. Maksimal 5MB.");
    return;
  }

  const reader = new FileReader();
  reader.onload = function (e) {
    previewImage.src = e.target.result;
    previewContainer.classList.remove("hidden");
    if (uploadIcon) uploadIcon.classList.add("hidden");
    if (uploadText) {
      uploadText.innerHTML =
        '<span class="text-green-600 font-medium">File berhasil dipilih</span><p class="text-xs mt-1">' +
        file.name +
        "</p>";
    }
  };
  reader.readAsDataURL(file);
}

function removeImage() {
  const fileInput = document.getElementById("foto");
  const previewContainer = document.getElementById("previewContainer");
  const uploadIcon = document.getElementById("uploadIcon");
  const uploadText = document.getElementById("uploadText");

  if (fileInput) fileInput.value = "";
  if (previewContainer) previewContainer.classList.add("hidden");
  if (uploadIcon) uploadIcon.classList.remove("hidden");
  if (uploadText) {
    uploadText.innerHTML =
      '<span>Klik untuk upload file</span><p class="text-xs mt-1">PNG, JPG, GIF hingga 5MB</p>';
  }
}

function openImageModal(filename) {
  const modal = document.getElementById("imageModal");
  if (modal) {
    document.getElementById("modalImage").src = "uploads/" + filename;
    modal.classList.remove("hidden");
    document.body.classList.add("modal-open");
  }
}

function closeImageModal() {
  const modal = document.getElementById("imageModal");
  if (modal) {
    modal.classList.add("hidden");
    document.body.classList.remove("modal-open");
  }
}

function showDeleteModal(pengaduanId) {
  currentPengaduanId = pengaduanId;
  document.getElementById("deletePengaduanId").value = pengaduanId;
  const modal = document.getElementById("deleteModal");
  if (modal) {
    modal.classList.remove("hidden");
    modal.classList.add("active");
    document.body.classList.add("modal-open");
  }
}

function setupModalCloseOnClickOutside() {
  const overlay = document.getElementById("offcanvasOverlay");
  if (overlay) {
    overlay.addEventListener("click", closeOffcanvas);
  }

  const imageModal = document.getElementById("imageModal");
  if (imageModal) {
    imageModal.addEventListener("click", function (e) {
      if (e.target === this) {
        closeImageModal();
      }
    });
  }

  const deleteModal = document.getElementById("deleteModal");
  if (deleteModal) {
    deleteModal.addEventListener("click", function (e) {
      if (e.target === this) {
        hideDeleteModal();
      }
    });
  }

  document.querySelectorAll(".modal").forEach((modal) => {
    if (modal.id !== "imageModal" && modal.id !== "deleteModal") {
      modal.addEventListener("click", function (e) {
        if (e.target === this) {
          if (modal.id === "detailModal") closeDetailModal();
          if (modal.id === "tanggapanModal") closeTanggapanModal();
          if (modal.id === "confirmationModal") closeConfirmationModal();
          if (modal.id === "addModal") closeAddModal();
          if (modal.id === "deleteModal") closeDeleteModal();
          if (modal.id === "imageModal") closeImageModal();

          if (modal.id === "editModal") {
            const hasEditId = document.getElementById("edit_id");
            if (hasEditId) {
              closeEditUserModal();
            } else {
              closeEditModal();
            }
          }
        }
      });
    }
  });
}

document.addEventListener("keydown", function (e) {
  if (e.key === "Escape") {
    closeOffcanvas();
    closeEditModal();
    closeEditUserModal();
    closeAddModal();
    closeDeleteModal();
    closeDetailModal();
    closeTanggapanModal();
    closeConfirmationModal();
    closeImageModal();

    hideDeleteModal();
    const imageModal = document.getElementById("imageModal");
    if (imageModal && !imageModal.classList.contains("hidden")) {
      closeImageModal();
    }
  }
});

function setupDeleteConfirmations() {
  const confirmDeleteBtn = document.getElementById("confirmDeleteBtn");
  if (confirmDeleteBtn) {
    confirmDeleteBtn.addEventListener("click", function (e) {
      if (
        !confirm(
          "Apakah Anda yakin ingin menghapus pengaduan ini? Tindakan ini tidak dapat dibatalkan."
        )
      ) {
        e.preventDefault();
        closeConfirmationModal();
      }
    });
  }
}

document.addEventListener("DOMContentLoaded", function () {
  setupModalCloseOnClickOutside();
  setupDeleteConfirmations();
  setupFileUpload();

  const deleteModal = document.getElementById("deleteModal");
  if (deleteModal) {
    deleteModal.addEventListener("click", function (e) {
      if (e.target === this) {
        hideDeleteModal();
      }
    });
  }

  initTypingEffect();

  if (typeof AOS !== "undefined") {
    AOS.init({ once: true, easing: "ease-out-cubic" });
  }

  initLoginPage();

  const jamSekarang = document.getElementById("jamSekarang");
  if (jamSekarang) {
    setInterval(function () {
      const now = new Date();
      const jam = String(now.getHours()).padStart(2, "0");
      const menit = String(now.getMinutes()).padStart(2, "0");
      const detik = String(now.getSeconds()).padStart(2, "0");
      jamSekarang.textContent = jam + ":" + menit + ":" + detik;
    }, 1000);
  }

  const selectAll = document.getElementById("selectAll");
  const rowCheckboxes = document.querySelectorAll(".row-checkbox");
  const btnBulkDelete = document.getElementById("btnBulkDelete");
  const selectedCount = document.getElementById("selectedCount");
  const selectedNum = document.getElementById("selectedNum");
  const bulkDeleteForm = document.getElementById("bulkDeleteForm");

  function updateBulkDeleteUI() {
    const checkedBoxes = document.querySelectorAll(".row-checkbox:checked");
    const count = checkedBoxes.length;

    if (count > 0) {
      if (btnBulkDelete) {
        btnBulkDelete.classList.remove("opacity-0", "pointer-events-none");
      }
      if (selectedCount) selectedCount.classList.remove("hidden");
      if (selectedNum) selectedNum.textContent = count;
    } else {
      if (btnBulkDelete) {
        btnBulkDelete.classList.add("opacity-0", "pointer-events-none");
      }
      if (selectedCount) selectedCount.classList.add("hidden");
    }

    if (selectAll && rowCheckboxes.length > 0) {
      selectAll.checked = count === rowCheckboxes.length;
      selectAll.indeterminate = count > 0 && count < rowCheckboxes.length;
    }
  }

  if (selectAll) {
    selectAll.addEventListener("change", function () {
      rowCheckboxes.forEach((checkbox) => {
        checkbox.checked = this.checked;
      });
      updateBulkDeleteUI();
    });
  }

  rowCheckboxes.forEach((checkbox) => {
    checkbox.addEventListener("change", updateBulkDeleteUI);
  });

  if (bulkDeleteForm) {
    bulkDeleteForm.addEventListener("submit", function (e) {
      const checkedBoxes = document.querySelectorAll(".row-checkbox:checked");
      if (checkedBoxes.length === 0) {
        e.preventDefault();
        alert("Pilih minimal satu pengaduan untuk dihapus");
        return false;
      }
      if (
        !confirm(
          "Apakah Anda yakin ingin menghapus " +
            checkedBoxes.length +
            " pengaduan terpilih? Tindakan ini tidak dapat dibatalkan."
        )
      ) {
        e.preventDefault();
        return false;
      }
    });
  }
});

function initTypingEffect() {
  const typingEl = document.getElementById("typing");
  if (!typingEl) return;

  const words = ["Pengaduan..", "Aspirasi..", "Solusi..", "Keluhan.."];
  let wordIndex = 0;
  let charIndex = 0;
  let deleting = false;

  function typingEffect() {
    const word = words[wordIndex];

    if (!deleting) {
      typingEl.textContent = word.slice(0, charIndex + 1);
      charIndex++;
      if (charIndex === word.length) {
        setTimeout(() => (deleting = true), 1200);
      }
    } else {
      typingEl.textContent = word.slice(0, charIndex - 1);
      charIndex--;
      if (charIndex === 0) {
        deleting = false;
        wordIndex = (wordIndex + 1) % words.length;
      }
    }

    setTimeout(typingEffect, deleting ? 55 : 90);
  }

  typingEffect();
}

function initLoginPage() {
  const loginForm = document.getElementById("loginForm");
  const forgotForm = document.getElementById("forgotForm");
  const forgotModal = document.getElementById("forgotModal");

  if (!loginForm) return;

  const usernameField = document.getElementById("username");
  if (usernameField && !usernameField.value) {
    usernameField.focus();
  }

  loginForm.addEventListener("keypress", function (e) {
    if (e.key === "Enter" && e.target.tagName !== "TEXTAREA") {
      if (e.target.form === this) {
        return true;
      }
    }
  });

  document.addEventListener("keydown", function (e) {
    if (e.key === "Escape" && forgotModal) {
      closeModal();
    }
  });

  if (forgotModal) {
    forgotModal.addEventListener("click", function (e) {
      if (e.target === this) {
        closeModal();
      }
    });
  }

  if (forgotForm) {
    forgotForm.addEventListener("submit", function (e) {
      const email = document.getElementById("email").value.trim();
      const telp = document.getElementById("telp").value.trim();

      if (!email || !telp) {
        e.preventDefault();
        alert("Harap isi email dan nomor telepon terlebih dahulu");
        return false;
      }

      return true;
    });
  }
}

function togglePasswordVisibility() {
  const passwordInput = document.getElementById("password");
  const eyeIcon = document.getElementById("eye-icon");
  const eyeOffIcon = document.getElementById("eye-off-icon");

  if (!passwordInput) return;

  if (passwordInput.type === "password") {
    passwordInput.type = "text";
    if (eyeIcon) eyeIcon.classList.add("hidden");
    if (eyeOffIcon) eyeOffIcon.classList.remove("hidden");
  } else {
    passwordInput.type = "password";
    if (eyeIcon) eyeIcon.classList.remove("hidden");
    if (eyeOffIcon) eyeOffIcon.classList.add("hidden");
  }
}

function showForgotModal() {
  const modal = document.getElementById("forgotModal");
  if (modal) {
    modal.classList.add("active");
    setTimeout(() => {
      const emailField = document.getElementById("email");
      if (emailField) emailField.focus();
    }, 100);
  }
}

function closeModal() {
  const modal = document.getElementById("forgotModal");
  if (modal) {
    modal.classList.remove("active");
    const usernameField = document.getElementById("username");
    if (usernameField) usernameField.focus();
  }
}
