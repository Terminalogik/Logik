<!DOCTYPE html>
<html>
<head>
  <meta charset="UTF-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=ZCOOL+QingKe+HuangYou&display=swap" rel="stylesheet">

  <title><?= $title ?></title>
  <!-- css  -->
 <link rel="stylesheet" href="<?= stack('/css/logik.css') ?>">
</head>
<body>
  <?= $content ?>
   <script src="<?= stack('/js/logikwelcome.js') ?>" defer></script>
</body>
</html>