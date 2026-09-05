<?php
namespace Apie\Export;

use Apie\Export\Concerns\FlattensValues;
use Apie\Export\Lists\FileExtensionList;
use Apie\Export\ValueObjects\FileExtension;
use Nyholm\Psr7\Stream;
use OpenSpout\Common\Entity\Row;
use OpenSpout\Writer\ODS\Writer;
use Psr\Http\Message\StreamInterface;
use UnitEnum;

class OdsExport implements ExportInterface
{
    use FlattensValues;

    public function streamFromSheets(array $sheets, string $outputFilename = 'export.ods'): StreamInterface
    {
        $tempFile = tempnam(sys_get_temp_dir(), 'apie_ods_');
        if ($tempFile === false) {
            throw new \RuntimeException('Unable to create temporary file for ODS export.');
        }

        $writer = new Writer();
        $writer->setCreator('Apie library');
        $writer->openToFile($tempFile);

        try {
            $sheetIndex = 0;
            foreach ($sheets as $sheetName => $data) {
                if ($sheetIndex > 0) {
                    $writer->addNewSheetAndMakeItCurrent();
                }

                foreach ($data as $row) {
                    // If row is Traversable or object, try to convert to array
                    if (is_object($row) && !$row instanceof \Stringable && !$row instanceof UnitEnum) {
                        if ($row instanceof \Traversable) {
                            $row = iterator_to_array($row);
                        } else {
                            // cast to array will extract properties; prefer that to avoid errors
                            $row = (array)$row;
                        }
                    }
                    if (!is_array($row)) {
                        // single value row -> wrap
                        $row = [$row];
                    }

                    $converted = [];
                    foreach ($row as $cell) {
                        $cell = $this->toSingleValue($cell);
                        if ($cell === null) {
                            $converted[] = '';
                        } else {
                            $converted[] = $cell;
                        }
                    }

                    $writer->addRow(Row::fromValues($converted));
                }

                $sheetIndex++;
            }

            $writer->close();

            $source = fopen($tempFile, 'rb');
            if ($source === false) {
                throw new \RuntimeException('Unable to read temporary ODS file.');
            }

            $stream = fopen('php://temp', 'r+');
            if ($stream === false) {
                fclose($source);
                throw new \RuntimeException('Unable to create temporary stream for ODS export.');
            }

            stream_copy_to_stream($source, $stream);
            fclose($source);
            rewind($stream);
        } finally {
            @unlink($tempFile);
        }

        return new Stream($stream);
    }

    public function getSupportedExtensions(): FileExtensionList
    {
        return new FileExtensionList([
            new FileExtension('ods'),
        ]);
    }
}
