/**
 * Image Crawler Dashboard - iframe version
 */

document.addEventListener('DOMContentLoaded', function() {
    const runCrawlerBtn = document.getElementById('runCrawlerBtn');
    const refreshTableBtn = document.getElementById('refreshTableBtn');
    const crawlerFrame = document.getElementById('crawlerFrame');
    const hideEmptyRowsCheckbox = document.getElementById('hideEmptyRows');

    // Run Crawler button handler
    runCrawlerBtn.addEventListener('click', function() {
        runCrawler();
    });

    // Refresh Table button handler
    refreshTableBtn.addEventListener('click', function() {
        window.location.reload();
    });

    // Hide/Show empty rows handler
    if (hideEmptyRowsCheckbox) {
        // Load saved state from localStorage
        const savedState = localStorage.getItem('hideEmptyRows');
        if (savedState !== null) {
            hideEmptyRowsCheckbox.checked = savedState === 'true';
            // Apply filter on page load
            filterEmptyRows(hideEmptyRowsCheckbox.checked);
        }

        // Save state on change
        hideEmptyRowsCheckbox.addEventListener('change', function() {
            localStorage.setItem('hideEmptyRows', this.checked);
            filterEmptyRows(this.checked);
        });
    }

    /**
     * Filter table rows - hide/show rows without data
     */
    function filterEmptyRows(hideEmpty) {
        const tableRows = document.querySelectorAll('tbody tr');

        tableRows.forEach(function(row) {
            const countCells = row.querySelectorAll('.count-cell');
            let hasData = false;

            // Check if any cell has data (not "-")
            countCells.forEach(function(cell) {
                if (cell.textContent.trim() !== '-') {
                    hasData = true;
                }
            });

            // Hide row if it has no data and checkbox is checked
            if (hideEmpty && !hasData) {
                row.style.display = 'none';
            } else {
                row.style.display = '';
            }
        });
    }

    /**
     * Run the crawler in iframe
     */
    function runCrawler() {
        // Disable button
        runCrawlerBtn.disabled = true;
        runCrawlerBtn.textContent = 'Running...';

        // Show iframe
        crawlerFrame.style.display = 'block';

        // Load crawl.php in iframe
        crawlerFrame.src = 'crawl.php';

        // Listen for iframe load complete
        crawlerFrame.onload = function() {
            // Re-enable button after a delay (crawler finished)
            setTimeout(function() {
                runCrawlerBtn.disabled = false;
                runCrawlerBtn.textContent = 'Run Crawler';
            }, 1000);
        };
    }
});
