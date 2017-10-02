<?php

    /**
     * Minimal class autoloader
     *
     * @param string $class Full qualified name of the class
     */
    function miniAutoloader($class)
    {
        require __DIR__ . '/../src/' . $class . '.php';
    }

    // If the Composer autoloader exists, use it. If not, use our own as fallback.
    $composerAutoloader = __DIR__.'/../vendor/autoload.php';
    if (is_readable($composerAutoloader)) {
        require $composerAutoloader;
    } else {
        spl_autoload_register('miniAutoloader');
    }

    $text = isset($_POST['text']) ? $_POST['text'] : null;

    $deepLy = new ChrisKonnertz\DeepLy\DeepLy();

?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>DeepLy Demo - Language Detection</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/framy/latest/css/framy.min.css">
    <style>
        body { padding: 20px }
        h1 { margin-bottom: 40px }
        h4 { margin-top: 40px }
        form { margin-bottom: 20px }
        textarea { resize: vertical; }
        blockquote { margin-left: 0; margin-right: 0; }
        div.success { border: 1px solid #4ce276; margin: 20px 0; padding: 10px; border-top-width: 10px }
        div.error { border: 1px solid #f36362; margin: 20px 0; padding: 10px; border-top-width: 10px }
    </style>
</head>
<body>
    <h1>DeepLy Demo</h1>

    <form method="POST">

        <div class="form-element">
            <label for="text">Text:</label>
            <textarea id="text" class="form-field" name="text" rows="4"><?php echo $text !== null ? $text : 'Hello world!' ?></textarea>
        </div>

        <div id="ping-result"></div>

        <input type="submit" value="Detect Language" class="button">
    </form>

    <div class="block result">
        <?php

            if ($text !== null) {
                try {
                    $result = $deepLy->detectLanguage($text);

                    echo '<div class="success">Result: <blockquote><b>' . $result . '</b></blockquote></div>';
                } catch (\Exception $exception) {
                    echo '<div class="error">'.$exception->getMessage().'</div>';
                }
            }

        ?>
    </div>

    <div class="block info">
        <small>
            This is not an official package.
            It is 100% open source and non-commercial.
            DeepL is a product from DeepL GmbH. More info:
            <a href="https://www.deepl.com/publisher.html">www.deepl.com/publisher.html</a>
        </small>
    </div>

    <script>
        (
            // Use DeepLy's ping method to check if the API server is reachable
            function()
            {
                var request = new XMLHttpRequest();

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
