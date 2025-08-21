<?php
class SimplePDF {
    private $content = '';
    private $x = 20;
    private $y = 20;
    private $font = 'Arial';
    private $fontSize = 12;
    private $fontStyle = '';
    
    public function addPage() {
        $this->content .= "%PDF-1.4\n";
        $this->content .= "1 0 obj\n<< /Type /Catalog /Pages 2 0 R >>\nendobj\n";
        return $this;
    }
    
    public function setFont($font, $style = '', $size = 12) {
        $this->font = $font;
        $this->fontStyle = $style;
        $this->fontSize = $size;
        return $this;
    }
    
    public function cell($w, $h, $text, $border = 0, $ln = 0, $align = 'L') {
        // Simple text output - in real implementation would handle positioning
        $this->content .= $text . "\n";
        if ($ln) $this->y += $h;
        return $this;
    }
    
    public function ln($h = null) {
        $this->y += $h ?: $this->fontSize;
        return $this;
    }
    
    public function output($dest = 'I', $name = 'doc.pdf') {
        if ($dest === 'D') {
            header('Content-Type: application/pdf');
            header('Content-Disposition: attachment; filename="' . $name . '"');
        }
        
        // Simplified PDF output - in production use a proper PDF library
        echo $this->generateSimplePDF();
    }
    
    private function generateSimplePDF() {
        // This is a very basic PDF structure - use TCPDF or FPDF for production
        $pdf = "%PDF-1.4\n";
        $pdf .= "1 0 obj\n<< /Type /Catalog /Pages 2 0 R >>\nendobj\n";
        $pdf .= "2 0 obj\n<< /Type /Pages /Kids [3 0 R] /Count 1 >>\nendobj\n";
        $pdf .= "3 0 obj\n<< /Type /Page /Parent 2 0 R /MediaBox [0 0 612 792] /Contents 4 0 R >>\nendobj\n";
        $pdf .= "4 0 obj\n<< /Length " . strlen($this->content) . " >>\nstream\n";
        $pdf .= "BT /F1 12 Tf 50 750 Td (" . str_replace("\n", ") Tj 0 -15 Td (", $this->content) . ") Tj ET\n";
        $pdf .= "endstream\nendobj\n";
        $pdf .= "xref\n0 5\n0000000000 65535 f \n0000000009 00000 n \n0000000058 00000 n \n0000000115 00000 n \n0000000206 00000 n \n";
        $pdf .= "trailer\n<< /Size 5 /Root 1 0 R >>\nstartxref\n" . strlen($pdf) . "\n%%EOF";
        
        return $pdf;
    }
}
?>