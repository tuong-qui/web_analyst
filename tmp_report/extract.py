from docx import Document
p=r'D:\Open_Source\Đồ án\Bao_cao_da_can_chinh.docx'
d=Document(p)
with open('tmp_report/paragraphs.txt','w',encoding='utf-8') as f:
 for i,p in enumerate(d.paragraphs):
  f.write(f'{i}: {p.text}\n')
