<?php
namespace Apie\Export;

use Apie\Export\Lists\FileExtensionList;
use Psr\Http\Message\StreamInterface;

final class ChainedExport implements ExportInterface
{
    private array $exporters = [];
    /**
     * @param ExportInterface[] $exporters
     */
    public function __construct(iterable $exporters)
    {
        foreach ($exporters as $exporter) {
            foreach ($exporter->getSupportedExtensions() as $extension) {
                $this->exporters[$extension->toNative()] = $exporter;
            }
        }
    }

    public function streamFromSheets(array $sheets, string $outputFilename): StreamInterface
    {
        $extension = pathinfo($outputFilename, PATHINFO_EXTENSION);
        if (!isset($this->exporters[$extension])) {
            throw new \InvalidArgumentException("No exporter found for extension: {$extension}");
        }
        return $this->exporters[$extension]->streamFromSheets($sheets, $outputFilename);
    }

    public function getSupportedExtensions(): FileExtensionList
    {
        return new FileExtensionList(array_keys($this->exporters));
    }
}