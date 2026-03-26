<?php

declare(strict_types=1);

$projectRoot = dirname(__DIR__);
$sourcePath = $projectRoot . DIRECTORY_SEPARATOR . 'docs' . DIRECTORY_SEPARATOR . 'hostinger-zoho-preparation-guide.md';
$outputPath = $projectRoot . DIRECTORY_SEPARATOR . 'docs' . DIRECTORY_SEPARATOR . 'hostinger-zoho-preparation-guide.pdf';

$markdown = file($sourcePath, FILE_IGNORE_NEW_LINES);

if ($markdown === false) {
    fwrite(STDERR, "Unable to read source guide.\n");
    exit(1);
}

$pageWidth = 595.28;
$pageHeight = 841.89;
$marginLeft = 48.0;
$marginRight = 48.0;
$marginTop = 54.0;
$marginBottom = 48.0;

$fontMap = [
    'regular' => 'F1',
    'bold' => 'F2',
];

$layoutMap = [
    'h1' => ['font' => 'bold', 'size' => 24.0, 'leading' => 30.0, 'gap_after' => 8.0, 'chars' => 38],
    'h2' => ['font' => 'bold', 'size' => 17.0, 'leading' => 22.0, 'gap_after' => 5.0, 'chars' => 58],
    'h3' => ['font' => 'bold', 'size' => 13.0, 'leading' => 18.0, 'gap_after' => 3.0, 'chars' => 72],
    'body' => ['font' => 'regular', 'size' => 11.0, 'leading' => 15.0, 'gap_after' => 2.0, 'chars' => 92],
    'bullet' => ['font' => 'regular', 'size' => 11.0, 'leading' => 15.0, 'gap_after' => 1.0, 'chars' => 88],
    'number' => ['font' => 'regular', 'size' => 11.0, 'leading' => 15.0, 'gap_after' => 1.0, 'chars' => 88],
];

$pages = [];
$currentPage = [];
$currentY = $pageHeight - $marginTop;

$pushLine = static function (array &$page, float $x, float $y, string $fontKey, float $size, string $text) use ($fontMap): void {
    $escaped = str_replace(['\\', '(', ')'], ['\\\\', '\(', '\)'], $text);
    $page[] = sprintf(
        "BT /%s %.2F Tf %.2F %.2F Td (%s) Tj ET",
        $fontMap[$fontKey],
        $size,
        $x,
        $y,
        $escaped
    );
};

$newPage = static function () use (&$pages, &$currentPage, &$currentY, $pageHeight, $marginTop): void {
    if ($currentPage !== []) {
        $pages[] = $currentPage;
    }

    $currentPage = [];
    $currentY = $pageHeight - $marginTop;
};

$ensureSpace = static function (float $heightNeeded) use (&$currentY, $marginBottom, &$newPage): void {
    if (($currentY - $heightNeeded) < $marginBottom) {
        $newPage();
    }
};

$renderWrapped = static function (string $text, string $type, float $xOffset = 0.0) use (
    &$currentPage,
    &$currentY,
    $marginLeft,
    $pushLine,
    $layoutMap,
    $ensureSpace
): void {
    $layout = $layoutMap[$type];
    $wrapped = wordwrap(trim($text), $layout['chars'], "\n", true);
    $lines = explode("\n", $wrapped);
    $blockHeight = (count($lines) * $layout['leading']) + $layout['gap_after'];

    $ensureSpace($blockHeight);

    foreach ($lines as $line) {
        $pushLine($currentPage, $marginLeft + $xOffset, $currentY, $layout['font'], $layout['size'], $line);
        $currentY -= $layout['leading'];
    }

    $currentY -= $layout['gap_after'];
};

$newPage();

foreach ($markdown as $line) {
    $trimmed = trim($line);

    if ($trimmed === '') {
        $currentY -= 7.0;
        continue;
    }

    if (str_starts_with($trimmed, '# ')) {
        $renderWrapped(substr($trimmed, 2), 'h1');
        continue;
    }

    if (str_starts_with($trimmed, '## ')) {
        $currentY -= 4.0;
        $renderWrapped(substr($trimmed, 3), 'h2');
        continue;
    }

    if (str_starts_with($trimmed, '### ')) {
        $currentY -= 2.0;
        $renderWrapped(substr($trimmed, 4), 'h3');
        continue;
    }

    if (preg_match('/^\d+\.\s+(.+)$/', $trimmed, $matches) === 1) {
        $renderWrapped('- ' . $matches[1], 'number');
        continue;
    }

    if (str_starts_with($trimmed, '- ')) {
        $renderWrapped('- ' . substr($trimmed, 2), 'bullet');
        continue;
    }

    $renderWrapped($trimmed, 'body');
}

if ($currentPage !== []) {
    $pages[] = $currentPage;
}

$objects = [];

$addObject = static function (string $content) use (&$objects): int {
    $objects[] = $content;
    return count($objects);
};

$fontRegularId = $addObject("<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica >>");
$fontBoldId = $addObject("<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica-Bold >>");

$pageObjectIds = [];
$contentObjectIds = [];

foreach ($pages as $pageCommands) {
    $stream = implode("\n", $pageCommands) . "\n";
    $contentObjectIds[] = $addObject("<< /Length " . strlen($stream) . " >>\nstream\n" . $stream . "endstream");
}

$pagesRootId = count($objects) + (count($pages) * 2) + 2;
$catalogId = $pagesRootId + 1;

foreach ($contentObjectIds as $contentId) {
    $pageObjectIds[] = $addObject(
        "<< /Type /Page /Parent {$pagesRootId} 0 R /MediaBox [0 0 {$pageWidth} {$pageHeight}] " .
        "/Resources << /Font << /F1 {$fontRegularId} 0 R /F2 {$fontBoldId} 0 R >> >> " .
        "/Contents {$contentId} 0 R >>"
    );
}

$kids = implode(' ', array_map(static fn (int $id): string => "{$id} 0 R", $pageObjectIds));
$addObject("<< /Type /Pages /Count " . count($pageObjectIds) . " /Kids [ {$kids} ] >>");
$addObject("<< /Type /Catalog /Pages {$pagesRootId} 0 R >>");

$pdf = "%PDF-1.4\n";
$offsets = [0];

foreach ($objects as $index => $object) {
    $offsets[] = strlen($pdf);
    $pdf .= ($index + 1) . " 0 obj\n" . $object . "\nendobj\n";
}

$xrefOffset = strlen($pdf);
$pdf .= "xref\n0 " . (count($objects) + 1) . "\n";
$pdf .= "0000000000 65535 f \n";

for ($i = 1; $i <= count($objects); $i++) {
    $pdf .= sprintf("%010d 00000 n \n", $offsets[$i]);
}

$pdf .= "trailer\n<< /Size " . (count($objects) + 1) . " /Root {$catalogId} 0 R >>\n";
$pdf .= "startxref\n{$xrefOffset}\n%%EOF";

file_put_contents($outputPath, $pdf);

echo $outputPath . PHP_EOL;
