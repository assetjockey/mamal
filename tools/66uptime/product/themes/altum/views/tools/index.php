<?php defined('ALTUMCODE') || die() ?>

<div class="container">
    <?= \Altum\Alerts::output_alerts() ?>

    <div class="row mb-4">
        <div class="col-12 col-lg d-flex align-items-center mb-3 mb-lg-0 text-truncate">
            <h1 class="h4 m-0 text-truncate"><i class="fas fa-fw fa-xs fa-screwdriver-wrench mr-1"></i> <?= l('tools.header') ?></h1>

            <div class="ml-2">
                <span data-toggle="tooltip" title="<?= l('tools.subheader') ?>">
                    <i class="fas fa-fw fa-info-circle text-muted"></i>
                </span>
            </div>
        </div>
    </div>

    <form id="search" action="" method="get" role="form">
        <div class="form-group">
            <input type="search" name="search" class="form-control form-control-lg" value="" placeholder="<?= l('global.filters.search') ?>" aria-label="<?= l('global.filters.search') ?>" />
        </div>
    </form>

    <div id="tools_no_data" class="mt-5 d-none">
        <?= include_view(THEME_PATH . 'views/partials/no_data.php', [
            'filters_get' => $data->filters->get ?? [],
            'name' => 'tools',
            'has_secondary_text' => false,
            'has_wrapper' => true,
        ]); ?>
    </div>

    <div class="row">
        <?php foreach($data->tools as $key => $value): ?>
            <?php if(settings()->tools->available_tools->{$key}): ?>
                <?= include_view(THEME_PATH . 'views/tools/tool_widget_' . (settings()->tools->style ?? 'frankfurt') . '.php', [
                    'tool_id' => $key,
                    'tool_icon' => $value['icon'],
                    'tools_usage' => $data->tools_usage
                ]); ?>
            <?php endif ?>
        <?php endforeach ?>
    </div>
</div>

<?php ob_start() ?>
<script>
    'use strict';
    
/* Cache all tool elements once */
    const tool_elements_array = [...document.querySelectorAll('[data-tool-id]')];

    /* Build a single array that holds each element + lowercase name */
    const tools_array = tool_elements_array.map(element => ({
        id: element.getAttribute('data-tool-id'),
        name: element.getAttribute('data-tool-name').toLowerCase(),
        reference: element
    }));

    /* Attach keyup listener for filtering */
    document.querySelector('#search input[name="search"]').addEventListener('keyup', event => {
        const search_string = event.currentTarget.value.toLowerCase();
        let any_tool_visible = false;

        for (let tool of tools_array) {
            if(tool.name.includes(search_string)) {
                tool.reference.classList.remove('d-none');
                any_tool_visible = true;
            } else {
                tool.reference.classList.add('d-none');
            }
        }

        /* Show or hide the #tools_not_found div */
        const tools_not_found_element = document.querySelector('#tools_no_data');
        if(any_tool_visible) {
            tools_not_found_element.classList.add('d-none');
        } else {
            tools_not_found_element.classList.remove('d-none');
        }
    });
</script>
<?php \Altum\Event::add_content(ob_get_clean(), 'javascript') ?>

<?php ob_start() ?>
    <script type="application/ld+json">
        {
            "@context": "https://schema.org",
            "@type": "BreadcrumbList",
            "itemListElement": [
                {
                    "@type": "ListItem",
                    "position": 1,
                    "name": <?= json_encode(l('index.title')) ?>,
                    "item": <?= json_encode(url()) ?>
                },
                {
                    "@type": "ListItem",
                    "position": 2,
                    "name": <?= json_encode(l('tools.title')) ?>,
                    "item": <?= json_encode(url('tools')) ?>
                }
            ]
        }
    </script>
<?php \Altum\Event::add_content(ob_get_clean(), 'javascript') ?>
