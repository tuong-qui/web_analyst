$ErrorActionPreference='Stop'
$root='C:\Users\ADMIN\.codex\visualizations\2026\11\01\01a19770-d4bb-7dd1-bb2a-47823cb34334'
$word=New-Object -ComObject Word.Application
$word.Visible=$false
$word.DisplayAlerts=0
try {
  $doc=$word.Documents.Open("$root\Bao_cao_da_can_chinh.docx",$false,$false)
  $doc.Repaginate()
  $doc.Fields.Update() | Out-Null
  foreach($toc in $doc.TablesOfContents){$toc.Update()}
  $doc.Repaginate()
  foreach($toc in $doc.TablesOfContents){$toc.UpdatePageNumbers()}
  $doc.Save()
  $doc.ExportAsFixedFormat("$root\qa.pdf",17)
  Write-Output "Pages: $($doc.ComputeStatistics(2))"
  $doc.Close(0)
} finally {$word.Quit()}
