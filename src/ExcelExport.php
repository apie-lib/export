<?php
namespace Apie\Export;

use Apie\Export\Concerns\FlattensValues;
use Apie\Export\Lists\FileExtensionList;
use Apie\Export\ValueObjects\FileExtension;
use Nyholm\Psr7\Stream;
use Psr\Http\Message\StreamInterface;
use ZipStream\ZipStream;

class ExcelExport implements ExportInterface
{
    use FlattensValues;

    public function streamFromSheets(array $sheets, string $outputFilename = 'export.xlsx'): StreamInterface
    {
        // Sanitize and reindex sheet names
        $sheetNames = [];
        $i = 1;
        foreach ($sheets as $name => $gen) {
            $clean = substr(preg_replace('/[\\\\\/\?\*\[\]:]/', '', (string)$name), 0, 31) ?: "Sheet{$i}";
            $sheetNames[] = $clean;
            $i++;
        }

        // Initialize ZIP stream
        $stream = fopen('php://temp', 'r+');
        $outputStream = new Stream($stream);

        $zip = new ZipStream(
            outputName: $outputFilename,
            outputStream: $outputStream,
            sendHttpHeaders: false,
        );

        // === [Content_Types].xml ===
        $types = [
            '<?xml version="1.0" encoding="UTF-8"?>',
            '<Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types">',
            '  <Default Extension="rels" ContentType="application/vnd.openxmlformats-package.relationships+xml"/>',
            '  <Default Extension="xml" ContentType="application/xml"/>',
            '  <Override PartName="/xl/workbook.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet.main+xml"/>',
            '  <Override PartName="/xl/styles.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.styles+xml"/>',
        ];
        foreach ($sheetNames as $i => $_) {
            $types[] = '  <Override PartName="/xl/worksheets/sheet' . ($i + 1) . '.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.worksheet+xml"/>';
        }
        $types[] = '</Types>';
        $zip->addFile('[Content_Types].xml', implode("\n", $types));

        // === _rels/.rels ===
        $zip->addFile('_rels/.rels', <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">
  <Relationship Id="rId1"
    Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/officeDocument"
    Target="xl/workbook.xml"/>
</Relationships>
XML);

        // === xl/workbook.xml ===
        $sheetsXml = [];
        foreach ($sheetNames as $i => $name) {
            $sheetsXml[] = '    <sheet name="' . htmlspecialchars($name, ENT_XML1) . '" sheetId="' . ($i + 1) . '" r:id="rId' . ($i + 1) . '"/>';
        }
        $workbookXml = <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<workbook xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main"
          xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships">
  <sheets>
    {SHEETS}
  </sheets>
</workbook>
XML;
        $workbookXml = str_replace('{SHEETS}', implode("\n", $sheetsXml), $workbookXml);
        $zip->addFile('xl/workbook.xml', $workbookXml);

        // === xl/_rels/workbook.xml.rels ===
        $relsXml = [
            '<?xml version="1.0" encoding="UTF-8"?>',
            '<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">',
        ];
        foreach ($sheetNames as $i => $_) {
            $relsXml[] = '  <Relationship Id="rId' . ($i + 1) .
                         '" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/worksheet"' .
                         ' Target="worksheets/sheet' . ($i + 1) . '.xml"/>';
        }
        $relsXml[] = '  <Relationship Id="rId' . (count($sheetNames) + 1) .
                     '" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/styles"' .
                     ' Target="styles.xml"/>';
        $relsXml[] = '</Relationships>';
        $zip->addFile('xl/_rels/workbook.xml.rels', implode("\n", $relsXml));

        // === xl/styles.xml (minimal) ===
        $stylesXml = <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<styleSheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main">
  <fonts count="1">
    <font><sz val="11"/><color theme="1"/><name val="Calibri"/><family val="2"/></font>
  </fonts>
  <fills count="1">
    <fill><patternFill patternType="none"/></fill>
  </fills>
  <borders count="1">
    <border><left/><right/><top/><bottom/><diagonal/></border>
  </borders>
  <cellStyleXfs count="1">
    <xf numFmtId="0" fontId="0" fillId="0" borderId="0"/>
  </cellStyleXfs>
  <cellXfs count="2">
    <xf numFmtId="0" fontId="0" fillId="0" borderId="0" xfId="0"/> <!-- style 0: text -->
    <xf numFmtId="1" fontId="0" fillId="0" borderId="0" xfId="0" applyNumberFormat="1"/> <!-- style 1: numeric -->
  </cellXfs>
</styleSheet>
XML;
        $zip->addFile('xl/styles.xml', $stylesXml);

        // === Add each worksheet ===
        $index = 1;
        foreach ($sheets as $name => $rowsGenerator) {
            $zip->addFileFromCallback("xl/worksheets/sheet{$index}.xml", function () use ($rowsGenerator) {
                $stream = fopen('php://temp', 'r+');
                fwrite($stream, '<?xml version="1.0" encoding="UTF-8"?>' . "\n");
                fwrite($stream, '<worksheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main">' . "\n");
                fwrite($stream, '<sheetData>' . "\n");

                $rowIndex = 1;
                foreach ($rowsGenerator as $row) {
                    fwrite($stream, '<row r="' . $rowIndex . '">');
                    $colIndex = 1;
                    foreach ($row as $cellValue) {
                        $cellValue = $this->toSingleValue($cellValue);
                        if ($cellValue === null) {
                            continue;
                        }
                        $colLetter = $this->getExcelColumnName($colIndex);
                        if (is_bool($cellValue)) {
                            fwrite($stream, '<c r="' . $colLetter . $rowIndex . '" s="1"><v>' . $cellValue ? '1' : '0' . '</v></c>');
                        } elseif (is_numeric($cellValue)) {
                            fwrite($stream, '<c r="' . $colLetter . $rowIndex . '" s="1"><v>' . $cellValue . '</v></c>');
                        } else {
                            $escaped = htmlspecialchars((string)$cellValue, ENT_XML1);
                            fwrite($stream, '<c t="inlineStr" r="' . $colLetter . $rowIndex . '" s="0"><is><t>' . $escaped . '</t></is></c>');
                        }
                        $colIndex++;
                    }
                    fwrite($stream, '</row>' . "\n");
                    $rowIndex++;
                }

                fwrite($stream, '</sheetData>' . "\n");
                fwrite($stream, '</worksheet>' . "\n");
                rewind($stream);
                return $stream;
            });
            $index++;
        }

        $zip->finish();
        rewind($stream);
        return $outputStream;
    }

    public function getSupportedExtensions(): FileExtensionList
    {
        return new FileExtensionList([
            new FileExtension('xlsx'),
        ]);
    }

    /**
     * Convert 1-based column index to Excel column name (A, B, ..., Z, AA, AB, ...).
     */
    private function getExcelColumnName(int $index): string
    {
        $name = '';
        while ($index > 0) {
            $index--;
            $name = chr(65 + ($index % 26)) . $name;
            $index = intdiv($index, 26);
        }
        return $name;
    }
}
