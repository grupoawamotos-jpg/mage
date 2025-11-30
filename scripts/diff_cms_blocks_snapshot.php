#!/usr/bin/env php
<?php
/**
 * Compara as duas últimas snapshots dos blocos CMS críticos.
 * Uso:
 *   php scripts/diff_cms_blocks_snapshot.php
 * Saída:
 *   Lista de blocos alterados (length ou sha256) e resumo.
 */

declare(strict_types=1);

$dir = __DIR__ . '/../relatorios';
if (!is_dir($dir)) {
    fwrite(STDERR, "Diretório de relatórios não existe: $dir\n");
    exit(1);
}

$files = glob($dir . '/cms_blocks_snapshot_*.json');
rsort($files);
if (count($files) < 2) {
    fwrite(STDERR, "Menos de duas snapshots disponíveis para diff.\n");
    exit(0);
}

$latest = $files[0];
$previous = $files[1];

$latestData = json_decode(file_get_contents($latest), true);
$prevData = json_decode(file_get_contents($previous), true);

if (!is_array($latestData) || !isset($latestData['blocks'])) {
    fwrite(STDERR, "Snapshot mais recente inválida.\n");
    exit(2);
}
if (!is_array($prevData) || !isset($prevData['blocks'])) {
    fwrite(STDERR, "Snapshot anterior inválida.\n");
    exit(2);
}

$changed = [];
foreach ($latestData['blocks'] as $id => $meta) {
    $prevMeta = $prevData['blocks'][$id] ?? null;
    if (!$prevMeta) {
        $changed[$id] = 'ADICIONADO';
        continue;
    }
    if (($meta['sha256'] ?? '') !== ($prevMeta['sha256'] ?? '')) {
        $changed[$id] = 'CONTEUDO_MODIFICADO';
        continue;
    }
    if (($meta['length'] ?? 0) !== ($prevMeta['length'] ?? 0)) {
        $changed[$id] = 'TAMANHO_ALTERADO';
        continue;
    }
}

echo "Comparando snapshots:\n  Atual:   $latest\n  Anterior: $previous\n\n";
if (!$changed) {
    echo "Nenhuma alteração detectada nos blocos monitorados.\n";
    exit(0);
}
foreach ($changed as $id => $status) {
    echo sprintf("- %s: %s\n", $id, $status);
}
exit(0);
