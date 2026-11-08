from docx import Document
d=Document(r'D:\Open_Source\Đồ án\Bao_cao_da_chinh_sua.docx')
p=0
for i,n in enumerate(d._element.body):
 if i==151:p=1
 if i>=151:p+=len(n.xpath('.//w:lastRenderedPageBreak'))
 if i in [151,201,270,396,402,422,445,452,466,471,559,618,725]: print(i,p)
