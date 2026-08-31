<?php

namespace App\Services;

use ZipArchive;

/**
 * ExcelBuilder — Generator file .xlsx murni tanpa library eksternal.
 * Menggunakan format Office Open XML (SpreadsheetML / OOXML).
 */
class ExcelBuilder
{
    private array $sheets = [];
    private string $title;

    public function __construct(string $title = 'Laporan LOFBI')
    {
        $this->title = $title;
    }

    /**
     * Tambah sheet baru. $rows adalah array of arrays.
     * $headings adalah header baris pertama (bold, background).
     */
    public function addSheet(string $name, array $headings, array $rows): static
    {
        $this->sheets[] = compact('name', 'headings', 'rows');
        return $this;
    }

    /**
     * Generate dan kirim file .xlsx sebagai HTTP download response.
     */
    public function download(string $filename): \Symfony\Component\HttpFoundation\BinaryFileResponse
    {
        $tmpPath = tempnam(sys_get_temp_dir(), 'xlsx_') . '.xlsx';
        $this->write($tmpPath);

        return response()->download($tmpPath, $filename, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ])->deleteFileAfterSend(true);
    }

    private function write(string $path): void
    {
        $zip = new ZipArchive();
        $zip->open($path, ZipArchive::CREATE | ZipArchive::OVERWRITE);

        $sheetCount = count($this->sheets);
        $sheetNames = array_column($this->sheets, 'name');

        // [Content_Types].xml
        $zip->addFromString('[Content_Types].xml', $this->contentTypes($sheetCount));

        // _rels/.rels
        $zip->addFromString('_rels/.rels', $this->rels());

        // docProps/app.xml
        $zip->addFromString('docProps/app.xml', $this->docPropsApp($sheetNames));

        // docProps/core.xml
        $zip->addFromString('docProps/core.xml', $this->docPropsCore());

        // xl/workbook.xml
        $zip->addFromString('xl/workbook.xml', $this->workbook($sheetNames));

        // xl/_rels/workbook.xml.rels
        $zip->addFromString('xl/_rels/workbook.xml.rels', $this->workbookRels($sheetCount));

        // xl/styles.xml
        $zip->addFromString('xl/styles.xml', $this->styles());

        // xl/sharedStrings.xml  — tidak dipakai; semua inline string
        foreach ($this->sheets as $idx => $sheet) {
            $sheetNum = $idx + 1;
            $zip->addFromString("xl/worksheets/sheet{$sheetNum}.xml",
                $this->worksheet($sheet['headings'], $sheet['rows']));
        }

        $zip->close();
    }

    // ── Helpers ──────────────────────────────────────────────────────────────

    private function cell(string $col, int $row, mixed $value, bool $bold = false, bool $isHeader = false): string
    {
        $ref = $col . $row;
        $styleIdx = $isHeader ? '1' : (is_numeric($value) ? '2' : '0');

        if (is_numeric($value) && !$isHeader) {
            return "<c r=\"{$ref}\" s=\"{$styleIdx}\" t=\"n\"><v>{$value}</v></c>";
        }

        $escaped = htmlspecialchars((string)$value, ENT_XML1, 'UTF-8');
        return "<c r=\"{$ref}\" s=\"{$styleIdx}\" t=\"inlineStr\"><is><t>{$escaped}</t></is></c>";
    }

    private function columnLetter(int $index): string
    {
        $letter = '';
        while ($index >= 0) {
            $letter = chr(65 + ($index % 26)) . $letter;
            $index = intdiv($index, 26) - 1;
        }
        return $letter;
    }

    private function worksheet(array $headings, array $rows): string
    {
        $xml = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>' . "\n";
        $xml .= '<worksheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main">';
        $xml .= '<sheetView workbookViewId="0"><selection/></sheetView>';
        $xml .= '<sheetData>';

        // Header row
        $xml .= '<row r="1">';
        foreach ($headings as $colIdx => $heading) {
            $col = $this->columnLetter($colIdx);
            $xml .= $this->cell($col, 1, $heading, true, true);
        }
        $xml .= '</row>';

        // Data rows
        foreach ($rows as $rowIdx => $row) {
            $rowNum = $rowIdx + 2;
            $xml .= "<row r=\"{$rowNum}\">";
            $values = array_values((array) $row);
            foreach ($values as $colIdx => $value) {
                $col = $this->columnLetter($colIdx);
                $xml .= $this->cell($col, $rowNum, $value);
            }
            $xml .= '</row>';
        }

        $xml .= '</sheetData></worksheet>';
        return $xml;
    }

    private function contentTypes(int $sheetCount): string
    {
        $sheets = '';
        for ($i = 1; $i <= $sheetCount; $i++) {
            $sheets .= "<Override PartName=\"/xl/worksheets/sheet{$i}.xml\" ContentType=\"application/vnd.openxmlformats-officedocument.spreadsheetml.worksheet+xml\"/>";
        }
        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types">
  <Default Extension="rels" ContentType="application/vnd.openxmlformats-package.relationships+xml"/>
  <Default Extension="xml" ContentType="application/xml"/>
  <Override PartName="/xl/workbook.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet.main+xml"/>
  <Override PartName="/xl/styles.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.styles+xml"/>
  <Override PartName="/docProps/core.xml" ContentType="application/vnd.openxmlformats-package.core-properties+xml"/>
  <Override PartName="/docProps/app.xml" ContentType="application/vnd.openxmlformats-officedocument.extended-properties+xml"/>
  ' . $sheets . '
</Types>';
    }

    private function rels(): string
    {
        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">
  <Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/officeDocument" Target="xl/workbook.xml"/>
  <Relationship Id="rId2" Type="http://schemas.openxmlformats.org/package/2006/relationships/metadata/core-properties" Target="docProps/core.xml"/>
  <Relationship Id="rId3" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/extended-properties" Target="docProps/app.xml"/>
</Relationships>';
    }

    private function workbook(array $sheetNames): string
    {
        $sheets = '';
        foreach ($sheetNames as $idx => $name) {
            $sheetNum = $idx + 1;
            $sheets .= "<sheet name=\"" . htmlspecialchars($name, ENT_XML1) . "\" sheetId=\"{$sheetNum}\" r:id=\"rId{$sheetNum}\"/>";
        }
        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<workbook xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main"
          xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships">
  <sheets>' . $sheets . '</sheets>
</workbook>';
    }

    private function workbookRels(int $sheetCount): string
    {
        $rels = '';
        for ($i = 1; $i <= $sheetCount; $i++) {
            $rels .= "<Relationship Id=\"rId{$i}\" Type=\"http://schemas.openxmlformats.org/officeDocument/2006/relationships/worksheet\" Target=\"worksheets/sheet{$i}.xml\"/>";
        }
        $styleId = $sheetCount + 1;
        $rels .= "<Relationship Id=\"rId{$styleId}\" Type=\"http://schemas.openxmlformats.org/officeDocument/2006/relationships/styles\" Target=\"styles.xml\"/>";
        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">' . $rels . '</Relationships>';
    }

    private function styles(): string
    {
        // Style 0: normal text | Style 1: bold header with background | Style 2: number
        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<styleSheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main">
  <fonts>
    <font><sz val="11"/><name val="Calibri"/></font>
    <font><b/><sz val="11"/><name val="Calibri"/></font>
  </fonts>
  <fills>
    <fill><patternFill patternType="none"/></fill>
    <fill><patternFill patternType="gray125"/></fill>
    <fill><patternFill patternType="solid"><fgColor rgb="FFD9EAF7"/></patternFill></fill>
  </fills>
  <borders>
    <border><left/><right/><top/><bottom/><diagonal/></border>
    <border>
      <left style="thin"><color rgb="FF000000"/></left>
      <right style="thin"><color rgb="FF000000"/></right>
      <top style="thin"><color rgb="FF000000"/></top>
      <bottom style="thin"><color rgb="FF000000"/></bottom>
    </border>
  </borders>
  <cellStyleXfs count="1"><xf numFmtId="0" fontId="0" fillId="0" borderId="0"/></cellStyleXfs>
  <cellXfs>
    <xf numFmtId="0" fontId="0" fillId="0" borderId="1" xfId="0" applyBorder="1"/>
    <xf numFmtId="0" fontId="1" fillId="2" borderId="1" xfId="0" applyFont="1" applyFill="1" applyBorder="1"/>
    <xf numFmtId="4"  fontId="0" fillId="0" borderId="1" xfId="0" applyBorder="1" applyNumberFormat="1"/>
  </cellXfs>
</styleSheet>';
    }

    private function docPropsApp(array $sheetNames): string
    {
        $titles = implode('', array_map(fn($n) => "<vt:lpstr>{$n}</vt:lpstr>", $sheetNames));
        $count = count($sheetNames);
        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<Properties xmlns="http://schemas.openxmlformats.org/officeDocument/2006/extended-properties"
            xmlns:vt="http://schemas.openxmlformats.org/officeDocument/2006/docPropsVTypes">
  <Application>LOFBI KSOP Banten</Application>
  <HeadingPairs>
    <vt:vector size="2" baseType="variant">
      <vt:variant><vt:lpstr>Worksheets</vt:lpstr></vt:variant>
      <vt:variant><vt:i4>' . $count . '</vt:i4></vt:variant>
    </vt:vector>
  </HeadingPairs>
  <TitlesOfParts><vt:vector size="' . $count . '" baseType="lpstr">' . $titles . '</vt:vector></TitlesOfParts>
</Properties>';
    }

    private function docPropsCore(): string
    {
        $now = date('Y-m-d\TH:i:s\Z');
        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<cp:coreProperties xmlns:cp="http://schemas.openxmlformats.org/package/2006/metadata/core-properties"
                   xmlns:dc="http://purl.org/dc/elements/1.1/"
                   xmlns:dcterms="http://purl.org/dc/terms/">
  <dc:creator>LOFBI KSOP Kelas I Banten</dc:creator>
  <dcterms:created xsi:type="dcterms:W3CDTF" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance">' . $now . '</dcterms:created>
</cp:coreProperties>';
    }
}
