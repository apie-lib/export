<?php
namespace Apie\Export;

use Apie\Core\Context\ApieContext;
use Apie\Core\Datalayers\Lists\EntityListInterface;
use Apie\Core\PropertyAccess;
use Apie\Export\Lists\FileExtensionList;
use Apie\HtmlBuilders\Columns\ColumnSelector;
use Apie\Serializer\Serializer;
use Psr\Http\Message\StreamInterface;

class EntityExport
{
    public function __construct(
        private readonly ColumnSelector $columnSelector,
        private readonly ExportInterface $exporter,
        private readonly Serializer $serializer
    ) {
    }
    
    public function streamFromEntityList(\ReflectionClass $resourceName, EntityListInterface $entityList, ApieContext $apieContext, string $outputFilename = 'export.xlsx'): StreamInterface
    {
        $columns = $this->columnSelector->getColumns($resourceName, $apieContext);
        $generator = function () use ($entityList, $columns, $apieContext) {
            $first = true;
            foreach ($entityList as $entity) {
                $data = [];
                foreach ($columns as $column) {
                    $data[$column] = $this->serializer->normalize(
                        PropertyAccess::getPropertyValue($entity, [$column], $apieContext, false),
                        $apieContext
                    );
                    if (is_array($data[$column])) {
                        $data[$column] = implode(', ', $data[$column]);
                    }
                }
                if ($first) {
                    yield $columns;
                    $first = false;
                }
                yield array_values($data);
            }
        };
        return $this->exporter->streamFromSheets([$resourceName->getShortName() => $generator()], $outputFilename);
    }

    public function getSupportedExtensions(): FileExtensionList
    {
        return $this->exporter->getSupportedExtensions();
    }
}
