<div
    data-sidebar="content"
    class="flex min-h-0 flex-1 flex-col gap-2 overflow-auto group-data-[collapsible=icon]:overflow-hidden"
>

    <!-- ========================================================= -->
    <!-- TỔNG QUAN -->
    <!-- ========================================================= -->
    <div data-sidebar="group" class="relative flex w-full min-w-0 flex-col p-2">

        <div
            data-sidebar="group-label"
            class="flex h-8 shrink-0 items-center rounded-md px-2 text-xs font-medium text-sidebar-foreground/70 outline-none ring-sidebar-ring transition-[margin,opacity] duration-200 ease-linear focus-visible:ring-2 [&>svg]:size-4 [&>svg]:shrink-0 group-data-[collapsible=icon]:-mt-8 group-data-[collapsible=icon]:opacity-0"
        >
            Tổng quan
        </div>

        <div data-sidebar="group-content" class="w-full text-sm">
            <ul data-sidebar="menu" class="flex w-full min-w-0 flex-col gap-1">

                <!-- Dashboard -->
                <li data-sidebar="menu-item" class="group/menu-item relative">
                    <a
                        data-sidebar="menu-button"
                        data-size="default"
                        href="/dashboard"
                        class="peer/menu-button flex w-full items-center gap-2 overflow-hidden rounded-md p-2 text-left outline-none ring-sidebar-ring cursor-pointer transition-[width,height,padding] focus-visible:ring-2 hover:bg-sidebar-accent hover:text-sidebar-accent-foreground group-data-[collapsible=icon]:!size-8 group-data-[collapsible=icon]:!p-2 [&>span:last-child]:truncate [&>svg]:size-4 [&>svg]:shrink-0 h-8 text-sm"
                    >
                        <!-- Dashboard icon -->
                        <svg
                            xmlns="http://www.w3.org/2000/svg"
                            width="24"
                            height="24"
                            viewBox="0 0 24 24"
                            fill="none"
                            stroke="currentColor"
                            stroke-width="2"
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            class="lucide lucide-layout-dashboard size-4"
                        >
                            <rect width="7" height="9" x="3" y="3" rx="1"></rect>
                            <rect width="7" height="5" x="14" y="3" rx="1"></rect>
                            <rect width="7" height="9" x="14" y="12" rx="1"></rect>
                            <rect width="7" height="5" x="3" y="16" rx="1"></rect>
                        </svg>

                        <span>Dashboard</span>
                    </a>
                </li>

            </ul>
        </div>
    </div>


    <!-- ========================================================= -->
    <!-- PHÂN TÍCH DỮ LIỆU -->
    <!-- ========================================================= -->
    <div data-sidebar="group" class="relative flex w-full min-w-0 flex-col p-2">

        <div
            data-sidebar="group-label"
            class="flex h-8 shrink-0 items-center rounded-md px-2 text-xs font-medium text-sidebar-foreground/70 outline-none ring-sidebar-ring transition-[margin,opacity] duration-200 ease-linear focus-visible:ring-2 [&>svg]:size-4 [&>svg]:shrink-0 group-data-[collapsible=icon]:-mt-8 group-data-[collapsible=icon]:opacity-0"
        >
            Phân tích dữ liệu
        </div>

        <div data-sidebar="group-content" class="w-full text-sm">
            <ul data-sidebar="menu" class="flex w-full min-w-0 flex-col gap-1">

                <!-- Tổng quan kinh doanh -->
                <li data-sidebar="menu-item" class="group/menu-item relative">
                    <a
                        data-sidebar="menu-button"
                        href="/analysis/overview"
                        class="peer/menu-button flex w-full items-center gap-2 overflow-hidden rounded-md p-2 text-left outline-none ring-sidebar-ring cursor-pointer transition-[width,height,padding] hover:bg-sidebar-accent hover:text-sidebar-accent-foreground group-data-[collapsible=icon]:!size-8 group-data-[collapsible=icon]:!p-2 [&>span:last-child]:truncate [&>svg]:size-4 [&>svg]:shrink-0 h-8 text-sm"
                    >
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


                <!-- Đơn hàng -->
                <li data-sidebar="menu-item" class="group/menu-item relative">
                    <a
                        data-sidebar="menu-button"
                        href="/analysis/orders"
                        class="peer/menu-button flex w-full items-center gap-2 overflow-hidden rounded-md p-2 text-left outline-none ring-sidebar-ring cursor-pointer transition-[width,height,padding] hover:bg-sidebar-accent hover:text-sidebar-accent-foreground group-data-[collapsible=icon]:!size-8 group-data-[collapsible=icon]:!p-2 [&>span:last-child]:truncate [&>svg]:size-4 [&>svg]:shrink-0 h-8 text-sm"
                    >
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


                <!-- Khách hàng -->
                <li data-sidebar="menu-item" class="group/menu-item relative">
                    <a
                        data-sidebar="menu-button"
                        href="/analysis/customers"
                        class="peer/menu-button flex w-full items-center gap-2 overflow-hidden rounded-md p-2 text-left outline-none ring-sidebar-ring cursor-pointer transition-[width,height,padding] hover:bg-sidebar-accent hover:text-sidebar-accent-foreground group-data-[collapsible=icon]:!size-8 group-data-[collapsible=icon]:!p-2 [&>span:last-child]:truncate [&>svg]:size-4 [&>svg]:shrink-0 h-8 text-sm"
                    >
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
                        href="/analysis/products"
                        class="peer/menu-button flex w-full items-center gap-2 overflow-hidden rounded-md p-2 text-left outline-none ring-sidebar-ring cursor-pointer transition-[width,height,padding] hover:bg-sidebar-accent hover:text-sidebar-accent-foreground group-data-[collapsible=icon]:!size-8 group-data-[collapsible=icon]:!p-2 [&>span:last-child]:truncate [&>svg]:size-4 [&>svg]:shrink-0 h-8 text-sm"
                    >
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

            </ul>
        </div>
    </div>


    <!-- ========================================================= -->
    <!-- PHÂN TÍCH GIỎ HÀNG -->
    <!-- ========================================================= -->
    <div data-sidebar="group" class="relative flex w-full min-w-0 flex-col p-2">

        <div
            data-sidebar="group-label"
            class="flex h-8 shrink-0 items-center rounded-md px-2 text-xs font-medium text-sidebar-foreground/70 outline-none ring-sidebar-ring transition-[margin,opacity] duration-200 ease-linear focus-visible:ring-2 [&>svg]:size-4 [&>svg]:shrink-0 group-data-[collapsible=icon]:-mt-8 group-data-[collapsible=icon]:opacity-0"
        >
            Phân tích giỏ hàng
        </div>

        <div data-sidebar="group-content" class="w-full text-sm">
            <ul data-sidebar="menu" class="flex w-full min-w-0 flex-col gap-1">

                <!-- Luật kết hợp -->
                <li data-sidebar="menu-item" class="group/menu-item relative">
                    <a
                        data-sidebar="menu-button"
                        href="{{ route('basket.rules') }}"
                        class="peer/menu-button flex w-full items-center gap-2 overflow-hidden rounded-md p-2 text-left outline-none ring-sidebar-ring cursor-pointer transition-[width,height,padding] hover:bg-sidebar-accent hover:text-sidebar-accent-foreground group-data-[collapsible=icon]:!size-8 group-data-[collapsible=icon]:!p-2 [&>span:last-child]:truncate [&>svg]:size-4 [&>svg]:shrink-0 h-8 text-sm"
                    >
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
                        class="peer/menu-button flex w-full items-center gap-2 overflow-hidden rounded-md p-2 text-left outline-none ring-sidebar-ring cursor-pointer transition-[width,height,padding] hover:bg-sidebar-accent hover:text-sidebar-accent-foreground group-data-[collapsible=icon]:!size-8 group-data-[collapsible=icon]:!p-2 [&>span:last-child]:truncate [&>svg]:size-4 [&>svg]:shrink-0 h-8 text-sm"
                    >
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
            class="flex h-8 shrink-0 items-center rounded-md px-2 text-xs font-medium text-sidebar-foreground/70 outline-none ring-sidebar-ring transition-[margin,opacity] duration-200 ease-linear focus-visible:ring-2 [&>svg]:size-4 [&>svg]:shrink-0 group-data-[collapsible=icon]:-mt-8 group-data-[collapsible=icon]:opacity-0"
        >
            Khách hàng
        </div>

        <div data-sidebar="group-content" class="w-full text-sm">
            <ul data-sidebar="menu" class="flex w-full min-w-0 flex-col gap-1">

                <!-- Phân khúc -->
                <li data-sidebar="menu-item" class="group/menu-item relative">
                    <a
                        data-sidebar="menu-button"
                        href="{{ route('customers.segments') }}"
                        class="peer/menu-button flex w-full items-center gap-2 overflow-hidden rounded-md p-2 text-left outline-none ring-sidebar-ring cursor-pointer transition-[width,height,padding] hover:bg-sidebar-accent hover:text-sidebar-accent-foreground group-data-[collapsible=icon]:!size-8 group-data-[collapsible=icon]:!p-2 [&>span:last-child]:truncate [&>svg]:size-4 [&>svg]:shrink-0 h-8 text-sm"
                    >
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


                <!-- Hồ sơ khách hàng -->
                <li data-sidebar="menu-item" class="group/menu-item relative">
                    <a
                        data-sidebar="menu-button"
                        href="/customers/profiles"
                        class="peer/menu-button flex w-full items-center gap-2 overflow-hidden rounded-md p-2 text-left outline-none ring-sidebar-ring cursor-pointer transition-[width,height,padding] hover:bg-sidebar-accent hover:text-sidebar-accent-foreground group-data-[collapsible=icon]:!size-8 group-data-[collapsible=icon]:!p-2 [&>span:last-child]:truncate [&>svg]:size-4 [&>svg]:shrink-0 h-8 text-sm"
                    >
                        <svg xmlns="http://www.w3.org/2000/svg"
                            width="24" height="24"
                            viewBox="0 0 24 24"
                            fill="none"
                            stroke="currentColor"
                            stroke-width="2"
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            class="size-4">
                            <circle cx="12" cy="8" r="4"></circle>
                            <path d="M6 20c0-3.3 2.7-6 6-6s6 2.7 6 6"></path>
                        </svg>

                        <span>Hồ sơ khách hàng</span>
                    </a>
                </li>

            </ul>
        </div>
    </div>


    <!-- ========================================================= -->
    <!-- HỖ TRỢ RA QUYẾT ĐỊNH -->
    <!-- ========================================================= -->
    <div data-sidebar="group" class="relative flex w-full min-w-0 flex-col p-2">

        <div
            data-sidebar="group-label"
            class="flex h-8 shrink-0 items-center rounded-md px-2 text-xs font-medium text-sidebar-foreground/70 outline-none ring-sidebar-ring transition-[margin,opacity] duration-200 ease-linear focus-visible:ring-2 [&>svg]:size-4 [&>svg]:shrink-0 group-data-[collapsible=icon]:-mt-8 group-data-[collapsible=icon]:opacity-0"
        >
            Hỗ trợ ra quyết định
        </div>

        <div data-sidebar="group-content" class="w-full text-sm">
            <ul data-sidebar="menu" class="flex w-full min-w-0 flex-col gap-1">

                <!-- Đề xuất hành động -->
                <li data-sidebar="menu-item" class="group/menu-item relative">
                    <a
                        data-sidebar="menu-button"
                        href="/decision-support/actions"
                        class="peer/menu-button flex w-full items-center gap-2 overflow-hidden rounded-md p-2 text-left outline-none ring-sidebar-ring cursor-pointer transition-[width,height,padding] hover:bg-sidebar-accent hover:text-sidebar-accent-foreground group-data-[collapsible=icon]:!size-8 group-data-[collapsible=icon]:!p-2 [&>span:last-child]:truncate [&>svg]:size-4 [&>svg]:shrink-0 h-8 text-sm"
                    >
                        <svg xmlns="http://www.w3.org/2000/svg"
                            width="24" height="24"
                            viewBox="0 0 24 24"
                            fill="none"
                            stroke="currentColor"
                            stroke-width="2"
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            class="size-4">
                            <path d="M12 3v18"></path>
                            <path d="M5 8h14"></path>
                            <path d="M7 3h10"></path>
                            <path d="M5 16h14"></path>
                        </svg>

                        <span>Đề xuất hành động</span>
                    </a>
                </li>


                <!-- Business Insights -->
                <li data-sidebar="menu-item" class="group/menu-item relative">
                    <a
                        data-sidebar="menu-button"
                        href="/decision-support/insights"
                        class="peer/menu-button flex w-full items-center gap-2 overflow-hidden rounded-md p-2 text-left outline-none ring-sidebar-ring cursor-pointer transition-[width,height,padding] hover:bg-sidebar-accent hover:text-sidebar-accent-foreground group-data-[collapsible=icon]:!size-8 group-data-[collapsible=icon]:!p-2 [&>span:last-child]:truncate [&>svg]:size-4 [&>svg]:shrink-0 h-8 text-sm"
                    >
                        <svg xmlns="http://www.w3.org/2000/svg"
                            width="24" height="24"
                            viewBox="0 0 24 24"
                            fill="none"
                            stroke="currentColor"
                            stroke-width="2"
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            class="size-4">
                            <path d="M3 12h4l3-9 4 18 3-9h4"></path>
                        </svg>

                        <span>Business Insights</span>
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
            class="flex h-8 shrink-0 items-center rounded-md px-2 text-xs font-medium text-sidebar-foreground/70 outline-none ring-sidebar-ring transition-[margin,opacity] duration-200 ease-linear focus-visible:ring-2 [&>svg]:size-4 [&>svg]:shrink-0 group-data-[collapsible=icon]:-mt-8 group-data-[collapsible=icon]:opacity-0"
        >
            Hệ thống
        </div>

        <div data-sidebar="group-content" class="w-full text-sm">
            <ul data-sidebar="menu" class="flex w-full min-w-0 flex-col gap-1">

                <!-- Dữ liệu -->
                <li data-sidebar="menu-item" class="group/menu-item relative">
                    <a
                        data-sidebar="menu-button"
                        href="/data"
                        class="peer/menu-button flex w-full items-center gap-2 overflow-hidden rounded-md p-2 text-left outline-none ring-sidebar-ring cursor-pointer transition-[width,height,padding] hover:bg-sidebar-accent hover:text-sidebar-accent-foreground group-data-[collapsible=icon]:!size-8 group-data-[collapsible=icon]:!p-2 [&>span:last-child]:truncate [&>svg]:size-4 [&>svg]:shrink-0 h-8 text-sm"
                    >
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
                        class="peer/menu-button flex w-full items-center gap-2 overflow-hidden rounded-md p-2 text-left outline-none ring-sidebar-ring cursor-pointer transition-[width,height,padding] hover:bg-sidebar-accent hover:text-sidebar-accent-foreground group-data-[collapsible=icon]:!size-8 group-data-[collapsible=icon]:!p-2 [&>span:last-child]:truncate [&>svg]:size-4 [&>svg]:shrink-0 h-8 text-sm"
                    >
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
