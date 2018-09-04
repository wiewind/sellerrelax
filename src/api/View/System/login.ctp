<h1 align="center"><?= __("Login") ?></h1>
<form role="form">
    <div class="form-group">
        <label for="firstname" class="col-sm-2 control-label"><?= __("Username") ?>:</label>
        <div class="col-sm-10">
            <input type="text" class="form-control" id="username" placeholder="<?= __("Please enter your username") ?>">
        </div>
    </div>
    <br />
    <br />
    <div class="form-group">
        <label for="lastname" class="col-sm-2 control-label"><?= __("Password") ?>:</label>
        <div class="col-sm-10">
            <input type="password" class="form-control" id="password" placeholder="<?= __("Please enter your password") ?>">
        </div>
    </div>
    <br />
    <div class="form-group">
        <div class="col-sm-offset-2 col-sm-10 error" id="resdiv">
        </div>
    </div>
    <br />
    <div class="form-group">
        <div class="col-sm-offset-2 col-sm-10">
            <button type="button" class="btn btn-default" onclick="onSubmit()"><?= __("Login") ?></button>
        </div>
    </div>
</form>
<script>
    var onSubmit = function () {
        $('#resdiv').html('please wait...');

        var username = $('#username').val();
        var password = $('#password').val();

        $.ajax({
            url: "<?= Configure::read('system.api.path') ?>/system/json/dologin",
            method: 'POST',
            data: {
                username: username,
                password: password
            }
        }).done(function(data) {
            data = jQuery.parseJSON(data);
            if (!data.success) {
                $('#resdiv').html('Error: ' + data.message);
            } else {
                window.location.assign('/')
            }
        });
    };
</script>