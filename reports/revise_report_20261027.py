from pathlib import Path
import re, sys, json
from docx import Document
from docx.shared import Cm, Pt, RGBColor
from docx.enum.text import WD_ALIGN_PARAGRAPH
from docx.enum.table import WD_TABLE_ALIGNMENT, WD_CELL_VERTICAL_ALIGNMENT
from docx.oxml import OxmlElement
from docx.oxml.ns import qn
from docx.text.paragraph import Paragraph
from PIL import Image, ImageDraw, ImageFont

ROOT=Path(__file__).parent
ASSETS=ROOT/'qa_20261027'; ASSETS.mkdir(exist_ok=True)
OUT=ROOT/'Bao_cao_do_an_WEB_ANALYST_hoan_chinh.docx'
src=(ROOT/'build_final_report.py').read_text(encoding='utf-8')
src=src.replace("out=ROOT/'Bao_cao_cuoi_khoa_WEB_ANALYST.docx'", "out=ROOT/'Bao_cao_do_an_WEB_ANALYST_hoan_chinh.docx'")
exec(compile(src,str(ROOT/'build_final_report.py'),'exec'), {'__file__':str(ROOT/'build_final_report.py')})
doc=Document(OUT)

def before(anchor,text,style=None):
    el=OxmlElement('w:p');anchor._p.addprevious(el)
    p=Paragraph(el,anchor._parent)
    if style:p.style=style
    p.add_run(text)
    if not style:p.alignment=WD_ALIGN_PARAGRAPH.JUSTIFY
    return p

def find(prefix):return next(p for p in doc.paragraphs if p.text.startswith(prefix))
def addsec(anchor,title,body,level=2):
    before(anchor,title,f'Heading {level}')
    for t in body.split('|'):before(anchor,t)

def addtable(anchor,title,headers,rows,widths=None):
    before(anchor,title,'Caption')
    t=doc.add_table(rows=1,cols=len(headers)); t.style='Table Grid'
    for c,v in zip(t.rows[0].cells,headers):c.text=v
    for row in rows:
        for c,v in zip(t.add_row().cells,row):c.text=str(v)
    anchor._p.addprevious(t._tbl)
    t.autofit=False
    for row in t.rows:
        for i,c in enumerate(row.cells):
            if widths:c.width=Cm(widths[i])
    return t

# Correct source facts and remove stale descriptions of planned persistence.
for p in doc.paragraphs:
    t=p.text
    replacements={
      'Nhập tên và thông tin mô tả; hệ thống kiểm tra đầu vào':'Nhập tên bộ dữ liệu; hệ thống kiểm tra đầu vào',
      'Đầu vào dataset gồm tên và mô tả theo biểu mẫu.':'Đầu vào tạo dataset gồm tên bắt buộc, tối đa 150 ký tự.',
      'Tra cứu bảng dữ liệu, phân trang và tải tệp nhập tương ứng.':'Tra cứu tối đa 100 bản ghi đầu theo mã và tải tệp nhập tương ứng.',
      'Danh sách dữ liệu nghiệp vụ trình bày theo bản ghi và phân trang.':'Danh sách dữ liệu nghiệp vụ hiển thị tối đa 100 bản ghi đầu; danh sách RFM và luật kết hợp phân trang 25 bản ghi mỗi trang.',
      'Phiên bản mã nguồn khảo sát ngày 26/10/2026.':'Phiên bản mã nguồn khảo sát ngày 27/10/2026.',
      'lưu cấu hình phiên phân tích, mã băm dữ liệu và xuất kết quả':'giao diện tra cứu lịch sử phiên phân tích, mã băm tệp nguồn và xuất kết quả',
      'Chưa ghi nhận ca lưu phiên là đạt nếu chức năng chưa triển khai.':'Các bảng kết quả và phiên chạy cần được đối chiếu với ca kiểm thử lưu và tái sử dụng hiện có.',
    }
    for a,b in replacements.items():t=t.replace(a,b)
    if t!=p.text:p.text=t

