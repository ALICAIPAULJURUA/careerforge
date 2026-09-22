<?php

namespace App\Services\Pdf;

use Dompdf\Dompdf;
use Dompdf\Options;

class DompdfGenerator implements PdfGeneratorInterface
{
    public function generate(string $html): string
    {
        $options = new Options();
        $options->set('isHtml5ParserEnabled', true);
        $options->set('isRemoteEnabled', false);
        $options->set('defaultFont', 'Helvetica');
        $options->set('isFontSubsettingEnabled', true);
        $options->set('chroot', base_path());

        $dompdf = new Dompdf($options);
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();

        $output = $dompdf->output();

        if ($output === '' || $output === false) {
            throw new \RuntimeException('PDF generation failed – empty output.');
        }

        // Validate it looks like PDF (%PDF)
        if (!str_starts_with($output, '%PDF')) {
            throw new \RuntimeException('PDF generation failed – invalid PDF header.');
        }

        return $output;
    }
}
