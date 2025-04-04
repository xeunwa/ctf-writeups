<?php

// Enable internal error handling
libxml_use_internal_errors(true);

// Enable external entity loading (XXE vulnerability)
libxml_set_external_entity_loader(function ($public, $system, $context) {
    return fopen($system, 'r'); // Allows reading external files
});

if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['submitdata'])) {
    $xml = $_POST['submitdata'];

    $dom = new DOMDocument();

    try {
        if (!$dom->loadXML($xml, LIBXML_NOENT | LIBXML_DTDLOAD)) {
            throw new Exception("Invalid input.");
        }

        $xpath = new DOMXPath($dom);
        $nameNode = $xpath->query("//name")->item(0);
        $emailNode = $xpath->query("//email")->item(0);

        if ($nameNode && $emailNode) {
            $name = htmlspecialchars($nameNode->nodeValue);
            $email = htmlspecialchars($emailNode->nodeValue);
            echo "<h3>Thank you for actually doing your work, $name. You're safe for now...</h3>";
        } else {
            throw new Exception("Invalid format.");
        }

    } catch (Exception $e) {
        echo "<h3>Error:</h3>";
        echo "<pre>" . htmlspecialchars($e->getMessage()) . "</pre>";
    }

    libxml_clear_errors();
} else {
    echo "Please enter valid XML data.";
}

?>