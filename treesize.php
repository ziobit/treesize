<?php
function getDirectoryDetails($directory) {
  $size = 0;
  $fileCount = 0;

  $files = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($directory));
  foreach ($files as $file) {
    if ($file->isFile()) {
      $size += $file->getSize();
      $fileCount++;
    }
  }

  return ['size' => $size, 'fileCount' => $fileCount];
}

function formatSize($size, $inKB) {
  if ($inKB) {
    return number_format($size / 1024) . ' kB';
  } else {
    return number_format($size / 1024 / 1024) . ' MB';
  }
}

$dir = isset($_GET['dir']) ? $_GET['dir'] : __DIR__;
$directories = glob($dir . '/*', GLOB_ONLYDIR);
$showInKB = isset($_GET['inKB']);
$sort = isset($_GET['sort']) ? $_GET['sort'] : 'size_desc';

$directoriesDetails = [];
foreach ($directories as $directoryPath) {
  $directoriesDetails[] = [
    'path' => $directoryPath,
    'name' => basename($directoryPath),
    'details' => getDirectoryDetails($directoryPath),
  ];
}

usort($directoriesDetails, function ($a, $b) use ($sort) {
  if ($sort == 'size_asc') {
    return $a['details']['size'] <=> $b['details']['size'];
  } elseif ($sort == 'size_desc') {
    return $b['details']['size'] <=> $a['details']['size'];
  } elseif ($sort == 'name_asc') {
    return strcasecmp($a['name'], $b['name']);
  } elseif ($sort == 'name_desc') {
    return strcasecmp($b['name'], $a['name']);
  }
  return 0;
});
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Directory Sizes and File Counts</title>
  <link href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
  <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
  <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
  <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css">
</head>
<body>
<div class="container mt-5">
  <h2>Directory Sizes and File Counts</h2>
  <div class="mb-3">
    <label>
      <input type="checkbox" id="toggleSize" <?= $showInKB ? 'checked' : '' ?>> Show size in kB
    </label>
  </div>
  <table class="table table-bordered">
    <thead>
      <tr>
        <th><a href="?dir=<?= urlencode($dir) ?>&sort=<?= $sort == 'name_asc' ? 'name_desc' : 'name_asc' ?><?= $showInKB ? '&inKB=1' : '' ?>">Directory</a></th>
        <th><a href="?dir=<?= urlencode($dir) ?>&sort=<?= $sort == 'size_asc' ? 'size_desc' : 'size_asc' ?><?= $showInKB ? '&inKB=1' : '' ?>">Details</a></th>
      </tr>
    </thead>
    <tbody>
      <?php if ($dir !== __DIR__): ?>
        <tr>
          <td>
            <a href="tree_size.php?dir=<?= urlencode(dirname($dir)) . ($showInKB ? '&inKB=1' : '') ?>">
              <i class="fa fa-level-up"></i> 
              ..
            </a>
          </td>
          <td>Go up</td>
        </tr>
      <?php endif; ?>

      <?php foreach ($directoriesDetails as $directory): ?>
        <tr>
          <td>
            <a href="tree_size.php?dir=<?= urlencode($directory['path']) . ($showInKB ? '&inKB=1' : '') ?>">
              <i class="fa fa-folder"></i> 
              <?= $directory['name'] ?>
            </a>
          </td>
          <td>
            <?= formatSize($directory['details']['size'], $showInKB) ?>
            /
            <?= number_format($directory['details']['fileCount']) ?> files
          </td>
        </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
</div>
<script>
  document.getElementById('toggleSize').addEventListener('change', function() {
    const inKB = this.checked ? '&inKB=1' : '';
    window.location.href = `tree_size.php?dir=<?= urlencode($dir) ?>${inKB}&sort=<?= $sort ?>`;
  });
</script>
</body>
</html>
