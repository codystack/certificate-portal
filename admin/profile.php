<?php
$page = "Profile";
include __DIR__ . "/components/head.php";
include __DIR__ . "/components/sidebar.php";
?>
<div class="main-content">
    <?php include __DIR__ . "/components/topbar.php"; ?>

    <div class="container-fluid">
        <div class="row justify-content-center">
            <div class="col-12 col-lg-10 col-xl-8">

                <?php include __DIR__ . "/components/profile-header.php"; ?>

                <input type="file" id="photoFileInput" accept=".jpg,.jpeg,.png" style="display:none;">
                <div class="row justify-content-between align-items-center">
                    <div class="col">
                        <div class="row align-items-center">
                            <div class="col-auto">
                                <div class="avatar avatar-lg" id="avatarWrap">
                                    <img class="avatar-img rounded-circle" id="avatarPreview"
                                         src="<?php echo e($avatar); ?>" alt="avatar" onerror="this.src='../assets/images/avatar.png'">
                                </div>
                            </div>
                            <div class="col ms-n2">
                                <h4 class="mb-1">Photograph</h4>
                                <small class="text-body-secondary">JPG or PNG, max 10 MB.</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-auto">
                        <button class="btn btn-sm btn-primary" id="uploadPhotoBtn" type="button">
                            <i class="fe fe-upload-cloud me-1"></i> Upload Photo
                        </button>
                    </div>
                </div>

                <hr class="my-5">

                <form id="profileForm">
                    <div class="row">
                        <div class="col-12 col-md-6">
                            <div class="form-floating form-group">
                                <input type="text" name="firstName" class="form-control" value="<?php echo $firstName; ?>" required>
                                <label>First Name</label>
                            </div>
                        </div>

                        <div class="col-12 col-md-6">
                            <div class="form-floating form-group">
                                <input type="text" name="lastName" class="form-control" value="<?php echo e($_SESSION["last_name"] ?? ""); ?>" required>
                                <label>Last Name</label>
                            </div>
                        </div>

                        <div class="col-12 col-md-6">
                            <div class="form-floating form-group">
                                <input type="email" class="form-control" value="<?php echo $userEmail; ?>" readonly>
                                <label>Email</label>
                            </div>
                        </div>

                        <div class="col-12 col-md-6">
                            <div class="form-floating form-group">
                                <input type="text" class="form-control" value="<?php echo $designation; ?>" readonly>
                                <label>Role</label>
                            </div>
                        </div>
                    </div>

                    <button class="btn btn-primary" id="profileSubmit" type="submit">
                        Save Changes
                    </button>
                </form>

                <br><br>

            </div>
        </div>
    </div>
</div>

<?php include __DIR__ . "/components/footer.php"; ?>

<script>
document.getElementById('uploadPhotoBtn').addEventListener('click', function () {
    document.getElementById('photoFileInput').click();
});

document.getElementById('photoFileInput').addEventListener('change', async function () {
    var file = this.files[0];
    if (!file) return;

    if (!['image/jpeg', 'image/png'].includes(file.type)) {
        notyf.error('Only JPG and PNG files are allowed.');
        this.value = '';
        return;
    }
    if (file.size > 10 * 1024 * 1024) {
        notyf.error('File is too large. Maximum size is 10 MB.');
        this.value = '';
        return;
    }

    var reader = new FileReader();
    reader.onload = function (e) {
        document.getElementById('avatarPreview').src = e.target.result;
    };
    reader.readAsDataURL(file);

    var btn = document.getElementById('uploadPhotoBtn');
    btn.disabled = true;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Uploading…';

    var fd = new FormData();
    fd.append('photo', file);
    fd.append('csrf', CSRF_TOKEN);

    try {
        var res = await fetch('auth/update/profile.php', { method: 'POST', body: fd });
        var data = await res.json();
        if (data.success) {
            notyf.success(data.message);
        } else {
            notyf.error(data.message);
            document.getElementById('avatarPreview').src = '<?php echo e($avatar); ?>';
        }
    } catch (e) {
        notyf.error('Network error. Please try again.');
        document.getElementById('avatarPreview').src = '<?php echo e($avatar); ?>';
    }

    btn.disabled = false;
    btn.innerHTML = '<i class="fe fe-upload-cloud me-1"></i> Upload Photo';
    document.getElementById('photoFileInput').value = '';
});

document.getElementById('profileForm').addEventListener('submit', async function (e) {
    e.preventDefault();
    var btn = document.getElementById('profileSubmit');
    btn.disabled = true;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Saving…';

    var fd = new FormData(this);
    fd.append('csrf', CSRF_TOKEN);

    try {
        var res = await fetch('auth/update/profile.php', { method: 'POST', body: fd });
        var data = await res.json();
        if (data.success) {
            notyf.success(data.message);
        } else {
            notyf.error(data.message);
        }
    } catch (e) {
        notyf.error('Network error. Please try again.');
    }

    btn.disabled = false;
    btn.innerHTML = 'Save Changes';
});
</script>
