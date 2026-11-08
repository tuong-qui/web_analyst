<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title ?? 'Web Analyst' }}</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        .web-layout {
            display: grid;
            grid-template-columns: 250px minmax(0, 1fr);
            min-height: 100vh;
        }

        .web-sidebar {
            position: sticky;
            top: 0;
            align-self: start;
            height: 100vh;
            height: 100dvh;
            overflow-y: auto;
            overscroll-behavior-y: contain;
            border-right: 1px solid #ccc;
        }

        .web-main {
            min-width: 0;
            padding: 24px;
        }
    </style>
</head>

<body>
    <div class="web-layout">
        
        <div
            data-sidebar="content"
            class="web-sidebar flex min-h-0 flex-col gap-2">

            <!-- ========================================================= -->
            <!-- PHÂN TÍCH DỮ LIỆU -->
            <!-- ========================================================= -->
            <div data-sidebar="group" class="relative flex w-full min-w-0 flex-col p-2">

                <div
                    data-sidebar="group-label"
                    class="flex h-8 shrink-0 items-center rounded-md px-2 text-xs font-medium text-sidebar-foreground/70 outline-none ring-sidebar-ring transition-[margin,opacity] duration-200 ease-linear focus-visible:ring-2 [&>svg]:size-4 [&>svg]:shrink-0 group-data-[collapsible=icon]:-mt-8 group-data-[collapsible=icon]:opacity-0">
                    Phân tích dữ liệu
                </div>

                <div data-sidebar="group-content" class="w-full text-sm">
                    <ul data-sidebar="menu" class="flex w-full min-w-0 flex-col gap-1">

                        <!-- Tổng quan kinh doanh -->
                        <li data-sidebar="menu-item" class="group/menu-item relative">
                            <a
                                data-sidebar="menu-button"
                                href="/"
                                class="peer/menu-button flex w-full items-center gap-2 overflow-hidden rounded-md p-2 text-left outline-none ring-sidebar-ring cursor-pointer transition-[width,height,padding] hover:bg-sidebar-accent hover:text-sidebar-accent-foreground group-data-[collapsible=icon]:!size-8 group-data-[collapsible=icon]:!p-2 [&>span:last-child]:truncate [&>svg]:size-4 [&>svg]:shrink-0 h-8 text-sm">
                                <svg xmlns="http://www.w3.org/2000/svg"
                                    width="24" height="24"
                                    viewBox="0 0 24 24"
                                    fill="none"
                                    stroke="currentColor"
                                    stroke-width="2"
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    class="size-4">
                                    <path d="M3 3v18h18"></path>
                                    <path d="m19 9-5 5-4-4-3 3"></path>
                                </svg>

                                <span>Tổng quan kinh doanh</span>
                            </a>
                        </li>

                        <!-- Khách hàng -->
                        <li data-sidebar="menu-item" class="group/menu-item relative">
                            <a
                                data-sidebar="menu-button"
                                href="/customers"
                                class="peer/menu-button flex w-full items-center gap-2 overflow-hidden rounded-md p-2 text-left outline-none ring-sidebar-ring cursor-pointer transition-[width,height,padding] hover:bg-sidebar-accent hover:text-sidebar-accent-foreground group-data-[collapsible=icon]:!size-8 group-data-[collapsible=icon]:!p-2 [&>span:last-child]:truncate [&>svg]:size-4 [&>svg]:shrink-0 h-8 text-sm">
                                <svg xmlns="http://www.w3.org/2000/svg"
                                    width="24" height="24"
                                    viewBox="0 0 24 24"
                                    fill="none"
                                    stroke="currentColor"
                                    stroke-width="2"
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    class="size-4">
                                    <path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"></path>
                                    <circle cx="9" cy="7" r="4"></circle>
                                    <path d="M22 21v-2a4 4 0 0 0-3-3.87"></path>
                                    <path d="M16 3.13a4 4 0 0 1 0 7.75"></path>
                                </svg>

                                <span>Khách hàng</span>
                            </a>
                        </li>
                        <!-- Sản phẩm -->
                        <li data-sidebar="menu-item" class="group/menu-item relative">
                            <a
                                data-sidebar="menu-button"
                                href="/products"
                                class="peer/menu-button flex w-full items-center gap-2 overflow-hidden rounded-md p-2 text-left outline-none ring-sidebar-ring cursor-pointer transition-[width,height,padding] hover:bg-sidebar-accent hover:text-sidebar-accent-foreground group-data-[collapsible=icon]:!size-8 group-data-[collapsible=icon]:!p-2 [&>span:last-child]:truncate [&>svg]:size-4 [&>svg]:shrink-0 h-8 text-sm">
                                <svg xmlns="http://www.w3.org/2000/svg"
                                    width="24" height="24"
                                    viewBox="0 0 24 24"
                                    fill="none"
                                    stroke="currentColor"
                                    stroke-width="2"
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    class="size-4">
                                    <path d="m16.5 9.4-9-5.19"></path>
                                    <path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"></path>
                                    <path d="m3.3 7 8.7 5 8.7-5"></path>
                                    <path d="M12 22V12"></path>
                                </svg>

                                <span>Sản phẩm</span>
                            </a>
                        </li>

                        <!-- Đơn hàng -->
                        <li data-sidebar="menu-item" class="group/menu-item relative">
                            <a
                                data-sidebar="menu-button"
                                href="/orders"
                                class="peer/menu-button flex w-full items-center gap-2 overflow-hidden rounded-md p-2 text-left outline-none ring-sidebar-ring cursor-pointer transition-[width,height,padding] hover:bg-sidebar-accent hover:text-sidebar-accent-foreground group-data-[collapsible=icon]:!size-8 group-data-[collapsible=icon]:!p-2 [&>span:last-child]:truncate [&>svg]:size-4 [&>svg]:shrink-0 h-8 text-sm">
                                <svg xmlns="http://www.w3.org/2000/svg"
                                    width="24" height="24"
                                    viewBox="0 0 24 24"
                                    fill="none"
                                    stroke="currentColor"
                                    stroke-width="2"
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    class="size-4">
                                    <path d="M6 2 3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4Z"></path>
                                    <path d="M3 6h18"></path>
                                    <path d="M16 10a4 4 0 0 1-8 0"></path>
                                </svg>

                                <span>Đơn hàng</span>
                            </a>
                        </li>
                        <!-- Chi tiết Đơn hàng -->
                        <li data-sidebar="menu-item" class="group/menu-item relative">
                            <a
                                data-sidebar="menu-button"
                                href="/order_items"
                                class="peer/menu-button flex w-full items-center gap-2 overflow-hidden rounded-md p-2 text-left outline-none ring-sidebar-ring cursor-pointer transition-[width,height,padding] hover:bg-sidebar-accent hover:text-sidebar-accent-foreground group-data-[collapsible=icon]:!size-8 group-data-[collapsible=icon]:!p-2 [&>span:last-child]:truncate [&>svg]:size-4 [&>svg]:shrink-0 h-8 text-sm">
                                <svg xmlns="http://www.w3.org/2000/svg"
                                    width="24" height="24"
                                    viewBox="0 0 24 24"
                                    fill="none"
                                    stroke="currentColor"
                                    stroke-width="2"
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    class="size-4">
                                    <path d="M6 2 3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4Z"></path>
                                    <path d="M3 6h18"></path>
                                    <path d="M16 10a4 4 0 0 1-8 0"></path>
                                </svg>

                                <span>Chi tiết đơn hàng</span>
                            </a>
                        </li>
                        <!-- Doanh thu -->
                        <li data-sidebar="menu-item" class="group/menu-item relative">
                            <a
                                data-sidebar="menu-button"
                                href="{{ route('order_payments') }}"
                                class="peer/menu-button flex w-full items-center gap-2 overflow-hidden rounded-md p-2 text-left outline-none ring-sidebar-ring cursor-pointer transition-[width,height,padding] hover:bg-sidebar-accent hover:text-sidebar-accent-foreground group-data-[collapsible=icon]:!size-8 group-data-[collapsible=icon]:!p-2 [&>span:last-child]:truncate [&>svg]:size-4 [&>svg]:shrink-0 h-8 text-sm">
                                <svg xmlns="http://www.w3.org/2000/svg"
                                    width="24" height="24"
                                    viewBox="0 0 24 24"
                                    fill="none"
                                    stroke="currentColor"
                                    stroke-width="2"
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    class="size-4">
                                    <path d="M6 2 3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4Z"></path>
                                    <path d="M3 6h18"></path>
                                    <path d="M16 10a4 4 0 0 1-8 0"></path>
                                </svg>

                                <span>Doanh thu</span>
                            </a>
                        </li>


                    </ul>
                </div>
            </div>


            <!-- ========================================================= -->
            <!-- PHÂN TÍCH GIỎ HÀNG -->
            <!-- ========================================================= -->
            <div data-sidebar="group" class="relative flex w-full min-w-0 flex-col p-2">

                <div
                    data-sidebar="group-label"
                    class="flex h-8 shrink-0 items-center rounded-md px-2 text-xs font-medium text-sidebar-foreground/70 outline-none ring-sidebar-ring transition-[margin,opacity] duration-200 ease-linear focus-visible:ring-2 [&>svg]:size-4 [&>svg]:shrink-0 group-data-[collapsible=icon]:-mt-8 group-data-[collapsible=icon]:opacity-0">
                    Phân tích giỏ hàng
                </div>

                <div data-sidebar="group-content" class="w-full text-sm">
                    <ul data-sidebar="menu" class="flex w-full min-w-0 flex-col gap-1">

                        <!-- Luật kết hợp -->
                        <li data-sidebar="menu-item" class="group/menu-item relative">
                            <a
                                data-sidebar="menu-button"
                                href="{{ route('basket.rules') }}"
                                class="peer/menu-button flex w-full items-center gap-2 overflow-hidden rounded-md p-2 text-left outline-none ring-sidebar-ring cursor-pointer transition-[width,height,padding] hover:bg-sidebar-accent hover:text-sidebar-accent-foreground group-data-[collapsible=icon]:!size-8 group-data-[collapsible=icon]:!p-2 [&>span:last-child]:truncate [&>svg]:size-4 [&>svg]:shrink-0 h-8 text-sm">
                                <svg xmlns="http://www.w3.org/2000/svg"
                                    width="24" height="24"
                                    viewBox="0 0 24 24"
                                    fill="none"
                                    stroke="currentColor"
                                    stroke-width="2"
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    class="size-4">
                                    <circle cx="9" cy="7" r="2"></circle>
                                    <circle cx="15" cy="17" r="2"></circle>
                                    <path d="M11 8.5 13 15.5"></path>
                                </svg>

                                <span>Luật kết hợp</span>
                            </a>
                        </li>


                        <!-- Gợi ý sản phẩm -->
                        <li data-sidebar="menu-item" class="group/menu-item relative">
                            <a
                                data-sidebar="menu-button"
                                href="{{ route('basket.recommendations') }}"
                                class="peer/menu-button flex w-full items-center gap-2 overflow-hidden rounded-md p-2 text-left outline-none ring-sidebar-ring cursor-pointer transition-[width,height,padding] hover:bg-sidebar-accent hover:text-sidebar-accent-foreground group-data-[collapsible=icon]:!size-8 group-data-[collapsible=icon]:!p-2 [&>span:last-child]:truncate [&>svg]:size-4 [&>svg]:shrink-0 h-8 text-sm">
                                <svg xmlns="http://www.w3.org/2000/svg"
                                    width="24" height="24"
                                    viewBox="0 0 24 24"
                                    fill="none"
                                    stroke="currentColor"
                                    stroke-width="2"
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    class="size-4">
                                    <path d="M9 18h6"></path>
                                    <path d="M10 22h4"></path>
                                    <path d="M12 2a7 7 0 0 0-4 12.74V17h8v-2.26A7 7 0 0 0 12 2Z"></path>
                                </svg>

                                <span>Gợi ý sản phẩm</span>
                            </a>
                        </li>

                    </ul>
                </div>
            </div>


            <!-- ========================================================= -->
            <!-- KHÁCH HÀNG -->
            <!-- ========================================================= -->
            <div data-sidebar="group" class="relative flex w-full min-w-0 flex-col p-2">

                <div
                    data-sidebar="group-label"
                    class="flex h-8 shrink-0 items-center rounded-md px-2 text-xs font-medium text-sidebar-foreground/70 outline-none ring-sidebar-ring transition-[margin,opacity] duration-200 ease-linear focus-visible:ring-2 [&>svg]:size-4 [&>svg]:shrink-0 group-data-[collapsible=icon]:-mt-8 group-data-[collapsible=icon]:opacity-0">
                    Khách hàng
                </div>

                <div data-sidebar="group-content" class="w-full text-sm">
                    <ul data-sidebar="menu" class="flex w-full min-w-0 flex-col gap-1">

                        <!-- Phân khúc -->
                        <li data-sidebar="menu-item" class="group/menu-item relative">
                            <a
                                data-sidebar="menu-button"
                                href="{{ route('customers.segments') }}"
                                class="peer/menu-button flex w-full items-center gap-2 overflow-hidden rounded-md p-2 text-left outline-none ring-sidebar-ring cursor-pointer transition-[width,height,padding] hover:bg-sidebar-accent hover:text-sidebar-accent-foreground group-data-[collapsible=icon]:!size-8 group-data-[collapsible=icon]:!p-2 [&>span:last-child]:truncate [&>svg]:size-4 [&>svg]:shrink-0 h-8 text-sm">
                                <svg xmlns="http://www.w3.org/2000/svg"
                                    width="24" height="24"
                                    viewBox="0 0 24 24"
                                    fill="none"
                                    stroke="currentColor"
                                    stroke-width="2"
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    class="size-4">
                                    <path d="M18 20a6 6 0 0 0-12 0"></path>
                                    <circle cx="12" cy="10" r="4"></circle>
                                    <circle cx="19" cy="5" r="2"></circle>
                                    <circle cx="5" cy="5" r="2"></circle>
                                </svg>

                                <span>Phân khúc khách hàng</span>
                            </a>
                        </li>

                    </ul>
                </div>
            </div>


            <!-- ========================================================= -->
            <!-- HỆ THỐNG -->
            <!-- ========================================================= -->
            <div data-sidebar="group" class="relative flex w-full min-w-0 flex-col p-2">

                <div
                    data-sidebar="group-label"
                    class="flex h-8 shrink-0 items-center rounded-md px-2 text-xs font-medium text-sidebar-foreground/70 outline-none ring-sidebar-ring transition-[margin,opacity] duration-200 ease-linear focus-visible:ring-2 [&>svg]:size-4 [&>svg]:shrink-0 group-data-[collapsible=icon]:-mt-8 group-data-[collapsible=icon]:opacity-0">
                    Hệ thống
                </div>

                <div data-sidebar="group-content" class="w-full text-sm">
                    <ul data-sidebar="menu" class="flex w-full min-w-0 flex-col gap-1">

                        <!-- Dữ liệu -->
                        <li data-sidebar="menu-item" class="group/menu-item relative">
                            <a
                                data-sidebar="menu-button"
                                href="/data"
                                class="peer/menu-button flex w-full items-center gap-2 overflow-hidden rounded-md p-2 text-left outline-none ring-sidebar-ring cursor-pointer transition-[width,height,padding] hover:bg-sidebar-accent hover:text-sidebar-accent-foreground group-data-[collapsible=icon]:!size-8 group-data-[collapsible=icon]:!p-2 [&>span:last-child]:truncate [&>svg]:size-4 [&>svg]:shrink-0 h-8 text-sm">
                                <svg xmlns="http://www.w3.org/2000/svg"
                                    width="24" height="24"
                                    viewBox="0 0 24 24"
                                    fill="none"
                                    stroke="currentColor"
                                    stroke-width="2"
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    class="size-4">
                                    <ellipse cx="12" cy="5" rx="9" ry="3"></ellipse>
                                    <path d="M3 5v14c0 1.66 4.03 3 9 3s9-1.34 9-3V5"></path>
                                    <path d="M3 12c0 1.66 4.03 3 9 3s9-1.34 9-3"></path>
                                </svg>

                                <span>Dữ liệu</span>
                            </a>
                        </li>


                        <!-- Tài khoản -->
                        <li data-sidebar="menu-item" class="group/menu-item relative">
                            <a
                                data-sidebar="menu-button"
                                href="/profile"
                                class="peer/menu-button flex w-full items-center gap-2 overflow-hidden rounded-md p-2 text-left outline-none ring-sidebar-ring cursor-pointer transition-[width,height,padding] hover:bg-sidebar-accent hover:text-sidebar-accent-foreground group-data-[collapsible=icon]:!size-8 group-data-[collapsible=icon]:!p-2 [&>span:last-child]:truncate [&>svg]:size-4 [&>svg]:shrink-0 h-8 text-sm">
                                <svg xmlns="http://www.w3.org/2000/svg"
                                    width="24"
                                    height="24"
                                    viewBox="0 0 24 24"
                                    fill="none"
                                    stroke="currentColor"
                                    stroke-width="2"
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    class="size-4">
                                    <circle cx="12" cy="8" r="4"></circle>
                                    <path d="M4 21a8 8 0 0 1 16 0"></path>
                                </svg>

                                <span>Tài khoản</span>
                            </a>
                        </li>

                    </ul>
                </div>
            </div>
        </div>
        @auth
            <style>.sidebar-signout{position:fixed;bottom:0;left:0;width:249px;padding:12px;background:#fff;border-top:1px solid #e2e8f0;z-index:10}.sidebar-signout button{display:flex;align-items:center;justify-content:center;gap:8px;width:100%;padding:9px 12px;color:#b91c1c;background:#fff;border:1px solid #fecaca;border-radius:8px;font-size:14px;font-weight:600}.sidebar-signout button:hover{background:#fef2f2}.sidebar-signout button:focus-visible{outline:2px solid #b91c1c;outline-offset:2px}.web-sidebar{padding-bottom:76px}@media(max-width:700px){.sidebar-signout{position:static;width:auto;grid-column:1 / -1}.web-sidebar{padding-bottom:0}}</style>
            <form method="POST" action="{{ route('logout') }}" class="sidebar-signout">
                @csrf
                <button type="submit"><i class="fa-solid fa-arrow-right-from-bracket" aria-hidden="true"></i>Đăng xuất</button>
            </form>
        @endauth
        <main class="web-main">
            {{ $slot }}
        </main>

    </div>

</body>

</html>
