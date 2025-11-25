<?php
// Set default values if the page didn't define them
if (!isset($pageTitle)) {
    $pageTitle = "Order Water Refill";
}
if (!isset($pageSubtitle)) {
    $pageSubtitle = "Hydrate made easy—order and we'll handle the rest!";
}

// Detect current page name
$currentPage = basename($_SERVER['PHP_SELF']); 
?>

<!-- Header -->
<div class="bg-white border-b border-gray-100 shadow-sm p-4 transition-all duration-300 ease-in-out">
    <div class="flex items-center justify-between">

        <!-- Title + Subtitle -->
        <div class="flex flex-col">
            <h1 class="text-3xl font-semibold text-blue-500"><?= htmlspecialchars($pageTitle) ?></h1>
            <p class="text-sm text-gray-700 mt-1"><?= htmlspecialchars($pageSubtitle) ?></p>
        </div>

        <!-- Header Actions -->
        <div class="flex items-center space-x-4">

            <!-- Notifications -->
            <a href="../Notification/cust_notifications.php"
               title="Notifications"
               class="w-10 h-10 flex items-center justify-center rounded-full transition
               <?= $currentPage === 'cust_notifications.php' ? 'bg-blue-100' : 'hover:bg-blue-100' ?>">
                <i class="fas fa-bell text-blue-500 text-xl"></i>
            </a>

            <!-- Messages -->
            <a href="../Messaging/cust_messages.php"
               title="Messages"
               class="w-10 h-10 flex items-center justify-center rounded-full transition
               <?= $currentPage === 'cust_messages.php' ? 'bg-blue-100' : 'hover:bg-blue-100' ?>">
                <i class="fas fa-envelope text-blue-500 text-xl"></i>
            </a>

            <!-- Cart -->
            <a href="../Order/cart.php"
               title="Cart"
               class="w-10 h-10 flex items-center justify-center rounded-full transition
               <?= $currentPage === 'cart.php' ? 'bg-blue-100' : 'hover:bg-blue-100' ?>">
                <i class="fas fa-shopping-cart text-blue-500 text-xl"></i>
            </a>

            <!-- Profile -->
            <a href="../Customer/profile.php"
               title="Profile"
               class="w-10 h-10 flex items-center justify-center rounded-full transition
               <?= $currentPage === 'profile.php' ? 'bg-blue-100' : 'hover:bg-blue-100' ?>">
                <i class="fas fa-user text-blue-500 text-xl"></i>
            </a>
        </div>
    </div>
</div>
