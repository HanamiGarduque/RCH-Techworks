
const currentPage = window.location.pathname.split("/").pop();

// Define your sidebar HTML template
const sidebarHTML = `
<aside class="group bg-gradient-to-br from-blue-400 to-blue-600 shadow-lg border-r border-gray-200 
flex-shrink-0 fixed top-0 left-0 h-full overflow-hidden flex flex-col text-white 
transition-all duration-500 ease-in-out w-20 hover:w-64 z-50">

    <!-- Logo -->
    <div class="px-7 py-7 border-b border-blue-400 flex items-center gap-2">
        <i class="fas fa-droplet text-3xl"></i>
        <div class="overflow-hidden">
            <span class="text-2xl font-semibold block whitespace-nowrap transition-all duration-500 ease-in-out 
                opacity-0 group-hover:opacity-100 translate-x-[-20px] group-hover:translate-x-0">
                RCH Water
            </span>
        </div>
    </div>

    <nav class="mt-6 flex-1">
        <ul class="space-y-1 px-4">
            ${createNavItem("../Admin/dashboard.php", "fas fa-chart-line", "Dashboard", "dashboard.php")}
            ${createNavItem("../Order/orders_list.php", "fas fa-shopping-cart", "Orders", "orders_list.php")}
            ${createNavItem("../GallonOwnership/gallon_ownership.php", "fas fa-tint", "Gallon Ownership", "gallon_ownership.php")}
            ${createNavItem("../Inventory/inventory_list.php", "fas fa-boxes", "Inventory", "inventory_list.php")}
            ${createNavItem("../Customer/customers_list.php", "fas fa-users", "Customers", "customers_list.php")}
            ${createNavItem("../Delivery/deliveries_list.php", "fas fa-truck", "Deliveries", "deliveries_list.php")}
        </ul>
    </nav>

    <div class="border-t border-blue-400 px-4 py-5">
        <a href="../logout.php"
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

// Function to generate each navigation item
function createNavItem(href, iconClass, label, fileName) {
    const isActive = currentPage === fileName;
    const baseClass = "flex items-center px-4 py-3 rounded-lg font-medium transition-all duration-200 ";
    const activeClass = "bg-white/20 text-white";
    const inactiveClass = "text-white hover:bg-white/20";
    return `
        <li>
            <a href="${href}" class="${baseClass} ${isActive ? activeClass : inactiveClass}">
                <i class="${iconClass} text-lg"></i>
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
