<?php defined('ALTUMCODE') || die() ?>
<?= '<?xml version="1.0" encoding="UTF-8"?>' ?>
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">
    <?php foreach ($data->sitemap_urls as $sitemap_url) : ?>
        <url>
            <loc><?= $sitemap_url['url'] ?></loc>
            <?php /* Last modification date */ ?>
            <?php if($sitemap_url['lastmod']): ?>
                <lastmod><?= $sitemap_url['lastmod'] ?></lastmod>
            <?php endif ?>
        </url>
    <?php endforeach ?>
</urlset>
