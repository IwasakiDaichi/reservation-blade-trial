<?php

namespace App\Services;

use App\Models\Reservation;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;
use RuntimeException;
use setasign\Fpdi\Fpdi;

class ReservationFormPdf
{
    private const PAGE_WIDTH  = 215.9;
    private const PAGE_HEIGHT = 330.2;
    private const MARGIN      = 14.0;
    private const CW          = 187.9; // content width

    public function generate(Reservation $reservation): string
    {
        $reservation->loadMissing('user');

        $pdf = new Fpdi('P', 'mm', [self::PAGE_WIDTH, self::PAGE_HEIGHT]);
        $pdf->SetMargins(self::MARGIN, 12, self::MARGIN);
        $pdf->SetAutoPageBreak(true, 14);
        $pdf->SetCompression(false);
        $pdf->SetTitle('CPDO Venue Reservation Form');

        $pdf->AddPage();
        $this->drawPage1($pdf, $reservation);

        $pdf->AddPage();
        $this->drawPage2($pdf, $reservation);

        return $pdf->Output('S');
    }

    // ─── PAGE 1 ──────────────────────────────────────────────────────────────

    private function drawPage1(Fpdi $pdf, Reservation $reservation): void
    {
        $this->drawFormHeader($pdf);
        $this->drawApplicantSection($pdf, $reservation);
        $this->drawVenueSection($pdf, $reservation);
        $this->drawRequirementsSection($pdf, $reservation);
        $this->drawTermsAndConditions($pdf);
        $this->drawPage1Signatures($pdf, $reservation);
    }

    private function drawFormHeader(Fpdi $pdf): void
    {
        $pdf->SetTextColor(0, 0, 0);

        $logoPath = public_path('University Of Baguio.PNG');
        $logoWidth = 42;
        $logoX = self::MARGIN + ((self::CW - $logoWidth) / 2);
        $pdf->Image($logoPath, $logoX, $pdf->GetY(), $logoWidth);
        $pdf->Ln(12);

        $pdf->SetFont('Arial', 'B', 10);
        $pdf->Cell(0, 5, 'CAMPUS PLANNING AND DEVELOPMENT OFFICE', 0, 1, 'C');
        $pdf->SetDrawColor(0, 0, 0);
        $pdf->Line(self::MARGIN, $pdf->GetY(), self::PAGE_WIDTH - self::MARGIN, $pdf->GetY());
        $pdf->Ln(2);

        $pdf->SetFont('Arial', 'B', 14);
        $pdf->Cell(0, 7, 'RESERVATION FORM', 0, 1, 'C');
        $pdf->SetFont('Arial', 'B', 10);
        $pdf->Cell(0, 5, 'Venue/Facilities', 0, 1, 'C');
        $pdf->Ln(2);
    }

    private function drawApplicantSection(Fpdi $pdf, Reservation $reservation): void
    {
        $lw = self::CW * 0.55; // left column width
        $rw = self::CW * 0.45; // right column width

        $this->drawSideBySideField($pdf,
            'Name of Applicant', $reservation->user->name, $lw,
            'Department', $reservation->department, $rw
        );
        $this->drawSideBySideField($pdf,
            'Date Applied', $reservation->created_at?->format('m/d/Y') ?? '', $lw,
            'Local No.', $reservation->mobile_number ?? '', $rw
        );
        $this->drawFullWidthField($pdf, 'Event Title/Purpose', $reservation->purpose ?? '');
    }

