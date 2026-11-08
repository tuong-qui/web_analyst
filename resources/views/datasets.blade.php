<x-web-layout>
    <x-slot name="title">Bộ dữ liệu của tôi</x-slot>
    <style>
        .dataset-create { display: inline-flex; align-items: center; justify-content: center; flex-shrink: 0; gap: 8px; height: 34px; padding: 4px 18px; border: 1px solid #999; border-radius: 4px; background: #fff; color: #111827; font-size: 14px; cursor: pointer; }
        .dataset-create:hover { background: #f3f4f6; }
        .dataset-create:focus-visible { outline: 2px solid #1e40af; outline-offset: 2px; }
        .orders-table-wrap { overflow-x: auto; border: 1px solid #d1d5db; border-radius: 6px; }
        .orders-table { width: 100%; border-collapse: collapse; font-size: 13px; white-space: nowrap; }
        .orders-table th, .orders-table td { padding: 10px 12px; border-bottom: 1px solid #e5e7eb; text-align: left; }
        .orders-table th { background: #1e3a8a; color: #fff; font-weight: 600; }
        .orders-table tbody tr:nth-child(even) { background: #f8fafc; }
        .orders-table tbody tr:hover { background: #eff6ff; }
    </style>
    <div class="container mt-4">
        <h2 class="text-lg font-semibold mb-4">Bộ dữ liệu của tôi</h2>
        <p class="mb-4">Chọn cùng một bộ dữ liệu khi nhập khách hàng, sản phẩm, đơn hàng và chi tiết đơn hàng.</p>
        @if (session('success'))
            <p role="status">{{ session('success') }}</p>
        @endif
        @foreach ($errors->all() as $error)
            <p role="alert">{{ $error }}</p>
        @endforeach
        <form method="POST" action="{{ route('datasets.store') }}" class="mb-4">
            @csrf
            <label for="datasetName" class="fw-bold">Tên bộ dữ liệu</label>
            <input id="datasetName" name="name" value="{{ old('name') }}" required maxlength="150" placeholder="Ví dụ: Cửa hàng tháng 10" class="border rounded p-2">
            <button class="dataset-create" type="submit"><span aria-hidden="true">↥</span> Tạo bộ dữ liệu</button>
        </form>
        <div class="orders-table-wrap">
            <table class="orders-table">
                <thead>
                    <tr>
                        <th scope="col">Tên bộ dữ liệu</th>
                        <th scope="col">Thời gian tạo</th>
                        <th scope="col">Trạng thái</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($datasets as $dataset)
                        <tr>
                            <td>{{ $dataset->name }}</td>
                            <td>{{ $dataset->created_at ? \Illuminate\Support\Carbon::parse($dataset->created_at)->format('d/m/Y H:i') : '—' }}</td>
                            <td>
                                @if ((int) session('dataset_id') === (int) $dataset->dataset_id)
                                    <span class="badge text-bg-success">Đang chọn</span>
                                @else
                                    <span class="badge text-bg-secondary">Đã lưu</span>
                                @endif
                                <form method="POST" action="{{ route('datasets.select') }}" class="d-inline-block ms-2">
                                    @csrf
                                    <input type="hidden" name="dataset_id" value="{{ $dataset->dataset_id }}">
                                    <button class="badge text-bg-primary" type="submit">Chọn và nhập dữ liệu</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="3" class="text-center text-muted">Bạn chưa có bộ dữ liệu. Hãy tạo bộ đầu tiên để bắt đầu.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</x-web-layout>
