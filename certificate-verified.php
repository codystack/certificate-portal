<?php
    session_start();

    require_once "config/db.php";

    
    if (!isset($_SESSION['certNum'])) {
        header('location: ./');
    }

    if (isset($_GET['close'])) {
        session_destroy();
        unset($_SESSION['certNum']);
        header("location: ./");
    }
?>
<html>
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
        <meta name="viewport" content="width=device-width, initial-scale=1" />
        <meta http-equiv="X-UA-Compatible" content="IE=edge" />
        <meta name="author" content="webify.com.ng">
        <title>Verify Certificate :: Glajoe Services&trade;</title>

        <link rel="stylesheet" href="assets/vendors/iconfonts/mdi/font/css/materialdesignicons.min.css">
        <link rel="stylesheet" href="assets/vendors/css/vendor.bundle.base.css">
        <link rel="stylesheet" href="assets/vendors/css/vendor.bundle.addons.css">
        <link rel="stylesheet" href="assets/css/verify.css">
        <link rel="stylesheet" href="assets/css/style.css">
        <link rel="shortcut icon" href="assets/images/glajoe-favicon.png" />
    </head>
<body>
    <div class="s130">
        <div class="container">
            <div class="row">
                <div class="col-lg-8 mx-auto mt-6">
                    <div class="card">
                        <div class="card-body mt-3">
                            <div class="text-center mb-4">
                                <div class="mb-0">
                                    <img alt="QR Code" src="assets/images/certificate.png" class="img-responsive" width="108" height="108">
                                </div>
                                <h4 class="mb-n3 mt-0">Certificate/Report No.</h4>
                                <div class="text-center mt-4">
                                    <div class="">
                                        <img src="assets/images/barcode.png">&nbsp; <span><?php echo $_SESSION['certNum']; ?></span>
                                    </div>
                                </div>
                            </div>

                            <div class="row mt-3 mb-3 text-center">
                                <div class="col-md-6 col-lg-6">
                                    <div class="d-flex align-items-start">
                                        <div class="flex-grow-1 mb-3">
                                            Title<br />
                                            <strong>
                                                <?php echo $_SESSION['title']; ?>
                                            </strong>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-md-6 col-lg-6">
                                    <div class="d-flex align-items-start">
                                        <div class="flex-grow-1 mb-3">
                                        Client's Name<br />
                                            <strong>
                                                <?php echo $_SESSION['client']; ?>
                                            </strong>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="mt-4 text-center mb-4">
                                <a href="https://portal.glajoeservices.com.ng/admin/<?php echo $_SESSION['image']; ?>" target="_blank" class="btn btn-primary">View Certificate</a>
                                <a href="close" class="btn btn-danger">Go Back</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script src="js/extention/choices.js"></script>
    <script src="https://unpkg.com/sweetalert/dist/sweetalert.min.js"></script>

    <?php
        if (isset($_SESSION['message']))
        {
            ?>
            <script>
                swal({
                    title: "<?php echo $_SESSION['message_title']; ?>",
                    text: "<?php echo $_SESSION['message']; ?>",
                    icon: "error",
                    buttons: false,
                    timer: 3000
                });
            </script>
            <?php
            unset($_SESSION['message']);
        }
    ?>
  </body>
</html>