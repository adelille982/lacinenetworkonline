<?php

namespace App\Service;

use Symfony\Component\HttpFoundation\StreamedResponse;

class CsvExporterService
{
    public function export(array $data, array $headers, string $filename): StreamedResponse
    {
        $response = new StreamedResponse(function () use ($data, $headers) {
            $handle = fopen('php://output', 'w');
            echo "\xEF\xBB\xBF";
            fputcsv($handle, $headers, ';');
            foreach ($data as $row) {
                fputcsv($handle, $row, ';');
            }
            fclose($handle);
        });

        $response->headers->set('Content-Type', 'text/csv; charset=utf-8');
        $response->headers->set('Content-Disposition', 'attachment; filename="' . $filename . '"');

        return $response;
    }
}
