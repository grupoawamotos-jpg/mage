#!/usr/bin/env php
<?php
declare(strict_types=1);

use Imagick;
use ImagickPixel;
use Magento\Framework\App\Bootstrap;
use Magento\Framework\App\Filesystem\DirectoryList;

require __DIR__ . '/../app/bootstrap.php';

$bootstrap = Bootstrap::create(BP, $_SERVER);
$objectManager = $bootstrap->getObjectManager();

/** @var DirectoryList $directoryList */
$directoryList = $objectManager->get(DirectoryList::class);
$mediaDir = rtrim($directoryList->getPath(DirectoryList::MEDIA), '/');

$images = [
    ['path' => 'slidebanner/real/slide1.svg', 'target' => 'slidebanner/real/slide1.webp', 'width' => 1600, 'height' => 520],
    ['path' => 'slidebanner/real/slide2.svg', 'target' => 'slidebanner/real/slide2.webp', 'width' => 1600, 'height' => 520],
    ['path' => 'slidebanner/real/slide3.svg', 'target' => 'slidebanner/real/slide3.webp', 'width' => 1600, 'height' => 520],
    ['path' => 'wysiwyg/home/side-honda.svg', 'target' => 'wysiwyg/home/side-honda.webp', 'width' => 320, 'height' => 320],
    ['path' => 'wysiwyg/home/side-yamaha.svg', 'target' => 'wysiwyg/home/side-yamaha.webp', 'width' => 320, 'height' => 320],
    ['path' => 'wysiwyg/banners/promo-banner.svg', 'target' => 'wysiwyg/banners/promo-banner.webp', 'width' => 400, 'height' => 400],
];

$errors = 0;
foreach ($images as $config) {
    $source = $mediaDir . '/' . $config['path'];
    $target = $mediaDir . '/' . $config['target'];

    if (!is_file($source)) {
        fwrite(STDERR, "[!] Arquivo de origem inexistente: {$config['path']}\n");
        $errors++;
        continue;
    }

    if (!is_dir(dirname($target))) {
        if (!@mkdir(dirname($target), 0755, true) && !is_dir(dirname($target))) {
            fwrite(STDERR, "[!] Não foi possível criar o diretório destino: {$config['target']}\n");
            $errors++;
            continue;
        }
    }

    try {
        $imagick = new Imagick();
        $imagick->setBackgroundColor(new ImagickPixel('transparent'));
        $imagick->readImage($source);

        if (!empty($config['width']) && !empty($config['height'])) {
            $imagick->setImageAlphaChannel(Imagick::ALPHACHANNEL_ACTIVATE);
            $imagick->resizeImage((int)$config['width'], (int)$config['height'], Imagick::FILTER_LANCZOS, 1, true);
        }

        $imagick->setImageFormat('webp');
        $imagick->setOption('webp:method', '6');
        $imagick->setOption('webp:alpha-quality', '90');
        $imagick->setImageCompressionQuality(82);
        $imagick->writeImage($target);
        $imagick->clear();
        $imagick->destroy();

        echo sprintf("[✓] %s -> %s\n", $config['path'], $config['target']);
    } catch (Throwable $exception) {
        fwrite(STDERR, sprintf("[✗] Falha ao converter %s: %s\n", $config['path'], $exception->getMessage()));
        $errors++;
    }
}

if ($errors > 0) {
    exit(1);
}

echo "Conversão concluída com sucesso.\n";
