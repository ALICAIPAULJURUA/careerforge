<?php

return [
    /*
    |--------------------------------------------------------------------------
    | PDF Driver
    |--------------------------------------------------------------------------
    |
    | This value determines which PDF generator implementation the
    | PdfGeneratorInterface resolves to. Supported: "dompdf", "browsershot"
    |
    */

    'driver' => env('PDF_DRIVER', 'dompdf'),
];
