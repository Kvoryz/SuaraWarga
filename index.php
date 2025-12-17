<?php
session_start();
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <title>SuaraWarga</title>

  <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600&display=swap" rel="stylesheet">
  <script src="https://cdn.tailwindcss.com"></script>
  <link href="https://unpkg.com/aos@2.3.4/dist/aos.css" rel="stylesheet">
  <link rel="stylesheet" href="assets/css/style.css">
</head>

<body class="bg-white text-gray-900">

  <header class="absolute top-6 left-6 flex items-center gap-2">
    <svg xmlns="http://www.w3.org/2000/svg" fill="none"
      viewBox="0 0 24 24" stroke-width="1.5"
      stroke="currentColor" class="w-6 h-6 text-gray-900">
      <path stroke-linecap="round" stroke-linejoin="round"
        d="M7.5 8.25h9m-9 3h5.25M12 20.25c4.97 0 9-3.694 9-8.25s-4.03-8.25-9-8.25-9 3.694-9 8.25c0 2.09.82 3.995 2.18 5.433L3 20.25l3.07-.82A10.13 10.13 0 0 0 12 20.25Z" />
    </svg>
    <span class="font-semibold text-lg tracking-tight">SuaraWarga</span>
  </header>

  <div class="min-h-screen flex flex-col justify-center items-center px-4 text-center">

    <h1
      class="text-3xl md:text-4xl font-semibold tracking-tight"
      data-aos="fade-up"
      data-aos-duration="700"
    >
      Website terpercaya untuk menyampaikan
      <span id="typing" class="cursor ml-1"></span>
    </h1>

    <div class="mt-10 flex gap-4" data-aos="fade-up" data-aos-delay="300">
      <button
        onclick="location.href='login.php'"
        class="px-8 py-2.5 border border-gray-300 rounded-lg text-sm hover:bg-gray-100 transition"
      >
        Masuk
      </button>

      <button
        onclick="location.href='register.php'"
        class="px-8 py-2.5 bg-gray-900 text-white rounded-lg text-sm hover:bg-gray-800 transition"
      >
        Daftar
      </button>
    </div>

  </div>

  <!-- AOS -->
  <script src="https://unpkg.com/aos@2.3.4/dist/aos.js"></script>
  <script src="assets/js/script.js"></script>

</body>
</html>
