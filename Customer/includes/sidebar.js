const currentPage = window.location.pathname.split("/").pop();

// Sidebar Template
const sidebarHTML = `
<aside class="group bg-gradient-to-br from-blue-400 to-blue-600 shadow-lg border-r border-gray-200 
flex-shrink-0 fixed top-0 left-0 h-full overflow-hidden flex flex-col text-white 
transition-all duration-500 ease-in-out w-20 hover:w-64 z-50">

    <!-- Logo -->
    <div class="px-7 py-7 border-b border-blue-400 flex items-center gap-2">
        <i class="fas fa-tint text-3xl"></i>
        <div class="overflow-hidden">
            <span class="text-2xl font-semibold block whitespace-nowrap transition-all duration-500 ease-in-out 
                opacity-0 group-hover:opacity-100 translate-x-[-20px] group-hover:translate-x-0">
                RCH Water
            </span>
        </div>
    </div>

    <nav class="mt-6 flex-1">
        <ul class="space-y-1 px-4">

            ${createNavItem("../Customer/order_water_refill.php", "fas fa-tint", "Order Water Refill", "order_water_refill.php")}
            ${createNavItem("../Customer/my_orders.php", "fas fa-box", "My Orders", "my_orders.php")}
        </ul>
    </nav>

    <div class="border-t border-blue-400 px-4 py-5">
        <a href="../../logout.php"
           id="logoutBtn"
           class="flex items-center px-4 py-3 text-white rounded-lg font-medium hover:bg-white/20">
            <i class="fas fa-sign-out-alt text-lg"></i>
            <div class="overflow-hidden ml-3">
                <span class="block whitespace-nowrap transition-all duration-500 ease-in-out 
                    opacity-0 group-hover:opacity-100 translate-x-[-10px] group-hover:translate-x-0">
                    Logout
                </span>
            </div>
        </a>
    </div>
</aside>
`;

// Generate each Nav Item
function createNavItem(href, icon, label, fileName) {
    const isActive = currentPage === fileName;
    const base = "flex items-center px-4 py-3 rounded-lg font-medium transition-all duration-200 ";
    const active = "bg-white/20 text-white";
    const inactive = "text-white hover:bg-white/20";

    return `
        <li>
            <a href="${href}" class="${base} ${isActive ? active : inactive}">
                <i class="${icon} text-lg"></i>
                <div class="overflow-hidden ml-3">
                    <span class="block whitespace-nowrap transition-all duration-500 ease-in-out 
                        opacity-0 group-hover:opacity-100 translate-x-[-10px] group-hover:translate-x-0">
                        ${label}
                    </span>
                </div>
            </a>
        </li>
    `;
}

document.addEventListener("DOMContentLoaded", () => {
    document.body.insertAdjacentHTML("afterbegin", sidebarHTML);
});
