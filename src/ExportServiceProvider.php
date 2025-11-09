<?php
namespace Apie\Export;

use Apie\ServiceProviderGenerator\UseGeneratedMethods;
use Illuminate\Support\ServiceProvider;

/**
 * This file is generated with apie/service-provider-generator from file: export.yaml
 * @codeCoverageIgnore
 */
class ExportServiceProvider extends ServiceProvider
{
    use UseGeneratedMethods;

    public function register()
    {
        $this->app->singleton(
            \Apie\Export\ExcelExport::class,
            function ($app) {
                return new \Apie\Export\ExcelExport(
                
                );
            }
        );
        \Apie\ServiceProviderGenerator\TagMap::register(
            $this->app,
            \Apie\Export\ExcelExport::class,
            array(
              0 =>
              array(
                'name' => 'Apie\\Export\\ExportInterface',
              ),
            )
        );
        $this->app->tag([\Apie\Export\ExcelExport::class], \Apie\Export\ExportInterface::class);
        $this->app->singleton(
            \Apie\Export\CsvExport::class,
            function ($app) {
                return new \Apie\Export\CsvExport(
                
                );
            }
        );
        \Apie\ServiceProviderGenerator\TagMap::register(
            $this->app,
            \Apie\Export\CsvExport::class,
            array(
              0 =>
              array(
                'name' => 'Apie\\Export\\ExportInterface',
              ),
            )
        );
        $this->app->tag([\Apie\Export\CsvExport::class], \Apie\Export\ExportInterface::class);
        $this->app->singleton(
            \Apie\Export\ZippedCsvExport::class,
            function ($app) {
                return new \Apie\Export\ZippedCsvExport(
                
                );
            }
        );
        \Apie\ServiceProviderGenerator\TagMap::register(
            $this->app,
            \Apie\Export\ZippedCsvExport::class,
            array(
              0 =>
              array(
                'name' => 'Apie\\Export\\ExportInterface',
              ),
            )
        );
        $this->app->tag([\Apie\Export\ZippedCsvExport::class], \Apie\Export\ExportInterface::class);
        $this->app->singleton(
            \Apie\Export\ChainedExport::class,
            function ($app) {
                return new \Apie\Export\ChainedExport(
                    $this->getTaggedServicesIterator(\Apie\Export\ExportInterface::class)
                );
            }
        );
        \Apie\ServiceProviderGenerator\TagMap::register(
            $this->app,
            \Apie\Export\ChainedExport::class,
            array(
              0 =>
              array(
                'name' => 'apie.context',
              ),
            )
        );
        $this->app->tag([\Apie\Export\ChainedExport::class], 'apie.context');
        $this->app->bind(\Apie\Export\ExportInterface::class, \Apie\Export\ChainedExport::class);
        
        $this->app->singleton(
            \Apie\Export\EntityExport::class,
            function ($app) {
                return new \Apie\Export\EntityExport(
                    $app->make(\Apie\HtmlBuilders\Columns\ColumnSelector::class),
                    $app->make(\Apie\Export\ExportInterface::class),
                    $app->make(\Apie\Serializer\Serializer::class)
                );
            }
        );
        \Apie\ServiceProviderGenerator\TagMap::register(
            $this->app,
            \Apie\Export\EntityExport::class,
            array(
              0 =>
              array(
                'name' => 'apie.context',
              ),
            )
        );
        $this->app->tag([\Apie\Export\EntityExport::class], 'apie.context');
        
    }
}
