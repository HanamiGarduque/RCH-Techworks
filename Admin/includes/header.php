<?php
// Set default values if the page didn't define them
if (!isset($pageTitle)) {
    $pageTitle = "Dashboard";
}
if (!isset($pageSubtitle)) {
    $pageSubtitle = "Welcome to the dashboard overview.";
}
?>

<!-- Header -->
<div class="bg-white border-b border-gray-100 shadow-sm p-4 transition-all duration-300 ease-in-out">
    <div class="flex items-center justify-between">
        <div class="flex flex-col">
            <h1 class="text-3xl font-semibold text-blue-500"><?= htmlspecialchars($pageTitle) ?></h1>
            <p class="text-sm text-gray-700 mt-1"><?= htmlspecialchars($pageSubtitle) ?></p>
        </div>

        <div class="flex items-center space-x-4">
            <a href="../Notification/notifications.php" title="Notifications" class="w-10 h-10 flex items-center justify-center rounded-full hover:bg-blue-100 cursor-pointer transition">
                <i class="fas fa-bell text-blue-500 text-xl"></i>
            </a>
            <a href="../Messaging/admin_messages.php" title="Messages" class="w-10 h-10 flex items-center justify-center rounded-full hover:bg-blue-100 cursor-pointer transition">
                <i class="fas fa-envelope text-blue-500 text-xl"></i>
            </a>
            <a href="../Admin/profile.php" title="Profile" class="w-10 h-10 flex items-center justify-center rounded-full hover:bg-blue-100 cursor-pointer transition">
                <i class="fas fa-user text-blue-500 text-xl"></i>
            </a>
        </div>
    </div>
</div>
