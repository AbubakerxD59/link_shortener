<script>
(function() {
    function copyText(text) {
        if (navigator.clipboard && navigator.clipboard.writeText) {
            return navigator.clipboard.writeText(text);
        }
        return new Promise(function(resolve, reject) {
            var ta = document.createElement('textarea');
            ta.value = text;
            ta.style.position = 'fixed';
            ta.style.left = '-9999px';
            document.body.appendChild(ta);
            ta.select();
            try {
                document.execCommand('copy');
                resolve();
            } catch (e) {
                reject(e);
            }
            document.body.removeChild(ta);
        });
    }

    document.addEventListener('click', function(e) {
        var copyBtn = e.target.closest('[data-copy-text]');
        if (!copyBtn) return;
        var text = copyBtn.getAttribute('data-copy-text');
        if (!text) return;
        copyText(text)
            .then(function() { showToast('Copied to clipboard!', 'success'); })
            .catch(function() { showToast('Could not copy. Select and copy manually.', 'error'); });
    });
})();
</script>