    private function drawVenueSection(Fpdi $pdf, Reservation $reservation): void
    {
        $pdf->SetFont('Arial', 'B', 8.5);
        $pdf->Cell(0, 5, 'Venue (Capacity)', 0, 1);

        $venueOptions = [
            ['Campus Gym',       'UB Cardinals Gym (2,000-2,500)'],
            ['RCB Dome',         'RCB Dome (300 armchairs)'],
            ['FGB Hall',         'FGB Hall (150-200)'],
            ['Centenial Hall',   'Centenial Hall (100-150)'],
            ['AMS Hall',         'AMS Hall (120 armchairs)'],
            ['Legacy',           'Legacy-multipurpose Hall (150-300)'],
            ['UB Student Center','UB Student Center (100)'],
            ['Alumni AVR',       'Alumni AVR (55)'],
            ['Centennial AVR',   'Centennial AVR (35)'],
        ];

        $storedVenue   = $reservation->venue ?? '';
        $knownVenues   = array_column($venueOptions, 0);
        $isOtherVenue  = $storedVenue === 'Other' || (!in_array($storedVenue, $knownVenues, true) && $storedVenue !== '');

        // Draw 3-column checkbox grid
        $colW = self::CW / 3;
        foreach (array_chunk($venueOptions, 3) as $row) {
            $y = $pdf->GetY();
            foreach ($row as $col => [$value, $label]) {
                $x       = self::MARGIN + ($col * $colW);
                $checked = strtolower($storedVenue) === strtolower($value);

                // Checkbox square
                $pdf->SetDrawColor(0, 0, 0);
                $pdf->Rect($x + 1, $y + 0.6, 3.2, 3.2);
                if ($checked) {
                    $pdf->SetFont('Arial', 'B', 7);
                    $pdf->Text($x + 1.5, $y + 3.4, 'X');
                }
                $pdf->SetFont('Arial', '', 7.5);
                $pdf->SetXY($x + 5.5, $y);
                $pdf->Cell($colW - 6, 4.5, $this->encode($label), 0, 0);
            }
            $pdf->SetY($y + 4.8);
        }

        // Others row
        $otherLabel   = 'Others (pls. specify)';
        $otherValue   = $reservation->other_venue ?? ($isOtherVenue ? $storedVenue : '');
        $y            = $pdf->GetY() + 0.5;
        $xOther       = self::MARGIN + 1;

        // Checkbox for Others
        $pdf->SetDrawColor(0, 0, 0);
        $pdf->Rect($xOther, $y + 0.6, 3.2, 3.2);
        if ($isOtherVenue) {
            $pdf->SetFont('Arial', 'B', 7);
            $pdf->Text($xOther + 0.5, $y + 3.4, 'X');
        }
        $pdf->SetFont('Arial', '', 7.5);
        $pdf->SetXY($xOther + 5, $y);
        $pdf->Cell(40, 4.5, $this->encode($otherLabel), 0, 0);

        // Value line
        $vx = $xOther + 5 + $pdf->GetStringWidth($this->encode($otherLabel)) + 2;
        $pdf->SetFont('Arial', '', 8);
        $pdf->SetXY($vx, $y);
        $pdf->Cell(50, 4.5, $this->encode($otherValue), 0, 0);
        $lineY = $y + 4.5;
        $pdf->SetDrawColor(0, 0, 0);
        $pdf->Line($vx, $lineY, $vx + 50, $lineY);
        $pdf->Ln(6);

        // Reserved Date(s) and Time
        $lw = self::CW * 0.55;
        $rw = self::CW * 0.45;

        $dateRange = $reservation->start_date?->format('m/d/Y')
            ?? $reservation->reserved_date?->format('m/d/Y')
            ?? '';
        if ($reservation->start_date && $reservation->end_date && !$reservation->start_date->isSameDay($reservation->end_date)) {
            $dateRange .= ' - ' . $reservation->end_date->format('m/d/Y');
        }

        $timeRange = $reservation->start_time && $reservation->end_time
            ? Carbon::parse($reservation->start_time)->format('g:i A') . ' - ' . Carbon::parse($reservation->end_time)->format('g:i A')
            : ($reservation->time ?? '');

        $this->drawSideBySideField($pdf, 'Reserved Date(s)', $dateRange, $lw, 'Time', $timeRange, $rw, true);

        // (month/date/year/day) hint
        $pdf->SetFont('Arial', '', 7);
        $pdf->Cell(0, 4, '(month/date/year/day)', 0, 1);
        $pdf->Ln(1);
    }

