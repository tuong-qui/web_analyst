$ErrorActionPreference = 'Stop'
$reportPath = 'D:\Open_Source\Laravel\web_analyst\reports\Bao_cao_do_an_WEB_ANALYST_hoan_chinh.docx'
$pdfPath = 'D:\Open_Source\Laravel\web_analyst\reports\qa_20261027\Bao_cao_do_an_WEB_ANALYST.pdf'
$wordApp = New-Object -ComObject Word.Application
$wordApp.Visible = $false
$wordApp.DisplayAlerts = 0
try {
    $reportDoc = $wordApp.Documents.Open($reportPath, $false, $false)
    $reportDoc.Fields.Update() | Out-Null
    foreach ($toc in $reportDoc.TablesOfContents) { $toc.Update() }
    $reportDoc.Repaginate()
    foreach ($toc in $reportDoc.TablesOfContents) { $toc.UpdatePageNumbers() }
    $reportDoc.Save()
    $reportDoc.ExportAsFixedFormat($pdfPath, 17)
    Write-Output ('Pages: ' + $reportDoc.ComputeStatistics(2))
    $reportDoc.Close(0)
} finally {
    $wordApp.Quit()
    [System.Runtime.InteropServices.Marshal]::ReleaseComObject($wordApp) | Out-Null
}
