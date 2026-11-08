from docx import Document
from docx.shared import Cm, Pt, RGBColor
from docx.enum.text import WD_ALIGN_PARAGRAPH
from docx.oxml import OxmlElement
from docx.oxml.ns import qn
from docx.enum.table import WD_CELL_VERTICAL_ALIGNMENT
from pathlib import Path
from copy import deepcopy
import re

ROOT=Path(r'C:\Users\ADMIN\.codex\visualizations\2026\11\01\01a19770-d4bb-7dd1-bb2a-47823cb34334')
ROOT.mkdir(exist_ok=True)
src=Path(r'D:\Open_Source\Đồ án\Hệ thống hỗ trợ ra quyết định dựa trên phân tích giỏ hàng trong thương mại điện tử.docx')
d=Document(src)

# Separate the use-case illustration from prose and its caption.
for p in list(d.paragraphs):
    if p.text.startswith('Sơ đồ Use Case tổng quát') and p._p.xpath('.//w:drawing'):
        p.insert_paragraph_before('Sơ đồ Use Case tổng quát của Web Analyst được trình bày tại Hình 3.1.')
        for t in p._p.xpath('.//w:t'):t.text=''
        cap=p.insert_paragraph_before('Hình 3.1. Sơ đồ use case')
        p._p.addnext(cap._p)
    if p.text.startswith('Vị trí chèn hình — Hình 4.1.'):
        p.text=p.text.replace('Vị trí chèn hình — ','',1)

def field(p,code):
    r=OxmlElement('w:r'); b=OxmlElement('w:fldChar'); b.set(qn('w:fldCharType'),'begin'); r.append(b); p._p.append(r)
    r=OxmlElement('w:r'); t=OxmlElement('w:instrText'); t.set(qn('xml:space'),'preserve'); t.text=' '+code+' '; r.append(t); p._p.append(r)
    for typ in ('separate','end'):
        r=OxmlElement('w:r'); e=OxmlElement('w:fldChar'); e.set(qn('w:fldCharType'),typ); r.append(e); p._p.append(r)

# Replace all stale cached contents, preserving the actual body and images.
ps=list(d.paragraphs)
for title,next_title,code in [
 ('MỤC LỤC','DANH MỤC TỪ VIẾT TẮT',r'TOC \o "1-3" \h \z \u'),
 ('DANH MỤC BẢNG','DANH MỤC HÌNH',r'TOC \h \z \t "Table Caption,1"'),
 ('DANH MỤC HÌNH','CHƯƠNG 1. TỔNG QUAN ĐỀ TÀI',r'TOC \h \z \t "Figure Caption,1"')]:
    ps=list(d.paragraphs); start=next(i for i,p in enumerate(ps) if p.text.strip()==title)
    end=next(i for i in range(start+1,len(ps)) if ps[i].text.strip()==next_title)
    for p in ps[start+1:end]: p._element.getparent().remove(p._element)
    p=ps[start].insert_paragraph_before('')
    ps[start]._p.addnext(p._p)
    field(p,code)

for name in ['Normal','Heading 1','Heading 2','Heading 3','Caption','TOC Heading']:
    s=d.styles[name]; s.font.name='Times New Roman'; s.font.size=Pt(13); s.font.color.rgb=RGBColor(0,0,0)
    s._element.get_or_add_rPr().get_or_add_rFonts().set(qn('w:eastAsia'),'Times New Roman')
    pf=s.paragraph_format; pf.space_after=Pt(6); pf.space_before=Pt(0); pf.line_spacing=1.5
for name in ['Front Heading','Table Caption','Figure Caption']:
    if name not in d.styles: d.styles.add_style(name,1)
    d.styles[name].base_style=d.styles['Normal']
for i in range(1,4):
    s=d.styles[f'Heading {i}']; s.font.bold=True; s.font.size=Pt(14 if i==1 else 13)
    s.paragraph_format.keep_with_next=True; s.paragraph_format.space_before=Pt(12); s.paragraph_format.space_after=Pt(6)
    s.paragraph_format.first_line_indent=Cm(0)
    s.paragraph_format.page_break_before=(i==1)
