<?php
require_once __DIR__ . '/../session.php';
redirectIfRoleMismatch('admin'); // only admins can access

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Receipt Manager - RCH Water</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>
    <style>
    body {
        font-family: 'Poppins', sans-serif;
        overflow-x: hidden;
    }

    /* Use scrollbar-gutter to reserve space without forcing scrollbar */
    html {
        scrollbar-gutter: stable;
    }

    /* Override SweetAlert2's body modifications */
    body.swal2-shown:not(.swal2-no-backdrop):not(.swal2-toast-shown) {
        overflow: hidden !important;
        padding-right: 0 !important;
    }

    body.swal2-height-auto {
        height: 100% !important;
    }

    .receipt-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
    }
    
    .swal-title-small {
        font-size: 1rem;
    }

    /* Custom scrollbar styling */
    ::-webkit-scrollbar {
        width: 8px;
        height: 8px;
    }

    ::-webkit-scrollbar-track {
        background: #f1f5f9;
    }

    ::-webkit-scrollbar-thumb {
        background: #cbd5e1;
        border-radius: 4px;
    }

    ::-webkit-scrollbar-thumb:hover {
        background: #94a3b8;
    }
</style>
</head>

<body class="bg-gray-100">
    <div class="flex h-screen overflow-hidden">
        <!-- Sidebar -->
        <script src="../Admin/includes/sidebar.js"></script>

        <!-- Main Content -->
        <div class="flex-1 ml-20 flex flex-col">
            <?php
            $pageTitle = "Receipts Manager";
            $pageSubtitle = "View and manage saved receipts";
            include '../Admin/includes/header.php';
            ?>

            <!-- Filters -->
            <div class="bg-white border-b p-4">
                <div class="flex gap-3">
                    <div class="flex-1">
                        <input type="text" id="searchInput" placeholder="Search by transaction number or customer..."
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div>
                        <input type="date" id="dateFilter"
                            class="px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    <button onclick="resetFilters()" class="bg-gray-200 hover:bg-gray-300 px-4 py-2 rounded-lg">
                        <i class="fas fa-redo mr-2"></i>Reset
                    </button>
                </div>
            </div>

            <!-- Receipts Grid -->
            <div class="flex-1 overflow-y-auto p-6">
                <div id="receiptsGrid" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4">
                    <!-- Receipts will be loaded here -->
                </div>

                <div id="emptyState" class="hidden text-center py-12">
                    <i class="fas fa-receipt text-6xl text-gray-300 mb-4"></i>
                    <p class="text-gray-500 text-lg">No receipts found</p>
                </div>

                <div id="loadingState" class="text-center py-12">
                    <i class="fas fa-spinner fa-spin text-4xl text-blue-500 mb-4"></i>
                    <p class="text-gray-500">Loading receipts...</p>
                </div>
            </div>
        </div>
    </div>
    <script src="..Admin/includes/admin_toast.js"></script>

    <script>
        let allReceipts = [];

        // Load receipts on page load
        document.addEventListener('DOMContentLoaded', () => {
            loadReceipts();
            setupEventListeners();
        });

        function setupEventListeners() {
            document.getElementById('searchInput').addEventListener('input', filterReceipts);
            document.getElementById('dateFilter').addEventListener('change', filterReceipts);
        }

        async function loadReceipts() {
            try {
                const res = await fetch('../Receipts/get_receipts.php');
                const data = await res.json();

                if (data.success) {
                    allReceipts = data.receipts;
                    renderReceipts(allReceipts);
                } else {
                    throw new Error(data.message);
                }
            } catch (err) {
                Swal.fire('Error', 'Failed to load receipts: ' + err.message, 'error');
                document.getElementById('loadingState').classList.add('hidden');
                document.getElementById('emptyState').classList.remove('hidden');
            }
        }

        function renderReceipts(receipts) {
            const grid = document.getElementById('receiptsGrid');
            const loading = document.getElementById('loadingState');
            const empty = document.getElementById('emptyState');

            loading.classList.add('hidden');

            if (receipts.length === 0) {
                grid.innerHTML = '';
                empty.classList.remove('hidden');
                return;
            }

            empty.classList.add('hidden');

            grid.innerHTML = receipts.map(receipt => `
                <div class="receipt-card bg-white rounded-lg shadow-sm border p-4 transition-all cursor-pointer"
                    onclick="viewReceipt('${receipt.image_path}', '${receipt.transaction_number}')">
                    <div class="aspect-[3/4] mb-3 bg-gray-100 rounded overflow-hidden">
                        <img src="../Assets/receipts/${receipt.image_path}" 
                            alt="Receipt ${receipt.transaction_number}"
                            class="w-full h-full object-contain">
                    </div>
                    <div class="space-y-1">
                        <h3 class="font-semibold text-sm text-gray-800 truncate">
                            ${receipt.transaction_number}
                        </h3>
                        <p class="text-xs text-gray-600 truncate">
                            <i class="fas fa-user mr-1"></i>${receipt.customer_name}
                        </p>
                        <p class="text-xs text-gray-600">
                            <i class="fas fa-calendar mr-1"></i>${formatDate(receipt.created_at)}
                        </p>
                        <p class="text-sm font-bold text-blue-600">
                            ₱${parseFloat(receipt.total_amount).toFixed(2)}
                        </p>
                    </div>
                    <div class="mt-3 pt-3 border-t flex gap-2">
                        <button onclick="event.stopPropagation(); downloadReceipt('${receipt.image_path}', '${receipt.transaction_number}')"
                            class="flex-1 bg-blue-500 hover:bg-blue-600 text-white text-xs py-2 rounded">
                            <i class="fas fa-download mr-1"></i>Download
                        </button>
                        <button onclick="event.stopPropagation(); deleteReceipt(${receipt.id}, '${receipt.transaction_number}')"
                            class="flex-1 bg-red-500 hover:bg-red-600 text-white text-xs py-2 rounded">
                            <i class="fas fa-trash mr-1"></i>Delete
                        </button>
                    </div>
                </div>
            `).join('');
        }

        function filterReceipts() {
            const search = document.getElementById('searchInput').value.toLowerCase();
            const date = document.getElementById('dateFilter').value;

            let filtered = allReceipts;

            if (search) {
                filtered = filtered.filter(r =>
                    r.transaction_number.toLowerCase().includes(search) ||
                    r.customer_name.toLowerCase().includes(search)
                );
            }

            if (date) {
                filtered = filtered.filter(r => r.created_at.startsWith(date));
            }

            renderReceipts(filtered);
        }

        function resetFilters() {
            document.getElementById('searchInput').value = '';
            document.getElementById('dateFilter').value = '';
            renderReceipts(allReceipts);
        }

        function viewReceipt(imagePath, transactionNumber) {
            Swal.fire({
                title: `Receipt: ${transactionNumber}`,
                imageUrl: `../Assets/receipts/${imagePath}`,
                imageAlt: 'Receipt',
                width: 400, // smaller width
                height: 600, // smaller width

                padding: '10px', // optional: reduce padding around the content
                showCloseButton: true,
                showCancelButton: true,
                confirmButtonText: '<i class="fas fa-download mr-2"></i>Download',
                cancelButtonText: 'Close',
                confirmButtonColor: '#3b82f6',
                customClass: {
                    title: 'swal-title-small'
                }
            }).then((result) => {
                if (result.isConfirmed) {
                    downloadReceipt(imagePath, transactionNumber);
                }
            });
        }


        function downloadReceipt(imagePath, transactionNumber) {
            const link = document.createElement('a');
            link.href = `../Assets/receipts/${imagePath}`;
            link.download = `${transactionNumber}.png`;
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);
        }

        async function deleteReceipt(id, transactionNumber) {
            const result = await Swal.fire({
                title: 'Delete Receipt?',
                text: `Are you sure you want to delete receipt ${transactionNumber}?`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#ef4444',
                cancelButtonColor: '#6b7280',
                confirmButtonText: 'Yes, delete it'
            });

            if (result.isConfirmed) {
                try {
                    const res = await fetch('../Receipts/delete_receipt.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json'
                        },
                        body: JSON.stringify({
                            id
                        })
                    });

                    const data = await res.json();

                    if (data.success) {
                        Swal.fire('Deleted!', 'Receipt has been deleted', 'success');
                        loadReceipts();
                    } else {
                        throw new Error(data.message);
                    }
                } catch (err) {
                    Swal.fire('Error', 'Failed to delete receipt: ' + err.message, 'error');
                }
            }
        }

        function formatDate(dateString) {
            const date = new Date(dateString);
            return date.toLocaleDateString('en-US', {
                month: 'short',
                day: 'numeric',
                year: 'numeric',
                hour: '2-digit',
                minute: '2-digit'
            });
        }
        (function() {
        const originalSwalFire = Swal.fire;
        let scrollPosition = 0;
        
        Swal.fire = function(...args) {
            // Store current scroll position
            scrollPosition = window.pageYOffset;
            
            // Get scrollbar width
            const scrollbarWidth = window.innerWidth - document.documentElement.clientWidth;
            
            // Fix body position
            document.body.style.overflow = 'hidden';
            document.body.style.position = 'fixed';
            document.body.style.top = `-${scrollPosition}px`;
            document.body.style.width = '100%';
            document.body.style.paddingRight = scrollbarWidth + 'px';
            
            // Call original SweetAlert
            const result = originalSwalFire.apply(this, args);
            
            // Restore body on close
            result.finally(() => {
                document.body.style.overflow = '';
                document.body.style.position = '';
                document.body.style.top = '';
                document.body.style.width = '';
                document.body.style.paddingRight = '';
                window.scrollTo(0, scrollPosition);
            });
            
            return result;
        };
    })();

    </script>
</body>

</html>