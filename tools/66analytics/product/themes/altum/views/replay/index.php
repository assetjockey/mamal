<?php defined('ALTUMCODE') || die() ?>

<div class="container">
    <?= \Altum\Alerts::output_alerts() ?>

    <?php if(settings()->main->breadcrumbs_is_enabled): ?>
        <nav aria-label="breadcrumb">
            <ol class="custom-breadcrumbs small">
                <li><a href="<?= url('replays') ?>"><?= l('replays.breadcrumb') ?></a> <i class="fas fa-fw fa-angle-right"></i></li>
                <li class="active" aria-current="page"><?= l('replay.breadcrumb') ?></li>
            </ol>
        </nav>
    <?php endif ?>

    <div class="d-flex justify-content-between mb-4">
        <h1 class="h4 m-0 text-truncate"><i class="fas fa-fw fa-xs fa-video mr-1"></i> <?= l('replay.header') ?></h1>

        <?php if(!$this->team): ?>
            <?= include_view(THEME_PATH . 'views/replays/replay_dropdown_button.php', ['id' => $data->replay->replay_id]) ?>
        <?php endif ?>
    </div>

    <?php
    /* Visitor */
    $icon = new \Jdenticon\Identicon([
        'value' => $data->visitor->visitor_uuid_binary,
        'size' => 80
    ]);
    $data->visitor->icon = $icon->getImageDataUri();
    ?>

    <div class="row justify-content-between m-n3">
        <div class="col-12 col-lg-4 p-3">
            <div class="card border-0">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div class="d-flex flex-column text-truncate">
                            <small class="text-muted text-uppercase font-weight-bold"><?= l('replays.replay.visitor') ?></small>
                            <span class="h4 font-weight-bolder text-truncate">
                                <img src="<?= ASSETS_FULL_URL . 'images/countries/' . ($data->visitor->country_code ? mb_strtolower($data->visitor->country_code) : 'unknown') . '.svg' ?>" class="img-fluid icon-favicon mr-1" />

                                <span class=""><?= $data->visitor->country_code ? get_country_from_country_code($data->visitor->country_code) :  l('global.unknown') ?></span>
                            </span>
                        </div>

                        <?php if(($data->visitor->custom_parameters = json_decode($data->visitor->custom_parameters, true)) && count($data->visitor->custom_parameters)): ?>
                            <?php ob_start() ?>
                            <div class='d-flex flex-column text-left'>
                                <div class='d-flex flex-column my-1'>
                                    <strong><?= sprintf(l('visitors.custom_parameters'), count($data->visitor->custom_parameters)) ?></strong>
                                </div>

                                <?php foreach($data->visitor->custom_parameters as $key => $value): ?>
                                    <div class='d-flex flex-column my-1'>
                                        <div><?= $key ?></div>
                                        <strong><?= $value ?></strong>
                                    </div>
                                <?php endforeach ?>
                            </div>

                            <?php $tooltip = ob_get_clean() ?>

                            <a href="<?= url('visitor/' . $data->visitor->visitor_id) ?>" class="mr-3" data-toggle="tooltip" data-html="true" title="<?= $tooltip ?>">
                                <span class="round-circle-md bg-gray-200 text-primary-700 p-3">
                                    <i class="fas fa-fw fa-lg fa-fingerprint"></i>
                                </span>
                            </a>
                        <?php else: ?>
                            <a href="<?= url('visitor/' . $data->visitor->visitor_id) ?>" class="mr-3">
                                <img src="<?= $data->visitor->icon ?>" class="visitor-avatar rounded-circle" alt="" />
                            </a>
                        <?php endif ?>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-12 col-lg-4 p-3">
            <div class="card border-0">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div class="d-flex flex-column">
                            <small class="text-muted text-uppercase font-weight-bold"><?= l('replays.replay.date') ?></small>
                            <span class="h4 font-weight-bolder" data-toggle="tooltip" title="<?= \Altum\Date::get($data->replay->datetime, 1) ?>"><?= \Altum\Date::get($data->replay->datetime, 2) ?></span>
                        </div>

                        <span class="round-circle-md bg-gray-200 text-primary-700 p-3">
                            <i class="fas fa-fw fa-lg fa-calendar"></i>
                        </span>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-12 col-lg-4 p-3">
            <div class="card border-0">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div class="d-flex flex-column">
                            <small class="text-muted text-uppercase font-weight-bold"><?= l('replay.duration') ?></small>
                            <span class="h4 font-weight-bolder"><?= \Altum\Date::seconds_to_his((new \DateTime($data->replay->last_datetime))->getTimestamp() - (new \DateTime($data->replay->datetime))->getTimestamp()) ?></span>
                        </div>

                        <span class="round-circle-md bg-gray-200 text-primary-700 p-3">
                            <i class="fas fa-fw fa-lg fa-stopwatch"></i>
                        </span>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-12 col-lg-4 p-3">
            <div class="card border-0">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div class="d-flex flex-column">
                            <small class="text-muted text-uppercase font-weight-bold"><?= l('replay.time_range') ?></small>
                            <span class="h4 font-weight-bolder"><?= \Altum\Date::get($data->replay->datetime, 3) ?> <i class="fas fa-fw fa-sm fa-arrow-right"></i> <?= \Altum\Date::get($data->replay->last_datetime, 3) ?></span>
                        </div>

                        <span class="round-circle-md bg-gray-200 text-primary-700 p-3">
                            <i class="fas fa-fw fa-lg fa-clock"></i>
                        </span>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-12 col-lg-4 p-3">
            <div class="card border-0">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div class="d-flex flex-column">
                            <small class="text-muted text-uppercase font-weight-bold"><?= l('replay.events') ?></small>
                            <span class="h4 font-weight-bolder"><?= nr($data->replay->events) ?></span>
                        </div>

                        <span class="round-circle-md bg-gray-200 text-primary-700 p-3">
                            <i class="fas fa-fw fa-lg fa-eye"></i>
                        </span>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-12 col-lg-4 p-3">
            <div class="card border-0">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div class="d-flex flex-column">
                            <small class="text-muted text-uppercase font-weight-bold"><?= l('replay.expiration_date') ?></small>
                            <span class="h4 font-weight-bolder"><?= \Altum\Date::get_time_until($data->replay->expiration_date) ?></span>
                        </div>

                        <span class="round-circle-md bg-gray-200 text-primary-700 p-3">
                            <i class="fas fa-fw fa-lg fa-hourglass-half"></i>
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="notification-container"></div>

    <div class="mt-5 clearfix d-flex justify-content-center" id="replay"></div>

    <div id="timeline" class="mt-5 d-flex flex-column" style="overflow-y: auto; max-height: 200px;"></div>