    private function drawRequirementsSection(Fpdi $pdf, Reservation $reservation): void
{
    $pdf->SetFont('Arial', 'B', 8.5);
    $pdf->Cell(0, 5, 'Requirements:', 0, 1);

    $lx    = self::MARGIN;
    $rx    = self::MARGIN + self::CW / 2;
    $colW  = self::CW / 2;
    $startY = $pdf->GetY();

    // ── LEFT: Equipment ──
    $y = $startY;

    $pdf->SetFont('Arial', 'B', 8);
    $pdf->SetXY($lx, $y);
    $pdf->Cell($colW, 4.5, 'Equipment:', 0, 0);
    $y += 4.5;

    $equip = [
        ['Sound System',   $reservation->sound_system_details ?: ($reservation->sound_system ? 'Yes' : '')],
        ['Microphone',     $reservation->microphone_details   ?: ($reservation->microphone   ? 'Yes' : '')],
        ['LED Video Wall', $reservation->video_wall_details   ?: ($reservation->video_wall   ? 'Yes' : '')],
    ];
    foreach ($equip as [$label, $val]) {
        $pdf->SetFont('Arial', '', 8);
        $pdf->SetXY($lx + 3, $y);
        $labelText = $label . ':';
        $lw2 = $pdf->GetStringWidth($this->encode($labelText)) + 2;
        $pdf->Cell($lw2, 5, $this->encode($labelText), 0, 0);
        $vx = $lx + 3 + $lw2;
        $pdf->SetXY($vx, $y);
        $pdf->Cell($colW - 3 - $lw2 - 2, 5, $this->encode($val), 0, 0);
        $pdf->SetDrawColor(0, 0, 0);
        $pdf->Line($vx, $y + 5, $lx + $colW - 2, $y + 5);
        $y += 5.5;
    }

    $pdf->SetFont('Arial', 'I', 7);
    $pdf->SetXY($lx + 3, $y);
    $pdf->Cell($colW - 3, 4, '(gym and centennial hall only)', 0, 0);
    $y += 4.5;

    $pdf->SetFont('Arial', 'B', 8);
    $pdf->SetXY($lx, $y);
    $pdf->Cell($colW, 4.5, 'Others, please specify:', 0, 0);
    $y += 4.5;

    $othersText = trim($reservation->other_requirements ?? '');

$otherLines = $othersText !== ''
    ? preg_split('/\r\n|\r|\n/', $othersText)
    : [''];

foreach ($otherLines as $i => $line) {
    $pdf->SetFont('Arial', '', 8);

    $wrappedLines = $this->wrapText(
        $pdf,
        $this->encode($line),
        $colW - 8,
        8
    );

    if (empty($wrappedLines)) {
        $wrappedLines = [''];
    }

    $pdf->SetXY($lx + 1, $y);
    $pdf->Cell(5, 4.5, ($i + 1) . '.', 0, 0);

    foreach ($wrappedLines as $wrappedLine) {
        $pdf->SetXY($lx + 6, $y);
        $pdf->Cell($colW - 8, 4.5, $wrappedLine, 0, 0);

        $pdf->Line(
            $lx + 6,
            $y + 4.5,
            $lx + $colW - 2,
            $y + 4.5
        );

        $y += 4.5;
    }

    $y += 1;
}

    $leftEndY = $y;

    // ── RIGHT: Physical Set-Up ──
    $y = $startY;

    $pdf->SetFont('Arial', 'B', 8);
    $pdf->SetXY($rx, $y);
    $pdf->Cell($colW, 4.5, 'Physical Set-Up:', 0, 0);
    $y += 4.5;

    $physical = [
        ['Flags (UB and Phils.)',         $reservation->flags            ? 'Yes' : 'No'],
        ['Podium',                        $reservation->podium           ? 'Yes' : 'No'],
        ['Number of Platform',            $reservation->platform         ? 'Yes' : 'No'],
        ['Number of Tables',              (string) ($reservation->tables ?? '')],
        ['Number of Chairs',              (string) ($reservation->chairs ?? '')],
        ['Removal of Rings-Goal (GYM)',   $reservation->removal_of_rings ? 'Yes' : 'No'],
    ];
    foreach ($physical as $i => [$label, $val]) {
        $pdf->SetFont('Arial', '', 8);
        $pdf->SetXY($rx + 1, $y);
        $pdf->Cell(5, 5, ($i + 1) . '.', 0, 0);
        $labelText = $label . ':';
        $lw2 = $pdf->GetStringWidth($this->encode($labelText)) + 1;
        $pdf->SetXY($rx + 6, $y);
        $pdf->Cell($lw2, 5, $this->encode($labelText), 0, 0);
        $vx = $rx + 6 + $lw2;
        $pdf->SetXY($vx, $y);
        $pdf->Cell($colW - 6 - $lw2 - 2, 5, $this->encode($val), 0, 0);
        $pdf->SetDrawColor(0, 0, 0);
        $pdf->Line($vx, $y + 5, $rx + $colW - 2, $y + 5);
        $y += 5.5;
    }

    $rightEndY = $y;

    $pdf->SetY(max($leftEndY, $rightEndY) + 1);
    }

