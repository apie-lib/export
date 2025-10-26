<?php
namespace Apie\Export\Actions;

use Apie\Core\Attributes\Context;
use Apie\Core\Attributes\Not;
use Apie\Core\Attributes\Requires;
use Apie\Core\Attributes\StaticCheck;
use Apie\Core\BoundedContext\BoundedContextHashmap;
use Apie\Core\BoundedContext\BoundedContextId;
use Apie\Core\Context\ApieContext;
use Apie\Core\ContextConstants;
use Apie\Core\Datalayers\ApieDatalayer;
use Apie\Core\Enums\ConsoleCommand;
use Apie\Core\Exceptions\HttpNotFoundException;
use Apie\Export\EntityExport;
use Apie\Export\ExportInterface;
use Nyholm\Psr7\Response;
use Psr\Http\Message\ResponseInterface;

class ExportAll
{
    #[StaticCheck(new Not(new Requires(ConsoleCommand::class)))]
    public function export(
        #[Context()]
        EntityExport $exporter,
        #[Context(ContextConstants::BOUNDED_CONTEXT_ID)]
        string $boundedContextId,
        #[Context(ContextConstants::RESOURCE_NAME)]
        string $resourceName,
        #[Context()]
        BoundedContextHashmap $boundedContextHashmap,
        #[Context()]
        ApieContext $apieContext,
        #[Context()]
        ApieDatalayer $dataLayer,
        #[Context('filename')]
        string $outputFilename = 'export',
        #[Context('extension')]
        string $extension = 'xlsx'
    ): ResponseInterface
    {
        $boundedContext = $boundedContextHashmap[$boundedContextId];
        foreach ($boundedContext->resources as $resource) {
            if ($resource->getShortName() === $resourceName) {
                return new Response(
                    200, 
                    [
                        'Content-Type' => 'application/octet-stream',
                        'Content-Disposition' => 'attachment; filename="' . $outputFilename . '.' . $extension . '"',
                    ],
                    $exporter->streamFromEntityList(
                        $resource,
                        $dataLayer->all($resource, new BoundedContextId($boundedContextId)),
                        $apieContext,
                        $outputFilename . '.' . $extension
                    )
                );
            }
        }

        throw new HttpNotFoundException();
    }
}