# Align the opening outline with the supplied eight-item structure.
mapping={
 '1. Lý do chọn đề tài':'1. Tính cấp thiết của đề tài',
 '2. Mục tiêu của đề tài':'3. Mục tiêu nghiên cứu',
 '3. Đối tượng và phạm vi nghiên cứu':'5. Đối tượng và phạm vi nghiên cứu',
 '4. Phương pháp nghiên cứu và thực hiện':'6. Phương pháp nghiên cứu',
 '5. Kết quả dự kiến':'8. Kết quả dự kiến',
 '6. Ý nghĩa thực tiễn':'2. Ý nghĩa thực tiễn',
 '7. Nội dung và thời gian thực hiện':'7. Thời gian thực hiện',
}
# Reorder whole heading blocks instead of merely renumbering.
start=find('PHẦN I  MỞ ĐẦU'); end=find('PHẦN II  NỘI DUNG')
els=[]; cur=start._p.getnext()
while cur is not end._p:els.append(cur);cur=cur.getnext()
blocks=[]
for e in els:
    p=Paragraph(e,start._parent) if e.tag==qn('w:p') else None
    if p is not None and p.style.name=='Heading 2':blocks.append([])
    if blocks:blocks[-1].append(e)
for e in els:e.getparent().remove(e)
for idx in [0,5,1]:
    for e in blocks[idx]:end._p.addprevious(e)
addsec(end,'4. Nội dung thực hiện','Nội dung đồ án gồm xác định bài toán phân tích dữ liệu thương mại điện tử; khảo sát chức năng và xây dựng yêu cầu; mô hình hóa ca sử dụng và luồng xử lý; thiết kế cơ sở dữ liệu có phạm vi bộ dữ liệu; xây dựng chức năng nhập liệu, thống kê, RFM, luật kết hợp và gợi ý mua kèm. Bước đánh giá đối chiếu phép tính tay với chương trình, kiểm tra các trường hợp sai dữ liệu và truy cập trái quyền.')
for idx in [2,3,6,4]:
    for e in blocks[idx]:end._p.addprevious(e)
for p in doc.paragraphs:
    if p.text in mapping:p.text=mapping[p.text]
# Keep section label with chapter 1, avoiding an isolated title page.
end.style='Subtitle';end.paragraph_format.page_break_before=True;end.paragraph_format.keep_with_next=True
find('CHƯƠNG 1').paragraph_format.page_break_before=False
before(find('1.3.1.'),'1.3. Cơ sở lý thuyết phân tích giỏ hàng','Heading 2')
for p in doc.paragraphs:
    if re.match(r'^1\.3\.\d+\.',p.text):p.style='Heading 3'

anchor=find('2.3. Yêu cầu phi chức năng')
addsec(anchor,'2.2.1. Yêu cầu chức năng và tiêu chí chấp nhận','Bảng 2.1 tổng hợp các chức năng đã xác định từ ứng dụng. Các tiêu chí chấp nhận tập trung vào đầu vào có thể tái lập và hành vi quan sát được: thông báo lỗi, phạm vi bản ghi, công thức và thứ tự kết quả.',3)
addsec(anchor,'2.2.2. Kế hoạch khảo sát người sử dụng','Hoạt động khảo sát nghiệp vụ được đề xuất theo hai nhóm: người quản lý quyết định mục tiêu phân tích và người trực tiếp chuẩn bị tệp. Một buổi khảo sát cần ghi rõ vai trò, ngày thực hiện, nguồn dữ liệu, vấn đề gặp phải và tiêu chí nghiệm thu. Câu trả lời được đối chiếu với chức năng hiện có trước khi chuyển thành yêu cầu thay đổi.|Kết quả khảo sát thực địa chỉ được tổng hợp khi có phiếu trả lời. Các mẫu trong phụ lục dùng để tổ chức thu thập ý kiến, không đại diện cho các cuộc phỏng vấn đã diễn ra.',3)

