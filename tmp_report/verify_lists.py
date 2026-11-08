from docx import Document
from docx.oxml.ns import qn
from zipfile import ZipFile
from pathlib import Path
import re
root=Path(r'D:\Open_Source\Laravel\web_analyst')
out=root/'Bao_cao_cap_nhat_danh_muc.docx'
d=Document(out)
def text(p):return ''.join(p.xpath('.//w:t/text()'))
def style(p):return p.xpath('./w:pPr/w:pStyle/@w:val')
b=list(d._element.body)
for kind,style_id,count in [('Bảng','TableCaption',18),('Hình','FigureCaption',22)]:
 caps=[text(n) for n in b if style(n)==[style_id]]
 assert len(caps)==count and len(set(caps))==count,(kind,caps)
 assert all(re.match(rf'{kind} [234]\.\d+\. ',x) for x in caps)
 lines=[text(n) for n in b if style(n)==['TOC1'] and text(n).startswith(kind+' ')]
 assert len(lines)==count,(kind,lines)
 assert all(line.startswith(cap) for line,cap in zip(lines,caps))
 print(kind,count,'with page number:',sum(bool(re.fullmatch(r'\d+',line[len(cap):])) for line,cap in zip(lines,caps)))
for i,n in enumerate(b):
 if style(n)==['TableCaption']:
  assert b[i+1].tag==qn('w:tbl')
 if style(n)==['FigureCaption']:
  assert b[i-1].xpath('.//w:drawing|.//w:pict')
assert not any('[Chèn ảnh' in text(n) for n in b)
with ZipFile(r'D:\Open_Source\Đồ án\Bao_cao_da_chinh_sua.docx') as orig, ZipFile(out) as new:
 def media(z):return sorted(z.read(n) for n in z.namelist() if n.startswith('word/media/'))
 assert media(orig)==media(new)
print('Verified captions, entries, order, and unchanged embedded media.')
