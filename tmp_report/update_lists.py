from collections import Counter
from copy import deepcopy
import re
from docx import Document
from docx.oxml import OxmlElement
from docx.oxml.ns import qn
from docx.text.paragraph import Paragraph

src=r'D:\Open_Source\Đồ án\Bao_cao_da_chinh_sua.docx'
d=Document(src)
b=list(d._element.body)
def tx(n): return ''.join(n.xpath('.//w:t/text()'))
def el(tag, **attrs):
 n=OxmlElement('w:'+tag)
 for k,v in attrs.items(): n.set(qn('w:'+k),str(v))
 return n
def run_text(parent,text):
 r=el('r')
 t=el('t')
 t.set(qn('xml:space'),'preserve')
 t.text=text
 r.append(t)
 parent.append(r)
def field_start(p,instr):
 r=el('r'); r.append(el('fldChar',fldCharType='begin'));p.append(r)
 r=el('r'); n=el('instrText');n.set(qn('xml:space'),'preserve');n.text=' '+instr+' ';r.append(n);p.append(r)
 r=el('r'); r.append(el('fldChar',fldCharType='separate'));p.append(r)
def field_end(p):
 r=el('r');r.append(el('fldChar',fldCharType='end'));p.append(r)
def keep(n,with_next=False):
 pf=Paragraph(n,d._body).paragraph_format
 pf.keep_together=True
 pf.keep_with_next=with_next
def remove(n): n.getparent().remove(n)
def blank_par(template):
 p=el('p')
 if template.pPr is not None:p.append(deepcopy(template.pPr))
 return p

tables={
202:'So sánh các phương án phân tích',
466:'Định dạng tệp và cách đọc dữ liệu',
483:'Quy tắc kiểm tra theo nhóm dữ liệu',
501:'Khóa phát hiện bản ghi trùng theo nhóm dữ liệu',
518:'Các thành phần thiết lập phân tích luật kết hợp',
537:'Các chỉ số tổng quan của phân tích luật kết hợp',
541:'Các cột trong bảng kết quả luật kết hợp',
574:'Giá trị mặc định và phạm vi của các ngưỡng gợi ý',
579:'Các nút thao tác trên giỏ hàng mô phỏng',
597:'Nội dung thẻ gợi ý bán kèm',
603:'Trạng thái và thông báo của chức năng gợi ý',
622:'Các trường thiết lập và lọc kết quả RFM',
639:'Cách xác định các giá trị RFM',
643:'Thang điểm Recency theo số ngày từ lần mua gần nhất',
647:'Điều kiện phân khúc và hướng chăm sóc khách hàng',
663:'Các cột trong danh sách khách hàng theo phân khúc',
686:'Các cột trong danh sách bộ dữ liệu',
705:'Quy tắc kiểm tra thông tin tài khoản',
}
figs={
269:270,395:396,401:402,421:422,443:445,
452:'Biểu mẫu nhập dữ liệu khách hàng',
471:'Trang nhập và tra cứu đơn hàng',
558:559,565:566,576:577,594:595,604:605,
617:618,623:624,657:658,664:665,671:672,
683:684,699:700,707:708,714:715,724:725,
}
assert [i for i,n in enumerate(b) if n.tag==qn('w:tbl')] == [0,120,*tables]
assert [i for i,n in enumerate(b) if i>0 and n.xpath('.//w:drawing|.//w:pict')] == list(figs)
last_id=max(map(int,d._element.xpath('.//w:bookmarkStart/@w:id')))
entries={'Bảng':[],'Hình':[]}
seq=Counter()
def caption(p,kind,chapter,title):
 global last_id
 # Retain existing bookmarks and paragraph styling.
 for child in list(p):
  if child.tag not in [qn('w:pPr'),qn('w:bookmarkStart'),qn('w:bookmarkEnd')]: p.remove(child)
 old_ends=p.xpath('./w:bookmarkEnd')
 for node in old_ends:p.remove(node)
 seq[kind,chapter]+=1
 number=seq[kind,chapter]
 last_id+=1
 label='Bang' if kind=='Bảng' else 'Hinh'
 anchor=f'{label}_{chapter}_{number}'
 p.append(el('bookmarkStart',id=last_id,name=anchor))
 run_text(p,f'{kind} {chapter}.')
 field_start(p,f'SEQ {label}_{chapter} \\* ARABIC')
 run_text(p,str(number))
 field_end(p)
 run_text(p,'. '+title.rstrip('.'))
 p.append(el('bookmarkEnd',id=last_id))
 for node in old_ends:p.append(node)
 entries[kind].append((tx(p),anchor))
 keep(p,kind=='Bảng')
for i,title in tables.items():
 p=b[201] if i==202 else blank_par(b[201])
 if i!=202:b[i].addprevious(p)
 caption(p,'Bảng',2 if i==202 else 4,title)
 # Keep the header and first body row beside the caption.
 for cell in b[i].xpath('./w:tr[1]/w:tc'):
  for n in cell.xpath('./w:p'): keep(n,True)
 tr=b[i].xpath('./w:tr[1]')[0]
 pr=tr.get_or_add_trPr()
 if not pr.xpath('./w:tblHeader'):pr.append(el('tblHeader'))
for i,value in figs.items():
 if isinstance(value,int):
  p=b[value]
  title=re.sub(r'^Hình \d+\.\d+\.\s*','',tx(p))
 else:
  p=blank_par(b[270]);title=value
  b[i].addnext(p)
 caption(p,'Hình',3 if i<443 else 4,title)
 for n in b[i].itersiblings():
  if n is p:break
  if n.tag==qn('w:p') and not tx(n) and not n.xpath('.//w:drawing|.//w:pict|.//w:br|./w:pPr/w:sectPr'):
   remove(n)
 keep(b[i],True)
# Remove the detached sequence number alongside the use case drawing.
for r in b[269].xpath('./w:r'):
 if r.xpath('./w:fldChar|./w:instrText|./w:t[text()="1"]') and not r.xpath('.//w:drawing'):
  remove(r)
pp=Paragraph(b[269],d._body)
pp.style=d.styles['Normal']
pp.paragraph_format.keep_with_next=True
# Entries without a matching screenshot do not belong in the figure list.
for i in (513,514,519,520,539):remove(b[i])
for n in b[420].xpath('.//w:t'):
 if n.text and 'hình 3.2.' in n.text:n.text=n.text.replace('hình 3.2.','Hình 3.4.')
def rebuild(kind,first,last):
 template=b[first]
 for idx,(label,anchor) in enumerate(entries[kind]):
  p=blank_par(template)
  if idx==0:
   style='Table Caption' if kind=='Bảng' else 'Figure Caption'
   field_start(p,f'TOC \\h \\z \\t "{style},1"')
  h=el('hyperlink',anchor=anchor,history=1)
  run_text(h,label)
  r=el('r');r.append(el('tab'));h.append(r)
  field_start(h,f'PAGEREF {anchor} \\h')
  run_text(h,'–')
  field_end(h)
  p.append(h)
  if idx==len(entries[kind])-1:field_end(p)
  template.addprevious(p)
 for i in range(first,last+1):remove(b[i])
rebuild('Bảng',123,123)
rebuild('Hình',126,148)
settings=d.settings.element
update=settings.find(qn('w:updateFields'))
if update is None:settings.append(el('updateFields',val='true'))
else:update.set(qn('w:val'),'true')
out=r'D:\Open_Source\Laravel\web_analyst\Bao_cao_cap_nhat_danh_muc.docx'
d.save(out)
print({k:len(v) for k,v in entries.items()})
