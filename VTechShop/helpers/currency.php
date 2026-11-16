<?php
function get_currencies(): array {
    return [
        "EUR" => "€",
        "USD" => "$",
        "GBP" => "£",
        "CHF" => "CHF",
        "RUB" => "₽",
        "TRY" => "₺",
        "PLN" => "zł",
        "SEK" => "kr",
        "CAD" => "$",
        "MXN" => "$",
        "BRL" => "R$",
        "ARS" => "$",
        "CLP" => "$",
        "COP" => "$",
        "PEN" => "S/",
        "UYU" => "$",
        "JPY" => "¥",
        "CNY" => "¥",
        "INR" => "₹",
        "KRW" => "₩",
        "AUD" => "$",
        "NZD" => "$",
        "SGD" => "$",
        "HKD" => "$",
        "THB" => "฿",
        "ILS" => "₪",
        "AED" => "د.إ",
        "SAR" => "ر.س",
        "ZAR" => "R",
        "EGP" => "E£",
        "NGN" => "₦",
        "MAD" => "د.م."
    ];
}

function format_price(float $amount, string $currency_code = 'EUR'): string {
    $currencies = get_currencies();
    return number_format($amount, 2, ',', '.') . ' ' . ($currencies[$currency_code] ?? '€');
}