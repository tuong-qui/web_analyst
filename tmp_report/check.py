from docx import Document
from zipfile import ZipFile
from lxml import etree
src=Document(r'D:\Open_Source\Đồ án\Bao_cao_da_can_chinh.docx')
out=Document('Bao_cao_da_chinh_sua.docx')
assert len(src.paragraphs)==len(out.paragraphs)
assert len(src.tables)==len(out.tables)
changed=[i for i,(a,b) in enumerate(zip(src.paragraphs,out.paragraphs)) if a.text!=b.text]
assert len(changed)==22
with ZipFile('Bao_cao_da_chinh_sua.docx') as z:
 for name in z.namelist():
  if name.endswith('.xml'): etree.fromstring(z.read(name))
 with ZipFile(r'D:\Open_Source\Đồ án\Bao_cao_da_can_chinh.docx') as s:
  media=[n for n in s.namelist() if n.startswith('word/media/')]
  assert all(s.read(n)==z.read(n) for n in media)
print('Verified: 22 edited paragraphs; paragraph/table counts and all images preserved; XML valid.')
print('Paragraphs:',len(out.paragraphs),'Tables:',len(out.tables),'Images:',len(media))
