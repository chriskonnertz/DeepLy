<?php

    /**
     * Minimal class autoloader
     *
     * @param string $class
     */
    function miniAutoloader($class)
    {
        require __DIR__ . '/../src/' . $class . '.php';
    }

    spl_autoload_register('miniAutoloader');

    $text = isset($_POST['text']) ? $_POST['text'] : null;
    $to = isset($_POST['to']) ? $_POST['to'] : null;
    $from = isset($_POST['from']) ? $_POST['from'] : null;

?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>DeepLy Demo</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/framy/latest/css/framy.min.css">
    <style>
        body { padding: 20px }
        h1 { margin-bottom: 40px }
        h4 { margin-top: 40px }
        form { margin-bottom: 20px }
        label { margin-top: 10px }
        div.success { border: 1px solid #4ce276; padding: 10px; border-top-width: 10px }
        div.error { border: 1px solid #f36362; padding: 10px; border-top-width: 10px }
        .info { margin-top: 20px }
    </style>
</head>
<body>
    <h1>DeepLy Demo</h1>

    <form method="POST">

        <div class="form-element">
            <label for="text">Text:</label>
            <input id="text" class="form-field" name="text" type="text" value="<?php echo $text !== null ? $text : 'Hello world!' ?>">

            <label for="to">To:</label>
            <input id="to" class="form-field" name="to" type="text" value="<?php echo $to !== null ? $to : 'DE' ?>">

            <label for="from">From:</label>
            <input id="from" class="form-field" name="from" type="text" value="<?php echo $from !== null ? $from : 'EN' ?>">
        </div>

        <input type="submit" value="Translate" class="button">
    </form>

    <div class="block result">
        <?php

            $deepLy = new ChrisKonnertz\DeepLy\DeepLy();

            if ($text !== null and $to !== null) {
                try {
                    $result = $deepLy->translate($text, $to, $from);

                    echo '<div class="success">Result: <code><b>' . $result . '</b></code></div>';
                } catch (\Exception $exception) {
                    echo '<div class="error">'.$exception->getMessage().'</div>';
                }
            }

        ?>
    </div>

    <div class="block info">
        This is not an official package.
        It will be 100% open source and non-commercial.
        DeepL is a product from DeepL GmbH. More info: <a href="https://www.deepl.com/publisher.html">www.deepl.com/publisher.html</a>
    </div>
</body>
</html>