anchor=find('3.3. Sơ đồ trình tự')
addsec(anchor,'3.2.6. Luồng hoạt động nhập dữ liệu','Người dùng chọn loại dữ liệu và tải tệp. Máy chủ xác định bộ dữ liệu thuộc tài khoản, đọc tiêu đề và kiểm tra các cột bắt buộc. Với từng dòng, hệ thống chuẩn hóa giá trị rỗng, kiểm tra kiểu, miền giá trị, khóa trùng và tham chiếu. Dữ liệu hợp lệ được gom lô để ghi; một lỗi phát sinh làm hủy giao dịch.|Điểm quyết định thứ nhất là cấu trúc tệp; điểm thứ hai là tính hợp lệ của dòng; điểm thứ ba là sự tồn tại của khóa ngoại trong cùng dataset. Thành công chỉ được xác nhận sau khi toàn bộ giao dịch hoàn tất. Do đó số dòng đã xử lý trong bộ nhớ không đồng nghĩa với số dòng đã lưu bền vững.',3)
addsec(anchor,'3.2.7. Luồng hoạt động tạo gợi ý','Hệ thống kiểm tra các danh mục đã chọn thuộc dữ liệu hiện tại rồi lấy các luật đạt ngưỡng. Với mỗi luật theo thứ tự xếp hạng, hệ thống giữ luật có tiền đề nằm trong lựa chọn và hệ quả chưa được chọn. Các hệ quả trùng được hợp nhất theo lần xuất hiện đầu tiên; mười ứng viên đầu được lưu và trình bày.|Nếu người dùng chưa chọn danh mục, hệ thống trả danh sách gợi ý rỗng. Khi ngưỡng mới không còn luật phù hợp, lựa chọn đầu vào vẫn được giữ để người dùng có thể điều chỉnh mà không phải chọn lại.',3)
anchor=find('3.4. Phân tích thuật toán')
addsec(anchor,'3.3.1. Nhánh tái sử dụng và nhánh tính mới','Sau khi khóa bản ghi dataset trong giao dịch, dịch vụ tạo cache_key từ phiên bản xử lý, rules_version và ba ngưỡng. Nếu tìm thấy phiên chạy tương ứng, hệ thống đọc kết quả đã lưu, kể cả trường hợp không có luật. Nếu chưa có, dịch vụ đếm giỏ, tính luật rồi lưu association_rule_runs và association_rules trước khi trả kết quả.|Đối với gợi ý, khóa tái sử dụng còn chứa danh sách danh mục đã được sắp xếp. Hai lựa chọn có cùng tập danh mục nhưng khác thứ tự không tạo phiên kết quả khác nhau. Khi dữ liệu liên quan thay đổi, cơ chế vô hiệu hóa tăng rules_version và loại kết quả cũ.',3)

