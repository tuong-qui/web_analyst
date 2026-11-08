<x-web-layout>
    <x-slot name="title">Phân khúc khách hàng</x-slot>
    <style>
        .segments{max-width:1440px;margin:auto;color:#172b4d}.segments h1{font-size:28px;font-weight:700}.seg-muted{color:#64748b;line-height:1.7}.seg-panel{padding:24px;background:white;border:1px solid #e2e8f0;border-radius:14px;margin:20px 0}.seg-filters,.seg-kpis{display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:16px}.segments label{display:block;margin-bottom:6px}.segments input,.segments select{width:100%;padding:9px;border:1px solid #cbd5e1;border-radius:8px}.seg-kpis strong{display:block;font-size:26px;color:#1e40af;overflow-wrap:anywhere}.seg-groups{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:18px}.seg-group{padding:16px;border:1px solid #dbeafe;border-radius:10px}.seg-track{height:10px;background:#f1f5f9;border-radius:8px;margin:10px 0;overflow:hidden}.seg-fill{height:100%;background:#2563eb}.seg-table{overflow:auto}.segments table{width:100%;border-collapse:collapse;font-size:14px}.segments td,.segments th{padding:12px;text-align:left;border-bottom:1px solid #e2e8f0;overflow-wrap:anywhere}.segments th{background:#eff6ff}.seg-badge{background:#eff6ff;color:#1e40af;border-radius:6px;padding:4px 8px;display:inline-block}.segments summary{cursor:pointer;font-weight:600}@media(max-width:700px){.seg-filters,.seg-kpis,.seg-groups{grid-template-columns:1fr}.seg-panel{padding:16px}}
    </style>
    <div class="segments">
        <h1>Phân khúc khách hàng</h1>
        <p class="seg-muted">Hiểu hành vi mua sắm qua lần mua gần nhất (R), số đơn hàng (F) và tổng thanh toán (M).</p>
        @if ($errors->any())<div class="alert alert-danger" role="alert"><ul>@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif
        <form class="seg-panel" method="GET" action="{{ route('customers.segments') }}">
            <div class="seg-filters">
                <div><label for="segment-dataset">Bộ dữ liệu</label><select id="segment-dataset" name="dataset_id">@foreach ($datasets as $dataset)<option value="{{ $dataset->dataset_id }}" @selected((int) $dataset->dataset_id === $datasetId)>{{ $dataset->name }}</option>@endforeach</select></div>
                <div><label for="segment-date">Ngày phân tích</label><input id="segment-date" type="date" name="date" value="{{ old('date', $analysis['date']) }}"><small class="seg-muted">Để trống để lấy ngày mua hợp lệ mới nhất.</small></div>
                <div><label for="segment-filter">Phân khúc</label><select id="segment-filter" name="segment"><option value="">Tất cả phân khúc</option>@foreach ($analysis['groups'] as $key => $group)<option value="{{ $key }}" @selected($filters['segment'] === $key)>{{ $group['name'] }}</option>@endforeach</select></div>
                <div><label for="segment-search">Tìm mã khách hàng</label><input id="segment-search" name="search" type="search" maxlength="100" value="{{ $filters['search'] }}" placeholder="Nhập mã khách hàng…"></div>
            </div>
            <div class="mt-3"><button class="btn btn-primary" type="submit">Phân tích</button> <a class="btn btn-outline-secondary" href="{{ route('customers.segments', ['dataset_id' => $datasetId]) }}">Đặt lại</a></div>
        </form>
        <div class="seg-kpis">
            <div class="seg-panel">Khách hàng phân tích<strong>{{ number_format($analysis['total']) }}</strong></div>
            <div class="seg-panel">Tổng thanh toán<strong>{{ number_format($analysis['monetary'], 2, ',', '.') }}</strong><small class="seg-muted">Theo đơn vị tiền của dữ liệu nguồn</small></div>
            <div class="seg-panel">Khách hàng giá trị cao<strong>{{ number_format($analysis['groups']['champions']['count']) }}</strong></div>
        </div>
        @if ($analysis['total'] === 0)
            <div class="seg-panel">Chưa có khách hàng đủ dữ liệu tại ngày phân tích. Hãy kiểm tra ngày mua, mã khách hàng duy nhất và dữ liệu đơn hàng. <a href="{{ route('customers', ['dataset_id' => $datasetId]) }}">Xem khách hàng →</a></div>
        @endif
        <section class="seg-panel">
            <h2 class="text-lg fw-bold">Phân bố khách hàng</h2>
            <p class="seg-muted">Tổng quan toàn bộ khách hàng đến ngày {{ $analysis['date'] ?? '—' }}. Bộ lọc phân khúc và tìm kiếm chỉ áp dụng cho bảng bên dưới.</p>
            <div class="seg-groups mt-3">
                @foreach ($analysis['groups'] as $key => $group)
                    @php($percent = $analysis['total'] ? $group['count'] / $analysis['total'] * 100 : 0)
                    <article class="seg-group">
                        <h3 class="fw-bold">{{ $group['name'] }}</h3>
                        <div>{{ number_format($group['count']) }} khách · {{ number_format($percent, 1, ',', '.') }}%</div>
                        <div class="seg-track" role="img" aria-label="{{ $group['name'] }}: {{ number_format($percent, 1) }}%"><div class="seg-fill" style="width:{{ $percent }}%"></div></div>
                        <p>Thanh toán: {{ number_format($group['monetary'], 2, ',', '.') }}</p>
                        <p class="seg-muted">{{ $group['action'] }}</p>
                    </article>
                @endforeach
            </div>
        </section>
        <section class="seg-panel">
            <h2 class="text-lg fw-bold">Danh sách khách hàng · {{ number_format($customers->total()) }} kết quả</h2>
            @if ($customers->isEmpty())<p class="seg-muted mt-3">Không có khách hàng phù hợp với bộ lọc.</p>@else
                <div class="seg-table mt-3"><table><thead><tr><th>Mã khách hàng</th><th>Lần mua gần nhất</th><th>R · số ngày</th><th>F · số đơn</th><th>M · thanh toán</th><th>Điểm R/F/M</th><th>Phân khúc</th></tr></thead><tbody>
                    @foreach ($customers as $customer)<tr>
                        <td>{{ $customer['customer'] }}</td><td>{{ $customer['last_purchase'] }}</td><td>{{ $customer['recency'] }}</td><td>{{ $customer['frequency'] }}</td><td>{{ number_format($customer['monetary'], 2, ',', '.') }}</td><td>{{ $customer['r'] }}/{{ $customer['f'] }}/{{ $customer['m'] }}</td><td><span class="seg-badge">{{ $analysis['groups'][$customer['segment']]['name'] }}</span></td>
                    </tr>@endforeach
                </tbody></table></div>
                <div class="mt-3">{{ $customers->links() }}</div>
            @endif
        </section>
        <details class="seg-panel"><summary>Cách tính RFM và quy tắc phân nhóm</summary>
            <p class="seg-muted mt-3">R là số ngày từ lần mua gần nhất đến ngày phân tích: ≤ 30 ngày được 5 điểm; 31–60: 4; 61–90: 3; 91–180: 2; trên 180: 1.</p>
            <p class="seg-muted"> F là số đơn: 1–4 đơn tương ứng 1–4 điểm, từ 5 đơn được 5 điểm.</p>
            <p class="seg-muted">M là tổng thanh toán. Điểm M từ 1–5, tăng thêm 1 khi chi tiêu vượt mỗi mốc phân vị 20%, 40%, 60%, 80%; thanh toán ≤ 0 được 1 điểm.</p>
            <p class="seg-muted">Áp dụng các quy tắc theo thứ tự dưới đây, mỗi khách chỉ thuộc nhóm đầu tiên phù hợp:</p>
            <ol>@foreach ($analysis['groups'] as $group)<li><strong>{{ $group['name'] }}</strong>: {{ $group['rule'] }}.</li>@endforeach</ol>
            <p class="seg-muted mt-3">Chỉ xét đơn có ngày mua đến hết ngày phân tích, loại canceled/unavailable; các trạng thái khác (kể cả chưa xác định) được giữ. Khách thiếu mã duy nhất hoặc không có đơn hợp lệ được bỏ qua. Đơn chưa có thanh toán vẫn tính vào F với giá trị M bằng 0. Ngưỡng ngày và điểm là quy tắc phân tích, có thể điều chỉnh theo chu kỳ kinh doanh.</p>
        </details>
    </div>
</x-web-layout>