    private function drawTermsAndConditions(Fpdi $pdf): void
    {
        $pdf->SetFont('Arial', 'B', 8.5);
        $pdf->Cell(0, 5, 'Terms and Conditions:', 0, 1);

        $pdf->SetFont('Arial', 'BU', 7.5);
        $pdf->Cell(0, 4, 'University/School Events', 0, 1);

        $uniTerms = [
            'All venue requirements. Including Reservation Form and setup layout must be finalized and submitted at least two (2) weeks before the scheduled event.',
            'All activities organized by students and faculty members must be endorsed by the School Dean.',
            'Student activities shall not be held on SUNDAYS or HOLIDAYS.',
            'All activities organized by university offices must be endorsed by the respective Director.',
            'CPDO must be informed of any postponement or cancellation at least three (3) days before the reserved date to allow the venue to be made available for other University activities.',
            'Any damage to university property shall be charged to the student or personnel responsible.',
        ];

        $this->drawNumberedList($pdf, $uniTerms, 7.0, [
            [0, 'least two (2) weeks'],
            [4, 'three (3) days'],
        ]);

        $pdf->SetFont('Arial', 'BU', 7.5);
        $pdf->Cell(0, 4, 'External Organization', 0, 1);

        $extTerms = [
            'All venue requirements must be finalized at least ONE (1) MONTH before the scheduled activity.',
            'Request from government agencies and external private institutions or organizations to conduct activities on campus are subject to the President\'s approval.',
            'A fifty percent (50%) down payment must be made upon submission of the Reservation Form. The remaining balance and the bond fee (if applicable) must be paid at least two (2) days before the event. Otherwise, reservation shall be cancelled. All payments must be made officially through the Cashier\'s office.',
            'CPDO must be informed of any postponement or cancellation. If payment have been made, a refund will be processed.',
            'The set up of selling booths and exhibits by the concessionaries is subject to the President\'s approval.',
            'Pre-security briefing and debriefing must be conducted by the assigned personnel in charge to trace out security measures to be improved before and after the event.',
            'A clearance must be obtained from CPDO before the bond fee can be claimed. Any additional charges and costs for property damage shall be deducted from the bond.',
        ];

        $this->drawNumberedList($pdf, $extTerms, 7.0, [
            [0, 'ONE (1) MONTH'],
        ]);

        // Italic acknowledgement paragraph
        $pdf->SetFont('Arial', 'I', 7.5);
        $italic = 'I hereby accept and agree to abide by all the terms and conditions specified herein, and further acknowledge that failure to abide by all such terms and conditions may result to the disapproval of my reservation and future reservation.';
        $pdf->MultiCell(self::CW, 3.5, $this->encode($italic), 0, 'C');
        $pdf->Ln(2);
    }

    /**
     * Draws a numbered list. $boldPhrases is array of [itemIndex, phrase] to bold inline.
     * Since FPDI/FPDF doesn't support inline bold, we render entire item in one style.
     * Bold items get 'B' font, others get ''.
     */
    private function drawNumberedList(Fpdi $pdf, array $items, float $fontSize, array $boldPhrases = []): void
    {
        $boldItems = array_column($boldPhrases, 0);
        foreach ($items as $i => $text) {
            $y     = $pdf->GetY();
            $lines = $this->wrapText($pdf, $this->encode($text), self::CW - 10, $fontSize);
            $h     = count($lines) * 3.2;

            $pdf->SetFont('Arial', '', $fontSize);
            $pdf->SetXY(self::MARGIN, $y);
            $pdf->Cell(7, 3.2, ($i + 1) . '.', 0, 0, 'R');

            foreach ($lines as $li => $line) {
                $pdf->SetXY(self::MARGIN + 8, $y + ($li * 3.2));
                $pdf->Cell(self::CW - 8, 3.2, $line, 0, 0);
            }
            $pdf->SetY($y + $h + 0.5);
        }
    }

