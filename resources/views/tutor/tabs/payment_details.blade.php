<!-- Payment Information Content -->
<div class="space-y-6">
    <!-- Row 1: Section Title -->
    <h3 class="text-lg md:text-xl font-semibold text-[#F39C12] dark:text-[#F39C12]">
        Payment Information
    </h3>
    <hr>
    <!-- Row 2: p text (left) + button (right) -->
    <div class="flex items-center justify-between">
        <p class="text-sm text-gray-500 dark:text-gray-400">
            Manage your payment info
        </p>
        <button
            class="bg-[#F39C12] text-white font-semibold px-6 py-2 rounded-full hover:bg-[#D97706] transition-colors duration-200">
            Edit Payment Info
        </button>
    </div>


    <!-- Payment Info Card -->
    <div
        class="bg-white dark:bg-gray-800 rounded-lg shadow-md p-4 md:p-6 space-y-4 border border-gray-200 dark:border-gray-700">

        <!-- Payment Method -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label class="block text-gray-700 dark:text-gray-200 font-medium mb-1">Payment Method</label>
                <input type="text" value="BDO" disabled
                    class="w-full border border-gray-300 dark:border-gray-600 rounded-md px-3 py-2 bg-gray-100 dark:bg-gray-900 text-gray-500 dark:text-gray-400 cursor-not-allowed">
            </div>

            <div>
                <label class="block text-gray-700 dark:text-gray-200 font-medium mb-1">Account Number</label>
                <input type="text" value="N/A" disabled
                    class="w-full border border-gray-300 dark:border-gray-600 rounded-md px-3 py-2 bg-gray-100 dark:bg-gray-900 text-gray-500 dark:text-gray-400 cursor-not-allowed">
            </div>

            <div class="md:col-span-2">
                <label class="block text-gray-700 dark:text-gray-200 font-medium mb-1">Account Name</label>
                <input type="text" value="N/A" disabled
                    class="w-full border border-gray-300 dark:border-gray-600 rounded-md px-3 py-2 bg-gray-100 dark:bg-gray-900 text-gray-500 dark:text-gray-400 cursor-not-allowed">
            </div>
        </div>
    </div>
</div>
