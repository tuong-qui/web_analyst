from docx import Document
from docx.oxml.ns import qn
import re
d=Document(r'D:\Open_Source\Đồ án\Bao_cao_da_chinh_sua.docx')
b=list(d._element.body)
for i,n in enumerate(b):
 t=''.join(n.xpath('.//w:t/text()'))
 if i>149 and (re.search(r'(?i)hình\s*[34]\.\d+|chèn ảnh',t) or n.xpath('./w:pPr/w:sectPr')):
  print(i,t[:450])
print('cached breaks',len(d._element.xpath('.//w:lastRenderedPageBreak')))
for i in [123,126,148,269,270,443,452,471]:
 print(i,b[i].xml[-1300:])
