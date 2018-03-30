<?php

/**
 * Minimal class autoloader
 *
 * @param string $class Full qualified name of the class
 */
function miniAutoloader($class)
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

$text = isset($_POST['text']) ? $_POST['text'] : null;

$deepLy = new ChrisKonnertz\DeepLy\DeepLy();

?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>DeepLy Demo - Split Text</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/framy/latest/css/framy.min.css">
    <style>
        body { padding: 20px }
        h1 { margin-bottom: 40px }
        h4 { margin-top: 40px }
        textarea { resize: vertical; }
        blockquote { margin-left: 0; margin-right: 0; }
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
        <a class="button " href="demo_split.php">Split</a>
        <a class="button border" href="demo_ping.php">Ping</a>
    </div>

    <div class="content">
        <form method="POST">

            <div class="form-element">
                <label for="text">Text:</label>
                <textarea id="text" class="form-field" name="text" rows="4"><?php
                    echo $text !== null ? $text : 'Hello world! That\'s one small step for man, one giant leap for mankind.'
                ?></textarea>
            </div>

            <div id="ping-result"></div>

            <input type="submit" value="Split Text" class="button">
        </form>

        <div class="block result">
            <?php

            if ($text !== null) {
                try {
                    $result = $deepLy->splitText($text);

                    $wrapped = '<table class="table border stripe"><tr><td>'.
                        implode('</td></tr><tr><td>', $result).
                        "</td></tr></table>";

                    echo '<div class="success">Sentences: <blockquote><b>' . $wrapped . '</b></blockquote></div>';
                } catch (\Exception $exception) {
                    echo '<div class="error">'.$exception->getMessage().'</div>';
                }
            }

            ?>
        </div>
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