    private function drawPage1Signatures(Fpdi $pdf, Reservation $reservation): void
    {
        $pdf->Ln(2);
        $lw = self::CW * 0.5;
        $rw = self::CW * 0.5;
        $lx = self::MARGIN;
        $rx = self::MARGIN + $lw;
        $sigH = 16;

        // Row 1: Applicant (left) | Security Personnel (right)
        $y = $pdf->GetY();
        $this->drawSignatureCell(
            $pdf,
            $lx,
            $y,
            $lw,
            $sigH,
            'Signature over printed name of Applicant',
            $reservation->applicant_signature_path,
            $reservation->user?->name
        );
        $this->drawSignatureCell($pdf, $rx, $y, $rw, $sigH, 'Signature over printed name of Security personnel', $reservation->security_signature_path);
        $pdf->SetY($y + $sigH + 1.5);

        // Row 2: Endorsed by (left) | Reservation Officer (right)
        $y = $pdf->GetY();
        $endorsedH = 14;

        // "Endorsed by:" label (left)
        $pdf->SetFont('Arial', 'B', 8);
        $pdf->SetXY($lx, $y);
        $pdf->Cell($lw, 5, 'Endorsed by:', 0, 0);

        // Reservation Officer (right)
        $pdf->SetXY($rx, $y);
        $pdf->Cell(28, 5, 'Reservation Officer:', 0, 0);
        $roLineX = $rx + 28;
        if ($reservation->reservation_officer_signature_path) {
            $this->addSignature($pdf, $reservation->reservation_officer_signature_path, $roLineX, $y, $rw - 28, 4);
        }
        $pdf->SetDrawColor(0, 0, 0);
        $pdf->Line($roLineX, $y + 4.5, $rx + $rw - 2, $y + 4.5);

        // Dean/Director signature (left)
        $y2 = $y + 5;
        if ($reservation->endorsement_signature_path) {
            $this->addSignature($pdf, $reservation->endorsement_signature_path, $lx, $y2, $lw - 4, 9);
        }
        $pdf->SetDrawColor(0, 0, 0);
        $pdf->Line($lx, $y2 + 9, $lx + $lw - 4, $y2 + 9);

        $pdf->SetFont('Arial', '', 7.5);
        $pdf->SetXY($lx, $y2 + 9.5);
        $pdf->Cell($lw - 4, 4, 'Dean/Director/Head of Office', 0, 0);

        // Approved / Disapproved + CPDO Director (right, row 2 lower)
        $y3 = $y + 5;
        $pdf->SetFont('Arial', '', 7.5);
        $pdf->SetXY($rx, $y3);
        $approvalMark = $reservation->status === 'approved' ? '[X] Approved' : '[ ] Approved';
        $pdf->Cell(28, 4, $approvalMark, 0, 0);
        $pdf->SetXY($rx + 32, $y3);
        $disapprovalMark = $reservation->status === 'rejected' ? '[X] Disapproved' : '[ ] Disapproved';
        $pdf->Cell($rw - 32, 4, $disapprovalMark, 0, 0);

        // CPDO Director signature
        $y4 = $y3 + 5;
        if ($reservation->approval_signature_path) {
            $this->addSignature($pdf, $reservation->approval_signature_path, $rx, $y4, $rw - 2, 9);
        }
        $pdf->Line($rx, $y4 + 9, $rx + $rw - 2, $y4 + 9);
        $pdf->SetFont('Arial', 'B', 7.5);
        $pdf->SetXY($rx, $y4 + 9.5);
        $pdf->Cell($rw - 2, 4, 'ENGR. BERNARD Z. ABAYA', 0, 0);
        $pdf->SetFont('Arial', '', 7);
        $pdf->SetXY($rx, $y4 + 13.5);
        $pdf->Cell($rw - 2, 3.5, 'Director, CPDO', 0, 0);

        $pdf->SetY($y4 + 18);

        // "Please furnish a copy..." note (left) | "Received by (CPDO):  CPDO-FO-006" (right)
        $yFinal = $pdf->GetY();
        $pdf->SetFont('Arial', 'B', 7);
        $pdf->SetXY($lx, $yFinal);
        $pdf->Cell($lw, 3.5, 'Please furnish a copy to the concerned offices (if services are needed)', 0, 0);
        $pdf->SetXY($rx, $yFinal);
        $pdf->Cell($rw, 3.5, 'Received by (CPDO):                        CPDO-FO-006', 0, 0, 'R');

        $pdf->SetFont('Arial', '', 7);
        $pdf->SetXY($lx, $yFinal + 3.5);
        $pdf->Cell(20, 3, 'Sound System', 0, 0);
        $pdf->SetXY($lx + 22, $yFinal + 3.5);
        $pdf->Cell(20, 3, 'Electrical', 0, 0);

        $pdf->SetXY($lx, $yFinal + 6.5);
        $pdf->Cell(20, 3, 'AVR', 0, 0);
        $pdf->SetXY($lx + 22, $yFinal + 6.5);
        $pdf->Cell(20, 3, 'General Services', 0, 0);

        $pdf->SetXY($lx, $yFinal + 9.5);
        $pdf->Cell(20, 3, 'Security', 0, 0);
    }