anchor=find('4.2. Thiết kế kiến trúc')
addsec(anchor,'4.1.2. Thiết kế lưu kết quả phân tích','association_rule_runs ghi nhận lần phân tích theo dataset và khóa cấu hình; association_rules lưu tiền đề và hệ quả ở cấp danh mục, số giỏ cùng các chỉ số. recommendation_runs lưu tập danh mục đầu vào; bundle_recommendations lưu ứng viên, thứ hạng và căn cứ định lượng.|customer_segments lưu ảnh chụp RFM theo ngày. Khóa chính ghép gồm dataset_id, analysis_date và customer_unique_id. Khi phân tích lại cùng ngày, hệ thống thay thế toàn bộ kết quả ngày đó trong một giao dịch và giữ nguyên các ngày khác. Đây là lưu trạng thái phân tích theo ngày, không phải nhật ký bất biến của mọi lần chạy.',3)
addtable(anchor,'Bảng 4.5  Các bảng lưu kết quả phân tích',['Bảng','Dữ liệu chính','Phạm vi'],[
 ['association_rule_runs','cache_key, baskets, items','dataset và phiên luật'],
 ['association_rules','run_id, hai danh mục, pair_count, chỉ số','từng luật có hướng'],
 ['recommendation_runs','cache_key, selected_items','dataset và lựa chọn'],
 ['bundle_recommendations','recommendation_run_id, rank, chỉ số','từng gợi ý'],
 ['customer_segments','ngày, định danh, RFM, nhóm','dataset và ngày phân tích'],
], [4.5,6,4.5])
anchor=find('4.4. Thiết kế luật')
addsec(anchor,'4.3.1. Tính điểm và xử lý các giá trị bằng nhau','Với danh sách Monetary đã sắp tăng, mỗi ngưỡng phân vị được lấy ở vị trí ceil(n × q) − 1, với q thuộc tập 0,2; 0,4; 0,6; 0,8. Khách hàng có giá trị dương nhận điểm 1 cộng số ngưỡng mà giá trị của họ lớn hơn. Vì sử dụng so sánh lớn hơn nghiêm ngặt, hai giá trị bằng nhau không bị tách điểm tại cùng một ngưỡng.|Nếu nhiều ngưỡng trùng, phân bố điểm không nhất thiết có đủ năm mức và không bảo đảm mỗi mức chứa 20% khách hàng. Đây là tính chất của cách chấm điểm hiện tại. Việc diễn giải cần dựa vào số khách thực tế ở từng mức, tránh xem các nhóm điểm là năm phần bằng nhau.',3)
addsec(anchor,'4.3.2. Lưu ảnh chụp trước lọc giao diện','Controller gọi CustomerSegmentSnapshots để lưu toàn bộ truy vấn phân tích trước khi lọc nhóm, tìm kiếm và phân trang. Cách tổ chức này bảo đảm ảnh chụp không chỉ chứa 25 khách hàng đang hiển thị hoặc một nhóm đang được chọn.|Dịch vụ khóa bản ghi dataset, xóa ảnh chụp cùng ngày và sao chép kết quả bằng INSERT SELECT trong giao dịch. Nếu thao tác chèn lỗi, giao dịch phục hồi dữ liệu trước đó. Ngày phân tích khác không bị xóa khi làm mới ngày hiện tại.',3)
anchor=find('4.5. Thiết kế giao diện')
addsec(anchor,'4.4.1. Ví dụ tính tay và diễn giải','Xét năm giỏ minh họa: {A,B}, {A,B}, {A}, {B}, {C}. Có ba giỏ chứa A, ba giỏ chứa B và hai giỏ chứa đồng thời A và B. Support của A ⇒ B là 2/5 × 100 = 40%; Confidence là 2/3 × 100 ≈ 66,67%; Lift là (2/3)/(3/5) ≈ 1,1111.|Ví dụ cho thấy xác suất xuất hiện B trong giỏ có A cao hơn tần suất nền của B trong bộ giỏ minh họa. Kết quả không chứng minh A gây ra việc mua B và không thể quy đổi thành mức tăng doanh thu. Tệp AssociationRuleTest kiểm tra chính bộ số đếm và các chỉ số này.',3)
addsec(anchor,'4.4.2. Kiểm soát phiên bản và kết quả rỗng','Một phiên có không luật vẫn được lưu để tránh tính lại khi người dùng mở cùng cấu hình. Việc tái sử dụng kết quả dựa vào dataset, rules_version và ngưỡng, thay vì chỉ dựa vào URL của trang. Dữ liệu lưu cần được vô hiệu hóa đồng bộ với giao dịch nhập có ảnh hưởng đến giỏ.|Cơ chế hiện tại hỗ trợ lưu và sử dụng lại kết quả trong xử lý. Giao diện quản lý lịch sử có lựa chọn mở từng phiên, ghi chú nghiệp vụ hoặc xuất hồ sơ phân tích vẫn là hướng có thể phát triển thêm.',3)

# Make conclusions reflect implemented persistence.
p=find('Ưu tiên hoàn thiện quy trình triển khai tái lập')
p.text='Ưu tiên hoàn thiện quy trình triển khai tái lập, thu thập nhật ký kiểm thử và thực nghiệm trên bộ dữ liệu cố định. Hệ thống đã có các bảng lưu luật, gợi ý và ảnh chụp RFM; bước tiếp theo là bổ sung giao diện tra cứu lịch sử, mã băm tệp nguồn và xuất kết quả để thuận tiện đối chiếu giữa các lần chạy.'

# Add source entries used for framework concepts and formula definitions.
anchor=Paragraph(find('PHỤ LỤC')._p.getprevious(),find('PHỤ LỤC')._parent)
for text in [
 '[4] Laravel. Validation, tài liệu phiên bản 13.x. https://laravel.com/framework/docs/13.x/validation. Truy cập ngày 27/10/2026.',
 '[5] Laravel. Database Getting Started, tài liệu phiên bản 13.x. https://laravel.com/framework/docs/13.x/database. Truy cập ngày 27/10/2026.',
 '[6] mlxtend. Association rules, định nghĩa các chỉ số đánh giá luật. https://rasbt.github.io/mlxtend/user_guide/frequent_patterns/association_rules/. Truy cập ngày 27/10/2026.'
]:before(anchor,text)
before(find('4.2.1.'),'Các nguyên tắc kiểm tra đầu vào và giao dịch cơ sở dữ liệu được đối chiếu với tài liệu Laravel [4], [5]. Các công thức Support, Confidence và Lift dùng cách định nghĩa trong tài liệu luật kết hợp [6]; mã nguồn dự án triển khai việc đếm bằng SQL, không sử dụng thư viện mlxtend.')

