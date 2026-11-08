import pdfplumber,re
with pdfplumber.open('tmp_report/caption_review.pdf') as d:
 listed={}
 for p in d.pages[9:11]:
  for line in p.extract_text().splitlines():
   m=re.match(r'^((?:Bảng|Hình) \d+\.\d+)\. .+?\.{4,}\s*(\d+)$',line)
   if m:listed[m[1]]=int(m[2])
 actual={}
 for p in d.pages[11:]:
  lines=p.extract_text().splitlines()
  footer=int(lines[-1])
  for line in lines:
   m=re.match(r'^((?:Bảng|Hình) \d+\.\d+)\.',line)
   if m:actual[m[1]]=footer
 assert len(actual)==len(listed)==40
 assert actual==listed,[(k,v,listed[k]) for k,v in actual.items() if listed[k]!=v]
 print('All 40 printed list page numbers match the caption page footers.')
