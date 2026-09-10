<?php

if (! function_exists('maskEmail')) {
    function maskEmail(string $email): string
    {
        $parts = explode('@', $email, 2);
        if (count($parts) < 2) {
            return $email;
        }

        $name = $parts[0];
        $domain = $parts[1];

        $length = strlen($name);
        if ($length <= 2) {
            $maskedName = substr($name, 0, 1).'*';
        } else {
            $maskedName = substr($name, 0, 2).str_repeat('*', max(1, $length - 3)).substr($name, -1);
        }

        return $maskedName.'@'.$domain;
    }
}
