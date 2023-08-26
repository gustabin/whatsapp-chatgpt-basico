<?php

/*
 * This file is part of the Kimai time-tracking app.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Export\Spreadsheet;

use App\Event\MetaDisplayEventInterface;
use App\Export\Spreadsheet\Extractor\AnnotationExtractor;
use App\Export\Spreadsheet\Extractor\MetaFieldExtractor;
use PhpOffice\PhpSpreadsheet\Spreadsheet;

final class EntityWithMetaFieldsExporter
{
    public function __construct(private SpreadsheetExporter $exporter, private AnnotationExtractor $annotationExtractor, private MetaFieldExtractor $metaFieldExtractor)
    {
    }

    public function export(string $class, array $entries, MetaDisplayEventInterface $event): Spreadsheet
    {
        $columns = array_merge($this->annotationExtractor->extract($class), $this->metaFieldExtractor->extract($event));

        return $this->exporter->export($columns, $entries);
    }
}
