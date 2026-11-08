<x-web-layout>
    <x-slot name="title">Luật kết hợp</x-slot>
    <style>
        .rules-page{max-width:1440px;margin:auto;color:#172b4d}.rules-page h1{font-size:28px;font-weight:700}.rules-muted{color:#64748b;font-size:14px}.rules-panel{padding:24px;border:1px solid #e2e8f0;border-radius:14px;background:white;margin-bottom:20px}.rules-filters{display:grid;grid-template-columns:2fr repeat(3,1fr) auto;gap:14px;align-items:end}.rules-filters label{display:block;font-size:13px;margin-bottom:8px}.rules-filters input,.rules-filters select{width:100%;border:1px solid #cbd5e1;border-radius:8px;padding:9px 12px}.rules-stats{display:flex;gap:36px;flex-wrap:wrap;margin:24px 0}.rules-stats strong{display:block;font-size:26px;color:#1e40af}.rules-table-wrap{overflow:auto}.rules-table{width:100%;border-collapse:collapse;font-size:14px}.rules-table th{background:#eff6ff;color:#1e3a8a}.rules-table th,.rules-table td{padding:14px 12px;border-bottom:1px solid #e2e8f0;text-align:left}.rules-item{overflow-wrap:anywhere;min-width:130px;max-width:300px}.rules-badge{color:#0f766e;background:#f0fdfa;padding:4px 8px;border-radius:6px;white-space:nowrap}.rules-empty{text-align:center;padding:48px 20px}.rules-help{display:grid;grid-template-columns:repeat(3,1fr);gap:20px}.rules-help h2{font-size:16px;font-weight:600;margin-bottom:8px}.rules-help p{font-size:13px;line-height:1.7;color:#64748b}@media(max-width:1200px){.rules-filters{grid-template-columns:repeat(3,minmax(0,1fr))}}@media(max-width:700px){.rules-filters,.rules-help{grid-template-columns:1fr}.rules-panel{padding:16px}.web-layout{grid-template-columns:1fr}.web-sidebar{position:static;height:auto;max-height:180px}.web-main{padding:16px}}
    </style>
    <div class="rules-page">
        <div class="mb-4"><p class="rules-muted mb-2">PHÂN TÍCH GIỎ HÀNG</p><h1>Luật kết hợp</h1><p class="rules-muted mt-2">Khám phá sản phẩm thường được mua cùng nhau để xây dựng combo và bán chéo.</p></div>
        @if ($errors->any())<div role="alert" class="alert alert-danger"><ul class="mb-0">@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif
        <form method="POST" action="{{ route('basket.rules.analyze') }}" class="rules-panel rules-filters">
            @csrf
            <div><label for="rule-dataset">Bộ dữ liệu</label><select id="rule-dataset" name="dataset_id" onchange="window.location.href = '{{ route('basket.rules') }}?dataset_id=' + encodeURIComponent(this.value)">@foreach ($datasets as $dataset)<option value="{{ $dataset->dataset_id }}" @selected($datasetId === (int) $dataset->dataset_id)>{{ $dataset->name }}</option>@endforeach</select></div>
            <div><label for="rule-support">Support tối thiểu (%)</label><input id="rule-support" type="number" name="support" value="{{ old('support', $filters['support']) }}" min="0.01" max="100" step="any" required></div>
            <div><label for="rule-confidence">Confidence tối thiểu (%)</label><input id="rule-confidence" type="number" name="confidence" value="{{ old('confidence', $filters['confidence']) }}" min="0" max="100" step="any" required></div>
            <div><label for="rule-lift">Lift tối thiểu</label><input id="rule-lift" type="number" name="lift" value="{{ old('lift', $filters['lift']) }}" min="0" max="1000" step="any" required></div>
            <button type="submit" class="btn btn-primary" style="padding:10px 18px">Phân tích</button>
        </form>
        <div class="rules-stats"><div><strong>{{ number_format($analysis['baskets']) }}</strong><span class="rules-muted">Giỏ hàng hợp lệ</span></div><div><strong>{{ number_format($analysis['items']) }}</strong><span class="rules-muted">Tên sản phẩm</span></div><div><strong>{{ number_format($rules->total()) }}</strong><span class="rules-muted">Luật đạt ngưỡng</span></div></div>
        <section class="rules-panel">
            <h2 class="text-lg fw-bold mb-2">Nếu mua A → thường mua B</h2><p class="rules-muted">Xếp theo Lift, Confidence rồi số giỏ mua cùng giảm dần. Luật A → B và B → A có thể có Confidence khác nhau.</p>
            @if ($rules->isEmpty())
                <div class="rules-empty"><h3 class="fw-bold">{{ $analysis['baskets'] === 0 ? 'Chưa có giỏ hàng hợp lệ' : 'Chưa tìm thấy luật đạt ngưỡng' }}</h3><p class="rules-muted mt-2">{{ $analysis['baskets'] === 0 ? 'Hãy nhập đơn hàng, chi tiết đơn hàng và sản phẩm trong cùng bộ dữ liệu.' : 'Thử giảm Support, Confidence hoặc Lift. Giỏ cần có ít nhất hai mặt hàng khác nhau để tạo luật.' }}</p><a href="{{ route('order_items', ['dataset_id' => $datasetId]) }}" class="text-primary">Xem chi tiết đơn hàng →</a></div>
            @else
                <div class="rules-table-wrap"><table class="rules-table"><thead><tr><th scope="col">Mua A</th><th scope="col">Mua thêm B</th><th scope="col">Giỏ mua cùng</th><th scope="col">Support</th><th scope="col">Confidence</th><th scope="col">Lift</th></tr></thead><tbody>
                    @foreach ($rules as $rule)<tr><td class="rules-item">{{ $rule['antecedent'] }}</td><td class="rules-item">→ {{ $rule['consequent'] }}</td><td>{{ number_format($rule['count']) }}</td><td>{{ number_format($rule['support'], 2, ',', '.') }}%</td><td>{{ number_format($rule['confidence'], 2, ',', '.') }}%</td><td><span class="rules-badge">{{ number_format($rule['lift'], 2, ',', '.') }}</span></td></tr>@endforeach
                </tbody></table></div><div class="mt-4">{{ $rules->links() }}</div>
            @endif
        </section>
        <div class="rules-panel rules-help">
            <div><h2>Support · độ hỗ trợ</h2><p>Số giỏ có cả A và B / tổng giỏ hợp lệ. Cho biết cặp này phổ biến đến đâu trong dữ liệu.</p></div>
            <div><h2>Confidence · độ tin cậy</h2><p>Số giỏ có cả A và B / số giỏ có A. Ví dụ 60% nghĩa là 60 trong 100 giỏ có A cũng có B.</p></div>
            <div><h2>Lift · mức liên hệ</h2><p>Confidence(A → B) / tỷ lệ giỏ có B (cùng dạng tỷ lệ). Lift > 1: mua cùng nhiều hơn mức độc lập; = 1: độc lập; < 1: ít hơn mức độc lập.</p></div>
        </div>
        <p class="rules-muted">Mỗi đơn hàng là một giỏ; mỗi tên chỉ tính một lần dù mua nhiều sản phẩm hoặc nhiều đơn vị. Loại đơn canceled/unavailable và mặt hàng trống. Bỏ sản phẩm chưa có tên; chỉ giỏ còn ít nhất một mặt hàng hợp lệ được dùng làm mẫu số, kể cả giỏ một mặt hàng. Phân tích toàn bộ dữ liệu đang chọn, giới hạn luật dạng một mặt hàng → một mặt hàng; liên hệ không khẳng định quan hệ nhân quả.</p>
    </div>
</x-web-layout>
