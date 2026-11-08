from docx import Document
from docx.oxml.ns import qn
import re
d=Document(r'D:\Open_Source\Đồ án\Bao_cao_da_chinh_sua.docx')
def txt(n):return ''.join(n.xpath('.//w:t/text()'))
b=list(d._element.body)
for i,n in enumerate(b):
 t=txt(n)
 pic=n.xpath('.//w:drawing|.//w:pict')
 if n.tag==qn('w:tbl') or pic or re.match(r'^(Bảng |Hình |DANH MỤC|CHƯƠNG|\d+\.\d+\.)',t,re.I) and ('Hình ' in t[:6] or 'Bảng ' in t[:6] or t.startswith('DANH MỤC')):
  print(i,n.tag.split('}')[-1],len(pic),t[:230])
  if n.tag==qn('w:tbl') or pic:
   for j in range(i-3,i+4):
    if 0<=j<len(b) and j!=i: print(' ',j,txt(b[j])[:135])
   if n.tag==qn('w:tbl'):
    print('  ',[[txt(c)[:90] for c in row] for row in n.xpath('./w:tr')[:2]])
print('FIELDS')
from collections import Counter
print(Counter(x.strip().split(' ')[0] for x in d._element.xpath('.//w:instrText/text()')))
for n in d._element.xpath('.//w:instrText'):
 if 'TOC' in n.text: print(n.text, b.index(n.getparent().getparent()) if n.getparent().getparent() in b else 'nested')
