<x-web-layout>
    <x-slot name="title">Tổng quan kinh doanh</x-slot>
    <style>
        .overview{max-width:1440px;margin:auto;color:#172b4d}.overview h1{font-size:28px;font-weight:700;margin-bottom:8px}.overview p,.overview small{color:#64748b}.ov-header{display:flex;justify-content:space-between;align-items:center;gap:20px;flex-wrap:wrap;margin-bottom:28px}.ov-kpis{display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:16px;margin-bottom:24px}.ov-card{border:1px solid #e2e8f0;border-radius:16px;background:white;padding:24px;min-width:0;box-shadow:0 3px 14px #172b4d05}.ov-kpis strong{display:block;font-size:27px;margin:12px 0;color:#1e40af;overflow-wrap:anywhere}.ov-grid{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:20px}.ov-card h2{font-size:18px;font-weight:600;margin-bottom:8px}.ov-card p{font-size:13px;margin-bottom:24px}.ov-empty{padding:40px 20px;text-align:center;background:#f8fafc;border:1px dashed #cbd5e1;border-radius:12px}.ov-row{margin-top:18px}.ov-label{display:flex;justify-content:space-between;gap:16px;font-size:13px;margin-bottom:7px}.ov-track{height:12px;border-radius:6px;background:#f1f5f9;overflow:hidden}.ov-fill{height:100%;border-radius:6px;background:#2563eb}.ov-chart{width:100%;height:auto}.overview table{width:100%;font-size:13px;border-collapse:collapse}.overview th,.overview td{padding:8px;border-bottom:1px solid #e2e8f0;text-align:right}.overview th:first-child,.overview td:first-child{text-align:left}.ov-note{font-size:12px;line-height:1.8;margin-top:24px}.overview select{max-width:100%;border-radius:8px;border:1px solid #cbd5e1;padding:8px 32px 8px 12px}@media(max-width:1100px){.ov-kpis{grid-template-columns:repeat(2,minmax(0,1fr))}.ov-grid{grid-template-columns:1fr}}@media(max-width:700px){.web-layout{grid-template-columns:1fr}.web-sidebar{position:static;height:auto;max-height:180px}.web-main{padding:16px}.ov-card{padding:16px}.ov-kpis{gap:10px}.ov-kpis strong{font-size:22px}}
    </style>
    <div class="overview">
        <header class="ov-header">
            <div><h1>Tổng quan kinh doanh</h1><p>Khám phá hiệu quả kinh doanh từ bộ dữ liệu của bạn.</p></div>
            @if ($datasets->isNotEmpty())
                <form method="GET" action="{{ route('home') }}">
                    <label for="home-dataset" class="d-block mb-2">Bộ dữ liệu đang phân tích</label>
                    <div class="d-flex gap-2"><select id="home-dataset" name="dataset_id">
                        @foreach ($datasets as $dataset)
                            <option value="{{ $dataset->dataset_id }}" @selected($datasetId === (int) $dataset->dataset_id)>{{ $dataset->name }}</option>
                        @endforeach
                    </select><button class="btn btn-primary" type="submit">Xem</button></div>
                </form>
            @endif
        </header>
        @if (! isset($analytics))
            <div class="ov-empty">
                <h2 class="text-xl fw-bold">{{ auth()->check() ? 'Bắt đầu với bộ dữ liệu đầu tiên' : 'Dữ liệu của bạn, góc nhìn của bạn' }}</h2>
                <p class="my-3">{{ auth()->check() ? 'Tạo bộ dữ liệu và nhập file để xem các biểu đồ phân tích.' : 'Đăng nhập để xem phân tích từ bộ dữ liệu của bạn.' }}</p>
                <a class="btn btn-primary" href="{{ auth()->check() ? route('data') : route('login') }}">{{ auth()->check() ? 'Tạo bộ dữ liệu' : 'Đăng nhập' }} →</a>
            </div>
        @else
            @php
                $number = fn ($value, $decimals = 0) => number_format((float) $value, $decimals, ',', '.');
                $statusNames = ['delivered' => 'Đã giao', 'shipped' => 'Đang vận chuyển', 'processing' => 'Đang xử lý', 'canceled' => 'Đã hủy', 'unavailable' => 'Không khả dụng', 'invoiced' => 'Đã xuất hóa đơn', 'approved' => 'Đã duyệt', 'created' => 'Mới tạo', 'unknown' => 'Chưa xác định'];
                $paymentNames = ['credit_card' => 'Thẻ tín dụng', 'debit_card' => 'Thẻ ghi nợ', 'boleto' => 'Boleto', 'voucher' => 'Phiếu mua hàng', 'not_defined' => 'Chưa xác định', 'unknown' => 'Chưa xác định'];
            @endphp
            <div class="ov-kpis">
                <div class="ov-card"><small>TỔNG THANH TOÁN</small><strong>{{ $number($analytics['payments'], 2) }}</strong><small>Từ các bản ghi thanh toán</small></div>
                <div class="ov-card"><small>TỔNG ĐƠN HÀNG</small><strong>{{ $number($analytics['orders']) }}</strong><small>{{ $number($analytics['deliveryRate'], 1) }}% đơn đã giao</small></div>
                <div class="ov-card"><small>KHÁCH HÀNG DUY NHẤT</small><strong>{{ $number($analytics['customers']) }}</strong><small>Theo customer_unique_id</small></div>
                <div class="ov-card"><small>THANH TOÁN / ĐƠN</small><strong>{{ $number($analytics['average'], 2) }}</strong><small>Trung bình trên đơn có thanh toán</small></div>
            </div>
            @if ($analytics['orders'] === 0)
                <div class="alert alert-info">Chưa có đơn hàng. <a href="{{ route('orders', ['dataset_id' => $datasetId]) }}">Nhập đơn hàng</a> để bắt đầu phân tích.</div>
            @endif
            <div class="ov-grid">
                <section class="ov-card">
                    <h2>Doanh thu đơn đã giao theo tháng</h2><p>Tổng thanh toán theo tháng mua hàng · toàn bộ thời gian</p>
                    @if ($analytics['monthly']->isEmpty())
                        <div class="ov-empty">Chưa có đơn hàng có ngày mua để vẽ biểu đồ.</div>
                    @else
                        @php
                            $months = $analytics['monthly'];
                            $maximum = max(1, (float) $months->max('value'));
                            $points = $months->values()->map(fn ($row, $index) => (40 + $index * 540 / max(1, $months->count() - 1)).','. (170 - max(0, (float) $row->value) / $maximum * 140))->implode(' ');
                        @endphp
                        <svg class="ov-chart" viewBox="0 0 620 210" role="img" aria-label="Thanh toán theo tháng, số liệu chi tiết trong bảng bên dưới">
                            @foreach ([30, 100, 170] as $y)<line x1="40" y1="{{ $y }}" x2="580" y2="{{ $y }}" stroke="#e2e8f0" />@endforeach
                            <polyline points="{{ $points }}" fill="none" stroke="#2563eb" stroke-width="3" stroke-linejoin="round" />
                            @foreach ($months as $month)
                                <circle cx="{{ 40 + $loop->index * 540 / max(1, $months->count() - 1) }}" cy="{{ 170 - max(0, (float) $month->value) / $maximum * 140 }}" r="4" fill="#2563eb"><title>{{ $month->month }}: {{ $number($month->value, 2) }}</title></circle>
                            @endforeach
                            <text x="40" y="199" fill="#64748b" font-size="12">{{ $months->first()->month }}</text>
                            @if ($months->count() > 1)<text x="580" y="199" text-anchor="end" fill="#64748b" font-size="12">{{ $months->last()->month }}</text>@endif
                            <text x="40" y="20" fill="#64748b" font-size="12">Cao nhất: {{ $number($months->max('value'), 2) }}</text>
                        </svg>
                        <details><summary>Xem số liệu theo tháng</summary><div style="max-height:300px;overflow:auto"><table><thead><tr><th>Tháng</th><th>Đơn hàng</th><th>Thanh toán</th></tr></thead><tbody>
                            @foreach ($months as $month)<tr><td>{{ $month->month }}</td><td>{{ $number($month->orders) }}</td><td>{{ $number($month->value, 2) }}</td></tr>@endforeach
                        </tbody></table></div></details>
                    @endif
                </section>
                @foreach (['statuses' => ['Trạng thái đơn hàng', 'Số lượng đơn theo trạng thái hiện tại'], 'categories' => ['Top 8 nhóm sản phẩm', 'Tổng giá bán sản phẩm · không gồm phí vận chuyển'], 'paymentTypes' => ['Phương thức thanh toán', 'Tổng giá trị thanh toán theo từng phương thức']] as $key => [$heading, $description])
                    <section class="ov-card"><h2>{{ $heading }}</h2><p>{{ $description }}</p>
                        @forelse ($analytics[$key] as $row)
                            <div class="ov-row"><div class="ov-label"><span>{{ match ($key) { 'statuses' => $statusNames[$row->label] ?? $row->label, 'paymentTypes' => $paymentNames[$row->label] ?? $row->label, default => $row->label } }}</span><strong>{{ $number($row->value, $key === 'statuses' ? 0 : 2) }}</strong></div>
                                <div class="ov-track"><div class="ov-fill" style="width:{{ max(0, min(100, (float) $row->value / max(1, (float) $analytics[$key]->max('value')) * 100)) }}%;background:{{ $key === 'paymentTypes' ? '#14b8a6' : '#2563eb' }}"></div></div>
                            </div>
                        @empty
                            <div class="ov-empty">{{ $key === 'categories' ? 'Nhập chi tiết đơn hàng và sản phẩm để xem phân tích.' : 'Chưa có dữ liệu để phân tích.' }}</div>
                        @endforelse
                    </section>
                @endforeach
            </div>
            <p class="ov-note">Số liệu thuộc bộ dữ liệu <strong>{{ $datasetName }}</strong>, bao gồm tất cả trạng thái đơn hàng. Giá trị tiền tệ giữ nguyên đơn vị của file nhập; tổng thanh toán không đồng nghĩa doanh thu thuần.
                @if ($analytics['undatedOrders'] > 0)<br>{{ $number($analytics['undatedOrders']) }} đơn chưa có ngày mua không xuất hiện trong biểu đồ theo tháng.@endif
            </p>
        @endif
    </div>
</x-web-layout>
