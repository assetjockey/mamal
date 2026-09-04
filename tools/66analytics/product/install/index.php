<?php
const ALTUMCODE = 66;
define('ROOT', realpath(__DIR__ . '/..') . '/');
require_once ROOT . 'app/includes/product.php';
require_once ROOT . 'install/info.php';

if(file_exists(ROOT . 'install/installed')) {
	die();
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

    <title><?= PRODUCT_NAME ?> Installation</title>
</head>
<body>

<div class="container py-4 col-12 col-md-10 col-lg-8 mx-auto">
    <div class="text-center">
        <img src="./assets/images/logo.png" class="img-fluid logo" alt="AltumCode logo" />
    </div>

    <header class="card header mt-4">
        <div class="card-body">
            <div class="d-flex flex-row align-items-center justify-content-between">
                <h1 class="h3 mb-0">Installation</h1>
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
                                <a href="#requirements" class="navigator nav-link font-size-little-small disabled"><span class="icon-wrapper icon-requirements mr-2"><i class="fas fa-fw fa-sm fa-clipboard-list"></i></span>Requirements</a>
                            </li>

                            <li class="nav-item">
                                <a href="#permissions" class="navigator nav-link font-size-little-small disabled"><span class="icon-wrapper icon-permissions mr-2"><i class="fas fa-fw fa-sm fa-lock"></i></span>Permissions</a>
                            </li>

                            <li class="nav-item">
                                <a href="#setup" class="navigator nav-link font-size-little-small disabled"><span class="icon-wrapper icon-setup mr-2"><i class="fas fa-fw fa-sm fa-cog"></i></span>Setup</a>
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

                        <a href="#requirements" id="welcome_start" class="navigator btn btn-block btn-primary mt-4">Start installation</a>
                    </section>

                    <section id="requirements" style="display: none">
						<?php $requirements = true ?>

                        <div class="table-responsive table-custom-container">
                            <table class="table table-custom">
                                <thead>
	                                <tr>
	                                    <th>Requirement</th>
	                                    <th></th>
	                                </tr>
                                </thead>
                                <tbody>
	                                <tr>
	                                    <td>PHP 8.4 - 8.5</td>
	                                    <td class="text-right">
											<?php if(version_compare(PHP_VERSION, '8.4.0', '>=') && version_compare(PHP_VERSION, '8.6', '<')): ?>
	                                            <i class="fas fa-fw fa-check-circle text-success"></i>
                                                
										<?php else: ?>
                                            <i class="fas fa-fw fa-times-circle text-danger"></i>
                                            
											<?php $requirements = false; ?>
										<?php endif ?>
                                    </td>
                                </tr>

	                                <tr>
	                                    <td>cURL</td>
	                                    <td class="text-right">
											<?php if(function_exists('curl_version')): ?>
	                                            <i class="fas fa-fw fa-check-circle text-success"></i>
                                                
										<?php else: ?>
                                            <i class="fas fa-fw fa-times-circle text-danger"></i>
                                            
											<?php $requirements = false; ?>
										<?php endif ?>
                                    </td>
                                </tr>

	                                <tr>
	                                    <td>OpenSSL</td>
	                                    <td class="text-right">
											<?php if(extension_loaded('openssl')): ?>
	                                            <i class="fas fa-fw fa-check-circle text-success"></i>
                                                
										<?php else: ?>
                                            <i class="fas fa-fw fa-times-circle text-danger"></i>
                                            
											<?php $requirements = false; ?>
										<?php endif ?>
                                    </td>
                                </tr>

	                                <tr>
	                                    <td>mbstring</td>
	                                    <td class="text-right">
											<?php if(extension_loaded('mbstring') && function_exists('mb_get_info')): ?>
	                                            <i class="fas fa-fw fa-check-circle text-success"></i>
                                                
										<?php else: ?>
                                            <i class="fas fa-fw fa-times-circle text-danger"></i>
                                            
											<?php $requirements = false; ?>
										<?php endif ?>
                                    </td>
                                </tr>

	                                <tr>
	                                    <td>MySQLi</td>
	                                    <td class="text-right">
											<?php if(function_exists('mysqli_connect')): ?>
	                                            <i class="fas fa-fw fa-check-circle text-success"></i>
                                                
										<?php else: ?>
                                            <i class="fas fa-fw fa-times-circle text-danger"></i>
                                            
											<?php $requirements = false; ?>
										<?php endif ?>
                                    </td>
                                </tr>
                                </tbody>
                            </table>
                        </div>

                        <div class="mt-4">
							<?php if($requirements): ?>
                                <a href="#permissions" class="navigator btn btn-block btn-primary">Next</a>
							<?php else: ?>
                                <div class="alert alert-danger" role="alert">
                                    Please make sure all the requirements are met before continuing.
                                </div>
							<?php endif ?>
                        </div>
                    </section>

                    <section id="permissions" style="display: none">
						<?php $permissions = true ?>

                        <div class="table-responsive table-custom-container">
                            <table class="table table-custom">
                                <thead>
                                <tr>
                                    <th>Folder or file path</th>
                                    <th></th>
                                </tr>
                                </thead>
                                <tbody>
									<?php foreach(INSTALL_WRITABLE_PATHS as $key): ?>
                                    <tr>
                                        <td>/<?= $key ?></td>
                                        <td class="text-right">
											<?php if(is_writable(ROOT . $key)): ?>
                                                <i class="fas fa-fw fa-check-circle text-success"></i>
                                                
											<?php else: ?>
                                                <i class="fas fa-fw fa-times-circle text-danger"></i>
                                                
												<?php $permissions = false; ?>
											<?php endif ?>
                                        </td>
                                    </tr>
								<?php endforeach ?>
                                </tbody>
                            </table>
                        </div>

                        <div class="mt-4">
							<?php if($permissions): ?>
                                <a href="#setup" class="navigator btn btn-block btn-primary">Next</a>
							<?php else: ?>
                                <div class="alert alert-danger" role="alert">
                                    Please make sure all the requirements listed on the documentation and on this page are met before continuing!
                                </div>
							<?php endif ?>
                        </div>
                    </section>

                    <section id="setup" style="display: none">
						<?php
						$actual_link = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
						$installation_url = preg_replace('/install\/$/', '', $actual_link);
						?>
                        <form id="setup_form" method="post" action="" role="form">
                            <div class="form-group">
                                <label for="license_key">License key</label>
                                <input type="text" class="form-control" id="license_key" name="license_key" required="required">
                                <small class="form-text text-muted">The unique license key that you got after purchasing. Also known as the Purchase Code.</small>
                            </div>

                            <div class="form-group">
                                <label for="installation_url">Website URL</label>
                                <input type="text" class="form-control" id="installation_url" name="installation_url" value="<?= $installation_url ?>" placeholder="https://example.com/" required="required">
                                <small class="form-text text-muted">Make sure to specify the full url of the installation path of the product.<br /> Subdomain example: <code>https://subdomain.domain.com/</code> <br />Subfolder example: <code>https://domain.com/product/</code></small>
                            </div>

                            <h3 class="mt-5">Database Details</h3>
                            <p>Fill in the database details that you will use for the installation of this product.</p>

                            <div class="form-group">
                                <label for="database_host">Host</label>
                                <input type="text" class="form-control" id="database_host" name="database_host" value="localhost" required="required">
                            </div>

                            <div class="form-group">
                                <label for="database_name">Name</label>
                                <input type="text" class="form-control" id="database_name" name="database_name" required="required">
                            </div>

                            <div class="form-group">
                                <label for="database_username">Username</label>
                                <input type="text" class="form-control" id="database_username" name="database_username" required="required">
                            </div>

                            <div class="form-group">
                                <label for="database_password">Password</label>
                                <input type="password" class="form-control" id="database_password" name="database_password">
                            </div>


                            <h3 class="mt-5">Newsletter</h3>
                            <p><strong>Optional.</strong> Receive email updates of <span style="font-weight: 500">new products</span>, <span style="font-weight: 500">discounts</span> and <span style="font-weight: 500">product updates</span>.</p>

                            <div class="form-group row">
                                <label for="newsletter_email" class="col-sm-2 col-form-label">Email</label>
                                <div class="col-sm-10">
                                    <input type="email" class="form-control" id="newsletter_email" name="newsletter_email" placeholder="Your valid email address">
                                </div>
                            </div>

                            <div class="form-group row">
                                <label for="newsletter_name" class="col-sm-2 col-form-label">Name</label>
                                <div class="col-sm-10">
                                    <input type="text" class="form-control" id="newsletter_name" name="newsletter_name" placeholder="Your full name">
                                </div>
                            </div>

                            <button type="submit" name="submit" class="btn btn-block btn-primary mt-4">Complete installation</button>
                        </form>
                    </section>

                    <section id="finish" style="display: none">
                        <div class="alert alert-success">The installation process has been successfuly completed!</div>

                        <div class="table-responsive table-custom-container mt-4">
                            <table class="table table-custom">
                                <tbody>
                                <tr>
                                    <td class="font-weight-bold">URL</td>
                                    <td><a href="" id="final_url"></a></td>
                                </tr>
                                <tr>
                                    <td class="font-weight-bold">Username</td>
                                    <td>admin</td>
                                </tr>
                                <tr>
                                    <td class="font-weight-bold">Password</td>
                                    <td>admin</td>
                                </tr>
                                </tbody>
                            </table>
                        </div>
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
