<?php
declare(strict_types=1);

use Dompdf\Dompdf;
use Dompdf\Options;

require dirname(__DIR__) . '/vendor/autoload.php';

$input = $argv[1] ?? '';
$output = $argv[2] ?? '';

if ($input === '' || $output === '') {
    fwrite(STDERR, "Uso: php docs/generate_markdown_pdf.php <input.md> <output.pdf>\n");
    exit(1);
}

if (!is_file($input)) {
    fwrite(STDERR, "Arquivo nao encontrado: {$input}\n");
    exit(1);
}

$markdown = file($input, FILE_IGNORE_NEW_LINES);
if ($markdown === false) {
    fwrite(STDERR, "Falha ao ler: {$input}\n");
    exit(1);
}

function formatInlineMarkdownPdf(string $text): string
{
    $escaped = htmlspecialchars($text, ENT_QUOTES, 'UTF-8');
    $escaped = preg_replace('/`([^`]+)`/', '<code>$1</code>', $escaped) ?? $escaped;
    return preg_replace('/\*\*([^*]+)\*\*/', '<strong>$1</strong>', $escaped) ?? $escaped;
}

function markdownToHtmlPdf(array $lines): string
{
    $html = '';
    $inUl = false;
    $inOl = false;

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
            continue;
        }

        if (preg_match('/^#{1,3}\s+(.+)$/', $trim, $m) === 1) {
            $closeLists();
            $level = strspn($trim, '#');
            $html .= "<h{$level}>" . formatInlineMarkdownPdf($m[1]) . "</h{$level}>";
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
            $html .= '<li>' . formatInlineMarkdownPdf($m[1]) . '</li>';
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
            $html .= '<li>' . formatInlineMarkdownPdf($m[1]) . '</li>';
            continue;
        }

        $closeLists();
        $html .= '<p>' . formatInlineMarkdownPdf($trim) . '</p>';
    }

    $closeLists();
    return $html;
}

$body = markdownToHtmlPdf($markdown);

$html = '<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <style>
        @page { margin: 26px 28px 34px 28px; }
        body { font-family: DejaVu Sans, sans-serif; font-size: 11px; color: #202124; line-height: 1.48; }
        h1 { font-size: 22px; margin: 0 0 10px; color: #12334f; }
        h2 { font-size: 15px; margin: 18px 0 8px; padding-bottom: 4px; border-bottom: 1px solid #d8e0e8; color: #17395c; }
        h3 { font-size: 12px; margin: 14px 0 6px; color: #244a6b; }
        p { margin: 6px 0; }
        ul, ol { margin: 6px 0 10px 18px; padding: 0; }
        li { margin: 2px 0; }
        code { background: #eef3f8; border: 1px solid #d7dde5; border-radius: 3px; padding: 1px 4px; font-size: 10px; }
        .meta { margin-bottom: 12px; padding: 10px 12px; background: #f4f8fb; border: 1px solid #d8e0e8; border-radius: 6px; }
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

if (file_put_contents($output, $dompdf->output()) === false) {
    fwrite(STDERR, "Falha ao escrever: {$output}\n");
    exit(1);
}

echo "PDF gerado em: {$output}\n";
