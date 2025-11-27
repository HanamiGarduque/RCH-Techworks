
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