# Ten appendices, as in the reference outline, adapted to this application.
find('Phụ lục 6. Kịch bản trình diễn').text='Phụ lục 10. Storyboard và kịch bản trình diễn'
anchor=find('Phụ lục 10.')
addsec(anchor,'Phụ lục 6. Từ điển giá trị và tham số','dataset_id là số nguyên dương và phải thuộc tài khoản hiện tại. date nhận ngày theo định dạng YYYY-MM-DD. segment nhận một trong sáu mã champions, loyal, new, at_risk, inactive, potential. sort nhận monetary, frequency hoặc recency.|support nhận từ 0,01 đến 100, đơn vị phần trăm. confidence nhận từ 0 đến 100, đơn vị phần trăm. lift nhận từ 0 đến 1000, không có đơn vị. Giá trị mặc định lần lượt là 0,1; 20; 1. page là số nguyên từ 1. items là mảng tối đa 50 tên danh mục khác nhau, mỗi tên tối đa 100 ký tự.')
addsec(anchor,'Phụ lục 7. Từ điển dữ liệu kết quả','Các bảng kết quả gắn với dataset_id. Chỉ số Support và Confidence của luật được lưu theo phần trăm; Lift là tỷ lệ. pair_count biểu diễn số giỏ hỗ trợ cặp, không phải số lượng hàng đã bán. rank biểu diễn thứ tự gợi ý và không phải mức xác suất mua.|Đối với customer_segments, recency đo bằng ngày; frequency đếm số đơn hợp lệ; monetary cộng giá trị thanh toán; r_score, f_score, m_score nhận các mức từ 1 đến 5. analysis_date là ngày tham chiếu, last_purchase là ngày mua gần nhất và analyzed_at là thời điểm ghi ảnh chụp.')
addtable(anchor,'Bảng PL2  Trường ảnh chụp RFM',['Trường','Kiểu trong migration','Ý nghĩa'],[
 ['dataset_id','unsignedBigInteger','Phạm vi dataset'],['analysis_date','date','Ngày tham chiếu'],['customer_unique_id','varchar 50','Định danh khách hàng'],['last_purchase','date','Ngày mua gần nhất'],['recency','unsignedInteger','Số ngày từ lần mua cuối'],['frequency','unsignedBigInteger','Số đơn hợp lệ'],['monetary','decimal 20,2','Tổng thanh toán'],['r_score f_score m_score','unsignedTinyInteger','Ba điểm RFM'],['segment','varchar 20','Mã phân nhóm'],['analyzed_at','timestamp','Thời điểm ghi kết quả']
],[4.8,4.4,5.8])
addsec(anchor,'Phụ lục 8. Biểu mẫu kiểm tra đầu vào','Biểu mẫu tạo dataset: ô tên bắt buộc, giới hạn 150 ký tự; khi thành công dataset mới được chọn trong phiên làm việc. Biểu mẫu phân tích luật: ba ô số và nút áp dụng; khi sai miền, người dùng nhận thông báo kiểm tra đầu vào.|Biểu mẫu nhập dữ liệu: chọn tệp đúng nhóm, hỗ trợ CSV, TSV, XLSX, XLS và ODS với giới hạn 51.200 KB tại các controller nhập chi tiết đơn và thanh toán. Bảng tính dùng trang tính đầu tiên; ô công thức bị từ chối và cần chuyển thành giá trị. Các trường ngày tháng được chuẩn hóa thành chuỗi YYYY-MM-DD HH:MM:SS.|Trình tự nhập đề xuất: khách hàng và sản phẩm; đơn hàng; chi tiết đơn hàng và thanh toán. Với khóa ngoại chưa có trong cùng dataset, sửa tệp hoặc nhập bảng cha trước rồi thực hiện lại. Thông báo lỗi có số dòng giúp người dùng đối chiếu với tệp nguồn.')
addsec(anchor,'Phụ lục 9. Biểu mẫu đánh giá đầu ra','Phiếu đối chiếu thống kê cần ghi tên dataset, thời điểm chạy, số đơn, số khách hàng duy nhất, tổng thanh toán và truy vấn kiểm tra độc lập. Giá trị thanh toán trung bình tính trên số đơn có bản ghi thanh toán, không mặc định lấy tổng số đơn làm mẫu số.|Phiếu đối chiếu luật cần ghi N, số giỏ tiền đề, số giỏ hệ quả và số giỏ mua chung để tính lại các chỉ số. Phiếu RFM cần ghi ngày tham chiếu, các đơn hợp lệ của một định danh khách hàng, các khoản thanh toán và bốn ngưỡng Monetary.|Phiếu đánh giá gợi ý cần kiểm tra danh mục đầu vào, danh mục được đề xuất, số giỏ hỗ trợ, chỉ số và thứ hạng. Các ứng viên không được trùng nhau hoặc nằm trong lựa chọn ban đầu. Kết quả rỗng là đầu ra hợp lệ khi không có luật thỏa điều kiện.')