    // ─── PAGE 2 ──────────────────────────────────────────────────────────────

    private function drawPage2(Fpdi $pdf, Reservation $reservation): void
    {
        $pdf->SetFont('Arial', 'B', 10);
        $pdf->Cell(0, 6, 'THE FOLLOWING POLICIES MUST BE STRICTLY FOLLOWED', 0, 1, 'C');
        $pdf->Ln(2);

        foreach ($this->policies() as $i => $policy) {
            $lines = $this->wrapText($pdf, $this->encode($policy), self::CW - 10, 7.5);
            $h     = max(3.5, count($lines) * 3.5);
            $this->ensureSpace($pdf, $h + 1, $reservation, true);

            $y = $pdf->GetY();
            $pdf->SetFont('Arial', 'B', 7.5);
            $pdf->SetXY(self::MARGIN, $y);
            $pdf->Cell(8, 3.5, ($i + 1) . '.', 0, 0, 'R');

            $pdf->SetFont('Arial', '', 7.5);
            foreach ($lines as $li => $line) {
                $pdf->SetXY(self::MARGIN + 10, $y + ($li * 3.5));
                $pdf->Cell(self::CW - 10, 3.5, $line, 0, 0);
            }
            $pdf->SetY($y + $h + 0.8);
        }

        $this->ensureSpace($pdf, 20, $reservation, true);
        $pdf->Ln(2);
        $pdf->SetFont('Arial', 'B', 8);
        $pdf->MultiCell(self::CW, 4, $this->encode('Signing below implies that you agree and are knowledgeable of all the policies and requirements thereof.'), 0, 'C');
        $pdf->Ln(2);

        // Conforme signature
        $y    = $pdf->GetY();
        $lw   = self::CW * 0.5;
        $lx   = self::MARGIN;
        $sigH = 18;

        if ($reservation->applicant_signature_path) {
            $this->addSignature($pdf, $reservation->applicant_signature_path, $lx, $y, $lw - 4, 12);
        }
        $pdf->SetDrawColor(0, 0, 0);
        $pdf->Line($lx, $y + 12, $lx + $lw - 4, $y + 12);
        $pdf->SetFont('Arial', 'B', 8);
        $pdf->SetXY($lx, $y + 12.5);
        $pdf->Cell($lw, 4, 'Conforme:', 0, 0);
        $pdf->SetFont('Arial', '', 7.5);
        $pdf->SetXY($lx, $y + 16.5);
        $pdf->Cell($lw, 3.5, 'Signature of Applicant', 0, 0);
    }

    // ─── FIELD HELPERS ───────────────────────────────────────────────────────

    /**
     * Two side-by-side underline fields.
     */
    private function drawSideBySideField(
        Fpdi $pdf,
        string $lLabel, string $lVal, float $lw,
        string $rLabel, string $rVal, float $rw,
        bool $bold = false
    ): void {
        $y  = $pdf->GetY();
        $lx = self::MARGIN;
        $rx = self::MARGIN + $lw;

        $pdf->SetFont('Arial', 'B', 8);
        $pdf->SetXY($lx, $y);
        $pdf->Cell($lw, 5, $this->encode($lLabel . ':'), 0, 0);

        $pdf->SetXY($rx, $y);
        $pdf->Cell($rw, 5, $this->encode($rLabel . ':'), 0, 0);

        // Values
        $pdf->SetFont('Arial', '', 8.5);
        $lLabelW = $pdf->GetStringWidth($this->encode($lLabel . ':  '));
        $rLabelW = $pdf->GetStringWidth($this->encode($rLabel . ':  '));

        $lValX = $lx + $lLabelW;
        $rValX = $rx + $rLabelW;

        $pdf->SetXY($lValX, $y);
        $pdf->Cell($lw - $lLabelW - 1, 5, $this->encode($lVal), 0, 0);
        $pdf->Line($lValX, $y + 5, $lx + $lw - 2, $y + 5);

        $pdf->SetXY($rValX, $y);
        $pdf->Cell($rw - $rLabelW - 1, 5, $this->encode($rVal), 0, 0);
        $pdf->Line($rValX, $y + 5, $rx + $rw - 2, $y + 5);

        $pdf->Ln(6.5);
    }

