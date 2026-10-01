<img src="https://raw.githubusercontent.com/apie-lib/apie-lib-monorepo/main/docs/apie-logo.svg" width="100px" align="left" />
<h1>export</h1>






 [![Latest Stable Version](https://poser.pugx.org/apie/export/v)](https://packagist.org/packages/apie/export) [![Total Downloads](https://poser.pugx.org/apie/export/downloads)](https://packagist.org/packages/apie/export) [![Latest Unstable Version](https://poser.pugx.org/apie/export/v/unstable)](https://packagist.org/packages/apie/export) [![License](https://poser.pugx.org/apie/export/license)](https://packagist.org/packages/apie/export) [![PHP Composer](https://apie-lib.github.io/projectCoverage/coverage-export.svg)](https://apie-lib.github.io/projectCoverage/export/index.html)  

[![PHP Composer](https://github.com/apie-lib/export/actions/workflows/php.yml/badge.svg?event=push)](https://github.com/apie-lib/export/actions/workflows/php.yml)

This package is part of the [Apie](https://github.com/apie-lib) library.
The code is maintained in a monorepo, so PR's need to be sent to the [monorepo](https://github.com/apie-lib/apie-lib-monorepo/pulls)

## Documentation
Exports Apie resources to CSV, XLSX, ODS, and ZIP-based CSV files.

### Standalone usage
Install it with:
```bash
composer require apie/export
```

The exporters implement `Apie\Export\ExportInterface` (see `CsvExport`, `XlsxExport`, `OdsExport`, `ZippedCsvExport`) and can be used from a command, controller, or queue worker without a framework. Create the exporter that matches the required format, pass it the resource list, and stream or save the returned PSR-7 response. `Apie\Export\ChainedExport` picks the right exporter for a requested `Apie\Export\ValueObjects\FileExtension`, and `EntityExport` combines it with `apie/html-builders`' `ColumnSelector` and `apie/serializer` to export a bounded context's resources.

### Symfony integration
Via `apie/apie-bundle`, `export.yaml` is loaded automatically and tags each exporter with `Apie\Export\ExportInterface`, so `ChainedExport` and `EntityExport` are available as `apie.context` services.

### Laravel integration
Via `apie/laravel-apie`, the generated `Apie\Export\ExportServiceProvider` is auto-registered and wires the same exporters into the Laravel container.
