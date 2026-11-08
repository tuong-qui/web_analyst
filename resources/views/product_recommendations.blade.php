<x-web-layout>
    <x-slot name="title">Gợi ý sản phẩm</x-slot>
    <style>
        .recommend-page{max-width:1200px;margin:auto;color:#172b4d}.recommend-page h1{font-size:28px;font-weight:700}.recommend-muted{color:#64748b;line-height:1.7}.recommend-panel{background:#fff;padding:24px;border:1px solid #e2e8f0;border-radius:14px;margin:20px 0}.recommend-filters{display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:16px}.recommend-page label{display:block;margin-bottom:8px}.recommend-page select,.recommend-page input[type=number]{width:100%;padding:10px;border:1px solid #cbd5e1;border-radius:8px}.recommend-options{max-height:260px;overflow:auto;display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:10px;margin:16px 0}.recommend-option{padding:10px;background:#f8fafc;border-radius:8px;overflow-wrap:anywhere}.recommend-results{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:16px}.recommend-card{border:1px solid #dbeafe;border-radius:12px;padding:20px;overflow-wrap:anywhere}.recommend-card h3{font-size:20px;color:#1e40af}.recommend-metrics{display:flex;gap:20px;flex-wrap:wrap;margin-top:16px}.recommend-metrics strong{display:block;color:#0f766e}@media(max-width:700px){.recommend-filters,.recommend-options,.recommend-results{grid-template-columns:1fr}.recommend-panel{padding:16px}}
    </style>
    <div class="recommend-page">
        <h1>Gợi ý sản phẩm</h1>
        <p class="recommend-muted">Tạo giỏ hàng mô phỏng để tìm những mặt hàng thường được mua cùng. Các tên hiện có đại diện cho nhóm sản phẩm trong dữ liệu.</p>
        @if ($errors->any())
            <div role="alert" class="alert alert-danger"><ul>@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>
        @endif
        <form method="POST" action="{{ route('basket.recommendations.generate') }}" class="recommend-panel">
            @csrf
            <input type="hidden" name="dataset_id" value="{{ $datasetId }}">
            <fieldset class="mt-4">
                <label for="recommend-search">Tìm sản phẩm</label>
                <input id="recommend-search" type="search" class="form-control" placeholder="Nhập tên sản phẩm…" autocomplete="off" aria-controls="recommend-options">
                <p id="recommend-search-status" class="recommend-muted mt-2" aria-live="polite"></p>
                <div id="recommend-options" class="recommend-options">
                    @forelse ($options as $option)
                        <label class="recommend-option" @if ($loop->index >= 20 && ! in_array($option, $items, true)) hidden @endif><input type="checkbox" name="items[]" value="{{ $option }}" @checked(in_array($option, $items, true))> {{ $option }}</label>
                    @empty
                        <p class="recommend-muted">Chưa có nhóm sản phẩm hợp lệ trong bộ dữ liệu. Hãy bổ sung dữ liệu bán hàng.</p>
                    @endforelse
                </div>
            </fieldset>
            <details><summary>Ngưỡng gợi ý</summary><div class="recommend-filters mt-3">
                <div><label for="recommend-support">Support tối thiểu (%)</label><input id="recommend-support" name="support" type="number" min="0.01" max="100" step="any" value="{{ old('support', $filters['support']) }}" required></div>
                <div><label for="recommend-confidence">Confidence tối thiểu (%)</label><input id="recommend-confidence" name="confidence" type="number" min="0" max="100" step="any" value="{{ old('confidence', $filters['confidence']) }}" required></div>
                <div><label for="recommend-lift">Lift tối thiểu</label><input id="recommend-lift" name="lift" type="number" min="0" max="1000" step="any" value="{{ old('lift', $filters['lift']) }}" required></div>
            </div></details>
            <p class="recommend-muted mt-2">Kết quả đã lưu được hiển thị khi mở trang. Nhấn Gợi ý để tính lại và thay thế kết quả theo giỏ hàng và ngưỡng đã chọn.</p>
            <div class="mt-4"><button class="btn btn-primary" type="submit">Gợi ý</button> <button type="submit" form="reset-recommendations" class="btn btn-outline-secondary">Làm mới giỏ</button></div>
        </form>
        <form id="reset-recommendations" method="POST" action="{{ route('basket.recommendations.generate') }}">
            @csrf
            <input type="hidden" name="dataset_id" value="{{ $datasetId }}">
        </form>
        <section class="recommend-panel" aria-live="polite">
            <h2 class="text-lg fw-bold">Đề xuất cho giỏ hàng của bạn</h2>
            @if ($items === [])
                <p class="recommend-muted mt-3">Chọn mặt hàng trong giỏ rồi nhấn Gợi ý để xem kết quả.</p>
            @elseif ($recommendations->isEmpty())
                <p class="recommend-muted mt-3">Chưa tìm thấy gợi ý đạt ngưỡng cho giỏ hàng này. Thử giảm ngưỡng gợi ý hoặc chọn mặt hàng khác.</p>
            @else
                <p class="recommend-muted">Tối đa 10 gợi ý, xếp theo Lift, Confidence rồi số giỏ mua cùng. Mỗi mặt hàng dùng luật phù hợp tốt nhất; không gợi ý lại mặt hàng đã chọn.</p>
                <div class="recommend-results mt-3">
                    @foreach ($recommendations as $recommendation)
                        <article class="recommend-card">
                            <h3>{{ $recommendation['consequent'] }}</h3>
                            <p class="recommend-muted">Vì bạn đã chọn <strong>{{ $recommendation['antecedent'] }}</strong>: {{ number_format($recommendation['confidence'], 2, ',', '.') }}% giỏ có mặt hàng này cũng có {{ $recommendation['consequent'] }}.</p>
                            <div class="recommend-metrics"><div>Support<strong>{{ number_format($recommendation['support'], 2, ',', '.') }}%</strong></div><div>Lift<strong>{{ number_format($recommendation['lift'], 2, ',', '.') }}</strong></div><div>Giỏ mua cùng<strong>{{ number_format($recommendation['count']) }}</strong></div></div>
                        </article>
                    @endforeach
                </div>
            @endif
        </section>
        <p class="recommend-muted">Gợi ý dựa trên lịch sử mua cùng trong bộ dữ liệu, không đảm bảo nhu cầu của từng khách hàng. <a href="{{ route('basket.rules', ['dataset_id' => $datasetId]) }}">Xem luật kết hợp và cách tính chỉ số →</a></p>
    </div>
    <style>.recommend-option[hidden]{display:none}</style>
    <script>
        (() => {
            const search = document.getElementById('recommend-search');
            const options = Array.from(document.querySelectorAll('#recommend-options .recommend-option'));
            const status = document.getElementById('recommend-search-status');
            const normalize = value => value.normalize('NFD').replace(/[\u0300-\u036f]/g, '').toLocaleLowerCase('vi').replace(/đ/g, 'd');
            const update = () => {
                const query = normalize(search.value.trim());
                let matches = 0;
                let selected = 0;
                options.forEach(option => {
                    const checkbox = option.querySelector('input');
                    const matching = normalize(checkbox.value).includes(query);
                    const inTop = matching && matches++ < 20;
                    option.hidden = !checkbox.checked && !inTop;
                    if (checkbox.checked) selected++;
                });
                status.textContent = (matches ? ` ` : 'Không tìm thấy sản phẩm phù hợp.') + ` Đã chọn ${selected}/50 mặt hàng.`;
                options.forEach(option => {
                    const checkbox = option.querySelector('input');
                    checkbox.disabled = !checkbox.checked && selected >= 50;
                });
            };
            search.addEventListener('input', update);
            search.addEventListener('keydown', event => { if (event.key === 'Enter') event.preventDefault(); });
            options.forEach(option => option.querySelector('input').addEventListener('change', update));
            update();
        })();
    </script>
</x-web-layout>
