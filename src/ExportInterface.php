<?php
namespace Apie\Export;

use Apie\Export\Lists\FileExtensionList;
use Psr\Http\Message\StreamInterface;
use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;

#[AutoconfigureTag(ExportInterface::class)]
interface ExportInterface
{
    public function streamFromSheets(array $sheets, string $outputFilename): StreamInterface;
    public function getSupportedExtensions(): FileExtensionList;
}
