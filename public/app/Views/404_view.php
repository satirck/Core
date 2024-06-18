<h1>
    <?php
    if (isset($message) && isset($code)) {
         echo sprintf('Error: %s, status code: %d', $message, $code);
    }
    ?>
</h1>
