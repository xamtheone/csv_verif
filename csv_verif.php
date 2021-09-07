<?php

/*
csv_verif.php path [nb_columns]
*/

$path = $argv[1] ?? '';
$column_count = (int)($argv[2] ?? 0);

if (empty($path)) {
  echo "Missing file or dir path as argument\n";
  exit;
}

if (is_file($path)) {
  verifFile($path, $column_count);
}
elseif (is_dir($path)) {
  verifDir($path, $column_count);
}

function verifFile($file, $column_count = 0) {
  echo date('c')." - Verifying $file...\n";
  $count_errors = 0;
  $h = fopen($file, 'r');
  if (!$h) {
    echo "Failed to read file\n";
    return;
  }

  $separator = getSeparator($h);

  if (!$separator) {
    echo "Failed to find a separator\n";
    return;
  }

  $line_num = 0;
  while ($line = fgetcsv($h)) {
    $line_num++;
    if (empty($column_count)) {
      $column_count = count($line);
      echo "Expecting $column_count columns\n";
      continue;
    }

    if (count($line) != $column_count) {
      echo "ERROR Line $line_num has ".count($line)." columns\n";
      $count_errors++;
    }
  }

  fclose($h);

  echo "$line_num lines, $count_errors errors\n";
}

function verifDir($path, $column_count = 0) {
  $files = scandir($path);
  foreach ($files as $file) {

    if ($file == '.' || $file == '..') {
      continue;
    }

    $ext = pathinfo($file, PATHINFO_EXTENSION);
    if ($ext != 'csv') {
      continue;
    }

    verifFile($path.DIRECTORY_SEPARATOR.$file, $column_count);
  }
}

function getSeparator($handle) {
  $line = fgets($handle);
  fseek($handle, 0);
  for ($i = 0; $i < strlen($line); $i++) {
    if (in_array($line[$i], [',', ';', "\t"])) {
      return $line[$i];
    }
  }

  return false;
}