    /**
     * Full-width underline field (label on its own line, value on the next with underline).
     */
    private function drawFullWidthField(Fpdi $pdf, string $label, string $value): void
    {
        $y  = $pdf->GetY();
        $lx = self::MARGIN;

        $pdf->SetFont('Arial', 'B', 8);
        $pdf->SetXY($lx, $y);
        $labelW = $pdf->GetStringWidth($this->encode($label . ':  '));
        $pdf->Cell($labelW, 5, $this->encode($label . ':'), 0, 0);

        $pdf->SetFont('Arial', '', 8.5);
        $pdf->SetXY($lx + $labelW, $y);
        $pdf->Cell(self::CW - $labelW - 1, 5, $this->encode($value), 0, 0);
        $pdf->Line($lx + $labelW, $y + 5, $lx + self::CW - 1, $y + 5);

        $pdf->Ln(6.5);
    }

    /**
     * Inline field: label: __ value __ (underline) for requirements rows.
     */
    private function drawInlineField(Fpdi $pdf, float $x, float $y, float $width, string $label, string $value): void
    {
        $pdf->SetFont('Arial', '', 7.5);
        $pdf->SetXY($x, $y);
        $labelW = $pdf->GetStringWidth($this->encode($label . ':  '));
        $pdf->Cell($labelW, 5, $this->encode($label . ':'), 0, 0);

        $valX = $x + $labelW;
        $pdf->SetFont('Arial', '', 8);
        $pdf->SetXY($valX, $y);
        $pdf->Cell($width - $labelW - 2, 5, $this->encode($value), 0, 0);
        $pdf->SetDrawColor(0, 0, 0);
        $pdf->Line($valX, $y + 5, $x + $width - 2, $y + 5);
    }

   private function drawSignatureCell(
        Fpdi $pdf,
        float $x, float $y, float $width, float $height,
        string $label, ?string $path,
        ?string $printedName = null
    ): void {
        $pdf->SetFont('Arial', '', 7.5);
        $pdf->SetXY($x, $y);

        // Signature image
        if ($path) {
            $this->addSignature($pdf, $path, $x, $y + 1, $width - 4, $height - 9);
        }

        // Signature line
        $pdf->SetDrawColor(0, 0, 0);
        $pdf->Line($x + 1, $y + $height - 7, $x + $width - 2, $y + $height - 7);

        // Printed name
        if ($printedName) {
            $pdf->SetFont('Arial', 'B', 7.5);
            $pdf->SetXY($x, $y + $height - 6.5);
            $pdf->Cell(
                $width - 2,
                3,
                $this->encode($printedName),
                0,
                0,
                'C'
            );
        }

        // Signature label
        $pdf->SetFont('Arial', '', 7);
        $pdf->SetXY($x, $y + $height - 3.5);
        $pdf->Cell($width - 2, 3, $this->encode($label), 0, 0, 'C');
    }

    // ─── SHARED UTILITIES ────────────────────────────────────────────────────

    private function addSignature(Fpdi $pdf, string $path, float $x, float $y, float $maxWidth, float $maxHeight): void
    {
        $absolutePath = Storage::disk('local')->path($path);
        if (!is_file($absolutePath)) {
            throw new RuntimeException('A saved reservation signature file is missing.');
        }

        $dimensions = getimagesize($absolutePath);
        if ($dimensions === false || $dimensions[0] <= 0 || $dimensions[1] <= 0) {
            throw new RuntimeException('A saved reservation signature image is invalid.');
        }

        $scale  = min($maxWidth / $dimensions[0], $maxHeight / $dimensions[1]);
        $width  = $dimensions[0] * $scale;
        $height = $dimensions[1] * $scale;

        $pdf->Image($absolutePath, $x + (($maxWidth - $width) / 2), $y + (($maxHeight - $height) / 2), $width, $height);
    }

    private function ensureSpace(Fpdi $pdf, float $height, ?Reservation $reservation, bool $continued = false): void
    {
        if ($pdf->GetY() + $height <= self::PAGE_HEIGHT - 14) {
            return;
        }

        $pdf->AddPage();
        if ($reservation && $continued) {
            // No header on overflow pages for page 2
        }
    }

