$ErrorActionPreference = 'Stop'
$work = 'D:\Open_Source\Laravel\web_analyst'
$path = Join-Path $work 'Bao_cao_cap_nhat_danh_muc.docx'
$pdf = Join-Path $work 'tmp_report\caption_review.pdf'
$word = New-Object -ComObject Word.Application
try {
    $word.Visible = $false
    $word.DisplayAlerts = 0
    $doc = $word.Documents.Open($path, $false, $false)
    try {
        for ($pass = 0; $pass -lt 3; $pass++) {
            $doc.Repaginate()
            $fields = @($doc.Fields | Where-Object { $_.Code.Text -match '^\s*TOC\s' })
            if ($fields.Count -ne 3) { throw "Expected 3 lists, found $($fields.Count)" }
            foreach ($field in $fields) {
                if (-not $field.Update()) { throw "Cannot update $($field.Code.Text)" }
            }
        }
        $doc.Repaginate()
        $doc.Save()
        $doc.ExportAsFixedFormat($pdf, 17)
        "Saved three updated lists. Pages: $($doc.ComputeStatistics(2))"
    } finally {
        $doc.Close(0)
    }
} finally {
    $word.Quit(0)
}
