<?php

namespace App\Services\Pdf;

interface PdfGeneratorInterface
{
    /**
     * Generate PDF binary from HTML string.
     *
     * @param string $html Rendered HTML for the resume
     * @return string PDF binary content
     *
     * @throws \Throwable If generation fails – caller should handle and never serve corrupt file
     */
    public function generate(string $html): string;
}