for i in range(1,4):
    name=f'TOC {i}'
    if name not in d.styles:d.styles.add_style(name,1)
    s=d.styles[name]; s.font.name='Times New Roman'; s.font.size=Pt(12)
    s.paragraph_format.line_spacing=1.15; s.paragraph_format.space_after=Pt(3)
    s.paragraph_format.first_line_indent=Cm(0)

front={'LỜI CAM ĐOAN','LỜI CẢM ƠN','TÓM TẮT ĐỒ ÁN','MỤC LỤC','DANH MỤC TỪ VIẾT TẮT','DANH MỤC BẢNG','DANH MỤC HÌNH'}
figure_counts={}; table_counts={}; chapter=0; in_body=False
for p in d.paragraphs:
    t=p.text.strip()
    if t.startswith('CHƯƠNG '):
        chapter=int(re.search(r'CHƯƠNG (\d+)',t).group(1)); in_body=True
    if in_body:
        # Correct numbering introduced by inserting the RFM section.
        if t=='2.5 Mô hình RFM':p.text='2.5. Mô hình RFM'
        elif re.match(r'^2\.[567](?:\.|\s)',t):
            p.text=re.sub(r'^2\.([567])',lambda m:'2.'+str(int(m[1])+1),t)
        if t.startswith('4.4.6.'):p.text=t.replace('4.4.6.','4.4.5.',1)
    t=p.text.strip()
    if t in front:
        p.style=d.styles['Front Heading']; p.paragraph_format.page_break_before=True
        p.paragraph_format.keep_with_next=True; p.alignment=WD_ALIGN_PARAGRAPH.CENTER
    elif t.startswith('CHƯƠNG ') or t in ['KẾT LUẬN VÀ HƯỚNG PHÁT TRIỂN','TÀI LIỆU THAM KHẢO']:
        p.style='Heading 1'; p.alignment=WD_ALIGN_PARAGRAPH.CENTER
    elif in_body and re.match(r'^\d+\.\d+\.\d+\.?\s',t):p.style='Heading 3'
    elif in_body and re.match(r'^\d+\.\d+\.?\s',t):p.style='Heading 2'
    elif p.style.name.startswith('Heading') and t:p.paragraph_format.keep_with_next=True
    elif re.match(r'^Hình\s+\d+\.[\dx]+',t):
        figure_counts[chapter]=figure_counts.get(chapter,0)+1
        p.text=re.sub(r'^Hình\s+\d+\.[\dx]+\s*[.:]?\s*',f'Hình {chapter}.{figure_counts[chapter]}. ',t)
        p.style='Figure Caption'; p.alignment=WD_ALIGN_PARAGRAPH.CENTER
        p.paragraph_format.keep_with_next=False; p.paragraph_format.keep_together=True
    elif re.match(r'^Bảng\s+\d+\.\d+',t):
        table_counts[chapter]=table_counts.get(chapter,0)+1
        p.text=re.sub(r'^Bảng\s+\d+\.\d+\s*[.:]?\s*',f'Bảng {chapter}.{table_counts[chapter]}. ',t)
        p.style='Table Caption'; p.alignment=WD_ALIGN_PARAGRAPH.CENTER; p.paragraph_format.keep_with_next=True
    elif t:
        p.alignment=WD_ALIGN_PARAGRAPH.JUSTIFY
        p.paragraph_format.first_line_indent=Cm(1)
    pf=p.paragraph_format
    pf.widow_control=True
    if p.style.name.startswith('Heading') or p.style.name=='Front Heading':
        pf.first_line_indent=Cm(0)
    if p._p.xpath('.//w:drawing'):
        p.alignment=WD_ALIGN_PARAGRAPH.CENTER; pf.first_line_indent=Cm(0); pf.keep_with_next=True
    for r in p.runs:
        r.font.name='Times New Roman'; r.font.size=Pt(13)
        r.font.color.rgb=RGBColor(0,0,0)
        if p.style.name.startswith('Heading') or p.style.name=='Front Heading':r.bold=True
    # Clear decorative rules from all heading styles/paragraphs.
    if p._p.pPr is not None:
        for el in list(p._p.pPr):
            if el.tag==qn('w:pBdr'):p._p.pPr.remove(el)