    private function wrapText(Fpdi $pdf, string $text, float $width, float $fontSize): array
    {
        $pdf->SetFont('Arial', '', $fontSize);
        $paragraphs = preg_split('/\r\n|\r|\n/', $text) ?: [''];
        $lines = [];

        foreach ($paragraphs as $paragraph) {
            $words = preg_split('/\s+/', trim($paragraph), -1, PREG_SPLIT_NO_EMPTY) ?: [];
            if ($words === []) {
                $lines[] = '';
                continue;
            }

            $line = '';
            foreach ($words as $word) {
                if ($pdf->GetStringWidth($word) > $width) {
                    if ($line !== '') {
                        $lines[] = $line;
                        $line    = '';
                    }
                    foreach (str_split($word) as $ch) {
                        if ($line !== '' && $pdf->GetStringWidth($line . $ch) > $width) {
                            $lines[] = $line;
                            $line    = '';
                        }
                        $line .= $ch;
                    }
                    continue;
                }

                $candidate = $line === '' ? $word : $line . ' ' . $word;
                if ($line !== '' && $pdf->GetStringWidth($candidate) > $width) {
                    $lines[] = $line;
                    $line    = $word;
                } else {
                    $line = $candidate;
                }
            }

            if ($line !== '') {
                $lines[] = $line;
            }
        }

        return $lines ?: ['-'];
    }

    private function encode(string $text): string
    {
        $encoded = iconv('UTF-8', 'windows-1252//TRANSLIT', $text);
        if ($encoded === false) {
            throw new RuntimeException('A reservation field could not be converted for printing.');
        }
        return $encoded;
    }

    private function policies(): array
    {
        return [
            'NO RESERVATION FORM. NO UTILIZATION POLICY.',
            'The approved seating capacity of the venue must be observed at all times for safety purposes.',
            'Energy and water must be used responsibly and conserved.',
            'The installation of heavy structures on venue floor is prohibited.',
            'The use of open flames and flammable materials is prohibited.',
            'Activities must end by 9:00 p.m. in accordance with Baguio City Ordinance No. 44, Series of 2018 ("Silent Night").',
            'Furniture and equipment must not be removed from or relocated within the venue without prior authorization.',
            'The use of double-sided tape, paint, or spray paint on venue surfaces is prohibited.',
            'Decorations must not obstruct or cover safety devices and features, including fire extinguishers, exit signs, piping, and fire alarm pull stations.',
            'The use of materials such as Styrofoam, bamboo poles or sticks, coconut husks, banana leaves, wood, stones, sand, and similar materials should be avoided. Decorations must not damage curtains, walls, or flooring.',
            'The prescribed standard size for tarpaulins must be observed. Tarpaulins that do not comply with the standard will not be permitted.',
            'Posters, display booths, and other marketing or advertising materials are not permitted without prior coordination.',
            'Chewing of betel nut is discouraged on university premises, in accordance with the applicable Anti-Moma City Ordinance.',
            'Smoking and vaping are prohibited.',
            'Support groups, including Janitorial, Maintenance, AVR, Sound System, MAP, MIS, and Security personnel, are not responsible for venue setup, decorations, or beautification.',
            'The applicant must coordinate any required physical setup with the appropriate technical support group at least two (2) days before the activity.',
            'Visitors must obtain security clearance from the Security Department.',
            'Do not leave your valuables unattended. Support committees are not responsible for safeguarding materials or personal belongings brought to or left at the venue.',
            'Event organizers must provide their own seat covers, tablecloths, linens, and similar items.',
            'Available resources are limited. Requests for materials and equipment are subject to availability.',
            'All event participants must comply with the minimum health standards implemented on the University campus.',
            'The clean-as-you-go (CLAYGO) practice must be followed. Participants must dispose of their garbage properly.',
            'Loitering inside the venue is strictly prohibited.',
            'Only athletes are allowed during trainings and only enrolled students are allowed during PE classes inside the gym.',
            'Any stage extension must be coordinated with CPDO in advance.',
            'Shoes with heels, steel toes, combat boots, leather soles, and similar footwear are not allowed on the open floor area. Rubber-soled or gum-soled shoes must be worn.',
            'Food is prohibited unless the activity has been properly coordinated with CPDO. Schools and departments are encouraged to serve food in packed form, except for institutional events.',
            'The coordinator is responsible for overall communication and for overseeing the implementation of health and safety protocols.',
            'The reservation shall be considered confirmed only upon approval by the authorized office. Requesting offices shall verify the status of their reservation and follow up on pending requests if necessary.',
        ];
    }
}
