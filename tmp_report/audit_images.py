from docx import Document
from docx.oxml.ns import qn
from PIL import Image,ImageOps,ImageDraw
from io import BytesIO
d=Document(r'D:\Open_Source\Đồ án\Bao_cao_da_chinh_sua.docx')
b=list(d._element.body)
def t(n):return ''.join(n.xpath('.//w:t/text()'))
for i in [123,124,126,148,149,201,269,270,452,471,511,512,513,514,519,520,538,539]:
 n=b[i]
 print(i,n.tag.split('}')[-1],t(n)[:260],n.xpath('./w:pPr/w:pStyle/@w:val'),n.xpath('.//w:instrText/text()'),n.xpath('.//w:bookmarkStart/@w:name'))
print([(s.style_id,s.name) for s in d.styles if 'Caption' in s.name])
ids=[i for i,n in enumerate(b) if n.xpath('.//a:blip/@r:embed') and i>0]
canvas=Image.new('RGB',(900,140*((len(ids)+2)//3)),'white')
draw=ImageDraw.Draw(canvas)
for j,i in enumerate(ids):
 rid=b[i].xpath('.//a:blip/@r:embed')[0]
 im=Image.open(BytesIO(d.part.related_parts[rid].blob)).convert('RGB')
 im.thumbnail((292,116))
 x,y=j%3*300,j//3*140
 canvas.paste(im,(x,y+19))
 draw.text((x+3,y),str(i),fill='black')
canvas.save('tmp_report/thumb.png')
