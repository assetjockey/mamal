<?php defined('ALTUMCODE') || die() ?>

<div class="altum-animate altum-animate-fill-none altum-animate-fade-in">
    <div class="d-flex justify-content-between mb-2">
        <div>
            <small class="text-muted font-weight-bold text-uppercase"><?= l('dashboard.referrers.referrer_host') ?></small>
        </div>

        <div class="d-flex justify-content-end">
            <?php if($data->options['bounce_rate']): ?>
                <div class="col">
                    <small class="text-muted font-weight-bold text-uppercase"><?= l('analytics.' . $data->by) ?></small>
                </div>

                <div class="col p-0 text-right">
                    <small class="text-muted font-weight-bold text-uppercase"><?= l('analytics.bounce_rate') ?></small>
                </div>
            <?php else: ?>
                <div class="col p-0">
                    <small class="text-muted font-weight-bold text-uppercase"><?= l('analytics.' . $data->by) ?></small>
                </div>
            <?php endif ?>
        </div>
    </div>

    <?php if(!$data->total_sum): ?>
        <div class="mb-3">
            <div class="d-flex justify-content-between mb-1">
                <div>
                    <span class="text-muted"><?= l('dashboard.basic.no_data') ?></span>
                </div>

                <div class="d-flex justify-content-end">
                    <div class="col">-</div>

                    <div class="col p-0 text-right row-fade-show-icon-percentage"><small class="text-muted">-</small></div>
                </div>
            </div>
        </div>
    <?php else: ?>

        <?php foreach($data->rows as $row): ?>
            <?php $percentage = round($row->total / $data->total_sum * 100, 2) ?>
            <?php $bounce_rate = !isset($row->bounces) || is_null($row->bounces) ? null : round($row->bounces / $row->total * 100, 1) ?>

            <div class="row-fade-show-icon position-relative">
                <div class="progress bg-gray-100 h-100 w-100 position-absolute">
                    <div class="progress-bar" role="progressbar" style="width: <?= $percentage ?>%;" aria-valuenow="<?= $percentage ?>" aria-valuemin="0" aria-valuemax="100"></div>
                </div>

                <div class="d-flex justify-content-between mb-1 row-fade-show-icon-content">
                    <div class="d-flex align-items-center" style="min-width:0;">
                        <?php if($row->referrer_host): ?>
                            <img referrerpolicy="no-referrer" src="<?= get_favicon_url_from_domain($row->referrer_host) ?>" class="img-fluid icon-favicon mr-2 flex-shrink-0" loading="lazy" />
                            <a href="#" data-target="#referrer_paths_modal" data-toggle="modal" data-referrer-host="<?= $row->referrer_host ?>" title="<?= $row->referrer_host ?>" class="text-truncate text-body" style="min-width:0;">
                                <?php
                                switch($row->referrer_host) {
                                    /* Search engines */
                                    case 'bing.com': echo 'Bing'; break;
                                    case 'baidu.com': echo 'Baidu'; break;
                                    case 'google.com': echo 'Google'; break;
                                    case 'yahoo.com': echo 'Yahoo'; break;
                                    case 'yandex.com': echo 'Yandex'; break;
                                    case 'duckduckgo.com': echo 'DuckDuckGo'; break;
                                    case 'ecosia.org': echo 'Ecosia'; break;
                                    case 'startpage.com': echo 'Startpage'; break;
                                    case 'aol.com': echo 'AOL'; break;
                                    case 'brave.com': echo 'Brave'; break;

                                    /* Social media */
                                    case 'threads.com': echo 'Threads'; break;
                                    case 'facebook.com': echo 'Facebook'; break;
                                    case 'instagram.com': echo 'Instagram'; break;
                                    case 'pinterest.com': echo 'Pinterest'; break;
                                    case 'x.com': case 'twitter.com': echo 'X'; break;
                                    case 'youtube.com': echo 'YouTube'; break;
                                    case 'tiktok.com': echo 'TikTok'; break;
                                    case 'reddit.com': echo 'Reddit'; break;
                                    case 'linkedin.com': echo 'LinkedIn'; break;
                                    case 'snapchat.com': echo 'Snapchat'; break;
                                    case 'telegram.org': echo 'Telegram'; break;

                                    /* AI */
                                    case 'openai.com': echo 'ChatGPT'; break;
                                    case 'claude.ai': echo 'Claude'; break;
                                    case 'perplexity.ai': echo 'Perplexity'; break;
                                    case 'copilot.microsoft.com': echo 'Microsoft Copilot'; break;

                                    /* Android Gmail App */
                                    case 'com.google.android.gm': echo 'Android Gmail App'; break;
                                    default: echo $row->referrer_host; break;
                                }
                                ?>
                            </a>
                            <a href="<?= 'https://' . $row->referrer_host ?>" target="_blank" rel="nofollow noopener" class="text-muted ml-1 flex-shrink-0"><i class="fas fa-fw fa-xs fa-external-link-alt"></i></a>
                            <a href="#" data-add-filter data-filter-by="referrer_host" data-filter-rule="is" data-filter-value="<?= e($row->referrer_host) ?>" class="text-muted ml-1 flex-shrink-0" data-toggle="tooltip" title="<?= l('analytics.filters.create') ?>" data-tooltip-hide-on-click><i class="fas fa-fw fa-xs fa-filter"></i></a>
                        <?php else: ?>
                            <span title="<?= l('dashboard.referrers.null') ?>" class=""><?= l('dashboard.referrers.null') ?></span>
                        <?php endif ?>
                    </div>

                    <div class="d-flex justify-content-end">
                        <div class="col">
                            <span>
                                <?= nr($row->total) ?>
                            </span>
                        </div>

                        <div class="col p-0 text-right row-fade-show-icon-percentage"><small class="text-muted"><?= $percentage ?>%</small></div>

                        <?php if($data->options['bounce_rate']): ?>
                            <div class="col p-0 text-right" style="min-width:70px;"><small class="text-muted"><?= !is_null($bounce_rate) ? $bounce_rate . '%' : l('global.na') ?></small></div>
                        <?php endif ?>
                    </div>
                </div>
            </div>

        <?php endforeach ?>

        <?php if($data->total_rows > count($data->rows)): ?>
            <a href="<?= url($this->base_url_path . 'referrers') ?>" class="text-body font-size-small mt-2"><?= sprintf(l('global.view_x_more'), nr($data->total_rows - count($data->rows))) ?></a>
        <?php endif ?>

    <?php endif ?>
</div>
