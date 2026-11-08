<x-web-layout>
    <x-slot name="title">Sản phẩm</x-slot>
    <style>
        .dataset-banner { display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 16px; max-width: 760px; margin-bottom: 24px; padding: 18px 20px; border: 1px solid #dbeafe; border-radius: 12px; background: #eff6ff; }
        .dataset-label { margin: 0 0 6px; color: #475569; font-size: 13px; }
        .dataset-name { margin: 0; color: #1e3a8a; font-size: 20px; font-weight: 700; overflow-wrap: anywhere; }
        .dataset-change { flex-shrink: 0; padding: 8px 12px; border: 1px solid #bfdbfe; border-radius: 8px; background: #fff; color: #1e40af; font-size: 14px; font-weight: 600; text-decoration: none; }
        .dataset-change:hover { background: #dbeafe; }
        .dataset-change:focus-visible { outline: 2px solid #1e40af; outline-offset: 3px; }
        .file-field { max-width: 760px; }
        .file-label { display: block; margin-bottom: 4px; font-size: 14px; }
        .file-row { display: flex; align-items: stretch; gap: 10px; }
        .file-name { flex: 1; min-width: 0; width: 100%; height: 34px; margin: 0; padding: 4px 8px; border: 1px solid #999; border-radius: 4px; background: #fff; color: #111827; font-size: 14px; }
        .file-name:focus { outline: 2px solid #0f766e; outline-offset: -2px; }
        .file-browse { position: relative; display: inline-flex; align-items: center; justify-content: center; flex-shrink: 0; gap: 8px; height: 34px; padding: 4px 18px; border: 1px solid #999; border-radius: 4px; background: #fff; color: #111827; font-size: 14px; cursor: pointer; }
        .file-browse:hover { background: #f3f4f6; }
        .file-browse:focus-within { outline: 2px solid #1e40af; outline-offset: 2px; }
        .file-browse input { position: absolute; inset: 0; width: 100%; height: 100%; opacity: 0; cursor: pointer; }
        .file-details { margin: 8px 0 4px; font-size: 13px; color: #4b5563; }
        .file-submit { margin-top: 12px; padding: 8px 20px; border: 1px solid #1e40af; border-radius: 4px; background: #1e40af; color: #fff; font-weight: 600; }
        .file-submit:hover { background: #1e3a8a; }
        .products-preview { margin-top: 28px; }
        .products-table-wrap { overflow-x: auto; border: 1px solid #d1d5db; border-radius: 6px; }
        .products-table { width: 100%; border-collapse: collapse; font-size: 13px; white-space: nowrap; }
        .products-table th, .products-table td { padding: 10px 12px; border-bottom: 1px solid #e5e7eb; text-align: left; }
        .products-table th { background: #1e3a8a; color: #fff; font-weight: 600; }
        .products-table tbody tr:nth-child(even) { background: #f8fafc; }
        .products-table tbody tr:hover { background: #eff6ff; }
    </style>
    <div class="container mt-4">
        <section class="dataset-banner" aria-labelledby="activeDatasetName">
            <div>
                <p class="dataset-label">Bộ dữ liệu đang sử dụng</p>
                <h2 id="activeDatasetName" class="dataset-name">{{ $datasetName }}</h2>
            </div>
            <a class="dataset-change" href="{{ route('data') }}">Đổi bộ dữ liệu &rarr;</a>
        </section>
        <section class="dataset-banner" aria-label="Tổng quan dữ liệu">
            <div><p class="dataset-label">Tổng số sản phẩm</p><strong>{{ number_format($summary->total, 0, ',', '.') }}</strong></div>
            <div><p class="dataset-label">Số danh mục</p><strong>{{ number_format($summary->categories, 0, ',', '.') }}</strong></div>
            <div><p class="dataset-label">Tổng số ảnh</p><strong>{{ number_format($summary->photos, 0, ',', '.') }}</strong></div>
        </section>
        <p class="file-details">Thống kê toàn bộ bộ dữ liệu đang chọn, không giới hạn 100 dòng xem trước.</p>
        <h3 class="text-lg font-semibold mb-4">Thêm dữ liệu sản phẩm</h3>
        <form method="POST" action="{{ route('products.import') }}" enctype="multipart/form-data">
            @csrf
            <input type="hidden" name="dataset_id" value="{{ $datasetId }}">
            <div class="file-field">
                <label for="fileName" class="file-label">File path <span style="color: #b91c1c;" aria-hidden="true">*</span></label>
                <div class="file-row">
                    <input type="text" id="fileName" class="file-name" readonly placeholder="Chưa chọn file" aria-describedby="fileSize">
                    <label class="file-browse">
                        <span aria-hidden="true">↥</span> Browse…
                        <input type="file" id="fileInput" name="file" accept=".csv,.tsv,.xlsx,.xls,.ods" required aria-label="Chọn file sản phẩm" aria-describedby="fileSize">
                    </label>
                </div>
                <p id="fileSize" class="file-details" role="status" aria-live="polite">Kích thước tối đa 50 MB</p>
            </div>
            <button type="submit" id="uploadButton" class="file-submit">Lưu vào database</button>
        </form>
        @if (session('success'))
            <p role="status" style="color: #166534; margin-top: 12px;">{{ session('success') }}</p>
        @endif
        @error('file')
            <p role="alert" style="color: #b91c1c; margin-top: 12px;">{{ $message }}</p>
        @enderror
        <section class="products-preview" aria-labelledby="productsPreviewTitle">
            <p class="file-details">Hiển thị tối đa 100 sản phẩm. File cần đủ 9 cột dưới đây.</p>
            <h3 id="productsPreviewTitle" class="text-lg font-semibold mb-4">Dữ liệu sản phẩm theo cấu trúc sau</h3>
            <div class="products-table-wrap" tabindex="0" role="region" aria-label="Bảng dữ liệu sản phẩm">
                <table class="products-table">
                    <thead>
                        <tr>
                            @foreach ($columns as $column)
                                <th scope="col">{{ $column }}</th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($products as $product)
                            <tr>
                                @foreach ($columns as $column)
                                    <td>{{ $product->{$column} ?? '' }}</td>
                                @endforeach
                            </tr>
                        @empty
                            <tr><td colspan="{{ count($columns) }}">Chưa có dữ liệu sản phẩm. Hãy upload file để nhập dữ liệu.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>
    </div>
    <script>
        document.getElementById('fileInput').addEventListener('change', function () {
            const file = this.files[0];
            const details = document.getElementById('fileSize');
            document.getElementById('fileName').value = file ? file.name : '';
            this.setCustomValidity('');
            details.style.color = '';
            if (!file) {
                details.textContent = 'Kích thước: —';
                return;
            }
            const units = ['B', 'KB', 'MB', 'GB'];
            const unit = file.size === 0 ? 0 : Math.min(Math.floor(Math.log(file.size) / Math.log(1024)), units.length - 1);
            details.textContent = 'Kích thước: ' + new Intl.NumberFormat('vi-VN', { maximumFractionDigits: 2 }).format(file.size / (1024 ** unit)) + ' ' + units[unit];
            if (file.size > 50 * 1024 * 1024) {
                const message = 'File vượt quá giới hạn 50 MB. Vui lòng chọn file nhỏ hơn.';
                this.setCustomValidity(message);
                details.textContent += ' — ' + message;
                details.style.color = '#b91c1c';
            }
        });
    </script>
</x-web-layout>
