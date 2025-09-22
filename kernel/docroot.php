<?php
function docroot()
{
    return $_SERVER['DOCUMENT_ROOT'];
}

function safe_strip_tags($value, $allowed_tags = ''): string
{
    if ($value === null) {
        return '';
    }

    return strip_tags((string)$value, $allowed_tags);
}
?>