# Actual technical diagrams, built from vector-like primitives at print resolution.
fontpath='C:/Windows/Fonts/times.ttf'
def diagram_png(key,kind):
    im=Image.new('RGB',(1800,1080),'white');dr=ImageDraw.Draw(im)
    f=ImageFont.truetype(fontpath,40); small=ImageFont.truetype(fontpath,36)
    def box(x,y,w,h,label,ellipse=False):
        (dr.ellipse if ellipse else dr.rectangle)((x,y,x+w,y+h),fill='#f5f5f5',outline='black',width=3)
        lines=label.split('\n');cy=y+h/2-len(lines)*20
        for line in lines:
            dr.text((x+w/2,cy),line,fill='black',font=f,anchor='mt');cy+=40
    def arrow(a,b,label=''):
        import math
        dr.line((a,b),fill='black',width=3)
        ang=math.atan2(b[1]-a[1],b[0]-a[0]); l=16
        dr.polygon([b,(b[0]-l*math.cos(ang-.5),b[1]-l*math.sin(ang-.5)),(b[0]-l*math.cos(ang+.5),b[1]-l*math.sin(ang+.5))],fill='black')
        if label:dr.text(((a[0]+b[0])/2+12,(a[1]+b[1])/2-32),label,fill='black',font=small)
    if kind=='flow':
        labs=['Đăng nhập và xác thực','Tạo hoặc chọn bộ dữ liệu','Nhập khách hàng và sản phẩm','Nhập đơn, chi tiết và thanh toán','Đối chiếu chất lượng dữ liệu']
        for i,lab in enumerate(labs):
            y=20+i*155;box(450,y,900,115,lab)
            if i<4:arrow((900,y+115),(900,y+155))
        arrow((900,755),(900,820))
        for x,lab in [(60,'Thống kê'),(650,'Phân nhóm RFM'),(1240,'Luật và gợi ý')]:
            box(x,900,500,130,lab);arrow((900,820),(x+250,900))
    elif kind=='use':
        dr.rectangle((530,40,1720,1020),outline='black',width=3)
        dr.text((1120,65),'Hệ thống WEB ANALYST',font=f,fill='black',anchor='mt')
        dr.ellipse((175,380,265,470),outline='black',width=4);dr.line((220,470,220,620),fill='black',width=4)
        dr.line((130,520,310,520),fill='black',width=4);dr.line((220,620,140,730),fill='black',width=4);dr.line((220,620,300,730),fill='black',width=4)
        dr.text((220,760),'Người dùng',font=f,fill='black',anchor='mt')
        for i,lab in enumerate(['Xác thực và hồ sơ','Tạo và chọn dataset','Nhập và tra cứu dữ liệu','Thống kê tổng quan','Phân nhóm RFM','Luật kết hợp và gợi ý']):
            y=150+i*140;box(750,y,770,100,lab,True);dr.line((320,550,750,y+50),fill='black',width=2)
    elif kind=='sequence':
        xs=[160,510,860,1210,1580];labels=['Trình duyệt','Controller','DatasetContext','Dịch vụ luật','Cơ sở dữ liệu']
        for x,lab in zip(xs,labels):
            box(x-140,30,280,100,lab)
            for y in range(140,1010,25):dr.line((x,y,x,y+12),fill='#777777',width=2)
        arrows=[(0,1,'Gửi tham số',200),(1,2,'Xác định dataset',300),(2,4,'Kiểm tra chủ sở hữu',400),(1,3,'Gọi analyze',520),(3,4,'Khóa dataset và tìm phiên',620),(3,4,'Đọc hoặc tính và lưu luật',740),(3,1,'Trả kết quả',860),(1,0,'Hiển thị Blade',970)]
        for a,b,lab,y in arrows:arrow((xs[a],y),(xs[b],y),lab)
    elif kind=='erd':
        nodes={'users':(730,20),'datasets':(730,190),'customers':(50,400),'products':(1310,400),'orders':(730,400),'order_items':(1030,660),'order_payments':(330,660),'Kết quả phân tích':(730,880)}
        for name,(x,y) in nodes.items():box(x,y,420,110,name)
        for a,b in [('users','datasets'),('datasets','customers'),('datasets','products'),('datasets','orders'),('customers','orders'),('orders','order_items'),('products','order_items'),('orders','order_payments'),('datasets','Kết quả phân tích')]:
            x,y=nodes[a];xx,yy=nodes[b]
            if a=='datasets' and b=='Kết quả phân tích':
                dr.line((x,245,500,245,500,935,730,935),fill='#555555',width=2);dr.text((505,845),'1 : N',font=small,fill='black')
            else:arrow((x+210,y+110),(xx+210,yy),'1 : N')
        dr.text((900,1040),'Các liên kết nghiệp vụ được ràng buộc cùng dataset_id',font=small,fill='black',anchor='mt')
    else:
        labs=['Trình duyệt và giao diện Blade','Route và Middleware xác thực','FormRequest và Controller','DatasetContext và các dịch vụ nghiệp vụ','Query Builder và cơ sở dữ liệu']
        for i,lab in enumerate(labs):
            y=30+i*210;box(250,y,1300,135,lab)
            if i<4:
                arrow((830,y+135),(830,y+210));arrow((970,y+210),(970,y+135))
    path=ASSETS/(key+'.png');im.save(path);return path

