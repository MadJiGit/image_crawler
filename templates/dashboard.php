<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Image Crawler Dashboard</title>
    <link rel="icon" type="image/png" href="../assets/favicon.png">
    <link rel="shortcut icon" type="image/png" href="../assets/favicon.png">
    <link rel="stylesheet" href="../assets/style.css">
</head>
<body>
    <div class="container">
        <header>
            <h1>Image Crawler Dashboard</h1>
            <p class="subtitle">Historical image counts for tracked URLs</p>
        </header>

        <main>
            <?php if (empty($urls)): ?>
                <div class="alert">
                    <p>No URLs found in database.</p>
                </div>
            <?php elseif (empty($dates)): ?>
                <div class="alert">
                    <p>No crawl data found. Run the crawler to collect data.</p>
                </div>
            <?php else: ?>
                <div class="table-header">
                    <div class="table-controls">
                        <label class="checkbox-label">
                            <input type="checkbox" id="hideEmptyRows" />
                            <span>Hide URLs without data</span>
                        </label>
                        <button id="refreshTableBtn" class="btn btn-secondary">Refresh Table</button>
                    </div>
                </div>
                <div class="table-wrapper">
                    <table>
                        <thead>
                            <tr>
                                <th class="url-column">URL</th>
                                <?php foreach ($dates as $date): ?>
                                    <th><?= htmlspecialchars($date) ?></th>
                                <?php endforeach; ?>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($data as $urlId => $urlData): ?>
                                <tr>
                                    <td class="url-cell">
                                        <a href="<?= htmlspecialchars($urlData['url']) ?>" target="_blank">
                                            <?= htmlspecialchars($urlData['url']) ?>
                                        </a>
                                    </td>
                                    <?php foreach ($dates as $date): ?>
                                        <td class="count-cell">
                                            <?= isset($urlData['counts'][$date]) ? $urlData['counts'][$date] : '-' ?>
                                        </td>
                                    <?php endforeach; ?>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>

            <!-- Terminal Console -->
            <section class="terminal-section">
                <div class="terminal-header">
                    <span class="terminal-title">Crawler Console</span>
                    <div class="terminal-actions">
                        <button id="runCrawlerBtn" class="btn btn-primary">Run Crawler</button>
                    </div>
                </div>
                <iframe id="crawlerFrame" name="crawlerFrame" class="crawler-iframe"></iframe>
            </section>
        </main>

        <footer>
            <div class="footer-content">
                <div class="credits">
                    Developed by <a href="https://mladenraykov.com" target="_blank">
                        <img class="madji-img-footer" src="../assets/logo.png" alt="MadJi">
                    </a>
                </div>
            </div>
        </footer>
    </div>

    <script src="../assets/app.js"></script>
</body>
</html>
