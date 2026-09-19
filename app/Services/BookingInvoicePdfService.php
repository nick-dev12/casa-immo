<?php

declare(strict_types=1);

namespace App\Services;

use App\Core\View;
use Dompdf\Dompdf;
use Dompdf\Options;

final class BookingInvoicePdfService
{
    public function __construct(
        private readonly BookingInvoiceService $invoiceService = new BookingInvoiceService(),
    ) {
    }

    /**
     * @param array<string, mixed> $booking
     */
    public function filename(array $booking): string
    {
        $invoice = $this->invoiceService->build($booking);
        $number = preg_replace('/[^A-Za-z0-9\-]/', '', (string) ($invoice['invoice_number'] ?? 'facture'));

        return ($number !== '' ? $number : 'facture') . '.pdf';
    }

    /**
     * @param array<string, mixed> $invoice
     */
    public function generate(array $invoice): string
    {
        $html = View::render('invoices/booking-pdf', [
            'invoice' => $invoice,
        ], null);

        $options = new Options();
        $options->set('isRemoteEnabled', false);
        $options->set('isHtml5ParserEnabled', true);
        $options->set('defaultFont', 'DejaVu Sans');

        $dompdf = new Dompdf($options);
        $dompdf->loadHtml($html, 'UTF-8');
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();

        return $dompdf->output();
    }
}