figures=[('Hình 3.1','flow'),('Hình 3.2','use'),('Hình 3.3','sequence'),('Hình 4.1','erd'),('Hình 4.2','mvc')]
for prefix,kind in figures:
    cap=next(p for p in doc.paragraphs if p.style.name=='Caption' and p.text.startswith(prefix))
    prev=cap._p.getprevious()
    while prev is not None:
        pp=Paragraph(prev,cap._parent)
        if pp.alignment!=WD_ALIGN_PARAGRAPH.CENTER or pp.style.name.startswith('Heading'):break
        prv=prev.getprevious();prev.getparent().remove(prev);prev=prv
    p=before(cap,'');p.alignment=WD_ALIGN_PARAGRAPH.CENTER;p.paragraph_format.first_line_indent=Cm(0)
    p.paragraph_format.keep_with_next=True;p.add_run().add_picture(str(diagram_png(prefix.replace(' ','_'),kind)),width=Cm(15))

# Fix running captions and build separate real Word lists of figures and tables.
for name in ['Report Table Caption','Report Figure Caption','Report Contents Title']:
    if name not in doc.styles:
        st=doc.styles.add_style(name,1)
        st.base_style=doc.styles['Caption'] if name!='Report Contents Title' else doc.styles['Heading 1']
        outline=OxmlElement('w:outlineLvl');outline.set(qn('w:val'),'9');st.element.get_or_add_pPr().append(outline)
find('MỤC LỤC').style='Report Contents Title'
for p in doc.paragraphs:
    if p.style.name=='Caption':p.paragraph_format.keep_with_next=True
for name,instr in [('DANH MỤC BẢNG BIỂU','TOC \\h \\z \\t "Report Table Caption,1"'),('DANH MỤC HÌNH ẢNH','TOC \\h \\z \\t "Report Figure Caption,1"')]:
    a=find(name);cur=a._p.getnext()
    while cur is not None:
        if cur.tag==qn('w:p') and Paragraph(cur,a._parent).style.name.startswith('Heading'):break
        nxt=cur.getnext();cur.getparent().remove(cur);cur=nxt
    pp=before(Paragraph(cur,a._parent),'')
    field=OxmlElement('w:fldSimple');field.set(qn('w:instr'),instr);pp._p.append(field)
table_no=0
for p in doc.paragraphs:
    if p.style.name=='Caption' and p.text.startswith('Bảng 4.'):
        table_no+=1;p.text=re.sub(r'^Bảng 4\.\d+',f'Bảng 4.{table_no}',p.text)
for label in ['Bảng','Hình']:
    n=0
    for p in doc.paragraphs:
        if p.style.name=='Caption' and p.text.startswith(label+' '):
            n+=1
            # Caption styles allow independent lists with stable chapter labels.
            txt=p.text; m=re.match(r'^(Bảng|Hình)\s+(\S+)\s+(.*)$',txt)
            if not m:continue
            _,number,title=m.groups();p.text=label+' '+number+'  '+title
            p.style='Report Table Caption' if label=='Bảng' else 'Report Figure Caption'

