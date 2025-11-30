<?php
declare(strict_types=1);

/**
 * Varre HTML (homepage ou arquivo) e valida atributos data-mage-init.
 */

function printUsage(): void
{
    fwrite(STDERR, "\nUso:\n");
    fwrite(STDERR, "  php scripts/debug_home_json.php\n");
    fwrite(STDERR, "  php scripts/debug_home_json.php --url \"https://site\"\n");
    fwrite(STDERR, "  php scripts/debug_home_json.php --file /caminho/arquivo.html\n\n");
}

function getArg(array $argv, string $name): ?string
{
    foreach ($argv as $i => $arg) {
        if (strpos($arg, "--{$name}=") === 0) {
            return substr($arg, strlen("--{$name}="));
        }
        if ($arg === "--{$name}" && isset($argv[$i + 1])) {
            return $argv[$i + 1];
        }
    }
    return null;
}

function fetchContentFromUrl(string $url): string
{
    $context = stream_context_create([
        'http' => [
            'method' => 'GET',
            'header' => "User-Agent: DebugMageInit/1.0\r\nAccept: text/html\r\n",
            'timeout' => 20,
            'ignore_errors' => true,
        ],
        'ssl' => [
            'verify_peer' => false,
            'verify_peer_name' => false,
        ],
    ]);

    $html = @file_get_contents($url, false, $context);
    if ($html === false) {
        throw new RuntimeException("Falha ao buscar URL: {$url}");
    }
    return $html;
}

function readFileContent(string $path): string
{
    if (!is_file($path)) {
        throw new RuntimeException("Arquivo não encontrado: {$path}");
    }
    $html = @file_get_contents($path);
    if ($html === false) {
        throw new RuntimeException("Falha ao ler arquivo: {$path}");
    }
    return $html;
}

function extractDataMageInitAttributes(string $html): array
{
    $results = [];
    $pattern = '/<([a-zA-Z0-9:-]+)([^>]*?)\sdata-mage-init\s*=\s*("([^"]*)"|\'([^\']*)\')([^>]*)>/s';

    if (preg_match_all($pattern, $html, $matches, PREG_SET_ORDER)) {
        foreach ($matches as $m) {
            $fullTag = $m[0];
            $json = $m[4] !== '' ? $m[4] : $m[5];
            $snippet = trim(substr($fullTag, 0, 300));
            $results[] = [$snippet, $json];
        }
    }
    return $results;
}

function isValidJson(string $json): bool
{
    if ($json === '') {
        return false;
    }
    json_decode($json, true);
    return json_last_error() === JSON_ERROR_NONE;
}

function summarizeErrors(array $errors): void
{
    if (empty($errors)) {
        echo "\n✅ Nenhum JSON inválido encontrado em data-mage-init.\n";
        return;
    }

    echo "\n❌ Encontrados " . count($errors) . " atributo(s) data-mage-init com JSON inválido.\n";
    $max = min(5, count($errors));
    echo "\nExemplos (até {$max}):\n";
    for ($i = 0; $i < $max; $i++) {
        [$snippet, $json, $errorMsg] = $errors[$i];
        echo "\n---\nElemento: \n" . $snippet . "\n";
        echo "JSON bruto: \n" . $json . "\n";
        echo "Erro: {$errorMsg}\n";
    }
}

function main(array $argv): int
{
    $url = getArg($argv, 'url');
    $file = getArg($argv, 'file');

    if ($url === null && $file === null && count($argv) > 1) {
        printUsage();
        return 2;
    }

    try {
        if ($file !== null) {
            $html = readFileContent($file);
            echo "Lendo HTML de arquivo: {$file}\n";
        } else {
            $baseUrl = getenv('BASE_URL');
            if (!$baseUrl) {
                $server = getenv('SERVER_NAME') ?: 'localhost';
                $scheme = getenv('SCHEME') ?: 'https';
                $baseUrl = $scheme . '://' . $server . '/';
            }
            $target = $url ?: rtrim($baseUrl, '/') . '/';
            echo "Buscando URL: {$target}\n";
            $html = fetchContentFromUrl($target);
        }
    } catch (Throwable $e) {
        fwrite(STDERR, "Erro ao obter conteúdo: " . $e->getMessage() . "\n");
        return 1;
    }

    $items = extractDataMageInitAttributes($html);
    echo "Encontrados " . count($items) . " elemento(s) com data-mage-init.\n";

    $errors = [];
    foreach ($items as [$snippet, $json]) {
        $valid = isValidJson($json);
        if (!$valid) {
            json_decode($json, true);
            $errorCode = json_last_error();
            $errorMsg = json_last_error_msg();
            $errors[] = [$snippet, $json, $errorMsg . " (code {$errorCode})"];
        }
    }

    summarizeErrors($errors);

    echo "\nResumo:\n";
    echo "  Total data-mage-init: " . count($items) . "\n";
    echo "  Inválidos: " . count($errors) . "\n";

    if (!empty($errors)) {
        echo "\nDicas:\n";
        echo "- Verifique templates que geram estes elementos (ex.: toolbar.phtml, sections.phtml).\n";
        echo "- Garanta que strings estejam entre aspas duplas e chaves/colchetes balanceados.\n";
        echo "- Em PHP, valide com json_decode() e caia para '{}' quando inválido.\n";
    }

    return empty($errors) ? 0 : 3;
}

exit(main($argv));
