<?php

namespace App\Modules\Shared\Infrastructure\Export;

use Symfony\Component\HttpFoundation\StreamedResponse;

final class SpreadsheetExport
{
    /**
     * @param  list<string>  $header
     * @param  list<list<string>>  $lines
     */
    public static function csv(string $filename, array $header, array $lines): StreamedResponse
    {
        return response()->streamDownload(function () use ($header, $lines): void {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, $header);
            foreach ($lines as $line) {
                fputcsv($handle, $line);
            }
            fclose($handle);
        }, $filename, ['Content-Type' => 'text/csv']);
    }

    /**
     * @param  list<string>  $header
     * @param  list<list<string>>  $lines
     */
    public static function excel(string $filename, string $sheet, array $header, array $lines): StreamedResponse
    {
        return response()->streamDownload(function () use ($sheet, $header, $lines): void {
            echo '<?xml version="1.0"?><Workbook xmlns="urn:schemas-microsoft-com:office:spreadsheet"><Worksheet ss:Name="'.self::xml($sheet).'" xmlns:ss="urn:schemas-microsoft-com:office:spreadsheet"><Table>';
            echo '<Row>'.implode('', array_map(fn (string $cell): string => '<Cell><Data ss:Type="String">'.self::xml($cell).'</Data></Cell>', $header)).'</Row>';

            foreach ($lines as $line) {
                echo '<Row>'.implode('', array_map(fn (string $cell): string => '<Cell><Data ss:Type="String">'.self::xml($cell).'</Data></Cell>', $line)).'</Row>';
            }

            echo '</Table></Worksheet></Workbook>';
        }, $filename, ['Content-Type' => 'application/vnd.ms-excel']);
    }

    private static function xml(string $value): string
    {
        return htmlspecialchars($value, ENT_XML1 | ENT_QUOTES, 'UTF-8');
    }
}
