<?php
namespace Apie\Export\Actions;

use Apie\Core\Attributes\Context;
use Apie\Core\Attributes\ExampleValue;
use Apie\Core\Attributes\Not;
use Apie\Core\Attributes\Requires;
use Apie\Core\Attributes\Route;
use Apie\Core\Attributes\StaticCheck;
use Apie\Core\Context\ApieContext;
use Apie\Core\ContextConstants;
use Apie\Core\Datalayers\Lists\EntityListInterface;
use Apie\Core\Enums\ConsoleCommand;
use Apie\Core\Enums\RequestMethod;
use Apie\Core\FileStorage\StoredFile;
use Apie\Export\EntityExport;
use Psr\Http\Message\UploadedFileInterface;

class ExportAll
{
    #[StaticCheck(
        new Not(new Requires(ConsoleCommand::class)),
        new Not(new Requires(ContextConstants::MCP_SERVER)),
        new Not(new Requires(ContextConstants::MAIN_MENU_BUILDER))
    )]
    #[Route('/export/{resourceName}.{extension}', RequestMethod::GET)]
    public function export(
        #[Context()]
        EntityExport $exporter,
        #[Context()]
        EntityListInterface $list,
        #[Context()]
        \ReflectionClass $resourceName,
        #[Context()]
        ApieContext $apieContext,
        #[Context('filename')]
        ?string $outputFilename = null,
        #[Context('extension')]
        #[ExampleValue('xlsx', 'Excel 2007+ file')]
        #[ExampleValue('csv', 'Comma separated values')]
        string $extension = 'xlsx'
    ): UploadedFileInterface {
        return StoredFile::createFromResource(
            $exporter->streamFromEntityList(
                $resourceName,
                $list,
                $apieContext,
                $outputFilename . '.' . $extension
            ),
            clientOriginalFile: $outputFilename . '.' . $extension
        );
    }
}
