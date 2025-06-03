<?php
require_once '../vendor/autoload.php';

use Searchcraft\Searchcraft;
use Searchcraft\Exception\SearchcraftException;

$config = require_once 'config.php';

$apiKey = $config['api_key'];
$apiEndpoint = $config['api_endpoint'];
$indexName = $config['index_name'];
if (empty($apiEndpoint)) {
    die('Error: api_endpoint is not configured');
}

$searchcraft = new Searchcraft($apiKey, Searchcraft::KEY_TYPE_READ, $apiEndpoint);
$query = '';
$options = [
    'limit' => 10,
    'mode' => 'fuzzy'
];


if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
    $query = $_GET['query'] ?? '';

    try {
        $response = $searchcraft->search()->query($indexName, $query, $options);
        header('Content-Type: application/json');
        echo json_encode($response);
        exit;
    } catch (SearchcraftException $e) {
        header('Content-Type: application/json');
        echo json_encode([
            'error' => $e->getMessage(),
            'errorData' => $e->getErrorData()
        ]);
        exit;
    } catch (Exception $e) {
        header('Content-Type: application/json');
        echo json_encode(['error' => "General Error: " . $e->getMessage()]);
        exit;
    }
}

$searchResults = null;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Searchcraft API PHP Client: Search Example</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary-bg: #0b0c17;
            --secondary-bg: #14162b;
            --panel-bg: #1d1f36;
            --primary-text: #ffffff;
            --secondary-text: #9ea4b6;
            --accent-color: #5d4fff;
            --accent-glow: #5d4fff60;
            --highlight-color: #00eeff;
            --highlight-glow: #00eeff40;
            --gradient-start: #4633cc;
            --gradient-mid: #5d4fff;
            --gradient-end: #00eeff;
            --border-color: #363864;
            --success-color: #00eab5;
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: 'Inter', sans-serif;
            background-color: var(--primary-bg);
            color: var(--primary-text);
            margin: 0;
            padding: 0;
            background-image:
                radial-gradient(circle at 10% 10%, rgba(93, 79, 255, 0.1) 0%, transparent 30%),
                radial-gradient(circle at 90% 90%, rgba(0, 238, 255, 0.1) 0%, transparent 30%);
            min-height: 100vh;
        }

        .container {
            max-width: 1000px;
            margin: 0 auto;
            padding: 40px 20px;
        }

        h1 {
            font-weight: 700;
            font-size: 2.5rem;
            margin-bottom: 40px;
            background: linear-gradient(90deg, var(--gradient-mid), var(--highlight-color));
            -webkit-background-clip: text;
            background-clip: text;
            -webkit-text-fill-color: transparent;
            text-align: center;
            letter-spacing: -0.5px;
        }

        h2 {
            font-weight: 600;
            font-size: 1.5rem;
            margin-bottom: 20px;
            color: var(--primary-text);
        }

        .search-form {
            margin-bottom: 30px;
            position: relative;
            display: flex;
            align-items: center;
        }

        .search-input {
            width: 100%;
            padding: 16px 20px;
            font-size: 16px;
            background-color: var(--secondary-bg);
            border: 1px solid var(--border-color);
            border-radius: 8px;
            color: var(--primary-text);
            transition: all 0.3s ease;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.25);
        }

        .search-input:focus {
            outline: none;
            box-shadow: 0 0 0 2px var(--accent-glow), 0 4
            px 20px rgba(0, 0, 0, 0.3);
            border-color: var(--accent-color);
        }

        #loading {
            display: none;
            margin-left: 10px;
            color: var(--secondary-text);
            position: absolute;
            right: 20px;
            top: 50%;
            transform: translateY(-50%);
        }

        .results {
            background-color: var(--secondary-bg);
            border-radius: 12px;
            padding: 30px;
            box-shadow: 0 8px 30px rgba(0, 0, 0, 0.2);
            border: 1px solid var(--border-color);
        }

        .error {
            color: #ff5e65;
            padding: 15px;
            background-color: rgba(255, 94, 101, 0.1);
            border-radius: 8px;
            margin-bottom: 20px;
            border: 1px solid rgba(255, 94, 101, 0.3);
        }

        .hit {
            margin-bottom: 20px;
            padding: 20px;
            background-color: var(--panel-bg);
            border-radius: 8px;
            border: 1px solid var(--border-color);
            overflow: hidden;
            width: 100%;
            transition: all 0.2s ease;
        }

        .hit:hover {
            box-shadow: 0 0 0 1px var(--accent-glow), 0 4px 15px rgba(0, 0, 0, 0.2);
            transform: translateY(-2px);
        }

        .hit p {
            margin-bottom: 10px;
            color: var(--secondary-text);
        }

        .hit strong {
            color: var(--primary-text);
            font-weight: 500;
        }

        pre {
            white-space: pre-wrap;
            word-wrap: break-word;
            overflow-wrap: break-word;
            max-width: 100%;
            overflow-x: auto;
            background-color: rgba(0, 0, 0, 0.2);
            padding: 15px;
            border-radius: 6px;
            font-size: 13px;
            line-height: 1.5;
            margin: 0;
            color: var(--secondary-text);
            border: 1px solid var(--border-color);
        }

        #hits-container {
            width: 100%;
        }

        #total-hits, #time-taken {
            display: inline-block;
            margin-right: 20px;
            color: var(--secondary-text);
            font-size: 0.9rem;
        }

        #time-taken {
            color: var(--highlight-color);
        }

        .stats-container {
            display: flex;
            margin-bottom: 20px;
            background-color: var(--panel-bg);
            padding: 10px 15px;
            border-radius: 6px;
            border: 1px solid var(--border-color);
        }

        .logo {
            text-align: center;
            margin-bottom: 30px;
        }

        .logo svg {
            height: 30px;
            width: auto;
        }

        #facets-container {
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid var(--border-color);
        }

        #facets-container h3 {
            font-size: 1.3rem;
            margin-bottom: 15px;
            color: var(--primary-text);
        }

        #facets-container h4 {
            font-size: 1.1rem;
            color: var(--highlight-color);
            margin: 15px 0 10px;
        }

        #facets-container ul {
            list-style-type: none;
            padding-left: 10px;
        }

        #facets-container li {
            margin-bottom: 8px;
            color: var(--secondary-text);
        }

        .glow-container {
            position: relative;
            z-index: 1;
        }

        .glow-container::before {
            content: "";
            position: absolute;
            top: -50%;
            left: -50%;
            width: 200%;
            height: 200%;
            background: radial-gradient(
                circle at center,
                var(--accent-glow) 0%,
                transparent 70%
            );
            opacity: 0.5;
            z-index: -1;
            pointer-events: none;
        }

        @keyframes pulse {
            0% { opacity: 0.6; }
            50% { opacity: 1; }
            100% { opacity: 0.6; }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="logo">
            <svg xmlns="http://www.w3.org/2000/svg" version="1.1" viewBox="0 0 402 60">
                <defs>
                    <linearGradient id="logo-gradient" x1="0%" y1="0%" x2="100%" y2="0%">
                        <stop offset="0%" stop-color="var(--gradient-mid)" />
                        <stop offset="100%" stop-color="var(--highlight-color)" />
                    </linearGradient>
                </defs>
                <path style="fill: url(#logo-gradient);" d="M176.9,25.7c0-6.5-4.8-10.3-15.5-10.3s-16.1,3.9-16.6,11.6h11.6c.3-2.8,1.7-3.5,4.9-3.5s4.1,1.1,4.1,2.6-1.3,2.5-3.9,2.7l-5.3,.4c-6,.5-9.7,2.1-11.5,4.7h0c0-11.8-7.2-18.6-17.7-18.6s-17.4,6.6-17.6,16.8c-1.2-6.1-6.3-9.4-15.9-11l-3.8-.6c-4.5-.8-6.6-1.7-6.6-4.5s1.8-3.8,5.8-3.8,7,1.7,7.2,5.3h12c-.5-9.9-7.4-15-19.4-15s-18.6,6.2-18.6,14,5.5,13,15.7,14.7l3.6,.6c5.5,.9,7.1,1.8,7.1,4.4s-2,4.1-6.4,4.1-8.4-.9-8.5-6.2h-12.2c0,9.3,6.6,15.9,20.3,15.9s19.6-4.3,20-13.9c1.4,8.4,8,13.6,17.4,13.6s13.4-3.2,16-9c.6,5.9,5.7,9,12,9s8.4-1.6,10.5-4.1c0,1.3.4,2.5.8,3.4h11.8c-.9-1.2-1.3-3.4-1.3-5.9v-17.4h0ZM126.9,23.7c3.4,0,5.7,1.8,6.5,5.4h-12.8c.8-3.8,3.2-5.4,6.3-5.4ZM132.9,38.7c-1.1,1.9-3.1,2.9-5.8,2.9s-5.8-1.8-6.6-5.5h23.1c-.3.8-.5,1.7-.5,2.7h-10.2ZM165.4,36.1c0,4.7-2.8,6.2-6.2,6.2s-4.2-1.2-4.2-3.2,1.3-3,3.8-3.3l3.8-.4c1.5-.2,2.4-.5,2.9-1.3v1.9h-.1ZM256.1,15.4c-4.6,0-7.8,2-10,4.8V3.7h-11.7v22.8c-1.8-6.9-7.8-11.1-16.3-11.1s-12.5,3-15.3,8v-7.5c-.7-.1-1.5-.2-2.4-.2-4.8,0-7.7,2.4-8.9,7v-6.5h-11.5v32.9h11.7v-14c0-6.1,2.7-8.7,7.9-8.7h1.8c-.6,1.9-.9,4-.9,6.2,0,10.4,6.8,17.2,17.5,17.2s14.4-4,16.3-10.6v9.8h11.7v-18.2c0-3.9,2.5-5.5,5.2-5.5s4.5,1.8,4.5,4.9v18.8h11.7v-21.3c0-8.5-5.1-12.4-11.3-12.4h0ZM223.6,35.5c-.3,3.4-2.5,4.8-5.5,4.8s-5.7-2.3-5.7-7.8,2-7.8,5.7-7.8,4.7,1.3,5.2,4.2h11.1v6.6h-10.8ZM291.8,35.5h11.5c-.5,8.8-7.3,14.3-17,14.3s-17.5-6.8-17.5-17.2,6.8-17.2,17.5-17.2,15.9,5.2,16.8,13.6h-11.5c-.5-2.9-2.5-4.2-5.2-4.2s-5.7,2.4-5.7,7.8,2,7.8,5.7,7.8,5.2-1.4,5.5-4.8h0ZM401.5,24.5v-8.3h-6.2V6.2h-11.7v9.9h-10.4v-.9c0-2,1-3.1,3.8-3.1h2.5V3.7c-1.6-.2-3.5-.3-5.2-.3-9.6,0-12.9,5.6-12.9,11.5v1.3h-4.6v6.7c-1.3-4.7-6.1-7.5-15.1-7.5s-12.9,2.1-15.2,6.3v-5.8c-.7-.1-1.5-.2-2.4-.2-4.8,0-7.7,2.4-8.9,7v-6.5h-11.5v32.9h11.7v-13.4c0-6.1,2.7-8.7,7.9-8.7h13.2c.3-2.8,1.7-3.5,4.9-3.5s4.1,1.1,4.1,2.6-1.3,2.5-3.9,2.7l-5.3,.4c-9.6,.8-13.1,4.4-13.1,10.3s5.3,10.3,12.1,10.3,8.4-1.6,10.5-4.1c0,1.3.4,2.5,.8,3.4h11.8c-.9-1.2-1.3-3.4-1.3-5.9v-18.7h4.3v24.6h11.7v-24.6h10.4v14.5c0,7.3,3.6,10.4,12.9,10.4s3.4,0,5-.3v-8.5h-2.4c-2.4,0-3.8-.2-3.8-2.9v-13.1h6.3ZM345.7,36.1c0,4.7-2.8,6.2-6.2,6.2s-4.2-1.2-4.2-3.2,1.3-3,3.8-3.3l3.8-.4c1.5-.2,2.4-.5,2.9-1.3v1.9h0ZM52.1,44.8c-8.1,8.1-20.1,9.8-29.9,5.1l-7.3,7.3c-3.4,3.4-9,3.4-12.4,0-3.4-3.4-3.4-9,0-12.4l7.3-7.3c1.3,2.6,3,5.1,5.1,7.2,2.2,2.2,4.6,3.9,7.2,5.1l17.5-17.5c3.4-3.4,3.4-9,0-12.4s-9-3.4-12.4,0l-17.5,17.5c-4.7-9.8-3-21.8,5.1-29.9,10.3-10.3,26.9-10.3,37.1,0,10.3,10.3,10.3,26.9,0,37.1h0l.2,.2Z"></path>
            </svg>
        </div>

        <h1>PHP Client: Search Example</h1>

        <div class="search-form glow-container">
            <input type="text" id="search-input" placeholder="Enter search query..." class="search-input" value="<?php echo htmlspecialchars($query); ?>">
            <span id="loading">Searching...</span>
        </div>

        <div id="error-container" class="error" style="display: none;"></div>

        <div id="results-container" class="results">
            <h2>Search Results</h2>

            <div class="stats-container">
                <p id="total-hits">Total hits: 0</p>
                <p id="time-taken">Time taken: 0 ms</p>
            </div>

            <div id="hits-container"></div>
            <div id="facets-container"></div>
        </div>
    </div>

    <script>
        let searchTimer;
        const searchInput = document.getElementById('search-input');
        const loadingIndicator = document.getElementById('loading');
        const errorContainer = document.getElementById('error-container');
        const resultsContainer = document.getElementById('results-container');
        const totalHitsElement = document.getElementById('total-hits');
        const timeTakenElement = document.getElementById('time-taken');
        const hitsContainer = document.getElementById('hits-container');
        const facetsContainer = document.getElementById('facets-container');

        // Function to perform search
        function performSearch(query) {
            if (query.trim() === '') {
                resetResults();
                return;
            }

            loadingIndicator.style.display = 'inline';
            errorContainer.style.display = 'none';

            fetch(`?query=${encodeURIComponent(query)}`, {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(response => response.json())
            .then(data => {
                loadingIndicator.style.display = 'none';

                if (data.error) {
                    showError(data.error, data.errorData);
                    return;
                }

                displayResults(data);
            })
            .catch(error => {
                loadingIndicator.style.display = 'none';
                showError("Error fetching results: " + error.message);
            });
        }

        function displayResults(response) {
            const data = response.data;

            totalHitsElement.textContent = `Total hits: ${data.count}`;
            const timeInMs = Math.round(data.time_taken * 1000);
            timeTakenElement.textContent = `Time taken: ${timeInMs} ms`;

            hitsContainer.innerHTML = '';
            facetsContainer.innerHTML = '';

            if (data.hits.length === 0) {
                hitsContainer.innerHTML = '<p>No results found.</p>';
                return;
            }

            data.hits.forEach(hit => {
                const hitElement = document.createElement('div');
                hitElement.className = 'hit';
                hitElement.innerHTML = `
                    <p><strong>Document ID:</strong> ${hit.document_id}</p>
                    <p><strong>Score:</strong> ${hit.score}</p>
                    <p><strong>Source Index:</strong> ${hit.source_index}</p>
                    <p><strong>Document:</strong></p>
                    <pre>${JSON.stringify(hit.doc, null, 2)}</pre>
                `;
                hitsContainer.appendChild(hitElement);
            });

            if (data.facets && Object.keys(data.facets).length > 0) {
                let facetsHtml = '<h3>Facets</h3>';

                for (const facetGroup of data.facets) {
                    for (const [facetName, facetValues] of Object.entries(facetGroup)) {
                        facetsHtml += `<h4>${facetName} facets:</h4><ul>`;

                        for (const facet of facetValues) {
                            facetsHtml += `<li>Path: ${facet.path}, Count: ${facet.count}`;

                            if (facet.children && facet.children.length > 0) {
                                facetsHtml += '<ul>';
                                for (const child of facet.children) {
                                    facetsHtml += `<li>Path: ${child.path}, Count: ${child.count}</li>`;
                                }
                                facetsHtml += '</ul>';
                            }

                            facetsHtml += '</li>';
                        }

                        facetsHtml += '</ul>';
                    }
                }

                facetsContainer.innerHTML = facetsHtml;
            }
        }

        function resetResults() {
            totalHitsElement.textContent = 'Total hits: 0';
            timeTakenElement.textContent = 'Time taken: 0 ms';
            hitsContainer.innerHTML = '<p class="empty-results">Enter a search query above.</p>';
            facetsContainer.innerHTML = '';
        }

        function showError(message, errorData) {
            errorContainer.style.display = 'block';
            let errorHtml = message;

            if (errorData) {
                errorHtml += `<pre>${JSON.stringify(errorData, null, 2)}</pre>`;
            }

            errorContainer.innerHTML = errorHtml;
        }


        searchInput.addEventListener('input', function() {

            clearTimeout(searchTimer);

            searchTimer = setTimeout(() => {
                performSearch(this.value);
            }, 90);
        });

        searchInput.addEventListener('focus', function() {
            this.parentElement.classList.add('focused');
        });

        searchInput.addEventListener('blur', function() {
            this.parentElement.classList.remove('focused');
        });

        if (searchInput.value.trim() !== '') {
            performSearch(searchInput.value);
        } else {
            resetResults();
        }
    </script>
</body>
</html>
