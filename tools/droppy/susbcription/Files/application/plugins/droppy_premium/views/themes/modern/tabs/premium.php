<style>
    .premium-iframe {
        min-width: 900px;
    }

    .premium-iframe::-webkit-scrollbar {
        width: 0px;
        height: 0px;
    }

    @media only screen and (max-width: 900px) {
        .premium-iframe {
            min-width: 300px;
        }
    }
</style>

<iframe src="<?php echo $this->config->item('site_url') ?>page/premium?iframe=true<?php foreach ($_GET AS $key => $val) { echo '&' .$key. '='. $val; } ?>" style="width: 100%;height: 100%;" class="premium-iframe"></iframe>

<script>
    var premium_frame = document.getElementsByClassName("premium-iframe");
    premium_frame.scrolling = "no";
</script>

<?php if(isset($_SESSION['droppy_premium'])) { ?>
<script>
    $('#page-tabs a[data-target="tab-gopremium"]').parents('li').remove();
</script>
<?php } ?>