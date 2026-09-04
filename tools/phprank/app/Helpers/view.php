<?php

/**
 * Get the category's color.
 *
 * @param $name
 * @return string|void
 */
function categoryColor($name)
{
    switch ($name) {
        case 'development':
            return 'info';
        case 'research':
            return 'danger';
        case 'content':
            return 'warning';
    }
}