# Formal typography and table geometry.
for sec in doc.sections:
    sec.page_width=Cm(21);sec.page_height=Cm(29.7)
    sec.left_margin=Cm(3.5);sec.right_margin=Cm(2);sec.top_margin=Cm(2.5);sec.bottom_margin=Cm(2.5)
for s in doc.styles:
    if s.type==1:
        s.font.name='Times New Roman';s.font.color.rgb=RGBColor(0,0,0)
        if s.element.rPr is not None:
            rf=s.element.rPr.rFonts
            if rf is not None:
                for attr in list(rf.attrib):
                    if attr.endswith('Theme'):del rf.attrib[attr]
                for k in ['ascii','hAnsi','eastAsia','cs']:rf.set(qn('w:'+k),'Times New Roman')
        for border in list(s.element.iter(qn('w:pBdr'))):border.getparent().remove(border)
normal=doc.styles['Normal'];normal.font.size=Pt(13)
normal.paragraph_format.line_spacing=1.5;normal.paragraph_format.space_after=Pt(3)
doc.styles['Title'].font.size=Pt(18);doc.styles['Title'].font.bold=True
doc.styles['Subtitle'].font.size=Pt(16);doc.styles['Subtitle'].font.bold=True
for t in doc.tables:
    t.alignment=WD_TABLE_ALIGNMENT.CENTER
    # Columns sized for mixed identifiers and prose.
    if len(t.columns)==3:widths=[3.4,4.8,7.3]
    elif len(t.columns)==2:widths=[5,10.5]
    else:widths=[15.5/len(t.columns)]*len(t.columns)
    t.autofit=False
    for c,w in zip(t.columns,widths):c.width=Cm(w)
    for ri,row in enumerate(t.rows):
        trpr=row._tr.get_or_add_trPr()
        if ri==0:trpr.append(OxmlElement('w:tblHeader'))
        trpr.append(OxmlElement('w:cantSplit'))
        for ci,c in enumerate(row.cells):
            c.width=Cm(widths[ci]);c.vertical_alignment=WD_CELL_VERTICAL_ALIGNMENT.CENTER
            pr=c._tc.get_or_add_tcPr()
            for tag in ['tcBorders','tcMar','shd']:
                for old in list(pr.findall(qn('w:'+tag))):pr.remove(old)
            borders=OxmlElement('w:tcBorders')
            for edge in ['top','left','bottom','right']:
                e=OxmlElement('w:'+edge)
                for k,v in [('val','single'),('sz','4'),('color','D9D9D9')]:e.set(qn('w:'+k),v)
                borders.append(e)
            pr.append(borders)
            mar=OxmlElement('w:tcMar')
            for edge in ['top','left','bottom','right']:
                e=OxmlElement('w:'+edge);e.set(qn('w:w'),'90');e.set(qn('w:type'),'dxa');mar.append(e)
            pr.append(mar)
            if ri==0:
                sh=OxmlElement('w:shd');sh.set(qn('w:fill'),'E7E6E6');pr.append(sh)
            for p in c.paragraphs:
                p.paragraph_format.first_line_indent=Cm(0);p.paragraph_format.line_spacing=1.15
                p.paragraph_format.space_after=Pt(3)
                for r in p.runs:r.font.size=Pt(11);r.bold=(ri==0)
for p in doc.paragraphs:
    for border in list(p._p.iter(qn('w:pBdr'))):border.getparent().remove(border)
    p.paragraph_format.widow_control=True
    if p.style.name.startswith('Heading'):
        p.paragraph_format.first_line_indent=Cm(0)
    if p.style.name in ['Caption','Report Table Caption','Report Figure Caption']:
        p.paragraph_format.first_line_indent=Cm(0);p.paragraph_format.line_spacing=1.15
        p.alignment=WD_ALIGN_PARAGRAPH.CENTER
        p.paragraph_format.keep_with_next=p.text.startswith('Bảng')
doc.core_properties.title='Xây dựng hệ thống phân tích dữ liệu thương mại điện tử và hỗ trợ gợi ý mua kèm'
doc.core_properties.subject='Báo cáo đồ án cuối khóa WEB ANALYST'
doc.save(OUT)
print(str(OUT))
print('Paragraphs',len(doc.paragraphs),'tables',len(doc.tables))
