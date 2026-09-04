<?php

define('ROOT_PATH', realpath(__DIR__ . '/..') . '/');
require_once ROOT_PATH . 'vendor/autoload.php';
require_once ROOT_PATH . 'app/includes/product.php';
require_once ROOT_PATH . 'config.php';
require_once ROOT_PATH . 'update/info.php';

$database = new \mysqli(
    DATABASE_SERVER,
    DATABASE_USERNAME,
    DATABASE_PASSWORD,
    DATABASE_NAME
);

if($database->connect_error) {
    die('The database connection has failed!');
}

$product_info = $database->query("SELECT `value` FROM `settings` WHERE `key` = 'product_info'")->fetch_object() ?? null;

if($product_info) {
    $product_info = json_decode($product_info->value);
}

?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

    <link rel="stylesheet" href="assets/css/bootstrap.min.css">
    <link rel="stylesheet" href="assets/css/custom.css">

    <link rel="icon" href="./assets/favicons/favicon.ico">

    <title><?= PRODUCT_NAME ?> Update</title>
</head>
<body>

<div class="container py-4 col-12 col-md-10 col-lg-8 mx-auto">
    <div class="text-center">
        <img src="./assets/images/logo.png" class="img-fluid logo" alt="AltumCode logo" />
    </div>

    <header class="card header mt-4">
        <div class="card-body">
            <div class="d-flex flex-row align-items-center justify-content-between">
                <h1 class="h3 mb-0">Update</h1>
                <p class="subheader d-flex flex-row">
                    <span class="text-muted h6 mb-0">
                        <a href="<?= PRODUCT_URL ?>" target="_blank" class="text-gray-500"><?= PRODUCT_NAME ?></a> by <a href="https://altumco.de/site" target="_blank" class="text-gray-500">AltumCode</a>
                    </span>
                </p>
            </div>
        </div>
    </header>
</div>

<main class="mt-4 mb-4">
    <div class="container col-12 col-md-10 col-lg-8 mx-auto">

        <div class="mb-4">
            <div class="card">
                <div class="card-body">
                    <nav class="nav sidebar-nav">
                        <ul class="sidebar mb-0 d-flex flex-column flex-lg-row flex-wrap" id="sidebar-ul">
                            <li class="nav-item">
                                <a href="#welcome" class="navigator nav-link font-size-little-small"><span class="icon-wrapper icon-welcome mr-2"><i class="fas fa-fw fa-sm fa-home"></i></span>Welcome</a>
                            </li>

                            <li class="nav-item">
                                <a href="#update" class="navigator nav-link font-size-little-small disabled"><span class="icon-wrapper icon-update mr-2"><i class="fas fa-fw fa-sm fa-sync-alt"></i></span>Update</a>
                            </li>

                            <li class="nav-item">
                                <a href="#finish" class="navigator nav-link font-size-little-small disabled"><span class="icon-wrapper icon-finish mr-2"><i class="fas fa-fw fa-sm fa-check-circle"></i></span>Finish</a>
                            </li>
                        </ul>
                    </nav>
                </div>
            </div>
        </div>

        <div class="" id="content">
            <div class="card">
                <div class="card-body">
                    <section id="welcome" style="display: none">
                        <p>Thank you for choosing <a href="https://altumco.de/site" target="_blank">AltumCode</a>.</p>

                        <p>By continuing, you agree to the privacy policy and terms of service of <a href="<?= PRODUCT_URL ?>" target="_blank"><?= PRODUCT_NAME ?></a>.</p>

                        <a href="#update" id="welcome_start" class="navigator btn btn-block btn-primary mt-4">Next</a>
                    </section>

                    <section id="update" style="display: none">
                        <form id="setup_form" method="post" action="" role="form">
                            <div class="form-group">
                                <label for="product_version">Current version</label>
                                <input type="text" class="form-control" id="product_version" name="product_version" value="<?= $product_info ? $product_info->version : (defined('PRODUCT_VERSION') ? PRODUCT_VERSION : '8.0.0') ?>" aria-describedby="license_help" readonly="readonly">
                            </div>

                            <div class="form-group">
                                <label for="new_product_version">Final version</label>
                                <input type="text" class="form-control" id="new_product_version" name="new_product_version" value="<?= NEW_PRODUCT_VERSION ?>" aria-describedby="license_help" readonly="readonly">
                            </div>

                            <?php if(($product_info ? $product_info->version : PRODUCT_VERSION) == NEW_PRODUCT_VERSION): ?>
                                <div class="alert alert-success">Your database is already on the latest version.</div>
                            <?php else: ?>
                                <button type="submit" name="submit" class="btn btn-block btn-primary mt-4">Apply update</button>
                            <?php endif ?>
                        </form>
                    </section>

                    <section id="finish" style="display: none">
                        <div class="alert alert-success"><strong>Success!</strong> The database update is finished!</div>

                        <div class="alert alert-info mb-0">It is now recommended to <strong>delete the /update folder</strong>.</div>
                    </section>
                </div>
            </div>
        </div>

    </div>
</main>

<script src="assets/js/jquery.min.js"></script>
<script src="assets/js/popper.min.js"></script>
<script src="assets/js/bootstrap.min.js"></script>
<script src="assets/js/tsparticles.confetti.bundle.min.js"></script>
<script src="assets/js/main.js"></script>
<script src="assets/js/fontawesome.min.js" defer></script>
<script src="assets/js/fontawesome-solid.min.js" defer></script>
<script src="assets/js/fontawesome-brands.modified.js" defer></script>

</body>
</html>
