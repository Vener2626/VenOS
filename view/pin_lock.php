<!-- PIN lock overlay: always shown on load for owner-only pages, regardless of any earlier unlock -->
<div id="pin-lock-overlay" class="fixed inset-0 z-[70] flex items-center justify-center bg-surface">
    <div class="w-full max-w-xs px-6 text-center">
        <div class="w-14 h-14 mx-auto mb-4 rounded-full bg-accent/10 flex items-center justify-center text-accent">
            <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
            </svg>
        </div>
        <h2 class="text-lg font-bold text-gray-100 mb-1">Owner Access</h2>
        <p class="text-sm text-gray-500 mb-6">Enter the 4-digit PIN to continue.</p>

        <div id="pin-dots" class="flex items-center justify-center gap-3 mb-2">
            <span class="pin-dot w-4 h-4 rounded-full border-2 border-white/20 transition"></span>
            <span class="pin-dot w-4 h-4 rounded-full border-2 border-white/20 transition"></span>
            <span class="pin-dot w-4 h-4 rounded-full border-2 border-white/20 transition"></span>
            <span class="pin-dot w-4 h-4 rounded-full border-2 border-white/20 transition"></span>
        </div>
        <div id="pin-error" class="hidden text-xs text-red-400 mb-2 h-4"></div>

        <div id="pin-keypad" class="grid grid-cols-3 gap-3 mt-6 justify-items-center"></div>

        <a href="index.php" class="inline-block mt-6 text-xs text-gray-500 hover:text-gray-300">&larr; Back to Sale</a>
    </div>
</div>