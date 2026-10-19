<?php

namespace App\Modules\Shared\Infrastructure\Sms;

use Illuminate\Http\UploadedFile;
use RuntimeException;
use ZipArchive;

/**
 * Turns an uploaded contact file or a semicolon-separated list into phone rows.
 * Columns are phone, and optionally name and business name.
 */
final class ContactListParser
{
    /**
     * @return list<array{phone: string, name: string, business_name: string}>
     */
    public function semicolon(string $raw): array
    {
        $rows = [];

        foreach (preg_split('/[;,\r\n]+/', $raw) ?: [] as $part) {
            $phone = trim($part);

            if ($phone !== '') {
                $rows[] = ['phone' => $phone, 'name' => '', 'business_name' => ''];
            }
        }

        return $rows;
    }

    /**
     * @return list<array{phone: string, name: string, business_name: string}>
     */
    public function file(UploadedFile $file): array
    {
        $extension = strtolower($file->getClientOriginalExtension());

        if (in_array($extension, ['csv', 'txt'], true)) {
            return $this->csv((string) file_get_contents($file->getRealPath()));
        }

        if ($extension === 'xlsx') {
            return $this->xlsx($file->getRealPath());
        }

        throw new RuntimeException('Save the contact file as CSV or Excel (.xlsx).');
    }

    /**
     * @return list<array{phone: string, name: string, business_name: string}>
     */
    public function csv(string $contents): array
    {
        $lines = preg_split('/\r\n|\n|\r/', trim($contents)) ?: [];
        $rows = [];
        $header = null;

        foreach ($lines as $index => $line) {
            if (trim($line) === '') {
                continue;
            }

            $cells = str_getcsv($line);

            if ($index === 0 && $this->looksLikeHeader($cells)) {
                $header = array_map(fn ($cell): string => strtolower(trim((string) $cell)), $cells);

                continue;
            }

            $rows[] = $this->row($cells, $header);
        }

        return array_values(array_filter($rows, fn (array $row): bool => $row['phone'] !== ''));
    }

    /**
     * @param  list<string|null>  $cells
     */
    private function looksLikeHeader(array $cells): bool
    {
        $first = strtolower(trim((string) ($cells[0] ?? '')));

        return in_array($first, ['phone', 'mobile', 'number', 'msisdn'], true);
    }

    /**
     * @param  list<string|null>  $cells
     * @param  list<string>|null  $header
     * @return array{phone: string, name: string, business_name: string}
     */
    private function row(array $cells, ?array $header): array
    {
        if ($header === null) {
            return [
                'phone' => trim((string) ($cells[0] ?? '')),
                'name' => trim((string) ($cells[1] ?? '')),
                'business_name' => trim((string) ($cells[2] ?? '')),
            ];
        }

        $mapped = [];

        foreach ($header as $index => $name) {
            $mapped[$name] = trim((string) ($cells[$index] ?? ''));
        }

        return [
            'phone' => $mapped['phone'] ?? $mapped['mobile'] ?? $mapped['number'] ?? $mapped['msisdn'] ?? '',
            'name' => $mapped['name'] ?? '',
            'business_name' => $mapped['business_name'] ?? $mapped['business'] ?? $mapped['organisation'] ?? '',
        ];
    }

    /**
     * @return list<array{phone: string, name: string, business_name: string}>
     */
    private function xlsx(string $path): array
    {
        $zip = new ZipArchive;

        if ($zip->open($path) !== true) {
            throw new RuntimeException('The Excel file could not be read. Save it as CSV and upload that instead.');
        }

        $shared = [];
        $sharedXml = $zip->getFromName('xl/sharedStrings.xml');

        if (is_string($sharedXml)) {
            preg_match_all('/<t[^>]*>(.*?)<\/t>/s', $sharedXml, $matches);
            $shared = array_map(fn (string $value): string => html_entity_decode(strip_tags($value)), $matches[1]);
        }

        $sheet = $zip->getFromName('xl/worksheets/sheet1.xml');
        $zip->close();

        if (! is_string($sheet)) {
            throw new RuntimeException('The Excel file has no first sheet.');
        }

        preg_match_all('/<row[^>]*>(.*?)<\/row>/s', $sheet, $rowMatches);
        $lines = [];

        foreach ($rowMatches[1] as $rowXml) {
            preg_match_all('/<c([^>]*)>(.*?)<\/c>/s', $rowXml, $cellMatches, PREG_SET_ORDER);
            $values = [];

            foreach ($cellMatches as $cell) {
                $type = str_contains($cell[1], 't="s"') ? 's' : 'n';
                preg_match('/<v>(.*?)<\/v>/s', $cell[2], $value);
                $raw = html_entity_decode($value[1] ?? '');
                $values[] = $type === 's' ? (string) ($shared[(int) $raw] ?? '') : $raw;
            }

            if ($values !== []) {
                $lines[] = $values;
            }
        }

        if ($lines === []) {
            return [];
        }

        $header = $this->looksLikeHeader($lines[0]) ? array_map(fn ($cell): string => strtolower(trim((string) $cell)), $lines[0]) : null;
        $rows = [];

        foreach ($lines as $index => $cells) {
            if ($header !== null && $index === 0) {
                continue;
            }

            $rows[] = $this->row($cells, $header);
        }

        return array_values(array_filter($rows, fn (array $row): bool => $row['phone'] !== ''));
    }
}