</div>


<?php ob_start() ?>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/rrweb-player@2.0.0-alpha.14/dist/style.css" />
<style>
    .rr-player {
        background-color: var(--white) !important;
        border-radius: var(--border-radius) !important;
        box-shadow: none !important;
    }
    .rr-controller {
        background-color: var(--white) !important;
    }
    .rr-progress {
        background: var(--gray-200) !important;
        border-top: solid 4px var(--white) !important;
        border-bottom: solid 4px var(--white) !important;
    }
    .rr-progress__handler {
        background: var(--primary) !important;
    }
    .rr-controller__btns .active {
        background-color: var(--primary) !important;
    }
    .rr-controller__btns .switch {
        display: none !important;
    }
    .rr-controller__btns button:last-child {
        display: none !important;
    }
    .replayer-wrapper {
        border-radius: var(--border-radius) !important;
    }
    .rr-player iframe {
        border-radius: var(--border-radius) !important;
    }

    .rr-player__frame {
        overflow: hidden;
    }
</style>
<?php \Altum\Event::add_content(ob_get_clean(), 'head') ?>

<?php ob_start() ?>
<script src="<?= ASSETS_FULL_URL . 'js/libraries/rrweb-player.min.js?v=' . PRODUCT_CODE ?>"></script>

<script>
    'use strict';

    /* Default loading state */
    let loading_html = document.querySelector('#loading').innerHTML;
    let notification_container = document.querySelector('.notification-container');
    document.querySelector('#replay').innerHTML = loading_html;

    /* Player vars */
    let player = null;
    let events_buffer = [];
    let chunk_size = 250;
    let timeline_map = [];

    $.ajax({
        type: 'GET',
        url: <?= json_encode(url('replay/read/' . $data->visitor->session_id)) ?>,
        success: (result) => {

            document.querySelector('#replay').innerHTML = '';

            /* Prepare the buffer */
            events_buffer = result.rows;

            /* Initiate replayer with the first chunk of the events */
            let initial_events = events_buffer.slice(0, chunk_size);

            player = new rrwebPlayer({
                target: document.querySelector('#replay'),
                data: {
                    events: initial_events,
                    autoPlay: false,
                }
            });

            /* event emitting */
            player.addEventListener('fullsnapshot-rebuilded', process_current_player_time)

            /* Remove the initial chunk from the buffer */
            events_buffer = events_buffer.slice(chunk_size);

            /* update sizing of player */
            setTimeout(update_player_styles, 500);

            /* Load remaining events gradually */
            load_events_in_chunks();

            /* Append HTML for the pageviews modal */
            $('#replay_events_result').html(result.replay_events_html);

            /* Timeline generation */
            process_timeline(result.pageviews);

        },
        error: event => {
            document.querySelector('#replay').innerHTML = '';
            display_notifications(<?= json_encode(l('replay.error_message')) ?>, 'error',  notification_container);
        },
        dataType: 'json'
    });


    let load_events_in_chunks = () => {
        if(!player) {
            return;
        }

        if(events_buffer.length === 0) {
            return;
        }

        /* Get next chunk of events */
        let next_chunk = events_buffer.slice(0, chunk_size);
        events_buffer = events_buffer.slice(chunk_size);

        /* Add events one by one to the player */
        for(let i = 0; i < next_chunk.length; i++) {
            player.addEvent(next_chunk[i]);
        }

        /* Schedule next chunk of events */
        setTimeout(load_events_in_chunks, 100);
    }

    let update_player_styles = () => {
        let rr_player = Array.from(document.getElementsByClassName('rr-player'));
        let player__frame = Array.from(document.getElementsByClassName('rr-player__frame'));
        player__frame[0].style.width = '100%';
        rr_player[0].style.width = '100%';
    }

    let process_timeline = events => {
        let first_timestamp = null;
        let timeline = document.querySelector('#timeline');

        let index = 0;
        let total_events = events.length;
        events.forEach(event => {
            if(!first_timestamp) {
                first_timestamp = event.timestamp;
            }

            /* Save timestamps */
            timeline_map[index] = event.timestamp;

            /* Player time for the play button */
            let event_player_time = event.timestamp - first_timestamp;

            /* Arrow down icon */
            let arrow_down_element = `<div class="text-center my-2"><i class="fas fa-fw fa-arrow-down fa-sm text-muted"></i></div>`;

            /* Prepare the event element */
            let event_element = `
            <div class="p-3 bg-gray-200 rounded-2x d-flex align-items-center justify-content-between replay-timeline-item" data-event-index="${index}">
                <div>
                    ${event.path} <a href="${event.data.href}" target="_blank" rel="nofollow noreferrer"><i class="fas fa-fw fa-xs fa-external-link-alt ml-1"></i></a>

                    <p class="mb-0 text-muted small">
                        <i class="fas fa-fw fa-xs fa-clock mr-1"></i> ${event.time}
                        <i class="fas fa-fw fa-xs fa-window-maximize ml-3 mr-1"></i> ${event.data.width}x${event.data.height}
                    </p>
                </div>

                <div>
                    <div onclick="player.goto(${event_player_time})" class="cursor-pointer">
                        <i class="fa fa-fw fa-2x fa-circle-play text-primary"></i>
                    </div>
                </div>
            </div>
            ${index++ == total_events - 1 ? '' : arrow_down_element}
            `;

            /* Append to HTML */
            timeline.innerHTML += event_element;
        })
    }

    let process_current_player_time = data => {
        let current_timestamp = data.timestamp;
        let found_index = null;
        let target_element = null;

        /* Find the event index that we must highlight */
        for(let i = 0; i < timeline_map.length; i++) {
            let timestamp = timeline_map[i];

            if(timestamp <= current_timestamp) {
                found_index = i;
            }
        }

        /* Highlight the proper event timeline item */
        document.querySelectorAll('div[data-event-index]').forEach(element => {
            let element_classes = element.classList;
            element_classes.remove('bg-primary-100');
            element_classes.remove('bg-gray-200');

            let element_index = element.getAttribute('data-event-index');

            if(element_index == found_index) {
                element.classList.add('bg-primary-100');
                target_element = element;
            } else {
                element.classList.add('bg-gray-200');
            }
        });

        /* Highlight the proper event timeline item */
        document.querySelectorAll('div[data-event-index]').forEach(element => {
            let element_classes = element.classList;
            element_classes.remove('bg-primary-100');
            element_classes.remove('bg-gray-200');

            let element_index = element.getAttribute('data-event-index');

            if(element_index == found_index) {
                element.classList.add('bg-primary-100');
                target_element = element;
            } else {
                element.classList.add('bg-gray-200');
            }
        });

        /* Scroll to element */
        if (target_element) {
            let timeline_container = document.querySelector('#timeline');

            if (timeline_container) {
                let container_rect = timeline_container.getBoundingClientRect();
                let target_rect = target_element.getBoundingClientRect();

                let offset =
                    target_rect.top
                    - container_rect.top
                    + timeline_container.scrollTop;

                timeline_container.scrollTo({
                    top: offset,
                    behavior: 'smooth'
                });
            }
        }
    }

    /* Responsive update player */
    window.addEventListener('resize', update_player_styles);
</script>
<?php \Altum\Event::add_content(ob_get_clean(), 'javascript') ?>

<?php \Altum\Event::add_content(fn() => include_view(THEME_PATH . 'views/partials/universal_delete_modal_form.php', [
    'name' => 'replay',
    'resource_id' => 'session_id',
    'has_dynamic_resource_name' => false,
    'path' => 'replays/delete'
]), 'modals'); ?>
