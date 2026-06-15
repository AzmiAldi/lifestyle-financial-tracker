<?php

if (! function_exists('rupiah')) {
    function rupiah(float|int|string|null $amount): string
    {
        $numericAmount = (float) ($amount ?? 0);

        return 'Rp '.number_format($numericAmount, 0, ',', '.');
    }
}
