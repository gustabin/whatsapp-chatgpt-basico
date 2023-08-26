<?php

/*
 * This file is part of the Kimai time-tracking app.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Export\Base;

use App\Entity\ExportableItem;
use App\Export\ExportFilename;
use App\Pdf\HtmlToPdfConverter;
use App\Pdf\PdfContext;
use App\Pdf\PdfRendererTrait;
use App\Project\ProjectStatisticService;
use App\Repository\Query\TimesheetQuery;
use Symfony\Component\HttpFoundation\Response;
use Twig\Environment;

class PDFRenderer implements DispositionInlineInterface
{
    use RendererTrait;
    use PDFRendererTrait;

    private string $id = 'pdf';
    private string $template = 'default.pdf.twig';
    private array $pdfOptions = [];

    public function __construct(private Environment $twig, private HtmlToPdfConverter $converter, private ProjectStatisticService $projectStatisticService)
    {
    }

    protected function getTemplate(): string
    {
        return '@export/' . $this->template;
    }

    protected function getOptions(TimesheetQuery $query): array
    {
        $decimal = false;
        if (null !== $query->getCurrentUser()) {
            $decimal = $query->getCurrentUser()->isExportDecimal();
        } elseif (null !== $query->getUser()) {
            $decimal = $query->getUser()->isExportDecimal();
        }

        return ['decimal' => $decimal];
    }

    public function getPdfOptions(): array
    {
        return $this->pdfOptions;
    }

    public function setPdfOption(string $key, string $value): PDFRenderer
    {
        $this->pdfOptions[$key] = $value;

        return $this;
    }

    /**
     * @param ExportableItem[] $timesheets
     * @param TimesheetQuery $query
     * @return Response
     * @throws \Twig\Error\LoaderError
     * @throws \Twig\Error\RuntimeError
     * @throws \Twig\Error\SyntaxError
     */
    public function render(array $timesheets, TimesheetQuery $query): Response
    {
        $filename = new ExportFilename($query);
        $context = new PdfContext();
        $context->setOption('filename', $filename->getFilename());

        $summary = $this->calculateSummary($timesheets);
        $content = $this->twig->render($this->getTemplate(), array_merge([
            'entries' => $timesheets,
            'query' => $query,
            'summaries' => $summary,
            'budgets' => $this->calculateProjectBudget($timesheets, $query, $this->projectStatisticService),
            'decimal' => false,
            'pdfContext' => $context
        ], $this->getOptions($query)));

        $pdfOptions = array_merge($context->getOptions(), $this->getPdfOptions());

        $content = $this->converter->convertToPdf($content, $pdfOptions);

        return $this->createPdfResponse($content, $context);
    }

    public function setTemplate(string $filename): PDFRenderer
    {
        $this->template = $filename;

        return $this;
    }

    public function setId(string $id): PDFRenderer
    {
        $this->id = $id;

        return $this;
    }

    public function getId(): string
    {
        return $this->id;
    }
}
