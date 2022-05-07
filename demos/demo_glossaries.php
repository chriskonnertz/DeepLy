<?php

    /**
     * Minimal class autoloader
     *
     * @param string $class Full qualified name of the class
     */
    function miniAutoloader(string $class)
    {
        $class = str_replace('\\', '/', $class);
        require __DIR__ . '/../src/' . $class . '.php';
    }

    // If the Composer autoloader exists, use it. If not, use our own as fallback.
    $composerAutoloader = __DIR__.'/../vendor/autoload.php';
    if (is_readable($composerAutoloader)) {
        require $composerAutoloader;
    } else {
        spl_autoload_register('miniAutoloader');
    }

    $key = $_POST['key'] ?? null;
    $text = $_POST['text'] ?? null;

    $deepLy = new ChrisKonnertz\DeepLy\DeepLy($key ?? '');

?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>DeepLy Demo - Language Detection</title>
    <link rel="shortcut icon" href="https://www.google.com/s2/favicons?domain=deepl.com">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/framy/latest/css/framy.min.css">
    <style>
        body { padding: 20px }
        h1 { margin-bottom: 40px }
        h4 { margin-top: 40px }
        textarea { resize: vertical; }
        blockquote { margin-left: 0; margin-right: 0; }
        pre { white-space: pre; }
        footer { color: #aaa }
        div.success { border: 1px solid #4ce276; margin-top: 20px; padding: 10px; border-top-width: 10px }
        div.error { border: 1px solid #f36362; margin-top: 20px; padding: 10px; border-top-width: 10px }
        .button-group { margin-bottom: 20px }
        .content { margin-bottom: 20px; padding: 20px; box-shadow: 0 1px 3px 0 #c8c8c8; }
    </style>
</head>
<body>
    <h1>DeepLy Demo</h1>

    <div class="button-group">
        <a class="button border" href="demo_translate.php">Translate</a>
        <a class="button border" href="demo_detect.php">Detect</a>
        <a class="button " href="demo_glossaries.php">Glossaries</a>
        <a class="button border" href="demo_documents.php">Documents</a>
        <a class="button border" href="demo_ping.php">Ping</a>
    </div>

    <div class="content">
        <form method="POST">
            <div class="form-element">
                <label for="key">API Key:</label>
                <input type="text" id="key" class="form-field" name="key" value="<?php echo $key?? '' ?>" placeholder="Get your API key from DeepL.com">
            </div>

            <div id="ping-result"></div>

            <input type="submit" name="list" value="Get All Glossaries" class="button">
            <input type="submit" name="last" value="Get Last Glossary" class="button">
            <input type="submit" name="entries" value="Get Glossary Entries" class="button">
            <input type="submit" name="create" value="Create Test Glossary" class="button">
            <input type="submit" name="delete" value="Delete Last Test Glossary" class="button">

            <div class="block result">
                <?php

                if (isset($_POST['list'])) {
                    try {
                        $result = $deepLy->getGlossaries();

                        echo '<div class="success">Glossaries found: <pre>';
                        print_r($result);
                        echo '</pre></div>';
                    } catch (\Exception $exception) {
                        echo '<div class="error">'.$exception->getMessage().'</div>';
                        die();
                    }
                }

                if (isset($_POST['last'])) {
                    try {
                        $glossaries = $deepLy->getGlossaries();

                        if (count($glossaries) > 0) {
                            $glossary = $deepLy->getGlossary($glossaries[count($glossaries) - 1]->glossaryId);
                            echo '<div class="success">Glossary: <pre>';
                            print_r($glossary);
                            echo '</pre></div>';
                        } else {
                            echo '<div class="error">No test glossaries found!
                                        Click on the create button to create a test glossary, then try again!</div>';
                        }
                    } catch (\Exception $exception) {
                        echo '<div class="error">'.$exception->getMessage().'</div>';
                        die();
                    }
                }

                if (isset($_POST['entries'])) {
                    try {
                        $glossaries = $deepLy->getGlossaries();

                        if (count($glossaries) > 0) {
                            $id = $glossaries[count($glossaries) - 1]->glossaryId;
                            $entries = $deepLy->getGlossaryEntries($id);
                            echo '<div class="success">Glossary Entries of glossary ' . $id . ': <pre>';
                            print_r($entries);
                            echo '</pre></div>';
                        } else {
                            echo '<div class="error">No test glossaries found!
                                        Click on the create button to create a test glossary, then try again!</div>';;
                        }
                    } catch (\Exception $exception) {
                        echo '<div class="error">'.$exception->getMessage().'</div>';
                        die();
                    }
                }

                if (isset($_POST['create'])) {
                    try {
                        $result = $deepLy->createGlossary('DeepLy Test', 'de', 'en', ['Entry 1 DE' => 'Entry 1 EN', 'Entry 2 DE' => 'Entry 2 EN']);

                        echo '<div class="success">Glossary created: <pre>';
                        print_r($result);
                        echo '</pre></div>';
                    } catch (\Exception $exception) {
                        echo '<div class="error">'.$exception->getMessage().'</div>';
                        die();
                    }
                }

                if (isset($_POST['delete'])) {
                    try {
                        $glossaries = $deepLy->getGlossaries();
                        if (count($glossaries) > 0) {
                            $candidate = null;
                            foreach ($glossaries as $glossary) {
                                if ($glossary->name === 'DeepLy Test') {
                                    $candidate = $glossary;
                                }
                            }
                            if ($candidate) {
                                $deepLy->deleteGlossary($candidate->glossaryId);
                                echo '<div class="success">Glossary '.$candidate->glossaryId.' deleted!</div>';
                            } else {
                                echo '<div class="error">No test glossaries found!
                                        Click on the create button to create a test glossary, then try again!</div>';
                            }

                        } else {
                            echo '<div class="error">No glossaries available!</div>';
                        }
                    } catch (\Exception $exception) {
                        echo '<div class="error">'.$exception->getMessage().'</div>';
                        die();
                    }
                }

                ?>
            </div>
        </form>

    </div>

    <footer class="block">
        <small>
            Version <?php echo ChrisKonnertz\DeepLy\DeepLy::VERSION ?>.
            This is not an official package.
            It is 100% open source and non-commercial.
            DeepL is a product from DeepL GmbH. More info:
            <a href="https://www.deepl.com/publisher.html">www.deepl.com/publisher.html</a>
        </small>
    </footer>

    <script>
        (
            // Use DeepLy's ping method to check if the API server is reachable
            function()
            {
                const request = new XMLHttpRequest();

                request.addEventListener('readystatechange', function() {
                    if (request.readyState === XMLHttpRequest.DONE) {
                        if (request.status !== 200 || request.responseText !== '1') {
                            document.getElementById('ping-result').innerHTML =
                                '<div class="error"><b>WARNING:</b> API seems unreachable.</div>';
                        }
                    }
                });

                request.open('GET', 'demo_ping.php?simple=1', true);
                request.send();
            }
        )();
    </script>
</body>
</html>
