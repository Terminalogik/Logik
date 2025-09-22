<!DOCTYPE html>
<html>
<head>
  <meta charset="UTF-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= $title ?></title>
  <!-- css  -->
  <script src="https://cdn.tailwindcss.com"></script>
  <link rel="stylesheet" href="<?= stack('css/mobilemenu.css') ?>">
</head>
<body class="bg-slate-400">
  <!-- Add navigation -->
  <?php require __DIR__ . '/../shards/nav.php'; ?>
  <!-- content goes there for each page.  -->
   <!-- TODO build the navigation to switch between the different views. -->
  <?= $content ?>
  <script src="<?= stack('js/ticketathonmobileicon.js') ?>"></script>
</body>
</html>
