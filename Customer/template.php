<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>RCH Water - Order Water Refill</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <style>
        body {
            font-family: 'Poppins', sans-serif;
        }

        /* Step indicator styling */
        .step-dots {
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 1.5rem;
            gap: 10px;
        }

        .step-dot {
            width: 2.5rem;
            height: 2.5rem;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 9999px;
            transition: all .3s;
        }

        .step-line {
            flex-grow: 1;
            height: 6px;
            background-color: #E5E7EB;
            border-radius: 4px;
            transition: background-color .3s;
        }

        .step-dot.completed,
        .step-dot.active {
            background-color: #2563eb;
            color: white;
        }

        .step-dot.pending {
            background-color: #E5E7EB;
            color: #4B5563;
        }

        .step-line.completed {
            background-color: #2563eb;
        }

        /* Disabled time slot styling */
        button[disabled] {
            cursor: not-allowed !important;
            opacity: 0.5 !important;
            background-color: #f3f4f6 !important;
            color: #9ca3af !important;
        }

        button[disabled]:hover {
            background-color: #f3f4f6 !important;
            color: #9ca3af !important;
        }
    </style>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>

<body class="bg-gray-100 overflow-hidden">
    <div class="flex h-screen">
        <!-- Sidebar -->
        <script src="includes/sidebar.js"></script>

        <!-- Main Content -->
        <main class="flex-1 ml-20 bg-gray-50 rounded-xl shadow overflow-y-auto">
            <div class="main-header">
                <?php //ibahin mo lang tong header na to sa bawat page para magiba title at subtitle
                $pageTitle = "Order Water Refill";
                $pageSubtitle = "Hydrate made easy—order and we'll handle the rest!";
                include 'includes/header.php';
                ?>
            </div>
            <!-- open -->
             
            <!-- dito ka mag iinsert ng content mo tin -->

            <!-- close -->
        </main>
    </div>
</body>