for s in d.sections:
    s.page_width=Cm(21); s.page_height=Cm(29.7)
    s.left_margin=Cm(3); s.right_margin=Cm(2); s.top_margin=Cm(2); s.bottom_margin=Cm(2)
    s.header_distance=Cm(1); s.footer_distance=Cm(1)
    for p in s.footer.paragraphs:
        p.clear(); p.alignment=WD_ALIGN_PARAGRAPH.CENTER
    field(s.footer.paragraphs[0],'PAGE')

# Roman front matter and Arabic body, both starting at 1; no number on cover.
chapter1=next(p for p in d.paragraphs if p.text.startswith('CHƯƠNG 1.'))
boundary=chapter1.insert_paragraph_before('')
front_sect=deepcopy(d.sections[-1]._sectPr)
for el in list(front_sect):
    if el.tag==qn('w:pgNumType'):front_sect.remove(el)
num=OxmlElement('w:pgNumType');num.set(qn('w:fmt'),'lowerRoman');num.set(qn('w:start'),'1');front_sect.append(num)
boundary._p.get_or_add_pPr().append(front_sect)
for el in list(d.sections[-1]._sectPr):
    if el.tag==qn('w:pgNumType'):d.sections[-1]._sectPr.remove(el)
num=OxmlElement('w:pgNumType');num.set(qn('w:fmt'),'decimal');num.set(qn('w:start'),'1');d.sections[-1]._sectPr.append(num)
for p in d.sections[0].footer.paragraphs:p.clear()

max_width=Cm(16)
for shape in d.inline_shapes:
    if shape.width>max_width:
        ratio=max_width/shape.width; shape.width=int(shape.width*ratio); shape.height=int(shape.height*ratio)
    if shape.height>Cm(14):
        ratio=Cm(14)/shape.height;shape.width=int(shape.width*ratio);shape.height=int(shape.height*ratio)

for ti,table in enumerate(d.tables):
    if ti==0:continue # retain the original cover
    table.autofit=False
    cols=len(table.columns)
    old=[c.width or max_width//cols for c in table.columns]; total=sum(old)
    for c,w in zip(table.columns,old):c.width=int(max_width*w/total)
    borders=OxmlElement('w:tblBorders')
    for edge in ['top','left','bottom','right','insideH','insideV']:
        e=OxmlElement('w:'+edge)
        for k,v in [('val','single'),('sz','4'),('color','D9D9D9')]:e.set(qn('w:'+k),v)
        borders.append(e)
    pr=table._tbl.tblPr
    for el in list(pr):
        if el.tag==qn('w:tblBorders'):pr.remove(el)
    pr.append(borders)
    for ri,row in enumerate(table.rows):
        row.height=None
        if ri==0:
            repeat=OxmlElement('w:tblHeader');row._tr.get_or_add_trPr().append(repeat)
        for ci,cell in enumerate(row.cells):
            cell.width=int(max_width*old[min(ci,len(old)-1)]/total)
            cell.vertical_alignment=WD_CELL_VERTICAL_ALIGNMENT.CENTER
            tcpr=cell._tc.get_or_add_tcPr()
            for el in list(tcpr):
                if el.tag==qn('w:shd'):tcpr.remove(el)
            sh=OxmlElement('w:shd');sh.set(qn('w:fill'),'E7E6E6' if ri==0 else 'FFFFFF');tcpr.append(sh)
            margins=OxmlElement('w:tcMar')
            for edge in ['top','left','bottom','right']:
                e=OxmlElement('w:'+edge);e.set(qn('w:w'),'85');e.set(qn('w:type'),'dxa');margins.append(e)
            tcpr.append(margins)
            for p in cell.paragraphs:
                p.paragraph_format.first_line_indent=Cm(0);p.paragraph_format.space_after=Pt(3);p.paragraph_format.space_before=Pt(3)
                p.paragraph_format.line_spacing=1.15;p.alignment=WD_ALIGN_PARAGRAPH.LEFT
                for r in p.runs:r.font.name='Times New Roman';r.font.size=Pt(11);r.font.color.rgb=RGBColor(0,0,0);r.bold=(ri==0)

settings=d.settings.element
up=OxmlElement('w:updateFields');up.set(qn('w:val'),'true');settings.append(up)
out=ROOT/'Bao_cao_da_can_chinh.docx'
d.save(out)
print(out)
