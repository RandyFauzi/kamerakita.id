<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Invoice - {{ $invoice_no }}</title>
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap');
        
        body {
            font-family: 'Inter', sans-serif;
            background-color: #f3f4f6;
            color: #333;
            -webkit-print-color-adjust: exact !important;
            print-color-adjust: exact !important;
        }

        .invoice-box {
            max-width: 800px;
            margin: 40px auto;
            padding: 50px;
            background: #fff;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
            min-height: 1000px; /* roughly A4 height for preview */
            position: relative;
        }

        @media print {
            body {
                background-color: #fff;
            }
            .invoice-box {
                margin: 0;
                padding: 20px;
                box-shadow: none;
                width: 100%;
                max-width: 100%;
                min-height: auto;
            }
            .no-print {
                display: none !important;
            }
        }
        
        .theme-blue {
            color: #2563eb; /* Tailwind blue-600 */
        }
        
        .bg-theme-blue {
            background-color: #2563eb;
        }
    </style>
</head>
<body>

    <div class="text-center py-4 no-print">
        <button onclick="window.print()" class="px-6 py-2 bg-indigo-600 text-white font-semibold rounded-md shadow hover:bg-indigo-700 transition">
            Print / Save as PDF
        </button>
    </div>

    <div class="invoice-box">
        <!-- Header -->
        <div class="flex justify-between items-end pb-4 border-b-2 border-blue-600 mb-8">
            <div class="flex items-center gap-3">
                <img src="{{ asset('vendor-assets/kamerakita/logo-mark.svg') }}" alt="KameraKita Logo" class="h-10">
                <h1 class="text-3xl font-bold theme-blue">KameraKita AI</h1>
            </div>
            <div>
                <h2 class="text-2xl font-bold text-gray-700 uppercase tracking-wide">Invoice Summary</h2>
            </div>
        </div>

        <!-- Meta info -->
        <div class="flex justify-end mb-12">
            <table class="text-sm text-gray-700">
                <tr>
                    <td class="font-bold pr-4 py-1">Invoice No:</td>
                    <td>{{ $invoice_no }}</td>
                </tr>
                <tr>
                    <td class="font-bold pr-4 py-1">Date:</td>
                    <td>{{ $invoice_date }}</td>
                </tr>
            </table>
        </div>

        <!-- Billed To -->
        <div class="mb-12 text-sm text-gray-800">
            <p class="font-bold mb-1 text-gray-600 uppercase tracking-wide">Billed To:</p>
            <p class="font-bold text-base">MyTron Labs Inc.</p>
            <p>8 The Green, STE A, Dover City,</p>
            <p>Kent County, DE 19901</p>
        </div>

        <!-- Table -->
        <div class="mb-8">
            <table class="w-full text-sm text-left">
                <thead class="bg-theme-blue text-white">
                    <tr>
                        <th class="py-3 px-4 font-bold uppercase tracking-wider">Description</th>
                        <th class="py-3 px-4 font-bold uppercase tracking-wider text-center">Total Workers</th>
                        <th class="py-3 px-4 font-bold uppercase tracking-wider text-center">Total Approved Hours</th>
                        <th class="py-3 px-4 font-bold uppercase tracking-wider text-center">Total Amount</th>
                    </tr>
                </thead>
                <tbody class="border-b border-gray-300">
                    <tr>
                        <td class="py-4 px-4 text-gray-800">Kamera Kita Workers</td>
                        <td class="py-4 px-4 text-center text-gray-800">{{ $total_workers }}</td>
                        <td class="py-4 px-4 text-center text-gray-800">{{ $total_approved_hours }}</td>
                        <td class="py-4 px-4 text-center text-gray-800">{{ $total_amount }}</td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- Disclaimer -->
        <div class="text-sm italic text-gray-600 mb-20">
            * This summary represents the total workload without individual email details. The applied rate is 4.00 per billable hour.
        </div>

        <!-- Footer -->
        <div class="absolute bottom-12 left-0 right-0 px-12">
            <div class="border-t border-gray-200 pt-4 text-center">
                <p class="text-sm text-gray-500 mb-1">Thank you for your business!</p>
                <p class="text-sm font-bold text-gray-400">KameraKita AI</p>
            </div>
        </div>
    </div>

</body>
</html>
