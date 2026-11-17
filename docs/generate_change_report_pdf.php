<?php
declare(strict_types=1);

use Dompdf\Dompdf;
use Dompdf\Options;

require dirname(__DIR__) . '/vendor/autoload.php';

$input = __DIR__ . '/RELATORIO_MUDANCAS_2026-03-13_a_2026-03-16.md';
$defaultOutput = __DIR__ . '/RELATORIO_MUDANCAS_2026-03-13_a_2026-03-16.pdf';
$output = $argv[1] ?? $defaultOutput;

if (!is_file($input)) {
    fwrite(STDERR, "Arquivo nao encontrado: {$input}\n");
    exit(1);
}

$markdown = file($input, FILE_IGNORE_NEW_LINES);
if ($markdown === false) {
    fwrite(STDERR, "Falha ao ler: {$input}\n");
    exit(1);
}

function formatInline(string $text): string
{
    $escaped = htmlspecialchars($text, ENT_QUOTES, 'UTF-8');
    return preg_replace('/`([^`]+)`/', '<code>$1</code>', $escaped) ?? $escaped;
}

function markdownToHtml(array $lines): string
{
    $html = '';
    $inUl = false;
    $inOl = false;
    $inTable = false;

    $closeLists = static function () use (&$html, &$inUl, &$inOl): void {
        if ($inUl) {
            $html .= '</ul>';
            $inUl = false;
        }
        if ($inOl) {
            $html .= '</ol>';
            $inOl = false;
        }
    };

    foreach ($lines as $line) {
        $trim = trim($line);

        if ($trim === '') {
            $closeLists();
            if ($inTable) {
                $html .= '</tbody></table>';
                $inTable = false;
            }
            continue;
        }

        if (preg_match('/^#{1,3}\s+(.+)$/', $trim, $m) === 1) {
            $closeLists();
            if ($inTable) {
                $html .= '</tbody></table>';
                $inTable = false;
            }
            $level = strspn($trim, '#');
            $text = formatInline($m[1]);
            $html .= "<h{$level}>{$text}</h{$level}>";
            continue;
        }

        if (preg_match('/^\d+\.\s+(.+)$/', $trim, $m) === 1) {
            if ($inUl) {
                $html .= '</ul>';
                $inUl = false;
            }
            if (!$inOl) {
                $html .= '<ol>';
                $inOl = true;
            }
            $html .= '<li>' . formatInline($m[1]) . '</li>';
            continue;
        }

        if (preg_match('/^-\s+(.+)$/', $trim, $m) === 1) {
            if ($inOl) {
                $html .= '</ol>';
                $inOl = false;
            }
            if (!$inUl) {
                $html .= '<ul>';
                $inUl = true;
            }
            $html .= '<li>' . formatInline($m[1]) . '</li>';
            continue;
        }

        if (str_starts_with($trim, '|') && str_ends_with($trim, '|')) {
            $closeLists();
            $cells = array_map('trim', explode('|', trim($trim, '|')));
            $isSeparator = true;
            foreach ($cells as $cell) {
                if (!preg_match('/^:?-{3,}:?$/', $cell)) {
                    $isSeparator = false;
                    break;
                }
            }
            if ($isSeparator) {
                continue;
            }

            if (!$inTable) {
                $html .= '<table><thead><tr>';
                foreach ($cells as $cell) {
                    $html .= '<th>' . formatInline($cell) . '</th>';
                }
                $html .= '</tr></thead><tbody>';
                $inTable = true;
            } else {
                $html .= '<tr>';
                foreach ($cells as $cell) {
                    $html .= '<td>' . formatInline($cell) . '</td>';
                }
                $html .= '</tr>';
            }
            continue;
        }

        $closeLists();
        if ($inTable) {
            $html .= '</tbody></table>';
            $inTable = false;
        }
        $html .= '<p>' . formatInline($trim) . '</p>';
    }

    $closeLists();
    if ($inTable) {
        $html .= '</tbody></table>';
    }

    return $html;
}

$body = markdownToHtml($markdown);

$html = '<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <style>
        @page { margin: 28px 26px; }
        body { font-family: DejaVu Sans, sans-serif; font-size: 11px; color: #202124; line-height: 1.45; }
        h1 { font-size: 21px; margin: 0 0 10px; color: #0f2742; }
        h2 { font-size: 15px; margin: 18px 0 8px; padding-bottom: 4px; border-bottom: 1px solid #d7dde5; color: #17395c; }
        h3 { font-size: 12px; margin: 14px 0 6px; color: #17395c; }
        p { margin: 6px 0; }
        ul, ol { margin: 6px 0 10px 18px; padding: 0; }
        li { margin: 2px 0; }
        code { background: #eef2f6; border: 1px solid #d7dde5; border-radius: 3px; padding: 1px 4px; font-size: 10px; }
        table { width: 100%; border-collapse: collapse; margin: 10px 0; table-layout: fixed; }
        th, td { border: 1px solid #cbd5e1; padding: 6px; text-align: left; vertical-align: top; word-wrap: break-word; }
        thead th { background: #eef4fb; }
    </style>
</head>
<body>' . $body . '</body>
</html>';

$options = new Options();
$options->set('isHtml5ParserEnabled', true);
$options->set('isRemoteEnabled', false);

$dompdf = new Dompdf($options);
$dompdf->loadHtml($html, 'UTF-8');
$dompdf->setPaper('A4', 'portrait');
$dompdf->render();

file_put_contents($output, $dompdf->output());
echo "PDF gerado em: {$output}\n";
