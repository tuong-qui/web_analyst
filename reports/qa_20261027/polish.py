from docx import Document
from docx.shared import Cm
import re
p='D:/Open_Source/Laravel/web_analyst/reports/Bao_cao_do_an_WEB_ANALYST_hoan_chinh.docx'
d=Document(p)
refs=False
for para in d.paragraphs:
    if para.style.name.startswith('Heading') and re.match(r'^\d+\.\d+\.\d+\.',para.text):para.style='Heading 3'
    if para.text=='TÀI LIỆU THAM KHẢO':refs=True
    elif para.text=='PHỤ LỤC':refs=False
    elif refs and para.text.startswith('['):para.alignment=0
    if 'Phân trang giúp giảm số bản ghi kết xuất.' in para.text:
        para.text=para.text.replace('Phân trang giúp giảm số bản ghi kết xuất.','Các trang này giới hạn 100 bản ghi đầu; RFM và luật kết hợp có phân trang riêng.')
for t in d.tables:
    if len(t.columns)!=3:continue
    headers=[c.text for c in t.rows[0].cells]
    if headers[0]=='Trường':widths=[5.1,4.2,6.2]
    elif headers[0]=='Bảng':widths=[5.1,5.3,5.1]
    elif headers[0]=='Nội dung':widths=[3.1,6,6.4]
    else:widths=[3.4,4.8,7.3]
    for c,w in zip(t.columns,widths):c.width=Cm(w)
    for row in t.rows:
        for c,w in zip(row.cells,widths):c.width=Cm(w)
for t in d.tables:
    for c in t.rows[0].cells:
        for para in c.paragraphs:para.paragraph_format.keep_with_next=True
d.save(p)
