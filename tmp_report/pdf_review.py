import pdfplumber
import pypdfium2 as pdfium
from PIL import Image,ImageDraw
from pathlib import Path
import re
q=Path('tmp_report')
d=pdfium.PdfDocument(q/'caption_review.pdf')
print('pages',len(d))
with pdfplumber.open(q/'caption_review.pdf') as textdoc:
 for start in range(0,len(d),20):
  sheet=Image.new('RGB',(1000,1152),'#dedede')
  dr=ImageDraw.Draw(sheet)
  for i in range(start,min(start+20,len(d))):
   page=textdoc.pages[i]
   s=page.extract_text() or ''
   caps=[x for x in s.splitlines() if re.match(r'^(Hình|Bảng) [234]\.\d+\.',x.strip())]
   pics=len(page.images)
   if caps or pics or not s.strip():print(i+1,pics,len(caps),repr(s[:40]),' | '.join(caps)[:100])
   im=d[i].render(scale=.30).to_pil()
   j=i-start;x=(j%5)*200+9;y=(j//5)*288+20
   sheet.paste(im,(x,y));dr.text((x,y-15),str(i+1),fill='black')
  sheet.save(q/f'proof_{start+1}.png')
