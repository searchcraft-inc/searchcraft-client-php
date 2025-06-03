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


$searchcraft = new Searchcraft($apiKey, Searchcraft::KEY_TYPE_ADMIN, $apiEndpoint);

if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
    $operation = $_POST['operation'] ?? '';

    try {
        $response = null;

        switch ($operation) {
            case 'create_index':
                $response = $searchcraft->index()->createIndex($indexName, [
                    'language' => 'en',
                    'search_fields' => ['title', 'content', 'tags'],
                    'fields' => [
                        'id' => [
                            'type' => 'text',
                            'required' => true,
                            'stored' => true,
                            'indexed' => false
                        ],
                        'title' => [
                            'type' => 'text',
                            'stored' => true
                        ],
                        'content' => [
                            'type' => 'text',
                            'stored' => true
                        ],
                        'tags' => [
                            'type' => 'text',
                            'stored' => true,
                            'multi' => true
                        ],
                        'category' => [
                            'type' => 'facet',
                            'stored' => true
                        ],
                        'publishedAt' => [
                            'type' => 'datetime',
                            'fast' => true,
                            'stored' => true,
                            'indexed' => true
                        ],
                        'priority' => [
                            'type' => 'i64',
                            'stored' => true,
                            'indexed' => true
                        ]
                    ],
                    'weight_multipliers' => [
                        'title' => 2.0,
                        'tags' => 1.5,
                        'content' => 1.0
                    ]
                ]);
                break;

            case 'add_documents':
                $testDocuments = [
                    [
                        'id' => '1',
                        'title' => 'Getting Started with Searchcraft',
                        'content' => 'Searchcraft is a powerful search engine that provides fast and accurate search results. This guide will help you get started with building your first search application.',
                        'tags' => ['tutorial', 'getting-started', 'search'],
                        'category' => '/documentation',
                        'publishedAt' => '2024-01-15T10:00:00Z',
                        'priority' => 10
                    ],
                    [
                        'id' => '2',
                        'title' => 'Advanced Search Techniques',
                        'content' => 'Learn how to create complex search queries, use faceted search, and implement real-time search suggestions with Searchcraft advanced features.',
                        'tags' => ['advanced', 'techniques', 'search', 'facets'],
                        'category' => '/tutorial',
                        'publishedAt' => '2024-01-20T14:30:00Z',
                        'priority' => 8
                    ],
                    [
                        'id' => '3',
                        'title' => 'Performance Optimization',
                        'content' => 'Optimize your Searchcraft implementation for better performance. Learn about indexing strategies, query optimization, and scaling your search infrastructure.',
                        'tags' => ['performance', 'optimization', 'scaling'],
                        'category' => '/best-practices',
                        'publishedAt' => '2024-01-25T09:15:00Z',
                        'priority' => 9
                    ],
                    [
                        'id' => '4',
                        'title' => 'Integration Examples',
                        'content' => 'Explore practical examples of integrating Searchcraft with popular frameworks and applications. Includes code samples and best practices.',
                        'tags' => ['integration', 'examples', 'frameworks'],
                        'category' => '/examples',
                        'publishedAt' => '2024-02-01T16:45:00Z',
                        'priority' => 7
                    ],
                    [
                        'id' => '5',
                        'title' => 'Security and Authentication',
                        'content' => 'Implement secure search with proper authentication and authorization. Learn about API keys, user permissions, and data protection.',
                        'tags' => ['security', 'authentication', 'permissions'],
                        'category' => '/security',
                        'publishedAt' => '2024-02-05T11:20:00Z',
                        'priority' => 10
                    ]
                ];

                $response = $searchcraft->documents()->addDocuments($indexName, $testDocuments);
                break;

            case 'patch_index':
                $response = $searchcraft->index()->patchIndex($indexName, [
                    'time_decay_field' => 'publishedAt',
                    'auto_commit_delay' => 2,
                    'exclude_stop_words' => true,
                    'weight_multipliers' => [
                        'title' => 3.0,
                        'tags' => 2.0,
                        'content' => 1.0
                    ]
                ]);
                break;

            case 'delete_index':
                $response = $searchcraft->index()->deleteIndex($indexName);
                break;

            case 'get_index':
                $response = $searchcraft->index()->getIndex($indexName);
                break;

            case 'list_indexes':
                $response = $searchcraft->index()->listIndexes();
                break;

            default:
                throw new SearchcraftException('Invalid operation');
        }

        header('Content-Type: application/json');
        echo json_encode([
            'success' => true,
            'operation' => $operation,
            'response' => $response
        ]);
        exit;

    } catch (SearchcraftException $e) {
        // Enhanced error logging for debugging
        error_log("Searchcraft Error: " . $e->getMessage());
        error_log("Error Data: " . json_encode($e->getErrorData()));

        header('Content-Type: application/json');
        echo json_encode([
            'success' => false,
            'operation' => $operation,
            'error' => $e->getMessage(),
            'errorData' => $e->getErrorData(),
            'debug' => [
                'operation' => $operation,
                'indexName' => $indexName,
                'apiEndpoint' => $apiEndpoint
            ]
        ]);
        exit;
    } catch (Exception $e) {
        error_log("General Error: " . $e->getMessage());
        error_log("Stack trace: " . $e->getTraceAsString());

        header('Content-Type: application/json');
        echo json_encode([
            'success' => false,
            'operation' => $operation,
            'error' => "General Error: " . $e->getMessage(),
            'debug' => [
                'operation' => $operation,
                'indexName' => $indexName,
                'apiEndpoint' => $apiEndpoint,
                'trace' => $e->getTraceAsString()
            ]
        ]);
        exit;
    }

}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Searchcraft API PHP Client: Index Operations Example</title>
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
            --error-color: #ff5e65;
            --warning-color: #ffa726;
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
            max-width: 1200px;
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

        .logo {
            text-align: center;
            margin-bottom: 30px;
        }

        .logo svg {
            height: 30px;
            width: auto;
        }

        .operations-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 20px;
            margin-bottom: 40px;
        }

        .operation-card {
            background-color: var(--secondary-bg);
            border: 1px solid var(--border-color);
            border-radius: 12px;
            padding: 25px;
            transition: all 0.3s ease;
        }

        .operation-card:hover {
            box-shadow: 0 0 0 1px var(--accent-glow), 0 8px 30px rgba(0, 0, 0, 0.3);
            transform: translateY(-2px);
        }

        .operation-card h3 {
            font-size: 1.2rem;
            margin-bottom: 10px;
            color: var(--highlight-color);
        }

        .operation-card p {
            color: var(--secondary-text);
            margin-bottom: 20px;
            line-height: 1.5;
        }

        .btn {
            display: inline-block;
            padding: 12px 24px;
            border: none;
            border-radius: 8px;
            font-size: 14px;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.3s ease;
            text-decoration: none;
            width: 100%;
            text-align: center;
        }

        .btn-primary {
            background: linear-gradient(90deg, var(--gradient-mid), var(--highlight-color));
            color: white;
        }

        .btn-primary:hover {
            transform: translateY(-1px);
            box-shadow: 0 4px 15px rgba(93, 79, 255, 0.4);
        }

        .btn-secondary {
            background-color: var(--panel-bg);
            color: var(--primary-text);
            border: 1px solid var(--border-color);
        }

        .btn-secondary:hover {
            background-color: var(--secondary-bg);
            border-color: var(--accent-color);
        }

        .btn-danger {
            background-color: var(--error-color);
            color: white;
        }

        .btn-danger:hover {
            background-color: #e53e3e;
            transform: translateY(-1px);
        }

        .btn:disabled {
            opacity: 0.6;
            cursor: not-allowed;
            transform: none !important;
        }

        .results-section {
            background-color: var(--secondary-bg);
            border-radius: 12px;
            padding: 30px;
            box-shadow: 0 8px 30px rgba(0, 0, 0, 0.2);
            border: 1px solid var(--border-color);
        }

        .result-item {
            margin-bottom: 20px;
            padding: 20px;
            background-color: var(--panel-bg);
            border-radius: 8px;
            border: 1px solid var(--border-color);
        }

        .result-item h4 {
            margin-bottom: 10px;
            color: var(--highlight-color);
            font-size: 1.1rem;
        }

        .result-success {
            border-left: 4px solid var(--success-color);
        }

        .result-error {
            border-left: 4px solid var(--error-color);
        }

        .result-content {
            background-color: rgba(0, 0, 0, 0.2);
            padding: 15px;
            border-radius: 6px;
            font-family: 'Monaco', 'Menlo', 'Ubuntu Mono', monospace;
            font-size: 13px;
            line-height: 1.5;
            white-space: pre-wrap;
            word-wrap: break-word;
            overflow-x: auto;
            color: var(--secondary-text);
            border: 1px solid var(--border-color);
        }

        .loading {
            display: none;
            color: var(--secondary-text);
            font-size: 14px;
            margin-top: 10px;
        }

        .loading.active {
            display: block;
        }

        .status-indicator {
            display: inline-block;
            width: 8px;
            height: 8px;
            border-radius: 50%;
            margin-right: 8px;
        }

        .status-success {
            background-color: var(--success-color);
        }

        .status-error {
            background-color: var(--error-color);
        }

        .index-info {
            background-color: var(--panel-bg);
            padding: 15px 20px;
            border-radius: 8px;
            margin-bottom: 30px;
            border: 1px solid var(--border-color);
            text-align: center;
        }

        .index-info strong {
            color: var(--highlight-color);
            font-size: 1.1rem;
        }

        @keyframes pulse {
            0% { opacity: 0.6; }
            50% { opacity: 1; }
            100% { opacity: 0.6; }
        }

        .loading::after {
            content: '...';
            animation: pulse 1.5s infinite;
        }

        .clear-results {
            background-color: var(--warning-color);
            color: white;
            border: none;
            padding: 8px 16px;
            border-radius: 6px;
            font-size: 12px;
            cursor: pointer;
            margin-bottom: 20px;
        }

        .clear-results:hover {
            background-color: #ff9800;
        }

        @media (max-width: 768px) {
            .operations-grid {
                grid-template-columns: 1fr;
            }

            h1 {
                font-size: 2rem;
            }

            .container {
                padding: 20px 15px;
            }
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

        <h1>PHP Client: Index Operations Example</h1>

        <div class="index-info">
            <p>Working with index: <strong><?php echo htmlspecialchars($indexName); ?></strong></p>
            <p>API Endpoint: <strong><?php echo htmlspecialchars($apiEndpoint); ?></strong></p>
        </div>

        <div class="operations-grid">
            <div class="operation-card">
                <h3>1. Create Index</h3>
                <p>Create a new search index with predefined schema including text fields, facets, and datetime fields.</p>
                <button class="btn btn-primary" onclick="performOperation('create_index')">Create Index</button>
                <div class="loading" id="loading-create_index">Creating index</div>
            </div>

            <div class="operation-card">
                <h3>2. Add Documents</h3>
                <p>Add sample documents to the index including articles about Searchcraft with various metadata.</p>
                <button class="btn btn-primary" onclick="performOperation('add_documents')">Add Sample Documents</button>
                <div class="loading" id="loading-add_documents">Adding documents</div>
            </div>

            <div class="operation-card">
                <h3>3. Get Index Details</h3>
                <p>Retrieve detailed information about the index including schema, settings, and document count.</p>
                <button class="btn btn-secondary" onclick="performOperation('get_index')">Get Index Info</button>
                <div class="loading" id="loading-get_index">Fetching index details</div>
            </div>

            <div class="operation-card">
                <h3>4. List All Indexes</h3>
                <p>Display all available indexes in the Searchcraft instance with their basic information.</p>
                <button class="btn btn-secondary" onclick="performOperation('list_indexes')">List Indexes</button>
                <div class="loading" id="loading-list_indexes">Loading indexes</div>
            </div>

            <div class="operation-card">
                <h3>5. Patch Index</h3>
                <p>Update index configuration without losing data. Modify weight multipliers and other patchable settings.</p>
                <button class="btn btn-secondary" onclick="performOperation('patch_index')">Patch Index Settings</button>
                <div class="loading" id="loading-patch_index">Updating index</div>
            </div>

            <div class="operation-card">
                <h3>6. Delete Index</h3>
                <p>Permanently delete the index and all its documents. This action cannot be undone.</p>
                <button class="btn btn-danger" onclick="performOperation('delete_index')">Delete Index</button>
                <div class="loading" id="loading-delete_index">Deleting index</div>
            </div>
        </div>

        <div class="results-section">
            <h2>Operation Results</h2>
            <button class="clear-results" onclick="clearResults()">Clear Results</button>
            <div id="results-container">
                <p style="color: var(--secondary-text); text-align: center; margin: 40px 0;">
                    Click any operation above to see the results here.
                </p>
            </div>
        </div>
    </div>

    <script>
        function performOperation(operation) {
            // Show loading indicator
            const loadingElement = document.getElementById('loading-' + operation);
            loadingElement.classList.add('active');

            // Disable the button
            const button = event.target;
            button.disabled = true;

            // Create form data
            const formData = new FormData();
            formData.append('operation', operation);

            fetch(window.location.href, {
                method: 'POST',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                // Hide loading indicator
                loadingElement.classList.remove('active');
                button.disabled = false;

                // Display result
                displayResult(data);
            })
            .catch(error => {
                // Hide loading indicator
                loadingElement.classList.remove('active');
                button.disabled = false;

                // Display error
                displayResult({
                    success: false,
                    operation: operation,
                    error: 'Request failed: ' + error.message
                });
            });
        }

        function displayResult(data) {
            const resultsContainer = document.getElementById('results-container');

            // Create result item
            const resultItem = document.createElement('div');
            resultItem.className = 'result-item ' + (data.success ? 'result-success' : 'result-error');

            const timestamp = new Date().toLocaleTimeString();
            const statusIndicator = '<span class="status-indicator ' + (data.success ? 'status-success' : 'status-error') + '"></span>';

            let resultContent = '';
            if (data.success) {
                resultContent = JSON.stringify(data.response, null, 2);
            } else {
                resultContent = 'Error: ' + data.error;
                if (data.errorData) {
                    resultContent += '\n\nError Details:\n' + JSON.stringify(data.errorData, null, 2);
                }
            }

            resultItem.innerHTML =
                '<h4>' +
                    statusIndicator +
                    getOperationTitle(data.operation) +
                    ' <span style="font-size: 0.9rem; color: var(--secondary-text); font-weight: normal;">' +
                        'at ' + timestamp +
                    '</span>' +
                '</h4>' +
                '<div class="result-content">' + resultContent + '</div>';

            // Add to top of results container
            if (resultsContainer.firstChild && resultsContainer.firstChild.tagName) {
                resultsContainer.insertBefore(resultItem, resultsContainer.firstChild);
            } else {
                resultsContainer.innerHTML = '';
                resultsContainer.appendChild(resultItem);
            }

            // Scroll to the new result
            resultItem.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
        }

        function getOperationTitle(operation) {
            const titles = {
                'create_index': 'Create Index',
                'add_documents': 'Add Documents',
                'get_index': 'Get Index Details',
                'list_indexes': 'List Indexes',
                'patch_index': 'Patch Index',
                'delete_index': 'Delete Index'
            };
            return titles[operation] || operation;
        }

        function clearResults() {
            const resultsContainer = document.getElementById('results-container');
            resultsContainer.innerHTML =
                '<p style="color: var(--secondary-text); text-align: center; margin: 40px 0;">' +
                    'Results cleared. Click any operation above to see new results here.' +
                '</p>';
        }

        // Add some visual feedback for button interactions
        document.addEventListener('DOMContentLoaded', function() {
            const buttons = document.querySelectorAll('.btn');
            buttons.forEach(function(button) {
                button.addEventListener('mousedown', function() {
                    this.style.transform = 'translateY(1px)';
                });

                button.addEventListener('mouseup', function() {
                    this.style.transform = '';
                });

                button.addEventListener('mouseleave', function() {
                    this.style.transform = '';
                });
            });
        });

        // Add keyboard shortcuts for operations
        document.addEventListener('keydown', function(e) {
            if (e.ctrlKey || e.metaKey) {
                switch(e.key) {
                    case '1':
                        e.preventDefault();
                        performOperation('create_index');
                        break;
                    case '2':
                        e.preventDefault();
                        performOperation('add_documents');
                        break;
                    case '3':
                        e.preventDefault();
                        performOperation('get_index');
                        break;
                    case '4':
                        e.preventDefault();
                        performOperation('list_indexes');
                        break;
                    case '5':
                        e.preventDefault();
                        performOperation('patch_index');
                        break;
                    case '6':
                        e.preventDefault();
                        performOperation('delete_index');
                        break;
                    case 'k':
                        e.preventDefault();
                        clearResults();
                        break;
                }
            }
        });

        // Show keyboard shortcuts help on page load
        console.log(
            'Searchcraft Index Operations Demo\n' +
            '=====================================\n\n' +
            'Keyboard Shortcuts:\n' +
            '• Ctrl/Cmd + 1: Create Index\n' +
            '• Ctrl/Cmd + 2: Add Documents\n' +
            '• Ctrl/Cmd + 3: Get Index Details\n' +
            '• Ctrl/Cmd + 4: List Indexes\n' +
            '• Ctrl/Cmd + 5: Patch Index\n' +
            '• Ctrl/Cmd + 6: Delete Index\n' +
            '• Ctrl/Cmd + K: Clear Results\n\n' +
            'Current Index: ' + <?php echo json_encode($indexName); ?> + '\n' +
            'API Endpoint: ' + <?php echo json_encode($apiEndpoint); ?>
        );
    </script>
</body>
</html>
