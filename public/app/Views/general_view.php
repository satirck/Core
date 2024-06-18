<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>In future</title>
</head>
<body>
<?php
if (isset($content_view)) {
    require_once $content_view;
} else {
  echo 'Not found page! in general page';
}
?>
</body>
</html>
