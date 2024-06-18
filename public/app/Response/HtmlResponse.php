<?php

declare(strict_types=1);

namespace App\Response;

class HtmlResponse implements ResponseInterface
{
    static function View(string $content_view, array $data): void
    {
        $views_path = 'app/Views/general_view.php';

        $content_view = sprintf('app/Views/%s_view.php', $content_view);

        if (file_exists($views_path)) {
            if ($data != []) {
                extract($data, EXTR_PREFIX_SAME, 'data_');
            }

            require_once $views_path;
        }
    }
}
