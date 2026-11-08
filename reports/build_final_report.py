import ast, re
from pathlib import Path
from docx import Document
from docx.shared import Cm, Pt, RGBColor
from docx.oxml import OxmlElement
from docx.oxml.ns import qn
from docx.enum.text import WD_ALIGN_PARAGRAPH
from docx.enum.section import WD_SECTION_START

ROOT=Path(__file__).parent
old=(ROOT/'build_report.py').read_text(encoding='utf-8')
chapters=next(ast.literal_eval(n.value) for n in ast.parse(old).body if isinstance(n,ast.Assign) and any(isinstance(t,ast.Name) and t.id=='chapters' for t in n.targets))
exec(old[:old.index('for text,size,bold in')])
doc.core_properties.title='Xây dựng hệ thống phân tích dữ liệu thương mại điện tử và hỗ trợ gợi ý mua kèm'
doc.styles['Normal'].paragraph_format.space_after=Pt(3)
for st in ['TOC 1','TOC 2','TOC 3']:
 try: s=doc.styles[st]
 except KeyError: s=doc.styles.add_style(st,1)
 s.font.name='Times New Roman';s.font.size=Pt(12);s.font.color.rgb=RGBColor(0,0,0)
 s.paragraph_format.line_spacing=1.15;s.paragraph_format.space_after=Pt(3)
 s.paragraph_format.first_line_indent=Cm(0)

def center(t,size=14,bold=False):
 x=doc.add_paragraph();x.alignment=WD_ALIGN_PARAGRAPH.CENTER;x.paragraph_format.first_line_indent=Cm(0)
 r=x.add_run(t);r.font.size=Pt(size);r.bold=bold
 return x
def page_section(fmt):
 s=doc.add_section(WD_SECTION_START.NEW_PAGE);s.footer.is_linked_to_previous=False
 num=OxmlElement('w:pgNumType');num.set(qn('w:fmt'),fmt);num.set(qn('w:start'),'1');s._sectPr.append(num)
 x=s.footer.paragraphs[0];x.alignment=WD_ALIGN_PARAGRAPH.CENTER;x.paragraph_format.first_line_indent=Cm(0);field(x,'PAGE')
def write(title,text,level=2):
 h(title,level)
 for t in text.split('|'):p(t)
def source_sections(ch,indices,prefix):
 for i,key in enumerate(indices,1):
  matches=[(a,b) for a,b in chapters[ch][1] if a.startswith(key+' ') or a.startswith(key+'. ')]
  for a,b in matches:
   a=re.sub(r'^\S+\s+',f'{prefix}.{i}. ',a)
   write(a,b)
def figure(title,lines):diagram(title,lines)

