<?php

function ads_home_page_top()
{
    if (ads('home_page_top') && request()->routeIs('home') && subscription()->plan->advertisements) {
        return '<center>
           <div class="vr-adv-unit vr-adv-unit-728x90 mt-80 mb-3">' . ads('home_page_top')->code . '</div>
        </center>';
    }
}

function ads_home_page_bottom()
{
    if (ads('home_page_bottom') && request()->routeIs('home') && subscription()->plan->advertisements) {
        return '<center>
           <div class="vr-adv-unit vr-adv-unit-728x90 mb-80 mt-3">' . ads('home_page_bottom')->code . '</div>
        </center>';
    }
}

function ads_blog_articles_center()
{
    if (ads('blog_articles_center') && subscription()->plan->advertisements) {
        return '<center>
           <div class="vr-adv-unit vr-adv-unit-728x90 my-4">' . ads('blog_articles_center')->code . '</div>
        </center>';
    }
}

function ads_blog_articles_bottom()
{
    if (ads('blog_articles_bottom') && subscription()->plan->advertisements) {
        return '<center>
           <div class="vr-adv-unit vr-adv-unit-728x90 my-5">' . ads('blog_articles_bottom')->code . '</div>
        </center>';
    }
}

function ads_blog_sidebar()
{
    if (ads('blog_sidebar') && subscription()->plan->advertisements) {
        return '<center>
           <div class="vr-adv-unit vr-adv-unit-300x280 my-4">' . ads('blog_sidebar')->code . '</div>
        </center>';
    }
}

function ads_blog_single_article_top()
{
    if (ads('blog_single_article_top') && subscription()->plan->advertisements) {
        return '<center>
           <div class="vr-adv-unit vr-adv-unit-728x90 my-4">' . ads('blog_single_article_top')->code . '</div>
        </center>';
    }
}

function ads_blog_single_article_bottom()
{
    if (ads('blog_single_article_bottom') && subscription()->plan->advertisements) {
        return '<center>
           <div class="vr-adv-unit vr-adv-unit-728x90 mt-4">' . ads('blog_single_article_bottom')->code . '</div>
        </center>';
    }
}

function ads_download_page_top()
{
    if (ads('download_page_top') && subscription()->plan->advertisements) {
        return '<center>
           <div class="vr-adv-unit vr-adv-unit-728x90 mb-4">' . ads('download_page_top')->code . '</div>
        </center>';
    }
}

function ads_download_page_bottom()
{
    if (ads('download_page_bottom') && subscription()->plan->advertisements) {
        return '<center>
           <div class="vr-adv-unit vr-adv-unit-728x90 mt-4">' . ads('download_page_bottom')->code . '</div>
        </center>';
    }
}

function ads_download_page_down_bottom()
{
    if (ads('download_page_down_bottom') && request()->routeIs('transfer.download.index') && subscription()->plan->advertisements) {
        return '<center>
           <div class="vr-adv-unit vr-adv-unit-728x90 mb-80">' . ads('download_page_down_bottom')->code . '</div>
        </center>';
    }
}

function head_code()
{
    if (ads('head_code') && subscription()->plan->advertisements) {
        return ads('head_code')->code;
    }
}