center('[TÊN TRƯỜNG]',16,True);center('[KHOA VÀ BỘ MÔN]',14,True)
center('\nBÁO CÁO ĐỒ ÁN CUỐI KHÓA',18,True)
x=doc.add_paragraph('XÂY DỰNG HỆ THỐNG PHÂN TÍCH DỮ LIỆU THƯƠNG MẠI ĐIỆN TỬ VÀ HỖ TRỢ GỢI Ý MUA KÈM','Title');x.alignment=WD_ALIGN_PARAGRAPH.CENTER;x.paragraph_format.first_line_indent=Cm(0)
center('Tên ứng dụng  WEB ANALYST',14,True)
center('\nNgành: [Tên ngành]\nChuyên ngành: [Tên chuyên ngành]',13)
center('\nSinh viên thực hiện: [Họ và tên]\nMã số sinh viên: [MSSV]\nLớp: [Tên lớp]\nGiảng viên hướng dẫn: [Họ tên và học vị]',13)
center('\n[Địa danh], năm [Năm bảo vệ]',13,True)
page_section('lowerRoman')
write('LỜI CẢM ƠN','Tôi xin cảm ơn quý thầy cô [Tên khoa], [Tên trường] đã trang bị kiến thức và phương pháp học tập trong suốt khóa học. Tôi xin cảm ơn [Họ tên giảng viên hướng dẫn] đã hướng dẫn việc xác định bài toán, xây dựng ứng dụng và hoàn thiện báo cáo.|Tôi xin cảm ơn gia đình và những người đã hỗ trợ quá trình thực hiện đồ án. Những góp ý của thầy cô là cơ sở để tôi tiếp tục hoàn thiện sản phẩm và phương pháp nghiên cứu.',1)
write('TÓM TẮT ĐỒ ÁN','Đề tài xây dựng ứng dụng web phục vụ quản lý và phân tích dữ liệu giao dịch thương mại điện tử. Người dùng tổ chức dữ liệu theo bộ dữ liệu cá nhân, nhập thông tin khách hàng, sản phẩm, đơn hàng, chi tiết đơn hàng và thanh toán, sau đó khai thác thống kê tổng quan, phân nhóm RFM và luật kết hợp giữa các danh mục.|Ứng dụng tổ chức theo kiến trúc MVC với Laravel, các lớp dịch vụ xử lý nghiệp vụ và giao diện Blade. Phân tích giỏ hàng sử dụng truy vấn SQL đếm đồng xuất hiện cặp danh mục; gợi ý mua kèm được tạo từ các luật đạt ngưỡng. Báo cáo phân tích yêu cầu, mô hình hóa quy trình, thiết kế dữ liệu, mô tả giao diện và đề xuất phương pháp kiểm chứng tính đúng đắn.|Kết quả xây dựng tạo nền tảng cho việc xem xét hành vi mua hàng và lựa chọn ứng viên bán kèm. Các luật là quan hệ thống kê; hiệu quả kinh doanh của đề xuất cần được đánh giá bằng thực nghiệm riêng.|Từ khóa: thương mại điện tử; phân tích giỏ hàng; luật kết hợp; RFM; Laravel.',1)
h('MỤC LỤC',1);field(doc.add_paragraph(),'TOC \\o "1-3" \\h \\z \\u')
h('DANH MỤC BẢNG BIỂU',1);field(doc.add_paragraph(),'TOC \\h \\z \\t "Caption,1"')
h('DANH MỤC HÌNH ẢNH',1)
p('Hình 3.1  Luồng nghiệp vụ đề xuất\nHình 3.2  Ca sử dụng tổng quát\nHình 3.3  Trình tự phân tích luật\nHình 4.1  Quan hệ thực thể nghiệp vụ\nHình 4.2  Kiến trúc xử lý MVC')
h('DANH MỤC VIẾT TẮT',1)
table('Danh mục từ viết tắt',['Từ','Diễn giải'],[['MVC','Model View Controller'],['RFM','Recency Frequency Monetary'],['CSV','Comma Separated Values'],['SQL','Structured Query Language'],['ERD','Entity Relationship Diagram'],['UI','User Interface'],['DSS','Decision Support System']])
page_section('decimal')
h('PHẦN I  MỞ ĐẦU',1)
source_sections(0,['1.1.','1.3.','1.4.','1.5.','1.6.'],'0')
write('6. Ý nghĩa thực tiễn','Một giao diện tập trung giúp người dùng giảm thao tác ghép dữ liệu và đối chiếu thủ công. Việc trình bày số giỏ hỗ trợ, độ tin cậy và độ nâng tạo căn cứ kiểm tra đề xuất. Phân nhóm RFM bổ sung góc nhìn theo khách hàng, trong khi luật kết hợp bổ sung góc nhìn theo giỏ hàng. Hai kết quả phục vụ các câu hỏi khác nhau và không thay thế cho nhau.')
write('7. Nội dung và thời gian thực hiện','Nội dung thực hiện gồm phân tích mã nguồn và yêu cầu, xác định cấu trúc dữ liệu, xây dựng luồng nhập và tra cứu, phân tích RFM, tính luật kết hợp, gợi ý mua kèm và kiểm thử.|Thời gian thực hiện chính thức: [Ngày bắt đầu] đến [Ngày kết thúc]. Các mốc nghiệm thu: khảo sát yêu cầu; hoàn thiện thiết kế; hoàn thiện chức năng; kiểm thử và chỉnh sửa; hoàn thiện báo cáo. Ngày thực tế của từng mốc được ghi theo kế hoạch học phần.')
h('PHẦN II  NỘI DUNG',1)
h('CHƯƠNG 1  TỔNG QUAN BÀI TOÁN VÀ HỆ THỐNG',1)
write('1.1. Bối cảnh và đối tượng sử dụng','Hệ thống hướng đến người quản lý hoặc người phân tích dữ liệu thương mại điện tử có các tệp giao dịch cần tổng hợp. Đây là bối cảnh bài toán của ứng dụng; không gán dự án cho một doanh nghiệp cụ thể khi chưa có hồ sơ khảo sát. Người dùng quản lý dữ liệu của chính tài khoản mình và lựa chọn bộ dữ liệu trước khi phân tích.')
source_sections(0,['1.2.'],'1.1')
write('1.2. Hiện trạng xử lý dữ liệu','Khi dữ liệu phân tán ở các tệp, người phân tích phải xác định khóa nối, kiểm tra dữ liệu thiếu và loại bản ghi trùng trước khi tổng hợp. Việc dùng trực tiếp số dòng chi tiết làm số đơn hoặc cộng thanh toán sau khi nối nhiều bảng có thể làm sai chỉ số. Hệ thống cần thống nhất đơn vị quan sát và phạm vi bộ dữ liệu cho mọi phép tính.')
source_sections(1,['2.2.','2.3.','2.3.1.','2.3.2.','2.3.3.','2.3.4.','2.3.5.'],'1.3')
write('1.4. Quy tắc nghiệp vụ','Mỗi bộ dữ liệu thuộc một tài khoản; các khóa nghiệp vụ được phân biệt trong phạm vi dataset_id. Nhập bảng cha trước bảng con để bảo đảm khóa ngoại. Giao dịch phân tích loại trạng thái canceled và unavailable; trạng thái null vẫn có thể được chấp nhận theo mã hiện tại.|Giỏ hàng được biểu diễn bằng tập danh mục phân biệt trong một đơn. Danh mục trống bị loại; giỏ một danh mục vẫn tham gia mẫu số. Gợi ý loại danh mục đã chọn và giữ tối đa mười hệ quả khác nhau theo thứ tự luật đã xếp hạng.')
write('1.5. Quy trình nghiệp vụ hiện tại','Quy trình đầu vào bắt đầu từ việc chuẩn bị tệp, xác định quan hệ giữa các bảng và kiểm tra các mã tham chiếu. Sau nhập, người dùng tra cứu và chọn công cụ phân tích phù hợp với câu hỏi. Luật có số quan sát thấp cần được kiểm tra kỹ trước khi sử dụng trong một quyết định bán kèm.')
write('1.6. Tiểu kết chương','Bài toán yêu cầu phối hợp quản lý dữ liệu và phân tích có thể giải thích. Tính đúng đắn của khóa nối, chính sách lọc và mẫu số giao dịch là điều kiện để kết quả có ý nghĩa.')
h('CHƯƠNG 2  KHẢO SÁT VÀ XÁC ĐỊNH YÊU CẦU HỆ THỐNG',1)
write('2.1. Thu thập yêu cầu','Yêu cầu được xác lập bằng việc đối chiếu tuyến truy cập, controller, dịch vụ, giao diện và các ca kiểm thử trong dự án. Phỏng vấn người dùng nghiệp vụ là hoạt động cần triển khai khi đưa hệ thống vào tổ chức cụ thể. Phiếu hỏi ở phụ lục phục vụ bước thu thập này.|Đối tượng khảo sát dự kiến gồm người quản lý kinh doanh và người phân tích dữ liệu. Các câu hỏi tập trung vào nguồn tệp, tần suất nhập, khái niệm khách hàng, trạng thái đơn hợp lệ và cách đánh giá tính hữu ích của gợi ý.')
write('2.2. Kết quả khảo sát chức năng hiện có','Mã nguồn có các tuyến đăng ký, đăng nhập, quản lý hồ sơ, tạo và chọn bộ dữ liệu, nhập năm nhóm dữ liệu nghiệp vụ, thống kê, phân nhóm khách hàng, luật kết hợp và gợi ý. Không có cơ sở để báo cáo tỷ lệ hài lòng hoặc số người tham gia phỏng vấn. Các nhu cầu ưu tiên dưới đây được rút ra từ phạm vi chức năng đã xây dựng.')
table('Bảng 2.1  Yêu cầu chức năng',['Mã','Chức năng','Tiêu chí chấp nhận'],[['F01','Xác thực','Bảo vệ trang nghiệp vụ bằng auth'],['F02','Quản lý dataset','Chỉ chọn dữ liệu thuộc tài khoản'],['F03','Nhập dữ liệu','Kiểm tra cột, khóa và kiểu dữ liệu'],['F04','Tra cứu và thống kê','Kết quả giới hạn trong dataset'],['F05','Phân nhóm RFM','Tính theo customer_unique_id'],['F06','Luật kết hợp','Lọc Support Confidence Lift'],['F07','Gợi ý mua kèm','Loại danh mục đã chọn và trùng lặp']])
write('2.3. Yêu cầu phi chức năng','Tính toàn vẹn yêu cầu không ghi một phần tệp khi có lỗi; tính bảo mật yêu cầu xác minh quyền sở hữu phía máy chủ. Khả năng sử dụng yêu cầu thông báo lỗi có vị trí và thông tin ngưỡng rõ ràng. Khả năng bảo trì yêu cầu tách lớp dịch vụ phân tích khỏi controller.|Hiệu năng phải được đo với số bản ghi, số giỏ và độ dài giỏ cụ thể. Phân trang SQL được ưu tiên cho danh sách; các chỉ tiêu thời gian phản hồi cần được xác định sau phép đo thay vì đưa ra một con số chưa kiểm chứng.')
write('2.4. Tiểu kết chương','Yêu cầu được tổ chức quanh luồng dữ liệu và quyền sở hữu. Tiêu chí nghiệm thu phải kiểm chứng đồng thời đầu ra phân tích, nhập liệu và cô lập bộ dữ liệu.')
h('CHƯƠNG 3  PHÂN TÍCH HỆ THỐNG',1)
write('3.1. Quy trình nghiệp vụ đề xuất','Người dùng đăng nhập, tạo hoặc chọn dataset, nhập các tệp theo quan hệ phụ thuộc và đối chiếu thống kê đầu vào. Sau đó người dùng xem RFM hoặc thiết lập ngưỡng để tính luật và tạo gợi ý. Khi kết quả không có luật, cần kiểm tra dữ liệu và ngưỡng trước khi đưa ra nhận định nghiệp vụ.')
figure('Hình 3.1  Luồng nghiệp vụ đề xuất',['Đăng nhập → Tạo hoặc chọn bộ dữ liệu','↓','Nhập khách hàng và sản phẩm → Nhập đơn hàng','↓','Nhập chi tiết đơn và thanh toán → Đối chiếu dữ liệu','↓','Thống kê tổng quan → RFM hoặc luật kết hợp → Gợi ý'])
write('3.2. Mô hình hóa yêu cầu','Tác nhân chính là người dùng đã đăng nhập. Khách chưa đăng nhập có thể truy cập các trang công khai và thực hiện xác thực. Mã nguồn không cho thấy mô hình quản trị viên riêng cho việc phân quyền quản trị toàn hệ thống; quyền đối với dữ liệu gắn với chủ sở hữu dataset.')
figure('Hình 3.2  Ca sử dụng tổng quát',['Khách → Đăng ký và đăng nhập','Người dùng → Quản lý hồ sơ và bộ dữ liệu','Người dùng → Nhập và tra cứu dữ liệu','Người dùng → Xem thống kê và phân nhóm RFM','Người dùng → Phân tích luật và xem gợi ý mua kèm'])
usecases=[('UC01','Tạo và chọn bộ dữ liệu','Người dùng đã đăng nhập','Nhập tên và thông tin mô tả; hệ thống kiểm tra đầu vào, lưu chủ sở hữu; người dùng chọn dataset để làm việc.','Tên không hợp lệ bị từ chối; dataset không thuộc tài khoản không được sử dụng.'),('UC02','Nhập dữ liệu nghiệp vụ','Đã chọn dataset và có bảng cha phù hợp','Chọn đúng nhóm dữ liệu, tải tệp; hệ thống đọc tiêu đề, kiểm tra dòng, kiểm tra mã tham chiếu và ghi trong giao dịch.','Thiếu cột, trùng khóa hoặc sai tham chiếu dẫn đến thông báo lỗi và hủy thay đổi.'),('UC03','Phân tích luật kết hợp','Có đơn và danh mục hợp lệ','Người dùng nhập ba ngưỡng; hệ thống chuẩn hóa giỏ, đếm mục và cặp, tính chỉ số và trả danh sách xếp hạng.','Ngưỡng ngoài miền bị từ chối; không có giỏ hợp lệ trả tập kết quả rỗng.'),('UC04','Phân nhóm khách hàng','Có khách hàng định danh và đơn có thời điểm mua','Người dùng chọn ngày tham chiếu; hệ thống tổng hợp thanh toán theo đơn, tính RFM và phân nhóm theo thứ tự ưu tiên.','Bản ghi không có định danh duy nhất hoặc ngày mua không tham gia tập phân tích.'),('UC05','Gợi ý mua kèm','Có luật đạt ngưỡng và danh mục đầu vào','Người dùng chọn danh mục; hệ thống chọn luật có tiền đề phù hợp, bỏ hệ quả đã chọn, loại trùng và giữ tối đa mười kết quả.','Không có luật phù hợp trả danh sách rỗng để người dùng điều chỉnh lựa chọn.')]
for i,(code,name,pre,flow,error) in enumerate(usecases,1):
 write(f'3.2.{i}. {code} {name}',f'Tiền điều kiện: {pre}.|Luồng chính: {flow}|Luồng ngoại lệ: {error}|Hậu điều kiện: dữ liệu hoặc kết quả chỉ thuộc bộ dữ liệu người dùng được phép truy cập.',3)
write('3.3. Sơ đồ trình tự phân tích','Trình duyệt gửi yêu cầu đến route có middleware auth. FormRequest xác nhận miền tham số; controller yêu cầu DatasetContext giải quyết dataset và gọi AssociationRules. Dịch vụ truy vấn cơ sở dữ liệu, tính kết quả rồi chuyển cho giao diện. Một mã dataset hợp lệ về kiểu vẫn phải được kiểm tra quyền sở hữu.')
figure('Hình 3.3  Trình tự phân tích luật',['Trình duyệt → auth → AssociationRuleRequest','↓','Controller → DatasetContext → Kiểm tra chủ sở hữu','↓','AssociationRules → SQL đếm giỏ mục và cặp','↓','Lọc và xếp hạng → Controller → Blade → Trình duyệt'])
write('3.4. Phân tích thuật toán và độ phức tạp','Mỗi giỏ có k danh mục phân biệt tạo k(k−1)/2 cặp không có hướng. Tổng số cặp phụ thuộc phân bố độ dài giỏ, vì vậy dữ liệu nhiều giỏ dài có thể tốn chi phí nối lớn dù cùng số đơn. Mỗi cặp sau đếm tạo hai luật có hướng.|Apriori [1] là cơ sở lý thuyết cho khai phá tập mục phổ biến. Phiên bản hiện tại đếm trực tiếp cặp bằng SQL, không triển khai Apriori đầy đủ và không sinh luật có nhiều danh mục trong một vế. Mở rộng tập mục nhiều phần tử cần lựa chọn và kiểm chứng thuật toán riêng.')
write('3.5. Tiểu kết chương','Các ca sử dụng làm rõ điều kiện, luồng chính và ngoại lệ. Phân tách xác thực, quyền sở hữu và xử lý phân tích giúp kiểm tra từng trách nhiệm và giảm sai sót khi bổ sung chức năng.')
h('CHƯƠNG 4  THIẾT KẾ VÀ XÂY DỰNG HỆ THỐNG',1)
write('4.1. Thiết kế cơ sở dữ liệu','Nhóm thực thể cốt lõi gồm users, datasets, customers, products, orders, order_items và order_payments. Các bảng phụ trợ có trong lược đồ không đồng nghĩa chức năng tương ứng đã được nối vào luồng ứng dụng. Thiết kế ở đây tập trung vào các bảng được sử dụng trực tiếp bởi controller và dịch vụ.')
figure('Hình 4.1  Quan hệ thực thể nghiệp vụ',['users 1 → N datasets','datasets 1 → N customers và products và orders','customers 1 → N orders','orders 1 → N order_items N ← 1 products','orders 1 → N order_payments','Các khóa ngoại nghiệp vụ cùng dataset_id'])
table('Bảng 4.1  Khóa và liên kết nghiệp vụ',['Bảng','Khóa','Quan hệ'],[['datasets','dataset_id','user_id tham chiếu users'],['customers','dataset_id customer_id','Thuộc một dataset'],['products','dataset_id product_id','Thuộc một dataset'],['orders','dataset_id order_id','Khách hàng cùng dataset'],['order_items','dataset_id order_id order_item_id','Đơn hàng và sản phẩm cùng dataset'],['order_payments','dataset_id order_id payment_sequential','Đơn hàng cùng dataset']])
write('4.1.1. Ràng buộc toàn vẹn','Migration mở rộng phạm vi dữ liệu chuyển các khóa nghiệp vụ sang khóa ghép dataset_id. Các bản ghi cũ được đưa vào dataset chưa xác định chủ sở hữu để tránh tự ý gán quyền. Migration nâng cấp nhắm đến lược đồ MySQL có sẵn, không phải quy trình tạo mới hoàn chỉnh trên mọi hệ quản trị.|Khóa ngoại ghép ngăn một dòng hàng tham chiếu sang sản phẩm của dataset khác. Việc xóa dữ liệu cần tuân thủ quan hệ tham chiếu; không thực hiện gộp ngược dataset có mã nghiệp vụ trùng bằng một migration hạ cấp tự động.',3)
write('4.2. Thiết kế kiến trúc MVC','Route xác định điểm vào; middleware bảo vệ truy cập; FormRequest kiểm tra tham số; controller điều phối; các lớp dịch vụ chịu trách nhiệm nghiệp vụ. Blade trình bày đầu ra và biểu mẫu. Dù Laravel hỗ trợ ORM, phần phân tích của dự án chủ yếu sử dụng Query Builder và SQL tổng hợp.')
figure('Hình 4.2  Kiến trúc xử lý MVC',['Trình duyệt và giao diện Blade','↕','Route → Middleware → FormRequest → Controller','↕','DatasetContext và các dịch vụ phân tích','↕','Query Builder → Cơ sở dữ liệu quan hệ'])
source_sections(3,['4.1.','4.2.','4.3.','4.3.1.','4.3.3.','4.3.4.','4.3.5.','4.4.','4.5.1.','4.5.2.','4.5.3.'],'4.2')
write('4.3. Thiết kế chức năng phân nhóm RFM','Recency là số ngày giữa ngày tham chiếu và lần mua gần nhất. Nếu không chỉ định ngày, hệ thống chọn ngày mua mới nhất của tập đơn có định danh hợp lệ. Frequency là số đơn theo customer_unique_id; Monetary là tổng thanh toán đã tổng hợp theo từng đơn trước khi nối, nhằm tránh nhân bản số đơn.|Điểm R lần lượt là 5, 4, 3, 2, 1 theo các mốc không quá 30, 60, 90, 180 và trên 180 ngày. Điểm F bằng số đơn và chặn trên tại 5. Điểm M dùng bốn ngưỡng phân vị 20%, 40%, 60%, 80% và so sánh lớn hơn nghiêm ngặt; các ngưỡng trùng có thể làm thiếu một số mức điểm. Monetary không dương nhận điểm 1.')
table('Bảng 4.2  Quy tắc nhóm RFM theo thứ tự ưu tiên',['Nhóm','Điều kiện'],[['Giá trị cao','R ≥ 4 F ≥ 3 M ≥ 4'],['Trung thành','R ≥ 3 F ≥ 3 sau nhóm trên'],['Mới','R ≥ 4 F = 1'],['Nguy cơ rời bỏ','R ≤ 2 F ≥ 2'],['Ít hoạt động','R ≤ 2 sau nhóm trên'],['Tiềm năng','Các trường hợp còn lại']])
write('4.4. Thiết kế luật kết hợp và gợi ý','AssociationRules chuẩn hóa danh mục, loại lặp trong một đơn và đếm số giỏ hợp lệ N. Support của cặp bằng số giỏ chứa cả hai mục chia N, Confidence chia cho số giỏ chứa tiền đề, Lift bằng Confidence dạng thập phân chia Support của hệ quả. Tham số Support và Confidence trên giao diện sử dụng đơn vị phần trăm.|Luật được xếp giảm theo Lift, Confidence và số giỏ mua chung; tên tiền đề và hệ quả dùng để phân xử khi bằng nhau. ProductRecommendations chọn luật có tiền đề trong các mục đã chọn, loại hệ quả đã chọn, giữ một kết quả cho mỗi hệ quả và giới hạn mười kết quả. Việc chọn nhiều mục không tạo ra một luật tiền đề nhiều mục; mỗi ứng viên vẫn dựa trên một luật cặp.')
table('Bảng 4.3  Miền tham số phân tích',['Tham số','Miền hợp lệ','Ý nghĩa'],[['Support','0,01 đến 100 %','Tỷ lệ giỏ chứa cặp'],['Confidence','0 đến 100 %','Tỷ lệ có hệ quả khi có tiền đề'],['Lift','0 đến 1000','Mức đồng xuất hiện so với độc lập']])
write('4.5. Thiết kế giao diện người dùng','Giao diện sử dụng các nhóm trang tài khoản, dữ liệu và phân tích. Các mô tả dưới đây đối chiếu với các tệp Blade của ứng dụng; ảnh chụp màn hình phục vụ bảo vệ cần lấy từ bản chạy đã nhập bộ dữ liệu được phép sử dụng.')
screens=[('Đăng nhập và hồ sơ','login.blade.php và profile/edit.blade.php','Biểu mẫu xác thực, phản hồi lỗi và cập nhật thông tin tài khoản. Các thao tác dữ liệu chỉ thực hiện sau đăng nhập.'),('Quản lý bộ dữ liệu','datasets.blade.php','Tạo và chọn dataset. Bộ dữ liệu đang chọn xác định phạm vi của các trang phân tích tiếp theo.'),('Danh sách khách hàng và sản phẩm','customers.blade.php và products.blade.php','Tra cứu bảng dữ liệu, phân trang và tải tệp nhập tương ứng. Thông báo lỗi phải giúp xác định dữ liệu cần sửa.'),('Đơn hàng và chi tiết đơn','orders.blade.php và order_items.blade.php','Hiển thị bản ghi đơn và dòng hàng; hai cấp dữ liệu có đơn vị đếm khác nhau.'),('Thanh toán','order_payments.blade.php','Tra cứu các lần thanh toán của đơn; một đơn có thể có nhiều dòng thanh toán.'),('Trang tổng quan','home.blade.php','Tóm tắt dữ liệu và chỉ số kinh doanh theo phạm vi mà HomeAnalytics thực hiện. Cần đối chiếu khái niệm tổng thanh toán với doanh thu kế toán trước khi sử dụng ngoài phạm vi đồ án.'),('Phân nhóm khách hàng','customer_segments.blade.php','Hiển thị phân nhóm RFM, ngày tham chiếu và danh sách khách hàng có phân trang; hỗ trợ lọc để xem nhóm quan tâm.'),('Luật kết hợp','association_rules.blade.php','Nhập Support Confidence Lift và xem các chỉ số cùng số giỏ hỗ trợ. Trạng thái không có kết quả cần được trình bày rõ.'),('Gợi ý mua kèm','product_recommendations.blade.php','Chọn các danh mục đầu vào và xem ứng viên mua kèm có căn cứ từ luật. Danh mục đã có trong lựa chọn không xuất hiện lại trong kết quả.')]
for i,(name,file,txt) in enumerate(screens,1):write(f'4.5.{i}. Giao diện {name.lower()}',txt+'|Thành phần giao diện: '+file+'.',3)
write('4.6. Kiểm thử và đánh giá','Kiểm chứng gồm đối chiếu phép tính tay với dịch vụ, kiểm tra phân quyền, nhập dữ liệu lỗi và tính nhất quán của tổng hợp. Các tệp kiểm thử sẵn có bao phủ luật kết hợp, gợi ý, RFM, nhập liệu và dataset. Sự tồn tại của ca kiểm thử không tự chứng minh chúng đạt trong môi trường triển khai.')
source_sections(4,['5.2.','5.3.1.','5.3.2.','5.3.3.','5.4.','5.4.1.','5.4.2.','5.6.'],'4.6')
table('Bảng 4.4  Đối chiếu kiểm chứng và phạm vi',['Nội dung','Bằng chứng','Giới hạn'],[['Luật kết hợp','AssociationRuleTest.php','Cần nhật ký chạy kiểm thử'],['RFM','CustomerSegmentTest.php','Chưa đo hiệu quả chiến dịch'],['Gợi ý','ProductRecommendationTest.php','Gợi ý danh mục từ luật cặp'],['Cô lập dữ liệu','DatasetIsolationTest.php','Cần kiểm tra cấu hình triển khai'],['Hiệu năng','Kế hoạch đo trên dữ liệu cố định','Chưa có số đo thực nghiệm']])
write('4.7. Tiểu kết chương','Thiết kế đặt phạm vi dataset xuyên suốt tầng truy vấn và xử lý. RFM, luật kết hợp và gợi ý có quy tắc riêng, được tách thành dịch vụ để thuận tiện đối chiếu. Đánh giá tiếp theo cần bổ sung ảnh bản chạy và nhật ký thực nghiệm.')
h('CHƯƠNG 5  KẾT LUẬN',1)
write('5.1. Kết quả đạt được','Dự án đã xây dựng các thành phần quản lý tài khoản và bộ dữ liệu, nhập và tra cứu dữ liệu giao dịch, thống kê tổng quan, phân nhóm khách hàng RFM, tính luật cặp danh mục và gợi ý mua kèm. Luồng xử lý tách controller và dịch vụ, đồng thời ràng buộc phạm vi truy vấn theo bộ dữ liệu.|Bộ ví dụ tính tay cho phép xác định kết quả kỳ vọng của Support Confidence Lift. Cấu trúc kiểm thử hiện có là nền tảng kiểm chứng các tình huống dữ liệu và quyền truy cập. Kết luận nghiệm thu cần được hoàn tất với kết quả chạy kiểm thử và trình diễn thực tế.')
write('5.2. Hạn chế','Mô-đun luật hiện giới hạn một danh mục ở mỗi vế và chưa khai phá tập mục nhiều phần tử. Gợi ý không xét tồn kho, biên lợi nhuận hay mức giảm giá tối ưu. RFM sử dụng ngưỡng và thứ tự nhóm do ứng dụng quy định, cần hiệu chỉnh nếu áp dụng cho một mô hình kinh doanh khác.|Chưa có bằng chứng thực nghiệm đầy đủ về hiệu năng trên dữ liệu lớn hoặc tác động đến doanh thu. Migration phụ thuộc lược đồ MySQL nền có sẵn làm tăng yêu cầu chuẩn bị khi triển khai mới. Các số liệu khảo sát người dùng và thời gian thực hiện chính thức cần được bổ sung theo hồ sơ đồ án.')
write('5.3. Đề xuất phát triển','Ưu tiên hoàn thiện quy trình triển khai tái lập, nhật ký kiểm thử và thực nghiệm trên bộ dữ liệu cố định. Bổ sung lưu cấu hình phiên phân tích, mã băm dữ liệu và xuất kết quả để đối chiếu giữa các lần chạy.|Ở lớp phân tích, có thể mở rộng tập mục nhiều phần tử, lọc theo thời gian và theo dõi độ ổn định của luật. Ở lớp ứng dụng, có thể bổ sung điều kiện tồn kho, lợi nhuận và đánh giá thử nghiệm đối chứng cho gợi ý. Mỗi hướng phát triển cần có tiêu chí đo và kiểm tra quyền dữ liệu tương ứng.')
h('TÀI LIỆU THAM KHẢO',1)
for t in ['[1] R. Agrawal và R. Srikant. Fast Algorithms for Mining Association Rules. VLDB, 1994, tr. 487–499. https://rsrikant.com/papers/vldb94.pdf','[2] Olist. Brazilian E-Commerce Public Dataset by Olist. https://www.kaggle.com/datasets/olistbr/brazilian-ecommerce. Nguồn dữ liệu tham chiếu; số liệu thực nghiệm phải gắn với tệp thực tế được nhập.','[3] Dự án web_analyst. routes/web.php; composer.json; app/Services; app/Http/Controllers; app/Http/Requests; resources/views; database/migrations; tests. Phiên bản mã nguồn khảo sát ngày 26/10/2026.']:p(t)
page_section('lowerRoman');h('PHỤ LỤC',1)
write('Phụ lục 1. Phiếu phỏng vấn người quản lý','Đơn vị và người trả lời: [Bổ sung]. Thời điểm: [Bổ sung].|Câu 1. Các tệp giao dịch được xuất từ đâu và cập nhật với tần suất nào?|Câu 2. Đơn hàng nào được xem là hợp lệ khi phân tích? Có xử lý đơn hủy và hoàn tiền riêng không?|Câu 3. Chỉ số nào được dùng để đánh giá một đề xuất mua kèm?|Câu 4. Có yêu cầu giới hạn theo thời gian, danh mục hoặc tồn kho không?|Câu 5. Ai được quyền xem và chia sẻ dữ liệu? Cách xác nhận chủ sở hữu là gì?')
write('Phụ lục 2. Phiếu khảo sát người phân tích','Người trả lời: [Bổ sung].|Câu 1. Những lỗi tệp nào xuất hiện thường xuyên nhất?|Câu 2. Cần xuất kết quả theo biểu mẫu nào?|Câu 3. Các thuật ngữ Support Confidence Lift có cần giải thích trực tiếp trên giao diện không?|Câu 4. Việc lọc nhóm khách hàng và chọn ngày tham chiếu đã đáp ứng công việc chưa?|Câu 5. Thời gian phản hồi chấp nhận được cho quy mô dữ liệu thực tế là bao nhiêu?')
write('Phụ lục 3. Từ điển dữ liệu cốt lõi','Các thuộc tính dưới đây phục vụ diễn giải lược đồ nghiệp vụ. Kiểu vật lý và ràng buộc chi tiết phải đối chiếu lược đồ nền cùng migration nâng cấp khi triển khai.')
table('Bảng PL1  Từ điển trường nghiệp vụ',['Trường','Vai trò','Quy tắc chính'],[['dataset_id','Phạm vi dữ liệu','Tham gia khóa và điều kiện truy vấn'],['customer_id','Khách hàng trong đơn','Duy nhất trong dataset'],['customer_unique_id','Gộp định danh RFM','Trim và loại giá trị rỗng'],['product_id','Mã sản phẩm','Duy nhất trong dataset'],['product_category_name','Mục phân tích giỏ','Loại trống và loại lặp trong đơn'],['order_id','Mã giao dịch','Duy nhất trong dataset'],['order_status','Trạng thái đơn','Loại canceled unavailable khi phân tích'],['order_purchase_timestamp','Thời điểm mua','Cần có cho RFM'],['order_item_id','Số thứ tự dòng hàng','Phân biệt trong đơn'],['payment_value','Giá trị thanh toán','Tổng hợp theo đơn trước khi nối'],['payment_sequential','Số thứ tự thanh toán','Phân biệt trong đơn']])
write('Phụ lục 4. Thiết kế đầu vào và kiểm tra hợp lệ','Đầu vào đăng nhập gồm thông tin tài khoản và mật khẩu. Đầu vào dataset gồm tên và mô tả theo biểu mẫu. Đầu vào nhập liệu gồm tệp phù hợp nhóm dữ liệu. Không đưa mật khẩu, chuỗi kết nối hoặc nội dung .env vào báo cáo.|Đầu vào luật gồm Support Confidence Lift trong miền quy định. Đầu vào gợi ý gồm danh sách danh mục và ngưỡng. Kiểm tra phía máy chủ là căn cứ cuối cùng; kiểm tra trên trình duyệt chỉ hỗ trợ thao tác người dùng.')
write('Phụ lục 5. Thiết kế đầu ra','Danh sách dữ liệu nghiệp vụ trình bày theo bản ghi và phân trang. Đầu ra RFM gồm ngày tham chiếu, nhóm, số khách hàng và các chỉ số theo khách hàng. Đầu ra luật gồm tiền đề, hệ quả, số giỏ, Support Confidence Lift. Đầu ra gợi ý kế thừa căn cứ của luật và loại hệ quả trùng hoặc đã được chọn.')
write('Phụ lục 6. Kịch bản trình diễn','Bước 1. Đăng nhập bằng tài khoản được phép và tạo bộ dữ liệu trình diễn.|Bước 2. Nhập khách hàng và sản phẩm, tiếp đến đơn hàng, chi tiết đơn và thanh toán.|Bước 3. Đối chiếu số dòng và tổng hợp trên các trang nghiệp vụ.|Bước 4. Mở RFM, chọn ngày tham chiếu và giải thích một khách hàng cụ thể.|Bước 5. Chạy luật với bộ năm giỏ minh họa, đối chiếu Support 40%, Confidence khoảng 66,67% và Lift khoảng 1,1111.|Bước 6. Chọn danh mục để xem gợi ý và kiểm tra danh mục đã chọn không xuất hiện lại.|Bước 7. Dùng tài khoản khác kiểm tra truy cập dataset không thuộc sở hữu bị từ chối.')
# Apply consistent visible borders and readable tables.
for t in doc.tables:
 for row in t.rows:
  for c in row.cells:
   tcpr=c._tc.get_or_add_tcPr();b=OxmlElement('w:tcBorders')
   for edge in ['top','left','bottom','right']:
    e=OxmlElement('w:'+edge);e.set(qn('w:val'),'single');e.set(qn('w:sz'),'4');e.set(qn('w:color'),'D9D9D9');b.append(e)
   tcpr.append(b)
   mar=OxmlElement('w:tcMar')
   for edge in ['top','left','bottom','right']:
    e=OxmlElement('w:'+edge);e.set(qn('w:w'),'90');e.set(qn('w:type'),'dxa');mar.append(e)
   tcpr.append(mar)
   for par in c.paragraphs:par.paragraph_format.space_after=Pt(3)
for par in doc.paragraphs:
 if re.match(r'^0\.\d+\.',par.text):
  par.text=re.sub(r'^0\.(\d+)\.',r'\1.',par.text)
settings=doc.settings.element;u=OxmlElement('w:updateFields');u.set(qn('w:val'),'true');settings.append(u)
out=ROOT/'Bao_cao_cuoi_khoa_WEB_ANALYST.docx';doc.save(out)
print(out);print('Paragraphs',len(doc.paragraphs),'tables',len(doc.tables))
